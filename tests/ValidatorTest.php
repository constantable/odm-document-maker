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

namespace Constantable\OdmDocumentMaker\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\MakerBundle\Exception\RuntimeCommandException;
use Constantable\OdmDocumentMaker\Validator;

class ValidatorTest extends TestCase
{
    public function testValidateLength()
    {
        $this->assertSame(100, Validator::validateLength('100'));
        $this->assertSame(99, Validator::validateLength(99));
    }

    public function testInvalidLength()
    {
        $this->expectException(RuntimeCommandException::class);
        $this->expectExceptionMessage('Invalid length "-100".');

        Validator::validateLength(-100);
    }

    public function testValidatePrecision()
    {
        $this->assertSame(15, Validator::validatePrecision('15'));
        $this->assertSame(21, Validator::validatePrecision(21));
    }

    public function testInvalidPrecision()
    {
        $this->expectException(RuntimeCommandException::class);
        $this->expectExceptionMessage('Invalid precision "66".');

        Validator::validatePrecision(66);
    }

    public function testValidateScale()
    {
        $this->assertSame(2, Validator::validateScale('2'));
        $this->assertSame(5, Validator::validateScale(5));
    }

    public function testInvalidScale()
    {
        $this->expectException(RuntimeCommandException::class);
        $this->expectExceptionMessage('Invalid scale "31".');

        Validator::validateScale(31);
    }

    public function testValidateClassName()
    {
        $this->assertSame('\App\Service\Foo', Validator::validateClassName('\App\Service\Foo'));
        $this->assertSame('Foo', Validator::validateClassName('Foo'));
    }

    public function testInvalidClassName()
    {
        $this->expectException(RuntimeCommandException::class);
        $this->expectExceptionMessage('"Class" is a reserved keyword and thus cannot be used as class name in PHP.');
        Validator::validateClassName('App\Entity\Class');
    }

    public function testInvalidEncodingInClassName()
    {
        $this->expectException(RuntimeCommandException::class);
        $this->expectExceptionMessage(sprintf('"%sController" is not a UTF-8-encoded string.', \chr(0xA6)));
        Validator::validateClassName(mb_convert_encoding('ŚController', 'ISO-8859-2', 'UTF-8'));
    }

}
