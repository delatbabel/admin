<?php
/**
 * Class Entity
 *
 * @author del
 */

namespace DDPro\Admin\Includes;

use Illuminate\Support\Fluent;

/**
 * Class Entity
 *
 * Base Entity class based on Laravel's Fluent class.
 *
 * ### Example
 *
 * <code>
 * $my_entity = new Entity(['one' => '1']);
 * </code>
 */
class Entity extends Fluent
{
    /**
     * The default attributes set on the container.
     *
     * @var array
     */
    protected $default_attributes = [];

    /**
     * Create a new fluent container instance.
     *
     * @param  array|object    $attributes
     */
    public function __construct($attributes = [])
    {
        foreach ($this->default_attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }
        parent::__construct($attributes);
    }
}
