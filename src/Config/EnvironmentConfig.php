<?php

namespace Phoenix\Config;

class EnvironmentConfig
{
    private $configuration;

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function getAdapter()
    {
        return $this->configuration['adapter'];
    }

    public function getDsn()
    {
        if ($this->checkConfigValue('dsn')) {
            return $this->configuration['dsn'];
        }
        $dsn = $this->configuration['adapter'] . ':dbname=' . $this->configuration['db_name'] . ';host=' . $this->configuration['host'];
        if ($this->checkConfigValue('port')) {
            $dsn .= ';port=' . $this->configuration['port'];
        }
        if ($this->checkConfigValue('charset')) {
            if ($this->configuration['adapter'] == 'pgsql') {
                $dsn .= ';options=\'--client_encoding=' . $this->configuration['charset'] . '\'';
            } else {
                $dsn .= ';charset=' . $this->configuration['charset'];
            }
        }
        return $dsn;
    }

    public function getUsername()
    {
        return isset($this->configuration['username']) ? $this->configuration['username'] : null;
    }

    public function getPassword()
    {
        return isset($this->configuration['password']) ? $this->configuration['password'] : null;
    }

    public function getCharset()
    {
        return isset($this->configuration['charset']) ? $this->configuration['charset'] : 'utf8';
    }

    private function checkConfigValue($key)
    {
        return isset($this->configuration[$key]) && $this->configuration[$key];
    }
}
