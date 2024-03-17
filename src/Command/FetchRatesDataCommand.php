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

#[AsCommand('app:fetch-rates-data', 'Fetching currency rates from AnyAPI on a daily basis')]
class FetchRatesDataCommand extends Command
{
    // TODO: Move to DB configs somewhere
    private const API_KEY = 'm4eei5o452p07rcbc1hvg4i0idep4h9oifljdvmbk17a40q6u4hm8';
    protected const BASE_CURRENCY = 'EUR';
    protected const TARGET_CURRENCIES = [
        'GBP',
        'AUD',
        'USD'
    ];

    public function __construct(
        protected HttpClientInterface $httpClient,
        protected LoggerInterface $logger,
        protected ExchangeRate $exchangeRate,
        protected EntityManagerInterface $entityManager,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setDescription('Fetch currency rates from the API')
            ->setHelp('This command fetch AnyAPI data about currencies');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->fetchRatesData();
        $output->writeln('CLI command executed successfully');

        foreach ($result as $rateData) {
            $exchangeRate = new ExchangeRate();
            $exchangeRate->setBaseCurrency($rateData['base']);
            $exchangeRate->setTargetCurrency($rateData['to']);
            $exchangeRate->setRate($rateData['rate']);
            $exchangeRate->setFetchedAt(new \DateTimeImmutable());

            $this->entityManager->persist($exchangeRate);
            $this->entityManager->flush();
        }

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

            foreach (self::TARGET_CURRENCIES as $targetCurrency) {
                $response = $this->httpClient->request(
                    'GET',
                    "https://anyapi.io/api/v1/exchange/convert?apiKey=$apiKey&base=$baseCurrency&to=$targetCurrency&amount=10000"
                );

                if ($response->getStatusCode() !== 200) {
                    $this->logger->error('ERROR | Not able to fetch rates for - ' . $targetCurrency);
                    continue;
                }

                $currenciesData[] = $response->toArray();
            }

            return $currenciesData;
        } catch (TransportExceptionInterface $e) {
            $this->logger->error($e->getMessage());

            return [];
        }
    }
}
