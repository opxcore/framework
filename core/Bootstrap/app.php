<?php

use OpxCore\Interfaces\ConfigInterface;
use OpxCore\Config\Config;
use OpxCore\Interfaces\ConfigRepositoryInterface;
use OpxCore\Interfaces\ConfigCacheRepositoryInterface;
use OpxCore\Config\ConfigFiles;
use OpxCore\Config\ConfigCacheFile;

$baseDir = dirname(__DIR__, 2);

$app = new \OpxCoreFramework\Application($baseDir);

/*
* Make bindings of implementations to interfaces
*/

// Bind config cache driver with parameters
$app->bind(ConfigCacheRepositoryInterface::class, ConfigCacheFile::class,
    ['path' => $app->path('cache/config'), 'prefix' => 'config', 'extension' => 'cache']
);

// Bind config repository driver with parameters
$app->bind(ConfigRepositoryInterface::class, ConfigFiles::class, ['path' => $app->path('config')]);

// Bind config driver
$app->bind(ConfigInterface::class, Config::class);

$app->init();

return $app;