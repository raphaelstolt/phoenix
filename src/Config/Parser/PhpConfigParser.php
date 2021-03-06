<?php

namespace Phoenix\Config\Parser;

use Phoenix\Exception\ConfigException;

class PhpConfigParser implements ConfigParserInterface
{
    public function parse($filename)
    {
        if (!file_exists($filename)) {
            throw new ConfigException('File "' . $filename . '" not found');
        }
        return require $filename;
    }
}
