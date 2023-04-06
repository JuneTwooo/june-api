<?php
   // check Token
      $_TOKEN->checkAccess('admin', 'token');

      switch (strtoupper($_METHOD))
      {
         case 'GET':
         {
            // Check parameters
               if (empty($_GET['limit']))       { $_GET['limit'] = 10; }      else { if (1 > $_GET['limit'])  { $_GET['limit']  = 10; } }
               if (empty($_GET['offset']))      { $_GET['offset'] = 0; }      else { if (0 > $_GET['offset']) { $_GET['offset'] = 0; } }
               if (empty($_GET['id']))          { $_GET['id'] = null; } 

            // MySQL Connect
               $_SQL = $_MYSQL->connect(array("api"));

            // Query SQL
               $results_print = array();
               foreach ($_SQL['api']->query
               (
                  "
                     SELECT 
                        `" . $_TABLE_LIST['api'] . "`.`token`.`token_id`,
                        `" . $_TABLE_LIST['api'] . "`.`token`.`token_access`,
                        (
                           SELECT JSON_ARRAYAGG(JSON_OBJECT(
                              'id',    `api`.`user`.`user_id`,
                              'email', `api`.`user`.`user_email`
                           )) 
                           FROM `" . $_TABLE_LIST['api'] . "`.`user`
                           WHERE `" . $_TABLE_LIST['api'] . "`.`user`.`user_tokenid` = `" . $_TABLE_LIST['api'] . "`.`token`.`token_id`
                        ) AS `user_associated`
                     FROM 
                        `" . $_TABLE_LIST['api'] . "`.`token`
                     WHERE
                        " . (empty($_GET['id']) ? "1" : "`" . $_TABLE_LIST['api'] . "`.`token`.`token_id` = '" . addslashes($_GET['id']) . "'") . "
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
                     'access'    => (isJson($itemSQL['token_access']) ? json_decode($itemSQL['token_access'], true) : array()),
                     'user'      => ($itemSQL['user_associated']  ? json_decode($itemSQL['user_associated'], true) : null),
                  ));
               };


            // Print Result
               $_JSON_PRINT->success(); 
               $_JSON_PRINT->response($results_print);
               $_JSON_PRINT->print();

            break;
         }

         case 'POST':
         {
            // Check parameters
               if ( empty($_POST['id']) )
               {
                  $_JSON_PRINT->fail("id must be specified"); 
                  $_JSON_PRINT->print();
               } 

               if ( 10 > strlen($_POST['id']) || strlen($_POST['id']) > 40 )
               {
                  $_JSON_PRINT->fail("id length must be between 10 and 40 char"); 
                  $_JSON_PRINT->print();
               }               

            // MySQL Connect
               $_SQL = $_MYSQL->connect(array("api"));

            // Query SQL
               $result = $_SQL['api']->insert
               (
                  "token",
                  [
                     "token_id" => $_POST['id'],
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

         case 'PUT':
         {
            // Check parameters
               if (empty($id))
               {
                  $_JSON_PRINT->fail("id must be specified"); 
                  $_JSON_PRINT->print();
               } 

               if (!isJson($access))
               {
                  $_JSON_PRINT->fail("access must be JSON formatted"); 
                  $_JSON_PRINT->print();
               } 
               
            // MySQL Connect
               $_SQL = $_MYSQL->connect(array("api"));

            // Query SQL
               $results_print = array();

               $_SQL['api']->query
               ("
                  UPDATE 
                     `" . $_TABLE_LIST['api'] . "`.`token` 
                  SET 
                     `" . $_TABLE_LIST['api'] . "`.`token`.`token_access` = :token_access, 
                     `" . $_TABLE_LIST['api'] . "`.`token`.`token_lastedit` = NOW() 
                  WHERE 
                     `" . $_TABLE_LIST['api'] . "`.`token`.`token_id` = :token_id
                  ;", 
                  [
                     ":token_id"       => $id,
                     ":token_access"   => $access,
                  ]
               );

            // Print Result
               $_JSON_PRINT->success(); 
               $_JSON_PRINT->response($results_print);
               $_JSON_PRINT->print();

            break;
         }

         case 'DELETE':
            {
               // Check parameters
                  if ( empty($id) )
                  {
                     $_JSON_PRINT->fail("id must be specified"); 
                     $_JSON_PRINT->print();
                  } 
   
   
               // MySQL Connect
                  $_SQL = $_MYSQL->connect(array("api"));
   
               // Query SQL
                  $result = $_SQL['api']->delete
                  (
                     "token",
                     [
                        "token_id" => $id,
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
?>