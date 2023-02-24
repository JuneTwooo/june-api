<?php
    $_CONFIG = array
    (
        'DEBUG'             => true,
        
        'ROOT'              => "/var/www/_api/",

        'PRODUCTS'          => array
        (
            'DEXOCARD'          => array
            (
                'ROOT'              => '/var/www/_res/dexocard/',
                'URL'               => 'https://www.dexocard.com/',
                'RES'               => 'https://res.dexocard.com/'
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