<?php
   // check Token
      $_TOKEN->checkAccess('admin', 'user/user');

      switch (strtoupper($_METHOD))
      {
         case 'GET':
         {
            // Check parameters
               if (empty($_GET['id']))
               {
                  $_JSON_PRINT->fail("id must be specified"); 
                  $_JSON_PRINT->print();
               }
         
            // MySQL Connect
               $_SQL = $_MYSQL->connect(array("api"));

            // Check email and password
               $result = $_SQL['api']->query
               ("
                  SELECT 
                     " . $_TABLE_LIST['api'] . ".`user`.`user_id`,
                     " . $_TABLE_LIST['api'] . ".`user`.`user_tokenid`,
                     " . $_TABLE_LIST['api'] . ".`token`.token_access
                  FROM 
                     " . $_TABLE_LIST['api'] . ".`user`
                  LEFT JOIN " . $_TABLE_LIST['api'] . ".`token` ON " . $_TABLE_LIST['api'] . ".`token`.`token_id` =  " . $_TABLE_LIST['api'] . ".`user`.`user_tokenid`
                  WHERE 
                     " . $_TABLE_LIST['api'] . ".`user`.`user_id` = :id
                  LIMIT 0,1
                  ;
               ", 
               [
                  ":id" => $_GET['id']
               ])->fetch(PDO::FETCH_ASSOC);

               if ($result)
               {
                  $_JSON_PRINT->success(); 
                  $_JSON_PRINT->response(array
                  (
                     "id"           => $result['user_id'],
                     "tokenid"      => $result['user_tokenid'],
                     "token_access" => $result['token_access'],
                  )); 
                  $_JSON_PRINT->print();
               }
               else
               {
                  $_JSON_PRINT->fail("wrong credentials or account does not exist"); 
                  $_JSON_PRINT->print();
               }

            // break GET
               break;
         }
      }
?>