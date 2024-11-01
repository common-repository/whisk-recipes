<?php

namespace Whisk\Recipes\Vendor\Carbon_Fields\Field;

use Whisk\Recipes\Vendor\Carbon_Fields\Exception\Incorrect_Syntax_Exception;
/**
 * Text field class.
 */
class Text_Field extends \Whisk\Recipes\Vendor\Carbon_Fields\Field\Field
{
    /**
     * {@inheritDoc}
     */
    protected $allowed_attributes = array('list', 'max', 'maxLength', 'min', 'pattern', 'placeholder', 'readOnly', 'step', 'type');
}
