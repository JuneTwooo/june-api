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
            if (!empty($_GET['limit']))   { $_LIMIT   = intval($_GET['limit']);  }                                      else { $_LIMIT  = 10; }
            if (!empty($_GET['offset']))  { $_OFFSET  = intval($_GET['offset']); }                                      else { $_OFFSET = 0; }
            if (!empty($_GET['operand']))    { $_OPERAND    = (strtolower($_GET['operand']) == 'or' ? "OR" : "AND"); }  else { $_OPERAND = "AND"; }
            
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

                  `card_name_nameFR`,
                  `card_name_nameEN`,

                  `card_flavorText_flavorTextFR`,
                  `card_flavorText_flavorTextEN`,

                  JSON_OBJECT
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
                  ) AS `card_property`,

                  JSON_OBJECT
                  (
                     'type1_id', `card_retreatCost_type1`,
                     'type2_id', `card_retreatCost_type2`,
                     'type3_id', `card_retreatCost_type3`,
                     'type4_id', `card_retreatCost_type4`,
                     'type5_id', `card_retreatCost_type5`
                  ) AS `card_retreatCost`,

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

                  JSON_OBJECT
                  (
                     'highFR',      `card_images_imagesHighFR`,
                     'highEN',      `card_images_imagesHighEN`,
                     'smallFR',     `card_images_imagesSmallFR`,
                     'smallEN',     `card_images_imagesSmallEN`,
                     'hasRelief ',  `card_holo_HasRelief`,
                     'lastUpdate',  `card_images_LastUpdate`
                  ) AS `card_image`,

                  JSON_OBJECT
                  (
                     'normal',      `card_holo_NormalExist`,
                     'holo',        IF(`card_holo_FullExist` IS NOT NULL, 1, `card_holo_HoloExist` ),
                     'reverse',     `card_holo_ReverseExist`
                  ) AS `card_variant`
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

                     'name'               => array
                     (
                        'FR' => (empty($thisCard['card_name_nameFR']) ? NULL : $thisCard['card_name_nameFR']), 
                        'EN' => (empty($thisCard['card_name_nameEN']) ? NULL : $thisCard['card_name_nameEN']), 
                     ),

                     'artist'             => array
                     (
                        array($thisCard['card_artist'])
                     ),

                     'pokemon'            => (empty($thisCard['card_nationalDexId']) ? NULL : json_decode($thisCard['card_nationalDexId'], true)),

                     'flavor_text'        => array
                     (
                        'FR' => (empty($thisCard['card_flavorText_flavorTextFR']) ? NULL : $thisCard['card_flavorText_flavorTextFR']), 
                        'EN' => (empty($thisCard['card_flavorText_flavorTextEN']) ? NULL : $thisCard['card_flavorText_flavorTextEN']), 
                     ),

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

                     'retreatCost'        => (empty($thisCard['card_retreatCost']) ? NULL : json_decode($thisCard['card_retreatCost'], true)),
                     'weaknesses'         => (empty($thisCard['card_weaknesses']) ? NULL : json_decode($thisCard['card_weaknesses'], true)),

                     'image'              => (empty($thisCard['card_image']) ? NULL : json_decode($thisCard['card_image'], true)),

                  ));
               }
  
            // Envoi des données
               $results_unfiltered = $_SQL['api']->query
               (
                  getQuery_Cards($_FILTERS_ACTIVE, "COUNT(*) AS total_rows_unfiltered", $_BLOC_WHERE, NULL), 
                  $_ASSOCS_VARS
               )->fetch(PDO::FETCH_ASSOC)['total_rows_unfiltered'];

               $_JSON_PRINT->addDataBefore('results_count',          count($results_print)); 
               $_JSON_PRINT->addDataBefore('results_filters_count',  $results_unfiltered); 
               
               // debug
               //$_SQL['api']->debug()->query(getQuery_Cards($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, "LIMIT " . $_OFFSET . ", " . $_LIMIT),$_ASSOCS_VARS);

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

               LEFT JOIN `card_name`            ON `card_name_cardid`            = `card_id`
               LEFT JOIN `card_flavorText`      ON `card_flavorText_cardid`      = `card_id`
               LEFT JOIN `card_propriety`       ON `card_propriety_cardid`       = `card_id`
               LEFT JOIN `card_retreatCost`     ON `card_retreatCost_cardid`     = `card_id`
               LEFT JOIN `card_images`          ON `card_images_cardid`          = `card_id`
               LEFT JOIN `card_holo`            ON `card_holo_cardid`            = `card_id`

               " . (in_array("pokemonid",       $_FILTERS_ACTIVE) ? "LEFT JOIN `card_nationalDexId`   ON `card_nationalDexId_cardid`      = `card_id`" : '') . "
               " . (in_array("typeid",          $_FILTERS_ACTIVE) ? "LEFT JOIN `card_types`           ON `card_types_cardid`              = `card_id`" : '') . "
               " . (in_array("ability",         $_FILTERS_ACTIVE) ? "LEFT JOIN `card_abilities`       ON `card_abilities_cardid`          = `card_id`" : '') . "
               " . (in_array("capacity",        $_FILTERS_ACTIVE) ? "LEFT JOIN `card_attacks`         ON `card_attacks_cardid`            = `card_id`" : '') . "

               " . ($_BLOC_WHERE ? "WHERE " . substr($_BLOC_WHERE, 0, strlen($_BLOC_WHERE) - 4) : '') . "

               " . ($_BLOC_LIMIT ? $_BLOC_LIMIT : '') . "
               ;
            ";
      }
?>