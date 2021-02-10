<?php

use OpxCore\App\Application;
use OpxCore\App\Interfaces\ProfilerInterface;
use OpxCore\App\Profiler;
use OpxCore\Config\Config;
use OpxCore\Config\ConfigCacheFiles;
use OpxCore\Config\ConfigRepositoryFiles;
use OpxCore\Config\Environment;
use OpxCore\Config\Interfaces\ConfigCacheInterface;
use OpxCore\Config\Interfaces\ConfigInterface;
use OpxCore\Config\Interfaces\ConfigRepositoryInterface;
use OpxCore\Config\Interfaces\EnvironmentInterface;
use OpxCore\Container\Container;
use OpxCore\Log\Interfaces\LoggerInterface;
use OpxCore\Log\LogManager;

try {
    // Path to project root
    $baseDir = __DIR__;

    // First create container to bind necessary dependencies for application creation
    $container = new Container;

    // Bind profiler
    $container->bind(ProfilerInterface::class, Profiler::class, [
        'startTime'=> @constant('OPXCORE_START'),
        'startMem'=> @constant('OPXCORE_START_MEM'),
    ]);

    // Create an application
    $app = new Application($container, $baseDir);

    $app->profiler()->start('config.binding');

    // Bind config driver.
    $app->container()->bind(ConfigInterface::class, Config::class);//, static function (ContainerInterface $container) {

    // Bind config repository driver with parameters, will be injected to Config
    $app->container()->bind(ConfigRepositoryInterface::class, ConfigRepositoryFiles::class, [
        'path' => $app->path('config')
    ]);

    // Bind config cache driver with parameters, will be injected to Config
    $app->container()->bind(ConfigCacheInterface::class, ConfigCacheFiles::class, [
        'path' => $app->path('storage' . DIRECTORY_SEPARATOR . 'system')
    ]);

    // Bind environment driver with parameters, will be injected to Config
    $app->container()->bind(EnvironmentInterface::class, Environment::class, [
        'path' => $app->path(), 'env' => '.env'
    ]);

    $app->profiler()->stop('config.binding');

    // Bind logger. All required arguments would be got from `config/log.php`.
    $app->profiler()->start('logger.binding');

    $app->container()->bind(LoggerInterface::class, LogManager::class, static function () {
        return app()->config()->get('log');
    });

    $app->profiler()->stop('logger.binding');

    // define function to access created application everywhere.
    if (!function_exists('app')) {
        /**
         * Get application instance.
         *
         * @return Application
         */
        function app(): Application
        {
            global $app;

            return $app;
        }
    }

    $app->init();

    $app->bootstrap();

} catch (Error | Exception $e) {
    echo "Application bootstrap error.<br>{$e->getMessage()}";
    die();
}


return $app;