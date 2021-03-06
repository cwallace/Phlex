<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitaaa161de2c47514ec2f8a3b9623e8014
{
    public static $files = array (
        'a0edc8309cc5e1d60e3047b5df6b7052' => __DIR__ . '/..' . '/guzzlehttp/psr7/src/functions_include.php',
        'c964ee0ededf28c96ebd9db5099ef910' => __DIR__ . '/..' . '/guzzlehttp/promises/src/functions_include.php',
        '37a3dc5111fe8f707ab4c132ef1dbc62' => __DIR__ . '/..' . '/guzzlehttp/guzzle/src/functions_include.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Component\\EventDispatcher\\' => 34,
        ),
        'R' => 
        array (
            'Rhumsaa\\Uuid\\' => 13,
        ),
        'P' => 
        array (
            'Psr\\Http\\Message\\' => 17,
        ),
        'K' => 
        array (
            'Kryptonit3\\SickRage\\' => 20,
        ),
        'G' => 
        array (
            'GuzzleHttp\\Psr7\\' => 16,
            'GuzzleHttp\\Promise\\' => 19,
            'GuzzleHttp\\' => 11,
            'GravityMedia\\Urn\\' => 17,
            'GravityMedia\\Ssdp\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Component\\EventDispatcher\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/event-dispatcher',
        ),
        'Rhumsaa\\Uuid\\' => 
        array (
            0 => __DIR__ . '/..' . '/rhumsaa/uuid/src',
        ),
        'Psr\\Http\\Message\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-message/src',
        ),
        'Kryptonit3\\SickRage\\' => 
        array (
            0 => __DIR__ . '/..' . '/kryptonit3/sickrage/src',
        ),
        'GuzzleHttp\\Psr7\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/psr7/src',
        ),
        'GuzzleHttp\\Promise\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/promises/src',
        ),
        'GuzzleHttp\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/guzzle/src',
        ),
        'GravityMedia\\Urn\\' => 
        array (
            0 => __DIR__ . '/..' . '/gravitymedia/urn/src',
        ),
        'GravityMedia\\Ssdp\\' => 
        array (
            0 => __DIR__ . '/..' . '/gravitymedia/ssdp/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'G' => 
        array (
            'Guzzle\\Stream' => 
            array (
                0 => __DIR__ . '/..' . '/guzzle/stream',
            ),
            'Guzzle\\Parser' => 
            array (
                0 => __DIR__ . '/..' . '/guzzle/parser',
            ),
            'Guzzle\\Http' => 
            array (
                0 => __DIR__ . '/..' . '/guzzle/http',
            ),
            'Guzzle\\Common' => 
            array (
                0 => __DIR__ . '/..' . '/guzzle/common',
            ),
        ),
    );

    public static $classMap = array (
        'Config_Lite' => __DIR__ . '/..' . '/pear-pear.php.net/Config_Lite/Config/Lite.php',
        'Config_Lite_Exception' => __DIR__ . '/..' . '/pear-pear.php.net/Config_Lite/Config/Lite/Exception.php',
        'Config_Lite_Exception_InvalidArgument' => __DIR__ . '/..' . '/pear-pear.php.net/Config_Lite/Config/Lite/Exception/InvalidArgument.php',
        'Config_Lite_Exception_Runtime' => __DIR__ . '/..' . '/pear-pear.php.net/Config_Lite/Config/Lite/Exception/Runtime.php',
        'Config_Lite_Exception_UnexpectedValue' => __DIR__ . '/..' . '/pear-pear.php.net/Config_Lite/Config/Lite/Exception/UnexpectedValue.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitaaa161de2c47514ec2f8a3b9623e8014::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitaaa161de2c47514ec2f8a3b9623e8014::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitaaa161de2c47514ec2f8a3b9623e8014::$prefixesPsr0;
            $loader->classMap = ComposerStaticInitaaa161de2c47514ec2f8a3b9623e8014::$classMap;

        }, null, ClassLoader::class);
    }
}
