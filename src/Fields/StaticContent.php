<?php
namespace DDPro\Admin\Fields;

class StaticContent extends Field
{
    /**
     * The specific defaults for subclasses to override
     *
     * @var array
     */
    protected $defaults = [
        'setter' => true,
    ];

    /**
     * The specific rules for subclasses to override
     *
     * @var array
     */
    protected $rules = [
        'content' => 'required',
    ];
}
