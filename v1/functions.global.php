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

   function uploadFile_Image
   (
      array $_FILE,
      string $dir_Base,
      string $dir_Target,
      string $file_Target,
      bool $toWEBP = true,
      array $fileTypeAccepted = array
      (
         'jpg',
         'jpeg',
         'png',
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
                  $image_filename = $file_Target . '.' . $imageFileType;

                  if ($toWEBP)
                  {
                     $buffer_image = null;
                     switch ($imageFileType)
                     {
                        case 'jpg':
                        case 'jpeg':
                        {
                           $buffer_image = imagecreatefromjpeg($dir_target_concat . $file_Target . '.' . $imageFileType);
                           break;
                        }

                        case 'png':
                        {
                           $buffer_image = imagecreatefrompng($dir_target_concat . $file_Target . '.' . $imageFileType);
                           break;
                        }
   
                        default:
                        {
                           break;
                        }
                     }

                     if ($buffer_image)
                     {
                        $image_filename = $file_Target . '.webp';
                        imagewebp($buffer_image, $dir_target_concat . $file_Target . '.webp');
                        unlink($dir_target_concat . $file_Target . '.' . $imageFileType);
                     }

                     $finalName  = $dir_Target . $image_filename;
                     $finalName  = preg_replace('#/+#','/', $finalName);

                     $img_RemoveWhiteBG = imagecreatefromwebp($dir_Base . $dir_Target . $image_filename);
                     $img_RemoveWhiteBG = imagecropauto($img_RemoveWhiteBG, IMG_CROP_WHITE);
                     imagewebp($img_RemoveWhiteBG, $dir_Base . $dir_Target . $image_filename);

                     return array('success' => 1, 'filename' => $finalName);
                  }
               }

               return array('success' => 0, 'raison' => null);
         }
         
      // Fin avec erreur
         return array('success' => 0, 'raison' => 'image not found');
   }

   function cleanTitleURL($title, $maxLength = 20)
   {
      $title = str_replace(' ', '-', $title);
      $title = str_replace('--', '-', $title);
      return substr(preg_replace("/[^A-Za-z0-9 ]/", '-', $title), 0, 19);
   }
?>