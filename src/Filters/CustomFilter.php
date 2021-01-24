<?php
namespace Habemus\Vacuum\Filters;

use \Closure;
use \InvalidArgumentException;
use \ReflectionFunction;

class CustomFilter {
	protected $closure;
	protected $sanitizer;
	protected $after;

	public function __construct($callback,$sanitizer = null,$execute_after = false){

		$this->closure = null;
		$this->sanitizer = null;
		$this->after = (boolean) $execute_after;

		if(!($callback instanceof Closure)){
			throw new InvalidArgumentException('Invalid Closure.');
		}

		$reflection = new ReflectionFunction($callback);
		$arguments  = $reflection->getParameters();

		if(count($arguments) != 1){
			throw new InvalidArgumentException('Invalid parameters: 1 expected ($value).');
		}



		if(!is_null($sanitizer) && !($sanitizer instanceof Closure)){
			throw new InvalidArgumentException('Invalid Sanitizer.');
		}

		$reflection = new ReflectionFunction($sanitizer);
		$arguments  = $reflection->getParameters();

		if(count($arguments) != 1){
			throw new InvalidArgumentException('Invalid parameters: 1 expected ($value).');
		}



		$this->sanitizer = $sanitizer;
		$this->closure = $callback;
	}

	public function validate($param){

		if(is_null($this->closure)){
			throw new InvalidArgumentException('Invalid Closure.');
		}

		return $this->closure->__invoke($param);
	}


	public function sanitize($param){
		if(is_null($this->sanitizer)){
			return $param;
		}

		return $this->sanitizer->__invoke($param);
	}

	public function executeAfter(){
		return $this->after;
	}

}
