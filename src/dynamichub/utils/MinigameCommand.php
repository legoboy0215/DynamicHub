<?php

namespace dynamichub\event;

use dynamichub\api\Minigame;
use dynamichub\DynamicHub;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;

abstract class MinigameCommand extends Command implements PluginIdentifiableCommand{
	private $hub;
	private $minigame;
	public function __construct(Minigame $minigame, DynamicHub $hub, $name, $description = "", $usage = "", $aliases = []){
		parent::__construct($name, $description, $usage, $aliases);
		$this->minigame = new \WeakRef($minigame);
		$this->hub = $hub;
	}
	public function getPlugin(){
		return $this->hub;
	}
	/**
	 * @return Minigame
	 */
	public function getMinigame(){
		return $this->minigame->get();
	}
	public function testPermissionSilent(CommandSender $sender){
		return ($this->minigame->valid() and $this->minigame->get()->isEnabled()) ? parent::testPermissionSilent($sender):false;
	}
}
