<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit89e0d95ff5689f5cfaff63d79cf4e55b
{
    public static $prefixLengthsPsr4 = array (
        'B' => 
        array (
            'Bluerhinos\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Bluerhinos\\' => 
        array (
            0 => __DIR__ . '/..' . '/bluerhinos/phpmqtt',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit89e0d95ff5689f5cfaff63d79cf4e55b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit89e0d95ff5689f5cfaff63d79cf4e55b::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit89e0d95ff5689f5cfaff63d79cf4e55b::$classMap;

        }, null, ClassLoader::class);
    }
}