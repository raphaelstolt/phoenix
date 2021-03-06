<?php

namespace Phoenix\Config\Parser;

use Phoenix\Exception\ConfigException;

class ConfigParserFactory
{
    public static function instance($type)
    {
        $type = strtolower($type);
        if ($type == 'php') {
            return new PhpConfigParser();
        }
        if (in_array($type, ['yml', 'yaml'])) {
            return new YamlConfigParser();
        }
        if ($type === 'neon') {
            return new NeonConfigParser();
        }
        if ($type === 'json') {
            return new JsonConfigParser();
        }
        throw new ConfigException('Unknown config type "' . $type . '"');
    }
}
