<?php

namespace App\Finder;

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
}