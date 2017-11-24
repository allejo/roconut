<?php

namespace AppBundle\Service;

use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

class AnsiTransformer
{
    public function transform($string, $key)
    {
        $converter = new AnsiToHtmlConverter(null, false);
        $string = Crypto::encrypt_v1($converter->convert($string), $key);

        return $string;
    }
}
