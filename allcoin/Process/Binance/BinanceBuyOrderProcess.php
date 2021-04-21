<?php


namespace AllCoin\Process\Binance;


use AllCoin\Builder\TransactionBuilder;
use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Database\DynamoDb\Exception\ItemSaveException;
use AllCoin\Dto\RequestDtoInterface;
use AllCoin\Dto\ResponseDtoInterface;
use AllCoin\Exception\Binance\BinanceBuyOrderProcessException;
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
     * @throws BinanceBuyOrderProcessException
     */
    public function handle(EventPrice|RequestDtoInterface $dto = null, array $params = []): ?ResponseDtoInterface
    {
        $assertPairId = $dto->getAssetPair()->getId();

        try {
            $assetPair = $this->assetPairRepository->findOneById($assertPairId);
        } catch (ItemReadException $exception) {
            $message = 'The asset pair cannot be fetched for buy order.';
            $this->logger->error($message, [
                'message' => $exception->getMessage()
            ]);
            throw new BinanceBuyOrderProcessException($message);
        }

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

        try {
            $this->transactionRepository->save($transaction, $assertPairId);
        } catch (ItemSaveException $exception) {
            $message = 'The transaction cannot be saved for buy order.';
            $this->logger->error($message, [
                'message' => $exception->getMessage()
            ]);
            throw new BinanceBuyOrderProcessException($message);
        }

        $assetPair->setLastTransaction($transaction);

        try {
            $this->assetPairRepository->save($assetPair, $dto->getAsset()->getId());
        } catch (ItemSaveException $exception) {
            $message = 'The asset pair cannot be saved for buy order.';
            $this->logger->error($message, [
                'message' => $exception->getMessage()
            ]);
            throw new BinanceBuyOrderProcessException($message);
        }

        $this->logger->debug('Transaction created!');

        return null;
    }

}
