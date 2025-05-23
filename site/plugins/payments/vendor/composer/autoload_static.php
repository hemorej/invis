<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticIniteaa10299fa512c51fc49a8c37be39488
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Stripe\\' => 7,
        ),
        'P' => 
        array (
            'Payments\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Stripe\\' => 
        array (
            0 => __DIR__ . '/..' . '/stripe/stripe-php/lib',
        ),
        'Payments\\' => 
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
            $loader->prefixLengthsPsr4 = ComposerStaticIniteaa10299fa512c51fc49a8c37be39488::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticIniteaa10299fa512c51fc49a8c37be39488::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticIniteaa10299fa512c51fc49a8c37be39488::$classMap;

        }, null, ClassLoader::class);
    }
}
