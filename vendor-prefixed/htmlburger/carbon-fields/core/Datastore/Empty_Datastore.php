<?php

namespace Whisk\Recipes\Vendor\Carbon_Fields\Datastore;

use Whisk\Recipes\Vendor\Carbon_Fields\Field\Field;
/**
 * Empty datastore class.
 */
class Empty_Datastore extends \Whisk\Recipes\Vendor\Carbon_Fields\Datastore\Datastore
{
    /**
     * {@inheritDoc}
     */
    public function init()
    {
    }
    /**
     * {@inheritDoc}
     */
    public function load(\Whisk\Recipes\Vendor\Carbon_Fields\Field\Field $field)
    {
    }
    /**
     * {@inheritDoc}
     */
    public function save(\Whisk\Recipes\Vendor\Carbon_Fields\Field\Field $field)
    {
    }
    /**
     * {@inheritDoc}
     */
    public function delete(\Whisk\Recipes\Vendor\Carbon_Fields\Field\Field $field)
    {
    }
}
