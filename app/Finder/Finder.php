<?php

namespace App\Finder;

use App\Player\Entity\CoverInfo;
use App\Player\Entity\MusicInfo;
use App\Player\Entity\SignerInfo;
use App\Player\Player;
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
        return array_filter($files, function ($item) use ($type) {
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
        if (isset($info['tags']['vorbiscomment'])) {
            $analyzeKey = 'vorbiscomment';
        }
        if (isset($info['tags']['id3v2'])) {
            $analyzeKey = 'id3v2';
        }
        $musicInfo->filename = pathinfo($file, PATHINFO_FILENAME);
        $musicInfo->path = $file;
        $musicInfo->url = config('player.default.host') . '/'. config('player.default.static.path') .'/' . pathinfo($file, PATHINFO_BASENAME);
        if (!empty($analyzeKey)) {
            $musicInfo->signer = join(',', $info['tags'][$analyzeKey]['artist']);
            $musicInfo->name = join(',', $info['tags'][$analyzeKey]['title']);
            $musicInfo->album = join(',', $info['tags'][$analyzeKey]['album']);
            if (!empty($info['tags'][$analyzeKey]['recording_time'])) {
                $musicInfo->recording_time = (new \DateTime($info['tags'][$analyzeKey]['recording_time'][0]))->getTimestamp();
            }
        } else {
            //?????????????????????
            $fileNameArr = explode('-', pathinfo($info['filename'], PATHINFO_FILENAME));
            if (count($fileNameArr) == 2) {
                $musicInfo->signer = trim($fileNameArr[0]);
                $musicInfo->name = trim($fileNameArr[1]);
            }
        }
        $musicInfo->time = round($info['playtime_seconds'], 0, PHP_ROUND_HALF_DOWN);

        $coverImgPath = $info['filepath'] . '/' . pathinfo($info['filename'], PATHINFO_FILENAME) . '.jpg';
        if (file_exists($coverImgPath)) {
            // ????????????????????? ????????????
            $musicInfo->cover = $coverImgPath;
        } else {
            //?????????????????????????????????????????????
            if (!empty($info['comments']['picture'][0]['data'])) {
                $fileType = explode('/', $info['comments']['picture'][0]['image_mime'])[1];
                $coverImgPath = $info['filepath'] . '/' . pathinfo($info['filename'], PATHINFO_FILENAME) . '.' . $fileType;
                if (!file_exists($coverImgPath)) {
                    $fp_local = fopen($coverImgPath, 'wb'); //??????????????????
                    fwrite($fp_local, $info['comments']['picture'][0]['data']);
                    fclose($fp_local);
                }
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
                $singerInfo->img = $info['filepath'] . '/' . $musicInfo->signer . '-' . $musicInfo->name . '.jpg'; //??????-?????? ???????????????
            }
            if (empty($singerInfo->img) && file_exists($info['filepath'] . '/' . $musicInfo->signer . '.jpg')) {
                $singerInfo->img = $info['filepath'] . '/' . $musicInfo->signer . '.jpg'; //??????-?????? ???????????????
            }
            $musicInfo->signer = $singerInfo;
        }

        if (is_null($musicInfo->cover)) {
            $musicInfo->cover = new CoverInfo();
        }
        if (is_null($musicInfo->signer)) {
            $musicInfo->signer = new SignerInfo(); //???????????????
        }

        $jsonPath  = $info['filepath'] . '/' . $musicInfo->name . '.json';
        if (file_exists($jsonPath)) {
            //???????????????json ?????????????????????
            $fd = fopen($jsonPath, 'r');
            $json = json_decode(fread($fd, filesize($jsonPath)), true);
            fclose($fd);
            if (is_array($json)) {
                //????????????
                $musicInfo->fill($json);
            }
        }
        return $musicInfo;
    }

    public static function sortPlayList($allMusicObject, $sortArr = [])
    {
        $notInSortList = [];
        $inSortList = [];

        /**
         * @var int $i
         * @var MusicInfo $object
         */
        foreach ($allMusicObject as $i => $object) {
            if (!in_array($object->filename, $sortArr)) {
                $notInSortList[] = $object;
                unset($allMusicObject[$i]);
            }
        }
        foreach ($sortArr as $sort) {
            foreach ($allMusicObject as $object) {
                if ($sort == $object->filename) {
                    $inSortList[] = $object;
                    break;
                }
            }
        }
        return array_merge($inSortList, $notInSortList);
    }

    public static function getMusicResource($path = '/resource/music', $sortPath = '/resource/music/sort.json')
    {
        $files = self::scanFiles(BASE_PATH . $path);
        $musicType = [
            'mp3', 'wav', 'flac'
        ];
        $files = array_values(array_filter($files, function ($file) use ($musicType) {
            return in_array(pathinfo($file, PATHINFO_EXTENSION), $musicType);
        }));

        $allMusicObject = [];
        foreach ($files as $file) {
            $musicInfo = Finder::getMusicInfo($file);
            $allMusicObject[] = $musicInfo;
        }

        //????????????
        if (!empty($sortPath) && file_exists(BASE_PATH . $sortPath)) {
            $fp_local = fopen(BASE_PATH . '/resource/music/sort.json', 'r'); //??????????????????
            $sortJson = fread($fp_local, filesize(BASE_PATH . '/resource/music/sort.json'));
            fclose($fp_local);
            $sortJson = json_decode($sortJson, true);
            $allMusicObject = Finder::sortPlayList($allMusicObject, $sortJson['sort']);
        }
        return $allMusicObject;
    }
}
