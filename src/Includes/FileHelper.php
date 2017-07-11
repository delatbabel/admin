<?php
/**
 * Class FileHelper
 *
 * @author Dang Nguyen
 */

namespace DDPro\Admin\Includes;

use Aws\S3\S3Client;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use Log;

/**
 * Class FileHelper
 *
 * Provides function to get the URL of a file when it is stored on cloud (AWS S3) storage.
 *
 * ### Example
 *
 * <code>
 * $file_path = FileHelper::getFileUrl('path/to/stored/file.pdf');
 * </code>
 *
 * @link https://stackoverflow.com/questions/25323753/laravel-league-flysystem-getting-file-url-with-aws-s3
 */
class FileHelper
{
    /**
     * Get raw file attribute based on its path on file storage
     *
     * @param $path string
     * @return string
     */
    public static function getRawFile($path)
    {
        $disk = config('filesystems.default');

        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::disk($disk);

        // Check that the path is valid
        if (! $storage->exists($path)) {
            return '';
        }

        // Fetch the raw data from the storage
        $mime_type = $storage->mimeType($path);
        $raw_file  = "data:$mime_type;base64," . base64_encode($storage->get($path));
        return $raw_file;
    }

    /**
     * Get file URL attribute based on its path on file storage
     *
     * @param $path string
     * @return string
     * @link https://stackoverflow.com/questions/25323753/laravel-league-flysystem-getting-file-url-with-aws-s3
     */
    public static function getFileUrl($path)
    {
        $disk = config('filesystems.default');
        #Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
        #    'Get File URL for path = ' . $path);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::disk($disk);

        // Check that the path is valid
        if (! $storage->exists($path)) {
            return '';
        }

        #Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
        #    'Filesystem driver = ' . $disk);
        switch ($disk) {
            case 's3':
                /** @var Filesystem $driver */
                $driver = $storage->getDriver();

                /** @var AwsS3Adapter $adapter */
                $adapter = $driver->getAdapter();

                /** @var S3Client $client */
                $client = $adapter->getClient();
                $bucket = config('filesystems.disks.s3.bucket');
                $url    = $client->getObjectUrl($bucket, $path);
                #Log::debug(__CLASS__ . ':' . __TRAIT__ . ':' . __FILE__ . ':' . __LINE__ . ':' . __FUNCTION__ . ':' .
                #    'URL = ' . $url);
                return $url;
                break;

            case 'local':
                return config('url') . '/' . $path;
                break;

            default:
                return static::getRawFile($path);
                break;
        }
    }
}
