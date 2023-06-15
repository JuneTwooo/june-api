<?php
   // check Token
      $_TOKEN->checkAccess('dexocard', 'tcg/set');

      switch (strtoupper($_METHOD))
      {
         case 'GET':
         {
            $_FILTERS_ACTIVE  = array();
            $_BLOC_WHERE      = '';
            $_ASSOCS_VARS     = array();

            // Defaults vars
               if (!empty($_GET['limit']))      { $_LIMIT      = intval($_GET['limit']);  }                                   else { $_LIMIT  = 10; }
               if (!empty($_GET['offset']))     { $_OFFSET     = intval($_GET['offset']); }                                   else { $_OFFSET = 0; }
               if (!empty($_GET['operand']))    { $_OPERAND    = (strtolower($_GET['operand']) == 'or' ? "OR " : "AND"); }    else { $_OPERAND = "AND"; }
               if (!empty($_GET['order']))      { $_ORDER      = $_GET['order']; }                                            else { $_ORDER = ""; }
            
            // Filtres
               if (!empty($_GET['search_text']))
               {
                  $_BLOC_WHERE      = $_BLOC_WHERE . " 
                  (
                        `card_set_nameFR` LIKE :search_text_set_namefr OR 
                        `card_set_nameEN` LIKE :search_text_set_nameen OR 
                        `card_set_id`     LIKE :search_text_set_id
                  ) AND";
                  $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":search_text_set_namefr"        => '%' . addslashes($_GET['search_text']) . '%']);   
                  $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":search_text_set_nameen"        => '%' . addslashes($_GET['search_text']) . '%']);   
                  $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":search_text_set_id"            => '%' . addslashes($_GET['search_text']) . '%']);   
               }

               if (!empty($_GET['id']))
               {
                  $_BLOC_WHERE      = $_BLOC_WHERE . "`card_set_id` = :card_set_id AND";
                  $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":card_set_id"        => addslashes($_GET['id'])]);
               }

               /*if (!empty($_GET['filters']))
               {
                  if (!isJson($_GET['filters']))
                  {
                     $_JSON_PRINT->fail("filters is not in JSON format"); 
                     $_JSON_PRINT->print();
                  }

                  foreach (json_decode($_GET['filters']) as $i => $item)
                  {
                     foreach ($item as $dataFilter)
                     {
                        $filter_Data      = $dataFilter->data;
                        $filter_Operand   = $dataFilter->operand;
                        $filter_Value     = $dataFilter->value;

                        array_push($_FILTERS_ACTIVE, $filter_Data);

                        // filtre les opérands inconnus
                        if (!in_array($filter_Operand, get_operand_array()))
                        {
                           $_JSON_PRINT->fail("unknow operand '$filter_Operand'"); 
                           $_JSON_PRINT->print();                                
                        }

                        switch ($filter_Data)
                        {
                           case 'id':
                           case 'serieid':
                           case 'isedition1':
                           case 'namefr':
                           case 'nameen':
                           case 'releasedate ':
                           {
                              $_BLOC_WHERE      = $_BLOC_WHERE . " `card_set_$filter_Data` $filter_Operand :$filter_Data" . "_$i " . $_OPERAND . " ";
                              $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":" . $filter_Data . "_" . $i => $filter_Value]);

                              break;
                           }

                           case 'abrvfr':
                           case 'abrven':
                           case 'total_card':
                           case 'total_card_w_hidden':
                           {
                              switch ($filter_Data)
                              {
                                 case 'total_card'             : { $col = 'card_set_printedTotal'; break; }
                                 case 'total_card_w_hidden'    : { $col = 'card_set_total'; break; }
                              }

                              $_BLOC_WHERE      = $_BLOC_WHERE . " `$col` $filter_Operand :$col" . "_$i $_OPERAND ";
                              $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":" . $col . "_" . $i => $filter_Value]);

                              break;
                           }

                           default:
                           {
                              $_JSON_PRINT->fail("unknow filter '$filter_Data'"); 
                              $_JSON_PRINT->print();                                   
                           }
                        }
                     }
                  }
               }*/

            // Création requête SQL
               $_BLOC_SELECT =
               "
                  *
               ";

            // Formatage des données envoyées
               $results_print = array();
               
               // MySQL Connect
               $_SQL    = $_MYSQL->connect(array("dexocard"));

               // Query
               foreach ($_SQL['dexocard']->query
               (
                  getQuery_Sets($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, "LIMIT " . $_OFFSET . ", " . $_LIMIT), 
                  $_ASSOCS_VARS
               )->fetchAll(PDO::FETCH_ASSOC) as $thisCard)
               {
                  array_push($results_print, array
                  (
                     'id'                       => $thisCard['card_set_id'],
                     'regionid'                 => $thisCard['card_set_region'],
                     'serieid'                  => $thisCard['card_set_serieid'],
                     'serie_name'               => array
                     (
                        'fr' => (empty($thisCard['card_serie_nameFR']) ? NULL : $thisCard['card_serie_nameFR']), 
                        'en' => (empty($thisCard['card_serie_nameEN']) ? NULL : $thisCard['card_serie_nameEN']), 
                     ),
                     'serie_name_abrv'          => array
                     (
                        'fr' => (empty($thisCard['card_serie_AbreviationFR']) ? NULL : $thisCard['card_serie_AbreviationFR']), 
                        'en' => (empty($thisCard['card_serie_AbreviationEN']) ? NULL : $thisCard['card_serie_AbreviationEN']), 
                     ),

                     'name'                     => array
                     (
                        'fr' => (empty($thisCard['card_set_nameFR']) ? NULL : $thisCard['card_set_nameFR']), 
                        'en' => (empty($thisCard['card_set_nameEN']) ? NULL : $thisCard['card_set_nameEN']), 
                     ),

                     'abrv'                     => array
                     (
                        'fr' => (empty($thisCard['card_set_AbreviationFR']) ? NULL : $thisCard['card_set_AbreviationFR']), 
                        'en' => (empty($thisCard['card_set_AbreviationEN']) ? NULL : $thisCard['card_set_AbreviationEN']), 
                     ),

                     'show'                     => $thisCard['card_set_show'],

                     'total_card'               => $thisCard['card_set_printedTotal'],
                     'total_card_w_hidden'      => $thisCard['card_set_total'],
                     'isedition1'               => $thisCard['card_set_isedition1'],

                     'release_date'             => array
                     (
                        'jp' => (empty($thisCard['card_set_releaseDatejp']) ? NULL : $thisCard['card_set_releaseDatejp']), 
                        'fr' => (empty($thisCard['card_set_releaseDatefr']) ? NULL : $thisCard['card_set_releaseDatefr']), 
                        'en' => (empty($thisCard['card_set_releaseDateen']) ? NULL : $thisCard['card_set_releaseDateen']), 
                     ),

                     'lastupdate_date'          => $thisCard['card_set_lastUpdate'],
                     'symbol'                   => array
                     (
                        'jp'  => (empty($thisCard['card_set_symboljp'])       ? NULL : $thisCard['card_set_symboljp']), 
                        'fr'  => (empty($thisCard['card_set_symbolfr'])       ? NULL : $thisCard['card_set_symbolfr']), 
                        'en'  => (empty($thisCard['card_set_symbolen'])       ? NULL : $thisCard['card_set_symbolen']), 
                        'old' => (empty($thisCard['card_set_images_symbol'])  ? NULL : $thisCard['card_set_images_symbol']), 
                     ),
                  ));
               }
  
            // Envoi des données
               $results_unfiltered = $_SQL['dexocard']->query
               (
                  getQuery_Sets($_FILTERS_ACTIVE, "COUNT(*) AS total_rows_unfiltered", $_BLOC_WHERE, NULL), 
                  $_ASSOCS_VARS
               )->fetch(PDO::FETCH_ASSOC)['total_rows_unfiltered'];

               $_JSON_PRINT->addDataBefore('results_count',          count($results_print)); 
               $_JSON_PRINT->addDataBefore('results_filters_count',  $results_unfiltered); 
               
               // debug
               //$_SQL['dexocard']->debug()->query(getQuery_Sets($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, "LIMIT " . $_OFFSET . ", " . $_LIMIT),$_ASSOCS_VARS);

               $_JSON_PRINT->success(); 
               $_JSON_PRINT->response($results_print); 
               $_JSON_PRINT->print();

            break;
         }

         case 'POST':
         case 'PUT':
         {
            // Check parameters
               if (empty($_PARAM['id']))
               {
                  $_JSON_PRINT->fail("id must be specified");
                  $_JSON_PRINT->print();                  
               }

            // MySQL Connect
               $_SQL          = $_MYSQL->connect(array("dexocard"));
         
            // Search exist
               $_SQL_ITEM  = $_SQL['dexocard']->query("SELECT * FROM `" . $_TABLE_LIST['dexocard'] . "`.`card_set` WHERE card_set_id = :set_id", [":set_id" => $_PARAM['id']])->fetch(PDO::FETCH_ASSOC);

            // Upload files
               $filenameUploaded = array();
               $phash            = array();
               $input_id         = 'symbol';

               foreach (array('fr', 'en') as $lang)
               {
                  $filenameUploaded[$lang] = false;

                  if (!empty($_FILES[$input_id . $lang]) || !empty($_FILES[$input_id . $lang]))
                  {
                     $dir_Target    = 'sets/';
                     $file_Target   = str_pad($_PARAM['id'], 6, "0", STR_PAD_LEFT) . '-' . $lang . '-' . cleanTitleURL($_PARAM['namefr']);
            
                     $uploadResult = null;
                     $uploadResult = uploadFile_Image($_FILES[$input_id . $lang], $_CONFIG['PRODUCTS']['DEXOCARD']['RES_ROOT'], $dir_Target, $file_Target, true);

                     if (!$uploadResult['success'])
                     {
                        $_JSON_PRINT->fail("upload error : " . $uploadResult['raison']);
                        $_JSON_PRINT->print();     
                     }
                     else
                     {
                        // traitement post upload
                        $filenameUploaded[$lang] = $uploadResult['filename'];
                     }
                  }
               }

            // Enregistrement SQL
               $update_sql = array();

               $update_sql = array_merge($update_sql, ["card_set_nameFR"               => (!empty($_PARAM['namefr']) ? $_PARAM['namefr'] : NULL)]);
               $update_sql = array_merge($update_sql, ["card_set_nameEN"               => (!empty($_PARAM['nameen']) ? $_PARAM['nameen'] : NULL)]);
               $update_sql = array_merge($update_sql, ["card_set_AbreviationFR"        => (!empty($_PARAM['abrvfr']) ? $_PARAM['abrvfr'] : NULL)]);
               $update_sql = array_merge($update_sql, ["card_set_AbreviationEN"        => (!empty($_PARAM['abrven']) ? $_PARAM['abrven'] : NULL)]);
               $update_sql = array_merge($update_sql, ["card_set_show"                 => (!empty($_PARAM['show'])   ? 1 : 0)]);
               if (!empty($filenameUploaded['fr']))      { $update_sql = array_merge($update_sql, ["card_set_symbolfr"           => $filenameUploaded['fr']]); }
               if (!empty($filenameUploaded['en']))      { $update_sql = array_merge($update_sql, ["card_set_symbolen"           => $filenameUploaded['en']]); }

               if ($update_sql)
               {
                  $results = $_SQL['dexocard']->update("card_set", $update_sql,
                  [
                     "card_set_id" => $_PARAM['id']
                  ]);
               }

            // Print Results
               $_JSON_PRINT->success(); 
               $_JSON_PRINT->response();
               $_JSON_PRINT->print();

            break;
         }
   
      }

      /**

      * @ignore

      */
      function getQuery_Sets($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, $_BLOC_LIMIT = NULL)
      {
         global $_TABLE_LIST;

         //$_BLOC_WHERE      = $_BLOC_WHERE . " `card_set_show` = 1 AND ";

         // Assemblage requête SQL
            return "
               SELECT 

               " . $_BLOC_SELECT . "

               FROM " . $_TABLE_LIST['dexocard'] . ".`card_set`
               
               LEFT JOIN `card_serie` ON `card_set`.`card_set_serieid` = `card_serie`.`card_serie_id`
               LEFT JOIN " . $_TABLE_LIST['dexocard'] . ".`card_set_images`            ON " . $_TABLE_LIST['dexocard'] . ".`card_set_images`.`card_set_images_setid`            = `card_set_id`

               " . ($_BLOC_WHERE ? "WHERE " . substr($_BLOC_WHERE, 0, strlen($_BLOC_WHERE) - 4) : '') . "

               ORDER BY 
                  `card_set`.`card_set_serieid` ASC,
                  `card_set`.`card_set_id` ASC

               " . ($_BLOC_LIMIT ? $_BLOC_LIMIT : '') . "
               ;
            ";
      }
?>