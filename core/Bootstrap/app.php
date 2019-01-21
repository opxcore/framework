<?php

$baseDir = dirname(__DIR__, 2);

$app = new \OpxCoreFramework\Application($baseDir);

/*
* Make bindings of implementations to interfaces
*/

// Bind config cache driver with parameters
$app->bind(
    \OpxCore\Interfaces\ConfigCacheRepositoryInterface::class,
    \OpxCore\Config\ConfigCacheFile::class,
    ['path' => $app->path('cache/config'), 'prefix' => 'config', 'extension' => 'cache']
);

// Bind config repository driver with parameters
$app->bind(
    \OpxCore\Interfaces\ConfigRepositoryInterface::class,
    \OpxCore\Config\ConfigFiles::class,
    ['path' => $app->path('config')]
);

// Bind config driver
$app->bind(\OpxCore\Interfaces\ConfigInterface::class, \OpxCore\Config\Config::class);

$app->init();

return $app;