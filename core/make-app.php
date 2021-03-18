<?php

use OpxCore\App\Application;
use OpxCore\App\Interfaces\AppInterface;
use OpxCore\Config\Config;
use OpxCore\Config\ConfigCacheFiles;
use OpxCore\Config\ConfigRepositoryFiles;
use OpxCore\Config\Environment;
use OpxCore\Config\Exceptions\EnvironmentException;
use OpxCore\Config\Interfaces\ConfigInterface;
use OpxCore\Config\Interfaces\EnvironmentInterface;
use OpxCore\ExceptionHandler\ExceptionHandler;
use OpxCore\ExceptionHandler\Interfaces\ExceptionHandlerInterface;
use OpxCore\Log\Interfaces\LogManagerInterface;
use OpxCore\Log\LogManager;
use OpxCore\Profiler\Interfaces\ProfilerInterface;
use OpxCore\Profiler\Profiler;

/**
 * @param string $baseDir
 *
 * @return  AppInterface
 *
 * @throws EnvironmentException
 */
function makeApplication(string $baseDir): AppInterface
{
    // Create container with base bindings and capture data for profiler
    $containerStartTimestamp = hrtime(true);
    $containerStartMemory = memory_get_usage();

    $container = new OpxCore\Container\Container();

    // Bind application profiler. This is not necessary but can help improve performance of application.
    $container->singleton(ProfilerInterface::class, Profiler::class, [
        'startTime' => @constant('OPXCORE_START'),
        'startMem' => @constant('OPXCORE_START_MEM'),
    ]);

    // Set base path to environment
    $env = new Environment($baseDir, '.env');
    $env->set('BASE_PATH', $baseDir);

    // Bind environment driver with parameters, will be injected to Config
    $container->instance(EnvironmentInterface::class, $env);


    // Bind config driver.
    $container->singleton(ConfigInterface::class, Config::class, function () use ($baseDir) {
        return [
            'repository' => new ConfigRepositoryFiles($baseDir . DIRECTORY_SEPARATOR . 'config'),
            'cache' => new ConfigCacheFiles($baseDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'system'),
            // environment would be resolved through container
        ];
    });

    // Bind exception handler as singleton to have access later.
    $container->singleton(ExceptionHandlerInterface::class, ExceptionHandler::class);

    $containerStopTimestamp = hrtime(true);
    $containerStopMemory = memory_get_usage();

    // Create an application.
    $app = new Application($container, $baseDir);

    // Bind logger. All required arguments would be got from `config/log.php` then logger would be called.
    // Attention!!! Logger will be available only after application initialization.
    $container->singleton(LogManagerInterface::class, LogManager::class, static function () use ($app) {
        return $app->config()->get('log');
    });

    // Write profiling for autoloader (with external captured stamps)
    $app->profiler()->start('autoload', constant('AUTOLOAD_START'), constant('AUTOLOAD_START_MEM'));
    $app->profiler()->stop('autoload', constant('AUTOLOAD_STOP'), constant('AUTOLOAD_STOP_MEM'));

    // Write profiling (with external captured stamps) for container creation and binds after profiler is set
    $app->profiler()->start('container.binding', $containerStartTimestamp, $containerStartMemory);
    $app->profiler()->stop('container.binding', $containerStopTimestamp, $containerStopMemory);

    return $app;
}
