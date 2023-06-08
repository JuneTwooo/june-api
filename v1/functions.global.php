<?php
   function isJson($string)
   {
      if (empty($string)) { return false; }

      json_decode($string);

      return json_last_error() === JSON_ERROR_NONE;
   }

   function get_operand_array()
   {
      return array
      (
         "=", 
         ">", 
         "<", 
         ">=", 
         "<=", 
         "LIKE"
      );
   }

   function convert_file_to_webp($file, $file_target)
   {
      $image_MimeType   = mime_content_type($file);

      $buffer_image = null;
      switch ($image_MimeType)
      {
         case 'image/jpg':
         case 'image/jpeg':
         {
            $buffer_image = imagecreatefromjpeg($file);
            break;
         }

         case 'image/png':
         {
            $buffer_image = imagecreatefrompng($file);
            break;
         }

         case 'image/webp':
         {
            $buffer_image = imagecreatefromwebp($file);
            break;
         }

         case 'image/avif':
         {
            $buffer_image = imagecreatefromavif($file);
            break;
         }

         default:
         {
            return array('success' => 0, 'raison' => "mime type " . $image_MimeType . " unknow");
            break;
         }
      }

      if ($buffer_image)
      {
         imagewebp($buffer_image, $file_target);
         return array('success' => 1);
      }
   }

   function uploadFile_Image(
      array $_FILE,
      string $dir_Base,
      string $dir_Target,
      string $file_Target,
      bool $autocrop = false,
      bool $toWEBP = true,
      array $fileTypeAccepted = array
      (
         'jpg',
         'jpeg',
         'png',
         'avif',
         'webp'
      )
   )
   {
      global $_JSON_PRINT;

      // slashes
         $dir_Base      = '/' . $dir_Base . '/';
         $dir_Target    = '/' . $dir_Target . '/';
         $file_Target   = '/' . strtolower($file_Target);

      // Check filesize
         if ($_FILE['size'] >= 14000000)
         {
            return array('success' => 0, 'raison' => "max upload filesize is over the limit of 14mb");
         }
         else if ($_FILE['size'] == 0)
         {
            return array('success' => 0, 'raison' => "filesize of image is 0 byte, can mean that the filesize reached the upload_max_filesize");
         }

      // prepare to upload
         $image_info = getimagesize($_FILE["tmp_name"]);
         if ($image_info)
         {
            // check if filetype is allowed
               $fileName_Exploded = explode('.', $_FILE['name']);
               $imageFileType = strtolower(end($fileName_Exploded));
               if (!in_array($imageFileType, $fileTypeAccepted))
               {
                  return array('success' => 0, 'raison' => "filetype " . $imageFileType . " is not allowed");
               }

            // create dir if not exist
               $dir_target_concat = $dir_Base . '/' . $dir_Target;
               if (!is_dir($dir_target_concat)) { mkdir($dir_target_concat); }

            // Move upload to dir + compress to webp if needed
               if (move_uploaded_file($_FILE["tmp_name"], $dir_target_concat . $file_Target . '.' . $imageFileType))
               {
                  $image_filename   = $file_Target . '.' . $imageFileType;
                  $image_MimeType   = mime_content_type($dir_target_concat . $file_Target . '.' . $imageFileType);

                  if ($toWEBP)
                  {
                     $buffer_image = null;
                     switch ($image_MimeType)
                     {
                        case 'image/jpg':
                        case 'image/jpeg':
                        {
                           $buffer_image = imagecreatefromjpeg($dir_target_concat . $file_Target . '.' . $imageFileType);
                           break;
                        }

                        case 'image/png':
                        {
                           $buffer_image = imagecreatefrompng($dir_target_concat . $file_Target . '.' . $imageFileType);
                           break;
                        }

                        case 'image/webp':
                        {
                           $buffer_image = imagecreatefromwebp($dir_target_concat . $file_Target . '.' . $imageFileType);
                           break;
                        }

                        case 'image/avif':
                        {
                           $buffer_image = imagecreatefromavif($dir_target_concat . $file_Target . '.' . $imageFileType);
                           break;
                        }
   
                        default:
                        {
                           return array('success' => 0, 'raison' => "mime type " . $image_MimeType . " unknow");
                           break;
                        }
                     }

                     if ($buffer_image)
                     {
                        $image_filename = $file_Target . '.webp';
                        imagepalettetotruecolor($buffer_image);
                        imagewebp($buffer_image, $dir_target_concat . $file_Target . '.webp');
                        
                        if ($imageFileType != 'webp') { unlink($dir_target_concat . $file_Target . '.' . $imageFileType); }
                     }

                     $finalName  = $dir_Target . $image_filename;
                     $finalName  = preg_replace('#/+#','/', $finalName);

                     // remove transparency if autocrop is true
                     if ($autocrop)
                     {
                        $img_RemoveWhiteBG = imagecreatefromwebp($dir_Base . $dir_Target . $image_filename);
                        $img_RemoveWhiteBG = imagecropauto($img_RemoveWhiteBG, IMG_CROP_SIDES);
                        
                        imagewebp($img_RemoveWhiteBG, $dir_Base . $dir_Target . $image_filename);
                     }

                     return array('success' => 1, 'filename' => $finalName);
                  }
               }

               return array('success' => 0, 'raison' => null);
         }
         
      // Fin avec erreur
         return array('success' => 0, 'raison' => 'image not found');
   }

   function cleanTitleURL($title, $maxLength = 32)
   {
      $file_ext         = pathinfo($title, PATHINFO_EXTENSION); 
      $file_name_str    = pathinfo($title, PATHINFO_FILENAME); 

      $search           = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ');
      $replace          = array('A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y');
      $file_name_str    = str_replace($search, $replace, $file_name_str);

      $file_name_str    = str_replace(' ', '-', $file_name_str); 
      $file_name_str    = preg_replace('/[^A-Za-z0-9\-\_]/', '', $file_name_str); 
      $file_name_str    = preg_replace('/-+/', '-', $file_name_str); 

      $clean_file_name  = $file_name_str; 
         
      return $clean_file_name; 
   }

   function cleanURL($title, $maxLength = 32)
   {
      $search  = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ');
      $replace = array('A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y');
      $title   = str_replace($search, $replace, $title);

      $title   = str_replace(' ', '-', $title); 
      $title   = preg_replace('/[^A-Za-z0-9\-\_]/', '', $title); 
      $title   = trim(preg_replace('/-+/', ' ', $title));
      $title   = preg_replace('/ /', '-', $title);
      $title   = strtolower($title);

      $clean_file_name  = ($title); 
         
      return $clean_file_name; 
   }
?>