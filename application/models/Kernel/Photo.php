<?php

class Application_Model_Kernel_Photo {

    private $_idPhoto;
    private $_photoPath = '';
    private $_photoAlt;
    private $_photoPosition;

    const FULL_IMAGE = 1;
    const PREV_IMAGE = 2;
    const PREV_GALLERY = 3;
    const PREV_GALLERY_HALF = 5;
    const PREV_SITE = 4;

    const SAVE_PATH = '/static/default/upload/images/';
    const TMP_DIR = '/tmp/adminka_images/';

    const ERROR_CUT = 'UNKNOW IMAGE CUT TYPE';
    const ERROR_NO_PHOTO = 'Фото не загружено';
    const ERROR_NOT_FOUND = 'Photo not found';
    const ERROR_WRONG_FILE = 'Можно загружать только картинки';

    public function __construct($idPhoto, $photoPath, $photoAlt, $photoPosition) {
        $this->_idPhoto = $idPhoto;
        $this->_photoPath = $photoPath;
        $this->_photoAlt = $photoAlt;
        $this->_photoPosition = $photoPosition;
    }

    public function getPosition() {
        return intval($this->_photoPosition);
    }

    /**
     * @param int $position
     * @return Application_Model_Kernel_Photo
     */
    public function setPosition($position) {
        $this->_photoPosition = intval($position);
        return $this;
    }
    
    public function increasePosition() {
        $db = Zend_Registry::get('db');
        $db->update('photos', array('photos.photoPosition' => new Zend_Db_Expr('photos.photoPosition + 1')));
    }

    public function save() {
        $db = Zend_Registry::get('db');
        $data = array(
            "idPhoto" => $this->_idPhoto,
            "photoPath" => $this->_photoPath,
            "photoAlt" => $this->_photoAlt,
            "photoPosition" => $this->_photoPosition
        );
        if (is_null($this->_idPhoto)) {
            $this->increasePosition();
            $db->insert('photos', $data);
            $this->_idPhoto = $db->lastInsertId();
        } else {
            $db->update('photos', $data, 'idPhoto = ' . intval($this->_idPhoto));
        }
//        $this->clearCache();
        return $this;
    }

    public function getId() {
        return $this->_idPhoto;
    }

    /**
     * Deleting photo file and record from db
     * @return void
     */
    public function delete() {
        if( $this->_photoPath != ''){
            $db = Zend_Registry::get('db');
            unlink(PUBLIC_PATH . self::SAVE_PATH . $this->_photoPath);
            $db->delete('photos', "photos.idPhoto = {$this->_idPhoto}");
//        $this->clearCache();
        }
        return $this;
    }

    public static function getAllTruePhotos() {
        $db = Zend_Registry::get('db');
        $select = $db->select();
        $select->from('photos');
        $photos = array();
        if (false !== ($result = $db->fetchAll($select))) {
            foreach ($result as $photo) {
                $photos[] = new self($photo->idPhoto, $photo->photoPath, $photo->photoAlt, $photo->photoPosition);
            }
        }
        return $photos;
    }

    private function clearCache() {
        if (!is_null($this->getId())) {
//            $cachemanager = Zend_Registry::get('cachemanager');
//            $cache = $cachemanager->getCache('photo');
//            if (!is_null($cache)) {
//                $cache->remove($this->getId());
//            }
        }
    }

    /**
     * @access public
     * @static
     * @param int $idPhoto
     * @throws Exception
     * @return Application_Model_Kernel_Photo
     */
    public static function getById($idPhoto) {
        $idPhoto = intval($idPhoto);
//        $cachemanager = Zend_Registry::get('cachemanager');
//        $cache = $cachemanager->getCache('photo');
//        if (($photo = $cache->load($idPhoto)) !== false) {
//            return $photo;
//        } else {
            $db = Zend_Registry::get('db');
            $select = $db->select()->from('photos');
            $select->where('idPhoto = ?', intval($idPhoto));
            if (false !== ($photoData = $db->fetchRow($select))) {
                $photo = new self($photoData->idPhoto, $photoData->photoPath, $photoData->photoAlt, $photoData->photoPosition);
//                $cache->save($photo);
                return $photo;
            } else
                throw new Exception(self::ERROR_NO_PHOTO);
//        }
    }

    public function getPhotoDir() {
        if (preg_match('/^(.*)\/([0-9a-z]{4})\.(jpg|gif|jpeg|png)$/i', $this->getPhotoPath(), $data))
            return $data[1];
        else
            throw new Exception('NOT SEE PHOTO SAVE DIR');
    }

    public function getPhotoPath() {
        return $this->_photoPath;
    }

