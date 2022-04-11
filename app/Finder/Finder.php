<?php

namespace App\Finder;

use App\Player\Entity\CoverInfo;
use App\Player\Entity\MusicInfo;
use App\Player\Entity\SignerInfo;
use JamesHeinrich\GetID3\GetID3;

class Finder
{
    public static function scanFiles(string $path = ''): array
    {
        $result = [];
        if (is_dir($path)) {
            $dir = dir($path);
            while (false !== ($name = $dir->read())) {
                if ($name !== '.' && $name !== '..') {
                    $subPath = $path . DIRECTORY_SEPARATOR . $name;
                    if (is_dir($subPath)) {
                        $result[] = array_merge($result, self::scanFiles($subPath));
                    } else {
                        $result[] = $subPath;
                    }
                }
            }
            $dir->close();
        }
        return $result;
    }

    public static function filterFileType($type = 'mp3', $files = [])
    {
        return array_filter($files, function ($item) use ($type){
            return pathinfo($item, PATHINFO_EXTENSION) == $type;
        });
    }

    public static function getMusicInfo($file): MusicInfo
    {
        $getID3 = new getID3();
        $getID3->setOption(array(
            'encoding' => 'UTF-8',
            'options_audiovideo_quicktime_ReturnAtomData' => true,
            'option_md5_data' => false
        ));
        $info = $getID3->analyze($file);

        $musicInfo = new MusicInfo();

		$analyzeKey = '';
		if(isset($info['tags']['vorbiscomment'])) {
			$analyzeKey = 'vorbiscomment';
		}
		if(isset($info['tags']['id3v2'])) {
			$analyzeKey = 'id3v2';
		}
		$musicInfo->path = $file;
		$musicInfo->url = config('player.default.host') . '/'. config('player.default.static.path') .'/' . pathinfo($file, PATHINFO_BASENAME);
		$musicInfo->signer = join(',', $info['tags'][$analyzeKey]['artist']);
        $musicInfo->name = join(',', $info['tags'][$analyzeKey]['title']);
        $musicInfo->album = join(',', $info['tags'][$analyzeKey]['album']);
		if (!empty($info['tags'][$analyzeKey]['recording_time'])) {
			$musicInfo->recording_time = (new \DateTime($info['tags'][$analyzeKey]['recording_time'][0]))->getTimestamp();
		}
        $musicInfo->time = round($info['playtime_seconds'], 0, PHP_ROUND_HALF_DOWN);

        $coverImgPath = $info['filepath'] . '/' . pathinfo($info['filename'], PATHINFO_FILENAME) . '.jpg';
        if (file_exists($coverImgPath)) {
            // 如果有同名图片 作为封面
            $musicInfo->cover = $coverImgPath;
        } else {
            //没有的话看歌曲文件是否带有封面
            if (!empty($info['comments']['picture'][0]['data'])) {
                $fileType = explode('/', $info['comments']['picture'][0]['image_mime'])[1];
                $coverImgPath = $info['filepath'] . '/' . pathinfo($info['filename'], PATHINFO_FILENAME) . '.' . $fileType;
                $fp_local = fopen($coverImgPath, 'wb'); //保存到同目录
                fwrite($fp_local, $info['comments']['picture'][0]['data']);
                fclose($fp_local);
                $musicInfo->cover = $coverImgPath;
            }
        }
        if (!empty($musicInfo->cover)) {
            $coverInfo = new CoverInfo();
            $coverInfo->path = $musicInfo->cover;
            $coverInfo->size = filesize($musicInfo->cover);
            $coverInfo->type = pathinfo($musicInfo->cover, PATHINFO_EXTENSION);
			$coverInfo->url = config('player.default.host') . '/'. config('player.default.static.path') .'/'.pathinfo($musicInfo->cover, PATHINFO_BASENAME);
            $musicInfo->cover = $coverInfo;
        }
        if (!empty($musicInfo->signer)) {
            $singerInfo = new SignerInfo();
            $singerInfo->name = $musicInfo->signer;
            if (file_exists($info['filepath'] . '/' . $musicInfo->signer . '-' . $musicInfo->name . '.jpg')) {
                $singerInfo->img = $info['filepath'] . '/' . $musicInfo->signer . '-' . $musicInfo->name . '.jpg'; //歌手-歌名 为最高优先
            }
            if (empty($singerInfo->img) && file_exists($info['filepath'] . '/' . $musicInfo->signer . '.jpg')) {
                $singerInfo->img = $info['filepath'] . '/' . $musicInfo->signer . '.jpg'; //歌手-歌名 为最高优先
            }
            $musicInfo->signer = $singerInfo;
        }

        if (is_null($musicInfo->cover)) {
            $musicInfo->cover = new CoverInfo();
        }
        if (is_null($musicInfo->signer)) {
            $musicInfo->signer = new SignerInfo(); //赋予空对象
        }

        $jsonPath  = $info['filepath'] . '/' . $musicInfo->name . '.json';
        if (file_exists($jsonPath)) {
            //如果有同名json 则视为配置文件
            $fd = fopen($jsonPath, 'r');
            $json = json_decode(fread($fd, filesize($jsonPath)), true);
            fclose($fd);
            if (is_array($json)) {
                //补充配置
                $musicInfo->fill($json);
            }
        }
        return $musicInfo;
    }
}