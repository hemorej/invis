<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit7e3ed87067b81c967bb4060e5f092265
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
        'M' => 
        array (
            'Monolog\\' => 8,
        ),
        'L' => 
        array (
            'Logger\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/src',
        ),
        'Monolog\\' => 
        array (
            0 => __DIR__ . '/..' . '/monolog/monolog/src/Monolog',
        ),
        'Logger\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit7e3ed87067b81c967bb4060e5f092265::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit7e3ed87067b81c967bb4060e5f092265::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit7e3ed87067b81c967bb4060e5f092265::$classMap;

        }, null, ClassLoader::class);
    }
}
