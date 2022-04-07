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
use App\Player\Player;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;

/**
 * @Listener
 */
class WorkStartLoadListener implements ListenerInterface
{
    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $log;

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
        $musicType = [
            'mp3', 'wav'
        ];
        $files = array_values(array_filter($files, function($file) use($musicType) {
            return in_array(pathinfo($file, PATHINFO_EXTENSION), $musicType);
        }));

        $allMusicObject = [];
        foreach ($files as $file) {
            $musicInfo = Finder::getMusicInfo($file);
            $this->log->info("加载歌曲：" . ($musicInfo->signer->name ?? '未知') . ' - ' . $musicInfo->name);
            $allMusicObject[] = $musicInfo;
        }
        Player::setPlayList($allMusicObject);
    }
}
