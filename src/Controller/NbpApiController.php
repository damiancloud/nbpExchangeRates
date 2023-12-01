<?php
namespace App\Controller;

use App\Service\NbpApiService;
use App\Validator\NbpExchangeRatesValidator;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

// use Nelmio\ApiDocBundle\Annotation\Model;
// use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

class NbpApiController extends AbstractController
{
    public function __construct(private NbpApiService $nbpApiService, private NbpExchangeRatesValidator $nbpExchangeRatesValidator)
    {
    }

    /**
     * Get rates exchange from NBP API
     * 
     * Retrieves exchange rates for a specified currency within a given date range.
     * 
     * @param string $currency The currency code (e.g., EUR, USD, CHF).
     * @param string $startDate The start date in the format YYYY-MM-DD.
     * @param string $endDate The end date in the format YYYY-MM-DD.
     * 
     * @return Response
     */
    #[Route('/api/exchange-rates/{currency}/{startDate<^\d{4}-\d{2}-\d{2}$>}/{endDate<^\d{4}-\d{2}-\d{2}$>}', methods: ['GET'])]
    
    #[OA\Response(
        response: 200,
        description: 'Successful response with exchange rate data.'
    )]
    #[OA\Response(
        response: 400,
        description: 'Error response when input validation fails or there is an issue with the NBP API.'
    )]
    #[OA\Parameter(
        name:"currency",
        in:"path",
        required:true,
        description:"The currency code (e.g., EUR, USD, CHF)."
    )]
    #[OA\Parameter(
        name:"endDate",
        in:"path",
        required:true,
        description:"The end date in the format YYYY-MM-DD."
    )]
    #[OA\Parameter(
        name:"startDate",
        in:"path",
        required:true,
        description:"The start date in the format YYYY-MM-DD."
    )]
    public function getExchangeRates(string $currency, string $startDate, string $endDate): Response
    {
        $currency = strtoupper($currency);
        try {
            $this->nbpExchangeRatesValidator->validateCurrency($currency);
      
            $startDate = new \DateTime($startDate);
            $endDate = new \DateTime($endDate);
            $this->nbpExchangeRatesValidator->validateDateRange($startDate, $endDate);
    
            $data = $this->nbpApiService->getData($currency, $startDate->modify("-1 day"), $endDate);
            
            $this->nbpExchangeRatesValidator->validateResultDataRates($data);

            $data = $this->nbpApiService->calculateRates($data['Rates']['Rate']);

            $response = new JsonResponse($data);

            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } catch (\InvalidArgumentException | \Exception $e) {
            return new JsonResponse(['error' => iconv('ISO-8859-2', 'UTF-8//IGNORE', $e->getMessage())], 400);
        }
    }
}