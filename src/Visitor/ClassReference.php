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
use PhpParser\Node\Name\FullyQualified as FullyQualifiedName;

class ClassReference extends ReferenceVisitor
{
    /**
     * @param Node $node
     */
    public function leaveNode(Node $node)
    {
        switch ($node->getType()) {
            case 'Expr_New':
            case 'Expr_Instanceof':
            case 'Expr_StaticCall':
            case 'Expr_StaticPropertyFetch':
            case 'Expr_ClassConstFetch':
                $this->processExpr($node);

                break;
            case 'Stmt_Class':
                $this->processStmt($node);

                break;
        }
    }

    /**
     * @param Node $node
     */
    protected function processExpr(Node $node)
    {
        // $node->class can be a variable expression.
        if ($node->class instanceof FullyQualifiedName) {
            $this->createTag('class', (string)$node->class, $node);
        }
    }

    /**
     * @param Node $node
     */
    protected function processStmt(Node $node)
    {
        // $node->class can be a variable expression.
        if ($node->extends instanceof FullyQualifiedName) {
            $this->createTag('class', (string)$node->extends, $node);
        }
    }
}
