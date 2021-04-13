<?php


namespace AllCoin\Process\Asset;


use AllCoin\Database\DynamoDb\Exception\PersistenceException;
use AllCoin\Database\DynamoDb\Exception\ReadException;
use AllCoin\DataMapper\AssetMapper;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Exception\Asset\AssetUpdateException;
use AllCoin\Process\ProcessInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use AllCoin\Service\DateTimeService;
use Psr\Log\LoggerInterface;

class AssetUpdateProcess implements ProcessInterface
{
    public function __construct(
        private AssetRepositoryInterface $assetRepository,
        private LoggerInterface $logger,
        private AssetMapper $assetMapper,
        private DateTimeService $dateTimeService
    )
    {
    }

    /**
     * @param \AllCoin\Dto\AssetRequestDto|null $dto
     * @param array $params
     * @return \AllCoin\Dto\ResponseDtoInterface
     * @throws \AllCoin\Exception\Asset\AssetUpdateException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ResponseDtoInterface
    {
        $assetId = $params['id'] ?? false;
        if (!$assetId) {
            throw new AssetUpdateException('The asset ID must be defined in $params');
        }

        try {
            $asset = $this->assetRepository->findOneById($assetId);
        } catch (ReadException $exception) {
            $message = 'The asset cannot be found!';
            $this->logger->error($message, [
                'id' => $assetId,
                'exception' => $exception->getMessage()
            ]);
            throw new AssetUpdateException($message);
        }

        $asset->setName($dto->getName());
        $asset->setUpdatedAt($this->dateTimeService->now());

        try {
            $this->assetRepository->save($asset);
        } catch (PersistenceException $exception) {
            $message = 'Cannot save the asset.';
            $this->logger->error($message, [
                'name' => $asset->getName(),
                'exception' => $exception->getMessage()
            ]);
            throw new AssetUpdateException($message);
        }

        return $this->assetMapper->mapModelToResponseDto($asset);
    }

}
