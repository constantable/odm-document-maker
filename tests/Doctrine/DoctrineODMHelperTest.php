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

namespace Constantable\OdmDocumentMaker\Tests\Doctrine;

use Doctrine\ODM\MongoDB\Types\Type;
use PHPUnit\Framework\TestCase;
use Constantable\OdmDocumentMaker\Doctrine\DoctrineODMHelper;

class DoctrineODMHelperTest extends TestCase
{
    /**
     * @dataProvider getTypeConstantTests
     */
    public function testGetTypeConstant(string $columnType, ?string $expectedConstant)
    {
        $this->assertSame($expectedConstant, DoctrineODMHelper::getTypeConstant($columnType));
    }

    public function getTypeConstantTests(): \Generator
    {
        yield 'unknown_type' => ['foo', null];
        yield 'string' => ['string', 'Type::STRING'];
        yield 'date_immutable' => ['date_immutable', 'Type::DATE_IMMUTABLE'];
    }

    /**
     * @dataProvider getCanColumnTypeBeInferredTests
     */
    public function testCanColumnTypeBeInferredByPropertyType(string $columnType, string $propertyType, bool $expected)
    {
        $this->assertSame($expected, DoctrineODMHelper::canColumnTypeBeInferredByPropertyType($columnType, $propertyType));
    }

    public function getCanColumnTypeBeInferredTests(): \Generator
    {
        yield 'non_matching' => [Type::RAW, 'string', false];
        yield 'yes_matching' => [Type::STRING, 'string', true];
    }
}
