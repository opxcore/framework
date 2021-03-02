<?php

use OpxCore\Config\Config;
use OpxCore\Config\ConfigCacheFiles;
use OpxCore\Config\ConfigRepositoryFiles;
use OpxCore\Config\Environment;
use OpxCore\Config\Interfaces\ConfigCacheInterface;
use OpxCore\Config\Interfaces\ConfigInterface;
use OpxCore\Config\Interfaces\ConfigRepositoryInterface;
use OpxCore\Config\Interfaces\EnvironmentInterface;
use OpxCore\Container\Interfaces\ContainerInterface;
use OpxCore\ExceptionHandler\ExceptionHandler;
use OpxCore\ExceptionHandler\Interfaces\ExceptionHandlerInterface;
use OpxCore\Log\Interfaces\LogManagerInterface;
use OpxCore\Log\LogManager;
use OpxCore\Profiler\Interfaces\ProfilerInterface;
use OpxCore\Profiler\Profiler;

function makeContainerBaseBindings(ContainerInterface $container, string $baseDir): ContainerInterface
{
    // Bind application profiler. This is not necessary but can help improve performance of application.
    $container->bind(ProfilerInterface::class, Profiler::class, [
        'startTime' => @constant('OPXCORE_START'),
        'startMem' => @constant('OPXCORE_START_MEM'),
    ]);

    // Bind config driver.
    $container->bind(ConfigInterface::class, Config::class);

    // Bind config repository driver with parameters, will be injected to Config
    $container->bind(ConfigRepositoryInterface::class, ConfigRepositoryFiles::class, [
        'path' => $baseDir . DIRECTORY_SEPARATOR . 'config'
    ]);

    // Bind config cache driver with parameters, will be injected to Config
    $container->bind(ConfigCacheInterface::class, ConfigCacheFiles::class, [
        'path' => $baseDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'system'
    ]);

    // Bind environment driver with parameters, will be injected to Config
    $container->bind(EnvironmentInterface::class, Environment::class, [
        'path' => $baseDir, 'env' => '.env'
    ]);

    // Bind logger. All required arguments would be got from `config/log.php` then logger would be called.
    // Attention!!! Logger will be available only after application initialization.
    $container->bind(LogManagerInterface::class, LogManager::class, static function () {
        return app()->config()->get('log');
    });

    // Bind exception handler as singleton to have access later.
    $container->singleton(ExceptionHandlerInterface::class, ExceptionHandler::class);

    return $container;
}
