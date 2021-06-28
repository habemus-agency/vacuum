<?php
namespace Habemus\Vacuum;

use \RuntimeException;
use \BadMethodCallException;
use \InvalidArgumentException;

use Habemus\Vacuum\Filters\Attributes;
use Habemus\Vacuum\Filters\Files;
use Habemus\Vacuum\Filters\CustomFilter;

class Cleaner {

	use Files,Attributes;

	private $default_message = '###field### is invalid.';

	protected $messages = [
		'required' => '###field### is required.',
		'email' => '###field### is not a valid e-mail.',
		'url' => '###field### is not a valid url.',
		'alpha_num' => '###field### is not alphanumeric.',
		'alpha' => '###field### must contain only letters.',
		'numeric' => '###field### must be a number.',
		'digits' => '###field### must contain only numbers and must be of length ###params###',
		'mimes' => '###field### mime must be of type ###params###',
		'file' => '###field### must be a valid file upload',
		'size' => '###field### must be of size ###params###',
		'in' => '###field### must be in ###params###',
		'gte' => '###field### must be greater or equal than ###params###',
		'gt' => '###field### must be greater than ###params###',
		'lt' => '###field### must be lower than ###params###',
		'lte' => '###field### must be lower or equal than ###params###',
		'min' => '###field### must be bigger or equal size ###params###',
		'max' => '###field### must be smaller or equal size ###params###',
		'present' => '###field### must be present',
		'integer' => '###field### is not integer',
		'boolean' => '###field### is not boolean',
		'string' => '###field### is not boolean',
		'date' => '###field### is not a valid date',
	];

	protected $custom_messages = [];

	protected $fields = null;
	protected $validated = [];
	protected $errors = null;
	protected $is_valid = false;

	public function __construct($fields,$custom_messages = null,$sanitizer_callback = null){

		if(!is_array($fields)){
			throw new \Exception('Fields must be an array!');
			$fields = [];
		}

		$sanitizer = function($value){

			return $value;
		};

		if(is_callable($sanitizer_callback)){
			$sanitizer = $sanitizer_callback;
		}


		foreach($fields as $k => $v){


			if(is_array($v)){

				if(FileUpload::isNativeFileUploadData($v)){
					$v = new FileUpload($v);
				}else{

					$v = array_map(function($value) use ($sanitizer){
						if(!is_array($value)){
							return $sanitizer($value);
						}
						//TODO:... recursive
					},$v);

				}

			}elseif(!is_object($v)){
				$v = $sanitizer($v);
			}

			$this->fields [$k] = $v;
		}

		if(!empty($custom_messages)){

			if(!is_array($custom_messages)){
				throw new InvalidArgumentException('Messages must be an array!');
			}else{
				$this->custom_messages = $custom_messages;
			}

		}
	}

	public function validate($field_filters){

		$this->is_valid = true;
		$this->errors = [];
		$this->validated = [];

		foreach($field_filters as $field => $multiple_filters){

			$valid = true;
			$nullable = false;
			$bail = false;

			$value = array_key_exists($field,$this->fields) ? $this->fields[$field] : null;

			if(!empty($multiple_filters)){

				$filters = [];

				if(is_string($multiple_filters)){
					$filters = explode('|',$multiple_filters);

				}elseif (is_array($multiple_filters)) {
					$filters = $multiple_filters;

				}

				foreach($filters as $filter){

					$error = false;
					$params = [];

					if(is_string($filter)){
						$params = $this->getFilterParams($filter);

						if(!method_exists($this,'filter_' . $filter)){
							throw new BadMethodCallException("Filter '$filter' doesn't exist.");

						}else{
							
							//validate if field is not nullable or if nullable but data is present
							if(!$nullable or ($nullable && $this->filter_required($value))){
								$error = !$this->{ 'filter_' . $filter }($value,$params,$field,$nullable,$bail);
							}
						}
					}elseif ($filter instanceof CustomFilter) {

						//sanitize if flag after is not set
						if(!$filter->executeAfter()){
							$value = $filter->sanitize($value);
						}

						//validate if field is not nullable or if nullable but data is present
						if(!$nullable or ($nullable && $this->filter_required($value))){
							$error = !$filter->validate($value);
						}

						//sanitize if flag after is set
						if($filter->executeAfter()){
							$value = $filter->sanitize($value);
						}

					}else{
						throw new InvalidArgumentException("Filter is of invalid type.");
					}

					if($error){

						$this->errors [$field] [is_string($filter) ? $filter : 'custom'] = $this->getErrorMessage($filter,$field,$params);
						$valid = false;
						$this->is_valid = false;

						if($bail){
							break 1;
						}

					}
				}

			}


			if($valid){
				$this->validated[$field] = $value;
			}


		}

		return $this->validated;
	}

	public function isValid(){
		return $this->is_valid;
	}


	public function errors(){
		return $this->errors;
	}

	public function validated(){
		return $this->validated;
	}

	public function setDefaultMessage($message){
		$this->default_message = $message;
	}

	private function getErrorMessage($filter,$field,$params){

		$message = '';

		$message = array_key_exists($field,$this->custom_messages) ? $this->custom_messages[$field] : '';

		if(empty($message)){
			if(!is_string($filter)){
				$message = $this->default_message;
			}else{
				$message = array_key_exists($filter,$this->messages) ? $this->messages[$filter] : $this->default_message;
			}
		}

		$name = str_replace(['_','-'],' ',$field);
		$message = str_replace('###field###',ucfirst($name),$message);
		$message = str_replace('###params###',implode(',',$params),$message);

		return $message;
	}

	private function getFilterParams(&$filter){
		$params = [];

		$param = explode(':',$filter);
		$filter = array_shift($param);

		if(!empty($param)){
			$params = explode(',',$param[0]);
		}

		return $params;
	}


	private function getNumber($value){
		return ($value == (int) $value) ? (int) $value : (float) $value;
	}


	/** special filters */

	private function filter_present($value,$params,$field){
		return in_array($field,array_keys($this->fields));
	}

	private function filter_nullable($value,$params,$field,&$nullable){
		$nullable = true;

		return true;
	}

	private function filter_bail($value,$params,$field,&$nullable,&$bail){
		$bail = true;

		return true;
	}

	private function filter_required($value){

		if (is_null($value)) {
			return false;
		} elseif (is_string($value) && trim($value) === '') {
				return false;
		} elseif ((is_array($value) || $value instanceof Countable) && count($value) < 1) {
				return false;
		}elseif ($value instanceof FileUpload){
			return !$value->isEmpty();
		}

		return true;
	}

}
