<?php
namespace Habemus\Vacuum;

use \RuntimeException;
use \Closure;

class Request {
    protected $method;
    protected $data;
    protected $validator;
    private $validated;
    private $has_run;

    public function __construct(array $custom_data = null){

        if(!is_null($custom_data)){
            $this->data = $custom_data;
        }else{
            //construct from globals
            $this->method = $_SERVER['REQUEST_METHOD'];
            switch($this->method)
            {
                case 'GET': $this->data = $_GET; break;
                case 'POST': $this->data = array_merge($_POST,$_FILES); break;
                default:
                    $this->data = [];
            }
        }

        $this->validator = null;
        $this->validated = [];
        $this->has_run = false;
    }

    public function validate(array $rules,array $messages = null,Closure $sanitizer = null){

        if($this->has_run){
            throw new RuntimeException('Request already validated.');
        }

        if(is_null($this->validator)){
            $this->validator = new Cleaner($this->data,$messages,$sanitizer);
        }

        if(!$this->isEmpty()){
            $validated = $this->validator->validate($rules);

            if($this->validator->isValid()){
                $this->validated = $validated;
            }
        }

        $this->has_run = true;

        return $this->validated;
    }

    public function isValid(){
        if($this->validator){
            return $this->validator->isValid();
        }

        return false;
    }

    public function getErrors(){

        if($this->validator){
            return $this->validator->errors();
        }

        return [];
    }

    public function isEmpty(){
        return empty($this->data);
    }

    public function getValidated(){
        return $this->validated;
    }

    public function getData(){
        return $this->data;
    }


}
