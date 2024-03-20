<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use App\Repository\ExchangeRateRepository;

#[Route('/api', name: 'api')]
class CurrencyController extends AbstractController
{
    /**
     * @var Serializer
     */
    protected Serializer $serializer;

    /**
     * @param EntityManagerInterface $entityManager
     * @param ExchangeRateRepository $exchangeRateRepository
     */
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected ExchangeRateRepository $exchangeRateRepository
    ) {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    #[Route('/currency/{currencyCode}', name: 'currency_get', methods: ['get'] )]
    public function getCurrencyInfo(string $currencyCode): Response
    {
        if (!$currencyCode) {
            return $this->failedRequestResponse(500, 'Missing currency code!');
        }

        $result = $this->exchangeRateRepository->findBy(['target_currency' => $currencyCode]);

        if (!$result) {
            return $this->failedRequestResponse(404, 'No records!');
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->setContent($this->serializer->serialize($result, 'json'));

        return $response;
    }

    /**
     * @param $statusCode
     * @param $message
     *
     * @return Response
     */
    private function failedRequestResponse($statusCode, $message): Response
    {
        $response = new Response();
        $response->setStatusCode($statusCode);
        $response->setContent(json_encode(['error' => $message]));

        return $response;
    }
}
