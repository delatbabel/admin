<?php

namespace DDPro\Admin\Includes;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
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
     * Returns an array with keys:
     *     errors
     *     path
     *     filename
     *     original_name
     *     resizes
     *
     * Note that it doesn't currently do that, it currently returns a string but it's
     * broken and needs to be fixed.
     *
     * @return array
     */
    protected function upload_image()
    {
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'Upload image with no validation');

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

        $fullPath = $this->path . $filename;

        // Upload the file
        $save = Storage::put($fullPath, file_get_contents($file), 'public');

        if (! $save) {
            Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
                'Storage put failed');

            abort(500, 'Could not save image');
        }

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
            'Storage put succeeded, path = ' . $fullPath);
        return $fullPath;
    }
}
