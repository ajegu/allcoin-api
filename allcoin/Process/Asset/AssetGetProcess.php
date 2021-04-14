<?php


namespace AllCoin\Process\Asset;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\DataMapper\AssetMapper;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Exception\Asset\AssetGetException;
use AllCoin\Process\ProcessInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use Psr\Log\LoggerInterface;

class AssetGetProcess implements ProcessInterface
{
    public function __construct(
        private AssetRepositoryInterface $assetRepository,
        private LoggerInterface $logger,
        private AssetMapper $assetMapper,
    )
    {
    }

    /**
     * @param RequestDtoInterface|null $dto
     * @param array $params
     * @return ResponseDtoInterface|null
     * @throws AssetGetException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ?ResponseDtoInterface
    {
        $assetId = $params['id'] ?? false;
        if (!$assetId) {
            throw new AssetGetException('The asset ID must be defined in $params');
        }

        try {
            $asset = $this->assetRepository->findOneById($assetId);
        } catch (ItemReadException $exception) {
            $message = 'The asset cannot be found!';
            $this->logger->error($message, [
                'id' => $assetId,
                'exception' => $exception->getMessage()
            ]);
            throw new AssetGetException($message);
        }

        return $this->assetMapper->mapModelToResponseDto($asset);
    }

}
