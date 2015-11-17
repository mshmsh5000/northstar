<?php namespace Northstar\Services;

use Storage;

class AWS
{
    /**
     * Store an image in S3.
     *
     * @param string $folder - Folder to write image to
     * @param string $filename - Filename to write image to
     * @param \Symfony\Component\HttpFoundation\File\File|string $file
     *   File object, or a base-64 encoded data URI
     *
     * @return string - URL of stored image
     */
    public function storeImage($folder, $filename, $file)
    {
        if (is_string($file)) {
            $path = 'uploads/' . $folder . '/' . $filename;
            $data = base64_decode($file);
        } else {
            $extension = $file->guessExtension();
            $path = 'uploads/' . $folder . '/' . $filename . '.' . $extension;
            $data = file_get_contents($file);
        }

        Storage::disk('s3')->put($filename, $data);

        return getenv('S3_URL') . $path;
    }

}
