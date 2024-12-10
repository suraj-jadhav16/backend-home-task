<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use App\Entity\ScanStatus;
use App\Service\DebrickedAPI;
use App\Service\EmailService;

class ScanService
{
    private $entityManager;
    private $debrickedAPI;
    private $emailService;
    private $minimumVulnerabilityLevel = 10;
    

    /**
     * Constructor to initialize the ScanService service
     * 
     * @param EntityManagerInterface $entityManager EntityManagerInterface service
     * @param DebrickedAPI $debrickedAPI DebrickedAPI service
     * @param EmailService $emailService EmailService service
     * 
     * @return null
     */
    public function __construct(DebrickedAPI $debrickedAPI, EntityManagerInterface $entityManager, EmailService $emailService)
    {
        $this->entityManager = $entityManager;
        $this->debrickedAPI = $debrickedAPI;
        $this->emailService = $emailService;
    }

    /**
     * Check the scan status of the files
     * 
     * @return int
     */
    public function checkScanStatus()
    {
        $scanResultUrl = $_ENV['DEBRICKED_API_SCAN_RESULT_URL'];

        $scanStatuses = $this->entityManager->getRepository(ScanStatus::class)
            ->findBy(['status' => 0]);

        if (count($scanStatuses) > 0) {
            $jwtToken = $this->debrickedAPI->getJwtToken();

            $curlObject = curl_init();

            foreach ($scanStatuses as $scanStatus) {
                $url = $scanResultUrl . '?ciUploadId=' . $scanStatus->getCiUploadId();
                curl_setopt($curlObject, CURLOPT_URL, $url);
                curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curlObject, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $jwtToken,
                    'accept: */*'
                ]);

                $response = curl_exec($curlObject);
                if ($response !== false) {
                    $responseData = json_decode($response, true);
                    if (isset($responseData['progress']) && $responseData['progress'] == 100) {
                        if($responseData['vulnerabilitiesFound'] >= $this->minimumVulnerabilityLevel){
                        
                            $subject = 'Scan Results';
                            $body = "ciUploadId: {$scanStatus->getCiUploadId()} has {$responseData['vulnerabilitiesFound']} vulnerabilities.";
                            $body .= "<br>";
                            $body .= "<br>Click <a href=\"{$responseData['detailsUrl']}\" target=\"_blank\">here</a> to check the detailed report.";
                            $this->emailService->sendEmail($subject, $body);

                        }
                        // Mark status in scan_status table as 1
                        $scanStatus->setStatus(1);
                        $scanStatus->setUpdatedAt();
                        $this->entityManager->persist($scanStatus);
                        $this->entityManager->flush();
                    }
                }
            }
            curl_close($curlObject);
        }
        return Command::SUCCESS;
    }
}
