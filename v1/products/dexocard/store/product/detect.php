<?php
   // check Token
      $_TOKEN->checkAccess('dexocard', 'store/product-detect');

      switch (strtoupper($_METHOD))
      {
         case 'GET':
         {
            $_FILTERS_ACTIVE  = array();
            $_BLOC_WHERE      = '';
            $_ASSOCS_VARS     = array();

            // Check parameters
               if (empty($_GET['limit']))             { $_GET['limit'] = 10; }            else { $_GET['limit']         = intval($_GET['limit']); }
               if (empty($_GET['offset']))            { $_GET['offset'] = 0; }            else { $_GET['offset']        = intval($_GET['offset']); }
               
               $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":phash_int" => number_format(hexdec($_GET['phash']), 0, '', '')]);

               if (!empty($_GET['categoryid']))
               {
                  $_BLOC_WHERE      = $_BLOC_WHERE . " `store_product_categorieid` = :categoryid AND";
                  $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":categoryid" =>$_GET['categoryid']]);   
               }

               if (!empty($_GET['search_text']))
               {
                  $_BLOC_WHERE      = $_BLOC_WHERE . " (`store_product_namefr` LIKE :search_text_productnamefr OR `card_set_nameFR` LIKE :search_text_setnamefr OR `card_set_AbreviationFR` LIKE :search_text_setabvrfr) AND";
                  $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":search_text_productnamefr" => '%' . $_GET['search_text'] . '%']);   
                  $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":search_text_setnamefr" => '%' . $_GET['search_text'] . '%']);   
                  $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":search_text_setabvrfr" => '%' . $_GET['search_text'] . '%']);   
               }


            // Création requête SQL
               $_BLOC_SELECT =
               "
                  *,
                  `card_set_nameFR`,
                  `card_set_AbreviationFR`,
                  BIT_COUNT(:phash_int^ `store_product_imagefr_phash`) as `phash_distance`
               ";

               $results_print = array();
               $_SQL    = $_MYSQL->connect(array("dexocard"));
               foreach ($_SQL['dexocard']->query
               (
						getQuery($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, "LIMIT " . $_GET['offset'] . ", " . $_GET['limit']), 
						$_ASSOCS_VARS
               )->fetchAll(PDO::FETCH_ASSOC) as $itemSQL)
               {
                  array_push($results_print, array
                  (
                     'phash_distance'        => $itemSQL['phash_distance'],
                     'id'                    => $itemSQL['store_product_id'],
                     'category_id'           => $itemSQL['store_product_categorieid'],
                     'set_id'                => $itemSQL['store_product_setid'],
                     'first_release'         => $itemSQL['store_product_datefirstrealease'],
                     'name'                  => array
                     (
                        'fr'                    => $itemSQL['store_product_namefr'],
                        'en'                    => $itemSQL['store_product_nameen']
                     ),
                     'image'                 => array
                     (
                        'fr'                    => array
                        (
                           'filename'              => $itemSQL['store_product_imagefr'],
                           'phash'                 => $itemSQL['store_product_imagefr_phash'],
                        ),
                        'en'                    => array
                        (
                           'filename'              => $itemSQL['store_product_imageen'],
                           'phash'                 => $itemSQL['store_product_imageen_phash'],
                        ),
                     ),
                     'datetime_add'          => $itemSQL['store_product_datetime_add'],
                     'datetime_lastupdate'   => $itemSQL['store_product_lastupdate'],
                  ));
               }

            // Print Results
               $_JSON_PRINT->success(); 
               $_JSON_PRINT->response($results_print);
               $_JSON_PRINT->print();
         }
      }

      function getQuery($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, $_BLOC_LIMIT = NULL)
      {
         global $_TABLE_LIST;

         // Assemblage requête SQL
            return "
               SELECT 

               " . $_BLOC_SELECT . "

               FROM        " . $_TABLE_LIST['dexocard'] . ".`store_product`
               LEFT JOIN   " . $_TABLE_LIST['dexocard'] . ".`card_set` ON " . $_TABLE_LIST['dexocard'] . ".`card_set`.`card_set_id` = `store_product_setid`

               " . ($_BLOC_WHERE ? "WHERE " . substr($_BLOC_WHERE, 0, strlen($_BLOC_WHERE) - 4) : '') . "

               ORDER BY 
                  `phash_distance` ASC,
                  `store_product_id` DESC

               " . ($_BLOC_LIMIT ? $_BLOC_LIMIT : '') . "
               ;
            ";
      }

?>