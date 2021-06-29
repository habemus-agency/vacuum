<?php
namespace Habemus\Vacuum;

use Symfony\Component\Mime\MimeTypes;
use \RuntimeException;
use \UnexpectedValueException;

class FileUpload extends File {
	//native data
	protected $name;
	protected $tmp_name;
	protected $type;
	protected $error;
	protected $size;


	public static function isNativeFileUploadData($upload_data){
		if(is_array($upload_data)){
			if(!array_diff([ 'name','type', 'tmp_name','error','size' ], array_keys($upload_data))){
				return true;
			}
		}

		return false;
	}

	public function __construct(array $php_native_file_data){

		$this->name = '';
		$this->tmp_name = '';
		$this->type = '';
		$this->error = 4;
		$this->size = 0;


		foreach ($php_native_file_data as $key => $value) {

			if(!property_exists($this,$key)){
				return;
			}

			$this->{$key} = $value;
		}

		if(!$this->isEmpty()){

			if(!is_uploaded_file($this->tmp_name)){
				throw new \RuntimeException("Not a valid uploaded file.");
			}

			//get path
			$exp_path = explode("/", $this->tmp_name);
			$raw_name = end($exp_path);
			$this->path = str_replace($raw_name,'',$this->tmp_name);

			//get filename
			$exp_name = explode('.',$this->name);
			$temp_extension = strtolower(end($exp_name));
			$this->filename = str_replace('.'.$temp_extension,'',$this->name);

			/**
			 * Laravel Filesystem
			 */

			//guess extension from mime
			if (! class_exists(MimeTypes::class)) {
				throw new RuntimeException(
					'To enable support for guessing extensions, please install the symfony/mime package.'
				);
			}
	
			$this->extension = (new MimeTypes)->getExtensions($this->type)[0] ?? null;

			if(is_null($this->extension)){
				throw new UnexpectedValueException("MimeType not supported");
			}

			//rename file with original filename and correct extension
			$new_path = $this->path . $this->filename . '.' . $this->extension;

			$trials = 0;
			while(file_exists($new_path) && $trials < 128){
				$trials++;
				$new_path = $this->path . $this->filename . '-' . $trials . '.' . $this->extension;
			}

			if(!$this->rename($new_path)){
				throw new RuntimeException("Could not rename uploaded file.");
			}
		}

	}

	public function isEmpty(){
		if($this->size == 0 || $this->error != UPLOAD_ERR_OK){
			return true;
		}

		return false;
	}

	public function getName(){
		return $this->name;
	}

	public function rename($new_path){

		if(rename($this->tmp_name,$new_path)){
			$this->tmp_name = $new_path;
			return true;
		}

		return false;
	}

	public function store($path){
		if(copy($this->tmp_name,$path)){
			return new File($path);
		}

		return false;
	}


	public function getPath(){
		return $this->tmp_name;
	}


	public function __destruct(){
		if(file_exists($this->tmp_name)){
			unlink($this->tmp_name);
		}
	}

}
