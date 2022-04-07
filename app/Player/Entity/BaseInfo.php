<?php

namespace App\Player\Entity;

abstract class BaseInfo
{
    public function fill($data = [])
    {
        foreach ($data as $key => $value) {
            if (is_array($value) && $this->{$key} instanceof BaseInfo) {
                $this->{$key}->fill($value);
            } else {
                $this->{$key} = $value;
            }
        }
    }

    public function toArray(): array
    {
        $outs = [];
        $arr = is_object($this) ? get_object_vars($this) : $this;
        foreach ($arr as $key => $value) {
            if ($value instanceof BaseInfo) {
                $value = $value->toArray();
            }
            $outs[$key] = $value;
        }
        return $outs;
    }
}