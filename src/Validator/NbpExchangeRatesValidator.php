<?php
namespace App\Validator;

use Symfony\Component\HttpFoundation\JsonResponse;

class NbpExchangeRatesValidator
{
    const SUPPORTED_CURRENCIES = ['EUR', 'USD', 'CHF'];
    const MAX_DATE_RANGE = 7;

    public function validateCurrency(string $currency): bool
    {
        if (!in_array($currency, self::SUPPORTED_CURRENCIES)) {
            throw new \InvalidArgumentException('Unsupported currency.');
        }

        return true;
    }

    /**
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * 
     * @return bool
     */
    public function validateDateRange(\DateTime $startDate, \DateTime $endDate): bool
    {
        $dateDiff = $endDate->diff($startDate)->days;

        if ($dateDiff > self::MAX_DATE_RANGE) {
            throw new \InvalidArgumentException('Date range exceeds the maximum allowed range of 7 days.');
        }

        return true;
    }

    /**
     * @param array $data
     * 
     * @return bool
     */
    public function validateResultDataRates(array $data): bool
    {
        if (!array_key_exists('Rates', $data) || !array_key_exists('Rate', $data['Rates'])) {
            throw new \InvalidArgumentException('Invalid data structure: Missing "Rates" or "Rate" key.');
        }

        return true;
    }
}