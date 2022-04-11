<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    'default' => [
        'host' => env('PLAYER_HOST', 'http://emo_server.abigeater.com'),
        'static' => [
            'path' => env('PLAYER_STATIC_PATH', 'static'),
        ]
    ],
];
