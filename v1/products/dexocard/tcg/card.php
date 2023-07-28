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
               if ($_LIMIT > 3000 || 0 >= $_LIMIT)
               {
                  $_JSON_PRINT->fail("limit must set between 1 and 3000"); 
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

                  foreach (json_decode($_GET['filters']) as $i => $item)
                  {
                     foreach ($item as $dataFilter)
                     {
                        $filter_Data      = $dataFilter->data;
                        $filter_Operand   = strtoupper($dataFilter->operand);
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
                           case 'setid':
                           case 'number':
                           case 'numbermax':
                           case 'index':
                           case 'level':
                           case 'hp':
                           case 'supertype':
                           case 'edition1':
                           case 'namefr':
                           {
                              if ($filter_Data == 'namefr')    { $filter_Data = 'name_nameFR'; }
                              if ($filter_Data == 'numbermax') { $filter_Data = 'set_printedTotal'; }
                              if ($filter_Data == 'edition1')  { $filter_Data = 'set_isedition1'; }

                              $_BLOC_WHERE      = $_BLOC_WHERE . " `card_$filter_Data` $filter_Operand :$filter_Data" . "_$i $_OPERAND ";
                              $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":" . $filter_Data . "_" . $i => ($filter_Operand == 'LIKE' ? '%' : '') . $filter_Value . ($filter_Operand == 'LIKE' ? '%' : '')]);

                              break;
                           }
                           case 'nameen':
                           case 'artist':
                           case 'rarity':
                           case 'rarity_simplified':
                           case 'rarity_index': 
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
                  `" . $_TABLE_LIST['dexocard'] . "`.`card`.`card_id`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`card`.`card_number`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`card`.`card_index`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`card`.`card_serieid`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`card`.`card_setid`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`card`.`card_level`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`card`.`card_hp`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`card`. `card_artist`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`card`.`card_rarity`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`card`.`card_raritySimplified`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`card`.`card_rarityIndex`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`card`.`card_supertype`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`card`.`card_convertedRetreatCost`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'fr',    `card_name`.`card_name_nameFR`,
                        'en',    `card_name`.`card_name_nameEN`
                     )) 
                     FROM 
                        `card_name`
                     WHERE 
                        `card_name`.`card_name_cardid` = `card_id` 
                     LIMIT 0,1
                  ) AS `card_name`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'fr',    `card_serie`.`card_serie_nameFR`,
                        'en',    `card_serie`.`card_serie_nameFR`
                     )) 
                     FROM 
                        `card_serie`
                     WHERE 
                        `card_serie`.`card_serie_id` = `card_serieid` 
                     LIMIT 0,1
                  ) AS `card_serie`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'fr',                      `card_set`.`card_set_nameFR`,
                        'en',                      `card_set`.`card_set_nameEN`,
                        'printedTotal',            `card_set`.`card_set_printedTotal`,
                        'is_ed1',                  `card_set`.`card_set_isedition1`
                     )) 
                     FROM 
                        `card_set`
                     WHERE 
                        `card_set`.`card_set_id` = `card_setid` 
                     LIMIT 0,1
                  ) AS `card_set`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'id1',    `card_alt`.`card_alt_cardid1`,
                        'id2',    `card_alt`.`card_alt_cardid2`,
                        'id3',    `card_alt`.`card_alt_cardid3`
                     )) 
                     FROM 
                        `card_alt`
                     WHERE 
                        `card_alt`.`card_alt_cardid` = `card_id` 
                     LIMIT 0,1
                  ) AS `card_alt`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'symbol', `card_set_images`.`card_set_images_symbol`
                     )) 
                     FROM 
                        `card_set_images`
                     WHERE 
                        `card_set_images`.`card_set_images_setid` = `card_setid` 
                     LIMIT 0,1
                  ) AS `card_set_images`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'fr',    `" . $_TABLE_LIST['dexocard'] . "`.`card_flavorText`.`card_flavorText_flavorTextFR`,
                        'en',    `" . $_TABLE_LIST['dexocard'] . "`.`card_flavorText`.`card_flavorText_flavorTextEN`
                     )) 
                     FROM 
                        `" . $_TABLE_LIST['dexocard'] . "`.`card_flavorText` 
                     WHERE 
                        `" . $_TABLE_LIST['dexocard'] . "`.`card_flavorText`.`card_flavorText_cardid` = `card_id` 
                     LIMIT 0,1
                  ) AS `card_flavorText`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'israinbow',      " . $_TABLE_LIST['dexocard'] . ".`card_propriety`.`card_propriety_israinbow`,
                        'isgold',         " . $_TABLE_LIST['dexocard'] . ".`card_propriety`.`card_propriety_isgold`,
                        'isblackgold',    " . $_TABLE_LIST['dexocard'] . ".`card_propriety`.`card_propriety_isblackgold`,
                        'isprime',        " . $_TABLE_LIST['dexocard'] . ".`card_propriety`.`card_propriety_isprime`,
                        'isescouade',     " . $_TABLE_LIST['dexocard'] . ".`card_propriety`.`card_propriety_isescouade`,
                        'isexmin',        " . $_TABLE_LIST['dexocard'] . ".`card_propriety`.`card_propriety_isexmin`,
                        'isEXmaj',        " . $_TABLE_LIST['dexocard'] . ".`card_propriety`.`card_propriety_isEXmaj`,
                        'isstar',         " . $_TABLE_LIST['dexocard'] . ".`card_propriety`.`card_propriety_isstar`,
                        'isdelta',        " . $_TABLE_LIST['dexocard'] . ".`card_propriety`.`card_propriety_isdelta`,
                        'isTURBO',        " . $_TABLE_LIST['dexocard'] . ".`card_propriety`.`card_propriety_isTURBO`,
                        'isGX',           " . $_TABLE_LIST['dexocard'] . ".`card_propriety`.`card_propriety_isGX`,
                        'isV',            " . $_TABLE_LIST['dexocard'] . ".`card_propriety`.`card_propriety_isV`,
                        'isVMAX',         " . $_TABLE_LIST['dexocard'] . ".`card_propriety`.`card_propriety_isVMAX`,
                        'isVSTAR',        " . $_TABLE_LIST['dexocard'] . ".`card_propriety`.`card_propriety_isVSTAR`,
                        'isLEGEND',       " . $_TABLE_LIST['dexocard'] . ".`card_propriety`.`card_propriety_isLEGEND`,
                        'isobscur',       " . $_TABLE_LIST['dexocard'] . ".`card_propriety`.`card_propriety_isobscur`,
                        'islumineux',     " . $_TABLE_LIST['dexocard'] . ".`card_propriety`.`card_propriety_islumineux`,
                        'isbrillant',     " . $_TABLE_LIST['dexocard'] . ".`card_propriety`.`card_propriety_isbrillant`,
                        'isnivx',         " . $_TABLE_LIST['dexocard'] . ".`card_propriety`.`card_propriety_isnivx`,
                        'ismega',         " . $_TABLE_LIST['dexocard'] . ".`card_propriety`.`card_propriety_ismega`
                     )) 
                     FROM 
                        " . $_TABLE_LIST['dexocard'] . ".`card_propriety`
                     WHERE 
                        " . $_TABLE_LIST['dexocard'] . ".`card_propriety`.`card_propriety_cardid` = `card_id` 
                     LIMIT 0,1
                  ) AS `card_property`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'name',  " . $_TABLE_LIST['dexocard'] . ".`card_abilities`.`card_abilities_name`,
                        'text',  " . $_TABLE_LIST['dexocard'] . ".`card_abilities`.`card_abilities_text`,
                        'type',  " . $_TABLE_LIST['dexocard'] . ".`card_abilities`.`card_abilities_type`
                     )) 
                     FROM 
                        " . $_TABLE_LIST['dexocard'] . ".`card_abilities`
                     WHERE
                        " . $_TABLE_LIST['dexocard'] . ".`card_abilities`.`card_abilities_cardid` = `card_id` AND 
                        " . $_TABLE_LIST['dexocard'] . ".`card_abilities`.`card_abilities_lang` = 'FR' 
                     LIMIT 0,1
                  ) AS `card_abilities_FR`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'name',  " . $_TABLE_LIST['dexocard'] . ".`card_abilities`.`card_abilities_name`,
                        'text',  " . $_TABLE_LIST['dexocard'] . ".`card_abilities`.`card_abilities_text`,
                        'type',  " . $_TABLE_LIST['dexocard'] . ".`card_abilities`.`card_abilities_type`
                     )) 
                     FROM
                        " . $_TABLE_LIST['dexocard'] . ".`card_abilities`
                     WHERE 
                        " . $_TABLE_LIST['dexocard'] . ".`card_abilities`.`card_abilities_cardid` = `card_id` AND
                        " . $_TABLE_LIST['dexocard'] . ".`card_abilities`.`card_abilities_lang` = 'EN'
                     LIMIT 0,1
                  ) AS `card_abilities_EN`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'name',                    " . $_TABLE_LIST['dexocard'] . ".`card_attacks`.`card_attacks_name`,
                        'text',                    " . $_TABLE_LIST['dexocard'] . ".`card_attacks`.`card_attacks_text`,
                        'damage',                  " . $_TABLE_LIST['dexocard'] . ".`card_attacks`.`card_attacks_damage`,
                        'convertedEnergyCost',     " . $_TABLE_LIST['dexocard'] . ".`card_attacks`.`card_attacks_convertedEnergyCost`,
                        'costtypeid1',             " . $_TABLE_LIST['dexocard'] . ".`card_attacks`.`card_attacks_costtypeid1`,
                        'costtypeid2',             " . $_TABLE_LIST['dexocard'] . ".`card_attacks`.`card_attacks_costtypeid2`,
                        'costtypeid3',             " . $_TABLE_LIST['dexocard'] . ".`card_attacks`.`card_attacks_costtypeid3`,
                        'costtypeid4',             " . $_TABLE_LIST['dexocard'] . ".`card_attacks`.`card_attacks_costtypeid4`,
                        'costtypeid5',             " . $_TABLE_LIST['dexocard'] . ".`card_attacks`.`card_attacks_costtypeid5`
                     ))
                     FROM
                        " . $_TABLE_LIST['dexocard'] . ".`card_attacks`
                     WHERE
                        " . $_TABLE_LIST['dexocard'] . ".`card_attacks`.`card_attacks_cardid` = `card_id` AND
                        " . $_TABLE_LIST['dexocard'] . ".`card_attacks`.`card_attacks_lang` = 'FR'
                  ) AS `card_attacks_FR`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'name',                    " . $_TABLE_LIST['dexocard'] . ".`card_attacks`.`card_attacks_name`,
                        'text',                    " . $_TABLE_LIST['dexocard'] . ".`card_attacks`.`card_attacks_text`,
                        'damage',                  " . $_TABLE_LIST['dexocard'] . ".`card_attacks`.`card_attacks_damage`,
                        'convertedEnergyCost',     " . $_TABLE_LIST['dexocard'] . ".`card_attacks`.`card_attacks_convertedEnergyCost`,
                        'costtypeid1',             " . $_TABLE_LIST['dexocard'] . ".`card_attacks`.`card_attacks_costtypeid1`,
                        'costtypeid2',             " . $_TABLE_LIST['dexocard'] . ".`card_attacks`.`card_attacks_costtypeid2`,
                        'costtypeid3',             " . $_TABLE_LIST['dexocard'] . ".`card_attacks`.`card_attacks_costtypeid3`,
                        'costtypeid4',             " . $_TABLE_LIST['dexocard'] . ".`card_attacks`.`card_attacks_costtypeid4`,
                        'costtypeid5',             " . $_TABLE_LIST['dexocard'] . ".`card_attacks`.`card_attacks_costtypeid5`
                     ))
                     FROM
                        " . $_TABLE_LIST['dexocard'] . ".`card_attacks`
                     WHERE
                        " . $_TABLE_LIST['dexocard'] . ".`card_attacks`.`card_attacks_cardid` = `card_id` AND
                        " . $_TABLE_LIST['dexocard'] . ".`card_attacks`.`card_attacks_lang` = 'EN'
                  ) AS `card_attacks_EN`,
            
                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'typeid',   " . $_TABLE_LIST['dexocard'] . ".`card_types`.`card_types_typeid`
                     ))
                     FROM
                        " . $_TABLE_LIST['dexocard'] . ".`card_types`
                     WHERE 
                        " . $_TABLE_LIST['dexocard'] . ".`card_types`.`card_types_cardid` = `card_id`
                  ) AS `card_types`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'typeid',   " . $_TABLE_LIST['dexocard'] . ".`card_weaknesses`.`card_weaknesses_typeid`,
                        'value',    " . $_TABLE_LIST['dexocard'] . ".`card_weaknesses`.`card_weaknesses_value`
                     ))
                     FROM
                        " . $_TABLE_LIST['dexocard'] . ".`card_weaknesses`
                     WHERE
                        " . $_TABLE_LIST['dexocard'] . ".`card_weaknesses`.`card_weaknesses_cardid` = `card_id`
                  ) AS `card_weaknesses`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'dexId',    " . $_TABLE_LIST['dexocard'] . ".`card_nationalDexId`.`card_nationalDexId_DexId`
                     ))
                     FROM
                        " . $_TABLE_LIST['dexocard'] . ".`card_nationalDexId`
                     WHERE
                        " . $_TABLE_LIST['dexocard'] . ".`card_nationalDexId`.`card_nationalDexId_cardid` = `card_id`
                  ) AS `card_nationalDexId`,

                  (
                     SELECT JSON_ARRAYAGG(JSON_OBJECT
                     (
                        'highFR',         " . $_TABLE_LIST['dexocard'] . ".`card_images`.`card_images_imagesHighFR`,
                        'highEN',         " . $_TABLE_LIST['dexocard'] . ".`card_images`.`card_images_imagesHighEN`,
                        'smallFR',        " . $_TABLE_LIST['dexocard'] . ".`card_images`.`card_images_imagesSmallFR`,
                        'smallEN',        " . $_TABLE_LIST['dexocard'] . ".`card_images`.`card_images_imagesSmallEN`,
                        'hasRelief',      " . $_TABLE_LIST['dexocard'] . ".`card_holo`.`card_holo_HasRelief`,
                        'lastUpdate',     " . $_TABLE_LIST['dexocard'] . ".`card_images`.`card_images_LastUpdate`
                     ))
                     FROM
                        " . $_TABLE_LIST['dexocard'] . ".`card_images`
                     WHERE
                        " . $_TABLE_LIST['dexocard'] . ".`card_images`.`card_images_cardid` = `card_id`
                  ) AS `card_image`,

                  JSON_OBJECT
                  (
                     'normal',      " . $_TABLE_LIST['dexocard'] . ".`card_holo`.`card_holo_NormalExist`,
                     'holo',        IF(" . $_TABLE_LIST['dexocard'] . ".`card_holo`.`card_holo_FullExist` IS NOT NULL, 1, " . $_TABLE_LIST['dexocard'] . ".`card_holo`.`card_holo_HoloExist`),
                     'reverse',     " . $_TABLE_LIST['dexocard'] . ".`card_holo`.`card_holo_ReverseExist`
                  ) AS `card_variant`,

                  (
                     SELECT (JSON_OBJECT
                     (
                        'count', count(*),
                        'avg', CAST(avg(" . $_TABLE_LIST['dexocard'] . ".`card_price_ebay`.`card_prices_Price`) AS DECIMAL(10,2)), 
                        'min', CAST(min(" . $_TABLE_LIST['dexocard'] . ".`card_price_ebay`.`card_prices_Price`) AS DECIMAL(10,2)), 
                        'max', CAST(max(" . $_TABLE_LIST['dexocard'] . ".`card_price_ebay`.`card_prices_Price`) AS DECIMAL(10,2)))) 

                        FROM 
                           " . $_TABLE_LIST['dexocard'] . ".`card_price_ebay`
                        WHERE
                           " . $_TABLE_LIST['dexocard'] . ".`card_price_ebay`.`card_prices_CardId` = `card_id` AND 
                           " . $_TABLE_LIST['dexocard'] . ".`card_price_ebay`.`card_prices_Sold` = 1 AND
                           DATE_ADD(" . $_TABLE_LIST['dexocard'] . ".`card_price_ebay`.`card_prices_DateLastSeen`, INTERVAL 28 DAY) >= NOW()
                  ) AS `price_stats_sold_ebay_28`,

                  (
                     SELECT (JSON_OBJECT
                     (
                        'count', count(*),
                        'avg', CAST(avg(" . $_TABLE_LIST['dexocard'] . ".`card_price_ebay`.`card_prices_Price`) AS DECIMAL(10,2)), 
                        'min', CAST(min(" . $_TABLE_LIST['dexocard'] . ".`card_price_ebay`.`card_prices_Price`) AS DECIMAL(10,2)), 
                        'max', CAST(max(" . $_TABLE_LIST['dexocard'] . ".`card_price_ebay`.`card_prices_Price`) AS DECIMAL(10,2)))) 

                        FROM 
                           " . $_TABLE_LIST['dexocard'] . ".`card_price_ebay`
                        WHERE
                           " . $_TABLE_LIST['dexocard'] . ".`card_price_ebay`.`card_prices_CardId` = `card_id` AND 
                           " . $_TABLE_LIST['dexocard'] . ".`card_price_ebay`.`card_prices_Sold` = 1 AND
                           DATE_ADD(" . $_TABLE_LIST['dexocard'] . ".`card_price_ebay`.`card_prices_DateLastSeen`, INTERVAL 90 DAY) >= NOW()
                  ) AS `price_stats_sold_ebay_90`
               ";

            // Formatage des données envoyées
               $results_print = array();
               $_SQL    = $_MYSQL->connect(array("dexocard"));
               foreach ($_SQL['dexocard']->query
               (
                  getQuery_Cards($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, "LIMIT " . $_OFFSET . ", " . $_LIMIT), 
                  $_ASSOCS_VARS
               )->fetchAll(PDO::FETCH_ASSOC) as $thisCard)
               {
                  $card_alt_arr  = array();
                  $card_serie    = (!empty($thisCard['card_serie'])  ? json_decode($thisCard['card_serie']) : null);
                  $card_set      = (!empty($thisCard['card_set'])    ? json_decode($thisCard['card_set'])   : null);
                  $card_name     = (!empty($thisCard['card_name'])   ? json_decode($thisCard['card_name'])  : null);
                  $card_alt      = (!empty($thisCard['card_alt'])    ? json_decode($thisCard['card_alt'])   : null);

                  /* other filters */
                  /* end other filters */

                  if (!empty($card_alt[0]->id1)) { array_push($card_alt_arr, $card_alt[0]->id1); }
                  if (!empty($card_alt[0]->id2)) { array_push($card_alt_arr, $card_alt[0]->id2); }
                  if (!empty($card_alt[0]->id3)) { array_push($card_alt_arr, $card_alt[0]->id3); }

                  $link_v1 = 'https://www.dexocard.com/cards/' . cleanURL(empty($card_set[0]->fr) ? $card_set[0]->en : $card_set[0]->fr) . '/' . cleanURL(empty($card_name[0]->fr) ? $card_name[0]->en : $card_name[0]->fr) . '/' . $thisCard['card_id'];

                  array_push($results_print, array
                  (
                     'id'                 => $thisCard['card_id'],
                     'serie_id'           => $thisCard['card_serieid'],
                     'serie_name'         => array
                     (
                        'fr' => (empty($card_serie[0]->fr) ? NULL : $card_serie[0]->fr),
                        'en' => (empty($card_serie[0]->en) ? NULL : $card_serie[0]->en),
                     ),
                     'set_id'             => $thisCard['card_setid'],
                     'set_name'           => array
                     (
                        'fr' => (empty($card_set[0]->fr) ? NULL : $card_set[0]->fr),
                        'en' => (empty($card_set[0]->en) ? NULL : $card_set[0]->en),
                     ),
                     'set_symbol'         => (empty($thisCard['card_set_images']) ? NULL : json_decode($thisCard['card_set_images'], true)[0]['symbol']),
                     'ed1'                => $card_set[0]->is_ed1,
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

                     'name'               =>array
                     (
                        'fr' => (empty($card_name[0]->fr) ? NULL : $card_name[0]->fr),
                        'en' => (empty($card_name[0]->en) ? NULL : $card_name[0]->en),
                     ),

                     'artist'             => array
                     (
                        array($thisCard['card_artist'])
                     ),

                     'pokemon'            => (empty($thisCard['card_nationalDexId']) ? NULL : json_decode($thisCard['card_nationalDexId'], true)),

                     'flavor_text'        => (empty($thisCard['card_flavorText']) ? NULL : json_decode($thisCard['card_flavorText'], true)[0]),

                     'abilities'          => array
                     (
                        'fr' => (empty($thisCard['card_abilities_FR']) ? NULL : json_decode($thisCard['card_abilities_FR'], true)),
                        'en' => (empty($thisCard['card_abilities_EN']) ? NULL : json_decode($thisCard['card_abilities_EN'], true)),
                     ),

                     'capacities'         => array
                     (
                        'fr' => (empty($thisCard['card_attacks_FR']) ? NULL : json_decode($thisCard['card_attacks_FR'], true)),
                        'en' => (empty($thisCard['card_attacks_EN']) ? NULL : json_decode($thisCard['card_attacks_EN'], true)),
                     ),

                     'propriety'          => (empty($thisCard['card_property']) ? NULL : json_decode($thisCard['card_property'], true)[0]),

                     'retreatCost'        => $thisCard['card_convertedRetreatCost'],
                     'weaknesses'         => (empty($thisCard['card_weaknesses']) ? NULL : json_decode($thisCard['card_weaknesses'], true)[0]),

                     'image'              => (empty($thisCard['card_image']) ? NULL : json_decode($thisCard['card_image'], true)[0]),

                     'alternative'        => $card_alt_arr,

                     'link'               => array
                     (
                        'v1'                 => ($link_v1),
                     ),

                     'price_stats'        => array
                     (
                        'ebay'               => array
                        (
                           'sold'               => array
                           (
                              '28d'       => (empty($thisCard['price_stats_sold_ebay_28'])         ? NULL : json_decode($thisCard['price_stats_sold_ebay_28'],          true)),
                              '90d'       => (empty($thisCard['price_stats_sold_ebay_90'])         ? NULL : json_decode($thisCard['price_stats_sold_ebay_90'],          true)),
                           ),
                        ),
                     ),
                  ));
               }

               //usort($results_print, fn($a, $b) => $a['id'] <=> $b['id']);
               array_multisort(array_column($results_print, 'id'), SORT_ASC, SORT_NATURAL, $results_print);
  
            // Envoi des données
               $results_unfiltered = $_SQL['dexocard']->query
               (
                  getQuery_Cards($_FILTERS_ACTIVE, "COUNT(*) AS total_rows_unfiltered", $_BLOC_WHERE, NULL), 
                  $_ASSOCS_VARS
               )->fetch(PDO::FETCH_ASSOC)['total_rows_unfiltered'];

               $_JSON_PRINT->addDataBefore('results_count',          count($results_print)); 
               $_JSON_PRINT->addDataBefore('results_filters_count',  $results_unfiltered); 
               
               // debug
               //$_SQL['dexocard']->debug()->query(getQuery_Cards($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, "LIMIT " . $_OFFSET . ", " . $_LIMIT),$_ASSOCS_VARS); exit();

               $_JSON_PRINT->success(); 
               $_JSON_PRINT->response($results_print); 
               $_JSON_PRINT->print();

            break;
         }

         case 'POST':
         case 'PUT':
         {
            $new_image = false;

            // Check parameters
               if (empty($_PARAM['id']))
               {
                  $_JSON_PRINT->fail("id must be specified");
                  $_JSON_PRINT->print();                  
               }

               if (!empty($_PARAM['image']) && empty($_PARAM['setid']))
               {
                  $_JSON_PRINT->fail("setid must be specified if image is specified");
                  $_JSON_PRINT->print();                  
               }

               if (!empty($_PARAM['image']) && empty($_PARAM['serieid']))
               {
                  $_JSON_PRINT->fail("serieid must be specified if image is specified");
                  $_JSON_PRINT->print();                  
               }

               if (!empty($_PARAM['text']) && !is_array(json_decode($_PARAM['text'], true)))
               {
                  $_JSON_PRINT->fail("text must be json string (not parsed)");
                  $_JSON_PRINT->print();                  
               }

               if (!empty($_PARAM['weakness']) && !is_array(json_decode($_PARAM['weakness'], true)))
               {
                  $_JSON_PRINT->fail("weakness must be json string (not parsed)");
                  $_JSON_PRINT->print();                  
               }

            // MySQL Connect
               $_SQL          = $_MYSQL->connect(array("dexocard"));
         
            // Create some tables
               $_SQL['dexocard']->query("INSERT IGNORE INTO `card`            (`card_id`, card_number, card_index, card_serieid, card_setid)       VALUES(:id, '', 0, 0, '');", [":id" => $_PARAM['id']]);
               $_SQL['dexocard']->query("INSERT IGNORE INTO `card_name`       (`card_name_cardid`)                                                 VALUES(:id);", [":id" => $_PARAM['id']]);
               $_SQL['dexocard']->query("INSERT IGNORE INTO `card_images`     (`card_images_cardid`)                                               VALUES(:id);", [":id" => $_PARAM['id']]);
               $_SQL['dexocard']->query("INSERT IGNORE INTO `card_propriety`  (`card_propriety_cardid`)                                            VALUES(:id);", [":id" => $_PARAM['id']]);

            // Search exist
               $_SQL_PRODUCT  = $_SQL['dexocard']->query(
                  "
                     SELECT
                        *
                     FROM 
                        `tcg`.`card`
                     LEFT JOIN `tcg`.`card_images` ON `card_images_cardid` = `card`.`card_id`
                     WHERE
                        card_id = :card_id
                  ", [":card_id" => $_PARAM['id']])->fetch(PDO::FETCH_ASSOC);
   
                  if (empty($_SQL_PRODUCT['card_id']))
                  {
                     $_JSON_PRINT->fail("id not found");
                     $_JSON_PRINT->print();                                   
                  }

            // Upload Image
               if (!empty($_FILES['card-image']))
               {
                  //print_r($_FILES['card-image']);
                  $dir_Target    = 'v1/tmp/';
                  $file_Target   = 'card-' . rand(111111, 9999999999);

                  $uploadResult = null;
                  $uploadResult = uploadFile_Image($_FILES['card-image'], $_CONFIG['ROOT'], $dir_Target, $file_Target, true);

                  if (!$uploadResult['success'])
                  {
                     $_JSON_PRINT->fail("upload error : " . $uploadResult['raison']);
                     $_JSON_PRINT->print();     
                  }
                  else
                  {
                     $new_image = convert_image_card($_PARAM, file_get_contents($_CONFIG['ROOT'] . $dir_Target . $file_Target . '.webp'));
                  }
               }

            // Download Image
               if (!$new_image && !empty($_PARAM['image']))
               {
                  if 
                  (
                     empty($_SQL_PRODUCT['card_images_imagesHigh' . strtoupper($_PARAM['lang'])]) || 
                     (!file_exists($_CONFIG['PRODUCTS']['DEXOCARD']['ROOT'] . $_SQL_PRODUCT['card_images_imagesHigh' . strtoupper($_PARAM['lang'])]))
                  )
                  {
                     $new_image = convert_image_card($_PARAM, download_image_card($_PARAM['image']));
                  }
               }
                  
            // Enregistrement SQL
               // Images
                  if ($new_image)
                  {
                     $folder = $_CONFIG['PRODUCTS']['DEXOCARD']['ROOT'] . 'img/cards/' . $_PARAM['setid'];

                     $results = $_SQL['dexocard']->update("card_images", 
                     [
                        "card_images_imagesHigh"   . strtoupper($_PARAM['lang'])    => ('img/cards/' . $_PARAM['setid'] . '/' . $_PARAM['lang'] . '_high_'  . md5($_PARAM['id']) . '.webp'),
                        "card_images_imagesSmall"  . strtoupper($_PARAM['lang'])    => ('img/cards/' . $_PARAM['setid'] . '/' . $_PARAM['lang'] . '_small_' . md5($_PARAM['id']) . '.webp'),
                        "card_images_LastUpdate"                                    => date('Y-m-d H:m:s', time()),
                     ],
                     [
                        "card_images_cardid " => $_PARAM['id']
                     ]);
                  }

               // Name
                  if (!empty($_PARAM['name']))
                  {
                     $name_formated    = str_replace('-ex', ' ex', $_PARAM['name']);

                     $update_sql = array();
                     if (!empty($name_formated))           { $update_sql = array_merge($update_sql, ["card_name_name"   . strtoupper($_PARAM['lang'])         => $name_formated]); }

                     if ($update_sql)
                     {
                        $results = $_SQL['dexocard']->update("card_name", $update_sql,
                        [
                           "card_name_cardid" => $_PARAM['id']
                        ]);
                     }
                  }

               // Talents
                  if (!empty($_PARAM['ability']))
                  {

                  }

               // Talents & Attaques en mode texte (généralement importé depuis pokemon.com)
                  if (!empty($_PARAM['text']))
                  {
                     // remove olds abilities and attacks
                        $_SQL['dexocard']->delete("card_attacks",
                        [
                           "AND" =>
                           [
                              "card_attacks_cardid"   => $_PARAM['id'],
                              "card_attacks_lang"     => strtoupper($_PARAM['lang'])
                           ]
                        ]);

                        $_SQL['dexocard']->delete("card_abilities",
                        [
                           "AND" =>
                           [
                              "card_abilities_cardid"   => $_PARAM['id'],
                              "card_abilities_lang"     => strtoupper($_PARAM['lang'])
                           ]
                        ]);

                     // insert new abilites and attacks
                        foreach (json_decode($_PARAM['text'], true) as $itemText)
                        {
                           switch ($itemText['type'])
                           {
                              case 'attack':
                              {
                                 $_SQL['dexocard']->insert("card_attacks",
                                 [
                                    "card_attacks_cardid"               => $_PARAM['id'],
                                    "card_attacks_lang"                 => strtoupper($_PARAM['lang']),
                                    "card_attacks_name"                 => (!empty($itemText['title'])            ? $itemText['title'] : NULL),
                                    "card_attacks_text"                 => (!empty($itemText['text'])             ? $itemText['text'] : NULL),
                                    "card_attacks_convertedEnergyCost"  => (!empty($itemText['energy_cost'])      ? $itemText['energy_cost'] : NULL),
                                    "card_attacks_damage"               => (!empty($itemText['degat'])            ? $itemText['degat'] : NULL),
                                    "card_attacks_costtypeid1"          => (isset($itemText['energy'][0])         ? $itemText['energy'][0] : NULL),
                                    "card_attacks_costtypeid2"          => (isset($itemText['energy'][1])         ? $itemText['energy'][1] : NULL),
                                    "card_attacks_costtypeid3"          => (isset($itemText['energy'][2])         ? $itemText['energy'][2] : NULL),
                                    "card_attacks_costtypeid4"          => (isset($itemText['energy'][3])         ? $itemText['energy'][3] : NULL),
                                    "card_attacks_costtypeid5"          => (isset($itemText['energy'][4])         ? $itemText['energy'][4] : NULL),
                                 ]);

                                 break;
                              }

                              case 'talent':
                              {
                                 $_SQL['dexocard']->insert("card_abilities",
                                 [
                                    "card_abilities_cardid"               => $_PARAM['id'],
                                    "card_abilities_lang"                 => strtoupper($_PARAM['lang']),
                                    "card_abilities_name"                 => (!empty($itemText['title'])            ? $itemText['title'] : NULL),
                                    "card_abilities_text"                 => (!empty($itemText['text'])             ? $itemText['text'] : NULL),
                                 ]);

                                 break;
                              }

                              default:
                              {
                                 $_JSON_PRINT->fail("ability type not found : " . $itemText['type']);
                                 $_JSON_PRINT->print();
                              }
                           }
                        }
                  }      

               // `card`
                  $update_sql = array();

                  if (!empty($_PARAM['rarete']))
                  {
                     $rarety_data = get_rarity_data($_PARAM['rarete']);

                     if (!$rarety_data)
                     {
                        $_JSON_PRINT->fail("rarity string unknow : " . $rarety_data);
                        $_JSON_PRINT->print();      
                     }
                  }

                  if (!empty($_PARAM['number']))            { $update_sql = array_merge($update_sql, ["card_number"              => $_PARAM['number']]); }
                  if (!empty($_PARAM['artist']))            { $update_sql = array_merge($update_sql, ["card_artist"              => $_PARAM['artist']]); }
                  if (!empty($_PARAM['supertype']))         { $update_sql = array_merge($update_sql, ["card_supertype"           => $_PARAM['supertype']]); }
                  if (!empty($_PARAM['rarete']))            { $update_sql = array_merge($update_sql, ["card_rarity"              => $_PARAM['rarete']]); }
                  if (!empty($_PARAM['rarete']))            { $update_sql = array_merge($update_sql, ["card_rarityIndex"         => $rarety_data['rarityIndex']]); }
                  if (!empty($_PARAM['rarete']))            { $update_sql = array_merge($update_sql, ["card_raritySimplified"    => $rarety_data['raritySimplified']]); }
                  if (!empty($_PARAM['hp']))                { $update_sql = array_merge($update_sql, ["card_hp"                  => $_PARAM['hp']]); }
                  if (!empty($_PARAM['index']))             { $update_sql = array_merge($update_sql, ["card_index"               => $_PARAM['index']]); }
                  if (!empty($_PARAM['level']))             { $update_sql = array_merge($update_sql, ["card_level"               => $_PARAM['level']]); }
                  if (!empty($_PARAM['setid']))             { $update_sql = array_merge($update_sql, ["card_setid"               => $_PARAM['setid']]); }
                  if (!empty($_PARAM['serieid']))           { $update_sql = array_merge($update_sql, ["card_serieid"             => $_PARAM['serieid']]); }

                  if ($update_sql)
                  {
                     $results = $_SQL['dexocard']->update("card", $update_sql,
                     [
                        "card_id" => $_PARAM['id']
                     ]);
                  }

               // `card_alt`
                  $update_sql = array();

                  if (!empty($_PARAM['alts']))              { $update_sql = array_merge($update_sql, ["card_alt_cardid1"               => json_decode($_PARAM['alts'], true)[0]]); }
                  if (!empty($_PARAM['alts']))              { $update_sql = array_merge($update_sql, ["card_alt_cardid2"               => json_decode($_PARAM['alts'], true)[1]]); }
                  if (!empty($_PARAM['alts']))              { $update_sql = array_merge($update_sql, ["card_alt_cardid3"               => json_decode($_PARAM['alts'], true)[2]]); }

                  if ($update_sql)
                  {
                     $_SQL['dexocard']->query("INSERT IGNORE INTO `card_alt`        (`card_alt_cardid`)                                                  VALUES(:id);", [":id" => $_PARAM['id']]);
                     $results = $_SQL['dexocard']->update("card_alt", $update_sql,
                     [
                        "card_alt_cardid" => $_PARAM['id']
                     ]);
                  }

               // Card Energy Type
                  if (!empty($_PARAM['type1']))
                  {
                     $_SQL['dexocard']->delete("card_types",
                     [
                        "AND" =>
                        [
                           "card_types_cardid"     => strtoupper($_PARAM['id'])
                        ]
                     ]);

                     if ($_PARAM['type1'])
                     {
                        $_SQL['dexocard']->insert("card_types",
                        [
                           "card_types_cardid"                 => $_PARAM['id'],
                           "card_types_typeid"                 => $_PARAM['type1'],
                        ]);
                     }

                     if ($_PARAM['type2'])
                     {
                        $_SQL['dexocard']->insert("card_types",
                        [
                           "card_types_cardid"                 => $_PARAM['id'],
                           "card_types_typeid"                 => $_PARAM['type2'],
                        ]);
                     }
                  }

               // Card Weakness
                  if (!empty($_PARAM['weakness']))
                  {
                     $_PARAM['weakness'] = json_decode($_PARAM['weakness'], true);

                     if (isset($_PARAM['weakness']['type']) && isset($_PARAM['weakness']['multiple']))
                     {
                        $_SQL['dexocard']->delete("card_weaknesses",
                        [
                           "AND" =>
                           [
                              "card_weaknesses_cardid"            => strtoupper($_PARAM['id'])
                           ]
                        ]);

                        $_SQL['dexocard']->insert("card_weaknesses",
                        [
                           "card_weaknesses_cardid"               => $_PARAM['id'],
                           "card_weaknesses_typeid"               => $_PARAM['weakness']['type'],
                           "card_weaknesses_value"                => $_PARAM['weakness']['multiple'],
                        ]);
                     }
                  }

            // Print Results
               $_JSON_PRINT->success(); 
               $_JSON_PRINT->response();
               $_JSON_PRINT->print();

            break;
         }
      }

      function getQuery_Cards($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, $_BLOC_LIMIT = NULL)
      {
         global $_TABLE_LIST;

         // Assemblage requête SQL
            return "
               SELECT 

               " . $_BLOC_SELECT . "

               FROM " . $_TABLE_LIST['dexocard'] . ".`card`

               LEFT JOIN `card_holo`      ON `card_holo` .`card_holo_cardid`           = `card_id`
               LEFT JOIN `card_name`      ON `card_name` .`card_name_cardid`           = `card_id`
               LEFT JOIN `card_set`       ON `card_set`  .`card_set_id`                = `card_setid`

               " . ($_BLOC_WHERE ? "WHERE " . substr($_BLOC_WHERE, 0, strlen($_BLOC_WHERE) - 4) : '') . "

               ORDER BY
                  " . $_TABLE_LIST['dexocard'] . ".`card`.`card_setid` DESC, 
                  " . $_TABLE_LIST['dexocard'] . ".`card`.`card_index` DESC

               " . ($_BLOC_LIMIT ? $_BLOC_LIMIT : '') . "
               ;
            ";
      }

      function download_image_card($url)
      {
         global $_JSON_PRINT;

         // Download image
            $host = $url;
            $ch   = curl_init();
            curl_setopt($ch, CURLOPT_URL, $host);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_AUTOREFERER, false);
            curl_setopt($ch, CURLOPT_REFERER, $host);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $result = curl_exec($ch);
            curl_close($ch);
      
            if (curl_errno($ch))
            {
               $error_msg = curl_error($ch);
               $_JSON_PRINT->fail();
               $_JSON_PRINT->print();    
            }

            return $result;
            //$finfo = new finfo(FILEINFO_MIME_TYPE);
            //$ext = $finfo->buffer($result);
      }
   
      function convert_image_card($_PARAM, $buffer)
      {
         global $_CONFIG;
         global $_JSON_PRINT;

         // Open buffer
            $img_downloaded = imagecreatefromstring($buffer);

         // Images infos
            $origWidth = imagesx($img_downloaded);
            $origHeight = imagesy($img_downloaded);

         // Create canvas
            $img_hd = imagecreatetruecolor ($origWidth, $origHeight);
            imageAlphaBlending($img_hd, false);
            imageSaveAlpha($img_hd, true);
            $trans = imagecolorallocatealpha($img_hd, 0, 0, 0, 127);
            imagefilledrectangle($img_hd, 0, 0, $origWidth - 1, $origHeight - 1, $trans);
            
         // copy image result to canvas
            imagecopy($img_hd, $img_downloaded, 0, 0, 0, 0, $origWidth, $origHeight);

         // folder destination  
            $folder = $_CONFIG['PRODUCTS']['DEXOCARD']['ROOT'] . 'img/cards/' . $_PARAM['setid'];

         // create folder destination if not exist
            if (!is_dir($folder))
            {
               if (!mkdir($folder))
               {
                  $_JSON_PRINT->fail("unable to create folder : " . $folder);
                  $_JSON_PRINT->print();   
               }
            }

         // resize HD to SD
            $newWidth   = 286;
            $newHeight  = 400;

            $img_sd = @imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($img_sd, $img_hd, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
            imagesavealpha($img_sd, true);
            $trans_colour = imagecolorallocatealpha($img_sd, 0, 0, 0, 127);
            imagefill($img_sd, 0, 0, $trans_colour);

         // Convert to WebP => HD / SD
            imagewebp($img_hd, $folder . '/' . $_PARAM['lang'] . '_high_' . md5($_PARAM['id']) . '.webp');
            imagewebp($img_sd, $folder . '/' . $_PARAM['lang'] . '_small_' . md5($_PARAM['id']) . '.webp');

         return true;
      }
?>