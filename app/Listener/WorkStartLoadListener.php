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
            'mp3', 'wav', 'flac'
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

        //加载顺序
        if (file_exists(BASE_PATH . '/resource/music/sort.json')) {
            $fp_local = fopen(BASE_PATH . '/resource/music/sort.json', 'r'); //保存到同目录
            $sortJson = fread($fp_local, filesize(BASE_PATH . '/resource/music/sort.json'));
            fclose($fp_local);
            $sortJson = json_decode($sortJson, true);
            $allMusicObject = Finder::sortPlayList($allMusicObject, $sortJson['sort']);
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
