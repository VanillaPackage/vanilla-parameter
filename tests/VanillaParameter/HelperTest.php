<?php

namespace Rentalhost\VanillaParameter;

use stdClass;
use Rentalhost\VanillaParameter\Test;
use Rentalhost\VanillaParameter\Test\A as ABase;
use PHPUnit_Framework_TestCase;

/**
 * Class HelperTest
 * @package Rentalhost\VanillaParameter
 */
class HelperTest extends PHPUnit_Framework_TestCase
{
    /**
     * Check if value is some kind of dependency.
     * @covers Rentalhost\VanillaParameter\Helper::isDependency
     */
    public function testIsDependency()
    {
        static::assertTrue(Helper::isDependency(Test\A::class));
        static::assertTrue(Helper::isDependency(Test\AInterface::class));
        static::assertTrue(Helper::isDependency(ABase::class));
        static::assertTrue(Helper::isDependency('stdClass'));
        static::assertTrue(Helper::isDependency("\\stdClass"));

        static::assertFalse(Helper::isDependency(function () {
        }));

        static::assertTrue(Helper::isDependency(new stdClass));
        static::assertTrue(Helper::isDependency(new Test\CallableClass));

        static::assertFalse(Helper::isDependency('UnknowClass'));
        static::assertFalse(Helper::isDependency(123));
    }

    /**
     * Test normalize method.
     *
     * @param string $type                  Type to normalize.
     * @param string $expectedNormalization Expected normalized type.
     *
     * @covers       Rentalhost\VanillaParameter\Helper::normalizeType
     * @dataProvider dataNormalize
     */
    public function testNormalize($type, $expectedNormalization)
    {
        static::assertSame($expectedNormalization, Helper::normalizeType($type));
    }

    /**
     * @return array
     */
    public function dataNormalize()
    {
        return [
            // Real names.
            1000 =>
                [ 'string', 'string' ],
            [ 'integer', 'integer' ],
            [ 'float', 'float' ],
            [ 'resource', 'resource' ],
            [ 'object', 'object' ],
            [ 'array', 'array' ],
            [ 'mixed', 'mixed' ],
            [ 'callable', 'callable' ],
            // Aliases.
            2000 =>
                [ 'bool', 'boolean' ],
            [ 'int', 'integer' ],
            [ 'double', 'float' ],
            [ 'any', 'mixed' ],
            [ '*', 'mixed' ],
            // Case-insensitive.
            3000 =>
                [ 'STRING', 'string' ],
            [ 'ANY', 'mixed' ],
            // Invalid.
            4000 =>
                [ 'invalid', null ],
        ];
    }

    /**
     * Test normalize types method.
     *
     * @param string[]|string $types                 Types to normalize.
     * @param string[]        $expectedNormalization Expected normalized types.
     *
     * @covers       Rentalhost\VanillaParameter\Helper::normalizeTypes
     * @dataProvider dataNormalizeArray
     */
    public function testNormalizeArray($types, $expectedNormalization)
    {
        static::assertSame($expectedNormalization, Helper::normalizeTypes($types));
    }

    /**
     * @return array
     */
    public function dataNormalizeArray()
    {
        return [
            // Simple.
            [ 'string', [ 'string' ] ],
            [ [ 'string' ], [ 'string' ] ],
            [ [ 'string', 'int' ], [ 'string', 'integer' ] ],
            // Class.
            [ [ 'string', 'int', Test\A::class ], [ 'string', 'integer', Test\A::class ] ],
            [ [ 'string', 'int', Test\B::class ], [ 'string', 'integer', Test\B::class ] ],
            // Interface.
            [ [ Test\BInterface::class ], [ Test\BInterface::class ] ],
            // Undefined class.
            [ [ 'SomeUndefinedClass' ], [ 'SomeUndefinedClass' ] ],
        ];
    }

    /**
     * Test normalize value method.
     *
     * @param mixed  $value                 Value to normalize.
     * @param string $expectedNormalization Expected normalized value type.
     *
     * @covers       Rentalhost\VanillaParameter\Helper::normalizeValue
     * @dataProvider dataNormalizeValue
     */
    public function testNormalizeValue($value, $expectedNormalization)
    {
        static::assertSame($expectedNormalization, Helper::normalizeValue($value));
    }

    /**
     * @return array
     */
    public function dataNormalizeValue()
    {
        $resource = curl_init();
        $stdclass = new stdClass;
        $callable = function () {
        };

        return [
            // Simple.
            [ 'string', 'string' ],
            [ 123, 'integer' ],
            [ 1.23, 'float' ],
            [ $resource, 'resource' ],
            [ [ ], 'array' ],
            // Class.
            [ $stdclass, 'stdClass' ],
            // Callable.
            [ 'max', 'callable' ],
            [ $callable, 'callable' ],
        ];
    }
}
