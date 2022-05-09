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
        $autoListen = config('player.default.listen.music.auto_load', true);
        if ($autoListen && $autoListen !== 'false') { //防止环境变量字符串化
            $this->log->info("已开启自动监听歌曲目录，如需关闭请修改配置。");
            co(function () {
                $speed = ((int)config('player.default.listen.speed', 10)) ?: 10;
                while (true) {
                    $list = Finder::getMusicResource('/resource/music', '/resource/music/sort.json');
                    if ($list != Player::getPlayList()) {
                        $this->log->info("监听到歌曲目录变动, 重新加载歌单列表, 加载后歌单数量: " . count($list));
                        foreach ($list as $musicInfo) {
                            $this->log->info("加载歌曲：" . ($musicInfo->signer->name ?? '未知') . ' - ' . $musicInfo->name);
                        }
                        Player::setPlayList($list);
                        Player::first();
                        Player::play();
                    }
                    sleep($speed);
                }
            });
        } else {
            $allMusicObject = Finder::getMusicResource('/resource/music', '/resource/music/sort.json');
            foreach ($allMusicObject as $musicInfo) {
                $this->log->info("加载歌曲：" . ($musicInfo->signer->name ?? '未知') . ' - ' . $musicInfo->name);
            }
            Player::setPlayList($allMusicObject);
            Player::first();
            Player::play();
        }

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
