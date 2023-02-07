<?php
   $_BENCH_START_TIME = microtime(true);

   // session
      session_start();
      if (empty($_SESSION['token'])) { $_SESSION['token'] = ''; usleep(100000); }

   // Debug
		ini_set('display_errors', E_ALL);
		error_reporting(1);

   // Headers
      header("Access-Control-Allow-Origin: *");
      header('Content-Type: application/json; charset=utf-8');

   // HTTP Origins
      switch (empty($_SERVER['HTTP_ORIGIN']) ? 'no_origin' : $_SERVER['HTTP_ORIGIN'])
      {
         case 'https://www.dexocard.com': { break; }
         case 'no_origin':                { break; }
      }

	// CONFIG & INIT
      require_once(__DIR__ . '/config.php');
      require_once($_CONFIG['ROOT'] . 'v1/init.php');
      require_once($_CONFIG['ROOT'] . 'v1/class/print_json.php');
      require_once($_CONFIG['ROOT'] . 'v1/class/token.php');
      require_once($_CONFIG['ROOT'] . 'v1/class/route.php');
      require_once($_CONFIG['ROOT'] . 'v1/class/mysql.php');
      require_once($_CONFIG['ROOT'] . 'v1/class/log.php');
      require_once($_CONFIG['ROOT'] . 'v1/error-handler.php');

   // JSON Print
      $_JSON_PRINT   = new print_JSON();
      $_TOKEN        = new token();
      $_ROUTE        = new route();
      $_LOG          = new logFile();
      $_MYSQL        = new MySQL();
      
   // Routing selon l'HOST
      $_PUBLIC_KEY   = (empty($_SERVER['HTTP_AUTHORIZATION']) ? NULL : $_SERVER['HTTP_AUTHORIZATION']);

      try
      {
         $_PRODUCT   = NULL;
         switch ($_SERVER['HTTP_HOST'])
         {
            case 'api.dexocard.com' : 
            {
               $_PRODUCT   = $_CONFIG['ROOT'] . 'v1/products/dexocard/';

               // CARTES

               // TCGO
                  $_ROUTE->get('/v1/tcgo/code',                            $_PRODUCT . 'tcgo/code');

               
               
               break;
            }
         }
      }
      catch (Exception $e) { exceptions_error_handler(0, $e->getMessage(), $e->getFile(), $e->getLine()); }
      catch (Throwable $e) { exceptions_error_handler(0, $e->getMessage(), $e->getFile(), $e->getLine()); }

   // 404
      $_JSON_PRINT->fail("unknow endpoint");
      $_JSON_PRINT->print();
?>