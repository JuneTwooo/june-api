<?php
   // Errors handler
   set_error_handler('exceptions_error_handler');   
      
   function exceptions_error_handler(int $number, string $message, string $errfile, int $errline)
   {
      global $_CONFIG;
      
      if (!$_CONFIG['DEBUG'])
      {
         // Mode débug inactif
         echo json_encode
         (
            array
            (
               "success" => 0, 
               "raison" => "internal error"
            )
         );
      }
      else
      {
         // Mode débuf actif
         echo json_encode
         (
            array
            (
               "success" => 0, 
               "raison" => 
               array
               (
                  'message'   => $message,
                  'file'      => $errfile,
                  'line'      => $errline
               )
            )
         );
      }

      exit();
   }
?>