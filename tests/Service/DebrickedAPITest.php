<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use App\Service\DebrickedAPI;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class DebrickedAPITest extends TestCase
{
    private $httpClientMock;
    private $paramsMock;
    private $debrickedApi;

    protected function setUp(): void
    {
        $this->httpClientMock = $this->createMock(HttpClientInterface::class);
        $this->paramsMock = $this->createMock(ParameterBagInterface::class);
        $this->paramsMock->method('get')->willReturn('/uploads');

        $this->debrickedApi = new DebrickedAPI(
            $this->httpClientMock,
            $this->paramsMock
        );
    }

    public function testGetJwtTokenSuccess()
    {
        $expectedToken = 'asdfghjkl';
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(200);
        $responseMock->method('toArray')->willReturn(['token' => $expectedToken]);

        $this->httpClientMock->method('request')->willReturn($responseMock);

        $token = $this->debrickedApi->getJwtToken();
        $this->assertEquals($expectedToken, $token);
    }

    public function testGetJwtTokenFailure()
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(500);

        $this->httpClientMock->method('request')->willReturn($responseMock);

        $token = $this->debrickedApi->getJwtToken();
        $this->assertNull($token);
    }

    public function testStartScan()
    {
        $ciUploadId = '12345';
        $repositoryName = 'abcd';
        $jwtToken = 'asdfghjkl';

        // Mock the response of the HTTP request for starting the scan
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockResponse->method('toArray')->willReturn(['status' => 'success', 'response' => ['ciUploadId'=> $ciUploadId]]);

        $this->httpClientMock->method('request')
            ->willReturn($mockResponse);

        $result = $this->debrickedApi->startScan($ciUploadId, $repositoryName, $jwtToken);

        $this->assertArrayHasKey('status', $result[0]);
        $this->assertEquals('success', $result[0]['status']);
    }
}
