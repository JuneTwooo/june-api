<?php
   // check Token
      $_TOKEN->checkAccess('dexocard', 'get_tcgo_code');

   // Nombre de code à afficher
      $_NB     = (empty($_NB) ? 1 : intval($_NB));

   // Nombre de code maximum à afficher
      if ($_NB > 10)
      {
         $_JSON_PRINT->fail("Maximum code is 10"); 
         $_JSON_PRINT->print();
      }

   // Nombre de code maximum à afficher
      if ($_NB == 0)
      {
         $_JSON_PRINT->fail("No code to print"); 
         $_JSON_PRINT->print();
      }

   // MySQL Connexion
      $_SQL    = SQL_Connect(array('dexocard_api'));

   // Affiche les codes
      $response = array();
      foreach ($_SQL['dexocard_api']->query
      (
         '
            SELECT 
               `tcg_code_code`,
               `tcg_code_setid`,
               `tcg_code_texte`,
               `tcg_code_dateadd`,
               `tcg_code_datechecked`
            FROM `tcg_code` 
            WHERE `tcg_code_dateused` IS NULL LIMIT :nb;
         ', 
         [":nb" => $_NB]
      )->fetchAll(PDO::FETCH_ASSOC) as $itemSQL)
      {
         $code = '';
         $code = $code .   substr($itemSQL['tcg_code_code'], 0, 3) . '-';
         $code = $code .   substr($itemSQL['tcg_code_code'], 3, 4) . '-';
         $code = $code .   substr($itemSQL['tcg_code_code'], 7, 3) . '-';
         $code = $code .   substr($itemSQL['tcg_code_code'], 10, 3);

         array_push($response, array
         (
            'tcg_code_code'         => $code,
            'tcg_code_setid'        => $itemSQL['tcg_code_setid'],
            'tcg_code_texte'        => $itemSQL['tcg_code_texte'],
            'tcg_code_dateadd'      => $itemSQL['tcg_code_dateadd'],
            'tcg_code_datechecked'  => $itemSQL['tcg_code_datechecked'],
         ));
      }

   // print
   $_JSON_PRINT->success();
   $_JSON_PRINT->response($response);
   $_JSON_PRINT->print();
?>