<?php


namespace AllCoin\Process\Asset;


use AllCoin\Builder\AssetBuilder;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\DataMapper\AssetMapper;
use AllCoin\Dto\AssetRequestDto;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Exception\Asset\AssetCreateException;
use AllCoin\Process\ProcessInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use Psr\Log\LoggerInterface;

class AssetCreateProcess implements ProcessInterface
{
    public function __construct(
        private AssetRepositoryInterface $assetRepository,
        private LoggerInterface $logger,
        private AssetMapper $assetMapper,
        private AssetBuilder $assetBuilder
    )
    {
    }

    /**
     * @param AssetRequestDto|null $dto
     * @param array $params
     * @return ResponseDtoInterface
     * @throws AssetCreateException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ResponseDtoInterface
    {
        $asset = $this->assetBuilder->build($dto->getName());

        try {
            $this->assetRepository->save($asset);
        } catch (ItemSaveException $exception) {
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
