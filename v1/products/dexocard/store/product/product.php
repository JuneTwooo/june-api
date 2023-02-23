<?php
   // check Token
      $_TOKEN->checkAccess('admin', 'store/product');

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
                     FROM 
                        `" . $_TABLE_LIST['dexocard'] . "`.`store_product`
                     WHERE
                        " . (empty($id) ? "1" : "`" . $_TABLE_LIST['dexocard'] . "`.`store_product`.`store_product_id` = '" . addslashes($id) . "'") . "
                     LIMIT :offset, :limit;
                  ", 
                  [
                     ":offset"   => $_GET['offset'],
                     ":limit"    => $_GET['limit'],
                  ]
               )->fetchAll(PDO::FETCH_ASSOC) as $itemSQL)
               {
                  // format response
                  array_push($results_print, array
                  (
                     'id'        => $itemSQL['store_product_id'],
                     'name'      => array
                     (
                        'fr'        => $itemSQL['store_product_namefr']
                     ),
                     'image'      => array
                     (
                        'fr'        => $itemSQL['store_product_imagefr']
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