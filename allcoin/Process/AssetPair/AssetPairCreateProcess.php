<?php


namespace AllCoin\Process\AssetPair;


use AllCoin\Builder\AssetPairBuilder;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\DataMapper\AssetPairMapper;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Process\ProcessInterface;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use JetBrains\PhpStorm\Pure;

class AssetPairCreateProcess extends AbstractAssetPairProcess implements ProcessInterface
{
    #[Pure] public function __construct(
        protected AssetRepositoryInterface $assetRepository,
        protected AssetPairRepositoryInterface $assetPairRepository,
        protected AssetPairMapper $assetPairMapper,
        private AssetPairBuilder $assetPairBuilder
    )
    {
        parent::__construct(
            $assetRepository,
            $assetPairRepository,
            $assetPairMapper,
        );
    }

    /**
     * @param RequestDtoInterface|null $dto
     * @param array $params
     * @return ResponseDtoInterface|null
     * @throws ItemSaveException
     * @throws ItemReadException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ?ResponseDtoInterface
    {
        $asset = $this->assetRepository->findOneById(
            $this->getAssetId($params)
        );

        $assetPair = $this->assetPairBuilder->build($dto->getName());

        $this->assetPairRepository->save($assetPair, $asset->getId());

        return $this->assetPairMapper->mapModelToResponseDto($assetPair);
    }

}
