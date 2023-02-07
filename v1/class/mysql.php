<?php
   use Medoo\Medoo;

   class MySQL
   {
      private $_connexion = array();

      function __construct()
      {
      }

      public function connect($array_dbs)
      {
         global $_CONFIG;
         
         foreach ($array_dbs as $_DB_TO_CONNECT)
         {
            switch ($_DB_TO_CONNECT)
            {
               case 'api':
               {
                  if (empty($this->_connexion[$_DB_TO_CONNECT]))
                  {
                     $this->_connexion[$_DB_TO_CONNECT] = new Medoo
                     ([
                        'type'      => 'mysql',
                        'host'      => '192.168.1.252',
                        'database'  => 'tcg',
                        'username'  => 'api',
                        'password'  => '.KaB5And/rE/eRX5',
                     
                        'charset'   => 'utf8mb4',
                        'collation' => 'utf8mb4_general_ci',
                        
                        'error'     => ($_CONFIG['DEBUG'] ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_WARNING),
   
                        'logging' 	=> ($_CONFIG['DEBUG'] ? true : false),
                     
                        'option'    => 
                        [
                           PDO::ATTR_CASE => PDO::CASE_NATURAL
                        ],
                     
                        'command'   => 
                        [
                           'SET SQL_MODE=ANSI_QUOTES'
                        ]
                     ]);
                  }
   
                  break;
               }
            }
         }

         return $this->_connexion;
      }
   }
?>