<?php

namespace Rentalhost\VanillaParameter;

use Closure;

class Helper
{
    /**
     * Check if value is some kind of dependency: class or interface.
     * @param  string $value Value to check.
     * @return boolean
     */
    public static function isDependency($value)
    {
        // A Closure will not be considered as an object.
        // I'll better treated by ParameterOrganizer.
        if (is_object($value)) {
            return !$value instanceof Closure;
        }

        return is_string($value) && (
            class_exists($value) ||
            interface_exists($value)
        );
    }
    /**
     * Normalize type.
     * @param  string $type Type to normalize.
     * @return string[]
     */
    public static function normalizeType($type)
    {
        $typeLower = strtolower($type);

        switch ($typeLower) {
            case "string":
            case "integer":
            case "float":
            case "resource":
            case "object":
            case "array":
            case "mixed":
            case "callable":
                return $typeLower;
                break;

            case "bool":
                return "boolean";
                break;

            case "int":
                return "integer";
                break;

            case "double":
                return "float";
                break;

            case "*":
            case "any":
                return "mixed";
                break;
        }
    }

    /**
     * Normalize an array of types.
     * @param  string[] $types Array of types to normalize.
     * @return string[]
     */
    public static function normalizeTypes($types)
    {
        // Normalize string.
        if (is_string($types)) {
            $types = [ $types ];
        }

        $results = [];

        foreach ($types as $type) {
            $typeNormalized = static::normalizeType($type);

            // If was not possible to normalize, it can be:
            // a class, interface or trait.
            if ($typeNormalized === null) {
                $results[] = $type;
                continue;
            }

            // Else, just add as a result.
            $results[] = $typeNormalized;
        }

        return array_unique($results);
    }

    /**
     * Normalize a value.
     * @param  string $value Value to normalize.
     * @return string
     */
    public static function normalizeValue($value)
    {
        // Check if it's a callable.
        if (is_callable($value)) {
            return "callable";
        }

        $normalize = static::normalizeType(gettype($value));

        // Check if it's a class.
        if ($normalize === "object") {
            return get_class($value);
        }

        return $normalize;
    }
}