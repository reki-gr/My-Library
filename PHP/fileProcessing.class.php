<?
    class FileProcessing {
        public $fileInfo;
        private $db;

        public function __construct()
        {
            // DB connection
            include_once $_SERVER["DOCUMENT_ROOT"]."/include/db_config.php";
            global $pdo;

            $this->db = $pdo;
        }

        public function upload($type, $path, $sizeLimit)
        {
            /**
             * @param array $this->fileInfo
             * @param string $type
             * @param string $path
             * @param int $sizeLimit // megabytes
             * @return array||boolean
             */
            $fileInfo       = $this->fileInfo;
            $fileName       = $fileInfo['name'];
            $fileTmpName    = $fileInfo['tmp_name'];
            $fileError      = $fileInfo['error'];
            $fileSize       = $fileInfo['size'];

            // If you want to add more types, add them to the array($mimeType)
            $mimeType       = array("image");

            // Error check
            if($fileError !== 0) {
                echo "There was an error uploading your file! : error code {$fileError}";
                return false;
            }

            //  Mime type check
            $mimeType['image'] = [
                // Images
                'png'   => 'image/png',
                'jpe'   => 'image/jpeg',
                'jpeg'  => 'image/jpeg',
                'jpg'   => 'image/jpeg',
                'gif'   => 'image/gif',
                'bmp'   => 'image/bmp',
                'ico'   => 'image/vnd.microsoft.icon',
                'tiff'  => 'image/tiff',
                'tif'   => 'image/tiff',
                'svg'   => 'image/svg+xml',
                'svgz'  => 'image/svg+xml'
            ];

            $mimeType['excel'] = [
                // excel
                'xls'   => 'application/vnd.ms-excel',
                'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ];

            $requestFileExt = mime_content_type($fileTmpName);

            if(empty($mimeType[$type]) || !in_array($requestFileExt, $mimeType[$type])) {
                echo "You cannot upload files of this type!";
                return false;
            }

            // Size limit check
            $megaBytes = 1024 * 1024;
            $sizeLimit = $sizeLimit * $megaBytes;

            if($fileSize > $sizeLimit) {
                echo "Your cannot upload files larger than {$sizeLimit}mb!";
                return false;
            }

            // Path directory check
            if(!is_dir($path)) {
                @mkdir($path, 0777, true);
            }

            // File name format
            $fileName = preg_replace("/[^A-Za-z0-9_\-\.]/", '', $fileName); // Remove special characters
            $fileName = preg_replace("/\s+/", '_', $fileName);              // Remove blank space
            $fileName = preg_replace("/\.(?=.*\.)/", '_', $fileName);       // Remove multiple extension
            $fileName = strtolower($fileName);                              // Convert to lowercase
            
            // If the file name already exists, add the date and time to the file name.
            if(!file_exists($path.$fileName)) {
                $fileName = date('ymdHis').'_'.$fileName;
            } else {
                for($i = 1; $i < 100; $i++) {
                    if(!file_exists($path.date('ymdHis').'_'.$i.'_'.$fileName)) {
                        $fileName = date('ymdHis').'_'.$i.'_'.$fileName;
                        break;
                    }
                }
            }
            
            // Upload to server
            $uploadStatus = move_uploaded_file($fileTmpName, $path.$fileName);

            if($uploadStatus === false) {
                echo "There was an error uploading your file!";
                return false;
            }
            
            // Return upload information
            $upload = [
                'fileName'  => $fileName,
                'filePath'  => $path.$fileName,
                'fileSize'  => $fileSize
            ];

            return $upload;
        }

        public function delete($path)
        {
            /**
             * @param string $this->fileInfo
             * @param string $path
             * @return boolean
             */
            
             $filePath = $path.$this->fileInfo;

            // File path check and delete
            if(file_exists($filePath)) {
                @unlink($filePath);
                echo "File deleted successfully!";
                return true;
            } else {
                return false;
            }
        }

        public function output($path)
        {
            /**
             * @param string $this->fileInfo
             * @param string $path
             * @return string $filePath
             */
            
             $filePath = $path.$this->fileInfo;
             
             return $filePath;
        }

        public function mimeCheck() {
            
        }
    }
?>