<?php
   // check Token
      $_TOKEN->checkAccess('dexocard', 'store/product-detect');

      switch (strtoupper($_METHOD))
      {
         case 'GET':
         {
            // Nom de l'item
               if (empty($_GET['name']))
               {
                  $_JSON_PRINT->fail("name of item is missing");
                  $_JSON_PRINT->print();
               }
         }
      }
?>