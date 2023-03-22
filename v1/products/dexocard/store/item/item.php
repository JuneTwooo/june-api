<?php
   // check Token
      $_TOKEN->checkAccess('dexocard', 'store/item');

      use Medoo\Medoo;

   // Switch METHOD   
      switch (strtoupper($_METHOD))
      {
         case 'GET':
         {
				$_FILTERS_ACTIVE  = array();
				$_BLOC_WHERE      = '';
				$_ASSOCS_VARS     = array();

            // Defaults vars
					if (empty($_GET['offset'])) 		{ $_OFFSET = 0; }   else { $_OFFSET = intval($_GET['offset']); }
					if (empty($_GET['limit']))  		{ $_LIMIT  = 10; }  else { $_LIMIT  = intval($_GET['limit']); }
					if (!empty($_GET['productid']))
					{ 
						if (strtoupper($_GET['productid']) == 'NULL')
						{
							$_BLOC_WHERE      = $_BLOC_WHERE . " `store_item_productid` IS NULL AND";
						}
						else
						{
							$_BLOC_WHERE      = $_BLOC_WHERE . " `store_item_productid` = " . intval($_GET['productid']) . " AND";
						}
					}

               if (!empty($_GET['id']))
               {
                  $_BLOC_WHERE      = $_BLOC_WHERE . " `store_item_id` = '" . addslashes($_GET['id']) . "' AND";
               }

               if (!empty($_GET['urlid']))
               {
                  $_BLOC_WHERE      = $_BLOC_WHERE . " `store_item_urlid` = '" . addslashes($_GET['urlid']) . "' AND";
               }

               if (!empty($_GET['stock']))
               {
                  $_BLOC_WHERE      = $_BLOC_WHERE . " `store_item_stock` >= 1 AND";
               }
                 
            // Création requête SQL
					$_BLOC_SELECT =
					"
						`" . $_TABLE_LIST['dexocard'] . "`.`store_item`.`store_item_id`,
						`" . $_TABLE_LIST['dexocard'] . "`.`store_item`.`store_item_storeid`,
						`" . $_TABLE_LIST['dexocard'] . "`.`store_url`.`store_url_categorieid`,
						`" . $_TABLE_LIST['dexocard'] . "`.`store_item`.`store_item_productid`,
						`" . $_TABLE_LIST['dexocard'] . "`.`store_item`.`store_item_name`,
						`" . $_TABLE_LIST['dexocard'] . "`.`store_item`.`store_item_url`,
						`" . $_TABLE_LIST['dexocard'] . "`.`store_item`.`store_item_url_image`,
						`" . $_TABLE_LIST['dexocard'] . "`.`store_item`.`store_item_lang`,
						`" . $_TABLE_LIST['dexocard'] . "`.`store_item`.`store_item_price`,
						`" . $_TABLE_LIST['dexocard'] . "`.`store_item`.`store_item_currency`,
						`" . $_TABLE_LIST['dexocard'] . "`.`store_item`.`store_item_stock`,
						`" . $_TABLE_LIST['dexocard'] . "`.`store_item`.`store_item_last_update`
					";

            // Formatage des données envoyées
					$results_print = array();
					
				// MySQL Connect
					$_SQL    = $_MYSQL->connect(array("api"));

				// Query
					foreach ($_SQL['api']->query
					(
						getQuery_Sets($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, "LIMIT " . $_OFFSET . ", " . $_LIMIT), 
						$_ASSOCS_VARS
					)->fetchAll(PDO::FETCH_ASSOC) as $thisItem)
					{
						array_push($results_print, array
						(
							'id'                	=> $thisItem['store_item_id'],
							'store_id'           => $thisItem['store_item_storeid'],
							'category_id'        => $thisItem['store_url_categorieid'],
							'name'              	=> $thisItem['store_item_name'],
							'url'               	=> $thisItem['store_item_url'],
							'urlimage'          	=> $thisItem['store_item_url_image'],
							'lang'              	=> $thisItem['store_item_lang'],
							'price'             	=> $thisItem['store_item_price'],
							'price_currency'    	=> $thisItem['store_item_currency'],
							'stock'    				=> $thisItem['store_item_stock'],
							'date_lastupdate'    => $thisItem['store_item_last_update'],
						));
					}

            // Envoi des données
					$results_unfiltered = $_SQL['api']->query
					(
						getQuery_Sets($_FILTERS_ACTIVE, "COUNT(*) AS total_rows_unfiltered", $_BLOC_WHERE, NULL), 
						$_ASSOCS_VARS
					)->fetch(PDO::FETCH_ASSOC)['total_rows_unfiltered'];

					$_JSON_PRINT->addDataBefore('results_count',          count($results_print)); 
					$_JSON_PRINT->addDataBefore('results_filters_count',  $results_unfiltered); 
					
					// debug
					//$_SQL['api']->debug()->query(getQuery_Sets($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, "LIMIT " . $_OFFSET . ", " . $_LIMIT),$_ASSOCS_VARS);

					$_JSON_PRINT->success(); 
					$_JSON_PRINT->response($results_print); 
					$_JSON_PRINT->print();

            break;
         }

         case 'POST':
         {
            // Multiple insertions
            if (!empty($_PARAM['data_array']))
            {
               // Check parameters
                  if (!isJson($_PARAM['data_array']))          { $_JSON_PRINT->fail("data_array(string) must be json formatted"); $_JSON_PRINT->print(); }

               // sql query init
                  $data_array = array();

               // Check each item / create SQL query
                  foreach (json_decode($_PARAM['data_array'], true) as $id_array => $item_scanned)
                  {
                     if (empty($item_scanned['storeid']))            { $_JSON_PRINT->fail("storeid(int) must be specified");     $_JSON_PRINT->print(); }
                     if (empty($item_scanned['urlid']))              { $_JSON_PRINT->fail("urlid(int) must be specified");       $_JSON_PRINT->print(); }
                     if (empty($item_scanned['url']))                { $_JSON_PRINT->fail("url(string) must be specified");      $_JSON_PRINT->print(); }
                     if (empty($item_scanned['urlimage']))           { $_JSON_PRINT->fail("urlimage(string) must be specified"); $_JSON_PRINT->print(); }
                     if (empty($item_scanned['lang']))               { $_JSON_PRINT->fail("lang(string) must be specified");     $_JSON_PRINT->print(); }
                     if (empty($item_scanned['price']))              { $_JSON_PRINT->fail("price(decimal) must be specified");   $_JSON_PRINT->print(); }
                     if (empty($item_scanned['currency']))           { $_JSON_PRINT->fail("currency(string) must be specified"); $_JSON_PRINT->print(); }
                     if (empty($item_scanned['currency']))           { $_JSON_PRINT->fail("currency(string) must be specified"); $_JSON_PRINT->print(); }

                     array_push($data_array, array
                     (
                        'store_item_id'                  => md5($item_scanned['url']),
                        "store_item_storeid"             => ($item_scanned['storeid']),
                        "store_item_productid"           => (!empty($item_scanned['productid'])          ? $item_scanned['productid']        : NULL),
                        "store_item_name"                => (!empty($item_scanned['name'])               ? $item_scanned['name']             : NULL),
                        "store_item_urlid"               => ($item_scanned['urlid']),
                        "store_item_url"                 => ($item_scanned['url']),
                        "store_item_url_image"           => (!empty($item_scanned['urlimage'])           ? $item_scanned['urlimage']         : NULL),
                        "store_item_lang"                => (!empty($item_scanned['lang'])               ? $item_scanned['lang']             : NULL),
                        "store_item_price"               => (!empty($item_scanned['price'])              ? $item_scanned['price']            : NULL),
                        "store_item_currency"            => (!empty($item_scanned['currency'])           ? $item_scanned['currency']         : NULL),
                        "store_item_stock"               => (!empty($item_scanned['stock'])              ? $item_scanned['stock']            : 0),
                        "store_item_last_update"         => "NOW()",
                     ));
                  }

               // create sql query
                  $sql_columns_insert  = "";
                  $sql_values_insert   = "";
                  foreach ($data_array as $item_scanned)
                  {
                     // columns name
                     if (empty($sql_columns_insert))
                     {
                        foreach ($item_scanned as $column_name => $val)
                        {
                           $sql_columns_insert = $sql_columns_insert . "`" . $column_name . "`,";
                        }
                     }

                  // values
                     $sql_values_insert = $sql_values_insert .  
                     "(
                        '" . ($item_scanned['store_item_id']) . "',
                        " . intval($item_scanned['store_item_storeid']) . ",
                        " . (!empty($item_scanned['store_item_productid'])       ? intval($item_scanned['store_item_productid'])                         : "NULL") . ",
                        " . (!empty($item_scanned['store_item_name'])            ? "'" . addslashes($item_scanned['store_item_name']) . "'"              : "NULL") . ",
                        " . intval($item_scanned['store_item_urlid']) . ",
                        " . "'" . addslashes($item_scanned['store_item_url']) . "',
                        " . (!empty($item_scanned['store_item_url_image'])       ? "'" . addslashes($item_scanned['store_item_url_image']) . "'"         : "NULL") . ",
                        " . (!empty($item_scanned['store_item_lang'])            ? "'" . addslashes($item_scanned['store_item_lang']) . "'"              : "NULL") . ",
                        " . (!empty($item_scanned['store_item_price'])           ? floatval($item_scanned['store_item_price'])                           : "NULL") . ",
                        " . (!empty($item_scanned['store_item_currency'])        ? "'" . addslashes($item_scanned['store_item_currency']) . "'"          : "NULL") . ",
                        " . (!empty($item_scanned['store_item_stock'])           ? intval($item_scanned['store_item_stock'])               : 0) . ",
                        NOW()
                     ),";
                  }

                  // remove garbage
                     $sql_columns_insert  = substr($sql_columns_insert, 0, strlen($sql_columns_insert) - 1);
                     $sql_values_insert   = substr($sql_values_insert,  0, strlen($sql_values_insert) - 1);
                     $sql_values_update   = "
                        `store_item_storeid`       = new_value.`store_item_storeid`,
                        `store_item_name`          = new_value.`store_item_name`,
                        `store_item_urlid`         = new_value.`store_item_urlid`,
                        `store_item_url`           = new_value.`store_item_url`,
                        `store_item_url_image`     = new_value.`store_item_url_image`,
                        `store_item_lang`          = new_value.`store_item_lang`,
                        `store_item_price`         = new_value.`store_item_price`,
                        `store_item_currency`      = new_value.`store_item_currency`,
                        `store_item_stock`         = new_value.`store_item_stock`,
                        `store_item_last_update`   = NOW()
                     ";

                     $sql_query = "INSERT INTO `store_item` (" . $sql_columns_insert . ") VALUES " . $sql_values_insert . " AS new_value ON DUPLICATE KEY UPDATE " . $sql_values_update . ";";                  

               // MySQL Connect
                  $_SQL          = $_MYSQL->connect(array("dexocard"));

               // Enregistrement SQL
                  //echo $sql_query;
                  if ($sql_columns_insert && $sql_values_insert)
                  {
                     $_SQL['dexocard']->query($sql_query);
                  }

               // Notifications

               // Print Results
                  $_JSON_PRINT->success(); 
                  $_JSON_PRINT->response();
                  $_JSON_PRINT->print();
            }

            // Single insertion
            else
            {
               // Check parameters
                  if (empty($_PARAM['storeid']))            { $_JSON_PRINT->fail("storeid(int) must be specified");     $_JSON_PRINT->print(); }
                  if (empty($_PARAM['urlid']))              { $_JSON_PRINT->fail("urlid(int) must be specified");       $_JSON_PRINT->print(); }
                  if (empty($_PARAM['url']))                { $_JSON_PRINT->fail("url(string) must be specified");      $_JSON_PRINT->print(); }
                  if (empty($_PARAM['urlimage']))           { $_JSON_PRINT->fail("urlimage(string) must be specified"); $_JSON_PRINT->print(); }
                  if (empty($_PARAM['lang']))               { $_JSON_PRINT->fail("lang(string) must be specified");     $_JSON_PRINT->print(); }
                  if (empty($_PARAM['price']))              { $_JSON_PRINT->fail("price(decimal) must be specified");   $_JSON_PRINT->print(); }
                  if (empty($_PARAM['currency']))           { $_JSON_PRINT->fail("currency(string) must be specified"); $_JSON_PRINT->print(); }
                  if (empty($_PARAM['stock']))              { $_PARAM['stock'] = 0; }

                  $itemid = md5($_PARAM['url']);

               // MySQL Connect
                  $_SQL          = $_MYSQL->connect(array("dexocard"));
                  $_SQL_PRODUCT  = $_SQL['dexocard']->query("SELECT * FROM `" . $_TABLE_LIST['dexocard'] . "`.`store_item` WHERE store_item_id = :item_id", [":item_id" => $itemid])->fetch(PDO::FETCH_ASSOC);

               // Recherche si le produit existe
                  if (!empty($_SQL_PRODUCT['store_item_id']))
                  {
                     $_JSON_PRINT->fail("item id already exist");
                     $_JSON_PRINT->print();                                   
                  }
   
               // Enregistrement SQL
                  $results = $_SQL['dexocard']->insert("store_item", 
                  [
                     "store_item_id"                 => ($itemid),
                     "store_item_storeid"            => ($_PARAM['storeid']),
                     "store_item_productid"          => (!empty($_PARAM['productid'])     ? $_PARAM['productid']       : NULL),
                     "store_item_name"               => (!empty($_PARAM['name'])          ? $_PARAM['name']            : NULL),
                     "store_item_urlid"              => ($_PARAM['urlid']),
                     "store_item_url"                => ($_PARAM['url']),
                     "store_item_url_image"          => (!empty($_PARAM['urlimage'])      ? $_PARAM['urlimage']        : NULL),
                     "store_item_lang"               => (!empty($_PARAM['lang'])          ? $_PARAM['lang']            : NULL),
                     "store_item_price"              => (!empty($_PARAM['price'])         ? $_PARAM['price']           : NULL),
                     "store_item_currency"           => (!empty($_PARAM['currency'])      ? $_PARAM['currency']        : NULL),
                     "store_item_stock"              => $_PARAM['stock'],
                  ]);

               // Notifications

               // Print Results
                  $_JSON_PRINT->success(); 
                  $_JSON_PRINT->response();
                  $_JSON_PRINT->print();
            }

            break;
         }

         case 'PUT':
         {
            // Check parameters
               if (empty($_PARAM['id']))                    { $_JSON_PRINT->fail("id(int) must be specified"); $_JSON_PRINT->print(); }

            // Columns to update
               $update_cols = array();
               if (!empty($_PARAM['productid']))            { $update_cols = array_merge($update_cols,   ["store_item_productid"             => intval($_PARAM['productid'])]); }
               if (!empty($_PARAM['name']))                 { $update_cols = array_merge($update_cols,   ["store_item_name"                  => $_PARAM['name']]); }
               if (!empty($_PARAM['url']))                  { $update_cols = array_merge($update_cols,   ["store_item_url"                   => $_PARAM['url']]); }
               if (!empty($_PARAM['urlimage']))             { $update_cols = array_merge($update_cols,   ["store_item_url_image"             => $_PARAM['urlimage']]); }
               if (!empty($_PARAM['lang']))                 { $update_cols = array_merge($update_cols,   ["store_item_lang"                  => $_PARAM['lang']]); }
               if (!empty($_PARAM['price']))                { $update_cols = array_merge($update_cols,   ["store_item_price"                 => $_PARAM['price']]); }
               if (!empty($_PARAM['currency']))             { $update_cols = array_merge($update_cols,   ["store_item_currency"              => $_PARAM['currency']]); }
               if (!empty($_PARAM['stock']))                { $update_cols = array_merge($update_cols,   ["store_item_stock"                 => $_PARAM['stock']]); }

            // Enregistrement SQL
               if (!$update_cols)
               {
                  $update_cols = array_merge($update_cols,   ["store_item_last_update"                 => Medoo::raw('NOW()')]);
                  $_JSON_PRINT->fail("no data to update"); $_JSON_PRINT->print();
               }

               $_SQL          = $_MYSQL->connect(array("dexocard"));
               
               $results = $_SQL['dexocard']->update("store_item", $update_cols,
               [
                  "store_item_id" => $_PARAM['id']
               ]);

            // Print Results
               $_JSON_PRINT->success(); 
               $_JSON_PRINT->response();
               $_JSON_PRINT->print();

            break;
         }

         case 'DELETE':
         {
            break;
         }
    }

	function getQuery_Sets($_FILTERS_ACTIVE, $_BLOC_SELECT, $_BLOC_WHERE, $_BLOC_LIMIT = NULL)
	{
		global $_TABLE_LIST;

		// Assemblage requête SQL
			return "
				SELECT 

				" . $_BLOC_SELECT . "

				FROM `" . $_TABLE_LIST['dexocard'] . "`.`store_item`

				LEFT JOIN `" . $_TABLE_LIST['dexocard'] . "`.`store_url` ON `" . $_TABLE_LIST['dexocard'] . "`.`store_url`.`store_url_id` = `store_item_urlid`
				
				" . ($_BLOC_WHERE ? "WHERE " . substr($_BLOC_WHERE, 0, strlen($_BLOC_WHERE) - 4) : '') . "

				ORDER BY 
					`" . $_TABLE_LIST['dexocard'] . "`.`store_item`.`store_item_id` ASC

				" . ($_BLOC_LIMIT ? $_BLOC_LIMIT : '') . "
				;
			";
	}
?>