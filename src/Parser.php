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

use PhpParser\Parser as PhpParser;
use PhpParser\Lexer\Emulative as Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;

class Parser
{
    /**
     * Parse a file and return found tags.
     *
     * @param string $filePath Full path to filename.
     * @return array
     */
    public function getTags($filePath)
    {
        // Create a PHP-Parser instance.
        $parser = new PhpParser(
            new Lexer(
                array(
                    'usedAttributes' => array(
                        'startLine',
                        'endLine',
                        'startFilePos',
                        'endFilePos',
                    )
                )
            )
        );

        // Parse the source code into a list of statements.
        $source = $this->getContents($filePath);
        $statements = $parser->parse($source);

        $traverser = new NodeTraverser;

        // Make sure all names are resolved as fully qualified.
        $traverser->addVisitor(new NameResolver);

        // Create visitors that turn statements into tags.
        $visitors = array(
            new Visitor\ClassDefinition,
            new Visitor\ClassReference,
            new Visitor\ConstantDefinition,
            new Visitor\FunctionDefinition,
            new Visitor\GlobalVariableDefinition,
            new Visitor\InterfaceDefinition,
            new Visitor\TraitDefinition,
        );

        foreach ($visitors as $visitor) {
            $traverser->addVisitor($visitor);
        }

        $traverser->traverse($statements);

        // Extract tags from the visitors.
        $tags = array();
        foreach ($visitors as $visitor) {
            $tags = array_merge(
                $tags,
                array_map(
                    function(Tag $tag) use ($source) {
                        $comment = substr(
                            $source,
                            $tag->getStartFilePos(),
                            $tag->getEndFilePos() - $tag->getStartFilePos() + 1
                        );

                        if (($bracePos = strpos($comment, '{')) !== false) {
                            $comment = substr($comment, 0, $bracePos) . ' {}';
                        }

                        return $tag->setComment(
                            preg_replace('/\s+/', ' ', $comment)
                        );
                    },
                    $visitor->getTags()
                )
            );
        }

        return $tags;
    }

    /**
     * Read the contents of a file.
     *
     * @param string $filePath Full path to filename.
     * @return string
     * @throws \Exception
     */
    protected function getContents($filePath)
    {
        $source = file_get_contents($filePath);
        if ($source === false) {
            throw new \Exception(
                "Error reading the contents of '{$filePath}'"
            );
        }

        return $source;
    }
}
