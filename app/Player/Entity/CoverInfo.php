<?php

namespace App\Player\Entity;

class CoverInfo extends BaseInfo
{
    /** @var string $path 路径 */
    public $path;

	/** @var string $url 访问路径 */
	public $url;

    /** @var string $size 文件大小 */
    public $size;

    /** @var string $type 文件类型 */
    public $type;
}