<?php

namespace dynamichub\event\memory;

class Arr implements Value{
	/** @var Value[] */
	private $map = [];
	private $listener;
	public function __construct(ArrUpdateListener $listener, array $baseArray){
		$this->listener = $listener;
		$this->setAll($baseArray);
	}
	public function getListener(){
		return $this->listener;
	}
	public function get($key){
		return $this->map[$key];
	}
	public function exists($key){
		return isset($this->map[$key]);
	}
	public function remove($key){
		unset($this->map[$key]);
		$this->onUpdate();
	}
	public function set($key, $value){
		$this->map[$key] = $value;
		$this->onUpdate();
	}
	public function getValue(){
		return $this->getAll();
	}
	public function getAll(){
		$map = [];
		foreach($this->map as $k => $v){
			$map[$k] = $v->getValue();
		}
		return $map;
	}
	public function setValue($value){
		$this->setAll($value);
	}
	public function setAll(array $array){
		$this->map = [];
		foreach($array as $key => $val){
			if($val instanceof Value){
				$this->map[$key] = $val;
			}
			elseif(is_array($val)){
				$this->map[$key] = new ChildArr($val, $this);
			}
			else{
				$this->map[$key] = new RealValue($val, $this);
			}
		}
	}
	protected function onUpdate(){
		$this->listener->onUpdate($this);
	}
}
