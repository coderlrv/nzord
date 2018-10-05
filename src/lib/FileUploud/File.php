<?php
namespace NZord\Lib\FileUploud;
use Intervention\Image\ImageManager;

class File{

    protected $file= null;
    protected $pathInfo = null;
    
    function __construct($file){
        $this->file = $file;
    }
    /**
     * Retorna nome original do arquivo
     *
     * @return string
     */
    public function getName(){
        return $this->file->getClientFilename();
    }
    /**
     * Retorna nome temporário do arquivo uploud
     *
     * @return string
     */
    public function getNameTmp(){
        return  $this->file->file;
    }

    /**
     * Retornar MediaType do arquivo. Ex: 'image/bmp','image/jpg'
     *
     * @return string
     */
    public function getType(){
        return $this->file->getClientMediaType();
    }
    /**
     * Retorna extensão original do arquivo.
     *
     * @return string
     */
    public function getExtesionOrin(){
        $extension = pathinfo($this->file->getClientFilename(), PATHINFO_EXTENSION);
        return $extension;
    }
    /**
     * Retorna tamanho do arquivo 
     *
     * @return int
     */
    public function getSize(){
        return $this->file->getSize();
    }
    /**
     * Checa se arquivo é uma arquivo valido.
     *
     * @return boolean
     */
    public function isDoc(){
        $types = [
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'application/msword',
            'application/vnd.oasis.opendocument.text',
            'application/vnd.oasis.opendocument.presentation',
            'application/vnd.oasis.opendocument.spreadsheet'
        ];
        return in_array($this->file->getClientMediaType(),$types);
    }
    /**
     * Checa se arquivo é do tipo imagem.
     *
     * MediaType = 'image/pjpeg','image/bmp','image/jpg','image/jpeg','image/gif','image/png'
     * @return boolean
     */
    public function isImage(){
        $types = ['image/pjpeg','image/bmp','image/jpg','image/jpeg','image/gif','image/png'];
        return in_array($this->file->getClientMediaType(),$types);
    }
    /**
     * Move arquivo para caminho passado por paramentro. 
     * Preciso passa nome arquivo junto.
     *
     * @param string $caminho
     * @return string
     */
    function move($caminho){
        list( $dirname,$filename ) = array_values( pathinfo($caminho));
        $this->createdDir($dirname);
        if(!$filename){
            $basename = bin2hex(random_bytes(12)); // see http://php.net/manual/en/function.random-bytes.php
            $filename = sprintf('%s.%0.8s', $basename, $this->getExtesionOrin());

            $caminho .= $filename;
        }

        if($this->file->moveTo($caminho)){
            return $filename;
        }else{
            return null;
        }
    }
    /**
     * Move arquivo e faz resize com tamanho passado.
     * Preciso passa nome arquivo junto.
     *
     * @param string $caminho
     * @param integer $witdh
     * @param integer $height
     * @return void
     */
    function moveImage($caminho,$witdh=800,$height=600){
        if(!$this->isImage()){
            return false;
        }

        list( $dirname ) = array_values( pathinfo($caminho) );
        $this->createdDir($dirname);
        try{
            // create an image manager instance with favored driver
            $manager = new ImageManager(array('driver' => 'gd'));
            // read image from temporary file
            $img = $manager->make($this->getNameTmp());

            // resize image
            $img->resize($witdh, $height);

            // save image
            $img->save($caminho);

            return true;
        }catch(Exception $ex){
            return false;
        }
    }

    private function createdDir($path){
        if(!is_dir($path)){
            mkdir($path,0777,true);
        }
    }
}
?>