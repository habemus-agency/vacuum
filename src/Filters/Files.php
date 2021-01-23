<?php
namespace Habemus\Vacuum\Filters;

use Habemus\Vacuum\FileUpload;

trait Files {

    /** files validation  */

	private function filter_file($file){
		return ($file instanceof FileUpload);
    }
    

    private function is_php_file(FileUpload $file){
        $phpExtensions = [
            'php', 'php3', 'php4', 'php5', 'phtml',
        ];

        if (in_array($file->getExtension(),$phpExtensions)) {
            return true;
        }

        return false;
    }


	private function filter_mimes($file,$params){

		if(!$this->filter_file($file)){
			return false;
		}

        //exclude unwanted php file upload
		if (!in_array('php', $params)) {
			if($this->is_php_file($file)){
				return false;
			}
		}

		foreach ($params as $type) {
			if ($file->getExtension() == $type) {
				return true;
			}

		}

		return false;
	}
}