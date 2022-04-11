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
namespace App\Controller;

use App\Finder\Finder;
use App\Player\Player;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;

/**
 * @Controller()
 */
class IndexController extends AbstractController
{
    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $log;

    /**
	 * @GetMapping(path="/get/play/list", methods="get")
	 * @return \Psr\Http\Message\ResponseInterface
	 */
    public function playList(): \Psr\Http\Message\ResponseInterface
	{
		$playList = Player::getPlayList();
		$list = [];
		foreach($playList as $item) {
			$list[] = $item->toArray();
		}
		return $this->response->json([
			'code' => 200,
			'data' => $list
		]);
    }

	/**
	 * @GetMapping(path="/get/play", methods="get")
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function play(): \Psr\Http\Message\ResponseInterface
	{
		return $this->response->json([
			'code' => 200,
			'data' => Player::getCurrentPlayInfo()
		]);
	}

    /**
     * @GetMapping(path="/reload/play/list", methods="get")
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function reload(): \Psr\Http\Message\ResponseInterface
    {
        $allMusicObject = Finder::getMusicResource('/resource/music', '/resource/music/sort.json');

        foreach ($allMusicObject as $musicInfo) {
            $this->log->info("加载歌曲：" . ($musicInfo->signer->name ?? '未知') . ' - ' . $musicInfo->name);
        }

        Player::setPlayList($allMusicObject);
        Player::first();

        return $this->response->json([
            'code' => 200,
            'data' => Player::getCurrentPlayInfo()
        ]);
    }
}
