<?php

namespace dynamichub\event\memory;

class RealValue implements Value{
	private $value;
	/** @var Arr */
	private $parent;
	public function __construct($value, Arr $parent){
		$this->value = $value;
		$this->parent = $parent;
	}
	public function get(){
		return $this->value;
	}
	public function set($value){
		$this->value = $value;
	}
	public function increment($size = 1){
		$this->value += $size;
	}
	public function decrement($size = 1){
		$this->value -= $size;
	}
	public function append($str){
		$this->value .= $str;
	}
	public function prepend($str){
		$this->value  = $str . $this->value;
	}
	public function getValue(){
		return $this->value;
	}
	public function setValue($value){
		$this->value = $value;
	}
	public function onUpdate(){
		$this->parent->getListener()->onUpdate($this->parent);
	}
}
