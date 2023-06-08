<?php
   // check Token
      $_TOKEN->checkAccess('dexocard', 'tcg/serie');

      switch (strtoupper($_METHOD))
      {
         case 'GET':
         {
            $_FILTERS_ACTIVE  = array();
            $_BLOC_WHERE      = '';
            $_ASSOCS_VARS     = array();

            // Defaults vars
               if (!empty($_GET['limit']))      { $_LIMIT      = intval($_GET['limit']);  }                                   else { $_LIMIT  = 10; }
               if (!empty($_GET['offset']))     { $_OFFSET     = intval($_GET['offset']); }                                   else { $_OFFSET = 0; }
               if (!empty($_GET['order']))      { $_ORDER      = $_GET['order']; }                                            else { $_ORDER = "AND"; }
            
            // Filtres
               if (!empty($_GET['search_text']))
               {
                  $_BLOC_WHERE      = $_BLOC_WHERE . " 
                  (
                        `card_serie_nameFR` LIKE :search_text_set_namefr OR 
                        `card_serie_nameEN` LIKE :search_text_set_nameen OR 
                        `card_serie_id`     LIKE :search_text_set_id
                  ) AND";
                  $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":search_text_set_namefr"        => '%' . addslashes($_GET['search_text']) . '%']);   
                  $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":search_text_set_nameen"        => '%' . addslashes($_GET['search_text']) . '%']);   
                  $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":search_text_set_id"            => '%' . addslashes($_GET['search_text']) . '%']);   
               }

               if (!empty($_GET['id']))
               {
                  $_BLOC_WHERE      = $_BLOC_WHERE . "`card_serie_id` = :card_serie_id AND";
                  $_ASSOCS_VARS     = array_merge($_ASSOCS_VARS, [":card_serie_id"        => addslashes($_GET['id'])]);
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
               )->fetchAll(PDO::FETCH_ASSOC) as $thisCard)
               {
                  array_push($results_print, array
                  (
                     'id'                       => $thisCard['card_serie_id'],
                     'name'               => array
                     (
                        'fr' => (empty($thisCard['card_serie_nameFR']) ? NULL : $thisCard['card_serie_nameFR']), 
                        'en' => (empty($thisCard['card_serie_nameEN']) ? NULL : $thisCard['card_serie_nameEN']), 
                     ),
                     'abrv'          => array
                     (
                        'fr' => (empty($thisCard['card_serie_AbreviationFR']) ? NULL : $thisCard['card_serie_AbreviationFR']), 
                        'en' => (empty($thisCard['card_serie_AbreviationEN']) ? NULL : $thisCard['card_serie_AbreviationEN']), 
                     ),

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

               FROM " . $_TABLE_LIST['dexocard'] . ".`card_serie`
               
               " . ($_BLOC_WHERE ? "WHERE " . substr($_BLOC_WHERE, 0, strlen($_BLOC_WHERE) - 4) : '') . "

               ORDER BY 
                  " . $_TABLE_LIST['dexocard'] . ".`card_serie`.`card_serie_id` ASC

               " . ($_BLOC_LIMIT ? $_BLOC_LIMIT : '') . "
               ;
            ";
      }
?>