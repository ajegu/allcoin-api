<?php


namespace Test\AllCoin\Process\AssetPairPrice;


use Ajegu\BinanceSdk\Client;
use Ajegu\BinanceSdk\Model\BookTickerResponse;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Model\Asset;
use AllCoin\Model\AssetPair;
use AllCoin\Model\AssetPairPrice;
use AllCoin\Process\AssetPairPrice\AssetPairPriceBinanceCreateProcess;
use AllCoin\Repository\AssetPairPriceRepositoryInterface;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class AssetPairPriceBinanceCreateProcessTest extends TestCase
{
    private AssetPairPriceBinanceCreateProcess $assetPairPriceBinanceCreateProcess;

    private Client $client;
    private AssetRepositoryInterface $assetRepository;
    private AssetPairRepositoryInterface $assetPairRepository;
    private AssetPairPriceRepositoryInterface $assetPairPriceRepository;
    private LoggerInterface $logger;

    public function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->assetRepository = $this->createMock(AssetRepositoryInterface::class);
        $this->assetPairRepository = $this->createMock(AssetPairRepositoryInterface::class);
        $this->assetPairPriceRepository = $this->createMock(AssetPairPriceRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->assetPairPriceBinanceCreateProcess = new AssetPairPriceBinanceCreateProcess(
            $this->client,
            $this->assetRepository,
            $this->assetPairRepository,
            $this->assetPairPriceRepository,
            $this->logger,
        );
    }

    /**
     * @throws ItemReadException
     * @throws ItemSaveException
     */
    public function testHandleShouldBeOK(): void
    {
        $asset = $this->createMock(Asset::class);
        $assetId = 'foo';
        $asset->expects($this->once())->method('getId')->willReturn($assetId);
        $assetName = 'bar';
        $asset->expects($this->once())->method('getName')->willReturn($assetName);

        $this->assetRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$asset]);

        $assetPair = $this->createMock(AssetPair::class);
        $assetPairName = 'foo';
        $assetPair->expects($this->once())->method('getName')->willReturn($assetPairName);

        $this->assetPairRepository->expects($this->once())
            ->method('findAllByAssetId')
            ->with($assetId)
            ->willReturn([$assetPair]);

        $symbol = strtoupper($assetName . $assetPairName);

        $bookTicker = $this->createMock(BookTickerResponse::class);
        $bidPrice = '1.2';
        $bookTicker->expects($this->once())->method('getBidPrice')->willReturn($bidPrice);
        $askPrice = '2.1';
        $bookTicker->expects($this->once())->method('getAskPrice')->willReturn($askPrice);

        $this->client->expects($this->once())
            ->method('getBookTicker')
            ->with(['symbol' => $symbol])
            ->willReturn($bookTicker);

        $assetPairPrice = new AssetPairPrice(
            bidPrice: $bidPrice,
            askPrice: $askPrice
        );
        $assetPairPrice->setAssetPair($assetPair);

        $this->assetPairPriceRepository->expects($this->once())
            ->method('save')
            ->with($assetPairPrice);

        $this->logger->expects($this->never())->method('error');

        $this->assetPairPriceBinanceCreateProcess->handle();
    }
}
