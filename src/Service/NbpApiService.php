<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Serializer\SerializerInterface;

class NbpApiService
{
    private const BASE_URL = 'http://api.nbp.pl/api/exchangerates/';

    public function __construct(
        private HttpClientInterface $httpClient, 
        private SerializerInterface $serializer
    ) {
    }

    /**
     * @param string $currency
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * 
     * @return array
     */
    public function getData(string $currency, \DateTime $startDate, \DateTime $endDate): array
    {
        $method = 'rates';
        $table = 'c';
        $queryParam = '?format=xml';

        $path = $method . '/' . $table . '/' . $currency . '/' . $startDate->format('Y-m-d') . '/' . $endDate->format('Y-m-d') . '/' . $queryParam;
        $response = $this->httpClient->request('GET', self::BASE_URL . $path, ['headers' => [
            'Accept' => 'application/xml',
            'Accept-Charset' => 'utf-8',
            ],
        ]);

        $responseContent = $response->getContent();
        $responseCharset = $response->getInfo('response_headers')['content-type'][1] ?? null;

        if ($responseCharset && !mb_check_encoding($responseContent, 'UTF-8')) {
            $responseContent = mb_convert_encoding($responseContent, 'UTF-8', $responseCharset);
        }

        $arrayData = $this->serializer->decode($responseContent, 'xml');

        return $arrayData;
    }

    /**
     * @param array $rates
     * 
     * @return array
     */
    public function calculateRates(array $rates): array
    {
        $result = [];
        foreach ($rates as $key => $rate) {
            if ($key > 0) {
                $previousRate = $rates[$key - 1];

                $bid = $rate['Bid'];
                $ask = $rate['Ask'];

                $result[] = [
                    'EffectiveDate' => $rate['EffectiveDate'],
                    'Bid' => $bid,
                    'Ask' => $ask,
                    'BidDifference' => round($bid - $previousRate['Bid'], 3),
                    'AskDifference' => round($ask - $previousRate['Ask'], 3),
                ];
            }
        }

        return $result;
    }
}