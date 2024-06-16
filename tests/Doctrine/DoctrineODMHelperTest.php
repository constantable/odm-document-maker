<?php

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
