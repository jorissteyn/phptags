<?php
/**
 * This file is part of PHPTAGS, a PHP source code tagging system.
 *
 * Copyright 2014 Joris Steyn <jorissteyn@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace PhpTags;

use PhpParser\Error as ParserError;
use PhpTags\Database;
use PhpTags\ExtensionTags;
use PhpTags\Files;
use PhpTags\Parser;

class IndexCommand
{
    /**
     * @var Files $files
     */
    public $files;

    /**
     * @var Database $database
     */
    public $database;

    /**
     * @var Parser $parser
     */
    public $parser;

    /**
     * @var ExtensionTags $extensionTags
     */
    public $extensionTags;

    /**
     * @param Files $files
     * @param Database $database
     * @param Parser $parser
     */
    public function __construct(Files $files, Database $database, Parser $parser, ExtensionTags $extensionTags)
    {
        $this->files         = $files;
        $this->database      = $database;
        $this->parser        = $parser;
        $this->extensionTags = $extensionTags;
    }

    /**
     * Execute the command.
     */
    public function execute(\Closure $reporter = null)
    {
        // Clear all existing tags.
        $this->database->truncate();

        // Export built-in tags.
        $tags = $this->extensionTags->getTags();
        if ($reporter !== null) {
            $reporter(
                "Importing tags for %d built-in modules...\n",
                count($tags)
            );
        }

        foreach ($tags as $module => $moduleTags) {
            $this->database->addTags("<$module>", $moduleTags);
        }

        // Start indexing the tags and report progress.
        $reporter(sprintf(
            "Importing %d source files...\n",
            count($this->files)
        ));

        foreach ($this->files as $index => $file) {
            try {
              $tags = $this->parser->getTags($file);
              $tagCount += count($tags);

              $this->database->addTags($file, $tags);

            } catch (ParserError $e) {
                if ($reporter !== null) {
                    $reporter(sprintf("\nError '%s' in '%s'\n", $e->getMessage(), $file));
                }
            }

            if ($reporter !== null) {
                $reporter(
                    sprintf(
                        "\r  [%d files, %d tags, %d%%]",
                        $index + 1,
                        $tagCount,
                        ceil(($index + 1) / count($this->files) * 100)
                    )
                );
            }
        }
    }
}
