<?php

namespace App\Controller;

use App\Service\FileUploader;
use App\Service\DebrickedAPI;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Process\Process;


class FileUploadController extends AbstractController
{
    private $fileUploader;
    private $debrickedAPI;

    /**
     * Constructor to initialize the FileUploader service
     * 
     * @param FileUploader $fileUploader FileUploader service
     * @param DebrickedAPI $debrickedAPI DebrickedAPI service
     * 
     * @return null
     */
    public function __construct(FileUploader $fileUploader, DebrickedAPI $debrickedAPI)
    {
        $this->fileUploader = $fileUploader;
        $this->debrickedAPI = $debrickedAPI;
    }

     /**
     * To upload the files and stores them under the uploads directory
     * 
     * @param Request $request Request object
     * 
     * @return JsonResponse
     */
    #[Route('/file/upload', name: 'file_operations', methods: ['POST'])]
    public function uploadFiles(Request $request)
    {
        $files = $request->files->get('files');

        if (!$files || !is_array($files)) {
            return new JsonResponse(['error' => 'No files uploaded'], 400);
        }

        $uploadedFiles = $this->fileUploader->uploadMultiple($files);

        $jwtToken = $this->debrickedAPI->getJwtToken();

        if (!$jwtToken) {
            return new JsonResponse(['error' => 'Unable to authenticate with Debricked API'], 500);
        }

        $scanPreprationResults = $this->debrickedAPI->prepareFilesForScan($uploadedFiles, $jwtToken);

        if (!$scanPreprationResults['ciUploadId']) {
            return new JsonResponse(['error' => 'Unable to retrive ciUploadId'], 500);
        }

        $ciUploadId = $scanPreprationResults['ciUploadId'];
        $repositoryName = $scanPreprationResults['repositoryName'];

        $scanResults = $this->debrickedAPI->startScan($ciUploadId, $repositoryName, $jwtToken);

        $process = new Process(['php', 'bin/console', 'app:check-scan-status', '--ciUploadId=' . $ciUploadId]);
        $process->start();

        if ($process->isRunning()) {
            return new JsonResponse(['status' => 'Process started in background']);
        }

        // return new JsonResponse([
        //     'jwtToken' => $jwtToken,
        //     'scanPreprationResults' => $scanPreprationResults,
        //     'scanResults' => $scanResults,
        // ], 200);
    }
}