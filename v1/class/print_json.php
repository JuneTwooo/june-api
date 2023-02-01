<?php
   class print_JSON
   {
      private $_success;
      private $_fail_raison;

      function __construct()
      {
         $this->_success      = 0;
         $this->_fail_raison  = null;
      }

      public function success(bool $result, string $raison = NULL)
      {
         if (!is_bool($result)) { throw new Exception('$result is not a boolean value'); }

         $this->_success = ($result ? 1 : 0);
         if ($result && !empty($raison)) { $this->_fail_raison = $result; }
      }

      public function print()
      {
         echo json_encode(
            array(
               'success'   => $this->_success,
               'raison'    => $this->_fail_raison,
               'timestamp' => time(),
            )
         );
         exit();
      }
   }
?>