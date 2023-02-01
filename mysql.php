<?php
   use Medoo\Medoo;

   function SQL_Connect($array_dbs)
   {
      global $_CONFIG;
      $dbs_connections = array();
      
      foreach ($array_dbs as $_DB_TO_CONNECT)
      {
         switch ($_DB_TO_CONNECT)
         {
            case 'dexocard_api':
            case 'dexocard_site':
            {
               $dbs_connections[$_DB_TO_CONNECT] = new Medoo
               ([
						'type'      => 'mysql',
						'host'      => '192.168.1.252',
						'database'  => 'tcg',
						'username'  => 'tcgpokemon',
						'password'  => '6mhwGRNc@wY(7*4o',
					
						'charset'   => 'utf8mb4',
						'collation' => 'utf8mb4_general_ci',
						
						'error'     => ($_CONFIG['DEBUG'] ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT),

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

               break;
            }
         }
      }

      return $dbs_connections;
   }
?>