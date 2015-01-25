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
namespace PhpTags\Visitor;

use PhpTags\Tag;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

abstract class DefinitionVisitor extends NodeVisitorAbstract
{
    /**
     * @param array $tags
     */
    public $tags = array();

    /**
     * Create a new tag and add it to the tags list.
     *
     * @param string $type
     * @param string $name
     * @return Tag
     */
    public function createTag($type, $name, Node $node)
    {
        return $this->tags[] = new Tag(
            $name,
            $type,
            Tag::DEFINITION,
            $node->getAttribute('startLine'),
            $node->getAttribute('endLine'),
            $node->getAttribute('startFilePos'),
            $node->getAttribute('endFilePos'),
            $node->getDocComment()
        );
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }
}
