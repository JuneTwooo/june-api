<?php
   // check Token
      $_TOKEN->checkAccess('dexocard', 'store/product');
      
   // use 
      use Jenssegers\ImageHash\ImageHash;
      use Jenssegers\ImageHash\Implementations\DifferenceHash;
      use Medoo\Medoo;

   // Switch METHOD   
      switch (strtoupper($_METHOD))
      {
         case 'GET':
         {
            $_FILTERS_ACTIVE  = array();
				$_BLOC_WHERE      = '';
				$_ASSOCS_VARS     = array();

            // Check parameters
               if (empty($_GET['offset'])) 		{ $_OFFSET = 0; }   else { $_OFFSET = intval($_GET['offset']); }
               if (empty($_GET['limit']))  		{ $_LIMIT  = 10; }  else { $_LIMIT  = intval($_GET['limit']); }
               
               if (empty($_GET['id']))          { $_GET['id'] = null; } 
               else
               {
                  $_BLOC_WHERE      = $_BLOC_WHERE . " `store_product_id` = " . intval($_GET['id']) . " AND";
               }

               if (!empty($_GET['search_text']))
               {
                  $_BLOC_WHERE      = $_BLOC_WHERE . " 
                  (
                        `store_categorie_namefr`   LIKE :search_text_categorie_namefr OR 
                        `card_set_nameFR`          LIKE :search_text_set_namefr OR 
                        `store_product_namefr`     LIKE :search_text_product_namefr
                  ) AND";
                  $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":search_text_categorie_namefr"  => '%' . $_GET['search_text'] . '%']);   
                  $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":search_text_set_namefr"        => '%' . $_GET['search_text'] . '%']);   
                  $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":search_text_product_namefr"    => '%' . $_GET['search_text'] . '%']);   
               }

            // MySQL Connect
               $_SQL = $_MYSQL->connect(array("dexocard"));

            // Création requête SQL
					$_BLOC_SELECT =
					"
						*,
                  (SELECT COUNT(*) FROM store_item WHERE store_item_productid = store_product_id) AS `item_count`
					";

            // Query SQL
               $results_print = array();
               foreach ($_SQL['dexocard']->query
               (
						getQuery_Sets($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, "LIMIT " . $_OFFSET . ", " . $_LIMIT), 
						$_ASSOCS_VARS
               )->fetchAll(PDO::FETCH_ASSOC) as $itemSQL)
               {
                  // format response
                  array_push($results_print, array
                  (
                     'id'                    => $itemSQL['store_product_id'],
                     'category'                   => array
                     (
                        'id'                    => $itemSQL['store_product_categorieid'],
                        'name'                     => array
                        (
                           'fr'                       => $itemSQL['store_categorie_namefr'],
                           'en'                       => $itemSQL['store_categorie_nameen'],
                        ),
                     ),
                     'set'                   => array
                     (
                        'id'                    => $itemSQL['store_product_setid'],
                        'name'                     => array
                        (
                           'fr'                       => $itemSQL['card_set_nameFR'],
                           'en'                       => $itemSQL['card_set_nameEN'],
                        ),
                        'abvr'                     => array
                        (
                           'fr'                       => $itemSQL['card_set_AbreviationFR'],
                           'en'                       => $itemSQL['card_set_AbreviationEN'],
                        ),
                     ),
                     'has_pins'              => $itemSQL['store_product_haspins'],
                     'has_token'             => $itemSQL['store_product_hastoken'],
                     'has_figurine'          => $itemSQL['store_product_hasfigurine'],
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
                     'item_count'            => $itemSQL['item_count'],
                     'date_firstrelease'     => $itemSQL['store_product_date_firstrealease'],
                     'datetime_add'          => $itemSQL['store_product_datetime_add'],
                     'datetime_lastupdate'   => $itemSQL['store_product_datetime_lastupdate'],
                  ));
               }

            // Envoi des données
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


            // break GET
               break;
         }

         case 'POST':
         case 'PUT':
         {
            // Check parameters
               if (empty(intval($_PARAM['id'])))
               {
                  $_JSON_PRINT->fail("id must be specified");
                  $_JSON_PRINT->print();                  
               }

               if (empty(intval($_PARAM['categoryid'])))
               {
                  $_JSON_PRINT->fail("categoryid must be specified");
                  $_JSON_PRINT->print();                  
               }

            // MySQL Connect
               $_SQL          = $_MYSQL->connect(array("dexocard"));
         
            // Get New ID
               if ($_PARAM['id'] == -1)
               {
                  $_SQL['dexocard']->insert("store_product", []);
                  $_PARAM['id'] = $_SQL['dexocard']->id();
               }
         
            // Search exist
               $_SQL_PRODUCT  = $_SQL['dexocard']->query("SELECT * FROM `" . $_TABLE_LIST['dexocard'] . "`.`store_product` WHERE store_product_id = :product_id", [":product_id" => $_PARAM['id']])->fetch(PDO::FETCH_ASSOC);

            // Recherche si le produit existe
               if (empty($_SQL_PRODUCT['store_product_id']))
               {
                  $_JSON_PRINT->fail("product id not found");
                  $_JSON_PRINT->print();                                   
               }

            // Upload files
               $filenameUploaded = array();
               $phash            = array();

               foreach (array('fr', 'en') as $lang)
               {
                  $filenameUploaded[$lang] = false;

                  if (!empty($_FILES['file-' . $lang]) || !empty($_FILES['file-' . $lang]))
                  {
                     $dir_Target    = 'product/' . $_PARAM['categoryid'] . '/';
                     $file_Target   = str_pad($_PARAM['id'], 6, "0", STR_PAD_LEFT) . '-' . $lang . '-' . cleanTitleURL($_PARAM['namefr']);
            
                     $uploadResult = null;
                     $uploadResult = uploadFile_Image($_FILES['file-' . $lang], $_CONFIG['PRODUCTS']['DEXOCARD']['RES_ROOT'], $dir_Target, $file_Target, true);

                     if (!$uploadResult['success'])
                     {
                        $_JSON_PRINT->fail("upload error : " . $uploadResult['raison']);
                        $_JSON_PRINT->print();     
                     }
                     else
                     {
                        $filenameUploaded[$lang] = $uploadResult['filename'];

                        $hasher = new ImageHash(new DifferenceHash());
                        $phash[$lang] = $hasher->hash($_CONFIG['PRODUCTS']['DEXOCARD']['RES_ROOT'] . $uploadResult['filename'])->toHex();
                        $phash[$lang] = hexdec($phash['fr']) . '';
                     }
                  }
               }
                  
            // Enregistrement SQL
               $date = DateTime::createFromFormat('d/m/Y', $_PARAM['release']);
               $results = $_SQL['dexocard']->update("store_product", 
               [
                  "store_product_categorieid"         => ($_PARAM['categoryid']),
                  "store_product_setid"               => ($_PARAM['setid']),
                  "store_product_namefr"              => (!empty($_PARAM['namefr'])          ? $_PARAM['namefr']           : NULL),
                  "store_product_nameen"              => (!empty($_PARAM['nameen'])          ? $_PARAM['nameen']           : NULL),
                  "store_product_imagefr"             => (!empty($filenameUploaded['fr'])    ? $filenameUploaded['fr']     : $_SQL_PRODUCT['store_product_imagefr']),
                  "store_product_imagefr_phash"       => (!empty($phash['fr'])               ? $phash['fr']                : $_SQL_PRODUCT['store_product_imagefr_phash']),
                  "store_product_imageen"             => (!empty($filenameUploaded['en'])    ? $filenameUploaded['en']     : $_SQL_PRODUCT['store_product_imageen']),
                  "store_product_imageen_phash"       => (!empty($phash['en'])               ? $phash['en']                : $_SQL_PRODUCT['store_product_imageen_phash']),
                  "store_product_date_firstrealease"  => (!empty($_PARAM['release'])         ? $date->format('Y-m-d')      : NULL),
                  "store_product_haspins"             => (!empty($_PARAM['has_pins'])        ? $_PARAM['has_pins']         : NULL),
                  "store_product_hastoken"            => (!empty($_PARAM['has_token'])       ? $_PARAM['has_token']        : NULL),
                  "store_product_hasfigurine"         => (!empty($_PARAM['has_figurine'])    ? $_PARAM['has_figurine']     : NULL),
                  "store_product_datetime_lastupdate" => Medoo::raw('NOW()'),
               ],
               [
                  "store_product_id" => $_PARAM['id']
               ]);

            // Print Results
               $_JSON_PRINT->success(); 
               $_JSON_PRINT->response();
               $_JSON_PRINT->print();

            break;
         }

         case 'DELETE':
         {
            // Check parameters
               if (empty(intval($_GET['id'])))
               {
                  $_JSON_PRINT->fail("id must be specified");
                  $_JSON_PRINT->print();                  
               }

            // MySQL Connect
               $_SQL          = $_MYSQL->connect(array("dexocard"));

            // Query SQL
               $result = $_SQL['dexocard']->delete
               (
                  "store_product",
                  [
                     "store_product_id" => $_GET['id'],
                  ]
               );

            // Print Result
               if (!empty($result))
               {
                  $_JSON_PRINT->success(); 
                  $_JSON_PRINT->response();
                  $_JSON_PRINT->print();
               }
               else
               {
                  $_JSON_PRINT->fail("unknow"); 
               }

         }
      }

      function getQuery_Sets($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, $_BLOC_LIMIT = NULL)
      {
         global $_TABLE_LIST;
   
         // Assemblage requête SQL
            return "
               SELECT 
   
               " . $_BLOC_SELECT . "
   
               FROM        `" . $_TABLE_LIST['dexocard'] . "`.`store_product`
               LEFT JOIN   `" . $_TABLE_LIST['dexocard'] . "`.`card_set`         ON `card_set_id`        = `store_product_setid`
               LEFT JOIN   `" . $_TABLE_LIST['dexocard'] . "`.`store_categorie`  ON `store_categorie_id` = `store_product_categorieid`
      
               " . ($_BLOC_WHERE ? "WHERE " . substr($_BLOC_WHERE, 0, strlen($_BLOC_WHERE) - 4) : '') . "

               ORDER BY 
                  `store_product_date_firstrealease` DESC,
                  `store_product_categorieid` ASC
   
               " . ($_BLOC_LIMIT ? $_BLOC_LIMIT : '') . "
               ;
            ";
      }
?>