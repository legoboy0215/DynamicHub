<?php

namespace DynamicHub\Utils;

use DynamicHub\DynamicHub;

class Configuration{
	public $sessionInit;
	public function __construct(DynamicHub $plugin){
		extract($plugin->getConfig()->getAll());
		if(!isset($sessionInit)){
			throw new \RuntimeException("Corrupted DynamicHub config file");
		}
		$this->sessionInit = $sessionInit;
	}

}
