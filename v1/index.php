<?php
   $_BENCH_START_TIME = microtime(true);

   // Debug
		ini_set('display_errors', E_ALL);
		error_reporting(1);

   // Headers
      header("Access-Control-Allow-Origin: *");
      header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
      header("Access-Control-Allow-Headers: *");
      header('Cache-Control: no-cache, must-revalidate');

   // HTTP Origins
      switch (empty($_SERVER['HTTP_ORIGIN']) ? 'no_origin' : $_SERVER['HTTP_ORIGIN'])
      {
         case 'https://www.dexocard.com': { break; }
         case 'no_origin':                { break; }
      }

   // GLOBALS
      $_METHOD    = (empty($_SERVER['REQUEST_METHOD']) ? NULL : strtoupper($_SERVER['REQUEST_METHOD']));
      $_DATA_DEBUG = array();

   // CHECK METHOD HEADER OVERRIDE
      if (!empty($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']))
      {
         $_METHOD = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
      }

   // CHECK IS SET METHOD
      switch ($_METHOD)
      {
         case 'GET'     : { break; }
         case 'POST'    : { break; }
         case 'PUT'     : { break; }
         case 'DELETE'  : { break; }
         default        :
         {
            $_JSON_PRINT->fail("no allowed method found");
            $_JSON_PRINT->print();
         }
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
      require_once($_CONFIG['ROOT'] . 'v1/functions.global.php');

   // Class
      $_JSON_PRINT   = new print_JSON();
      $_TOKEN        = new token();
      $_ROUTE        = new route();
      $_LOG          = new logFile();
      $_MYSQL        = new MySQL();

   // SQL Tables List
      $_TABLE_LIST = array
      (
         'api'       => "api",
         'dexocard'  => "tcg",
      );

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

               // TCG
                  $_ROUTE->GET('/v1/tcg/card',                                $_PRODUCT . 'tcg/card');
                  $_ROUTE->GET('/v1/tcg/card/price',                          $_PRODUCT . 'tcg/card-price');
                  $_ROUTE->GET('/v1/tcg/set',                                 $_PRODUCT . 'tcg/set');

               // TCG O
                  $_ROUTE->GET('/v1/tcgo/code',                               $_PRODUCT . 'tcgo/code');

               // STORE
                  $_ROUTE->GET('/v1/store/product/category',                  $_PRODUCT . 'store/product/category');
                  $_ROUTE->GET('/v1/store/product/category/$id',              $_PRODUCT . 'store/product/category');
                  $_ROUTE->GET('/v1/store/product/$id',                       $_PRODUCT . 'store/product/product');
                  $_ROUTE->GET('/v1/store/product/detect',                    $_PRODUCT . 'store/product/detect');
                  $_ROUTE->GET('/v1/store/product',                           $_PRODUCT . 'store/product/product');


               // BOT
                  $_ROUTE->GET('/v1/bot/store-scraping/url',                  $_PRODUCT . 'bot/store-scraping/url');


               break;
            }

            case 'api.venet.cc' : 
            {
               $_PRODUCT   = $_CONFIG['ROOT'] . 'v1/products/admin/';

               $_ROUTE->GET('/v1/user/login',                        $_PRODUCT . 'user/login');

               $_ROUTE->GET('/v1/token',                             $_PRODUCT . 'token/token');
               $_ROUTE->GET('/v1/token/$id',                         $_PRODUCT . 'token/token');
               $_ROUTE->POST('/v1/token',                            $_PRODUCT . 'token/token');
               $_ROUTE->PUT('/v1/token/$id/$access',                 $_PRODUCT . 'token/token');

               break;
            }

            default:
            {
               break;
            }
         }
      }
      catch (Exception $e) { exceptions_error_handler(0, $e->getMessage(), $e->getFile(), $e->getLine()); }
      catch (Throwable $e) { exceptions_error_handler(0, $e->getMessage(), $e->getFile(), $e->getLine()); }

   // 404
      $_JSON_PRINT->fail("unknow endpoint or no method for this endpoint");
      $_JSON_PRINT->print();
?>