<?php
    $_CONFIG_FOLDER['root'] = "/var/www/_api/";
    $_CONFIG_FOLDER['www']  = "https://api.dexocard.com/";

    $_CONFIG = array
    (
        'DEBUG'             => true,

        'ROOT'              => $_CONFIG_FOLDER['root'],

        'TIMEZONE'          => "Europe/Paris",
        
        'SITE'              => array
        (
            'ROOT_FOLDER'       => $_CONFIG_FOLDER['root'] . 'www/',
            'URL'               => $_CONFIG_FOLDER['www'],
        ),

        'DEX'               => array
        (
            'ROOT_FOLDER'       => $_CONFIG_FOLDER['root'] . 'www/dex/',
            'URL'               => $_CONFIG_FOLDER['www'] . 'dex',
            'MYSQL'             => array
            (
                'HOST'              => '192.168.1.252',
                'PORT'              => 3306,
                'USER'              => 'tcgpokemon',
                'PASS'              => '6mhwGRNc@wY(7*4o',
                'BASE'              => 'tcg'
            ),
        ),

        'API'               => array
        (
            'ROOT_FOLDER'       => $_CONFIG_FOLDER['root'] . 'www/api/',
            'URL'               => $_CONFIG_FOLDER['www'] . 'api/',
        ),

        'PHPFASTCACHE'      => array
        (
            'TYPE'              => 'redis',

            'FILES'             => array
            (
                'FOLDER'            => sys_get_temp_dir() . '/dexocard-dev/'
            ),

            'REDIS'             => array
            (
                'HOST'              => '192.168.1.250',
                'PORT'              => 6379,
                'USER'              => '',
                'PASS'              => '',
            )
        ),
    );
?>