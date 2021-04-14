<?php


namespace AllCoin\Dto;


class ListResponseDto implements ResponseDtoInterface
{
    /**
     * ListResponseDto constructor.
     * @param ResponseDtoInterface[] $data
     */
    public function __construct(
        private array $data
    )
    {
    }

    /**
     * @return ResponseDtoInterface[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param ResponseDtoInterface[] $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }


}
