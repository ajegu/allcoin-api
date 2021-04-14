<?php


namespace AllCoin\Process\AssetPair;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\DataMapper\AssetPairMapper;
use AllCoin\Dto\AssetPairRequestDto;
use AllCoin\Dto\AssetPairResponseDto;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Exception\AssetPair\AssetPairCreateException;
use AllCoin\Model\AssetPair;
use AllCoin\Process\ProcessInterface;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use AllCoin\Service\DateTimeService;
use AllCoin\Service\UuidService;
use Psr\Log\LoggerInterface;

class AssetPairCreateProcess implements ProcessInterface
{
    public function __construct(
        private AssetRepositoryInterface $assetRepository,
        private AssetPairRepositoryInterface $assetPairRepository,
        private LoggerInterface $logger,
        private UuidService $uuidService,
        private DateTimeService $dateTimeService,
        private AssetPairMapper $assetPairMapper
    )
    {
    }

    /**
     * @param AssetPairRequestDto|null $dto
     * @param array $params
     * @return AssetPairResponseDto|null
     * @throws AssetPairCreateException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ?ResponseDtoInterface
    {
        $assetId = $params['assetId'] ?? false;
        if (!$assetId) {
            throw new AssetPairCreateException('The asset ID must be defined in $params');
        }

        try {
            $asset = $this->assetRepository->findOneById($assetId);
        } catch (ItemReadException $exception) {
            $message = 'The asset cannot be found!';
            $this->logger->error($message, [
                'id' => $assetId,
                'exception' => $exception->getMessage()
            ]);
            throw new AssetPairCreateException($message);
        }

        $assetPair = new AssetPair(
            asset: $asset,
            id: $this->uuidService->generateUuid(),
            name: $dto->getName(),
            createdAt: $this->dateTimeService->now()
        );

        try {
            $this->assetPairRepository->save($assetPair);
        } catch (ItemSaveException $exception) {
            $message = 'The asset pair cannot be saved!';
            $this->logger->error($message, [
                'id' => $assetId,
                'name' => $assetPair->getName(),
                'exception' => $exception->getMessage()
            ]);
            throw new AssetPairCreateException($message);
        }

        return $this->assetPairMapper->mapModelToResponseDto($assetPair);
    }

}
