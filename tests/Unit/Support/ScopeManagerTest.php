<?php

namespace MerchantOfComplexityTest\Oauth\Unit\Support;

use BadMethodCallException;
use MerchantOfComplexity\Oauth\Infrastructure\Models\ScopeModel;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideScope;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\ScopeTransformer;
use MerchantOfComplexity\Oauth\Support\ScopeManager;
use MerchantOfComplexityTest\Oauth\TestCase;
use Prophecy\Argument;

class ScopeManagerTest extends TestCase
{
    /**
     * @test
     */
    public function it_filter_scopes_with_available_scopes(): void
    {
        $scopeProvider = $this->prophesize(ProvideScope::class);
        $scopeTransformer = $this->prophesize(ScopeTransformer::class);

        $scopeFooModel = new ScopeModel('foo');
        $scopeProvider->scopeOfIdentifier(Argument::exact('foo'))->willReturn($scopeFooModel);

        $scopeBarModel = new ScopeModel('bar');
        $scopeProvider->scopeOfIdentifier(Argument::exact('bar'))->willReturn($scopeBarModel);

        $manager = new ScopeManager($scopeProvider->reveal(), $scopeTransformer->reveal());

        $this->assertEquals([$scopeFooModel, $scopeBarModel], $manager->filterScopes($scopeFooModel, $scopeBarModel));
    }

    /**
     * @test
     */
    public function it_filter_invalid_scopes(): void
    {
        $scopeProvider = $this->prophesize(ProvideScope::class);
        $scopeTransformer = $this->prophesize(ScopeTransformer::class);

        $scopeFooModel = new ScopeModel('foo');
        $scopeProvider->scopeOfIdentifier(Argument::exact('foo'))->willReturn($scopeFooModel);

        $scopeBarModel = new ScopeModel('bar');
        $scopeProvider->scopeOfIdentifier(Argument::exact('bar'))->willReturn($scopeBarModel);

        $invalidScopeModel = new ScopeModel('bar_bar');
        $scopeProvider->scopeOfIdentifier(Argument::exact('bar_bar'))->willReturn(null);


        $manager = new ScopeManager($scopeProvider->reveal(), $scopeTransformer->reveal());

        $this->assertEquals([$scopeFooModel, $scopeBarModel],
            $manager->filterScopes($invalidScopeModel, $scopeFooModel, $scopeBarModel)
        );
    }

    /**
     * @test
     */
    public function it_check_if_scope_exists(): void
    {
        $scopeProvider = $this->prophesize(ProvideScope::class);
        $scopeTransformer = $this->prophesize(ScopeTransformer::class);

        $scopeFooModel = new ScopeModel('foo');
        $scopeProvider->scopeOfIdentifier(Argument::exact('foo'))->willReturn(null);

        $scopeBarModel = new ScopeModel('bar');
        $scopeProvider->scopeOfIdentifier(Argument::exact('bar'))->willReturn($scopeBarModel);


        $manager = new ScopeManager($scopeProvider->reveal(), $scopeTransformer->reveal());

        $this->assertFalse($manager->isScopeAvailable('foo'));
        $this->assertTrue($manager->isScopeAvailable('bar'));
    }

    /**
     * @test
     */
    public function it_check_if_scopes_are_equals(): void
    {
        $scopeProvider = $this->prophesize(ProvideScope::class);
        $scopeTransformer = $this->prophesize(ScopeTransformer::class);

        $scopeFooModel = new ScopeModel('foo');
        $scopeBarModel = new ScopeModel('bar');

        $scopeTransformer->toStringArray([$scopeFooModel])->willReturn(['foo']);
        $scopeTransformer->toStringArray([$scopeBarModel])->willReturn(['bar']);

        $manager = new ScopeManager($scopeProvider->reveal(), $scopeTransformer->reveal());

        $this->assertFalse($manager->equalsScopes([$scopeFooModel], [$scopeBarModel]));
        $this->assertTrue($manager->equalsScopes([$scopeFooModel], [$scopeFooModel]));
        $this->assertTrue($manager->equalsScopes([$scopeBarModel], [$scopeBarModel]));
    }

    /**
     * @test
     */
    public function it_check_if_scopes_are_equals_by_array_equality(): void
    {
        $scopeProvider = $this->prophesize(ProvideScope::class);
        $scopeTransformer = $this->prophesize(ScopeTransformer::class);

        $scopeTransformer->toStringArray(['foo', 'bar'])->willReturn(['foo', 'bar']);
        $scopeTransformer->toStringArray(['foo', 'bar'])->willReturn(['foo', 'bar']);

        $manager = new ScopeManager($scopeProvider->reveal(), $scopeTransformer->reveal());

        $this->assertTrue($manager->equalsScopes(['foo', 'bar'], ['foo', 'bar']));
    }

    /**
     * @test
     */
    public function it_return_difference_between_scopes(): void
    {
        $scopeProvider = $this->prophesize(ProvideScope::class);
        $scopeTransformer = $this->prophesize(ScopeTransformer::class);

        $scopeTransformer->toStringArray(['foo', 'bar'])->willReturn(['foo', 'bar']);
        $scopeTransformer->toStringArray(['foo'])->willReturn(['foo']);

        $manager = new ScopeManager($scopeProvider->reveal(), $scopeTransformer->reveal());

        $this->assertEquals([1 => 'bar'], $manager->diffScopes(['foo', 'bar'], ['foo']));
    }

    /**
     * @test
     */
    public function it_raise_exception_when_scope_transformer_method_does_not_exists(): void
    {
    }
}