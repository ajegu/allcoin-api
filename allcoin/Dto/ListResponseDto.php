<?php


namespace AllCoin\Dto;


class ListResponseDto implements ResponseDtoInterface
{
    /**
     * ListResponseDto constructor.
     * @param \AllCoin\Dto\ResponseDtoInterface[] $data
     */
    public function __construct(
        private array $data
    )
    {
    }

    /**
     * @return \AllCoin\Dto\ResponseDtoInterface[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param \AllCoin\Dto\ResponseDtoInterface[] $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }


}
