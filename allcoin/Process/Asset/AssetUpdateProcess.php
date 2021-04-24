<?php


namespace AllCoin\Process\Asset;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\DataMapper\AssetMapper;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Process\ProcessInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use AllCoin\Service\DateTimeService;
use JetBrains\PhpStorm\Pure;

class AssetUpdateProcess extends AbstractAssetProcess implements ProcessInterface
{
    #[Pure] public function __construct(
        protected AssetRepositoryInterface $assetRepository,
        protected AssetMapper $assetMapper,
        private DateTimeService $dateTimeService
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
     * @throws ItemReadException
     * @throws ItemSaveException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ResponseDtoInterface
    {
        $asset = $this->assetRepository->findOneById(
            $this->getAssetId($params)
        );

        $asset->setName($dto->getName());
        $asset->setUpdatedAt($this->dateTimeService->now());

        $this->assetRepository->save($asset);

        return $this->assetMapper->mapModelToResponseDto($asset);
    }

}
