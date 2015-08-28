<?php

namespace Rentalhost\VanillaParameter\Test;

/**
 * Class CallableClass
 * @package Rentalhost\VanillaParameter\Test
 */
class CallableClass
{
    /**
     * @return bool
     */
    public function __invoke()
    {
        return true;
    }
}
