<?php
   function isJson($string)
   {
      json_decode($string);

      return json_last_error() === JSON_ERROR_NONE;
   }

   function get_operand_array()
   {
      return array
      (
         "=", 
         ">", 
         "<", 
         ">=", 
         "<=", 
         "LIKE"
      );
   }
?>