    public function getPath($type, $priview = false) {
        $view = Zend_Layout::getMvcInstance()->getView();
        $path = '/image.php?';
        
        if( $priview !== false ){
            try{
                @list($imageWidthCheck, $imageHeightCheck) = getimagesize(PUBLIC_PATH .self::SAVE_PATH .$this->_photoPath);
                if( $imageHeightCheck >= $imageWidthCheck ){
                    @$ratio = $imageHeightCheck / $imageWidthCheck;
                    $height = $type;
                    @$width = round($height/$ratio);
                } else {
                    @$ratio = $imageHeightCheck / $imageWidthCheck;
                    $width = $type;
                    @$height = round($width/$ratio);
                }

                @$proportions = intval( $width / $height* 100) / 100;


                $path .= "width=$width&amp;";
                $path .= "height=$height&amp;";
            } catch (Exception $e){

            }

            //$path .= "cropratio=$proportions:1&amp;";
        } else {
            switch ($type) {
                case self::PREV_IMAGE:
                    $path .= 'width=181&amp;';
                    $path .= 'height=181&amp;';
                    $path .= 'cropratio=1:1&amp;';
                    break;
                case self::PREV_GALLERY:
                    $path .= 'width=150&amp;';
                    $path .= 'height=150&amp;';
                    $path .= 'cropratio=1:1&amp;';
                    break;
                case self::PREV_GALLERY_HALF:
                    $path .= 'width=75&amp;';
                    $path .= 'height=75&amp;';
                    $path .= 'cropratio=1:1&amp;';
                    break;
                case self::PREV_SITE:
                    $path .= 'width=110&amp;';
                    $path .= 'height=110&amp;';
                    $path .= 'cropratio=1:1&amp;';
                    break;
                case self::FULL_IMAGE:
                    return self::SAVE_PATH . $this->_photoPath;
                    break;
                default:
                    $type = explode(':', $type);
                    $proportions = intval($type[0] / $type[1] * 100) / 100;
                    $path .= "width={$type[0]}&amp;";
                    $path .= "height={$type[1]}&amp;";
                    $path .= "cropratio=$proportions:1&amp;";
                    break;
            }   
        }
        return $path .= 'image=' . self::SAVE_PATH . $this->_photoPath;
    }

    public function validate($tmpName) {
        $imageInfo = @getimagesize($tmpName);
        if (!$imageInfo)
            throw new Exception(self::ERROR_WRONG_FILE);
        if (!preg_match('/image/i', $imageInfo['mime']))
            throw new Exception(self::ERROR_WRONG_FILE);
        return $this;
    }

    public function upload($tmpName, $name) {
        $this->_photoPath = '';
        if (!preg_match('/^.*\.(jpg|gif|jpeg|png)$/i', $name, $expansion))
            throw new Exception('Можно загружать только картинки c расширением jpg,png,gif');
        $randomPath = md5(uniqid() . microtime(true));
        $i = 0;
        $step = 4;
        while ($i < strlen($randomPath) - $step) {
            $segment = (substr($randomPath, $i, $step));
            $this->_photoPath .= $segment . '/';
            $i+=$step;
        }

        @mkdir(PUBLIC_PATH . self::SAVE_PATH . date('dmY').'/', 0777, true);

        $this->_photoPath .= substr($randomPath, $i, $step) . '.' . strtolower($expansion[1]);
        $this->_photoPath = date('dmY').'/'.$name;
        copy($tmpName, PUBLIC_PATH . self::SAVE_PATH . $this->_photoPath);
        return $this;
    }

    /**
     * hard code
     * work only with linux @todo
     */
    public static function clearPhotos() {
        system('ls -l ' . PUBLIC_PATH . self::SAVE_PATH . ' | wc -l', $startCount);
        system('rm -rf ' . self::TMP_DIR);
        system('mkdir ' . self::TMP_DIR);
        foreach (self::getAllTruePhotos() as $photo) {
            mkdir(self::TMP_DIR . $photo->getPhotoDir(), 0777, true);
            if (copy(PUBLIC_PATH . self::SAVE_PATH . $photo->getPhotoPath(), self::TMP_DIR . $photo->getPhotoPath()) === false) {
                $photo->delete();
            }
        }
        system('rm -rf ' . PUBLIC_PATH . self::SAVE_PATH);
        system('cp -rf ' . self::TMP_DIR . ' ' . PUBLIC_PATH . self::SAVE_PATH);
        system('rm -rf ' . self::TMP_DIR);
        system('ls -l ' . PUBLIC_PATH . self::SAVE_PATH . ' | wc -l', $endCount);
        return intval($startCount) - intval($endCount);
    }
    
    public function movePhotoToTmpDir( $vars ){
        $fileSize = '';
        
        if (isset($vars['qqfile'])) {
            $originalFileName = $vars['qqfile']; // from FF, Chrome  - comes as GET param
            
            $fileFullNameArray = explode('.', $originalFileName);
            $fileExtension = array_pop($fileFullNameArray);
            $fileName = str_replace('.', '', microtime(true)) . '.' . $fileExtension;
            $uploadedFile = PUBLIC_PATH.self::TMP_DIR . '/' . $fileName;

            $input = fopen("php://input", "r");
            $temp = tmpfile();
            $realSize = stream_copy_to_stream($input, $temp);
            fclose($input);

            $target = fopen($uploadedFile, "w");        
            fseek($temp, 0, SEEK_SET);
            stream_copy_to_stream($temp, $target);
            fclose($target);

            $fileSize = $realSize;
        } elseif(isset($vars['qqfile']['name'])) {
            $originalFileName = $vars['qqfile']['name']; // from Opera - comes as simple form element input with type 'file'
            
            $fileFullNameArray = explode('.', $originalFileName);
            $fileExtension = array_pop($fileFullNameArray);
            $fileName = str_replace('.', '', microtime(true)) . '.' . $fileExtension;
            $uploadedFile = PUBLIC_PATH.self::TMP_DIR . '/' . $fileName;
            
            if( !move_uploaded_file($vars['qqfile']['tmp_name'], $uploadedFile) )
                    throw new Exception('Неудалось поместить фото в нужную папку');
            $fileSize = $_FILES['qqfile']['size'];
        } elseif (isset($vars['http_url'])) {
            $url = $vars['http_url'];
            $originalFileName = basename($url);
            $fileFullNameArray = explode('.', $originalFileName);

            if (count($fileFullNameArray)>1) {
                $originalFileName = substr($vars['name'],0,20).'.'.$fileFullNameArray[1];
            }

            $uploadedFile = PUBLIC_PATH.self::TMP_DIR . $originalFileName;
            file_put_contents($uploadedFile, file_get_contents($url));
        }
        return array('tmp'=>$uploadedFile, 'name'=> $originalFileName);
    }
}