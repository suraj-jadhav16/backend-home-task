<?php
// src/Service/FileUploader.php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $uploadDirectory;

    /**
     * Initialize the uploading file directory
     * 
     * @return null
     */
    public function __construct(string $uploadDirectory)
    {
        $this->uploadDirectory = $uploadDirectory;
    }

    /**
     * Uploads a single file and returns the file name.
     * 
     * @param UploadedFile $file Abstract uploadedFile class
     * 
     * @return string
     */
    public function upload(UploadedFile $file)
    {
        $fileName = $file->getClientOriginalName();
        $file->move($this->uploadDirectory, $fileName);

        return $fileName;
    }

    /**
     * Uploads multiple files and returns an array of file names.
     * 
     * @param array $files Array of files of type file object
     * 
     * @return string
     */
    public function uploadMultiple(array $files)
    {
        $uploadedFiles = [];

        foreach ($files as $file) {
            if ($file->isValid()) {
                $uploadedFiles[] = $this->upload($file);
            }
        }

        return $uploadedFiles;
    }
}