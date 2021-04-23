<?php


namespace Test\App\Lambda;


use AllCoin\Database\DynamoDb\Exception\ItemReadException;
use AllCoin\Exception\NotificationHandlerException;
use AllCoin\Process\AssetPairPrice\AssetPairPriceAnalyzerProcess;
use App\Lambda\PriceAnalyzerLambda;
use Test\TestCase;

class PriceAnalyzerLambdaTest extends TestCase
{
    private PriceAnalyzerLambda $priceAnalyzerLambda;

    private AssetPairPriceAnalyzerProcess $assetPairPriceAnalyzerProcess;

    public function setUp(): void
    {
        $this->assetPairPriceAnalyzerProcess = $this->createMock(AssetPairPriceAnalyzerProcess::class);

        $this->priceAnalyzerLambda = new PriceAnalyzerLambda(
            $this->assetPairPriceAnalyzerProcess
        );
    }

    /**
     * @throws ItemReadException
     * @throws NotificationHandlerException
     */
    public function testInvokeShouldBeOK(): void
    {
        $this->assetPairPriceAnalyzerProcess->expects($this->once())
            ->method('handle');

        $this->priceAnalyzerLambda->__invoke([]);
    }
}
