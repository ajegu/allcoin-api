<?php


namespace AllCoin\Database\DynamoDb;


use AllCoin\Database\DynamoDb\Exception\MarshalerException;
use Aws\DynamoDb\Marshaler;
use Psr\Log\LoggerInterface;
use UnexpectedValueException;

class MarshalerService
{
    public function __construct(
        private Marshaler $marshaler,
        private LoggerInterface $logger,
    )
    {
    }

    /**
     * @param array $item
     * @return array
     * @throws \AllCoin\Database\DynamoDb\Exception\MarshalerException
     */
    public function marshalItem(array $item): array
    {
        try {
            return $this->marshaler->marshalItem($item);
        } catch (UnexpectedValueException $exception) {
            $message = 'Cannot marshal the item.';
            $this->logger->error($message, [
                'exception' => $exception->getMessage(),
                'item' => $item
            ]);
            throw new MarshalerException($message);
        }
    }
}
