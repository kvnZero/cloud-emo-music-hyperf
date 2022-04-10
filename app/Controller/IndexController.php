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

use App\Player\Player;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;

/**
 * @Controller()
 */
class IndexController extends AbstractController
{
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
}
