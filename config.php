<?php
    $_CONFIG = array
    (
        'DEBUG'             => true,
        
        'ROOT'              => "/var/www/_api/",

        'TIMEZONE'          => "Europe/Paris",

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