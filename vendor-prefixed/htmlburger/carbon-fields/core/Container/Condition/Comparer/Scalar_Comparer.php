<?php

namespace Whisk\Recipes\Vendor\Carbon_Fields\Container\Condition\Comparer;

use Whisk\Recipes\Vendor\Carbon_Fields\Exception\Incorrect_Syntax_Exception;
class Scalar_Comparer extends \Whisk\Recipes\Vendor\Carbon_Fields\Container\Condition\Comparer\Comparer
{
    /**
     * Supported comparison signs
     *
     * @var array<string>
     */
    protected $supported_comparison_operators = array('>', '>=', '<', '<=');
    /**
     * Check if comparison is true for $a and $b
     *
     * @param mixed  $a
     * @param string $comparison_operator
     * @param mixed  $b
     * @return bool
     */
    public function is_correct($a, $comparison_operator, $b)
    {
        if (!\is_scalar($a)) {
            \Whisk\Recipes\Vendor\Carbon_Fields\Exception\Incorrect_Syntax_Exception::raise('Environment value for comparison is not scalar: ' . \print_r($a, \true));
            return \false;
        }
        if (!\is_scalar($b)) {
            \Whisk\Recipes\Vendor\Carbon_Fields\Exception\Incorrect_Syntax_Exception::raise('Supplied comparison value is not scalar: ' . \print_r($b, \true));
            return \false;
        }
        switch ($comparison_operator) {
            case '>':
                return $a > $b;
            case '>=':
                return $a >= $b;
            case '<':
                return $a < $b;
            case '<=':
                return $a <= $b;
        }
        return \false;
    }
}
