<?php

namespace Dogfromthemoon\LaravelWhatsappSender;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Dogfromthemoon\LaravelWhatsappSender\Skeleton\SkeletonClass
 */
class LaravelWhatsappSenderFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-whatsapp-sender';
    }
}
