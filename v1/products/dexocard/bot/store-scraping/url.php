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
               if (!empty($_PARAM['storeid']))              { $_BLOC_WHERE      = $_BLOC_WHERE . " `store_url_storeid` = " . intval($_PARAM['storeid']) . " AND"; }
               if (!empty($_PARAM['id']))                   { $_BLOC_WHERE      = $_BLOC_WHERE . " `store_url_id` = " . intval($_PARAM['id']) . " AND"; }
               if (!empty($_PARAM['store_scan']))           { $_BLOC_WHERE      = $_BLOC_WHERE . " `store_scan` = 1 AND"; }
               if (!empty($_PARAM['store_url_usetor']))     { $_USER_TOR      = 1;  }        else { $_USER_TOR    = 0; }
            
            // Création requête SQL
               $_BLOC_SELECT =
               "
                  `" . $_TABLE_LIST['dexocard'] . "`.`store_url`.`store_url_id`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`store_url`.`store_url_storeid`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`store_url`.`store_url_usetor`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`store_url`.`store_url_categorieid`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`store_url`.`store_url_javascript`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`store_url`.`store_url_url`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`store_url`.`store_url_lastupdate`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`store_url`.`store_url_testask`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`store_url`.`store_url_testresult`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`store`.`store_name`,
                  `" . $_TABLE_LIST['dexocard'] . "`.`store`.`store_scan`
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
                     'storename'                => $thisCard['store_name'],
                     'storescan'                => $thisCard['store_scan'],
                     'categoryid'               => $thisCard['store_url_categorieid'],
                     'usetor'                   => $thisCard['store_url_usetor'],
                     'url'                      => $thisCard['store_url_url'],
                     'javascript'               => $thisCard['store_url_javascript'],
                     'test'                     => $thisCard['store_url_testask'],
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

         case 'POST':
         case 'PUT':
         {
            // Defaults vars
               if (empty($_PARAM['id']) && empty($_PARAM['storeid']))  { $_JSON_PRINT->fail("id or storeid must be specified"); $_JSON_PRINT->print(); }
               
               if (!empty($_PARAM['test']) && intval($_PARAM['test']))
               {
                  if (empty(intval($_PARAM['id'])))
                  {
                     $_JSON_PRINT->fail("id must be specified for testing url");
                     $_JSON_PRINT->print();
                  }
               }
             
            // MySQL Connect
               $_SQL          = $_MYSQL->connect(array("dexocard"));
     
            // Get New ID if id=-1
               if (!empty($_PARAM['id']) && $_PARAM['id'] == -1 && !empty($_PARAM['storeid']))
               {
                  $_SQL['dexocard']->insert("store_url", ["store_url_storeid" => $_PARAM['storeid']]);
                  $_PARAM['id'] = $_SQL['dexocard']->id();
               }

            // Get URL info from SQL
               if (!empty($_PARAM['id']))
               {
                  $_SQL_URL  = $_SQL['dexocard']->query("SELECT * FROM `" . $_TABLE_LIST['dexocard'] . "`.`store_url` WHERE store_url_id = :url_id", [":url_id" => $_PARAM['id']])->fetch(PDO::FETCH_ASSOC);

                  if (empty($_SQL_URL['store_url_id']))
                  {
                     $_JSON_PRINT->fail("store id not found");
                     $_JSON_PRINT->print();                                   
                  }
               }

            // si un test est demandé, on attends le résultat du bot pendant $maxSeconds secondes
               ini_set('max_execution_time', 60);
               set_time_limit(60);
               
               $maxSeconds = 30;
               if (!empty($_PARAM['test']))
               {
                  // Connexion SL
                  $_SQL = $_MYSQL->connect(array("dexocard"));

                  // Vérifie la dernière fois que le bot à update un item
                  $results_last_update = $_SQL['dexocard']->query
                  (
                     "SELECT `store_url_lastupdate` FROM `" . $_TABLE_LIST['dexocard'] . "`.store_url ORDER BY `store_url_lastupdate` DESC LIMIT 0,1;"
                  )->fetch(PDO::FETCH_ASSOC);
                  
                  if ((time() - strtotime($results_last_update['store_url_lastupdate'])) >= 60)
                  {
                     $_JSON_PRINT->fail("bot store-scrapping seems to be offline");
                     $_JSON_PRINT->print();
                  }

                  // On demande au bot de faire le test sur cette URL
                  $_SQL['dexocard']->update("store_url", 
                  [
                     "store_url_testask"     => 1,
                     "store_url_testresult"  => null
                  ],
                  [
                     "store_url_id"          => $_PARAM['id']
                  ]);

                  // reset du query précédent puisqu'on va envoyer un nouveau query comprennant le résultat du test, si le bot est en marche
                  $results_print = array();

                  // on attend donc $maxSeconds secondes grâce à une boucle
                  $timeStart = time();
                  while (1)
                  {
                     $results_from_bot = $_SQL['dexocard']->query
                     (
                        "SELECT `store_url_id`, `store_url_testresult` FROM `" . $_TABLE_LIST['dexocard'] . "`.store_url WHERE store_url_id = " . intval($_PARAM['id']) . " LIMIT 0,1;"
                     )->fetch(PDO::FETCH_ASSOC);

                     if ($results_from_bot['store_url_testresult'] != "")
                     {
                        // le résultat est là
                        $results_print = array
                        (
                           'id'              => $results_from_bot['store_url_id'],
                           'testresults'     => $results_from_bot['store_url_testresult'],
                        );

                        // reset 
                        $_SQL['dexocard']->update("store_url", 
                        [
                           "store_url_testask"     => null,
                           "store_url_testresult"  => null
                        ],
                        [
                           "store_url_id"          => $_PARAM['id']
                        ]);

                        // Print Results
                        $_JSON_PRINT->success(); 
                        $_JSON_PRINT->response($results_print);
                        $_JSON_PRINT->print();
                     }
                     else
                     {
                        if ((time() - $timeStart) >= $maxSeconds)
                        {
                           // reset
                              $_SQL['dexocard']->update("store_url", 
                              [
                                 "store_url_testask"     => null,
                                 "store_url_testresult"  => null
                              ],
                              [
                                 "store_url_id"          => $_PARAM['id']
                              ]);

                           // show error
                              $_JSON_PRINT->fail("bot store-scrapping not responding after " . $maxSeconds . " secondes");
                              $_JSON_PRINT->print();
                        }
                        else
                        { 
                           sleep(1);
                        }
                     }
                  }
               }
               else
               {
                  // Columns to update (si aucun test demandé)
                     $update_cols = array();
                     if (isset($_PARAM['url']))                  { $update_cols = array_merge($update_cols, ["store_url_url"                => $_PARAM['url']]); }
                     if (isset($_PARAM['categoryid']))           { $update_cols = array_merge($update_cols, ["store_url_categorieid"        => $_PARAM['categoryid']]); }
                     if (isset($_PARAM['javascript']))           { $update_cols = array_merge($update_cols, ["store_url_javascript"         => $_PARAM['javascript']]); }
                     if (isset($_PARAM['testresults']))          { $update_cols = array_merge($update_cols, ["store_url_testresult"         => $_PARAM['testresults']]); }
                     if (isset($_PARAM['usetor']))               { $update_cols = array_merge($update_cols, ["store_url_usetor"             => (!empty($_PARAM['usetor'])     ? 1 : 0)]); }
                     if (isset($_PARAM['lastupdate']))           { $update_cols = array_merge($update_cols, ["store_url_lastupdate"         => (!empty($_PARAM['lastupdate']) ? $_PARAM['lastupdate'] : null)]); }

                     // Enregistrement SQL
                     if (!$update_cols)
                     {
                        $_JSON_PRINT->fail("no data to update"); $_JSON_PRINT->print();
                     }

                     $_SQL          = $_MYSQL->connect(array("dexocard"));
                     $_SQL['dexocard']->update("store_url", $update_cols,
                     [
                        (!empty($_PARAM['id']) ? "store_url_id" : "store_url_storeid") => (!empty($_PARAM['id']) ? $_PARAM['id'] : $_PARAM['storeid'])
                     ]);
               }

            // Print Results
               $_JSON_PRINT->success(); 
               $_JSON_PRINT->response();
               $_JSON_PRINT->print();           

            break;
         }

         case 'DELETE':
         {
            // Defaults vars
               if (empty(intval($_PARAM['id']))) { $_JSON_PRINT->fail("id must be specified"); $_JSON_PRINT->print(); }
             
            // MySQL Connect
               $_SQL          = $_MYSQL->connect(array("dexocard"));

            // Query SQL
               $result = $_SQL['dexocard']->delete
               (
                  "store_url",
                  [
                     "store_url_id" => $_GET['id'],
                  ]
               );

            // Print Result
               if (!empty($result))
               {
                  $_JSON_PRINT->success(); 
                  $_JSON_PRINT->response();
                  $_JSON_PRINT->print();
               }
               else
               {
                  $_JSON_PRINT->fail("unknow"); 
               }

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
               
               LEFT JOIN `" . $_TABLE_LIST['dexocard'] . "`.`store`        ON `store_url`.`store_url_storeid` = `store_id`
               
               " . ($_BLOC_WHERE ? "WHERE " . substr($_BLOC_WHERE, 0, strlen($_BLOC_WHERE) - 4) : '') . "

               ORDER BY
               `" . $_TABLE_LIST['dexocard'] . "`.`store_url`.`store_url_testask` DESC,
               `" . $_TABLE_LIST['dexocard'] . "`.`store_url`.`store_url_lastupdate` ASC

               " . ($_BLOC_LIMIT ? $_BLOC_LIMIT : '') . "
               ;
            ";
      }
?>