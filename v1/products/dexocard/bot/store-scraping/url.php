<?php
   // check Token
      $_TOKEN->checkAccess('dexocard', 'store-scraping/url');

      use Medoo\Medoo;

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
               $_OFFSET = (empty($_PARAM['offset']) ? 0 : intval($_PARAM['offset']));
               $_LIMIT  = (empty($_PARAM['limit']) ? 1 : intval($_PARAM['limit']));
               if (!empty($_PARAM['storeid']))
               {
                  $_BLOC_WHERE      = $_BLOC_WHERE . " `store_url_storeid` = " . intval($_PARAM['storeid']) . " AND";
               }
               if (!empty($_PARAM['id']))
               {
                  $_BLOC_WHERE      = $_BLOC_WHERE . " `store_url_id` = " . intval($_PARAM['id']) . " AND";
               }


               if (!empty($_PARAM['store_url_usetor']))       { $_USER_TOR      = 1;  }        else { $_USER_TOR    = 0; }
            
            // Création requête SQL
               $_BLOC_SELECT =
               "
                  `" . $_TABLE_LIST['dexocard'] . "`.`store_url`.`store_url_id`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`store_url`.`store_url_storeid`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`store_url`.`store_url_usetor`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`store_url`.`store_url_categorieid`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`store_url`.`store_url_javascript`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`store_url`.`store_url_url`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`store_url`.`store_url_lastupdate`
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
                     'id'                       => $thisCard['store_url_id'],
                     'storeid'                  => $thisCard['store_url_storeid'],
                     'categoryid'               => $thisCard['store_url_categorieid'],
                     'usetor'                   => $thisCard['store_url_usetor'],
                     'url'                      => $thisCard['store_url_url'],
                     'javascript'               => $thisCard['store_url_javascript'],
                     'date_lastupdate'          => $thisCard['store_url_lastupdate'],
                  ));
               }

            // Update : `store_url_lastupdate`
               if (empty($_PARAM['id']) && empty($_PARAM['storeid']))
               {
                  $results = $_SQL['dexocard']->update("store_url", 
                  [
                     "store_url_lastupdate"   => Medoo::raw('NOW()'),
                  ],
                  [
                     "store_url_id" => $results_print[0]['id']
                  ]);
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
      }

      function getQuery_Sets($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, $_BLOC_LIMIT = NULL)
      {
         global $_TABLE_LIST;

         // Assemblage requête SQL
            return "
               SELECT 

               " . $_BLOC_SELECT . "

               FROM `" . $_TABLE_LIST['dexocard'] . "`.`store_url`
               
               " . ($_BLOC_WHERE ? "WHERE " . substr($_BLOC_WHERE, 0, strlen($_BLOC_WHERE) - 4) : '') . "

               ORDER BY 
                  `" . $_TABLE_LIST['dexocard'] . "`.`store_url`.`store_url_lastupdate` ASC

               " . ($_BLOC_LIMIT ? $_BLOC_LIMIT : '') . "
               ;
            ";
      }
?>