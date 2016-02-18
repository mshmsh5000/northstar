<?php

namespace Northstar\Services;

use finfo;
use Storage;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AWS
{
    /**
     * Store an image in S3.
     *
     * @param string $folder - Folder to write image to
     * @param string $filename - Filename to write image to
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile|string $file
     *   File object, or a base-64 encoded data URI
     *
     * @return string - URL of stored image
     */
    public function storeImage($folder, $filename, $file)
    {
        if (is_string($file)) {
            $data = $this->base64StringToDataString($file);
            $extension = $this->guessExtension($data);
        } else {
            $extension = $file->guessExtension();
            $data = file_get_contents($file->getPathname());
        }

        // Make sure we're only uploading valid image types
        if (! in_array($extension, ['jpeg', 'png'])) {
            throw new UnprocessableEntityHttpException('Invalid file type. Upload a JPEG or PNG.');
        }

        $path = 'uploads/'.$folder.'/'.$filename.'.'.$extension;
        Storage::disk('s3')->put($filename, $data);

        return config('filesystems.disks.s3.public_url').$path;
    }

    /**
     * Guess the extension from a data buffer string.
     * @param string $data - Data buffer string
     * @return string - file extension
     */
    protected function guessExtension($data)
    {
        $f = new finfo();
        $mimeType = $f->buffer($data, FILEINFO_MIME_TYPE);
        $guesser = ExtensionGuesser::getInstance();

        return $guesser->guess($mimeType);
    }

    /**
     * Decode Base-64 encoded string into a raw data buffer string.
     * @param $string - Base-64 encoded string
     * @return string - raw data
     */
    protected function base64StringToDataString($string)
    {
        // Trim the mime-type (e.g. `data:image/png;base64,`) from the string
        $file = last(explode(',', $string));

        return base64_decode($file);
    }
}
