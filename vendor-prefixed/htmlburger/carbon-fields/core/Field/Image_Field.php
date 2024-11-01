<?php

namespace Whisk\Recipes\Vendor\Carbon_Fields\Field;

/**
 * Image field class.
 *
 * Allows selecting and saving a media attachment file,
 * where the image ID is saved in the database.
 */
class Image_Field extends \Whisk\Recipes\Vendor\Carbon_Fields\Field\File_Field
{
    public $field_type = 'image';
}
