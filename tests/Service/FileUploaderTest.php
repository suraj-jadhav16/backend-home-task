<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\FileUploader;

class FileUploaderTest extends TestCase
{
    public function testUpload()
    {
        $mockFile = $this->createMock(UploadedFile::class);

        $uploadDir = '/uploads';
        $fileName = 'file.txt';

        $mockFile->method('getClientOriginalName')->willReturn($fileName);
        $mockFile->expects($this->once())
                 ->method('move')
                 ->with($uploadDir, $fileName);

        $fileUploader = new FileUploader($uploadDir);
        $result = $fileUploader->upload($mockFile);

        $this->assertEquals($fileName, $result);
    }

    public function testUploadMultiple()
    {
        $mockFile1 = $this->createMock(UploadedFile::class);
        $mockFile2 = $this->createMock(UploadedFile::class);

        $mockFile1->method('isValid')->willReturn(true);
        $mockFile1->method('getClientOriginalName')->willReturn('file1.txt');
        $mockFile1->expects($this->once())
                  ->method('move')
                  ->with('/uploads', 'file1.txt');

        $mockFile2->method('isValid')->willReturn(true);
        $mockFile2->method('getClientOriginalName')->willReturn('file2.txt');
        $mockFile2->expects($this->once())
                  ->method('move')
                  ->with('/uploads', 'file2.txt');

        $files = [$mockFile1, $mockFile2];

        $fileUploader = new FileUploader('/uploads');
        $uploadedFiles = $fileUploader->uploadMultiple($files);

        $this->assertEquals(['file1.txt', 'file2.txt'], $uploadedFiles);
    }
}
