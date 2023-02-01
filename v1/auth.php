<?php
   global $_JSON_PRINT;
   global $_TOKEN;

   $_TOKEN->setKey($_PUBLIC_KEY);
   $token = $_TOKEN->auth();
?>