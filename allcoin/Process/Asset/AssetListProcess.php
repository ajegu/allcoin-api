<?php


namespace AllCoin\Process\Asset;


use AllCoin\Database\DynamoDb\Exception\ReadException;
use AllCoin\DataMapper\AssetMapper;
use AllCoin\Dto\ListResponseDto;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Exception\Asset\AssetListException;
use AllCoin\Model\Asset;
use AllCoin\Process\ProcessInterface;
use AllCoin\Repository\AssetRepositoryInterface;
use Psr\Log\LoggerInterface;

class AssetListProcess implements ProcessInterface
{
    public function __construct(
        private AssetRepositoryInterface $assetRepository,
        private AssetMapper $assetMapper,
        private LoggerInterface $logger
    )
    {
    }

    /**
     * @param \AllCoin\Dto\RequestDtoInterface|null $dto
     * @param array $params
     * @return \AllCoin\Dto\ResponseDtoInterface
     * @throws \AllCoin\Exception\Asset\AssetListException
     */
    public function handle(RequestDtoInterface $dto = null, array $params = []): ResponseDtoInterface
    {
        try {
            $assets = $this->assetRepository->findAll();
        } catch (ReadException $exception) {
            $message = 'Cannot read the asset list.';
            $this->logger->error($message, [
                'exception' => $exception->getMessage()
            ]);
            throw new AssetListException($message);
        }

        $assetsDto = array_map(function (Asset $asset) {
            return $this->assetMapper->mapModelToResponseDto($asset);
        }, $assets);

        return new ListResponseDto($assetsDto);
    }

}
