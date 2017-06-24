<?php

namespace DDPro\Admin\Includes;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Log;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class CustomMultup
 *
 * Customised file uploader class based on Multup with no validation and files being
 * uploaded to Flysystem storage rather than local storage.
 */
class CustomMultup extends Multup
{
    /**
     * Upload the image
     *
     * Returns an UploadedImage with keys:
     *     errors
     *     path
     *     url
     *     filename
     *     original_name
     *     resizes
     *
     * Note:  Callers to this function may previously have expected a string, not an object.
     *
     * @return UploadedImage
     */
    protected function upload_image()
    {
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'Upload image with no validation');

        $errors        = '';
        $path          = '';
        $url           = '';
        $filename      = '';
        $resizes       = [];

        /** @var UploadedFile $file */
        $file = $this->image[$this->input];

        $original_name = $file->getClientOriginalName();
        if ($this->random) {
            if (is_callable($this->random_cb)) {
                $filename = call_user_func($this->random_cb, $original_name);
            } else {
                $ext      = File::extension($original_name);
                $filename = $this->generate_random_filename() . '.' . $ext;
            }
        } else {
            $filename = $original_name;
        }

        $path = $this->path . $filename;

        // Upload the file
        $save = Storage::put($path, file_get_contents($file), 'public');

        if (! $save) {
            $errors = 'Storage put failed';
            Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
                $errors);

            return new UploadedImage([
                'errors'            => $errors,
                'path'              => $path,
                'url'               => $url,
                'filename'          => $filename,
                'original_name'     => $original_name,
                'resizes'           => $resizes,
            ]);
        }

        $url  = ImageHelper::getImageUrl($path);

        // Come back to this if we ever need resizing.
        // Do resize here
        /*
        if (is_array($this->image_sizes)) {
            // Move the file to local storage & make thumbnails
            $file = $this->image[$this->input]->move('/tmp/', $filename);

            $resizer = new Resize();
            $resizer->create($file, '/tmp/', $filename, $this->image_sizes);

            foreach ($this->image_sizes as $size) {
                $storage->put($size[3] . $filename, file_get_contents($size[3] . $filename), 'public');
            }
        }
        */

        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'Storage put succeeded, path = ' . $path . ', url = ' . $url);

        return new UploadedImage([
            'errors'            => $errors,
            'path'              => $path,
            'url'               => $url,
            'filename'          => $filename,
            'original_name'     => $original_name,
            'resizes'           => $resizes,
        ]);
    }
}
