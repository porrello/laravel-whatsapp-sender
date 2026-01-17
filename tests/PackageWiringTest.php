<?php

namespace Dogfromthemoon\LaravelWhatsappSender\Tests;

use Dogfromthemoon\LaravelWhatsappSender\LaravelWhatsappSender;
use Dogfromthemoon\LaravelWhatsappSender\LaravelWhatsappSenderFacade;
use Illuminate\Support\Facades\Facade;

class PackageWiringTest extends TestCase
{
    public function test_it_registers_a_singleton_in_the_container(): void
    {
        $a = $this->app->make('laravel-whatsapp-sender');
        $b = $this->app->make('laravel-whatsapp-sender');

        $this->assertInstanceOf(LaravelWhatsappSender::class, $a);
        $this->assertSame($a, $b);
    }

    public function test_facade_resolves_from_the_container(): void
    {
        Facade::setFacadeApplication($this->app);

        $root = LaravelWhatsappSenderFacade::getFacadeRoot();
        $this->assertInstanceOf(LaravelWhatsappSender::class, $root);
    }

    public function test_it_prefers_config_values_for_credentials_when_instantiated_via_container(): void
    {
        $this->app['config']->set('laravel-whatsapp-sender.phone_number_id', 'PHONE_ID_FROM_CONFIG');
        $this->app['config']->set('laravel-whatsapp-sender.token', 'TOKEN_FROM_CONFIG');

        $sender = $this->app->make('laravel-whatsapp-sender');

        $phoneId = $this->getProtectedProperty($sender, 'phoneNumberId');
        $token = $this->getProtectedProperty($sender, 'token');

        $this->assertSame('PHONE_ID_FROM_CONFIG', $phoneId);
        $this->assertSame('TOKEN_FROM_CONFIG', $token);
    }

    private function getProtectedProperty(object $object, string $property): mixed
    {
        $ref = new \ReflectionProperty($object, $property);
        $ref->setAccessible(true);
        return $ref->getValue($object);
    }
}
