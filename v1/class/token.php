<?php
   class token
   {
      private $_publicKey;

      function __construct()
      {
         $this->_publicKey    = null;
      }

      public function setKey($key)  { $this->_publicKey  = $key; }

      public function auth()
      {
         global $_JSON_PRINT;

         // aucune clé définie
         if (!$this->_publicKey)
         {
            $_JSON_PRINT->fail("Public key is required");
            $_JSON_PRINT->print();
         }

         echo 'okkk';
      }
   }
?>