<?php
/**
 * @version 0.2.0
 * @author Nick Kelly @ Frozen Node
 * @link github.com/
 */
namespace DDPro\Admin\Includes;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Log;
use Symfony\Component\HttpFoundation\File\UploadedFile;

//use Admin\Libraries\Includes\Resize;

/**
 * Class Multup
 *
 * Requires Validator, URL, and Str class from Laravel if used
 *
 * ### Example
 *
 * <code>
 * $multup = Multup::open(
 *     $file_name
 *     null,
 *     $path,
 *     true
 * );
 *
 * $result = $multup->upload();
 * </code>
 */
class Multup
{
    /** @var  array image array */
    protected $image;

    /** @var string Laravel validation rules */
    protected $rules;

    /** @var  boolean randomise uploaded filename */
    protected $random;

    /** @var string path relative to /public/ that the image should be saved in */
    protected $path;

    /** @var string id/name of the file input to find */
    protected $input;

    /** @var integer How long the random filename should be */
    protected $random_length = 32;

    /** @var Callable function for setting your own random filename */
    protected $random_cb;

    /** @var array  Sizing information for thumbs to create array ( width, height, crop_type, path_to_save, quality) */
    protected $image_sizes;

    /** @var Callable Upload callback function to be called after an image is done being uploaded */
    protected $upload_callback;

    /*@ @var array Additional arguments to be passed into the callback function */
    protected $upload_callback_args;

    /**
     * Instantiates the Multup
     *
     * @param  string $input name of the file to upload
     * @param  string $rules laravel style validation rules string
     * @param  string $path path to move the images if valid
     * @param  bool $random Whether or not to randomize the filename, the filename will be set to a 32 character string if true
     */
    public function __construct($input, $rules, $path, $random)
    {
        $this->input  = $input;
        $this->rules  = $rules;
        $this->path   = $path;
        $this->random = $random;
    }

    /**
     * Static call, Laravel style.
     *
     * Returns a new Multup object, allowing for chainable calls
     *
     * @param  string $input name of the file to upload
     * @param  string $rules laravel style validation rules string
     * @param  string $path path to move the images if valid
     * @param  bool $random Whether or not to randomize the filename, the filename will be set to a 32 character string if true
     * @return static
     */
    public static function open($input, $rules, $path, $random = true)
    {
        return new static($input, $rules, $path, $random);
    }

    /**
     * Set the length of the randomized filename
     *
     * @param int $len
     * @return $this
     */
    public function set_length($len)
    {
        $this->random_length = $len;

        return $this;
    }

    /**
     * Upload the image
     *
     * Returns an array of results
     *    each result will be an array() with keys:
     *        errors array -> empty if saved properly, otherwise $validation->errors object
     *        path string -> full URL to the file if saved, empty if not saved
     *        filename string -> name of the saved file or file that could not be uploaded
     *
     * @return array
     */
    public function upload()
    {
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'Upload handler');

        /** @var UploadedFile $file */
        $file        = Input::file($this->input);
        $this->image = [$this->input => $file];
        $result      = [];

        $result[] = $this->post_upload_process($this->upload_image());

        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'Upload result', $result);

        return $result;

        // Come back to this if we ever need multi image upload.  I think that this is how
        // the original code was supposed to work but the current file and image widgets
        // only support a single file upload.
        /*
        if (! is_array($images)) {
            $this->image = [$this->input => $images];

            $result[] = $this->post_upload_process($this->upload_image());
        } else {
            $size = $count($images['name']);

            for ($i = 0; $i < $size; $i++) {
                $this->image = [
                    $this->input => [
                        'name'      => $images['name'][$i],
                        'type'      => $images['type'][$i],
                        'tmp_name'  => $images['tmp_name'][$i],
                        'error'     => $images['error'][$i],
                        'size'      => $images['size'][$i]
                    ]
                ];

                $result[] = $this->post_upload_process($this->upload_image());
            }
        }

        return $result;
        */
    }

    /**
     * Upload the image
     *
     * Returns an entity with keys:
     *     errors
     *     path
     *     filename
     *     original_name
     *     resizes
     *
     * @return UploadedImage
     */
    protected function upload_image()
    {
        Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
            'Upload image with validation');

        // validate the image
        $validation    = Validator::make($this->image, [$this->input => $this->rules]);
        $errors        = [];
        $original_name = $this->image[$this->input]->getClientOriginalName();
        $path          = '';
        $filename      = '';
        $resizes       = '';

        if ($validation->fails()) {
            // use the messages object for the errors
            $errors = implode('. ', $validation->messages()->all());
            Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
                'Image validation failed: ' . $errors);

            return compact('errors', 'path', 'filename', 'original_name', 'resizes');
        }

        if ($this->random) {
            if (is_callable($this->random_cb)) {
                $filename =  call_user_func($this->random_cb, $original_name);
            } else {
                $ext      = File::extension($original_name);
                $filename = $this->generate_random_filename() . '.' . $ext;
            }
        } else {
            $filename = $original_name;
        }

        // upload the file
        $save = $this->image[$this->input]->move($this->path, $filename);
        // $save = Input::upload($this->input, $this->path, $filename);

        if ($save) {
            $path = $this->path . $filename;

            if (is_array($this->image_sizes)) {
                $resizer = new Resize();
                $resizes = $resizer->create($save, $this->path, $filename, $this->image_sizes);
            }
        } else {
            $errors = 'Could not save image';
        }

        return new UploadedImage([
            'errors'            => $errors,
            'path'              => $path,
            'filename'          => $filename,
            'original_name'     => $original_name,
            'resizes'           => $resizes,
        ]);
    }

    /**
     * Default random filename generation
     *
     * @return string
     */
    protected function generate_random_filename()
    {
        return Str::random($this->random_length);
    }

    /**
     * Set a random filename generation callback
     *
     * @param Callable $func
     * @return $this
     */
    public function filename_callback($func)
    {
        if (is_callable($func)) {
            $this->random_cb = $func;
        }

        return $this;
    }

    /**
     * Set the callback function to be called after each image is done uploading
     *
     * @param Callable $cb
     * @param string $args
     * @return $this
     */
    public function after_upload($cb, $args = '')
    {
        if (is_callable($cb)) {
            $this->upload_callback      = $cb;
            $this->upload_callback_args = $args;
        } else {
            /* some sort of error... */
        }
        return $this;
    }

    /**
     * Sets the sizes for resizing the original
     *
     * parameter format
     * array(
     *  array(
     *	 int $width , int $height , string 'exact, portrait, landscape, auto or crop', string 'path/to/file.jpg' , int $quality
     *	)
     * )
     *
     * @param array $sizes
     * @return $this
     */
    public function sizes($sizes)
    {
        $this->image_sizes = $sizes;
        return $this;
    }

    /**
     * Called after an image is successfully uploaded
     *
     * If an upload_callback function has been defined it will also append a variable to the array
     * named callback_result
     *
     * @param array $args
     * @return array
     */
    protected function post_upload_process($args)
    {
        if (empty($args['errors'])) {

            // Call the upload callback if defined
            if (is_callable($this->upload_callback)) {
                if (! empty($this->upload_callback_args) && is_array($this->upload_callback_args)) {
                    $args = array_merge($this->upload_callback_args, $args);
                }

                $args['callback_result']  = call_user_func($this->upload_callback, $args);
            }
        }

        return $args;
    }
}
