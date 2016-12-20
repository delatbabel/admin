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
        $save = $this->image[$this->input]->move($this->path, $filename);

        if ($save) {
            if (is_array($this->image_sizes)) {
                $resizer = new Resize();
                $resizer->create($save, $this->path, $filename, $this->image_sizes);
            }
        } else {
            abort(500, 'Could not save image');
        }

        return $filename;
    }
}
