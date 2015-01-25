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

use PhpTags\Database;
use PhpTags\Files;
use PhpTags\Parser;
use PhpTags\Tag;

class QueryCommand
{
    /**
     * @const string
     */
    const FORMAT_TABS = 'tabs';

    /**
     * @const string
     */
    const FORMAT_LISP = 'lisp';

    /**
     * @const string
     */
    const FORMAT_CTAGS = 'ctags';

    /**
     * @var Database $database
     */
    public $database;

    /**
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Execute the command.
     *
     * @param string      $pattern
     * @param string      $usageType  Find tags of type 'reference' or 'definition' (default)
     * @param bool        $ignoreCase Case sentitive search (optional)
     * @param string      $tagType    Filter by tag type (optional)
     * @param string      $format     Output format
     * @return string
     */
    public function findByPattern($pattern, $usageType = PhpTags\Tag::DEFINITION, $ignoreCase = true, $tagType = null, $format = null)
    {
        if ($tagType === '*') {
            $tagType = null;
        }

        $tags = $this->database->findByPattern($pattern, $usageType, $ignoreCase, $tagType);

        foreach ($tags as $index => $tag) {
            // Usage type is part of the query, no need to return it.
            unset($tag['usage_type']);

            foreach ($tag as $field => $value) {
                $tag[$field] = $this->escapeString($tag[$field]);
            }

            $tags[$index] = $tag;
        }

        switch ($format) {
            case self::FORMAT_CTAGS:
                return $this->formatCtags($tags);

            case self::FORMAT_LISP:
                return $this->formatLisp($tags);

            default:
                return $this->formatTabSeparated($tags);
        }
    }

    /**
     * Format tags to global output
     *
     * @param array $tags
     * @return string
     */
    public function formatTabSeparated($tags)
    {
        $lines = array();

        foreach ($tags as $tag) {
            $lines[] = implode("\t", $tag);
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * Format tags as ctags
     *
     * @param array $tags
     * @return string
     */
    public function formatCtags($tags)
    {
        $lines = array();

        foreach ($tags as $tag) {
            $lines[] = sprintf(
                "%s\t%s\t%s",
                $tag['name'],
                $tag['file'],
                $tag['start'],
                ';"' . $tag['comment']
            );
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * Format tags to global output
     *
     * @param array $tags
     * @return string
     */
    public function formatLisp($tags)
    {
        $lists = array();

        foreach ($tags as $tag) {
            $atoms = array();
            foreach ($tag as $field => $value) {
                switch ($field) {
                    case 'start_line':
                    case 'end_line':
                    case 'start_file_pos':
                    case 'end_file_pos':
                        $atoms[] = $value;
                        break;

                    default:
                        $atoms[] = sprintf(
                            '"%s"',
                            str_replace(
                                array('\\', '"', "'"),
                                array('\\\\', '\\"', "\\'"),
                                $value
                            )
                        );
                        break;
                }
            }

            $lists[] = sprintf('(%s)', implode(" ", $atoms));
        }

        return sprintf('(%s)', implode("\n  ", $lists));
    }

    /**
     * Escape string for use in tag output
     *
     * @param string $value
     * @return string
     */
    public function escapeString($value)
    {
        return str_replace(
            array("\t", "\r", "\n"),
            array('\t', '\r', '\n'),
            $value
        );
    }
}
