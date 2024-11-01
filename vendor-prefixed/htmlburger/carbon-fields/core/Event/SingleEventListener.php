<?php

namespace Whisk\Recipes\Vendor\Carbon_Fields\Event;

class SingleEventListener extends \Whisk\Recipes\Vendor\Carbon_Fields\Event\PersistentListener
{
    /**
     * Flag if the event has been called
     *
     * @var boolean
     */
    protected $called = \false;
    /**
     * {@inheritDoc}
     */
    public function is_valid()
    {
        return !$this->called;
    }
    /**
     * {@inheritDoc}
     */
    public function notify()
    {
        $this->called = \true;
        return \call_user_func_array(array($this, 'parent::notify'), \func_get_args());
    }
}
