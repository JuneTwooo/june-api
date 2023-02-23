<?php
   function isJson($string)
   {
      if (empty($string)) { return false; }

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