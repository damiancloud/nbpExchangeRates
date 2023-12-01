<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;


class NbpExchangeRatesTest extends WebTestCase
{
    private function request(string $url): KernelBrowser
    {
        $client = static::createClient();
        $client->request(
            'GET',
            $url,
            [],
            [],
            ['HTTP_X-TOKEN-SYSTEM' => $_ENV['APP_REQUIRED_TOKEN']]
        );

        return $client;
    }

    public function testRequestWithXTokenSystem()
    {
        $client = static::createClient();
        $testCases = [
            ['currency' => 'usd', 'startDate' => '2023-11-28', 'endDate' => '2023-11-29'],
            ['currency' => 'chf', 'startDate' => '2023-11-28', 'endDate' => '2023-11-29'],
            ['currency' => 'EUR', 'startDate' => '2023-11-28', 'endDate' => '2023-11-29'],
            ['currency' => 'eur', 'startDate' => '2023-11-28', 'endDate' => '2023-11-29'],
        ];

        foreach ($testCases as $testCase) {
            $client->request(
                'GET',
                sprintf('/api/exchange-rates/%s/%s/%s', $testCase['currency'], $testCase['startDate'], $testCase['endDate']),
                [],
                [],
                ['HTTP_X-TOKEN-SYSTEM' => $_ENV['APP_REQUIRED_TOKEN']]
            );

            $response = $client->getResponse();
            $content = json_decode($response->getContent());
            $this->assertIsArray($content);
            $this->assertResponseIsSuccessful();
            $this->assertResponseHeaderSame('Content-Type', 'application/json');
        }
    }

    public function testRequestWithoutXTokenSystem()
    {
        $client = static::createClient();
        $client->request(
            'GET',
            '/api/exchange-rates/eur/2023-11-01/2023-11-07',
            [],
            [],
            []
        );
        $this->assertResponseStatusCodeSame(401);
    }

    public function testRequest()
    {
        $client = $this->request('/api/exchange-rates/usd/2023-11-29/2023-11-29');
        $this->assertResponseStatusCodeSame(200);

        $response = $client->getResponse();
        $content = json_decode($response->getContent());
 
        $this->assertIsArray($content);
        $this->assertResponseIsSuccessful();

        $expectedData = [
            (object)[
                'EffectiveDate' => '2023-11-29',
                'Bid' => '3.9152',
                'Ask' => '3.9942',
                'BidDifference' => -0.029,
                'AskDifference' => -0.03,
            ],
        ];

        $this->assertEquals($expectedData, $content);
    }

    public function testDateRangeLimit()
    {
        $client = $this->request('/api/exchange-rates/usd/2023-11-21/2023-11-29');
        $this->assertResponseStatusCodeSame(400);

        $response = $client->getResponse();
        $this->assertStringContainsString('Date range exceeds the maximum allowed range of 7 days.', $response->getContent());
    }

    public function testValidCurrency()
    {
        $client = $this->request('/api/exchange-rates/usdd/2023-11-21/2023-11-29');
        $this->assertResponseStatusCodeSame(400);

        $response = $client->getResponse();
        $this->assertStringContainsString('Unsupported currency.', $response->getContent());
    }

    public function testValidEndDate()
    {
        $client = $this->request('/api/exchange-rates/usd/2023-11-21/2023-11-42');
        $this->assertResponseStatusCodeSame(400);

        $response = $client->getResponse();
        $this->assertStringContainsString('Failed to parse time string (2023-11-42) at position 9 (2): Unexpected character', $response->getContent());
    }

    public function testEndDateInFuture()
    {
        $endDate = new \DateTime();
        $client = $this->request('/api/exchange-rates/usd/2023-11-29/' . $endDate->modify("+5 day")->format('Y-m-d'));
        $this->assertResponseStatusCodeSame(400);

        $response = $client->getResponse();
        $this->assertStringContainsString('Invalid date range', $response->getContent());
    }
}
