<?php


namespace AllCoin\Process\Asset;


use AllCoin\Builder\AssetBuilder;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\DataMapper\AssetMapper;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Process\ProcessInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use JetBrains\PhpStorm\Pure;

class AssetCreateProcess extends AbstractAssetProcess implements ProcessInterface
{
    #[Pure] public function __construct(
        protected AssetRepositoryInterface $assetRepository,
        protected AssetMapper $assetMapper,
        private AssetBuilder $assetBuilder
    )
    {
        parent::__construct(
            $assetRepository,
            $assetMapper
        );
    }

    /**
     * @param RequestDtoInterface|null $dto
     * @param array $params
     * @return ResponseDtoInterface
     * @throws ItemSaveException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ResponseDtoInterface
    {
        $asset = $this->assetBuilder->build($dto->getName());
        $this->assetRepository->save($asset);

        return $this->assetMapper->mapModelToResponseDto($asset);
    }

}
