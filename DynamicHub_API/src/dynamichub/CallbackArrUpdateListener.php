<?php

namespace dynamichub;

use dynamichub\event\memory\Arr;
use dynamichub\event\memory\ArrUpdateListener;

class CallbackArrUpdateListener implements ArrUpdateListener{
	/** @var callable */
	private $callback;
	public function __construct(callable $callback){
		$this->callback = $callback;
	}
	public function onUpdate(Arr $arr){
		call_user_func($this->callback, $arr);
	}
}
