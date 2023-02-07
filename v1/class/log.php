<?php
   class logFile
   {
      /*
         LOG LEVEL :
            1 TRACE
            2 DEBUG
            3 INFO
            4 WARN
            5 ERROR
            6 FATAL
      */

      public function write(int $level, int $success, string $type, string $data)
      {
         switch ($level)
         {
            case 1: { return; break; }
            case 2: { break; }
            case 3: { break; }
            case 4: { break; }
            case 5: { break; }
            case 6: { break; }
         }

         global $_MYSQL;
         global $_TOKEN;

         /*
         $_SQL    = $_MYSQL->connect(array("api"));
         $_SQL['api']->query
         ("
            INSERT INTO `api`.`log`
               (log_level, log_host, log_key, log_type, log_data, log_success)
            VALUES
               (:log_level, :log_host, :log_key, :log_type, :log_data, :log_success)
            ;
         ",
            [
               ":log_level"   => $level,
               ":log_host"    => (empty($_SERVER['HTTP_HOST']) ? NULL : $_SERVER['HTTP_HOST']),
               ":log_key"     => $_TOKEN->getKey(),
               ":log_type"    => $type,
               ":log_data"    => $data,
               ":log_success" => $success,
            ]
         );*/
      }
   }

?>