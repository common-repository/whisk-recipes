<?php

namespace Whisk\Recipes\Vendor\Carbon_Fields\Container\Condition\Comparer;

use Whisk\Recipes\Vendor\Carbon_Fields\Exception\Incorrect_Syntax_Exception;
class Custom_Comparer extends \Whisk\Recipes\Vendor\Carbon_Fields\Container\Condition\Comparer\Comparer
{
    /**
     * Supported comparison signs
     *
     * @var array<string>
     */
    protected $supported_comparison_operators = array('CUSTOM');
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
        if (!\is_callable($b)) {
            \Whisk\Recipes\Vendor\Carbon_Fields\Exception\Incorrect_Syntax_Exception::raise('Supplied comparison value is not a callable: ' . \print_r($b, \true));
            return \false;
        }
        switch ($comparison_operator) {
            case 'CUSTOM':
                return (bool) $b($a);
        }
        return \false;
    }
}
