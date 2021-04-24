<?php


namespace Test\AllCoin\DataMapper;


use AllCoin\DataMapper\EventOrderMapper;
use AllCoin\Model\EventOrder;
use AllCoin\Service\SerializerService;
use Test\TestCase;

class EventOrderMapperTest extends TestCase
{
    private EventOrderMapper $eventOrderMapper;

    private SerializerService $serializerService;

    public function setUp(): void
    {
        $this->serializerService = $this->createMock(SerializerService::class);

        $this->eventOrderMapper = new EventOrderMapper(
            $this->serializerService
        );
    }

    public function testMapJsonToEventShouldBeOK(): void
    {
        $data = 'foo';

        $this->serializerService->expects($this->once())
            ->method('deserializeToEvent')
            ->with($data, EventOrder::class)
            ->willReturn($this->createMock(EventOrder::class));

        $this->eventOrderMapper->mapJsonToEvent($data);
    }
}
