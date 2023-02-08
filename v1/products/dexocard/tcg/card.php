<?php
   // check Token
      $_TOKEN->checkAccess('dexocard', 'tcg/card');

      switch (strtoupper($_METHOD))
      {
         case 'GET':
         {
            // WHERE
               $_ASSOCS_VARS  = array();
               $_BLOC_WHERE   = '';
               foreach ($_GET as $param => $value)
               {
                  switch (strtolower($param))
                  {
                     case 'length':
                     {
                        $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":length" => intval($value)]);

                        break;
                     }

                     case 'serieid':
                     case 'setid':
                     case 'cardid':
                     case 'rarity':
                     case 'raritysimplified':
                     case 'supertype':
                     {
                        $_BLOC_WHERE      = $_BLOC_WHERE . " `card_$param` LIKE :$param AND";
                        $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":" . $param => $value]);

                        break;
                     }

                     case 'level':
                     case 'hp':
                     {
                        $_BLOC_WHERE      = $_BLOC_WHERE . " `card_$param` " . substr($value, 0, 1) . " :$param AND";
                        $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":" . $param => intval(substr($value, 1, 9999))]);

                        break;
                     }
                  }
               }

            // DEFAULTS VARS
               if (empty($_ASSOCS_VARS[':length'])) { $_ASSOCS_VARS[':length'] = 1; }

            // formatage des données envoyées
               $QUERY = "
                  SELECT 

                     `card_id`,
                     `card_number`,
                     `card_index`,
                     `card_serieid`,
                     `card_setid`,
                     `card_level`,
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

                  FROM `card`

                  LEFT JOIN `card_name`            ON `card_name_cardid`         = `card_id`
                  LEFT JOIN `card_flavorText`      ON `card_flavorText_cardid`   = `card_id`
                  LEFT JOIN `card_propriety`       ON `card_propriety_cardid`    = `card_id`
                  LEFT JOIN `card_retreatCost`     ON `card_retreatCost_cardid`  = `card_id`
                  LEFT JOIN `card_images`          ON `card_images_cardid`       = `card_id`
                  LEFT JOIN `card_holo`            ON `card_holo_cardid`         = `card_id`

                  " . ($_BLOC_WHERE ? "WHERE " . substr($_BLOC_WHERE, 0, strlen($_BLOC_WHERE) - 4) : '') . "

                  LIMIT 0, :length
                  ;
               ";

               $response = array();
               $_SQL    = $_MYSQL->connect(array("api"));
               foreach ($_SQL['api']->debug()->query
               (
                  $QUERY, 
                  $_ASSOCS_VARS
               )->fetchAll(PDO::FETCH_ASSOC) as $thisCard)
               {
                  array_push($response, array
                  (
                     'id'                 => $thisCard['card_id'],
                     'serie_id'           => $thisCard['card_serieid'],
                     'set_id'             => $thisCard['card_setid'],
                     'number'             => $thisCard['card_number'],
                     'index'              => $thisCard['card_index'],
                     'level'              => $thisCard['card_level'],
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

                     'type'               => array
                     (
                        'FR' => (empty($thisCard['card_subtypes_nameFR']) ? NULL : json_decode($thisCard['card_subtypes_nameFR'], true)),
                        'EN' => (empty($thisCard['card_subtypes_nameEN']) ? NULL : json_decode($thisCard['card_subtypes_nameEN'], true)),
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

               $_JSON_PRINT->success(); 
               $_JSON_PRINT->response($response); 
               $_JSON_PRINT->print();

            break;
         }
      }
?>