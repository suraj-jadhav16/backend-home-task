<?php

namespace App\Controller;

use App\Service\FileUploader;
use App\Service\DebrickedAPI;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Service\EmailService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ScanStatus;
use Symfony\Component\Mailer\MailerInterface;


class FileUploadController extends AbstractController
{
    private $fileUploader;
    private $debrickedAPI;
    private $entityManager;
    private $emailService;


    /**
     * Constructor to initialize the FileUploader service
     * @param EmailService $emailService EmailService service
     * @param MailerInterface $mailer Mailer service
     * 
     * @return null
     */
    public function __construct(
        FileUploader $fileUploader,
        DebrickedAPI $debrickedAPI,
        EntityManagerInterface $entityManager,
        EmailService $emailService
    ) {
        $this->fileUploader = $fileUploader;
        $this->debrickedAPI = $debrickedAPI;
        $this->entityManager = $entityManager;
        $this->emailService = $emailService;
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
        try { 
            $files = $request->files->get('files');

            if (!$files || !is_array($files)) {
                return new JsonResponse(['error' => 'No files uploaded'], 400);
            }

            $uploadedFiles = $this->fileUploader->uploadMultiple($files);
            $this->emailService->sendEmail("File Uploading", "File uploading in progress");

            $jwtToken = $this->debrickedAPI->getJwtToken();
            if (!$jwtToken) {
                return new JsonResponse(['error' => 'Unable to authenticate with Debricked API'], 500);
            }

            $this->emailService->sendEmail("Files Uploading", "Files uploading in progress");
            $scanPreprationResults = $this->debrickedAPI->prepareFilesForScan($uploadedFiles, $jwtToken);

            if (!$scanPreprationResults['ciUploadId']) {
                return new JsonResponse(['error' => 'Unable to retrive ciUploadId'], 500);
            }

            $ciUploadId = (int) $scanPreprationResults['ciUploadId'];
            $repositoryName = $scanPreprationResults['repositoryName'];

            $scanResults = $this->debrickedAPI->startScan($ciUploadId, $repositoryName, $jwtToken);

            $scanStatus = new ScanStatus();
            $scanStatus->setCiUploadId($ciUploadId);
            $scanStatus->setStatus(false);
            
            $this->entityManager->persist($scanStatus);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Files were uploaded successfully'], 200);
        } catch (\Exception $e) {
            $this->emailService->sendEmail("Files Uploading", "Error while uploading files : " . $e->getMessage());
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}