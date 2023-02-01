<?php
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
      require_once(__DIR__ . '/../config.php');
      require_once($_CONFIG['ROOT'] . 'init.php');
      require_once($_CONFIG['ROOT'] . 'v1/class/print_json.php');
      require_once($_CONFIG['ROOT'] . 'v1/class/token.php');
      require_once($_CONFIG['ROOT'] . 'v1/class/route.php');
      require_once($_CONFIG['ROOT'] . 'v1/error-handler.php');

   // JSON Print
      $_JSON_PRINT   = new print_JSON();
      $_TOKEN        = new token();
      $_ROUTE        = new route();

   // Routing selon l'HOST
      try
      {
         $_PRODUCT   = NULL;
         switch ($_SERVER['HTTP_HOST'])
         {
            case 'api.dexocard.com' : 
            {
               $_PRODUCT   = $_CONFIG['ROOT'] . 'v1/products/dexocard/';

               $_ROUTE->get('/v1/$_PUBLIC_KEY/tcgo/code',                               $_PRODUCT . 'tcgo/code');
               $_ROUTE->get('/v1/$_PUBLIC_KEY/tcgo/code/$_NB',                          $_PRODUCT . 'tcgo/code');
               
               break;
            }
         }
      }
      catch (Exception $e) { exceptions_error_handler(0, $e->getMessage(), $e->getFile(), $e->getLine()); }
      catch (Throwable $e) { exceptions_error_handler(0, $e->getMessage(), $e->getFile(), $e->getLine()); }
?>