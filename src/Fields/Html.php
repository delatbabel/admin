<?php
namespace DDPro\Admin\Fields;

class Html extends Field
{
    /**
     * The specific rules for subclasses to override
     *
     * @var array
     */
    protected $rules = [
        'content' => 'required',
    ];
}
