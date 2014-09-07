<?php

namespace exampleminigame;

use dynamichub\DynamicHub;
use pocketmine\plugin\PluginBase;

class Loader extends PluginBase{
	public function onEnable(){
		$instance = DynamicHub::getSafeInstance($this->getServer());
		$instance->register(new ExampleMinigame($this, $instance));
	}
}
