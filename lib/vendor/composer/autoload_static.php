<?php

class ComposerStaticInitc9d1712c89a185e5e43835a7b621bf09
{
    public static $files = array (
    );

    public static function getInitializer(\Composer\Autoload\ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr4 = ComposerStaticInitc9d1712c89a185e5e43835a7b621bf09::$prefixesPsr4;
            $loader->fallbackDirsPsr4 = ComposerStaticInitc9d1712c89a185e5e43835a7b621bf09::$fallbackDirsPsr4;
        }, null, \Composer\Autoload\ClassLoader::class);
    }

    public static $prefixesPsr4 = array (
        'RtRaselBD\\Cosmotown\\' =>
            array (
                0 => __DIR__ . '/../..' . '/src',
            ),
        'WHMCS\\Module\\Registrar\\Cosmotown\\' =>
            array (
                0 => __DIR__ . '/../..' . '',
            ),
    );

    public static $fallbackDirsPsr4 = array (
    );
}
