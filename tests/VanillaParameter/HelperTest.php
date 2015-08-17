<?php

namespace Rentalhost\VanillaParameter;

use stdClass;
use Rentalhost\VanillaParameter\Test;
use Rentalhost\VanillaParameter\Test\A as ABase;
use PHPUnit_Framework_TestCase;

class HelperTest extends PHPUnit_Framework_TestCase
{
    /**
     * Check if value is some kind of dependency.
     * @covers Rentalhost\VanillaParameter\Helper::isDependency
     */
    public function testIsDependency()
    {
        $this->assertTrue(Helper::isDependency(Test\A::class));
        $this->assertTrue(Helper::isDependency(Test\AInterface::class));
        $this->assertTrue(Helper::isDependency(ABase::class));
        $this->assertTrue(Helper::isDependency("stdClass"));
        $this->assertTrue(Helper::isDependency("\\stdClass"));

        $this->assertFalse(Helper::isDependency(function () { }));

        $this->assertTrue(Helper::isDependency(new stdClass));
        $this->assertTrue(Helper::isDependency(new Test\CallableClass));

        $this->assertFalse(Helper::isDependency("UnknowClass"));
        $this->assertFalse(Helper::isDependency(123));
    }

    /**
     * Test normalize method.
     * @covers Rentalhost\VanillaParameter\Helper::normalizeType
     * @dataProvider dataNormalize
     */
    public function testNormalize($type, $expectedNormalization)
    {
        $this->assertSame($expectedNormalization, Helper::normalizeType($type));
    }

    public function dataNormalize()
    {
        return [
            // Real names.
            1000 =>
            [ "string",     "string" ],
            [ "integer",    "integer" ],
            [ "float",      "float" ],
            [ "resource",   "resource" ],
            [ "object",     "object" ],
            [ "array",      "array" ],
            [ "mixed",      "mixed" ],
            [ "callable",   "callable" ],

            // Aliases.
            2000 =>
            [ "bool",       "boolean" ],
            [ "int",        "integer" ],
            [ "double",     "float" ],
            [ "any",        "mixed" ],
            [ "*",          "mixed" ],

            // Case-insensitive.
            3000 =>
            [ "STRING",     "string" ],
            [ "ANY",        "mixed" ],

            // Invalid.
            4000 =>
            [ "invalid",    null ],
        ];
    }

    /**
     * Test normalize types method.
     * @covers Rentalhost\VanillaParameter\Helper::normalizeTypes
     * @dataProvider dataNormalizeArray
     */
    public function testNormalizeArray($type, $expectedNormalization)
    {
        $this->assertSame($expectedNormalization, Helper::normalizeTypes($type));
    }

    public function dataNormalizeArray()
    {
        return [
            // Simple.
            [ "string", [ "string" ] ],
            [ [ "string" ], [ "string" ] ],
            [ [ "string", "int" ], [ "string", "integer" ] ],

            // Class.
            [ [ "string", "int", Test\A::class ], [ "string", "integer", Test\A::class ] ],
            [ [ "string", "int", Test\B::class ], [ "string", "integer", Test\B::class ] ],

            // Interface.
            [ [ Test\BInterface::class ], [ Test\BInterface::class ] ],

            // Undefined class.
            [ [ "SomeUndefinedClass" ], [ "SomeUndefinedClass" ] ],
        ];
    }

    /**
     * Test normalize value method.
     * @covers Rentalhost\VanillaParameter\Helper::normalizeValue
     * @dataProvider dataNormalizeValue
     */
    public function testNormalizeValue($value, $expectedNormalization)
    {
        $this->assertSame($expectedNormalization, Helper::normalizeValue($value));
    }

    public function dataNormalizeValue()
    {
        $resource = mysql_connect();
        $stdclass = new stdclass;
        $callable = function () {};

        return [
            // Simple.
            [ "string",  "string" ],
            [ 123,       "integer" ],
            [ 1.23,      "float" ],
            [ $resource, "resource" ],
            [ [],        "array" ],

            // Class.
            [ $stdclass, "stdClass" ],

            // Callable.
            [ "max",     "callable" ],
            [ $callable, "callable" ],
        ];
    }
}
