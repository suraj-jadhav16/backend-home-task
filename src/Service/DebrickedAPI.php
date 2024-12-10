<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use \Exception;

class DebrickedAPI
{
    private $httpClient;
    private $url;
    private $username;
    private $password;

    /**
     * Initialize the HTTP client service
     * 
     * @param HttpClientInterface $httpClient HTTP client service
     * @param ParameterBagInterface $params ParameterBagInterface service
     * 
     * @return null
     */
    public function __construct(HttpClientInterface $httpClient, ParameterBagInterface $params)
    {
        $this->httpClient = $httpClient;
        $this->url = $_ENV['DEBRICKED_API_URL'];
        $this->username = $_ENV['DEBRICKED_API_USERNAME'];
        $this->password = $_ENV['DEBRICKED_API_PASSWORD'];
        $this->uploadDirectory = $params->get('upload_directory');
    }

    /**
     * Authincates and returns the JWT token for Debricked API
     * 
     * @return string|Exception
     */
    public function getJwtToken()
    {
        try {
            $response = $this->httpClient->request('POST', $this->url, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => [
                    '_username' => $this->username,
                    '_password' => $this->password,
                ]
            ]);
            
            // TODO: There is limit on the request per hour, we could cache the token in future.
            if ($response->getStatusCode() === 200) {
                $data = $response->toArray();
                return $data['token'];
            }
        } catch (Exception $e) {
            throw new Exception('Unable to authenticate with Debricked API');
        }

        return null;
    }

    /**
     * Send the files to the Debricked API for scan preparation
     * 
     * @param array $files Array of file names
     * @param string $jwtToken JWT token for Debricked API
     * 
     * @return array
     */
    public function prepareFilesForScan(array $files, string $jwtToken)
    {
        $preprationResults = [];
        $uploadUrl = $_ENV['DEBRICKED_API_PREPARE_SCAN_URL'];
        $ciUploadId = null;
        $repositoryName = 'test_' . rand();

        $curlObject = curl_init();
        foreach ($files as $fileName) {
            $filePath = $this->uploadDirectory . '/' . $fileName; 

            
            curl_setopt($curlObject, CURLOPT_URL, $uploadUrl);
            curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlObject, CURLOPT_POST, true);
            curl_setopt($curlObject, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $jwtToken,
                'accept: */*',
                'Content-Type: multipart/form-data'
            ]);

            $postFields = [
                'commitName' => 'abcd',
                'fileData' => curl_file_create($filePath),
                'repositoryName' => $repositoryName,
            ];

            if ($ciUploadId !== null) {
                $postFields['ciUploadId'] = $ciUploadId;
            }

            curl_setopt($curlObject, CURLOPT_POSTFIELDS, $postFields);

            $response = curl_exec($curlObject);
            if ($response === false) {
                $preprationResults[] = ['status' => 'error', 'response' => []];
            } else {
                $responseData = json_decode($response, true);
                if (isset($responseData['ciUploadId'])) {
                    $ciUploadId = $responseData['ciUploadId'];
                }
                $preprationResults[] = ['status' => 'success', 'response' => $response];
            }
        }
        curl_close($curlObject);
        $preprationResults['ciUploadId'] = $ciUploadId;
        $preprationResults['repositoryName'] = $repositoryName;

        return $preprationResults;
    }

    /**
     * Starts the scan for the files uploaded
     * 
     * @param string $ciUploadId CI upload ID
     * @param string $repositoryName Repository name
     * @param string $jwtToken JWT token for Debricked API
     * 
     * @return array
     */
    public function startScan(string $ciUploadId, string $repositoryName, string $jwtToken)
    {
        $scanResults = [];
        $scanUrl = $_ENV['DEBRICKED_API_SCAN_URL'];

        $curlObject = curl_init();

        curl_setopt($curlObject, CURLOPT_URL, $scanUrl);
        curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlObject, CURLOPT_POST, true);

        curl_setopt($curlObject, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $jwtToken,
            'accept: application/json',
            'Content-Type: multipart/form-data'
        ]);

        $postFields = [
            'commitName' => 'abcd',
            'ciUploadId' => $ciUploadId,
            'repositoryName' => $repositoryName,
            'debrickedConfig' => json_encode([
                'overrides' => [
                    'pURL' => 'string',
                    'version' => 'string',
                    'fileRegexes' => ['string']
                ]
            ]),
            'experimental' => 'true',
            'returnCommitData' => 'false',
            'debrickedIntegration' => 'null',
            'integrationName' => 'null',
        ];

        curl_setopt($curlObject, CURLOPT_POSTFIELDS, $postFields);

        $response = curl_exec($curlObject);
        if ($response === false) {
            $scanResults[] = ['status' => 'error', 'response' => []];
        } else {
            $responseData = json_decode($response, true);
            $scanResults[] = ['status' => 'success', 'response' => $responseData];
        }
        curl_close($curlObject);

        return $scanResults;
    }
}