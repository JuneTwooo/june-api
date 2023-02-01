<?php
   // PhpFastCache
   use Phpfastcache\CacheManager;
   use Phpfastcache\Config\ConfigurationOption;
   use Phpfastcache\Drivers\Redis\Config;

   switch (strtoupper($_CONFIG['PHPFASTCACHE']['TYPE']))
   {
      case 'FILES' : 
      {
         CacheManager::setDefaultConfig(new ConfigurationOption(
         [
               'path' => $_CONFIG['PHPFASTCACHE']['FILES']['FOLDER']
         ]));
         
         $_PHPFASTCACHE = CacheManager::getInstance('files');
      
         break;
      }

      case 'REDIS' :
      {
         // REDIS
         $_PHPFASTCACHE = CacheManager::getInstance('redis', new Config
         ([
               'host'      => $_CONFIG['PHPFASTCACHE']['REDIS']['HOST'],
               'port'      => $_CONFIG['PHPFASTCACHE']['REDIS']['PORT'],
               'password'  => $_CONFIG['PHPFASTCACHE']['REDIS']['PASS'],
         ]));

         break;
      }
    }
?>