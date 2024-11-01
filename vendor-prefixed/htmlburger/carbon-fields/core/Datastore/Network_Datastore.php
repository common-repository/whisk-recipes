<?php

namespace Whisk\Recipes\Vendor\Carbon_Fields\Datastore;

use Whisk\Recipes\Vendor\Carbon_Fields\Field\Field;
/**
 * Theme options datastore class.
 */
class Network_Datastore extends \Whisk\Recipes\Vendor\Carbon_Fields\Datastore\Meta_Datastore
{
    /**
     * {@inheritDoc}
     */
    public function get_meta_type()
    {
        return 'site';
    }
    /**
     * {@inheritDoc}
     */
    public function get_table_name()
    {
        global $wpdb;
        return $wpdb->sitemeta;
    }
    /**
     * {@inheritDoc}
     */
    public function get_table_field_name()
    {
        return 'site_id';
    }
}
