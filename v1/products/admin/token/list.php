<?php
   // check Token
      $_TOKEN->checkAccess('admin', 'token/list');

      switch (strtoupper($_METHOD))
      {
         case 'GET':
         {
            // Check parameters
               if (empty($_GET['limit']))       { $_GET['limit'] = 10; }      else { if (1 > $_GET['limit'])  { $_GET['limit']  = 10; } }
               if (empty($_GET['offset']))      { $_GET['offset'] = 0; }      else { if (0 > $_GET['offset']) { $_GET['offset'] = 0; } }
               if (empty($_GET['id']))          { $_GET['id'] = null; } 
               if (empty($_GET['show_access'])) { $_GET['show_access'] = 0; } else { $_TOKEN->checkAccess('admin', 'token/show_access'); }

            // MySQL Connect
               $_SQL = $_MYSQL->connect(array("api"));

            // Query SQL
               $results_print = array();
               foreach ($_SQL['api']->query
               (
                  "
                     SELECT 
                        `api`.`token`.`token_id`,
                        `api`.`token`.`token_access`,
                        (
                           SELECT JSON_ARRAYAGG(JSON_OBJECT(
                              'id',    `api`.`user`.`user_id`,
                              'email', `api`.`user`.`user_email`
                           )) FROM `api`.`user` WHERE `api`.`user`.`user_tokenid` = `api`.`token`.`token_id`
                        ) AS `user_associated`
                     FROM 
                        `api`.`token`
                     WHERE
                        " . (empty($_GET['id']) ? "1" : "`api`.`token`.`token_id` = '" . addslashes($_GET['id']) . "'") . "
                     LIMIT :offset, :limit;
                  ", 
                  [
                     ":offset"   => $_GET['offset'],
                     ":limit"    => $_GET['limit'],
                  ]
               )->fetchAll(PDO::FETCH_ASSOC) as $itemSQL)
               {
                  // format response
                  array_push($results_print, array
                  (
                     'id'        => $itemSQL['token_id'],
                     'access'    => ($_GET['show_access']         ? json_decode($itemSQL['token_access'], true) : null),
                     'user'      => ($itemSQL['user_associated']  ? json_decode($itemSQL['user_associated'], true) : null),
                  ));
               }



               $_JSON_PRINT->success(); 
               $_JSON_PRINT->response($results_print);
               $_JSON_PRINT->print();

            // break GET
               break;
         }
      }
?>