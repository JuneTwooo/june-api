<?php
   // check Token
      $_TOKEN->checkAccess('dexocard', 'tcg/set');

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
               if (!empty($_GET['limit']))      { $_LIMIT      = intval($_GET['limit']);  }                                   else { $_LIMIT  = 10; }
               if (!empty($_GET['offset']))     { $_OFFSET     = intval($_GET['offset']); }                                   else { $_OFFSET = 0; }
               if (!empty($_GET['operand']))    { $_OPERAND    = (strtolower($_GET['operand']) == 'or' ? "OR " : "AND"); }    else { $_OPERAND = "AND"; }
            
            // Filtres
               if (!empty($_GET['filters']))
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
               }

            // Création requête SQL
               $_BLOC_SELECT =
               "
                  " . $_TABLE_LIST['dexocard'] . ".`card_set`.`card_set_id`,
                  " . $_TABLE_LIST['dexocard'] . ".`card_set`.`card_set_region`,
                  " . $_TABLE_LIST['dexocard'] . ".`card_set`.`card_set_serieid`,
                  " . $_TABLE_LIST['dexocard'] . ".`card_set`.`card_set_nameFR`,
                  " . $_TABLE_LIST['dexocard'] . ".`card_set`.`card_set_nameEN`,
                  " . $_TABLE_LIST['dexocard'] . ".`card_set`.`card_set_AbreviationFR`,
                  " . $_TABLE_LIST['dexocard'] . ".`card_set`.`card_set_AbreviationEN`,
                  " . $_TABLE_LIST['dexocard'] . ".`card_set`.`card_set_total`,
                  " . $_TABLE_LIST['dexocard'] . ".`card_set`.`card_set_printedTotal`,
                  " . $_TABLE_LIST['dexocard'] . ".`card_set`.`card_set_isedition1`,
                  " . $_TABLE_LIST['dexocard'] . ".`card_set`.`card_set_releaseDate`,

                  " . $_TABLE_LIST['dexocard'] . ".`card_set_images`.`card_set_images_symbol`
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
                     'id'                       => $thisCard['card_set_id'],
                     'regionid'                 => $thisCard['card_set_region'],
                     'serieid'                  => $thisCard['card_set_serieid'],

                     'name'                     => array
                     (
                        'FR' => (empty($thisCard['card_set_nameFR']) ? NULL : $thisCard['card_set_nameFR']), 
                        'EN' => (empty($thisCard['card_set_nameEN']) ? NULL : $thisCard['card_set_nameEN']), 
                     ),

                     'abrv'                     => array
                     (
                        'FR' => (empty($thisCard['card_set_AbreviationFR']) ? NULL : $thisCard['card_set_AbreviationFR']), 
                        'EN' => (empty($thisCard['card_set_AbreviationEN']) ? NULL : $thisCard['card_set_AbreviationEN']), 
                     ),

                     'total_card'               => $thisCard['card_set_printedTotal'],
                     'total_card_w_hidden'      => $thisCard['card_set_total'],
                     'isedition1'               => $thisCard['card_set_isedition1'],
                     'release_date'             => $thisCard['card_set_releaseDate'],
                     'symbol'                   => $thisCard['card_set_images_symbol'],
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
      }

      /**

      * @ignore

      */
      function getQuery_Sets($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, $_BLOC_LIMIT = NULL)
      {
         global $_TABLE_LIST;

         // Assemblage requête SQL
            return "
               SELECT 

               " . $_BLOC_SELECT . "

               FROM " . $_TABLE_LIST['dexocard'] . ".`card_set`

               LEFT JOIN " . $_TABLE_LIST['dexocard'] . ".`card_set_images`            ON " . $_TABLE_LIST['dexocard'] . ".`card_set_images`.`card_set_images_setid`            = `card_set_id`

               " . ($_BLOC_WHERE ? "WHERE " . substr($_BLOC_WHERE, 0, strlen($_BLOC_WHERE) - 4) : '') . "

               ORDER BY 
                  " . $_TABLE_LIST['dexocard'] . ".`card_set`.`card_set_id` ASC

               " . ($_BLOC_LIMIT ? $_BLOC_LIMIT : '') . "
               ;
            ";
      }
?>