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
        $allMusicObject = Finder::getMusicResource('/resource/music', '/resource/music/sort.json');

        foreach ($allMusicObject as $musicInfo) {
            $this->log->info("加载歌曲：" . ($musicInfo->signer->name ?? '未知') . ' - ' . $musicInfo->name);
        }

        Player::setPlayList($allMusicObject);
		Player::next();
		Player::play();
		co(function(){
			while (true) {
				if (Player::isPlay()) {
					Player::nextSecond();
				}
				sleep(1);
			}
		});
    }
}
