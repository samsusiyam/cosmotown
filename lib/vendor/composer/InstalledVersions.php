<?php

namespace Composer\Autoload;

class InstalledVersions
{
    private static $installed;
    private static $canGetVersions;

    public static function isInstalled($packageName)
    {
        return isset(self::getInstalled()[0]['versions'][$packageName]);
    }

    public static function getVersion($packageName)
    {
        $installed = self::getInstalled();
        foreach ($installed as $item) {
            if (isset($item['versions'][$packageName])) {
                return $item['versions'][$packageName];
            }
        }

        return null;
    }

    public static function getInstalled()
    {
        if (null !== self::$installed) {
            return self::$installed;
        }

        require __DIR__ . '/installed.php';
        self::$installed = array(0 => $GLOBALS['__composer_installed_files'][0] ?? require __DIR__ . '/installed.php');

        return self::$installed;
    }

    public static function getRawData()
    {
        return self::getInstalled()[0];
    }
}
