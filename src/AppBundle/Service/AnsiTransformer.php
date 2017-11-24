<?php

namespace AppBundle\Service;

use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

class AnsiTransformer
{
    public function transform($string, $key)
    {
        $converter = new AnsiToHtmlConverter(null, false);

        $string = $this->xtermify($string);
        $string = Crypto::encrypt_v1($converter->convert($string), $key);

        return $string;
    }

    /**
     * Replace ANSI-RGB to xterm colors
     *
     * @link https://forums.bzflag.org/viewtopic.php?f=2&t=18559#p172364
     *
     * @param  string $string
     *
     * @return string mixed
     */
    private function xtermify($string)
    {
        $rgbToXterm = array(
            '/\[38;2;255;255;0m/'   => '[33m', // yellow
            '/\[38;2;255;0;0m/'     => '[31m', // red
            '/\[38;2;0;255;0m/'     => '[32m', // green
            '/\[38;2;25;51;255m/'   => '[34m', // blue
            '/\[38;2;255;0;255m/'   => '[35m', // purple
            '/\[38;2;255;255;255m/' => '[37m', // white (observer)
            '/\[38;2;204;204;204m/' => '[38;5;252m', // grey (rabbit)
            '/\[38;2;255;127;0m/'   => '[38;5;208m', // orange (hunter)
        );

        foreach ($rgbToXterm as $needle => $transform) {
            $string = preg_replace($needle, $transform, $string);
        }

        return $string;
    }
}
