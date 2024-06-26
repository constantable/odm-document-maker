<?php

/*
 * Copyright (c) 2004-2020 Fabien Potencier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Constantable\OdmDocumentMaker;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\MakerBundle\Exception\RuntimeCommandException;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @internal
 */
final class Validator
{
    public static function validateClassName(string $className, string $errorMessage = ''): string
    {
        // remove potential opening slash, so we don't match on it
        $pieces = explode('\\', ltrim($className, '\\'));
        $shortClassName = Str::getShortClassName($className);

        $reservedKeywords = ['__halt_compiler', 'abstract', 'and', 'array',
            'as', 'break', 'callable', 'case', 'catch', 'class',
            'clone', 'const', 'continue', 'declare', 'default', 'die', 'do',
            'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor',
            'endforeach', 'endif', 'endswitch', 'endwhile', 'eval',
            'exit', 'extends', 'final', 'finally', 'fn', 'for', 'foreach', 'function',
            'global', 'goto', 'if', 'implements', 'include',
            'include_once', 'instanceof', 'insteadof', 'interface', 'isset',
            'list', 'match', 'namespace', 'new', 'or', 'print', 'private',
            'protected', 'public', 'readonly', 'require', 'require_once', 'return',
            'static', 'switch', 'throw', 'trait', 'try', 'unset',
            'use', 'var', 'while', 'xor', 'yield',
            'int', 'float', 'bool', 'string', 'true', 'false', 'null', 'void',
            'iterable', 'object', '__file__', '__line__', '__dir__', '__function__', '__class__',
            '__method__', '__namespace__', '__trait__', 'self', 'parent', 'collection',
        ];

        foreach ($pieces as $piece) {
            if (!mb_check_encoding($piece, 'UTF-8')) {
                $errorMessage = $errorMessage ?: sprintf('"%s" is not a UTF-8-encoded string.', $piece);

                throw new RuntimeCommandException($errorMessage);
            }

            if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $piece)) {
                $errorMessage = $errorMessage ?: sprintf('"%s" is not valid as a PHP class name (it must start with a letter or underscore, followed by any number of letters, numbers, or underscores)', $className);

                throw new RuntimeCommandException($errorMessage);
            }

            if (\in_array(strtolower($shortClassName), $reservedKeywords, true)) {
                throw new RuntimeCommandException(sprintf('"%s" is a reserved keyword and thus cannot be used as class name in PHP.', $shortClassName));
            }
        }

        // return original class name
        return $className;
    }

    public static function notBlank(?string $value = null): string
    {
        if (null === $value || '' === $value) {
            throw new RuntimeCommandException('This value cannot be blank.');
        }

        return $value;
    }

    public static function validateLength($length)
    {
        if (!$length) {
            return $length;
        }

        $result = filter_var($length, \FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        if (false === $result) {
            throw new RuntimeCommandException(sprintf('Invalid length "%s".', $length));
        }

        return $result;
    }

    public static function validatePrecision($precision)
    {
        if (!$precision) {
            return $precision;
        }

        $result = filter_var($precision, \FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 65],
        ]);

        if (false === $result) {
            throw new RuntimeCommandException(sprintf('Invalid precision "%s".', $precision));
        }

        return $result;
    }

    public static function validateScale($scale)
    {
        if (!$scale) {
            return $scale;
        }

        $result = filter_var($scale, \FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => 30],
        ]);

        if (false === $result) {
            throw new RuntimeCommandException(sprintf('Invalid scale "%s".', $scale));
        }

        return $result;
    }

    public static function validateBoolean($value)
    {
        if ('yes' == $value) {
            return true;
        }

        if ('no' == $value) {
            return false;
        }

        if (null === $valueAsBool = filter_var($value, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE)) {
            throw new RuntimeCommandException(sprintf('Invalid bool value "%s".', $value));
        }

        return $valueAsBool;
    }

    public static function validatePropertyName(string $name): string
    {
        // check for valid PHP variable name
        if (!Str::isValidPhpVariableName($name)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid PHP property name.', $name));
        }

        return $name;
    }

    public static function validateDoctrineFieldName(string $name, ManagerRegistry $registry): string
    {
        self::validatePropertyName($name);

        return $name;
    }

    public static function classExists(string $className, string $errorMessage = ''): string
    {
        self::notBlank($className);

        if (!class_exists($className)) {
            $errorMessage = $errorMessage ?: sprintf('Class "%s" doesn\'t exist; please enter an existing full class name.', $className);

            throw new RuntimeCommandException($errorMessage);
        }

        return $className;
    }

    public static function classDoesNotExist($className): string
    {
        self::notBlank($className);

        if (class_exists($className)) {
            throw new RuntimeCommandException(sprintf('Class "%s" already exists.', $className));
        }

        return $className;
    }
}
