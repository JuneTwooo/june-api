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
                           case 'namefr':
                           case 'nameen':
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

                           case 'pokemonid':
                           case 'typeid':
                           case 'ability':
                           case 'capacity':
                           {
                              switch ($filter_Data)
                              {
                                 case 'pokemonid'        : { $col = 'card_nationalDexId_DexId'; break; }
                                 case 'typeid'           : { $col = 'card_types_typeid'; break; }
                                 case 'ability'          : { $col = 'card_abilities_name'; break; }
                                 case 'capacity'         : { $col = 'card_attacks_name'; break; }
                              }

                              $_BLOC_WHERE      = $_BLOC_WHERE . " `$col` $filter_Operand :$col" . "_$i $_OPERAND ";
                              $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":" . $col . "_" . $i => $filter_Value]);

                              break;
                           }

                           case 'isnormal':
                           case 'isholo':
                           case 'isreverse':
                           case 'israinbow':
                           case 'isgold':
                           case 'isprime':
                           case 'isescouade':
                           case 'isexmin':
                           case 'isexmaj':
                           case 'isstar':
                           case 'isdelta':
                           case 'isturbo':
                           case 'isgx':
                           case 'isv':
                           case 'isvmax':
                           case 'isvstar':
                           case 'islegend':
                           case 'isobscur':
                           case 'islumineux':
                           case 'isbrillant':
                           case 'isnivx':                              
                           case 'ismega':
                           {
                              switch ($filter_Data)
                              {
                                 case 'isnormal'         : { $col = 'card_holo_NormalExist'; break; }
                                 case 'isholo'           : { $col = 'card_holo_HoloExist'; break; }
                                 case 'isreverse'        : { $col = 'card_holo_ReverseExist'; break; }
                                 case 'israinbow'        : { $col = 'card_propriety_israinbow'; break; }
                                 case 'isgold'           : { $col = 'card_propriety_isgold'; break; }
                                 case 'isprime'          : { $col = 'card_propriety_isprime'; break; }
                                 case 'isescouade'       : { $col = 'card_propriety_isescouade'; break; }
                                 case 'isexmin'          : { $col = 'card_propriety_isexmin'; break; }
                                 case 'isexmaj'          : { $col = 'card_propriety_isEXmaj'; break; }
                                 case 'isstar'           : { $col = 'card_propriety_isstar'; break; }
                                 case 'isdelta'          : { $col = 'card_propriety_isdelta'; break; }
                                 case 'isturbo'          : { $col = 'card_propriety_isTURBO'; break; }
                                 case 'isgx'             : { $col = 'card_propriety_isGX'; break; }
                                 case 'isv'              : { $col = 'card_propriety_isV'; break; }
                                 case 'isvmax'           : { $col = 'card_propriety_isVMAX'; break; }
                                 case 'isvstar'          : { $col = 'card_propriety_isVSTAR'; break; }
                                 case 'islegend'         : { $col = 'card_propriety_isLEGEND'; break; }
                                 case 'isobscur'         : { $col = 'card_propriety_isobscur'; break; }
                                 case 'islumineux'       : { $col = 'card_propriety_islumineux'; break; }
                                 case 'isbrillant'       : { $col = 'card_propriety_isbrillant'; break; }
                                 case 'isnivx'           : { $col = 'card_propriety_isnivx'; break; }
                                 case 'ismega'           : { $col = 'card_propriety_ismega'; break; }
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
                        'FR', `card_name_nameFR`,
                        'EN', `card_name_nameEN`
                     )) FROM `card_name` WHERE `card_name_cardid` = `card_id` LIMIT 0,1
                  ) AS `card_name`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'FR', `card_flavorText_flavorTextFR`,
                        'EN', `card_flavorText_flavorTextEN`
                     )) FROM `card_flavorText` WHERE `card_flavorText_cardid` = `card_id` LIMIT 0,1
                  ) AS `card_flavorText`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'israinbow', `card_propriety_israinbow`,
                        'isgold', `card_propriety_isgold`,
                        'isblackgold', `card_propriety_isblackgold`,
                        'isprime', `card_propriety_isprime`,
                        'isescouade', `card_propriety_isescouade`,
                        'isexmin', `card_propriety_isexmin`,
                        'isEXmaj', `card_propriety_isEXmaj`,
                        'isstar', `card_propriety_isstar`,
                        'isdelta', `card_propriety_isdelta`,
                        'isTURBO', `card_propriety_isTURBO`,
                        'isGX', `card_propriety_isGX`,
                        'isV', `card_propriety_isV`,
                        'isVMAX', `card_propriety_isVMAX`,
                        'isVSTAR', `card_propriety_isVSTAR`,
                        'isLEGEND', `card_propriety_isLEGEND`,
                        'isobscur', `card_propriety_isobscur`,
                        'islumineux', `card_propriety_islumineux`,
                        'isbrillant', `card_propriety_isbrillant`,
                        'isnivx', `card_propriety_isnivx`,
                        'ismega', `card_propriety_ismega`
                     )) FROM `card_propriety` WHERE `card_propriety_cardid` = `card_id` LIMIT 0,1
                  ) AS `card_property`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'name', `card_abilities_name`,
                        'text', `card_abilities_text`,
                        'type', `card_abilities_type`
                     )) FROM `card_abilities` WHERE `card_abilities_cardid` = `card_id` AND `card_abilities_lang` = 'FR' LIMIT 0,1
                  ) AS `card_abilities_FR`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'name',                 `card_abilities_name`,
                        'text',                 `card_abilities_text`,
                        'type',                 `card_abilities_type`
                     )) FROM `card_abilities` WHERE `card_abilities_cardid` = `card_id` AND `card_abilities_lang` = 'EN' LIMIT 0,1
                  ) AS `card_abilities_EN`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'name',                 `card_attacks_name`,
                        'text',                 `card_attacks_text`,
                        'damage',               `card_attacks_damage`,
                        'convertedEnergyCost',  `card_attacks_convertedEnergyCost`,
                        'costtypeid1',          `card_attacks_costtypeid1`,
                        'costtypeid2',          `card_attacks_costtypeid2`,
                        'costtypeid3',          `card_attacks_costtypeid3`,
                        'costtypeid4',          `card_attacks_costtypeid4`,
                        'costtypeid5',          `card_attacks_costtypeid5`
                     )) FROM `card_attacks` WHERE `card_attacks_cardid` = `card_id` AND `card_attacks_lang` = 'FR'
                  ) AS `card_attacks_FR`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'name',                 `card_attacks_name`,
                        'text',                 `card_attacks_text`,
                        'damage',               `card_attacks_damage`,
                        'convertedEnergyCost',  `card_attacks_convertedEnergyCost`,
                        'costtypeid1',          `card_attacks_costtypeid1`,
                        'costtypeid2',          `card_attacks_costtypeid2`,
                        'costtypeid3',          `card_attacks_costtypeid3`,
                        'costtypeid4',          `card_attacks_costtypeid4`,
                        'costtypeid5',          `card_attacks_costtypeid5`
                     )) FROM `card_attacks` WHERE `card_attacks_cardid` = `card_id` AND `card_attacks_lang` = 'EN'
                  ) AS `card_attacks_EN`,
            
                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'typeid', `card_types_typeid`
                     )) FROM `card_types` WHERE `card_types_cardid` = `card_id`
                  ) AS `card_types`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'typeid',   `card_weaknesses_typeid`,
                        'value',    `card_weaknesses_value`
                     )) FROM `card_weaknesses` WHERE `card_weaknesses_cardid` = `card_id`
                  ) AS `card_weaknesses`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'dexId', `card_nationalDexId_DexId`
                     )) FROM `card_nationalDexId` WHERE `card_nationalDexId_cardid` = `card_id`
                  ) AS `card_nationalDexId`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'highFR',      `card_images_imagesHighFR`,
                        'highEN',      `card_images_imagesHighEN`,
                        'smallFR',     `card_images_imagesSmallFR`,
                        'smallEN',     `card_images_imagesSmallEN`,
                        'hasRelief ',  `card_holo_HasRelief`,
                        'lastUpdate',  `card_images_LastUpdate`
                     )) FROM `card_images` WHERE `card_images_cardid` = `card_id`
                  ) AS `card_image`,

                  JSON_OBJECT
                  (
                     'normal',      `card_holo_NormalExist`,
                     'holo',        IF(`card_holo_FullExist` IS NOT NULL, 1, `card_holo_HoloExist`),
                     'reverse',     `card_holo_ReverseExist`
                  ) AS `card_variant`,

                  (
                     SELECT (JSON_OBJECT
                     (
                        'count', count(*),
                        'avg', CAST(avg(`card_prices_Price`) AS DECIMAL(10,2)), 
                        'min', CAST(min(`card_prices_Price`) AS DECIMAL(10,2)), 
                        'max', CAST(max(`card_prices_Price`) AS DECIMAL(10,2)))) FROM `card_price_ebay` WHERE `card_prices_CardId` = `card_id` AND `card_prices_Sold` = 1 AND date_sub(`card_prices_DateLastSeen`, INTERVAL 7 day)
                  ) AS `price_stats_sold_28`,

                  (
                     SELECT (JSON_OBJECT
                     (
                        'count', count(*),
                        'avg', CAST(avg(`card_prices_Price`) AS DECIMAL(10,2)), 
                        'min', CAST(min(`card_prices_Price`) AS DECIMAL(10,2)), 
                        'max', CAST(max(`card_prices_Price`) AS DECIMAL(10,2)))) FROM `card_price_ebay` WHERE `card_prices_CardId` = `card_id` and card_prices_Grader = 'PCA' AND card_prices_Grade = 10 AND `card_prices_Sold` = 1 AND date_sub(`card_prices_DateLastSeen`, INTERVAL 28 day)
                  ) AS `price_stats_sold_28_PCA_10`,

                  (
                     SELECT (JSON_OBJECT
                     (
                        'count', count(*),
                        'avg', CAST(avg(`card_prices_Price`) AS DECIMAL(10,2)), 
                        'min', CAST(min(`card_prices_Price`) AS DECIMAL(10,2)), 
                        'max', CAST(max(`card_prices_Price`) AS DECIMAL(10,2)))) FROM `card_price_ebay` WHERE `card_prices_CardId` = `card_id` and card_prices_Grader = 'PCA' AND card_prices_Grade = 9.5 AND `card_prices_Sold` = 1 AND date_sub(`card_prices_DateLastSeen`, INTERVAL 28 day)
                  ) AS `price_stats_sold_28_PCA_95`

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
                  array_push($results_print, array
                  (
                     'id'                 => $thisCard['card_id'],
                     'serie_id'           => $thisCard['card_serieid'],
                     'set_id'             => $thisCard['card_setid'],
                     'number'             => $thisCard['card_number'],
                     'index'              => $thisCard['card_index'],
                     'level'              => $thisCard['card_level'],
                     'hp'                 => $thisCard['card_hp'],
                     'rarity'             => $thisCard['card_rarity'],
                     'rarity_simplified'  => $thisCard['card_raritySimplified'],
                     'rarity_index'       => $thisCard['card_rarityIndex'],
                     'supertype'          => $thisCard['card_supertype'],

                     'types'              => (empty($thisCard['card_types']) ? NULL : json_decode($thisCard['card_types'], true)),

                     'variant'            => (empty($thisCard['card_variant']) ? NULL : json_decode($thisCard['card_variant'], true)),

                     'name'               => (empty($thisCard['card_name']) ? NULL : json_decode($thisCard['card_name'], true)),

                     'artist'             => array
                     (
                        array($thisCard['card_artist'])
                     ),

                     'pokemon'            => (empty($thisCard['card_nationalDexId']) ? NULL : json_decode($thisCard['card_nationalDexId'], true)),

                     'flavor_text'        => (empty($thisCard['card_flavorText']) ? NULL : json_decode($thisCard['card_flavorText'], true)),

                     'abilities'          => array
                     (
                        'FR' => (empty($thisCard['card_abilities_FR']) ? NULL : json_decode($thisCard['card_abilities_FR'], true)),
                        'EN' => (empty($thisCard['card_abilities_EN']) ? NULL : json_decode($thisCard['card_abilities_EN'], true)),
                     ),

                     'capacities'         => array
                     (
                        'FR' => (empty($thisCard['card_attacks_FR']) ? NULL : json_decode($thisCard['card_attacks_FR'], true)),
                        'EN' => (empty($thisCard['card_attacks_EN']) ? NULL : json_decode($thisCard['card_attacks_EN'], true)),
                     ),

                     'propriety'          => (empty($thisCard['card_property']) ? NULL : json_decode($thisCard['card_property'], true)),

                     'retreatCost'        => $thisCard['card_convertedRetreatCost'],
                     'weaknesses'         => (empty($thisCard['card_weaknesses']) ? NULL : json_decode($thisCard['card_weaknesses'], true)),

                     'image'              => (empty($thisCard['card_image']) ? NULL : json_decode($thisCard['card_image'], true)),

                     'price_stats'        => array
                     (
                        'sold'         => array
                        (
                           'ungraded_28d' => (empty($thisCard['price_stats_sold_28']) ? NULL : json_decode($thisCard['price_stats_sold_28'], true)),
                           'pca_10_28d' => (empty($thisCard['price_stats_sold_28_PCA_10']) ? NULL : json_decode($thisCard['price_stats_sold_28_PCA_10'], true)),
                           'pca_95_28d' => (empty($thisCard['price_stats_sold_28_PCA_95']) ? NULL : json_decode($thisCard['price_stats_sold_28_PCA_95'], true)),
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

               LEFT JOIN `card_holo`            ON `card_holo_cardid`            = `card_id`

               " . (in_array("pokemonid",       $_FILTERS_ACTIVE) ? "LEFT JOIN `card_nationalDexId`   ON `card_nationalDexId_cardid`      = `card_id`" : '') . "
               " . (in_array("typeid",          $_FILTERS_ACTIVE) ? "LEFT JOIN `card_types`           ON `card_types_cardid`              = `card_id`" : '') . "
               " . (in_array("ability",         $_FILTERS_ACTIVE) ? "LEFT JOIN `card_abilities`       ON `card_abilities_cardid`          = `card_id`" : '') . "
               " . (in_array("capacity",        $_FILTERS_ACTIVE) ? "LEFT JOIN `card_attacks`         ON `card_attacks_cardid`            = `card_id`" : '') . "

               " . ($_BLOC_WHERE ? "WHERE " . substr($_BLOC_WHERE, 0, strlen($_BLOC_WHERE) - 4) : '') . "

               ORDER BY `card_setid` ASC, `card_index` ASC

               " . ($_BLOC_LIMIT ? $_BLOC_LIMIT : '') . "

               
               ;
            ";
      }
?>