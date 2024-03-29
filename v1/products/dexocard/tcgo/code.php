<?php
   // check Token
      $_TOKEN->checkAccess('dexocard', 'tcgo/code');
      
      use chillerlan\QRCode\{QRCode, QROptions};
      use chillerlan\QRCode\Common\EccLevel;
      use chillerlan\QRCode\Data\QRMatrix;
      use chillerlan\QRCode\Output\QROutputInterface;
      use Medoo\Medoo;      

      switch (strtoupper($_METHOD))
      {
         case 'GET':
         {
            // Nombre de code à afficher
               $_NB     = (empty($_GET['length'])  ? 1 : intval($_GET['length']));
               $_RAND   = (empty($_GET['rand'])    ? 1 : 0);

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
               $_SQL    = $_MYSQL->connect(array("dexocard"));

            // Qr Code opts 
               $qrcodes_options = new QROptions(
               [
                  'version'             => 7,
                  'scale'               => 3,
                  'imageBase64'         => true,
                  'bgColor'             => [255, 255, 255],
                  'imageTransparent'    => false,
                  'drawCircularModules' => true,
                  'drawLightModules'    => true,
                  'circleRadius'        => 0.4,
                  'keepAsSquare'        =>
                  [
                     QRMatrix::M_FINDER_DOT,
                  ],
               ]);

            // Affiche les codes
               $response = array();
               foreach ($_SQL['dexocard']->query
               (
                  "
                     SELECT 
                        " . $_TABLE_LIST['dexocard'] . ".`tcg_code`.`tcg_code_code`,
                        " . $_TABLE_LIST['dexocard'] . ".`tcg_code`.`tcg_code_setid`,
                        " . $_TABLE_LIST['dexocard'] . ".`tcg_code`.`tcg_code_texte`,
                        " . $_TABLE_LIST['dexocard'] . ".`tcg_code`.`tcg_code_dateadd`,
                        " . $_TABLE_LIST['dexocard'] . ".`tcg_code`.`tcg_code_datechecked`
                     FROM
                        " . $_TABLE_LIST['dexocard'] . ".`tcg_code`
                     WHERE
                        " . $_TABLE_LIST['dexocard'] . ".`tcg_code`.`tcg_code_dateused` IS NULL
                     " . ($_RAND ? "ORDER BY RAND()" : "") . "
                     LIMIT 0, :nb;
                  ", 
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
                     'tcg_code_qrcode'       => (new QRCode($qrcodes_options))->render($itemSQL['tcg_code_code']),
                  ));

                  $results = $_SQL['dexocard']->update("tcg_code", 
                  [
                     "tcg_code_dateused" => Medoo::raw('NOW()'),
                  ],
                  [
                     "tcg_code_code" => $itemSQL['tcg_code_code']
                  ]);
               }

            // print
               $_JSON_PRINT->success();
               $_JSON_PRINT->response($response);
               $_JSON_PRINT->print();

            break;
         }
      }
?>