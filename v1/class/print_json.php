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
         global $_LOG;
         $this->_state = 1;

         $_LOG->write(2, 1, 'print_JSON->success', "");
      }

      public function fail($raison = NULL)
      {
         global $_LOG;

         $this->_state = 0;

         if (!empty($raison)) { $this->_fail_raison = $raison; }

         $_LOG->write(2, 0, 'print_JSON->fail', $raison);
      }

      public function response($obj)
      {
         global $_LOG;

         $_LOG->write(1, 0, 'print_JSON->reponse', serialize($obj));

         $this->_response = $obj;
      }

      public function print()
      {
         global $_LOG;
         global $_BENCH_START_TIME;

         $_BENCH_END_TIME = microtime(TRUE);

         $callBack = json_encode(
            array(
               'success'   => $this->_state,
               'raison'    => $this->_fail_raison,
               'response'  => $this->_response,
               'timestamp' => time(),
               'bench'     => ($_BENCH_END_TIME - $_BENCH_START_TIME)
            )
         );

         $_LOG->write(1, 0, 'print_JSON->print', serialize($callBack));

         echo $callBack;
         exit();
      }
   }
?>