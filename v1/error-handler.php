<?php
   // Errors handler
   set_error_handler(function(int $number, string $message, string $errfile, int $errline)
   {
      global $_JSON_RETURN;
      global $_CONFIG;
      
      if (empty($_CONFIG['debug']))
      {
         array_push($_JSON_RETURN['raison'], "internal error");
      }
      else
      {
         array_push($_JSON_RETURN['raison'], array
         (
            'message'   => $message,
            'file'      => $errfile,
            'line'      => $errline
         ));
      }
      
      echo_json(true);
   });

   function echo_json($exit = false)
   {
      global $_JSON_RETURN;

      echo json_encode($_JSON_RETURN);

      if ($exit) { exit(); }
   }
?>