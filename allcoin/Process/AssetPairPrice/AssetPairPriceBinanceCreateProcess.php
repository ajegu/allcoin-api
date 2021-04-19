<?php


namespace AllCoin\Process\AssetPairPrice;


use Ajegu\BinanceSdk\Client;
use Ajegu\BinanceSdk\Exception\UnexpectedStatusCodeException;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Exception\AssetPairPrice\AssetPairPriceBinanceCreateException;
use AllCoin\Model\Asset;
use AllCoin\Model\AssetPair;
use AllCoin\Model\AssetPairPrice;
use AllCoin\Process\ProcessInterface;
use AllCoin\Repository\AssetPairPriceRepositoryInterface;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use Psr\Log\LoggerInterface;

class AssetPairPriceBinanceCreateProcess implements ProcessInterface
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
     * @throws AssetPairPriceBinanceCreateException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ?ResponseDtoInterface
    {
        $assets = $this->getAssets();

        $this->logger->debug(
            count($assets) . ' found for get price'
        );

        foreach ($assets as $asset) {

            $assetName = $asset->getName();
            $this->logger->debug("Get pair for asset {$assetName}");

            $assetPairs = $this->getAssetPairs($asset->getId());

            foreach ($assetPairs as $assetPair) {
                $symbol = strtoupper($assetName . $assetPair->getName());
                $this->logger->debug("Get price for symbol {$symbol}");
                if ($assetPairPrice = $this->getAssetPairPrice($symbol)) {
                    $assetPairPrice->setAssetPair($assetPair);
                    $this->save($assetPairPrice);
                    $this->logger->debug("New price saved!");
                } else {
                    $this->logger->debug("No price found!");
                }
            }
        }

        return null;
    }

    /**
     * @return Asset[]
     * @throws AssetPairPriceBinanceCreateException
     */
    private function getAssets(): array
    {
        try {
            return $this->assetRepository->findAll();
        } catch (ItemReadException $exception) {
            $message = 'Cannot get the assets for Binance process';
            $this->logger->error($message, [
                'exception' => $exception->getMessage()
            ]);
            throw new AssetPairPriceBinanceCreateException($message);
        }
    }

    /**
     * @param string $assetId
     * @return AssetPair[]
     * @throws AssetPairPriceBinanceCreateException
     */
    private function getAssetPairs(string $assetId): array
    {
        try {
            return $this->assetPairRepository->findAllByAssetId($assetId);
        } catch (ItemReadException $exception) {
            $message = 'Cannot get the asset pairs for Binance process.';
            $this->logger->error($message, [
                'exception' => $exception->getMessage(),
                'assetId' => $assetId
            ]);
            throw new AssetPairPriceBinanceCreateException($message);
        }
    }

    /**
     * @param string $symbol
     * @return AssetPairPrice|null
     * @throws AssetPairPriceBinanceCreateException
     */
    private function getAssetPairPrice(string $symbol): ?AssetPairPrice
    {
        try {
            $bookTicker = $this->client->getBookTicker(['symbol' => $symbol]);
        } catch (UnexpectedStatusCodeException $exception) {
            $message = 'Cannot get the book ticker from Binance.';
            $this->logger->error($message, [
                'exception' => $exception->getMessage()
            ]);
            throw new AssetPairPriceBinanceCreateException($message);
        }

        return new AssetPairPrice(
            bidPrice: $bookTicker->getBidPrice(),
            askPrice: $bookTicker->getAskPrice()
        );
    }

    /**
     * @param AssetPairPrice $assetPairPrice
     * @throws AssetPairPriceBinanceCreateException
     */
    private function save(AssetPairPrice $assetPairPrice): void
    {
        try {
            $this->assetPairPriceRepository->save($assetPairPrice);
        } catch (ItemSaveException $exception) {
            $message = 'Cannot save the asset pair price for Binance process.';
            $this->logger->error($message, [
                'exception' => $exception->getMessage(),
                'assetPairId' => $assetPairPrice->getAssetPair()->getId()
            ]);
            throw new AssetPairPriceBinanceCreateException($message);
        }
    }
}
