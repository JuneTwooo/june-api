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

         if (!$this->_access[$product][$key])
         {
            $_JSON_PRINT->fail("access denied for this token"); 
            $_JSON_PRINT->print();
            exit();
         }

         return true;
      }

      public function auth()
      {
         global $_JSON_PRINT;

         // aucune clé définie
         if (!$this->_publicKey)
         {
            $_JSON_PRINT->fail("public key is required");
            $_JSON_PRINT->print();
            exit();
         }

         switch ($this->_publicKey)
         {
            case "dexocard_oiql4ys4w0nxq89" :
            {
               $this->_access['dexocard']['get_tcgo_code'] = true;

               return true;
               break;
            }

            default:
            {               
               $_JSON_PRINT->fail("wrong public key");

               usleep(1000000);
               $_JSON_PRINT->print();
               
               return false;
               break;
            }
         }
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