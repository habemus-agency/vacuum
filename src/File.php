<?php
namespace Habemus\Vacuum;

use \RuntimeException;
use \UnexpectedValueException;

class File {

	protected $extension;
	protected $filename; //name without extension
	protected $path; //path without filename and extension

    protected $size;
    protected $type;


	public function __construct(string $fullpath){


		if(!file_exists($fullpath)){
            throw new RuntimeException("File doesn't exists.");
        }

        $this->type = mime_content_type($fullpath);
        $this->size = filesize($fullpath);
        $this->setInfo($fullpath);
	}

    protected function setInfo($fullpath){

        //get path
        $exp_path = explode("/", $fullpath);
        $raw_name = end($exp_path);
        $this->path = str_replace($raw_name,'',$fullpath);

        //get filename
        $exp_name = explode('.',$raw_name);
        $this->extension = strtolower(end($exp_name));
        $this->filename = str_replace('.'.$this->extension,'',$raw_name);
    }

	public function isEmpty(){
		if($this->size == 0){
			return true;
		}

		return false;
	}

	public function getFilename(){
		return $this->filename . '.' . $this->extension;
	}

	public function getMime(){
		return $this->type;
	}


	public function getExtension(){
		return $this->extension;
	}

	public function getSize(){
		return $this->size;
	}

	public function move($new_path){

        if(is_dir($new_path)){
            return false;
        }

		if(rename($this->getPath(),$new_path)){
			$this->setInfo($new_path);
			return true;
		}

		return false;
	}


	public function getPath(){
		return $this->path . $this->getFilename();
	}

}
