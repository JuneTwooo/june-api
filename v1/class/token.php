<?php
   class token
   {
      private $_publicKey;
      public $_access;

      public function setKey($key)  { $this->_publicKey  = $key; }

      public function checkAccess($product, $key)
      {
         global $_JSON_PRINT;

         if (!$this->_access[$product][$key])
         {
            $_JSON_PRINT->fail("Access denied for this token"); 
            $_JSON_PRINT->print(); 
         }

         return true;
      }

      public function auth()
      {
         global $_JSON_PRINT;

         // aucune clé définie
         if (!$this->_publicKey)
         {
            $_JSON_PRINT->fail("Public key is required");
            $_JSON_PRINT->print();
         }

         switch ($this->_publicKey)
         {
            case "dexocard_oiql4ys4w0nxq89" :
            {
               $this->_access['dexocard']['get_tcgo_code'] = true;

               break;
            }

            default:
            {
               usleep(1000000);
               $_JSON_PRINT->fail("Wrong public key");
               $_JSON_PRINT->print();

               break;
            }
         }
         $_SESSION['token'] = $this->_publicKey;
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