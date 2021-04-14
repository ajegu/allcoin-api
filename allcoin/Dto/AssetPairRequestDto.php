<?php


namespace AllCoin\Dto;


class AssetPairRequestDto implements RequestDtoInterface
{
    const NAME = 'name';

    public function __construct(
        private string $name
    )
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }


}
