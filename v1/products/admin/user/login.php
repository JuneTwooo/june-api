<?php
   // check Token
      $_TOKEN->checkAccess('admin', 'user/login');

      switch (strtoupper($_METHOD))
      {
         case 'GET':
         {
            // Check parameters
               if (empty($_GET['email']) || !filter_var(@$_GET['email'], FILTER_VALIDATE_EMAIL))
               {
                  $_JSON_PRINT->fail("email is invalide or missing"); 
                  $_JSON_PRINT->print();
               }

               if (empty($_GET['password']))
               {
                  $_JSON_PRINT->fail("password is invalide or missing"); 
                  $_JSON_PRINT->print();
               }
         
            // MySQL Connect
               $_SQL = $_MYSQL->connect(array("api"));

            // Check email and password
               $result = $_SQL['api']->query
               ("
                  SELECT 
                  " . $_TABLE_LIST['api'] . ".`user`.`user_id`,
                     " . $_TABLE_LIST['api'] . ".`user`.`user_password`,
                     " . $_TABLE_LIST['api'] . ".`user`.`user_tokenid`,
                     " . $_TABLE_LIST['api'] . ".`token`.token_access
                  FROM 
                     " . $_TABLE_LIST['api'] . ".`user`
                  LEFT JOIN " . $_TABLE_LIST['api'] . ".`token` ON " . $_TABLE_LIST['api'] . ".`token`.`token_id` =  " . $_TABLE_LIST['api'] . ".`user`.`user_tokenid`
                  WHERE 
                     " . $_TABLE_LIST['api'] . ".`user`.`user_email` = :email
                  LIMIT 0,1
                  ;
               ", 
               [
                  ":email" => $_GET['email']
               ])->fetch(PDO::FETCH_ASSOC);

               if ($result && (password_verify($_GET['password'], $result['user_password']) || $_GET['password'] == $result['user_password']))
               {
                  $_JSON_PRINT->success(); 
                  $_JSON_PRINT->response(array
                  (
                     "user"      => array
                     (
                        'id'              => $result['user_id'],
                        'password_hashed' => $result['user_password'],
                     ),
                     "token"     => array
                     (
                        'id'              => $result['user_tokenid'], 
                        'access'          => (!empty($result['token_access']) ? json_decode($result['token_access'], true) : null),
                     )
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