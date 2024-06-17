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

namespace Constantable\OdmDocumentMaker\Tests\Util;

use Constantable\OdmDocumentMaker\Doctrine\EmbedMany;
use Constantable\OdmDocumentMaker\Doctrine\EmbedOne;
use Constantable\OdmDocumentMaker\Util\ClassSourceManipulator;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Field;
use PhpParser\Builder\Param;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class ClassSourceManipulatorTest extends TestCase
{
    /**
     * @dataProvider getAddPropertyTests
     */
    public function testAddProperty(string $sourceFilename, $propertyName, array $commentLines, $expectedSourceFilename): void
    {
        $source = file_get_contents(__DIR__.'/fixtures/source/'.$sourceFilename);
        $expectedSource = file_get_contents(__DIR__.'/fixtures/add_property/'.$expectedSourceFilename);

        $manipulator = new ClassSourceManipulator($source);
        $manipulator->addProperty(name: $propertyName, comments: $commentLines);

        $this->assertSame($expectedSource, $manipulator->getSourceCode());
    }

    public function getAddPropertyTests(): \Generator
    {
        yield 'normal_property_add' => [
            'User_simple.php',
            'fooProp',
            [],
            'User_simple.php',
        ];

        yield 'with_no_properties_and_comment' => [
            'User_no_props.php',
            'fooProp',
            [
                '@var string',
                '@internal',
            ],
            'User_no_props.php',
        ];

        yield 'no_properties_and_constants' => [
            'User_no_props_constants.php',
            'fooProp',
            [],
            'User_no_props_constants.php',
        ];

        yield 'property_empty_class' => [
            'User_empty.php',
            'fooProp',
            [],
            'User_empty.php',
        ];
    }

    /**
     * @dataProvider getAddGetterTests
     */
    public function testAddGetter(string $sourceFilename, string $propertyName, string $type, array $commentLines, $expectedSourceFilename): void
    {
        $source = file_get_contents(__DIR__.'/fixtures/source/'.$sourceFilename);
        $expectedSource = file_get_contents(__DIR__.'/fixtures/add_getter/'.$expectedSourceFilename);

        $manipulator = new ClassSourceManipulator($source);
        $manipulator->addGetter($propertyName, $type, true, $commentLines);

        $this->assertSame($expectedSource, $manipulator->getSourceCode());
    }

    public function getAddGetterTests(): \Generator
    {
        yield 'normal_getter_add' => [
            'User_simple.php',
            'fooProp',
            'string',
            [],
            'User_simple.php',
        ];

        yield 'normal_getter_add_bool' => [
            'User_simple.php',
            'fooProp',
            'bool',
            [],
            'User_simple_bool.php',
        ];

        yield 'getter_no_props_comments' => [
            'User_no_props.php',
            'fooProp',
            'string',
            [
                '@return string',
                '@internal',
            ],
            'User_no_props.php',
        ];

        yield 'getter_empty_class' => [
            'User_empty.php',
            'fooProp',
            'string',
            [],
            'User_empty.php',
        ];
    }

    /**
     * @dataProvider getAddSetterTests
     */
    public function testAddSetter(string $sourceFilename, string $propertyName, ?string $type, bool $isNullable, array $commentLines, $expectedSourceFilename): void
    {
        $source = file_get_contents(__DIR__.'/fixtures/source/'.$sourceFilename);
        $expectedSource = file_get_contents(__DIR__.'/fixtures/add_setter/'.$expectedSourceFilename);

        $manipulator = new ClassSourceManipulator($source);
        $manipulator->addSetter($propertyName, $type, $isNullable, $commentLines);

        $this->assertSame($expectedSource, $manipulator->getSourceCode());
    }

    public function getAddSetterTests(): \Generator
    {
        yield 'normal_setter_add' => [
            'User_simple.php',
            'fooProp',
            'string',
            false,
            [],
            'User_simple.php',
        ];

        yield 'setter_no_props_comments' => [
            'User_no_props.php',
            'fooProp',
            'string',
            true,
            [
                '@param string $fooProp',
                '@internal',
            ],
            'User_no_props.php',
        ];

        yield 'setter_empty_class' => [
            'User_empty.php',
            'fooProp',
            'string',
            false,
            [],
            'User_empty.php',
        ];

        yield 'setter_null_type' => [
            'User_simple.php',
            'fooProp',
            null,
            false,
            [],
            'User_simple_null_type.php',
        ];
    }

    /**
     * @dataProvider getAttributeClassTests
     */
    public function testAddAttributeToClass(string $sourceFilename, string $expectedSourceFilename, string $attributeClass, array $attributeOptions, string $attributePrefix = null): void
    {
        $source = file_get_contents(__DIR__.'/fixtures/source/'.$sourceFilename);
        $expectedSource = file_get_contents(__DIR__.'/fixtures/add_class_attribute/'.$expectedSourceFilename);
        $manipulator = new ClassSourceManipulator($source);
        $manipulator->addAttributeToClass($attributeClass, $attributeOptions, $attributePrefix);

        self::assertSame($expectedSource, $manipulator->getSourceCode());
    }

    public function getAttributeClassTests(): \Generator
    {
        yield 'Empty class' => [
            'User_empty.php',
            'User_empty.php',
            Document::class,
            [],
        ];

        yield 'Class already has attributes' => [
            'User_simple.php',
            'User_simple.php',
            Field::class,
            ['message' => 'We use this attribute for class level tests so we dont have to add additional test dependencies.'],
        ];
    }

    public function testAddInterface(): void
    {
        $source = file_get_contents(__DIR__.'/fixtures/source/User_simple.php');
        $expectedSource = file_get_contents(__DIR__.'/fixtures/implements_interface/User_simple.php');

        $manipulator = new ClassSourceManipulator($source);
        $manipulator->addInterface(UserInterface::class);

        $this->assertSame($expectedSource, $manipulator->getSourceCode());
    }

    public function testAddInterfaceToClassWithOtherInterface(): void
    {
        $source = file_get_contents(__DIR__.'/fixtures/source/User_simple_with_interface.php');
        $expectedSource = file_get_contents(__DIR__.'/fixtures/implements_interface/User_simple_with_interface.php');

        $manipulator = new ClassSourceManipulator($source);
        $manipulator->addInterface(UserInterface::class);

        $this->assertSame($expectedSource, $manipulator->getSourceCode());
    }

    public function testAddMethodBuilder(): void
    {
        $source = file_get_contents(__DIR__.'/fixtures/source/User_empty.php');
        $expectedSource = file_get_contents(__DIR__.'/fixtures/add_method/UserEmpty_with_newMethod.php');

        $manipulator = new ClassSourceManipulator($source);

        $methodBuilder = $manipulator->createMethodBuilder('testAddNewMethod', 'string', true, ['test comment on public method']);

        $manipulator->addMethodBuilder(
            $methodBuilder,
            [
                (new Param('someParam'))->setType('string')->getNode(),
            ], <<<'CODE'
                <?php
                $this->someParam = $someParam;
                CODE
        );

        $this->assertSame($expectedSource, $manipulator->getSourceCode());
    }

    public function testAddMethodWithBody(): void
    {
        $source = file_get_contents(__DIR__.'/fixtures/source/EmptyController.php');
        $expectedSource = file_get_contents(__DIR__.'/fixtures/add_method/Controller_with_action.php');

        $manipulator = new ClassSourceManipulator($source);

        $methodBuilder = $manipulator->createMethodBuilder('action', 'JsonResponse', false, ['@Route("/action", name="app_action")']);
        $methodBuilder->addParam(
            (new Param('param'))->setType('string')
        );
        $manipulator->addMethodBody($methodBuilder,
            <<<'CODE'
                <?php
                return new JsonResponse(['param' => $param]);
                CODE
        );
        $manipulator->addMethodBuilder($methodBuilder);
        $manipulator->addUseStatementIfNecessary('Symfony\\Component\\HttpFoundation\\JsonResponse');
        $manipulator->addUseStatementIfNecessary('Symfony\\Component\\Routing\\Annotation\\Route');

        $this->assertSame($expectedSource, $manipulator->getSourceCode());
    }

    public function testAddConstructor(): void
    {
        $source = file_get_contents(__DIR__.'/fixtures/source/User_empty.php');
        $expectedSource = file_get_contents(__DIR__.'/fixtures/add_constructor/UserEmpty_with_constructor.php');

        $manipulator = new ClassSourceManipulator($source);

        $manipulator->addConstructor([
                (new Param('someObjectParam'))->setType('object')->getNode(),
                (new Param('someStringParam'))->setType('string')->getNode(),
                ], <<<'CODE'
                    <?php
                    $this->someObjectParam = $someObjectParam;
                    $this->someMethod($someStringParam);
                    CODE
        );

        $this->assertSame($expectedSource, $manipulator->getSourceCode());
    }

    public function testAddConstructorInClassContainsPropsAndMethods(): void
    {
        $source = file_get_contents(__DIR__.'/fixtures/source/User_simple.php');
        $expectedSource = file_get_contents(__DIR__.'/fixtures/add_constructor/UserSimple_with_constructor.php');

        $manipulator = new ClassSourceManipulator($source);

        $manipulator->addConstructor([
            (new Param('someObjectParam'))->setType('object')->getNode(),
            (new Param('someStringParam'))->setType('string')->getNode(),
        ], <<<'CODE'
            <?php
            $this->someObjectParam = $someObjectParam;
            $this->someMethod($someStringParam);
            CODE
        );

        $this->assertSame($expectedSource, $manipulator->getSourceCode());
    }

    public function testAddConstructorInClassContainsOnlyConstants(): void
    {
        $source = file_get_contents(__DIR__.'/fixtures/source/User_with_const.php');
        $expectedSource = file_get_contents(__DIR__.'/fixtures/add_constructor/User_with_constructor_constante.php');

        $manipulator = new ClassSourceManipulator($source);

        $manipulator->addConstructor([
            (new Param('someObjectParam'))->setType('object')->getNode(),
            (new Param('someStringParam'))->setType('string')->getNode(),
        ], <<<'CODE'
            <?php
            $this->someObjectParam = $someObjectParam;
            $this->someMethod($someStringParam);
            CODE
        );

        $this->assertSame($expectedSource, $manipulator->getSourceCode());
    }

    public function testAddConstructorInClassContainsConstructor(): void
    {
        $source = file_get_contents(__DIR__.'/fixtures/source/User_with_constructor.php');

        $manipulator = new ClassSourceManipulator($source);

        $this->expectException('LogicException');
        $this->expectExceptionMessage('Constructor already exists');

        $manipulator->addConstructor([
            (new Param('someObjectParam'))->setType('object')->getNode(),
            (new Param('someStringParam'))->setType('string')->getNode(),
        ], <<<'CODE'
            <?php
            $this->someObjectParam = $someObjectParam;
            $this->someMethod($someStringParam);
            CODE
        );
    }

    /**
     * @dataProvider getAddEmbedOneTests
     */
    public function testAddEmbedOne(string $sourceFilename, string $expectedSourceFilename, EmbedOne $embedOne): void
    {
        $sourcePath = __DIR__.'/fixtures/source';
        $expectedPath = __DIR__.'/fixtures/add_embed_one';

        $this->runAddEmbedOneTests(
            file_get_contents(sprintf('%s/%s', $sourcePath, $sourceFilename)),
            file_get_contents(sprintf('%s/%s', $expectedPath, $expectedSourceFilename)),
            $embedOne
        );
    }

    private function runAddEmbedOneTests(string $source, string $expected, EmbedOne $embedOne): void
    {
        $manipulator = new ClassSourceManipulator($source, false);
        $manipulator->addEmbedOne($embedOne);

        $this->assertSame($expected, $manipulator->getSourceCode());
    }

    public function getAddEmbedOneTests(): \Generator
    {
        yield 'embed_one_simple' => [
            'Document_User_simple.php',
            'Document_User_simple.php',
            new EmbedOne(
                propertyName: 'avatarPhoto',
                targetClassName: \App\Document\UserAvatarPhoto::class,
                isNullable: true,
            ),
        ];
    }

    /**
     * @dataProvider getAddEmbedManyTests
     */
    public function testAddEmbedMany(string $sourceFilename, string $expectedSourceFilename, EmbedMany $embedMany): void
    {
        $sourcePath = __DIR__.'/fixtures/source';
        $expectedPath = __DIR__.'/fixtures/add_embed_many';

        $this->runAddEmbedManyTests(
            file_get_contents(sprintf('%s/%s', $sourcePath, $sourceFilename)),
            file_get_contents(sprintf('%s/%s', $expectedPath, $expectedSourceFilename)),
            $embedMany
        );
    }

    private function runAddEmbedManyTests(string $source, string $expected, EmbedMany $embedMany): void
    {
        $manipulator = new ClassSourceManipulator($source, false);
        $manipulator->addEmbedMany($embedMany);

        $this->assertSame($expected, $manipulator->getSourceCode());
    }

    public function getAddEmbedManyTests(): \Generator
    {
        yield 'embed_many_simple' => [
            'Document_User_simple.php',
            'Document_User_simple.php',
            new EmbedMany(
                propertyName: 'avatarPhotos',
                targetClassName: \App\Document\UserAvatarPhoto::class,
                isNullable: true,
            ),
        ];
    }
}
