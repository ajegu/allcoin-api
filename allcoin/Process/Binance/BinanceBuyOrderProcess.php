<?php


namespace AllCoin\Process\Binance;


use AllCoin\Builder\TransactionBuilder;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Model\EventPrice;
use AllCoin\Model\Transaction;
use AllCoin\Process\ProcessInterface;
use AllCoin\Repository\AssetPairRepositoryInterface;
use AllCoin\Repository\TransactionRepositoryInterface;
use Psr\Log\LoggerInterface;

class BinanceBuyOrderProcess implements ProcessInterface
{
    const FIXED_TRANSACTION_AMOUNT = 10;

    public function __construct(
        private TransactionBuilder $transactionBuilder,
        private TransactionRepositoryInterface $transactionRepository,
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

        if ($assetPair->getLastTransaction()?->getDirection() === Transaction::BUY) {
            $this->logger->debug('The asset pair has already bought.', [
                'transactionId' => $assetPair->getLastTransaction()->getId()
            ]);
            return null;
        }

        $quantity = round(self::FIXED_TRANSACTION_AMOUNT / $dto->getPrice(), 5);

        $transaction = $this->transactionBuilder->build(
            quantity: $quantity,
            amount: self::FIXED_TRANSACTION_AMOUNT,
            direction: Transaction::BUY,
            version: $dto->getName()
        );

        $this->transactionRepository->save($transaction, $assertPairId);

        $assetPair->setLastTransaction($transaction);
        $this->assetPairRepository->save($assetPair, $dto->getAsset()->getId());

        $this->logger->debug('Transaction created!');

        return null;
    }

}
