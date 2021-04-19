<?php


namespace AllCoin\Process\AssetPair;


use AllCoin\Builder\AssetPairBuilder;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\DataMapper\AssetPairMapper;
use AllCoin\Dto\AssetPairRequestDto;
use AllCoin\Dto\AssetPairResponseDto;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Exception\AssetPair\AssetPairCreateException;
use AllCoin\Process\ProcessInterface;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use JetBrains\PhpStorm\Pure;
use Psr\Log\LoggerInterface;

class AssetPairCreateProcess extends AbstractAssetPairProcess implements ProcessInterface
{
    #[Pure] public function __construct(
        protected AssetRepositoryInterface $assetRepository,
        protected AssetPairRepositoryInterface $assetPairRepository,
        protected LoggerInterface $logger,
        protected AssetPairMapper $assetPairMapper,
        private AssetPairBuilder $assetPairBuilder
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
     * @param AssetPairRequestDto|null $dto
     * @param array $params
     * @return AssetPairResponseDto|null
     * @throws AssetPairCreateException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ?ResponseDtoInterface
    {
        $assetId = $this->getAssetId($params, AssetPairCreateException::class);
        $asset = $this->getAsset($assetId, AssetPairCreateException::class);

        $assetPair = $this->assetPairBuilder->build($asset, $dto->getName());

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
