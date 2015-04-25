#!/usr/bin/env php
<?php

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../../../autoload.php';
}

use Commando\Command as CommandOptions;
use PhpTags\Database;
use PhpTags\ExtensionTags;
use PhpTags\Files;
use PhpTags\IndexCommand;
use PhpTags\Parser;
use PhpTags\QueryCommand;

// Setup generic options.
if (array_intersect(array('index', 'query'), $argv)) {
    $options = new CommandOptions();
    $options->option()
        ->title('action')
        ->describedAs('Possible actions: index, query');

    // The project root specifies the starting point of the recursive file
    // search. This allows running the command from outside of the project
    // directory.
    $options->option('root')
        ->describedAs('Project root (defaults to current directory)')
        ->must(function($directory) {
            return is_dir($directory);
        });

    // It's possible to change the name of the SQLite database file. Absolute file
    // paths are allowed and the path does not have to be contained inside the
    // project root.
    $options->option('database')
        ->describedAs('Location of SQLite database')
        ->default('PHPTAGS.sqlite');
} else {
    print <<<USAGE
Usage:
        {$argv[0]} index [--help]
        {$argv[0]} query [PATTERN] [--help]

USAGE;
    exit;
}

// Setup action-specific options.
if (in_array('index', $argv)) {
    // All files matching the regular expression are included in the parsing
    // process. The expression is performed on the full file path, so it's
    // possible to exclude specific directories.
    $options->option('pattern')
        ->describedAs('PCRE file name pattern to match')
        ->default('/\.php[s457]?$/i');

    // Specify built-in extensions to include tags.
    $options->option('extensions')
        ->describedAs('Comma-separated list of built-in extensions to extract tags');
} else {
    // The command takes a required tag name pattern.
    $options->option()
        ->title('pattern')
        ->default('.*')
        ->describedAs('The PCRE pattern to match tag names');

    $options->option('ignore-case')
        ->aka('i')
        ->describedAs('Ignore case distinctions in the pattern')
        ->boolean();

    $options->option('type')
        ->aka('t')
        ->describedAs('Filter by tag type');

    $options->option('format')
        ->aka('f')
        ->describedAs('Output format: tabs (default), lisp or ctags');

    $options->option('reference')
        ->aka('r')
        ->describedAs('Find references instead of definitions')
        ->boolean();
}

// Determine project directory and database location.
$database = basename($options['database']);
if ($options['root']) {
    $directory = $options['root'];
} else {
    // If database name is absolute, deduce project root
    if ($options['database'][0] === DIRECTORY_SEPARATOR) {
        $directory = dirname($options['database']);
    } else {
        $directory = getcwd();

        // Find project root.
        while (!file_exists($directory . DIRECTORY_SEPARATOR . $options['database'])) {
            $directory = dirname($directory);

            // Fall back to current directory.
            if ($directory === DIRECTORY_SEPARATOR) {
                $directory = getcwd();
                break;
            }
        }
    }
}

// Initialize the SQLite database.
$database = new Database(
    new SQLite3($directory . DIRECTORY_SEPARATOR . $database)
);

// Execute the requested action.
switch ($options[0]) {
    case 'index':
        $extensions = explode(',', $options['extensions']);
        $extensions = array_filter($extensions);

        $command = new IndexCommand(
            new Files(
                $directory,
                $options['pattern']
            ),
            $database,
            new Parser,
            new ExtensionTags($extensions)
        );

        $command->execute(function($message) {
            print $message;
        });

        break;

    case 'query':
        $command = new QueryCommand($database);
        $usageType = ((bool)$options['reference'])
            ? \PhpTags\Tag::REFERENCE
            : \PhpTags\Tag::DEFINITION;

        switch ($options['format']) {
            case 'ctags':
                $outputFormat = QueryCommand::FORMAT_CTAGS;
                break;

            case 'lisp':
                $outputFormat = QueryCommand::FORMAT_LISP;
                break;

            default:
                $outputFormat = QueryCommand::FORMAT_TABS;
        }

        if (isset($options[1])) {
            print $command->findByPattern(
                $options[1],
                $usageType,
                (bool)$options['ignore-case'],
                $options['type'],
                $outputFormat
            );
        }

        break;
}

print PHP_EOL;
