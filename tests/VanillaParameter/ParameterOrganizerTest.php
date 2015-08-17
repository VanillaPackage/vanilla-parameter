<?php

namespace Rentalhost\VanillaParameter;

use stdClass;
use ReflectionMethod;
use Rentalhost\VanillaParameter\Test;
use PHPUnit_Framework_TestCase;

class ParameterOrganizerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test basic methods.
     * @covers Rentalhost\VanillaParameter\Parameter::__construct
     * @covers Rentalhost\VanillaParameter\Parameter::organize
     * @covers Rentalhost\VanillaParameter\ParameterOrganizer::__construct
     * @covers Rentalhost\VanillaParameter\ParameterOrganizer::add
     * @covers Rentalhost\VanillaParameter\ParameterOrganizer::expects
     * @covers Rentalhost\VanillaParameter\ParameterOrganizer::validate
     * @covers Rentalhost\VanillaParameter\ParameterOrganizer::defaultValue
     */
    public function testBasic()
    {
        $this->assertTrue(Parameter::organize([])->validate());

        // Simple validation.
        $this->assertTrue(Parameter::organize([ 1 ])->validate());
        $this->assertTrue(Parameter::organize([ 1, 2, 3 ])->validate());
        $this->assertTrue(Parameter::organize([ "a" ])->validate());
        $this->assertTrue(Parameter::organize([ "a", "b", "c" ])->validate());
        $this->assertTrue(Parameter::organize([ true ])->validate());
        $this->assertTrue(Parameter::organize([ false ])->validate());
        $this->assertTrue(Parameter::organize([ 1, "b", false ])->validate());

        // Expects validation.
        $this->assertTrue(Parameter::organize([ 1, 2, 3 ])->expects(2)->validate());
        $this->assertTrue(Parameter::organize([ "a", "b", "c" ])->expects(2)->validate());
        $this->assertTrue(Parameter::organize([ 1, "b", false ])->expects(2)->validate());
        $this->assertTrue(Parameter::organize([ 1, 0 ])->expects(2)->validate());
        $this->assertFalse(Parameter::organize([ 1 ])->expects(2)->validate());
        $this->assertFalse(Parameter::organize([ "a" ])->expects(2)->validate());
        $this->assertFalse(Parameter::organize([ true ])->expects(2)->validate());
        $this->assertFalse(Parameter::organize([ false ])->expects(2)->validate());

        // No parameters validation.
        $organizer = Parameter::organize([])
            ->add($parameter1)
            ->add($parameter2)
            ->add($parameter3);

        $this->assertNull($parameter1);
        $this->assertNull($parameter2);
        $this->assertNull($parameter3);
        $this->assertTrue($organizer->validate());

        unset($parameter1, $parameter2, $parameter3);

        // Single parameter validation.
        $organizer = Parameter::organize([ false ])
            ->add($parameter1, null, true);

        $this->assertSame(false, $parameter1);
        $this->assertTrue($organizer->validate());

        unset($parameter1);

        // Required parameters validation.
        $organizer = Parameter::organize([ 1, "a", false ])
            ->add($parameter1, null, true)
            ->add($parameter2, null, true)
            ->add($parameter3, null, true);

        $this->assertSame(1, $parameter1);
        $this->assertSame("a", $parameter2);
        $this->assertSame(false, $parameter3);
        $this->assertTrue($organizer->validate());

        unset($parameter1, $parameter2, $parameter3);

        // Required parameters validation (fail).
        $organizer = Parameter::organize([ 1, "a" ])
            ->add($parameter1, null, true)
            ->add($parameter2, null, true)
            ->add($parameter3, null, true);

        $this->assertSame(1, $parameter1);
        $this->assertSame("a", $parameter2);
        $this->assertSame(null, $parameter3);
        $this->assertFalse($organizer->validate());

        unset($parameter1, $parameter2, $parameter3);

        // Default value set (even if required fails).
        $organizer = Parameter::organize([ false, false ])
            ->add($parameter1, null, true)
            ->add($parameter2, null, true)->defaultValue(true)
            ->add($parameter3, null, true)->defaultValue(true);

        $this->assertSame(false, $parameter1);
        $this->assertSame(false, $parameter2);
        $this->assertSame(true, $parameter3);
        $this->assertFalse($organizer->validate());

        unset($parameter1, $parameter2, $parameter3);

        // Organize to second parameter.
        $organizer = Parameter::organize([ "abc" ])
            ->add($parameter1, "int")->defaultValue(123)
            ->add($parameter2)->defaultValue("def");

        $this->assertSame(123, $parameter1);
        $this->assertSame("abc", $parameter2);
        $this->assertTrue($organizer->validate());

        unset($parameter1, $parameter2);

        // Organize, even if required fails.
        $organizer = Parameter::organize([ "abc" ])
            ->add($parameter1, "int", true)->defaultValue(123)
            ->add($parameter2)->defaultValue("def");

        $this->assertSame(123, $parameter1);
        $this->assertSame("abc", $parameter2);
        $this->assertFalse($organizer->validate());

        unset($parameter1, $parameter2);

        // Class tests.
        $instance1 = new stdClass;
        $organizer = Parameter::organize([ $instance1 ])
            ->add($parameter1, stdClass::class, true);

        $this->assertSame($instance1, $parameter1);
        $this->assertTrue($organizer->validate());

        unset($parameter1);

        // Class tests.
        $instance1 = new stdClass;
        $organizer = Parameter::organize([ $instance1 ])
            ->add($parameter1, "int")
            ->add($parameter2, stdClass::class, true);

        $this->assertNull($parameter1);
        $this->assertSame($instance1, $parameter2);
        $this->assertTrue($organizer->validate());

        unset($parameter1, $parameter2);

        // Instance of.
        $instance1 = new Test\B;
        $organizer = Parameter::organize([ $instance1 ])
            ->add($parameter1, Test\A::class)
            ->add($parameter2, Test\A::class, true);

        $this->assertSame($instance1, $parameter1);
        $this->assertNull($parameter2);
        $this->assertFalse($organizer->validate());

        unset($parameter1, $parameter2);

        // Instance of (should fail).
        $organizer = Parameter::organize([ new Test\B ])
            ->add($parameter1, Test\C::class);

        $this->assertNull($parameter1);
        $this->assertTrue($organizer->validate());

        unset($parameter1);
    }

    /**
     * Test someExpectedTypes method.
     * @covers Rentalhost\VanillaParameter\ParameterOrganizer::someExpectedTypes
     * @dataProvider dataSomeExpectedTypes
     */
    public function testSomeExpectedTypes($value, $expectedTypes, $expectedResult = true)
    {
        $organizer = new ParameterOrganizer([]);

        $reflection = new ReflectionMethod($organizer, "someExpectedTypes");
        $reflection->setAccessible(true);

        $this->assertSame($expectedResult, $reflection->invoke($organizer, $value, $expectedTypes));
    }

    public function dataSomeExpectedTypes()
    {
        $resource = mysql_connect();
        $callable = function () {};

        return [
            // Mixed.
            1000 =>
            [ "hello", [] ],
            [ "hello", [ "mixed" ] ],
            [ 123,     [ "mixed" ] ],

            // Specified type.
            2000 =>
            [ "string",  [ "string" ] ],
            [ 123,       [ "integer" ] ],
            [ 1.23,      [ "float" ] ],
            [ $resource, [ "resource" ] ],
            [ [],        [ "array" ] ],

            // Max is both a string or a callable.
            // But callable is not needly a string.
            // 3000 =>
            [ "max", [ "string" ] ],
            [ "max", [ "callable" ] ],
            [ $callable, [ "string" ], false ],
            [ $callable, [ "callable" ] ],

            // stdClass is both string or a class.
            4000 =>
            [ stdClass::class, [ "object" ] ],
            [ stdClass::class, [ "string" ] ],
            [ new stdClass, [ stdClass::class ] ],

            // Class types.
            5000 =>
            [ new Test\CallableClass, [ Test\CallableClass::class ] ],
            [ new Test\A,  [ Test\A::class ] ],
            [ new Test\C,  [ Test\BInterface::class ] ],
            [ new Test\D,  [ Test\A::class ] ],

            // Class type (not).
            6000 =>
            [ new Test\A,  [ Test\B::class ], false ],
        ];
    }
}
