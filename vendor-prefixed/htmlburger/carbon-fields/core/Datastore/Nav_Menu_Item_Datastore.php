<?php

namespace Whisk\Recipes\Vendor\Carbon_Fields\Datastore;

use Whisk\Recipes\Vendor\Carbon_Fields\Field\Field;
class Nav_Menu_Item_Datastore extends \Whisk\Recipes\Vendor\Carbon_Fields\Datastore\Post_Meta_Datastore
{
    public function get_garbage_prefix()
    {
        if (!$this->get_object_id()) {
            return '';
        }
        return '_menu-item-' . $this->get_object_id() . '_';
    }
    public function get_clean_field_name($field)
    {
        $name = \is_object($field) && \is_subclass_of($field, 'Whisk\\Recipes\\Vendor\\Whisk\\Recipes\\Vendor\\Carbon_Fields\\Field\\Field') ? $field->get_name() : $field;
        $garbage_prefix = $this->get_garbage_prefix();
        $garbage_prefix_length = \strlen($garbage_prefix);
        if (\substr($name, 0, $garbage_prefix_length) === $garbage_prefix) {
            $name = \substr($name, $garbage_prefix_length);
        }
        return $name;
    }
    public function get_dirty_field_name($field)
    {
        $name = \is_object($field) && \is_subclass_of($field, 'Whisk\\Recipes\\Vendor\\Whisk\\Recipes\\Vendor\\Carbon_Fields\\Field\\Field') ? $field->get_name() : $field;
        $garbage_prefix = $this->get_garbage_prefix();
        $garbage_prefix_length = \strlen($garbage_prefix);
        if (\substr($name, 0, $garbage_prefix_length) !== $garbage_prefix) {
            $name = $garbage_prefix . $name;
        }
        return $name;
    }
    /**
     * Load the field value(s)
     *
     * @param Field $field The field to load value(s) in.
     */
    public function load(\Whisk\Recipes\Vendor\Carbon_Fields\Field\Field $field)
    {
        if (!$this->get_object_id()) {
            return;
        }
        $old_name = $field->get_name();
        $field->set_name($this->get_clean_field_name($field));
        $result = parent::load($field);
        $field->set_name($old_name);
        return $result;
    }
    /**
     * Save the field value(s)
     *
     * @param Field $field The field to save.
     */
    public function save(\Whisk\Recipes\Vendor\Carbon_Fields\Field\Field $field)
    {
        if (!$this->get_object_id()) {
            return;
        }
        $clone = clone $field;
        $clone->set_name($this->get_clean_field_name($field));
        parent::save($clone);
    }
    /**
     * Delete the field value(s)
     *
     * @param Field $field The field to delete.
     */
    public function delete(\Whisk\Recipes\Vendor\Carbon_Fields\Field\Field $field)
    {
        $clone = clone $field;
        $clone->set_name($this->get_clean_field_name($field));
        parent::delete($clone);
    }
}
