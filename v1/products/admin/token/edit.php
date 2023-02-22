<?php
   // check Token
      $_TOKEN->checkAccess('admin', 'token/edit');

      switch (strtoupper($_METHOD))
      {
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

               $_JSON_PRINT->success(); 
               $_JSON_PRINT->response($results_print);
               $_JSON_PRINT->print();

            // break GET
               break;
         }
      }
?>