<?php


namespace AllCoin\Process\Binance;


use Ajegu\BinanceSdk\Client;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Model\AssetPairPrice;
use AllCoin\Process\ProcessInterface;
use AllCoin\Repository\AssetPairPriceRepositoryInterface;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use Psr\Log\LoggerInterface;

class BinancePriceSyncProcess implements ProcessInterface
{
    public function __construct(
        private Client $client,
        private AssetRepositoryInterface $assetRepository,
        private AssetPairRepositoryInterface $assetPairRepository,
        private AssetPairPriceRepositoryInterface $assetPairPriceRepository,
        private LoggerInterface $logger
    )
    {
    }

    /**
     * @param RequestDtoInterface|null $dto
     * @param array $params
     * @return ResponseDtoInterface|null
     * @throws ItemReadException
     * @throws ItemSaveException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ?ResponseDtoInterface
    {
        $assets = $this->assetRepository->findAll();

        $this->logger->debug(
            count($assets) . ' found for get price'
        );

        foreach ($assets as $asset) {

            $assetName = $asset->getName();
            $this->logger->debug("Get pair for asset {$assetName}");

            $assetPairs = $this->assetPairRepository->findAllByAssetId($asset->getId());

            foreach ($assetPairs as $assetPair) {
                $symbol = strtoupper($assetName . $assetPair->getName());
                $this->logger->debug("Get price for symbol {$symbol}");
                $assetPairPrice = $this->getAssetPairPrice($symbol);
                $assetPairPrice->setAssetPair($assetPair);
                $this->assetPairPriceRepository->save($assetPairPrice);
            }
        }

        return null;
    }

    /**
     * @param string $symbol
     * @return AssetPairPrice
     */
    private function getAssetPairPrice(string $symbol): AssetPairPrice
    {
        $bookTicker = $this->client->getBookTicker(['symbol' => $symbol]);

        return new AssetPairPrice(
            bidPrice: $bookTicker->getBidPrice(),
            askPrice: $bookTicker->getAskPrice()
        );
    }
}
