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
namespace App\Listener;

use App\Finder\Finder;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;

/**
 * @Listener
 */
class WorkStartLoadListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            AfterWorkerStart::class,
        ];
    }

    /**
     * @param QueryExecuted $event
     */
    public function process(object $event)
    {
        $files = Finder::scanFiles(BASE_PATH . '/resource/music');
        var_dump($files);
    }
}
