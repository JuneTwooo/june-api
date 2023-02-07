<?php
   // check Token
      $_TOKEN->checkAccess('dexocard', 'tcg/card');

      switch (strtoupper($_METHOD))
      {
         case 'GET':
         {
            $_SQL    = $_MYSQL->connect(array("api"));
            foreach ($_SQL['api']->query
            (
               '
                  SELECT 
                     `card_number`,
                     `card_index`,
                     `card_serieid`,
                     `card_setid`,
                     `card_level`
                  FROM `card` 
               ', 
               []
            )->fetchAll(PDO::FETCH_ASSOC) as $thisCard)
            {
print_r($thisCard);
            }
            //$_JSON_PRINT->fail("No code to print"); 
            //$_JSON_PRINT->print();

            break;
         }
      }
?>