<?php

namespace Rentalhost\VanillaParameter;

use Closure;

class ParameterOrganizer
{
    /**
     * Store parameters.
     * @var mixed[]
     */
    private $parameters;

    /**
     * Store parameters count.
     * @var integer
     */
    private $parametersCount;

    /**
     * Current parameter index.
     * @var integer
     */
    private $index;

    /**
     * Store if parameters is all ok.
     * @var boolean
     */
    private $valid;

    /**
     * Stores current reference.
     * @var reference
     */
    private $reference;

    /**
     * Indicate if reference was validated.
     * @var boolean
     */
    private $referenceValidated;

    /**
     * Construct the organizer.
     * @param  array  $parameters Parameter organizer.
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
        $this->parametersCount = count($parameters);
        $this->index = 0;
        $this->valid = true;
    }

    /**
     * Add a new expected paramter.
     * @param  reference       $reference   Variable to save parameter.
     * @param  string|string[] $expectTypes Valid parameter types (default: mixed).
     * @param  boolean         $required    Is parameter is required (default: false).
     * @return $this
     */
    public function add(&$reference, $expectTypes = null, $required = null)
    {
        unset($this->reference);

        // Add current reference to instance.
        $this->reference = &$reference;
        $this->referenceValidated = false;

        // Parse expected types.
        $expectTypes = Helper::normalizeTypes($expectTypes ?: []);

        if ($this->index < $this->parametersCount) {
            $currentParameter = $this->parameters[$this->index];

            // Validate if is some of expected types.
            if (static::someExpectedTypes($currentParameter, $expectTypes)) {
                $reference = $currentParameter;

                $this->referenceValidated = true;
                $this->index++;

                return $this;
            }
        }

        // Will invalid in two cases, when required:
        // 1. If there is not parameters to check anymore.
        // 2. If last parameter no match with expected types.
        if ($required === true) {
            $this->valid = false;
        }

        return $this;
    }

    /**
     * Validate if has expected number of parameters.
     * @return $this
     */
    public function expects($count)
    {
        $this->valid = $this->valid && count($this->parameters) >= $count;

        return $this;
    }

    /**
     * Specify a default value, when parameter is not defined on current index.
     * @param  mixed $value Default value.
     * @return $this
     */
    public function defaultValue($value)
    {
        if (!$this->referenceValidated) {
            $this->reference = $value;
        }

        return $this;
    }

    /**
     * Validate if all parameters are ok.
     * @return boolean
     */
    public function validate()
    {
        return $this->valid;
    }

    /**
     * Check if value matches with expected type.
     * @param  mixed $value         Value to check.
     * @param  array $expectedTypes Expected types.
     * @return boolean
     */
    private function someExpectedTypes($value, array $expectedTypes)
    {
        // Allow if mixed was defined or if list is empty.
        if (empty($expectedTypes)
        ||  in_array("mixed", $expectedTypes)) {
            return true;
        }

        // If value is some kind of dependency, check if is subclass.
        if (Helper::isDependency($value)) {
            $dependencyTypes = array_filter($expectedTypes, [ Helper::class, "isDependency" ]);

            foreach ($dependencyTypes as $dependencyType) {
                if (is_a($value, $dependencyType)) {
                    return true;
                }
            }

            return false;
        }

        // Check if parameter type is on list.
        return in_array(Helper::normalizeValue($value), $expectedTypes);
    }
}
