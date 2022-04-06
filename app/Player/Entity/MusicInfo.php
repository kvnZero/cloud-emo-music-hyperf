<?php

namespace App\Player\Entity;

class MusicInfo
{
    /** @var string $name 名字 */
    public $name;

    /** @var int $time 时长 */
    public $time;

    /** @var CoverInfo $cover 封面信息 */
    public $cover;

    /** @var SignerInfo $signer 演唱者信息 */
    public $signer;
}