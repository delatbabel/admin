<?php
/**
 * Class UploadedImage
 *
 * @author del
 */

namespace DDPro\Admin\Includes;

use Delatbabel\Fluents\Entity;

/**
 * Class UploadedImage
 *
 * This is an entity class to hold data about uploaded images.
 *
 * ### Example
 *
 * <code>
 * $my_image = new UploadedImage(['path' => '/asdf/asdf/asdf.jpg']);
 *
 * if ($my_image->isSuccessful()) {
 *     // ...
 * }
 * </code>
 */
class UploadedImage extends Entity
{
    protected $default_attributes = [
        'errors'        => '',
        'path'          => '',
        'url'           => '',
        'filename'      => '',
        'original_name' => '',
        'resizes'       => [],
    ];

    /**
     * Returns true if the upload was successful.
     *
     * @return bool
     */
    public function isSuccessful()
    {
        $errors = $this->get('errors');
        if (empty($errors)) {
            return true;
        }
        return false;
    }
}
