<?php

namespace OpxCore\Bootstrappers;

use OpxCore\App\Interfaces\AppBootstrapperInterface;
use OpxCore\App\Interfaces\AppInterface;
use OpxCore\Kernel\Interfaces\KernelInterface;
use OpxCore\Kernel\Kernel;
use OpxCore\Request\Interfaces\RequestInterface;
use OpxCore\Request\Request;

class HttpFoundationBootstrapper implements AppBootstrapperInterface
{

    /**
     * Register bindings for http processing.
     *
     * @param AppInterface $app
     *
     * @return  array|null Instances to be registered in application container.
     */
    public function bootstrap(AppInterface $app): ?array
    {
        $app->container()->singleton(KernelInterface::class, Kernel::class, static function () use ($app) {
            return $app->config()->get('middlewares.global.http');
        });

        $app->container()->bind(RequestInterface::class, Request::class);

        // Bind http response

        return null;
    }
}