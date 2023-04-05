<?php
    $_CONFIG = array
    (
        'DEBUG'             => true,
        
        'ROOT'              => "/var/www/_api/",

        'PRODUCTS'          => array
        (
            'DEXOCARD'          => array
            (
                'RES_ROOT'          => '/var/www/_res/dexocard/',
                'RES_URL'           => 'https://res.dexocard.com/',
                'ROOT'              => '/var/www/dexocard.com/www/',
                'URL'               => 'https://www.dexocard.com/',
            ),
        ),

        'TIMEZONE'          => "Europe/Paris",

        'PHPFASTCACHE'      => array
        (
            'TYPE'              => 'redis',

            'FILES'             => array
            (
                'FOLDER'            => sys_get_temp_dir() . '/api-dev/'
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