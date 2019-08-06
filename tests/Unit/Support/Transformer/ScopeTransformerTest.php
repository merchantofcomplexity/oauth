<?php

namespace MerchantOfComplexityTest\Oauth\Unit\Support\Transformer;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use MerchantOfComplexity\Oauth\Infrastructure\Models\ScopeModel;
use MerchantOfComplexity\Oauth\League\Entity\Scope;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\ScopeInterface;
use MerchantOfComplexity\Oauth\Support\Transformer\ScopeTransformer;
use MerchantOfComplexityTest\Oauth\TestCase;

class ScopeTransformerTest extends TestCase
{
    /**
     * @test
     */
    public function it_transform_scope_entity_to_scope_model(): void
    {
        $transformer = new ScopeTransformer();

        $scope = new Scope();
        $scope->setIdentifier('foo');

        $scopeModel = $transformer->toModel($scope);

        $this->assertInstanceOf(ScopeInterface::class, $scopeModel);
        $this->assertEquals('foo', $scopeModel->toString());
    }

    /**
     * @test
     */
    public function it_transform_scope_model_to_scope_entity(): void
    {
        $transformer = new ScopeTransformer();

        $scope = new ScopeModel('foo');

        $scope = $transformer->toLeague($scope);

        $this->assertInstanceOf(ScopeEntityInterface::class, $scope);
        $this->assertEquals('foo', $scope->getIdentifier());
    }

    /**
     * @test
     */
    public function it_transform_array_of_scope_models_to_array_of_scope_entities(): void
    {
        $transformer = new ScopeTransformer();

        $entities = $transformer->toLeagueArray($this->scopeModels());

        foreach ($entities as $entity) {
            $this->assertInstanceOf(ScopeEntityInterface::class, $entity);
        }
    }

    /**
     * @test
     */
    public function it_transform_array_of_scope_entities_to_array_of_scope_models(): void
    {
        $transformer = new ScopeTransformer();

        $models = $transformer->toModelArray($this->scopeEntities());

        foreach ($models as $model) {
            $this->assertInstanceOf(ScopeInterface::class, $model);
        }
    }

    /**
     * @test
     */
    public function it_transform_any_array_type_to_string(): void
    {
        $transformer = new ScopeTransformer();

        $models = $transformer->toModelArray($this->scopeEntities());
        $modelsToString = $transformer->toStringArray($models);

        $this->assertEquals(['foo', 'bar'], $modelsToString);

        $entities = $transformer->toLeagueArray($this->scopeModels());
        $entitiesToString = $transformer->toStringArray($entities);

        $this->assertEquals(['foo', 'bar'], $entitiesToString);

        $expected = ['baz', 'bar_bar'];
        $expectedToString = $transformer->toStringArray($expected);

        $this->assertEquals($expected, $expectedToString);
    }

    protected function scopeModels(): array
    {
        return [
            new ScopeModel('foo'),
            new ScopeModel('bar')
        ];
    }

    protected function scopeEntities(): array
    {
        $scopeFoo = new Scope();
        $scopeFoo->setIdentifier('foo');

        $scopeBar = new Scope();
        $scopeBar->setIdentifier('bar');

        return [$scopeFoo, $scopeBar];
    }
}