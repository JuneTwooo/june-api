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
               if (empty($_GET['offset'])) 		{ $_OFFSET = 0; }       else { $_OFFSET = intval($_GET['offset']); }
               if (empty($_GET['limit']))  		{ $_LIMIT  = 10; }      else { $_LIMIT  = intval($_GET['limit']); }

            // Bloc Where
               if (!empty($_GET['giveawayid']))
               {
                  $_BLOC_WHERE      = $_BLOC_WHERE . " `dgtu_giveawayid` = '" . addslashes($_GET['giveawayid']) . "' AND";
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
                  getQuery_GET_1($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, "LIMIT " . $_OFFSET . ", " . $_LIMIT), 
                  $_ASSOCS_VARS
               )->fetchAll(PDO::FETCH_ASSOC) as $thisSQL)
               {
                  array_push($results_print, array
                  (
                     'id'                       => $thisSQL['dgtu_id'],
                     'giveawayid'               => $thisSQL['dgtu_giveawayid'],
                     'userid'                   => $thisSQL['dgtu_userid'],
                     'username'                 => $thisSQL['dgtu_username'],
                     'datetime'                 => $thisSQL['dgtu_datetime'],
                  ));
               }

            // Envoi des données
               $results_unfiltered = $_SQL['dexocard']->query
               (
                  getQuery_GET_1($_FILTERS_ACTIVE, "COUNT(*) AS total_rows_unfiltered", $_BLOC_WHERE, NULL), 
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
                  if (empty($_PARAM['giveawayid']))   { $_JSON_PRINT->fail("giveawayid must be specified"); $_JSON_PRINT->print(); }
                  if (empty($_PARAM['userid']))       { $_JSON_PRINT->fail("userid must be specified");     $_JSON_PRINT->print(); }

                  $_SQL_ITEM  = $_SQL['dexocard']->query("SELECT * FROM `" . $_TABLE_LIST['dexocard'] . "`.`discord_giveaway_tcgo_user` WHERE `dgtu_giveawayid` = :giveawayid AND `dgtu_userid` = :userid", [":giveawayid" => $_PARAM['giveawayid'], ":userid" => $_PARAM['userid']])->fetch(PDO::FETCH_ASSOC);
                  if ($_SQL_ITEM)
                  {
                     $_JSON_PRINT->fail("userid already exist for this giveawayid");
                     $_JSON_PRINT->print();      
                  }
   
                  $_SQL['dexocard']->insert("discord_giveaway_tcgo_user", []);
                  $_PARAM['id'] = $_SQL['dexocard']->id();
               }
         
            // Search exist
               if (empty($_SQL_ITEM))
               {
                  $_SQL_ITEM  = $_SQL['dexocard']->query("SELECT * FROM `" . $_TABLE_LIST['dexocard'] . "`.`discord_giveaway_tcgo_user` WHERE `dgtu_id` = :id", [":id" => $_PARAM['id']])->fetch(PDO::FETCH_ASSOC);
               
                  if (empty($_SQL_ITEM))
                  {
                     $_JSON_PRINT->fail("giveaway not found");
                     $_JSON_PRINT->print();  
                  }
               }

            // Enregistrement SQL
               $update_sql = array();

               $update_sql = array_merge($update_sql, ["dgtu_giveawayid"               => (!empty($_PARAM['giveawayid'])   ? $_PARAM['giveawayid'] : NULL)]);
               $update_sql = array_merge($update_sql, ["dgtu_userid"                   => (!empty($_PARAM['userid'])       ? $_PARAM['userid']     : NULL)]);
               $update_sql = array_merge($update_sql, ["dgtu_username"                 => (!empty($_PARAM['username'])     ? $_PARAM['username']   : NULL)]);

               if ($update_sql)
               {
                  $results = $_SQL['dexocard']->update("discord_giveaway_tcgo_user", $update_sql,
                  [
                     "dgtu_id" => $_PARAM['id']
                  ]);
               }

            // Formatage des données envoyées
               $results_print = array();

               array_push($results_print, array
               (
                  'id'                 => $_SQL_ITEM['dgtu_id'],
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
      function getQuery_GET_1($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, $_BLOC_LIMIT = NULL)
      {
         global $_TABLE_LIST;

         //$_BLOC_WHERE      = $_BLOC_WHERE . " `card_set_show` = 1 AND ";

         // Assemblage requête SQL
            return "
               SELECT 

               " . $_BLOC_SELECT . "

               FROM " . $_TABLE_LIST['dexocard'] . ".`discord_giveaway_tcgo_user`
               
               " . ($_BLOC_WHERE ? "WHERE " . substr($_BLOC_WHERE, 0, strlen($_BLOC_WHERE) - 4) : '') . "

               ORDER BY 
                  " . $_TABLE_LIST['dexocard'] . ".`discord_giveaway_tcgo_user`.`dgtu_id` ASC

               " . ($_BLOC_LIMIT ? $_BLOC_LIMIT : '') . "
               ;
            ";
      }
?>