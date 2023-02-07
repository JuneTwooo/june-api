<?php
   // Errors handler
   set_error_handler('exceptions_error_handler');   
      
   function exceptions_error_handler(int $number, string $message, string $errfile, int $errline)
   {
      global $_CONFIG;
      global $_JSON_PRINT;

      if (!$_CONFIG['DEBUG'])
      {
         $_JSON_PRINT->fail("internal error");
      }
      else
      {
         // Mode débuf actif
         $_JSON_PRINT->fail("message: $message; file      => $errfile; line      => $errline");
      }

      $_JSON_PRINT->print();
   }
?>