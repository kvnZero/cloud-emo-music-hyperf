<?php
declare(strict_types=1);

namespace App\Player;

use App\Player\Entity\MusicInfo;

class Player
{
    /** @var MusicInfo[] $playList 扫描的播放列表 */
    static $playList = [];

    /** @var int $playIndex 正在播放的下标 */
    static $playIndex = -1;

    /** @var \DateTime $playTime 正在播放时长 */
    static $playTime = null;

    /** @var bool $playStatus 是否播放 */
    static $playStatus = false;

    /**
     * 下一首
     */
    public static function next()
    {
        self::$playIndex++;
        if (count(self::$playList) == self::$playIndex) {
            self::$playIndex = 0; //回到首
        }
    }

    /**
     * 上一首
     */
    public static function last()
    {
        self::$playIndex--;
        if (count(self::$playList) == -1) {
            self::$playIndex = count(self::$playList); //去到尾
        }
    }

    /**
     * 跳转到某一秒
     * @param \DateTime $dateTime
     */
    public static function jumpTo(\DateTime $dateTime)
    {
        self::$playTime = $dateTime;
    }

    /**
     * 停止
     */
    public static function stop()
    {
        self::$playStatus = false;
    }

    /**
     * 继续
     */
    public static function play()
    {
        self::$playStatus = true;
    }

    /**
     * 设置播放列表
     * @param array $list
     */
    public static function setPlayList(array $list)
    {
        self::$playList = $list;
    }

    /**
     * 获取播放列表
     * @return MusicInfo[]
     */
    public static function getPlayList(): array
    {
        return self::$playList;
    }

    /**
     * 获取当前播放信息
     * @return array
     */
    public static function getCurrentPlayInfo(): array
    {
        return [
            'music' => self::$playList[self::$playIndex],
            'status' => self::$playStatus,
            'time' => self::$playTime
        ];
    }
}