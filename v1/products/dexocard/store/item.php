<?php
   // check Token
      $_TOKEN->checkAccess('admin', 'dexocard/item');
      
   // use 
      use Jenssegers\ImageHash\ImageHash;
      use Jenssegers\ImageHash\Implementations\DifferenceHash;

   // Switch METHOD   
      switch (strtoupper($_METHOD))
      {
        case 'GET':
        {
        }

        case 'POST':
        {
            // MySQL Connect
                $_SQL          = $_MYSQL->connect(array("dexocard"));

            // Get New ID
            
            // Check parameters
                if (empty(intval($id)))
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
                $_SQL_PRODUCT  = $_SQL['dexocard']->query("SELECT * FROM `" . $_TABLE_LIST['dexocard'] . "`.`store_product` WHERE store_product_id = :product_id", [":product_id" => $id])->fetch(PDO::FETCH_ASSOC);

            // Recherche si le produit existe
                if (empty($_SQL_PRODUCT['store_product_id']))
                {
                    $_JSON_PRINT->fail("product id $id not found");
                    $_JSON_PRINT->print();                                   
                }

            // Upload files
                $filenameUploaded = array();
                foreach (array('fr', 'en') as $lang)
                {
                    $filenameUploaded[$lang] = false;

                    if (!empty($_FILES['file-' . $lang]) || !empty($_FILES['file-' . $lang]))
                    {
                        $dir_Target    = 'product/' . $_GET['categoryid'] . '/';
                        $file_Target   = str_pad($id, 6, "0", STR_PAD_LEFT) . '-' . cleanTitleURL($_FILES['file-' . $lang]['name'], 30) . '-' . 'nom';
            
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
                    "store_product_imageen"             => (!empty($filenameUploaded['en']) ? $filenameUploaded['en']  : $_SQL_PRODUCT['store_product_imageen']),
                    "store_product_datefirstrealease"   => (!empty($_GET['release'])        ? $date->format('Y-m-d')   : NULL),
                ],
                [
                    "store_product_id" => $id
                ]);

            // Print Results
                $_JSON_PRINT->success(); 
                $_JSON_PRINT->response();
                $_JSON_PRINT->print();

            break;
        }

        case 'PUT':
        {

        }

        case 'DELETE':
        {
        }
      }
?>