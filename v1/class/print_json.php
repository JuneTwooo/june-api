<?php
   class print_JSON
   {
      private $_state;
      private $_fail_raison;
      private $_response;
      private $_additionalResponsesBefore;
      private $_additionalResponsesAfter;
      public $_printed;

      function __construct()
      {
         $this->_state                       = 0;
         $this->_response                    = 0;
         $this->_fail_raison                 = null;
         $this->_additionalResponsesBefore   = array();
         $this->_additionalResponsesAfter    = array();
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

      public function addDataBefore($col, $val)
      {
         array_push($this->_additionalResponsesBefore, array($col => $val));
      }

      public function addDataAfter($col, $val)
      {
         array_push($this->_additionalResponsesAfter, array($col => $val));
      }

      public function response($obj)
      {
         global $_LOG;

         $_LOG->write(1, 0, 'print_JSON->reponse', serialize($obj));

         $this->_response = $obj;
      }

      public function print()
      {
         global $_CONFIG;
         global $_DATA_DEBUG;
         global $_LOG;
         global $_BENCH_START_TIME;

         // Construction de la réponse
            $results = array
            (
               'success'         => $this->_state,
               'raison'          => $this->_fail_raison,
            );

         // Ajout de nouveaux éléments personnalisés à la réponse (AFTER)
            foreach ($this->_additionalResponsesBefore as $itemResponse)
            {
               $col  = key($itemResponse);
               $val  = $itemResponse[key($itemResponse)];

               $results = array_merge($results, array($col => $val));
            }

         // Embrique la réponse + clear (pour libérer la ram, évidemment)
            $results = array_merge($results, array('results' => $this->_response));
            $this->_response  = NULL;

         // Ajout de nouveaux éléments personnalisés à la réponse (AFTER)
            foreach ($this->_additionalResponsesAfter as $itemResponse)
            {
               $col  = key($itemResponse);
               $val  = $itemResponse[key($itemResponse)];

               $results = array_merge($results, array($col => $val));
            }

         // Ajout d'éléments de débug à la réponse
            $_BENCH_END_TIME = microtime(true);
            $results = array_merge($results, array('timestamp' => time()));
            if ($_CONFIG['DEBUG']) { $results = array_merge($results, array('debug' => $_DATA_DEBUG)); }
            if ($_CONFIG['DEBUG']) { $results = array_merge($results, array('bench' =>  ($_BENCH_END_TIME - $_BENCH_START_TIME))); }

         // Log Debug
            $_LOG->write(1, 0, 'print_JSON->print', serialize($results));

         // Print + exit
            echo json_encode($results);
            exit();
      }
   }
?>