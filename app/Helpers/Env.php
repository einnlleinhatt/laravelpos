<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class Env
{
    public static function envPath()
    {
        $evn = app()->environment();
        if (file_exists(base_path('.env.' . $evn))) {
            return base_path('.env.' . $evn);
        }
        return base_path('.env');
    }

    public static function read()
    {
        $evn = app()->environment();
        return file_get_contents(self::envPath());
    }

    public static function update($data, $changeable = true)
    {
        if (empty($data) || !is_array($data) || !is_file(self::envPath())) {
            return false;
        }

        // return self::updateData($data);
        return $changeable ? self::updateChangeable($data) : self::updateData($data);
    }

    private static function getChangeable($string = null)
    {
        $str = Str::after($string ?: self::read(), '# changeable start');
        return Str::before($str, '# changeable end');
    }

    private static function putContents($contents)
    {
        file_put_contents(self::envPath(), $contents, LOCK_EX);
        return true;
    }

    private static function updateChangeable($data)
    {
        $search  = [];
        $replace = [];
        $env     = self::getChangeable();
        foreach ($data as $key => $value) {
            $original  = env($key);
            $replace[] = $key . '=' . (Str::contains($value, [' ', '$', '#', ',']) ? '"' . $value . '"' : $value);
            $search[]  = $key . '=' . (Str::contains($original, [' ', '$', '#', ',']) ? '"' . $original . '"' : $original);
        }
        $changeable = str_replace($search, $replace, $env);
        $contents   = str_replace(self::getChangeable(), $changeable, self::read());
        return self::putContents($contents);
    }

    private static function updateData($data)
    {
        $search  = [];
        $replace = [];
        $env     = self::read();
        foreach ($data as $key => $value) {
            $original  = env($key);
            $replace[] = $key . '=' . (Str::contains($value, [' ', '$', '#', ',']) ? '"' . $value . '"' : $value);
            $search[]  = $key . '=' . (Str::contains($original, [' ', '$', '#', ',']) ? '"' . $original . '"' : $original);
        }
        $contents = str_replace($search, $replace, $env);
        return self::putContents($contents);
    }
}
