<?php


namespace AllCoin\Process\Asset;


use AllCoin\Database\DynamoDb\Exception\DeleteException;
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
     * @param \AllCoin\Dto\RequestDtoInterface|null $dto
     * @param array $params
     * @return \AllCoin\Dto\ResponseDtoInterface|null
     * @throws \AllCoin\Exception\Asset\AssetDeleteException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ?ResponseDtoInterface
    {
        $assetId = $params['id'] ?? false;
        if (!$assetId) {
            throw new AssetDeleteException('The asset ID must be defined in $params');
        }

        try {
            $this->assetRepository->delete($assetId);
        } catch (DeleteException $exception) {
            $message = 'The asset cannot be delete!';
            $this->logger->error($message, [
                'id' => $assetId,
                'exception' => $exception->getMessage()
            ]);
            throw new AssetDeleteException($message);
        }

        return null;
    }

}
