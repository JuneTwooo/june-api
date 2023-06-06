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
               if (empty($_GET['offset'])) 		{ $_OFFSET = 0; }    else { $_OFFSET = intval($_GET['offset']); }
               if (empty($_GET['limit']))  		{ $_LIMIT  = 1; }    else { $_LIMIT  = intval($_GET['limit']); }

            // Bloc Where
               if (!empty($_GET['winnerid']))
               {
                  if ($_GET['winnerid'] == 'null')
                  {
                     $_BLOC_WHERE      = $_BLOC_WHERE . " `dgt_winner_userid` IS NULL AND";
                  }
                  else
                  {
                     $_BLOC_WHERE      = $_BLOC_WHERE . " `dgt_winner_userid` = '" . addslashes($_GET['giveawayid']) . "' AND";
                  }
               }

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
               )->fetchAll(PDO::FETCH_ASSOC) as $thisSQL)
               {
                  array_push($results_print, array
                  (
                     'id'                 => $thisSQL['dgt_id'],
                     'channelid'          => $thisSQL['dgt_channelid'],
                     'messageid'          => $thisSQL['dgt_messageid'],
                     'date_start'         => $thisSQL['dgt_datetime_start'],
                     'winner_userid'      => $thisSQL['dgt_winner_userid'],
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
            // MySQL Connect
               $_SQL          = $_MYSQL->connect(array("dexocard"));

            // Check parameters
               if (strtoupper($_METHOD) == 'PUT' && empty($_PARAM['id']))
               {
                  $_JSON_PRINT->fail("id must be specified");
                  $_JSON_PRINT->print();                  
               }

               if (strtoupper($_METHOD) == 'POST' && empty($_PARAM['id']))
               {
                  if (empty($_PARAM['channelid']))   { $_JSON_PRINT->fail("channelid must be specified"); $_JSON_PRINT->print(); }
                  if (empty($_PARAM['messageid']))   { $_JSON_PRINT->fail("messageid must be specified"); $_JSON_PRINT->print(); }

                  $_SQL['dexocard']->insert("discord_giveaway_tcgo", []);
                  $_PARAM['id'] = $_SQL['dexocard']->id();
               }
         
            // Search exist
               $_SQL_ITEM  = $_SQL['dexocard']->query("SELECT * FROM `discord_giveaway_tcgo` WHERE `dgt_id` = :id", [":id" => $_PARAM['id']])->fetch(PDO::FETCH_ASSOC);

            // Enregistrement SQL
               $update_cols = array();

               if (isset($_PARAM['channelid']))             { $update_cols = array_merge($update_cols, ["dgt_channelid"        => $_PARAM['channelid']]); }
               if (isset($_PARAM['messageid']))             { $update_cols = array_merge($update_cols, ["dgt_messageid"        => $_PARAM['messageid']]); }
               if (isset($_PARAM['winner_userid']))         { $update_cols = array_merge($update_cols, ["dgt_winner_userid"    => $_PARAM['winner_userid']]); }

               if (!$update_cols)
               {
                  $_JSON_PRINT->fail("no data to update"); $_JSON_PRINT->print();
               }
               else
               {
                  $results = $_SQL['dexocard']->update("discord_giveaway_tcgo", $update_cols,
                  [
                     "dgt_id" => $_PARAM['id']
                  ]);
               }

            // Formatage des données envoyées
               $results_print = array();

               $_SQL_ITEM  = $_SQL['dexocard']->query("SELECT * FROM `discord_giveaway_tcgo` WHERE `dgt_id` = :id", [":id" => $_PARAM['id']])->fetch(PDO::FETCH_ASSOC);
               array_push($results_print, array
               (
                  'id'                 => $_SQL_ITEM['dgt_id'],
                  'channelid'          => $_SQL_ITEM['dgt_channelid'],
                  'messageid'          => $_SQL_ITEM['dgt_messageid'],
                  'date_start'         => $_SQL_ITEM['dgt_datetime_start'],
                  'winner_userid'      => $_SQL_ITEM['dgt_winner_userid'],
               ));

            // Print Results
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

         //$_BLOC_WHERE      = $_BLOC_WHERE . " `card_set_show` = 1 AND ";

         // Assemblage requête SQL
            return "
               SELECT 

               " . $_BLOC_SELECT . "

               FROM `discord_giveaway_tcgo`

               LEFT JOIN `discord_giveaway_tcgo_user` ON `discord_giveaway_tcgo`.`dgt_winner_userid` = `discord_giveaway_tcgo_user`.`dgtu_userid`
               
               " . ($_BLOC_WHERE ? "WHERE " . substr($_BLOC_WHERE, 0, strlen($_BLOC_WHERE) - 4) : '') . "

               ORDER BY 
                  `discord_giveaway_tcgo`.`dgt_id` DESC

               " . ($_BLOC_LIMIT ? $_BLOC_LIMIT : '') . "
               ;
            ";
      }
?>