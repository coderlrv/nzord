<?php 
namespace  NZord\Lib\FileUploud;

use Slim\Http\Request;

class FileUploud{
    protected $uploadedFiles;
    
    protected $errors;
    /**
     * 
     * 
     */
    public function __construct(Request $request)
    {
        $this->uploadedFiles = $request->getUploadedFiles();
    }
    /**
     * Retorna lista de arquivos
     *
     * @param string $name - Nome campo file do form
     * @return array|NZord\Lib\FileUploud\File
     */
    public function getFiles($name){
        $dataFiles = $this->uploadedFiles[$name];

        if(is_object($dataFiles)){  
            if($dataFiles->getError() === UPLOAD_ERR_OK){
                return new File($dataFiles);
            }
        }else{
            $files = [];
            foreach($dataFiles as $file){
                if($dataFiles->getError() === UPLOAD_ERR_OK){
                    array_push($files,new File($dataFiles));
                }
            }

            return $files;
        }
    }
}

