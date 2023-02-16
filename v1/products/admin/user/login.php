<?php
   // check Token
      $_TOKEN->checkAccess('admin', 'user/login');

      switch (strtoupper($_METHOD))
      {
         case 'GET':
         {

               $results_unfiltered = $_SQL['api']->query
               (
                  getQuery_Sets($_FILTERS_ACTIVE, "COUNT(*) AS total_rows_unfiltered", $_BLOC_WHERE, NULL), 
                  $_ASSOCS_VARS
               )->fetch(PDO::FETCH_ASSOC)['total_rows_unfiltered'];

               $_JSON_PRINT->addDataBefore('results_count',          count($results_print)); 
               $_JSON_PRINT->addDataBefore('results_filters_count',  $results_unfiltered); 
               
               // debug
               //$_SQL['api']->debug()->query(getQuery_Sets($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, "LIMIT " . $_OFFSET . ", " . $_LIMIT),$_ASSOCS_VARS);

               $_JSON_PRINT->success(); 
               $_JSON_PRINT->response($results_print); 
               $_JSON_PRINT->print();

            break;
         }
      }
?>