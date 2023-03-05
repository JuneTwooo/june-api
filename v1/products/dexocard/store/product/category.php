<?php
   // check Token
      $_TOKEN->checkAccess('dexocard', 'store/product-category');

      switch (strtoupper($_METHOD))
      {
         case 'GET':
         {
            // Check parameters
               if (empty($_GET['limit']))       { $_GET['limit'] = 10; }      else { if (1 > $_GET['limit'])  { $_GET['limit']  = 10; } }
               if (empty($_GET['offset']))      { $_GET['offset'] = 0; }      else { if (0 > $_GET['offset']) { $_GET['offset'] = 0; } }
               if (empty($id))                  { $id = null; } 

            // MySQL Connect
               $_SQL = $_MYSQL->connect(array("dexocard"));

            // Query SQL
               $results_print = array();
               foreach ($_SQL['dexocard']->query
               (
                  "
                     SELECT 
                        *
                     FROM        `" . $_TABLE_LIST['dexocard'] . "`.`store_categorie`
                     WHERE
                        " . (empty($id) ? "1" : "`" . $_TABLE_LIST['dexocard'] . "`.`store_product`.`store_product_id` = '" . addslashes($id) . "'") . "
                     ORDER BY store_categorie_namefr ASC
                     LIMIT :offset, :limit;
                  ", 
                  [
                     ":offset"   => intval($_GET['offset']),
                     ":limit"    => intval($_GET['limit']),
                  ]
               )->fetchAll(PDO::FETCH_ASSOC) as $itemSQL)
               {
                  // format response
                  array_push($results_print, array
                  (
                     'id'           => $itemSQL['store_categorie_id'],
                     'name'         => array
                     (
                        'fr'           => $itemSQL['store_categorie_namefr']
                     ),
                  ));
               }


               $_JSON_PRINT->success(); 
               $_JSON_PRINT->response($results_print);
               $_JSON_PRINT->print();

            // break GET
               break;
         }
      }
?>