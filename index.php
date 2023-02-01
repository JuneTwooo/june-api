<?php
   // debug
      ini_set('display_errors', 0);
      error_reporting(0);

   // Headers
      header("Access-Control-Allow-Origin: *");
      header('Content-Type: application/json; charset=utf-8');

   $_JSON_RETURN = array('success' => 0);

   if (@$_GET['error'])
   {
      $_JSON_RETURN['raison'] = $_GET['error'];
   }

   echo json_encode($_JSON_RETURN);
   exit();
?>