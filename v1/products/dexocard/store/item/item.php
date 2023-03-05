<?php
   // check Token
      $_TOKEN->checkAccess('dexocard', 'store/item');
      
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
               $_BLOC_WHERE      = $_BLOC_WHERE . " `store_item_stock` > 0 AND";
                 
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
            // Check parameters
               if (empty($_POST['storeid']))            { $_JSON_PRINT->fail("storeid(int) must be specified"); $_JSON_PRINT->print(); }
               if (empty($_POST['urlid']))              { $_JSON_PRINT->fail("urlid(int) must be specified"); $_JSON_PRINT->print(); }
               if (empty($_POST['url']))                { $_JSON_PRINT->fail("url(string) must be specified"); $_JSON_PRINT->print(); }
               if (empty($_POST['urlimage']))           { $_JSON_PRINT->fail("urlimage(string) must be specified"); $_JSON_PRINT->print(); }
               if (empty($_POST['lang']))               { $_JSON_PRINT->fail("lang(string) must be specified"); $_JSON_PRINT->print(); }
               if (empty($_POST['price']))              { $_JSON_PRINT->fail("price(decimal) must be specified"); $_JSON_PRINT->print(); }
               if (empty($_POST['currency']))           { $_JSON_PRINT->fail("currency(string) must be specified"); $_JSON_PRINT->print(); }
               if (empty($_POST['stock']))              { $_POST['stock'] = 0; }

               $itemid = md5($_POST['url']);

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
                  "store_item_storeid"            => ($_POST['storeid']),
                  "store_item_productid"          => (!empty($_POST['productid'])     ? $_POST['productid']       : NULL),
                  "store_item_urlid"              => ($_POST['urlid']),
                  "store_item_name"               => (!empty($_POST['name'])          ? $_POST['name']            : NULL),
                  "store_item_url"                => ($_POST['url']),
                  "store_item_url_image"          => (!empty($_POST['urlimage'])      ? $_POST['urlimage']        : NULL),
                  "store_item_lang"               => (!empty($_POST['lang'])          ? $_POST['lang']            : NULL),
                  "store_item_price"              => (!empty($_POST['price'])         ? $_POST['price']           : NULL),
                  "store_item_currency"           => (!empty($_POST['currency'])      ? $_POST['currency']        : NULL),
                  "store_item_stock"              => $_POST['stock'],
               ]);

            // Print Results
               $_JSON_PRINT->success(); 
               $_JSON_PRINT->response();
               $_JSON_PRINT->print();

            break;
         }

         case 'PUT':
         {
            // Check parameters
               if (empty($_GET['id']))     { $_JSON_PRINT->fail("id(int) must be specified"); $_JSON_PRINT->print(); }

            // Columns to update
               $update_cols = array();
               if (!empty($_GET['productid']))           { $update_cols = array_merge($update_cols, ["store_item_productid"         => intval($_GET['productid'])]); }

            // Enregistrement SQL
               if (!$update_cols)
               {
                  $_JSON_PRINT->fail("no data to update"); $_JSON_PRINT->print();
               }

               $_SQL          = $_MYSQL->connect(array("dexocard"));
               
               $results = $_SQL['dexocard']->update("store_item", $update_cols,
               [
                  "store_item_id" => $_GET['id']
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