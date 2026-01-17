<?php

namespace Dogfromthemoon\LaravelWhatsappSender\Tests;

use Dogfromthemoon\LaravelWhatsappSender\LaravelWhatsappSenderServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelWhatsappSenderServiceProvider::class,
        ];
    }
}
