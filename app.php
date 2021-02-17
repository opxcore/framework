<?php

use OpxCore\App\Application;
use OpxCore\ExceptionHandler\ExceptionHandler;
use OpxCore\ExceptionHandler\Interfaces\ExceptionHandlerInterface;
use OpxCore\Profiler\Interfaces\ProfilerInterface;
use OpxCore\Profiler\Profiler;
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

// Use try-catch to handle exceptions before ExceptionHandler would be registered.
try {
    // Path to project root
    $baseDir = __DIR__;
    $containerStartTimestamp = hrtime(true);
    $containerStartMemory = memory_get_usage();

    // First create container to bind necessary dependencies for application creation
    $container = new Container;

    // Bind profiler.
    $container->bind(ProfilerInterface::class, Profiler::class, [
        'startTime' => @constant('OPXCORE_START'),
        'startMem' => @constant('OPXCORE_START_MEM'),
    ]);

    // Bind config driver.
    $container->bind(ConfigInterface::class, Config::class);//, static function (ContainerInterface $container) {

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
    $container->bind(LoggerInterface::class, LogManager::class, static function () {
        return app()->config()->get('log');
    });

    // Bind exception handler as singleton to have access later.
    $container->singleton(ExceptionHandlerInterface::class, ExceptionHandler::class);

    $containerStopTimestamp = hrtime(true);
    $containerStopMemory = memory_get_usage();

    // Create an application.
    $app = new Application($container, $baseDir);

} catch (Error | Throwable | Exception $e) {
    echo "<h1>Application creation error.</h1><p>{$e->getMessage()}</p>";
    $trace = $e->getTrace();
    foreach ($trace as $entry) {
        echo "<p style='font-size: 0.9rem;margin: 0.2rem;'><i>{$entry['file']}:{$entry['line']}</i> " . ($entry['class'] ?? null) . ($entry['type'] ?? null) . ($entry['function'] ?? null) . "</p>";
    }
    die();
}

// Now $app has registered container with bindings (see above) and configured profiler if it was bound.
// Also exception handler was registered if it was bound.
// $app->path() is available and points to project root directory.

// Write profiling for autoloader
$app->profiler()->start('autoload', constant('AUTOLOAD_START'), constant('AUTOLOAD_START_MEM'));
$app->profiler()->stop('autoload', constant('AUTOLOAD_STOP'), constant('AUTOLOAD_STOP_MEM'));

// Write profiling for container creation and binds after profiler is set (with external captured stamps)
$app->profiler()->start('container.binding', $containerStartTimestamp, $containerStartMemory);
$app->profiler()->stop('container.binding', $containerStopTimestamp, $containerStopMemory);

// define function to access created application everywhere.
$app->profiler()->start('register.app.function');
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
$app->profiler()->stop('register.app.function');

$app->init();

$app->bootstrap();

return $app;