<?php


namespace Test\AllCoin\Process\Binance;


use AllCoin\Builder\AssetBuilder;
use AllCoin\Builder\AssetPairBuilder;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Exception\Binance\BinanceSyncAssetException;
use AllCoin\Model\Asset;
use AllCoin\Model\AssetPair;
use AllCoin\Process\Binance\BinanceSyncAssetProcess;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use Http\Client\HttpClient;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Http\Response;
use Nyholm\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Test\TestCase;

class BinanceSyncAssetProcessTest extends TestCase
{
    private BinanceSyncAssetProcess $binanceSyncAssetProcess;

    private HttpClient $client;
    private AssetRepositoryInterface $assetRepository;
    private AssetPairRepositoryInterface $assetPairRepository;
    private LoggerInterface $logger;
    private AssetBuilder $assetBuilder;
    private AssetPairBuilder $assetPairBuilder;

    public function setUp(): void
    {
        $this->client = $this->createMock(HttpClient::class);
        $this->assetRepository = $this->createMock(AssetRepositoryInterface::class);
        $this->assetPairRepository = $this->createMock(AssetPairRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->assetBuilder = $this->createMock(AssetBuilder::class);
        $this->assetPairBuilder = $this->createMock(AssetPairBuilder::class);

        $this->binanceSyncAssetProcess = new BinanceSyncAssetProcess(
            $this->client,
            $this->assetRepository,
            $this->assetPairRepository,
            $this->logger,
            $this->assetBuilder,
            $this->assetPairBuilder,
        );
    }

    public function testHandleWithRequestErrorShouldThrowException(): void
    {
        $request = new Request(
            method: IlluminateRequest::METHOD_GET,
            uri: BinanceSyncAssetProcess::BINANCE_URI
        );

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with($request)
            ->willThrowException($this->createMock(ClientExceptionInterface::class));

        $this->logger->expects($this->once())->method('error');
        $this->expectException(BinanceSyncAssetException::class);

        $this->assetRepository->expects($this->never())->method('existsByName');
        $this->assetBuilder->expects($this->never())->method('build');
        $this->assetRepository->expects($this->never())->method('save');
        $this->assetPairRepository->expects($this->never())->method('findAllByAssetId');
        $this->assetPairBuilder->expects($this->never())->method('build');
        $this->assetPairRepository->expects($this->never())->method('save');

        $this->binanceSyncAssetProcess->handle();
    }

    /**
     * @throws BinanceSyncAssetException
     */
    public function testHandleWithBadStatusCodeShouldStop(): void
    {
        $request = new Request(
            method: IlluminateRequest::METHOD_GET,
            uri: BinanceSyncAssetProcess::BINANCE_URI
        );

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(Response::HTTP_I_AM_A_TEAPOT);

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $this->logger->expects($this->once())->method('warning');

        $this->assetRepository->expects($this->never())->method('existsByName');
        $this->assetBuilder->expects($this->never())->method('build');
        $this->assetRepository->expects($this->never())->method('save');
        $this->assetPairRepository->expects($this->never())->method('findAllByAssetId');
        $this->assetPairBuilder->expects($this->never())->method('build');
        $this->assetPairRepository->expects($this->never())->method('save');

        $this->binanceSyncAssetProcess->handle();
    }

    /**
     * @throws BinanceSyncAssetException
     */
    public function testHandleWithNotNeedAssetPairShouldStop(): void
    {
        $request = new Request(
            method: IlluminateRequest::METHOD_GET,
            uri: BinanceSyncAssetProcess::BINANCE_URI
        );

        $symbols = [
            'data' => [
                [
                    'b' => 'foo',
                    'q' => 'bar'
                ]
            ]
        ];

        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
            ->method('__toString')
            ->willReturn(json_encode($symbols));

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $response->expects($this->once())->method('getBody')->willReturn($body);

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $this->logger->expects($this->never())->method('error');
        $this->assetRepository->expects($this->never())->method('existsByName');
        $this->assetBuilder->expects($this->never())->method('build');
        $this->assetRepository->expects($this->never())->method('save');
        $this->assetPairRepository->expects($this->never())->method('findAllByAssetId');
        $this->assetPairBuilder->expects($this->never())->method('build');
        $this->assetPairRepository->expects($this->never())->method('save');

        $this->binanceSyncAssetProcess->handle();
    }

    public function testHandleWithAssetReadErrorShouldThrowException(): void
    {
        $request = new Request(
            method: IlluminateRequest::METHOD_GET,
            uri: BinanceSyncAssetProcess::BINANCE_URI
        );

        $assetName = 'foo';
        $assetPairName = 'USDT';
        $symbols = [
            'data' => [
                [
                    'b' => $assetName,
                    'q' => $assetPairName
                ]
            ]
        ];

        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
            ->method('__toString')
            ->willReturn(json_encode($symbols));

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $response->expects($this->once())->method('getBody')->willReturn($body);

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $this->assetRepository->expects($this->once())
            ->method('existsByName')
            ->with($assetName)
            ->willThrowException($this->createMock(ItemReadException::class));

        $this->logger->expects($this->once())->method('error');
        $this->expectException(BinanceSyncAssetException::class);

        $this->assetBuilder->expects($this->never())->method('build');
        $this->assetRepository->expects($this->never())->method('save');
        $this->assetPairRepository->expects($this->never())->method('findAllByAssetId');
        $this->assetPairBuilder->expects($this->never())->method('build');
        $this->assetPairRepository->expects($this->never())->method('save');

        $this->binanceSyncAssetProcess->handle();
    }

    public function testHandleWithAssetSaveErrorShouldThrowException(): void
    {
        $request = new Request(
            method: IlluminateRequest::METHOD_GET,
            uri: BinanceSyncAssetProcess::BINANCE_URI
        );

        $assetName = 'foo';
        $assetPairName = 'USDT';
        $symbols = [
            'data' => [
                [
                    'b' => $assetName,
                    'q' => $assetPairName
                ]
            ]
        ];

        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
            ->method('__toString')
            ->willReturn(json_encode($symbols));

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $response->expects($this->once())->method('getBody')->willReturn($body);

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $this->assetRepository->expects($this->once())
            ->method('existsByName')
            ->with($assetName)
            ->willReturn(null);

        $asset = $this->createMock(Asset::class);
        $this->assetBuilder->expects($this->once())
            ->method('build')
            ->with($assetName)
            ->willReturn($asset);

        $this->assetRepository->expects($this->once())
            ->method('save')
            ->willThrowException($this->createMock(ItemSaveException::class));

        $this->logger->expects($this->once())->method('error');
        $this->expectException(BinanceSyncAssetException::class);

        $this->assetPairRepository->expects($this->never())->method('findAllByAssetId');
        $this->assetPairBuilder->expects($this->never())->method('build');
        $this->assetPairRepository->expects($this->never())->method('save');

        $this->binanceSyncAssetProcess->handle();
    }

    public function testHandleWithAssetPairReadErrorShouldThrowException(): void
    {
        $request = new Request(
            method: IlluminateRequest::METHOD_GET,
            uri: BinanceSyncAssetProcess::BINANCE_URI
        );

        $assetName = 'foo';
        $assetPairName = 'USDT';
        $symbols = [
            'data' => [
                [
                    'b' => $assetName,
                    'q' => $assetPairName
                ]
            ]
        ];

        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
            ->method('__toString')
            ->willReturn(json_encode($symbols));

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $response->expects($this->once())->method('getBody')->willReturn($body);

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $this->assetRepository->expects($this->once())
            ->method('existsByName')
            ->with($assetName)
            ->willReturn(null);

        $asset = $this->createMock(Asset::class);
        $assetId = 'foo';
        $asset->expects($this->once())->method('getId')->willReturn($assetId);

        $this->assetBuilder->expects($this->once())
            ->method('build')
            ->with($assetName)
            ->willReturn($asset);

        $this->assetRepository->expects($this->once())
            ->method('save');

        $this->assetPairRepository->expects($this->once())
            ->method('findAllByAssetId')
            ->with($assetId)
            ->willThrowException($this->createMock(ItemReadException::class));

        $this->logger->expects($this->once())->method('error');
        $this->expectException(BinanceSyncAssetException::class);

        $this->assetPairBuilder->expects($this->never())->method('build');
        $this->assetPairRepository->expects($this->never())->method('save');

        $this->binanceSyncAssetProcess->handle();
    }

    public function testHandleWithAssetPairSaveErrorShouldThrowException(): void
    {
        $request = new Request(
            method: IlluminateRequest::METHOD_GET,
            uri: BinanceSyncAssetProcess::BINANCE_URI
        );

        $assetName = 'foo';
        $assetPairName = 'USDT';
        $symbols = [
            'data' => [
                [
                    'b' => $assetName,
                    'q' => $assetPairName
                ]
            ]
        ];

        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
            ->method('__toString')
            ->willReturn(json_encode($symbols));

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $response->expects($this->once())->method('getBody')->willReturn($body);

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $this->assetRepository->expects($this->once())
            ->method('existsByName')
            ->with($assetName)
            ->willReturn(null);

        $asset = $this->createMock(Asset::class);
        $assetId = 'foo';
        $asset->expects($this->exactly(2))->method('getId')->willReturn($assetId);

        $this->assetBuilder->expects($this->once())
            ->method('build')
            ->with($assetName)
            ->willReturn($asset);

        $this->assetRepository->expects($this->once())
            ->method('save');

        $assetPair = $this->createMock(AssetPair::class);
        $assetPairName = 'foo';
        $assetPair->expects($this->once())->method('getName')->willReturn($assetPairName);

        $this->assetPairRepository->expects($this->once())
            ->method('findAllByAssetId')
            ->with($assetId)
            ->willReturn([$assetPair]);

        $newAssetPair = $this->createMock(AssetPair::class);
        $this->assetPairBuilder->expects($this->once())
            ->method('build')
            ->willReturn($newAssetPair);

        $this->assetPairRepository->expects($this->once())
            ->method('save')
            ->with($newAssetPair, $assetId)
            ->willThrowException($this->createMock(ItemSaveException::class));

        $this->logger->expects($this->once())->method('error');
        $this->expectException(BinanceSyncAssetException::class);

        $this->binanceSyncAssetProcess->handle();
    }

    /**
     * @throws BinanceSyncAssetException
     */
    public function testHandleShouldBeOKWithNonExistingAssetPair(): void
    {
        $request = new Request(
            method: IlluminateRequest::METHOD_GET,
            uri: BinanceSyncAssetProcess::BINANCE_URI
        );

        $assetName = 'foo';
        $assetPairName = 'USDT';
        $symbols = [
            'data' => [
                [
                    'b' => $assetName,
                    'q' => $assetPairName
                ]
            ]
        ];

        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
            ->method('__toString')
            ->willReturn(json_encode($symbols));

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $response->expects($this->once())->method('getBody')->willReturn($body);

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $this->assetRepository->expects($this->once())
            ->method('existsByName')
            ->with($assetName)
            ->willReturn(null);

        $asset = $this->createMock(Asset::class);
        $assetId = 'foo';
        $asset->expects($this->exactly(2))->method('getId')->willReturn($assetId);

        $this->assetBuilder->expects($this->once())
            ->method('build')
            ->with($assetName)
            ->willReturn($asset);

        $this->assetRepository->expects($this->once())
            ->method('save');

        $assetPair = $this->createMock(AssetPair::class);
        $assetPairName = 'foo';
        $assetPair->expects($this->once())->method('getName')->willReturn($assetPairName);

        $this->assetPairRepository->expects($this->once())
            ->method('findAllByAssetId')
            ->with($assetId)
            ->willReturn([$assetPair]);

        $newAssetPair = $this->createMock(AssetPair::class);
        $this->assetPairBuilder->expects($this->once())
            ->method('build')
            ->willReturn($newAssetPair);

        $this->assetPairRepository->expects($this->once())
            ->method('save')
            ->with($newAssetPair, $assetId);

        $this->logger->expects($this->never())->method('error');

        $this->binanceSyncAssetProcess->handle();
    }

    /**
     * @throws BinanceSyncAssetException
     */
    public function testHandleShouldBeOKWithExistingAssetPair(): void
    {
        $request = new Request(
            method: IlluminateRequest::METHOD_GET,
            uri: BinanceSyncAssetProcess::BINANCE_URI
        );

        $assetName = 'foo';
        $assetPairName = 'USDT';
        $symbols = [
            'data' => [
                [
                    'b' => $assetName,
                    'q' => $assetPairName
                ]
            ]
        ];

        $body = $this->createMock(StreamInterface::class);
        $body->expects($this->once())
            ->method('__toString')
            ->willReturn(json_encode($symbols));

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $response->expects($this->once())->method('getBody')->willReturn($body);

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $this->assetRepository->expects($this->once())
            ->method('existsByName')
            ->with($assetName)
            ->willReturn(null);

        $asset = $this->createMock(Asset::class);
        $assetId = 'foo';
        $asset->expects($this->once())->method('getId')->willReturn($assetId);

        $this->assetBuilder->expects($this->once())
            ->method('build')
            ->with($assetName)
            ->willReturn($asset);

        $this->assetRepository->expects($this->once())
            ->method('save');

        $assetPair = $this->createMock(AssetPair::class);
        $assetPairName = 'USDT';
        $assetPair->expects($this->once())->method('getName')->willReturn($assetPairName);

        $this->assetPairRepository->expects($this->once())
            ->method('findAllByAssetId')
            ->with($assetId)
            ->willReturn([$assetPair]);

        $this->assetPairBuilder->expects($this->never())->method('build');
        $this->assetPairRepository->expects($this->never())->method('save');

        $this->logger->expects($this->never())->method('error');

        $this->binanceSyncAssetProcess->handle();
    }
}
