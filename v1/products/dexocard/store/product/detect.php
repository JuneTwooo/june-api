<?php
   // check Token
      $_TOKEN->checkAccess('dexocard', 'store/product-detect');

      switch (strtoupper($_METHOD))
      {
         case 'GET':
         {
            // Check parameters
               if (empty($_GET['limit']))             { $_GET['limit'] = 10; }            else { $_GET['limit']         = intval($_GET['limit']); }
               if (empty($_GET['offset']))            { $_GET['offset'] = 0; }            else { $_GET['offset']        = intval($_GET['offset']); }


            // PHASH de l'image
               if (empty($_GET['phash']))
               {
                  $_JSON_PRINT->fail("phash of image is missing");
                  $_JSON_PRINT->print();
               }

            // Recherche du phash le plus proche
               $results_print = array();
               $_SQL    = $_MYSQL->connect(array("dexocard"));
               foreach ($_SQL['dexocard']->query(
               "
                  SELECT 
                     *,
                     BIT_COUNT(:phash_int^ `store_product_imagefr_phash`) as `phash_distance`
                  FROM 
                     " . $_TABLE_LIST['dexocard'] . ".`store_product`
                  " . (!empty($_GET['categoryid']) ? "WHERE `store_product_categorieid` = " . intval($_GET['categoryid']) : "") . "
                  ORDER BY 
                     `phash_distance` ASC
                  LIMIT :offset, :limit
                  ;
               ",
               [
                  ":phash_int"      => number_format(hexdec($_GET['phash']), 0, '', ''),
                  ":offset"         => $_GET['offset'],
                  ":limit"          => $_GET['limit'],
               ]
               )->fetchAll(PDO::FETCH_ASSOC) as $itemSQL)
               {
                  array_push($results_print, array
                  (
                     'phash_distance'     => $itemSQL['phash_distance'],
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
                  ));
               }

            // Print Results
               $_JSON_PRINT->success(); 
               $_JSON_PRINT->response($results_print);
               $_JSON_PRINT->print();
         }
      }
?>