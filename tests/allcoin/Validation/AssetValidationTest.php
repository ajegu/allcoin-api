<?php


namespace Test\AllCoin\Validation;


use AllCoin\Validation\AssetValidation;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Validation\Validator;
use Test\TestCase;

class AssetValidationTest extends TestCase
{
    private AssetValidation $assetValidation;

    public function setUp(): void
    {
        $this->assetValidation = new AssetValidation();

        parent::setUp();
    }

    public function testPostRulesShouldBeOK(): void
    {
        $rules = $this->assetValidation->getPostRules();

        $data = [
            'name' => 'foo'
        ];

        $validator = new Validator(
            $this->app->make(Translator::class),
            $data,
            $rules
        );

        $this->assertFalse($validator->fails());
    }

    public function testPostRulesShouldFails(): void
    {
        $rules = $this->assetValidation->getPostRules();

        $data = [];

        $validator = new Validator(
            $this->app->make(Translator::class),
            $data,
            $rules
        );

        $this->assertTrue($validator->fails());
    }
}
