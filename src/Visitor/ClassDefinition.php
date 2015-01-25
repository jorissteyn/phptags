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

use PhpParser\Node;

class ClassDefinition extends DefinitionVisitor
{
    /**
     * @param Node $node
     */
    public function leaveNode(Node $node)
    {
        if ($node->getType() === 'Stmt_Class') {
            $this->processNode($node);
        }
    }

    /**
     * @param Node $node
     */
    protected function processNode(Node $node, $type = 'class')
    {
        $this->createTag($type, $node->namespacedName, $node);

        foreach ($node->stmts as $statement) {
            switch($statement->getType()) {
                case 'Stmt_ClassMethod':
                    $this->createTag(
                        'function',
                        $node->namespacedName . '::' . $statement->name,
                        $statement
                    );

                    break;

                case 'Stmt_ClassConst':
                    foreach ($statement->consts as $constant) {
                        $this->createTag(
                            'constant',
                            $node->namespacedName . '::' . $constant->name,
                            $constant
                        );
                    }

                    break;

                case 'Stmt_Property':
                    foreach ($statement->props as $property) {
                        $this->createTag(
                            'variable',
                            $node->namespacedName . '::' . $property->name,
                            $property
                        );
                    }

                    break;
            }
        }
    }
}
