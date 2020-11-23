<?php

namespace Tests;

use App\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Mockery;
use Throwable;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mockery::getConfiguration()->allowMockingNonExistentMethods(false);
    }

    use CreatesApplication;



    public function disableExceptionHandling(): void
    {
        $this->app->instance(ExceptionHandler::class, new class extends Handler {
            public function __construct() {}
            public function report(Throwable $e) {}
            public function render ($request, Throwable $e) {
                throw $e;
            }
        });
    }
}
