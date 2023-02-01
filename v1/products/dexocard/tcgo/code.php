<?php
   $_SQL    = SQL_Connect(array('dexocard_api'));
   $_NB     = (empty($_NB) ? 1 : $_NB);

   if ($_NB > 10)
   {
      $_JSON_PRINT->fail("Maximum code is 10"); 
      $_JSON_PRINT->print();
   }

   
   //$_JSON_PRINT->response($_NB);
?>