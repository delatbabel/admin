<?php
namespace DDPro\Admin\Includes;

/**
 * Class CustomMultup
 * Custom from Multup with no validation
 *
 * @package DDPro\Admin\Includes
 */
class CustomMultup extends Multup
{
    public static function open($input, $rules, $path, $random = true)
    {
        return new CustomMultup($input, $rules, $path, $random);
    }

    protected function upload_image()
    {
        $original_name = $this->image[$this->input]->getClientOriginalName();
        if ($this->random) {
            if (is_callable($this->random_cb)) {
                $filename = call_user_func($this->random_cb, $original_name);
            } else {
                $ext = \File::extension($original_name);
                $filename = $this->generate_random_filename() . '.' . $ext;
            }
        } else {
            $filename = $original_name;
        }

        // Upload the file
        $disk = config('filesystems.default');
        $storage = \Storage::disk($disk);
        $save = $storage->put($this->path . $filename, file_get_contents($this->image[$this->input]), 'public');

        if ($save) {
            // Do resize here
//            if (is_array($this->image_sizes)) {
//                // Move the file to local storage & make thumbnails
//                $file = $this->image[$this->input]->move('/tmp/', $filename);
//
//                $resizer = new Resize();
//                $resizer->create($file, '/tmp/', $filename, $this->image_sizes);
//
//                foreach ($this->image_sizes as $size) {
//                    $storage->put($size[3] . $filename, file_get_contents($size[3] . $filename), 'public');
//                }
//            }
        } else {
            abort(500, 'Could not save image');
        }

        return $this->path . $filename;
    }
}
