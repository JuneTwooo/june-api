<?php
   // check Token
      $_TOKEN->checkAccess('dexocard', 'tcg/card');

      switch (strtoupper($_METHOD))
      {
         case 'GET':
         {
            // Formatage des données envoyées
               $results_print = array();
               
               // MySQL Connect
               $_SQL    = $_MYSQL->connect(array("dexocard"));

               // Query
               foreach ($_SQL['dexocard']->query("SHOW COLUMNS FROM card_propriety;")->fetchAll(PDO::FETCH_ASSOC) as $thisProperty)
               {
                  if ($thisProperty['Field'] == 'card_propriety_cardid') { continue; }

                  array_push($results_print, array
                  (
                     str_replace('card_propriety_', '', $thisProperty['Field'])
                  ));
               }

  
            // Envoi des données
               $_JSON_PRINT->addDataBefore('results_count',          count($results_print)); 
               $_JSON_PRINT->addDataBefore('results_filters_count',  count($results_print));
               
               $_JSON_PRINT->success(); 
               $_JSON_PRINT->response($results_print); 
               $_JSON_PRINT->print();

            break;
         }

         case 'POST':
         case 'PUT':
         {

            break;
         }
   
      }
?>