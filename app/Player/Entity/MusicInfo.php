<?php

namespace App\Player\Entity;

class MusicInfo extends BaseInfo
{
	/** @var string $path 路径 */
	public $path;

	/** @var string $url 访问路径 */
	public $url;

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

    /** @var string $filename 源文件名 */
    public $filename;
}