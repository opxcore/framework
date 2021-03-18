<?php

use OpxCore\Log\LogFile;

return [
    'default' => 'file',
    'loggers' => [
        'file' => [
            'driver' => LogFile::class,
            'filename' => env('BASE_PATH') . '/storage/logs/opx.log',
        ],
    ],
    'groups' => [],
];