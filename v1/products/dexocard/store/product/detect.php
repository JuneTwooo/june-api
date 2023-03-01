<?php
   // check Token
      $_TOKEN->checkAccess('dexocard', 'store/product-detect');

      switch (strtoupper($_METHOD))
      {
         case 'GET':
         {
            // Check parameters
               if (empty($_GET['maxdistance']))       { $_GET['maxdistance'] = 20; }      else { $_GET['maxdistance'] = intval($_GET['maxdistance']); }

            // id l'item (MD5 de l'URL)
               if (empty($_GET['id']))
               {
                  $_JSON_PRINT->fail("id of item is missing");
                  $_JSON_PRINT->print();
               }

            // Nom de l'item
               if (empty($_GET['name']))
               {
                  $_JSON_PRINT->fail("name of item is missing");
                  $_JSON_PRINT->print();
               }

            // PHASH de l'image
               if (empty($_GET['phash']))
               {
                  $_JSON_PRINT->fail("phash of image is missing");
                  $_JSON_PRINT->print();
               }

            // Recherche du phash le plus proche
               $_SQL    = $_MYSQL->connect(array("dexocard"));
               $itemSQL = $_SQL['dexocard']->query(
               "
                  SELECT 
                     *, 
                     BIT_COUNT(:phash_int^ `store_product_imagefr_phash`) as `phash_distance`
                  FROM 
                     " . $_TABLE_LIST['dexocard'] . ".`store_product`
                  " . (!empty($_GET['categoryid']) ? "WHERE `store_product_categorieid` = " . intval($_GET['categoryid']) : "") . "
                  HAVING
                     `phash_distance` <= :max_distance
                  ;
               ",
               [
                  ":phash_int"      => hexdec($_GET['phash']),
                  ":max_distance"   => $_GET['maxdistance'],
               ])->fetch(PDO::FETCH_ASSOC);

               $results_print = null;
               if ($itemSQL)
               {
                  $results_print = array
                  (
                     'id'                 => $itemSQL['store_product_id'],
                     'category_id'        => $itemSQL['store_product_categorieid'],
                     'set_id'             => $itemSQL['store_product_setid'],
                     'first_release'      => $itemSQL['store_product_datefirstrealease'],
                     'name'               => array
                     (
                        'fr'                 => $itemSQL['store_product_namefr'],
                        'en'                 => $itemSQL['store_product_nameen']
                     ),
                     'image'              => array
                     (
                        'fr'                 => array
                        (
                           'filename'           => $itemSQL['store_product_imagefr'],
                           'phash'              => $itemSQL['store_product_imagefr_phash'],
                        ),
                        'en'                 => array
                        (
                           'filename'           => $itemSQL['store_product_imageen'],
                           'phash'              => $itemSQL['store_product_imageen_phash'],
                        ),
                     ),
                     'datetime_add'       => $itemSQL['store_product_datetime_add'],
                     'phash_distance'     => $itemSQL['phash_distance'],
                  );
               }

            // Print Results
               $_JSON_PRINT->success(); 
               $_JSON_PRINT->response($results_print);
               $_JSON_PRINT->print();
         }
      }
?>