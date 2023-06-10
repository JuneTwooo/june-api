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
               foreach ($_SQL['dexocard']->query("SELECT * FROM card_type")->fetchAll(PDO::FETCH_ASSOC) as $thisSQL)
               {
                  array_push($results_print, array
                  (
                     'id'        => $thisSQL['card_type_id'],
                     'name'      => array
                     (
                        'fr' => $thisSQL['card_type_nameFR'],
                        'en' => $thisSQL['card_type_nameEN']
                     )
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