<?php


namespace AllCoin\Process\Binance;


use AllCoin\Builder\AssetBuilder;
use AllCoin\Builder\AssetPairBuilder;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Exception\Binance\BinanceSyncAssetException;
use AllCoin\Model\Asset;
use AllCoin\Model\AssetPair;
use AllCoin\Process\ProcessInterface;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use Http\Client\HttpClient;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Http\Response;
use Nyholm\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;

class BinanceSyncAssetProcess implements ProcessInterface
{
    const BINANCE_URI = 'https://www.binance.com/bapi/asset/v2/public/asset-service/product/get-products?includeEtf=true';

    const NEEDED_ASSET_PAIRS = ['USDT'];

    public function __construct(
        private HttpClient $client,
        private AssetRepositoryInterface $assetRepository,
        private AssetPairRepositoryInterface $assetPairRepository,
        private LoggerInterface $logger,
        private AssetBuilder $assetBuilder,
        private AssetPairBuilder $assetPairBuilder
    )
    {
    }

    /**
     * @param RequestDtoInterface|null $dto
     * @param array $params
     * @return ResponseDtoInterface|null
     * @throws BinanceSyncAssetException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ?ResponseDtoInterface
    {
        $request = new Request(
            method: IlluminateRequest::METHOD_GET,
            uri: self::BINANCE_URI
        );

        try {
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            $message = 'Cannot fetch the symbol from Binance!';
            $this->logger->error($message, [
                'url' => self::BINANCE_URI,
                'message' => $exception->getMessage()
            ]);
            throw new BinanceSyncAssetException($message);
        }

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $result = json_decode($response->getBody(), true);

            foreach ($result['data'] as $symbol) {
                $assetName = $symbol['b'];
                $assetPairName = $symbol['q'];

                if (!in_array($assetPairName, self::NEEDED_ASSET_PAIRS)) {
                    continue;
                }

                if (!$asset = $this->existsAsset($assetName)) {
                    $asset = $this->createAsset($assetName);
                }

                if (null === $this->getAssetPair($asset->getId(), $assetPairName)) {
                    $this->createAssetPair($asset, $assetPairName);
                }
            }
        } else {
            $this->logger->warning(
                'The assets could not be sync for Binance',
                [
                    'response' => (string)$response->getBody()
                ]
            );
        }

        return null;
    }

    /**
     * @param string $assetName
     * @return ?Asset
     * @throws BinanceSyncAssetException
     */
    private function existsAsset(string $assetName): ?Asset
    {
        try {
            return $this->assetRepository->existsByName($assetName);
        } catch (ItemReadException $exception) {
            $message = 'The asset cannot be verify during Binance sync!';
            $this->logger->error($message, [
                'name' => $assetName,
                'message' => $exception->getMessage()
            ]);
            throw new BinanceSyncAssetException($message);
        }
    }

    /**
     * @param string $base
     * @return Asset
     * @throws BinanceSyncAssetException
     */
    private function createAsset(string $base): Asset
    {
        $asset = $this->assetBuilder->build($base);

        try {
            $this->assetRepository->save($asset);
        } catch (ItemSaveException $exception) {
            $message = 'The asset cannot be save during Binance sync!';
            $this->logger->error($message, [
                'base' => $base,
                'message' => $exception->getMessage()
            ]);
            throw new BinanceSyncAssetException($message);
        }

        return $asset;
    }

    /**
     * @param string $assetId
     * @param string $assetPairName
     * @return AssetPair|null
     * @throws BinanceSyncAssetException
     */
    private function getAssetPair(string $assetId, string $assetPairName): ?AssetPair
    {
        try {
            $assetPairs = $this->assetPairRepository->findAllByAssetId($assetId);
        } catch (ItemReadException $exception) {
            $message = 'The asset pair cannot be find during Binance sync!';
            $this->logger->error($message, [
                'assetId' => $assetId,
                'message' => $exception->getMessage()
            ]);
            throw new BinanceSyncAssetException($message);
        }

        foreach ($assetPairs as $assetPair) {
            if ($assetPair->getName() === $assetPairName) {
                return $assetPair;
            }
        }

        return null;
    }

    /**
     * @param Asset $asset
     * @param string $assetPairName
     * @throws BinanceSyncAssetException
     */
    private function createAssetPair(Asset $asset, string $assetPairName): void
    {
        $assetPair = $this->assetPairBuilder->build($assetPairName);

        try {
            $this->assetPairRepository->save($assetPair, $asset->getId());
        } catch (ItemSaveException $exception) {
            $message = 'The asset pair cannot be save during Binance sync!';
            $this->logger->error($message, [
                'assetPairName' => $assetPairName,
                'message' => $exception->getMessage()
            ]);
            throw new BinanceSyncAssetException($message);
        }
    }
}
