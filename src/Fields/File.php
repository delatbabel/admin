<?php
namespace DDPro\Admin\Fields;

use DDPro\Admin\Includes\CustomMultup;

class File extends Field
{

    /**
     * The specific defaults for subclasses to override
     *
     * @var array
     */
    protected $defaults = [
        'naming'            => 'random',
        'length'            => 32,
        'mimes'             => false,
        'size_limit'        => 2,
        'display_raw_value' => false,
    ];

    /**
     * The specific rules for subclasses to override
     *
     * @var array
     */
    protected $rules = [
        'location' => 'required|string',
        'naming'   => 'in:keep,random',
        'length'   => 'integer|min:0',
        'mimes'    => 'string',
    ];

    /**
     * Builds a few basic options
     */
    public function build()
    {
        parent::build();

        // set the upload url depending on the type of config this is
        $url   = $this->validator->getUrlInstance();
        $route = $this->config->getType() === 'settings' ? 'admin_settings_file_upload' : 'admin_file_upload';

        // set the upload url to the proper route
        $this->suppliedOptions['upload_url'] = $url->route($route,
            [$this->config->getOption('name'), $this->suppliedOptions['field_name']]);
    }

    /**
     * This function is used to perform the actual upload using the CustomMultup class
     *
     * @return array
     */
    public function doUpload()
    {
        // Come back to this if we ever need MIME type validation
        // $mimes = $this->getOption('mimes') ? '|mimes:' . $this->getOption('mimes') : '';
        // $result = Multup::open('file', 'max:' . $this->getOption('size_limit') * 1000 . $mimes,

        // use the multup library to perform the upload
        // open parameters:
        // $input, $rules, $path, $random = true
        /** @var CustomMultup $multup */
        $multup = CustomMultup::open(
            $this->getOption('field_name'),
            null,
            $this->getOption('location'),
            $this->getOption('naming') === 'random'
        );

        /** @var array $result */
        $result = $multup
            ->set_length($this->getOption('length'))
            ->upload();

        return $result[0];
    }
}
