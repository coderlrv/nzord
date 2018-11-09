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
        if(!$this->uploadedFiles) return null;
        
        $dataFiles = $this->uploadedFiles[$name];
        if(is_object($dataFiles)){
     
            if($dataFiles->getError() === UPLOAD_ERR_OK){
                return new File($dataFiles);
            }else{
                $fileName = $dataFiles->getClientFilename();
                throw new FileUploudException("NameFile: $fileName, Erro:". $this->codeToMessage($dataFiles->getError()));
            }
        }else{
            $files = [];
            foreach($dataFiles as $file){
                if($file->getError() === UPLOAD_ERR_OK){
                    array_push($files,new File($file));
                }else{
                    $fileName = $file->getClientFilename();
                    throw new FileUploudException("NameFile: $fileName, Erro:". $this->codeToMessage($file->getError()));
                }
            }

            return $files;
        }
    }

    private function codeToMessage($code) 
    { 
        switch ($code) { 
            case UPLOAD_ERR_INI_SIZE: 
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini"; 
                break; 
            case UPLOAD_ERR_FORM_SIZE: 
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break; 
            case UPLOAD_ERR_PARTIAL: 
                $message = "The uploaded file was only partially uploaded"; 
                break; 
            case UPLOAD_ERR_NO_FILE: 
                $message = "No file was uploaded"; 
                break; 
            case UPLOAD_ERR_NO_TMP_DIR: 
                $message = "Missing a temporary folder"; 
                break; 
            case UPLOAD_ERR_CANT_WRITE: 
                $message = "Failed to write file to disk"; 
                break; 
            case UPLOAD_ERR_EXTENSION: 
                $message = "File upload stopped by extension"; 
                break; 

            default: 
                $message = "Unknown upload error"; 
                break; 
        } 
        return $message; 
    } 
}

