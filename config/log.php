<?php

use OpxCore\Log\LogFile;

return [
    'default' => 'file',
    'loggers' => [
        'file' => [
            'driver' => LogFile::class,
            'filename' => app()->path('storage/logs/opx.log'),
        ],
    ],
    'groups' => [],
];