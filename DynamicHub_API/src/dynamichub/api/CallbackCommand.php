<?php

namespace dynamichub\api;

use dynamichub\DynamicHub;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;

class CallbackCommand extends Command implements PluginIdentifiableCommand{
	const RESULT_FALSE = 0;
	const RESULT_TRUE = 1;
	const RESULT_PARENT = 2;
	/** @var callable */
	private $callback;
	/** @var DynamicHub */
	private $hub;
	/**
	 * @var callable
	 */
	private $permissionTest;
	public function __construct(DynamicHub $hub, callable $callback, $name, callable $permissionTest = null){
		$this->hub = $hub;
		$this->callback = $callback;
		parent::__construct($name);
		$this->permissionTest = $permissionTest;
	}
	/**
	 * @return DynamicHub
	 */
	public function getPlugin(){
		return $this->hub;
	}
	public function execute(CommandSender $sender, $label, array $args){
		$r = call_user_func($this->callback, $this, $args, $sender);
		if(is_string($r)){
			$sender->sendMessage($r);
		}
		if($r === false){
			$sender->sendMessage("Wrong usage!");
		}
		return true;
	}
	public function testPermissionSilent(CommandSender $sender){
		if(is_callable($this->permissionTest)){
			$result = call_user_func($this->permissionTest, $sender);
			if($result === self::RESULT_FALSE){
				return false;
			}
			if($result === self::RESULT_TRUE){
				return true;
			}
		}
		return parent::testPermissionSilent($sender);
	}
}
