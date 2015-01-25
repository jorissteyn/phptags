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

class Tag
{
    /**
     * Usage type: definition.
     *
     * @const string
     */
    const DEFINITION = 'definition';

    /**
     * Usage type: reference.
     *
     * @const string
     */
    const REFERENCE  = 'reference';

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var string $tagType
     */
    protected $tagType;

    /**
     * @var string $usageType
     */
    protected $usageType;

    /**
     * @var int $startLine
     */
    protected $startLine;

    /**
     * @var int $endLine
     */
    protected $endLine;

    /**
     * @var int $startFilePos
     */
    protected $startFilePos;

    /**
     * @var int $endFilePos
     */
    protected $endFilePos;

    /**
     * @var string $comment
     */
    protected $comment;

    /**
     * @param string $name
     * @param string $tagType
     * @param string $usageType
     * @param int    $startLine
     * @param int    $endLine
     * @param int    $startFilePos
     * @param int    $endFilePos
     * @param string $comment
     */
    public function __construct($name, $tagType, $usageType, $startLine = 0, $endLine = 0, $startFilePos = 0, $endFilePos = 0, $comment = '')
    {
        $this->name         = (string)$name;
        $this->tagType      = (string)$tagType;
        $this->usageType    = (string)$usageType;
        $this->startLine    = (int)$startLine;
        $this->endLine      = (int)$endLine;
        $this->startFilePos = (int)$startFilePos;
        $this->endFilePos   = (int)$endFilePos;
        $this->comment      = (string)$comment;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTagType()
    {
        return $this->tagType;
    }

    /**
     * @return string
     */
    public function getUsageType()
    {
        return $this->usageType;
    }

    /**
     * @return int
     */
    public function getStartLine()
    {
        return $this->startLine;
    }

    /**
     * @return int
     */
    public function getEndLine()
    {
        return $this->endLine;
    }

    /**
     * @return int
     */
    public function getStartFilePos()
    {
        return $this->startFilePos;
    }

    /**
     * @return int
     */
    public function getEndFilePos()
    {
        return $this->endFilePos;
    }

    /**
     * @param string $comment
     * @return Tag
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }
}
