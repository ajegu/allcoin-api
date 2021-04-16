<?php


namespace AllCoin\Process\Asset;


use AllCoin\Database\DynamoDb\Exception\ItemDeleteException;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Exception\Asset\AssetDeleteException;
use AllCoin\Process\ProcessInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use Psr\Log\LoggerInterface;

class AssetDeleteProcess implements ProcessInterface
{
    public function __construct(
        private AssetRepositoryInterface $assetRepository,
        private LoggerInterface $logger
    )
    {
    }

    /**
     * @param RequestDtoInterface|null $dto
     * @param array $params
     * @return ResponseDtoInterface|null
     * @throws AssetDeleteException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ?ResponseDtoInterface
    {
        $assetId = $params['id'] ?? false;
        if (!$assetId) {
            throw new AssetDeleteException('The asset ID must be defined in $params');
        }

        try {
            $this->assetRepository->delete($assetId);
        } catch (ItemDeleteException $exception) {
            $message = 'The asset cannot be deleted!';
            $this->logger->error($message, [
                'id' => $assetId,
                'exception' => $exception->getMessage()
            ]);
            throw new AssetDeleteException($message);
        }

        return null;
    }

}
