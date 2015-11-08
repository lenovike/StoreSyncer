<?php

namespace AppBundle\EventListener;

use AppBundle\Config\Config;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StoreConfigListener implements EventSubscriberInterface
{
    private $defaultLocale;

    public function __construct($defaultLocale = 'en')
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {

        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            return;
        }

        $config = Config::getConfig();
        foreach ($config as $storeSessionKey => $store){
            $storeConfig = $request->getSession()->get($storeSessionKey);
            if (!$storeConfig){
                $request->getSession()->set($storeSessionKey, $store);
            }
        }

    }

    public static function getSubscribedEvents()
    {
        return array(
            // must be registered before the default Locale listener
            KernelEvents::REQUEST => array(array('onKernelRequest', 17)),
        );
    }
}