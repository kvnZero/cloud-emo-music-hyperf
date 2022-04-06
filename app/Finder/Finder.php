<?php

namespace App\Finder;

class Finder
{
    public static function scanFiles(string $path = ''): array
    {
        if (is_dir($path)) {
            $subs = [];
            $dir = dir($path);
            while (false !== ($name = $dir->read())) {
                if ($name !== '.' && $name !== '..') {
                    $subPath = $path . DIRECTORY_SEPARATOR . $name;
                    $list = self::scanFiles($subPath);
                    $subs[$name] = $list;
                }
            }
            $dir->close();
        } else {
            return [];
        }
        return $subs;
    }

    public static function filterFileType($type = 'mp3', $files = [])
    {
        return array_filter($files, function ($item) use ($type){
            return pathinfo($item, PATHINFO_EXTENSION) == $type;
        });
    }
}