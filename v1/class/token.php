<?php
   class token
   {
      private $_publicKey;
      public $_access;

      public function setKey($key)  { $this->_publicKey  = $key; }
      public function getKey()      { return $this->_publicKey; }

      public function checkAccess($product, $key)
      {
         global $_JSON_PRINT;
         global $_METHOD;

         // search response
         $access_response = (empty($this->_access[$product]) ? NULL : $this->_access[$product]);
         foreach (explode('/', $key) as $route) { $access_response = (empty($access_response[$route]) ? NULL : $access_response[$route]); }

         if (empty($access_response[$_METHOD]))
         {
            $_JSON_PRINT->fail("access denied"); 
            $_JSON_PRINT->print();
         }

         return true;
      }

      public function auth()
      {
         global $_JSON_PRINT;
         global $_MYSQL;
         global $_PHPFASTCACHE;
         global $_DATA_DEBUG;

         // aucune clé définie
         if (!$this->_publicKey)
         {
            $_JSON_PRINT->fail("public key is required");
            $_JSON_PRINT->print();
         }
         $_PHPFASTCACHE->clear();

         $_CACHE_KEY = md5('token-access-' . $this->_publicKey);
         $_CACHE[$_CACHE_KEY] = $_PHPFASTCACHE->getItem($_CACHE_KEY);
         if (!@$_CACHE[$_CACHE_KEY]->isHit())
         {
            $_SQL          = $_MYSQL->connect(array("api"));
            $result        = $_SQL['api']->query("SELECT `token_access` FROM `api`.`token` WHERE `token_id` = :token_id;", [":token_id" => $this->_publicKey])->fetch(PDO::FETCH_ASSOC);   
            $result        = (empty($result) ? array() : json_decode($result['token_access'], true));

            setCache($_CACHE[$_CACHE_KEY], $result, (86400 * 30));
            array_push($_DATA_DEBUG, array('token' => array('from_db_cache' => 0)));
         }
         else
         {
            $result = $_CACHE[$_CACHE_KEY]->get();

            array_push($_DATA_DEBUG, array('token' => array('from_db_cache' => 1)));
         }

         $this->_access = $result;
      }

      function __construct()
      {
         $this->_access    = array
         (
            'dexocard'  => array
            (
               'get_tcgo_code'   => false,
            ),
         );
      }
   }
?>