<?php
   // check Token
      $_TOKEN->checkAccess('dexocard', 'store/product');
      
   // use 
      use Jenssegers\ImageHash\ImageHash;
      use Jenssegers\ImageHash\Implementations\DifferenceHash;

   // Switch METHOD   
      switch (strtoupper($_METHOD))
      {
         case 'GET':
         {
            // Check parameters
               if (empty($_GET['limit']))       { $_GET['limit'] = 10; }      else { if (1 > $_GET['limit'])  { $_GET['limit']  = 10; } }
               if (empty($_GET['offset']))      { $_GET['offset'] = 0; }      else { if (0 > $_GET['offset']) { $_GET['offset'] = 0; } }
               if (empty($_GET['id']))          { $_GET['id'] = null; } 

            // MySQL Connect
               $_SQL = $_MYSQL->connect(array("dexocard"));

            // Query SQL
               $results_print = array();
               foreach ($_SQL['dexocard']->query
               (
                  "
                     SELECT 
                        *
                     FROM        `" . $_TABLE_LIST['dexocard'] . "`.`store_product`
                     WHERE
                        " . (empty($_GET['id']) ? "1" : "`" . $_TABLE_LIST['dexocard'] . "`.`store_product`.`store_product_id` = '" . addslashes($_GET['id']) . "'") . "
                     ORDER BY 
                        `store_product_datefirstrealease` DESC,
                        `store_product_categorieid` ASC
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
                     'id'                 => $itemSQL['store_product_id'],
                     'category_id'        => $itemSQL['store_product_categorieid'],
                     'set_id'             => $itemSQL['store_product_setid'],
                     'first_release'      => $itemSQL['store_product_datefirstrealease'],
                     'has_pins'           => $itemSQL['store_product_haspins'],
                     'has_figurine'       => $itemSQL['store_product_hasfigurine'],
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

            // break GET
               break;
         }

         case 'POST':
         {
            // Check parameters
               if (empty(intval($_GET['id'])))
               {
                  $_JSON_PRINT->fail("id must be specified");
                  $_JSON_PRINT->print();                  
               }

               if (empty(intval($_GET['categoryid'])))
               {
                  $_JSON_PRINT->fail("categoryid must be specified");
                  $_JSON_PRINT->print();                  
               }

            // MySQL Connect
               $_SQL          = $_MYSQL->connect(array("dexocard"));
         
            // Get New ID
               $_new_id  = $_SQL['dexocard']->insert("store_product", []);
               $_GET['id'] = $_SQL['dexocard']->id();
         
            // Search exist
               $_SQL_PRODUCT  = $_SQL['dexocard']->query("SELECT * FROM `" . $_TABLE_LIST['dexocard'] . "`.`store_product` WHERE store_product_id = :product_id", [":product_id" => $_GET['id']])->fetch(PDO::FETCH_ASSOC);

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
                     $dir_Target    = 'product/' . $_GET['categoryid'] . '/';
                     $file_Target   = str_pad($_GET['id'], 6, "0", STR_PAD_LEFT) . '-' . $lang . '-' . cleanTitleURL($_GET['namefr']);
            
                     $uploadResult = null;
                     $uploadResult = uploadFile_Image($_FILES['file-' . $lang], $_CONFIG['PRODUCTS']['DEXOCARD']['ROOT'], $dir_Target, $file_Target);

                     if (!$uploadResult['success'])
                     {
                        $_JSON_PRINT->fail("upload error : " . $uploadResult['raison']);
                        $_JSON_PRINT->print();     
                     }
                     else
                     {
                        $filenameUploaded[$lang] = $uploadResult['filename'];

                        $hasher = new ImageHash(new DifferenceHash());
                        $phash[$lang] = $hasher->hash($_CONFIG['PRODUCTS']['DEXOCARD']['ROOT'] . $uploadResult['filename'])->toHex();
                        $phash[$lang] = hexdec($phash['fr']) . '';
                     }
                  }
               }
                  
            // Enregistrement SQL
               $date = DateTime::createFromFormat('d/m/Y', $_GET['release']);
               $results = $_SQL['dexocard']->update("store_product", 
               [
                  "store_product_categorieid"         => ($_GET['categoryid']),
                  "store_product_setid"               => ($_GET['setid']),
                  "store_product_namefr"              => (!empty($_GET['namefr'])         ? $_GET['namefr']          : NULL),
                  "store_product_nameen"              => (!empty($_GET['nameen'])         ? $_GET['nameen']          : NULL),
                  "store_product_imagefr"             => (!empty($filenameUploaded['fr']) ? $filenameUploaded['fr']  : $_SQL_PRODUCT['store_product_imagefr']),
                  "store_product_imagefr_phash"       => (!empty($phash['fr'])            ? $phash['fr']             : $_SQL_PRODUCT['store_product_imagefr_phash']),
                  "store_product_imageen"             => (!empty($filenameUploaded['en']) ? $filenameUploaded['en']  : $_SQL_PRODUCT['store_product_imageen']),
                  "store_product_imageen_phash"       => (!empty($phash['en'])            ? $phash['en']             : $_SQL_PRODUCT['store_product_imageen_phash']),
                  "store_product_datefirstrealease"   => (!empty($_GET['release'])        ? $date->format('Y-m-d')   : NULL),
                  "store_product_haspins"             => (!empty($_GET['has_pins'])       ? $_GET['has_pins']        : NULL),
                  "store_product_hasfigurine"         => (!empty($_GET['has_figurine'])   ? $_GET['has_figurine']    : NULL),
               ],
               [
                  "store_product_id" => $_GET['id']
               ]);

            // Print Results
               $_JSON_PRINT->success(); 
               $_JSON_PRINT->response();
               $_JSON_PRINT->print();

            break;
         }

         case 'PUT':
         {
            // Check parameters
               if (empty(intval($_GET['id']))) { $_JSON_PRINT->fail("id must be specified"); $_JSON_PRINT->print(); }
               if (empty(intval($_GET['categoryid']))) { $_JSON_PRINT->fail("categoryid must be specified"); $_JSON_PRINT->print(); }

            // MySQL Connect
               $_SQL          = $_MYSQL->connect(array("dexocard"));

            // Get Product info from SQL
               $_SQL_PRODUCT  = $_SQL['dexocard']->query("SELECT * FROM `" . $_TABLE_LIST['dexocard'] . "`.`store_product` WHERE store_product_id = :product_id", [":product_id" => $_GET['id']])->fetch(PDO::FETCH_ASSOC);

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
                     $dir_Target    = 'product/' . $_GET['categoryid'] . '/';
                     $file_Target   = str_pad($_GET['id'], 6, "0", STR_PAD_LEFT) . '-' . $lang . '-' . cleanTitleURL($_GET['namefr'], 30);
            
                     $uploadResult = null;
                     $uploadResult = uploadFile_Image($_FILES['file-' . $lang], $_CONFIG['PRODUCTS']['DEXOCARD']['ROOT'], $dir_Target, $file_Target);

                     if (!$uploadResult['success'])
                     {
                        $_JSON_PRINT->fail("upload error : " . $uploadResult['raison']);
                        $_JSON_PRINT->print();     
                     }
                     else
                     {
                        $filenameUploaded[$lang] = $uploadResult['filename'];

                        $hasher = new ImageHash(new DifferenceHash());
                        $phash[$lang] = $hasher->hash($_CONFIG['PRODUCTS']['DEXOCARD']['ROOT'] . $uploadResult['filename'])->toHex();
                        $phash[$lang] = hexdec($phash['fr']) . '';
                     }
                  }
               }
                  
            // Enregistrement SQL
               $date = DateTime::createFromFormat('d/m/Y', $_GET['release']);
               $results = $_SQL['dexocard']->update("store_product", 
               [
                  "store_product_categorieid"         => ($_GET['categoryid']),
                  "store_product_setid"               => ($_GET['setid']),
                  "store_product_namefr"              => (!empty($_GET['namefr'])         ? $_GET['namefr']          : NULL),
                  "store_product_nameen"              => (!empty($_GET['nameen'])         ? $_GET['nameen']          : NULL),
                  "store_product_imagefr"             => (!empty($filenameUploaded['fr']) ? $filenameUploaded['fr']  : $_SQL_PRODUCT['store_product_imagefr']),
                  "store_product_imagefr_phash"       => (!empty($phash['fr'])            ? $phash['fr']             : $_SQL_PRODUCT['store_product_imagefr_phash']),
                  "store_product_imageen"             => (!empty($filenameUploaded['en']) ? $filenameUploaded['en']  : $_SQL_PRODUCT['store_product_imageen']),
                  "store_product_imageen_phash"       => (!empty($phash['en'])            ? $phash['en']             : $_SQL_PRODUCT['store_product_imageen_phash']),
                  "store_product_datefirstrealease"   => (!empty($_GET['release'])        ? $date->format('Y-m-d')   : NULL),
                  "store_product_haspins"             => (!empty($_GET['has_pins'])       ? $_GET['has_pins']        : NULL),
                  "store_product_hasfigurine"         => (!empty($_GET['has_figurine'])   ? $_GET['has_figurine']    : NULL),
               ],
               [
                  "store_product_id" => $_GET['id']
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
?>