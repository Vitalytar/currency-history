<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use App\Entity\ExchangeRate;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand('app:fetch-rates-data', 'Fetching currency rates from AnyAPI on a daily basis or manually')]
class FetchRatesDataCommand extends Command
{
    // Better to move API Key to DB somewhere, new table with configs
    private const API_KEY = 'm4eei5o452p07rcbc1hvg4i0idep4h9oifljdvmbk17a40q6u4hm8';
    protected const BASE_CURRENCY = 'EUR';
    protected const TARGET_CURRENCIES = [
        'GBP',
        'AUD',
        'USD'
    ];

    /**
     * @param HttpClientInterface    $httpClient
     * @param LoggerInterface        $logger
     * @param ExchangeRate           $exchangeRate
     * @param EntityManagerInterface $entityManager
     * @param string|null            $name
     */
    public function __construct(
        protected HttpClientInterface $httpClient,
        protected LoggerInterface $logger,
        protected ExchangeRate $exchangeRate,
        protected EntityManagerInterface $entityManager,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Fetch currency rates from the API')
            ->setHelp('This command fetch AnyAPI data about currencies');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->fetchRatesData();

        foreach ($result as $rateData) {
            $exchangeRate = new ExchangeRate();
            $exchangeRate->setBaseCurrency($rateData['base']);
            $exchangeRate->setTargetCurrency($rateData['to']);
            $exchangeRate->setRate($rateData['rate']);
            $exchangeRate->setFetchedAt(new \DateTimeImmutable());

            $this->entityManager->persist($exchangeRate);
            $this->entityManager->flush();
        }

        $output->writeln('CLI command executed successfully');

        return Command::SUCCESS;
    }

    /**
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    protected function fetchRatesData(): array
    {
        try {
            $apiKey = self::API_KEY;
            $baseCurrency = self::BASE_CURRENCY;
            $currenciesData = [];
            $response = $this->httpClient->request(
                'GET',
                "https://anyapi.io/api/v1/exchange/rates?apiKey=$apiKey&base=$baseCurrency"
            );

            if ($response->getStatusCode() !== 200) {
                $this->logger->error('ERROR | Not able to fetch currency rates!');

                return [];
            }

            $currencyData = $response->toArray();

            foreach (self::TARGET_CURRENCIES as $targetCurrency) {
                $currenciesData[] = [
                    'base' => $currencyData['base'],
                    'to' => $targetCurrency,
                    'rate' => $currencyData['rates'][$targetCurrency]
                ];
            }

            return $currenciesData;
        } catch (TransportExceptionInterface $e) {
            $this->logger->error($e->getMessage());

            return [];
        }
    }
}
