<?php

namespace App\Helpers;

/**
 * @property string $yandexDiskToken
 */
class LocalConfigH
{
    private static $config = null;

    private static function Init()
    {
        if (isset(self::$config)) {
            return;
        }
        self::$config = include(__DIR__ . '/../../config/local.config.php');
    }

    private static $instance = null;

    /**
     * @return self|null
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __get(string $name)
    {
        self::Init();
        /** @var array $config_arr */
        $keys = array_keys(self::$config ?? []);
        $requested_key = '';
        switch ($name) {
            case 'yandexDiskToken':
                $requested_key = 'yandex_disk_jwt';
                break;
        }

        if (!in_array($requested_key, $keys)) {
            return null;
        }
        return self::$config[$requested_key];
    }
}
