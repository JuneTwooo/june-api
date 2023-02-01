<?php
   usleep(100);

   // JSON Return
      $_JSON_RETURN = array
      (
         'success'   => 0,
         'raison'    => array()
      );

   // Debug
		ini_set('display_errors', E_ALL);
		error_reporting(1);

   // Headers
      header("Access-Control-Allow-Origin: *");
      header('Content-Type: application/json; charset=utf-8');

   // HTTP Origins
      switch (empty($_SERVER['HTTP_ORIGIN']) ? 'no_origin' : $_SERVER['HTTP_ORIGIN'])
      {
         case 'https://sfggrading.com':     { break; }
         case 'https://www.sfggrading.com': { break; }
         case 'no_origin':
         {
            break;
         }
      }

	// CONFIG & INIT
      require_once(__DIR__ . '/../config.php');
      require_once($_CONFIG['ROOT'] . 'init.php');
      require_once($_CONFIG['ROOT'] . 'v1/error-handler.php');

   // Routing
   //$router = new Router($_GET['url']); 
      //require_once($_CONFIG['ROOT'] . 'functions.global.php');

      /*
   // API
      $_API_ENDPOINT_FOLDER   = $_GET['module'];
      $_API_ENDPOINT          = $_GET['api'];

   // JSON Return
      $_JSON_RETURN = array
      (
         'success'   => 0,
         'raison'    => 'unknow'
      );

   if (!file_exists($_CONFIG['API']['ROOT_FOLDER'] . $_API_ENDPOINT_FOLDER . '/' . $_API_ENDPOINT . '.php'))
   {
      $_JSON_RETURN['success']   = 0;
      $_JSON_RETURN['raison']    = 'no_endpoint_found';
   }
   else
   {
      include $_CONFIG['API']['ROOT_FOLDER'] . $_API_ENDPOINT_FOLDER . '/' . $_API_ENDPOINT . '.php';
   }

   if ($_JSON_RETURN['success'])
   {
      unset($_JSON_RETURN['raison']);
   }

   echo json_encode($_JSON_RETURN);*/
?>