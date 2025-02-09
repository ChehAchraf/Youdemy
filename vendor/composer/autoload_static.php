<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitedf557e976a7d6f1a407b4a3c41ace1a
{
    public static $files = array (
        '2cffec82183ee1cea088009cef9a6fc3' => __DIR__ . '/..' . '/ezyang/htmlpurifier/library/HTMLPurifier.composer.php',
    );

    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'H' => 
        array (
            'HTMLPurifier' => 
            array (
                0 => __DIR__ . '/..' . '/ezyang/htmlpurifier/library',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitedf557e976a7d6f1a407b4a3c41ace1a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitedf557e976a7d6f1a407b4a3c41ace1a::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitedf557e976a7d6f1a407b4a3c41ace1a::$prefixesPsr0;
            $loader->classMap = ComposerStaticInitedf557e976a7d6f1a407b4a3c41ace1a::$classMap;

        }, null, ClassLoader::class);
    }
}
