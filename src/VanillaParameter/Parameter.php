<?php

namespace Rentalhost\VanillaParameter;

use Closure;

class Parameter
{
    /**
     * Protect constructor.
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Construct a new parameter organizer.
     * @param  array  $parameters Parameter organizer.
     * @return ParameterOrganizer
     */
    public static function organize(array $parameters)
    {
        return new ParameterOrganizer($parameters);
    }
}