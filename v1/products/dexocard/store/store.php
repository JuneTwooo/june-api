<?php
   // check Token
      $_TOKEN->checkAccess('dexocard', 'store/store');

      switch (strtoupper($_METHOD))
      {
         case 'GET':
         {
            /*
                ?operand=and&filters=[{"filter":{"data":"id","operand":"=","value":"swsh12"}},{"filter":{"data":"id","operand":"=","value":"swsh11"}}]
                ?operand=or&filters=[{"filter":{"data":"id","operand":"=","value":"swsh12}}]
            */

            $_FILTERS_ACTIVE  = array();
            $_BLOC_WHERE      = '';
            $_ASSOCS_VARS     = array();

            // Defaults vars
               if (empty($_GET['offset'])) { $_OFFSET = 0; }   else { $_OFFSET = intval($_GET['offset']); }
               if (empty($_GET['limit']))  { $_LIMIT  = 10; }  else { $_LIMIT  = intval($_GET['limit']); }
               if (!empty($_GET['id']))
               {
                  $_BLOC_WHERE      = $_BLOC_WHERE . " `store_id` = " . intval($_GET['id']) . " AND";
               }
            
            // Création requête SQL
               $_BLOC_SELECT =
               "
                  `" . $_TABLE_LIST['dexocard'] . "`.`store`.`store_id`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`store`.`store_name`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`store`.`store_url`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`store`.`store_icon`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`store`.`store_code`
               ";

            // Formatage des données envoyées
                $results_print = array();
                
                // MySQL Connect
                $_SQL    = $_MYSQL->connect(array("api"));

                // Query
                foreach ($_SQL['api']->query
                (
                    getQuery_Sets($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, "LIMIT " . $_OFFSET . ", " . $_LIMIT), 
                    $_ASSOCS_VARS
                )->fetchAll(PDO::FETCH_ASSOC) as $thisCard)
                {
                    array_push($results_print, array
                    (
                        'id'                => $thisCard['store_id'],
                        'name'              => $thisCard['store_name'],
                        'url'               => $thisCard['store_url'],
                        'icon'              => $thisCard['store_icon'],
                        'code'              => $thisCard['store_code'],
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

            break;
         }

         case 'PUT':
         case 'POST':
         {
            // Check parameters
               if (empty(intval($_PARAM['id']))) { $_JSON_PRINT->fail("id must be specified"); $_JSON_PRINT->print(); }

            // MySQL Connect
               $_SQL          = $_MYSQL->connect(array("dexocard"));

            // Get New ID if id=-1
               if ($_PARAM['id'] == -1)
               {
                  $_SQL['dexocard']->insert("store", []);
                  $_PARAM['id'] = $_SQL['dexocard']->id();
               }

            // Get Store info from SQL
               $_SQL_STORE  = $_SQL['dexocard']->query("SELECT * FROM `" . $_TABLE_LIST['dexocard'] . "`.`store` WHERE store_id = :store_id", [":store_id" => $_PARAM['id']])->fetch(PDO::FETCH_ASSOC);
            
             // Recherche si le store existe
               if (empty($_SQL_STORE['store_id']))
               {
                  $_JSON_PRINT->fail("store id not found");
                  $_JSON_PRINT->print();                                   
               }

            // Upload files
               $filenameUploaded = null;

               if (!empty($_FILES['file']))
               {
                  $dir_Target    = 'store/' . $_PARAM['id'] . '/';
                  $file_Target   = str_pad($_PARAM['id'], 6, "0", STR_PAD_LEFT) . '-' . cleanTitleURL($_PARAM['name'], 30);
         
                  $uploadResult = null;
                  $uploadResult = uploadFile_Image($_FILES['file'], $_CONFIG['PRODUCTS']['DEXOCARD']['ROOT'], $dir_Target, $file_Target);

                  if (!$uploadResult['success'])
                  {
                     $_JSON_PRINT->fail("upload error : " . $uploadResult['raison']);
                     $_JSON_PRINT->print();     
                  }
                  else
                  {
                     $filenameUploaded = $uploadResult['filename'];
                  }
               }

            // Columns to update
               $update_cols = array();
               if (!empty($_PARAM['name']))           { $update_cols = array_merge($update_cols, ["store_name"          => $_PARAM['name']]); }
               if (!empty($_PARAM['url']))            { $update_cols = array_merge($update_cols, ["store_url"           => $_PARAM['url']]); }
               if (!empty($filenameUploaded))         { $update_cols = array_merge($update_cols, ["store_icon"          => $filenameUploaded]); }

            // Enregistrement SQL
               if (!$update_cols)
               {
                  $_JSON_PRINT->fail("no data to update"); $_JSON_PRINT->print();
               }

               $_SQL          = $_MYSQL->connect(array("dexocard"));
               
               $results = $_SQL['dexocard']->update("store", $update_cols,
               [
                  "store_id" => $_PARAM['id']
               ]);

            // Print Results
               $_JSON_PRINT->success(); 
               $_JSON_PRINT->response();
               $_JSON_PRINT->print();

            break;
         }
         
      }

      function getQuery_Sets($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, $_BLOC_LIMIT = NULL)
      {
         global $_TABLE_LIST;

         // Assemblage requête SQL
            return "
               SELECT 

               " . $_BLOC_SELECT . "

               FROM `" . $_TABLE_LIST['dexocard'] . "`.`store`
               
               " . ($_BLOC_WHERE ? "WHERE " . substr($_BLOC_WHERE, 0, strlen($_BLOC_WHERE) - 4) : '') . "

               ORDER BY 
                  `" . $_TABLE_LIST['dexocard'] . "`.`store`.`store_id` ASC

               " . ($_BLOC_LIMIT ? $_BLOC_LIMIT : '') . "
               ;
            ";
      }
?>