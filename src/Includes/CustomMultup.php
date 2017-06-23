<?php

namespace DDPro\Admin\Includes;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class CustomMultup
 *
 * Customised file uploader class based on Multup with no validation and files being
 * uploaded to Flysystem storage rather than local storage.
 */
class CustomMultup extends Multup
{
    protected function upload_image()
    {
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

        // Upload the file
        $save = Storage::put($this->path . $filename, file_get_contents($file), 'public');

        if (! $save) {
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

        return $this->path . $filename;
    }
}
