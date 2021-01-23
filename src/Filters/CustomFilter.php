<?php
namespace Habemus\Vacuum\Filters;

use \Closure;
use \InvalidArgumentException;
use \ReflectionFunction;

class CustomFilter {
	protected $closure;

	public function __construct($callback){

		$this->closure = null;

		if(!($callback instanceof Closure)){
			throw new InvalidArgumentException('Invalid Closure.');
		}

		$reflection = new ReflectionFunction($callback);
		$arguments  = $reflection->getParameters();

		if(count($arguments) != 1){
			throw new InvalidArgumentException('Invalid parameters: 1 expected ($value).');
		}


		$this->closure = $callback;
	}

	public function validate($param){

		if(is_null($this->closure)){
			throw new InvalidArgumentException('Invalid Closure.');
		}

		return $this->closure->__invoke($param);
	}

}
