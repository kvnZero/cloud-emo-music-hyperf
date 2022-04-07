<?php

namespace App\Player\Entity;

class MusicInfo extends BaseInfo
{
    /** @var string $name 名字 */
    public $name;

    /** @var int $time 时长 */
    public $time;

    /** @var CoverInfo $cover 封面信息 */
    public $cover;

    /** @var SignerInfo $signer 演唱者信息 */
    public $signer;

    /** @var string $album 专辑名 */
    public $album;

    /** @var int $recording_time 发布时间 */
    public $recording_time;
}