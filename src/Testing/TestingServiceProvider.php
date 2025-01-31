<?php

namespace Nuwave\Lighthouse\Testing;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Illuminate\Testing\TestResponse;
use Nuwave\Lighthouse\Events\RegisterDirectiveNamespaces;

class TestingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MockResolverService::class);
        TestResponse::mixin(new TestResponseMixin());
    }

    public function boot(Dispatcher $dispatcher): void
    {
        $dispatcher->listen(
            RegisterDirectiveNamespaces::class,
            static function (): string {
                return __NAMESPACE__;
            }
        );
    }
}
