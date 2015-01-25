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

class ExtensionTags
{
    /**
     * @param array $extensions
     */
    public $extensions = array();

    /**
     * @param array $extensions
     */
    public function __construct(array $extensions = array())
    {
        if (!$extensions) {
            $extensions = \get_loaded_extensions();
        }

        $this->extensions = $extensions;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        $tags = array();

        foreach ($this->extensions as $extension) {
            $tags[$extension] = $this->getTagsForExtension($extension);
        }

        return $tags;
    }

    /**
     * @return array
     */
    protected function getTagsForExtension($name)
    {
        if (!extension_loaded($name)) {
            return array();
        }

        $tags = array();
        $module = new \ReflectionExtension($name);

        // Export constants.
        foreach ($module->getConstants() as $name => $value) {
            $tags[] = new Tag(
                $name, 'constant', Tag::DEFINITION
            );
        }

        // Export functions.
        foreach ($module->getFunctions() as $function) {
            $tags[] = new Tag(
                $function->getName(),
                'function',
                TAG::DEFINITION
            );
        }

        // Export classes.
        foreach ($module->getClasses() as $class) {
            $tags[] = new Tag(
                $class->getName(),
                'class',
                TAG::DEFINITION

            );

            foreach ($class->getMethods() as $method) {
                $tags[] = new Tag(
                    sprintf(
                        '%s::%s',
                        $class->getName(),
                        $method->getName()
                    ),
                    'function',
                    TAG::DEFINITION
                );
            }

            foreach ($class->getProperties() as $property) {
                $tags[] = new Tag(
                    sprintf(
                        '%s::%s',
                        $class->getName(),
                        $property->getName()
                    ),
                    'variable',
                    TAG::DEFINITION
                );
            }

            foreach ($class->getConstants() as $constant => $value) {
                $tags[] = new Tag(
                    sprintf(
                        '%s::%s',
                        $class->getName(),
                        $constant
                    ),
                    'constant',
                    TAG::DEFINITION
                );
            }
        }

        return $tags;
    }
}
