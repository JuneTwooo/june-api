<?php
   // check Token
      $_TOKEN->checkAccess('dexocard', 'tcg/card');

      switch (strtoupper($_METHOD))
      {
         case 'GET':
         {
            /*
               ?operand=and&filters=[{"filter":{"data":"level","operand":">=","value":1}},{"filter":{"data":"level","operand":"<","value":20}}]
               ?operand=or&filters=[{"filter":{"data":"level","operand":"=","value":1}}]
               ?operand=or&filters=[{"filter":{"data":"id","operand":"=","value":"base1-4"}}]
            */

            $_FILTERS_ACTIVE  = array();
            $_BLOC_WHERE      = '';
            $_ASSOCS_VARS     = array();

            // Defaults vars
               if (!empty($_GET['limit']))      { $_LIMIT   = intval($_GET['limit']);  }                                      else { $_LIMIT  = 10; }
               if (!empty($_GET['offset']))     { $_OFFSET  = intval($_GET['offset']); }                                      else { $_OFFSET = 0; }
               if (!empty($_GET['operand']))    { $_OPERAND = (strtolower($_GET['operand']) == 'or' ? "OR" : "AND"); }        else { $_OPERAND = "AND"; }
            
            // Handles
               if ($_LIMIT > 3000)
               {
                  $_JSON_PRINT->fail("maximum limit is 3000"); 
                  $_JSON_PRINT->print();
               }
               
            // Filtres
               if (!empty($_GET['filters']))
               {
                  if (!isJson($_GET['filters']))
                  {
                     $_JSON_PRINT->fail("filters is not in JSON format"); 
                     $_JSON_PRINT->print();
                  }

                  $array_OperandsList = array("=", ">", "<", ">=", "<=", "LIKE");
                  
                  foreach (json_decode($_GET['filters']) as $i => $item)
                  {
                     foreach ($item as $dataFilter)
                     {
                        $filter_Data      = $dataFilter->data;
                        $filter_Operand   = $dataFilter->operand;
                        $filter_Value     = $dataFilter->value;

                        array_push($_FILTERS_ACTIVE, $filter_Data);

                        // filtre les opérands inconnus
                        if (!in_array($filter_Operand, $array_OperandsList))
                        {
                           $_JSON_PRINT->fail("unknow operand '$filter_Operand'"); 
                           $_JSON_PRINT->print();                                
                        }

                        switch ($filter_Data)
                        {
                           case 'id':
                           case 'serieid':
                           case 'setid':
                           case 'number':
                           case 'index':
                           case 'level':
                           case 'hp':
                           case 'supertype':
                           case 'artist':
                           case 'rarity':
                           case 'rarity_simplified':
                           case 'rarity_index':
                           {
                              if ($filter_Data == 'namefr') { $filter_Data = 'name_namefr'; }

                              $_BLOC_WHERE      = $_BLOC_WHERE . " `card_$filter_Data` $filter_Operand :$filter_Data" . "_$i $_OPERAND ";
                              $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":" . $filter_Data . "_" . $i => $filter_Value]);

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
                  `card_id`,
                  `card_number`,
                  `card_index`,
                  `card_serieid`,
                  `card_setid`,
                  `card_level`,
                  `card_hp`,
                  `card_artist`,
                  `card_rarity`,
                  `card_raritySimplified`,
                  `card_rarityIndex`,
                  `card_supertype`,
                  `card_convertedRetreatCost`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'dexId', `card_nationalDexId_DexId`
                     )) FROM `card_nationalDexId` WHERE `card_nationalDexId_cardid` = `card_id`
                  ) AS `card_nationalDexId`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'item_id', `card_prices_ItemId`,
                        'title', `card_prices_Title`,
                        'price', `card_prices_Price`,
                        'variant', `card_prices_Type`
                     )) FROM `card_price_ebay` 
                     WHERE 
                        `card_prices_CardId` = `card_id` AND 
                        `card_prices_Sold` = 0 AND
                        `card_prices_GraderId` IS NULL
                     ORDER BY `card_prices_DateLastSeen` ASC
                  ) AS `ebay_prices_unsold_ungraded`
               ";

            // Formatage des données envoyées
               $results_print = array();
               $_SQL    = $_MYSQL->connect(array("api"));
               foreach ($_SQL['api']->query
               (
                  getQuery_Cards($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, "LIMIT " . $_OFFSET . ", " . $_LIMIT),
                  $_ASSOCS_VARS
               )->fetchAll(PDO::FETCH_ASSOC) as $thisCard)
               {
                  $prices = array();

                  $prices['sold_history'] = $_SQL['api']->query("
                     SELECT 
                        DATE(`card_prices_DateLastSeen`) as `date`,
                        COUNT(*) AS `count`,
                        CAST(AVG(`card_prices_Price`) AS DECIMAL(10,2)) AS `avg`,
                        CAST(MIN(`card_prices_Price`) AS DECIMAL(10,2)) AS `min`,
                        CAST(MAX(`card_prices_Price`) AS DECIMAL(10,2)) AS `max`
                     FROM `card_price_ebay` 
                     WHERE `card_prices_CardId` = :card_id AND `card_prices_GraderId` IS NULL AND `card_prices_Sold` = 1 
                     GROUP BY DATE(`card_prices_DateLastSeen`)
                     ORDER BY `date` ASC;
                  ", [":card_id" => $thisCard['card_id']])->fetchAll(PDO::FETCH_ASSOC);

                  array_push($results_print, array
                  (
                     'id'                 => $thisCard['card_id'],

                     'ebay'               => array
                     (
                        'unsold'               => array
                        (
                           'ungraded'        => (empty($thisCard['ebay_prices_unsold_ungraded']) ? NULL : json_decode($thisCard['ebay_prices_unsold_ungraded'], true)),
                        ),
   
                        'history'            => array
                        (
                           'sold'            => (empty($prices['sold_history']) ? NULL : ($prices['sold_history'])),
                        ),   
                     ),

                  ));
               }

               //usort($results_print, fn($a, $b) => $a['id'] <=> $b['id']);
               array_multisort(array_column($results_print, 'id'), SORT_ASC, SORT_NATURAL, $results_print);
  
            // Envoi des données
               $results_unfiltered = $_SQL['api']->query
               (
                  getQuery_Cards($_FILTERS_ACTIVE, "COUNT(*) AS total_rows_unfiltered", $_BLOC_WHERE, NULL), 
                  $_ASSOCS_VARS
               )->fetch(PDO::FETCH_ASSOC)['total_rows_unfiltered'];

               $_JSON_PRINT->addDataBefore('results_count',          count($results_print)); 
               $_JSON_PRINT->addDataBefore('results_filters_count',  $results_unfiltered); 
               
               // debug
               //$_SQL['api']->debug()->query(getQuery_Cards($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, "LIMIT " . $_OFFSET . ", " . $_LIMIT),$_ASSOCS_VARS); exit();

               $_JSON_PRINT->success(); 
               $_JSON_PRINT->response($results_print); 
               $_JSON_PRINT->print();

            break;
         }
      }

      function getQuery_Cards($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, $_BLOC_LIMIT = NULL)
      {
         // Assemblage requête SQL
            return "
               SELECT 

               " . $_BLOC_SELECT . "

               FROM `card`

               " . (in_array("pokemonid",       $_FILTERS_ACTIVE) ? "LEFT JOIN `card_nationalDexId`   ON `card_nationalDexId_cardid`      = `card_id`" : '') . "
               " . (in_array("typeid",          $_FILTERS_ACTIVE) ? "LEFT JOIN `card_types`           ON `card_types_cardid`              = `card_id`" : '') . "

               " . ($_BLOC_WHERE ? "WHERE " . substr($_BLOC_WHERE, 0, strlen($_BLOC_WHERE) - 4) : '') . "

               ORDER BY `card_setid` ASC, `card_index` ASC

               " . ($_BLOC_LIMIT ? $_BLOC_LIMIT : '') . "

               
               ;
            ";
      }
?>