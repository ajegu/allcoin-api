<?php


namespace AllCoin\Process\Asset;


use AllCoin\Database\DynamoDb\Exception\PersistenceException;
use AllCoin\DataMapper\AssetMapper;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Exception\Asset\AssetCreateException;
use AllCoin\Model\Asset;
use AllCoin\Process\ProcessInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use AllCoin\Service\DateTimeService;
use Psr\Log\LoggerInterface;

class AssetCreateProcess implements ProcessInterface
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
     * @throws \AllCoin\Exception\Asset\AssetCreateException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ResponseDtoInterface
    {
        $asset = new Asset(
            name: $dto->getName(),
            createdAt: $this->dateTimeService->now()
        );

        try {
            $this->assetRepository->save($asset);
        } catch (PersistenceException $exception) {
            $message = 'Cannot save the asset.';
            $this->logger->error($message, [
                'name' => $asset->getName(),
                'exception' => $exception->getMessage()
            ]);
            throw new AssetCreateException($message);
        }

        return $this->assetMapper->mapModelToResponseDto($asset);
    }

}
