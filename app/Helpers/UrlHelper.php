<?php

namespace App\Helpers;

class UrlHelper
{
    /**
     * Replace Localhost URL to Production URL
     *
     * @param mixed $data
     * @return mixed
     */
    public static function replaceLocalhostUrl($data)
    {
        $from = "http://127.0.0.1:3013";
        $to = "https://be-intern.bccdev.id/there";

        if (is_string($data)) {
            return str_replace($from, $to, $data);
        }

        if (is_array($data)) {
            array_walk_recursive($data, function (&$value) use ($from, $to) {
                if (is_string($value)) {
                    $value = str_replace($from, $to, $value);
                }
            });
            return $data;
        }

        return $data;
    }
}
