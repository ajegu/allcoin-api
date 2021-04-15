<?php


namespace AllCoin\Process\AssetPair;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\DataMapper\AssetPairMapper;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Exception\AssetPair\AssetPairUpdateException;
use AllCoin\Process\ProcessInterface;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use AllCoin\Service\DateTimeService;
use JetBrains\PhpStorm\Pure;
use Psr\Log\LoggerInterface;

class AssetPairUpdateProcess extends AbstractAssetPairProcess implements ProcessInterface
{
    #[Pure] public function __construct(
        protected AssetRepositoryInterface $assetRepository,
        protected AssetPairRepositoryInterface $assetPairRepository,
        protected LoggerInterface $logger,
        private DateTimeService $dateTimeService,
        protected AssetPairMapper $assetPairMapper
    )
    {
        parent::__construct(
            $assetRepository,
            $assetPairRepository,
            $logger,
            $assetPairMapper,
        );
    }

    /**
     * @param RequestDtoInterface|null $dto
     * @param array $params
     * @return ResponseDtoInterface
     * @throws AssetPairUpdateException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ResponseDtoInterface
    {
        $assetId = $this->getAssetId($params, AssetPairUpdateException::class);
        $assetPairId = $this->getAssetPairId($params, AssetPairUpdateException::class);

        $asset = $this->getAsset($assetId, AssetPairUpdateException::class);

        try {
            $assetPair = $this->assetPairRepository->findOneById($assetPairId);
        } catch (ItemReadException $exception) {
            $message = 'The asset pair cannot be found!';
            $this->logger->error($message, [
                'id' => $assetPairId,
                'exception' => $exception->getMessage()
            ]);
            throw new AssetPairUpdateException($message);
        }

        $assetPair->setAsset($asset);
        $assetPair->setName($dto->getName());
        $assetPair->setUpdatedAt($this->dateTimeService->now());

        try {
            $this->assetPairRepository->save($assetPair);
        } catch (ItemSaveException $exception) {
            $message = 'The asset pair cannot be saved!';
            $this->logger->error($message, [
                'id' => $assetPairId,
                'exception' => $exception->getMessage()
            ]);
            throw new AssetPairUpdateException($message);
        }

        return $this->assetPairMapper->mapModelToResponseDto($assetPair);
    }


}
