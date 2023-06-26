<?php
   // check Token
      $_TOKEN->checkAccess('dexocard', 'tcg/serie');

      switch (strtoupper($_METHOD))
      {
         case 'GET':
         {
            /*
               https://api.dexocard.com/v1/tcg/serie?limit=99&order=order-asc
            */

            $_FILTERS_ACTIVE  = array();
            $_BLOC_WHERE      = '';
            $_ASSOCS_VARS     = array();
            $_ORDER           = "id-ASC";

            // Defaults vars
               if (!empty($_GET['limit']))      { $_LIMIT      = intval($_GET['limit']);  }                                   else { $_LIMIT  = 10; }
               if (!empty($_GET['offset']))     { $_OFFSET     = intval($_GET['offset']); }                                   else { $_OFFSET = 0; }
               if (!empty($_GET['order']))  		{ $_ORDER      = $_GET['order']; }

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
                  getQuery_Sets($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, "LIMIT " . $_OFFSET . ", " . $_LIMIT, $_ORDER), 
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
      function getQuery_Sets($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, $_BLOC_LIMIT = NULL, $_ORDER = NULL)
      {
         global $_TABLE_LIST;

         // order
            $order_sql = '';
            if ($_ORDER)
            {
               $_ORDER = explode(',', $_ORDER);
               foreach ($_ORDER as $itemOrder)
               {
                  $exploded_order = explode('-', $itemOrder);
                  $column  = trim($exploded_order[0]);
                  $dir     = (strtoupper(trim($exploded_order[1])) == 'ASC' ? 'ASC' : 'DESC');

                  switch ($column)
                  {
                     case 'name'                         :
                     { 
                        $order_sql = $order_sql .     "`card_serie_nameFR` "                   . $dir . ", ";
                        $order_sql = $order_sql .     "`card_serie_nameEN` "                   . $dir . ", ";
                        break;
                     }

                     case 'id'                           :
                     { 
                        $order_sql = $order_sql .     "`card_serie_id` "                   . $dir . ", ";
                        break;
                     }

                     case 'order'                        :
                     { 
                        $order_sql = $order_sql . "`card_serie_order` "                   . $dir . ", ";
                        break;
                     }


                     case 'date_release'      : { $order_sql = $order_sql . "`card_set_releaseDatefr` "     . $dir . ", ";        break; }
                  }
               }
               $order_sql = substr($order_sql, 0, strlen($order_sql) - 2);
            }

         // Assemblage requête SQL
            return "
               SELECT 

               " . $_BLOC_SELECT . "

               FROM " . $_TABLE_LIST['dexocard'] . ".`card_serie`
               
               " . ($_BLOC_WHERE ? "WHERE " . substr($_BLOC_WHERE, 0, strlen($_BLOC_WHERE) - 4) : '') . "

               " . ($order_sql ? "ORDER BY $order_sql" : '') . "

               " . ($_BLOC_LIMIT ? $_BLOC_LIMIT : '') . "
               ;
            ";
      }
?>