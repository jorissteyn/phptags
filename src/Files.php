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

class Files implements \Countable, \Iterator
{
    /**
     * @var string $directory
     */
    protected $directory;

    /**
     * @var string $pattern
     */
    protected $pattern;

    /**
     * @var array $list
     */
    protected $list;

    /**
     * @var int $position
     */
    protected $position = 0;

    /**
     * @param string $directory
     * @param string $pattern
     */
    public function __construct($directory, $pattern)
    {
        $this->directory = $directory;
        $this->pattern = $pattern;
    }

    /**
     * Return files all files in the directory matching pattern.
     *
     * The actual recursive scanning happens the first time this method is
     * called, subsequent invocations return the cached result.
     *
     * @return array
     */
    public function ls()
    {
        if ($this->list === null) {
            $this->list = $this->uncachedList();
        }

        return $this->list;
    }

    /**
     * Count all files matching pattern.
     *
     * @return array
     */
    public function count()
    {
        return count($this->ls());
    }

    /**
     * @return array
     */
    protected function uncachedList()
    {
        $list = array();

        foreach (new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->directory)
            ),
            $this->pattern
        ) as $path => $file) {
            $list[] = $file->getPathName();
        }

        return $list;
    }

    /**
     * Reset iterator index.
     */
    function rewind() {
        $this->position = 0;
    }

    /**
     * Return current file.
     *
     * @return string
     */
    function current() {
        $list = $this->ls();

        return $list[$this->position];
    }

    /**
     * Return current index.
     *
     * @return int
     */
    function key() {
        return $this->position;
    }

    /**
     * Increment the iterator index.
     */
    function next() {
        $this->position++;
    }

    /**
     * Check if all files have been iterated over.
     *
     * @return bool
     */
    function valid() {
        $list = $this->ls();

        return isset($list[$this->position]);
    }
}
