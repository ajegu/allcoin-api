<?php


namespace AllCoin\Process\Binance;


use AllCoin\Builder\OrderBuilder;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Model\EventPrice;
use AllCoin\Model\Order;
use AllCoin\Process\ProcessInterface;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class BinanceOrderBuyProcess implements ProcessInterface
{
    const FIXED_TRANSACTION_AMOUNT = 10;

    public function __construct(
        private OrderBuilder $orderBuilder,
        private OrderRepositoryInterface $orderRepository,
        private AssetPairRepositoryInterface $assetPairRepository,
        private LoggerInterface $logger
    )
    {
    }

    /**
     * @param EventPrice|RequestDtoInterface|null $dto
     * @param array $params
     * @return ResponseDtoInterface|null
     * @throws ItemReadException
     * @throws ItemSaveException
     */
    public function handle(EventPrice|RequestDtoInterface $dto = null, array $params = []): ?ResponseDtoInterface
    {
        $assertPairId = $dto->getAssetPair()->getId();

        $assetPair = $this->assetPairRepository->findOneById($assertPairId);

        if ($assetPair->getLastOrder()?->getDirection() === Order::BUY) {
            $this->logger->debug('The asset pair has already bought.', [
                'orderId' => $assetPair->getLastOrder()->getId()
            ]);
            return null;
        }

        $quantity = round(self::FIXED_TRANSACTION_AMOUNT / $dto->getPrice(), 5);

        $order = $this->orderBuilder->build(
            quantity: $quantity,
            amount: self::FIXED_TRANSACTION_AMOUNT,
            direction: Order::BUY,
            version: $dto->getName()
        );

        $this->orderRepository->save($order, $assertPairId);

        $assetPair->setLastOrder($order);
        $this->assetPairRepository->save($assetPair, $dto->getAsset()->getId());

        $this->logger->debug('Order buy created!');

        return null;
    }

}
