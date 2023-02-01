<?php
   class print_JSON
   {
      private $_state;
      private $_fail_raison;
      private $_response;

      function __construct()
      {
         $this->_state      = 0;
         $this->_fail_raison  = null;
      }

      public function success()
      {
         $this->_state = 1;
      }

      public function fail($raison = NULL)
      {
         $this->_state = 0;

         if (!empty($raison)) { $this->_fail_raison = $raison; }
      }

      public function response($obj)
      {
         $this->_response = $obj;
      }

      public function print()
      {
         echo json_encode(
            array(
               'success'   => $this->_state,
               'raison'    => $this->_fail_raison,
               'response'  => $this->_response,
               'timestamp' => time(),
            )
         );
         exit();
      }
   }
?>