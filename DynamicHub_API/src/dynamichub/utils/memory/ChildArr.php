<?php

namespace dynamichub\event\memory;

class ChildArr extends Arr{
	/** @var Arr */
	private $parent;
	public function __construct(array $arr, Arr $parent){
		$this->parent = $parent;
		parent::__construct(new DummyListener($this), $arr);
	}
	/**
	 * @return Arr
	 */
	public function getParent(){
		return $this->parent;
	}
}
