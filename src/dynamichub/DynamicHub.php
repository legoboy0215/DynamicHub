<?php

namespace dynamichub;

use dynamichub\api\Minigame;
use dynamichub\event\MinigameCommand;
use dynamichub\event\MinigameEventExecutor;
use dynamichub\event\MinigameHandler;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\entity\EntityEvent;
use pocketmine\event\Event;
use pocketmine\event\EventPriority;
use pocketmine\event\inventory\InventoryEvent;
use pocketmine\event\level\LevelEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\inventory\Inventory;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\tile\Tile;

class DynamicHub extends PluginBase implements Listener{
	/** @var Minigame[] */
	private $games = [];
	/** @var event\MinigameHandler[][] */
	private $registeredHandles = [];
	public function onEnable(){

	}
	public function onDisable(){
		foreach($this->games as $game){
			$game->onDisable();
		}
	}
	public function register(Minigame $minigame){
		$this->games[$minigame->getName()] = $minigame;
	}
	public function registerCommand(MinigameCommand $cmd){
		$this->getServer()->getCommandMap()->register($cmd->getMinigame()->getName(), $cmd);
	}
	public function registerListener(Minigame $minigame, Listener $listener = null){
		if($listener === null){
			$listener = $minigame;
		}
		$class = new \ReflectionClass($listener);
		$methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
		foreach($methods as $method){
			if($method->isStatic()){
				continue;
			}
			$priority = EventPriority::NORMAL;
			$ignoreCancelled = false;
			$docComment = $method->getDocComment();
			if(is_string($docComment)){
				if(preg_match('/^[\t ]*\\* @priority[\t ]+([a-zA-Z]+)$/m', $docComment, $matches)){
					$matches[1] = strtoupper($matches[1]);
					if(defined($path = "pocketmine\\event\\EventPriority::" . $matches[1])){
						$priority = constant("pocketmine\\event\\EventPriority::" . $matches[1]);
					}
				}
				if(preg_match('/^[\t ](\\* )?@ignoreCancelled[\t ]+([a-zA-Z]+)$/m', $docComment, $matches)){
					$matches[1] = strtolower($matches[1]);
					if($matches[1] === "false"){
						$ignoreCancelled = false;
					}elseif($matches[1] === "true"){
						$ignoreCancelled = true;
					}
				}
			}
			$params = $method->getParameters();
			if(count($params) === 1){
				$param = $params[0];
				$paramClass = @$param->getClass();
				if($paramClass instanceof \ReflectionClass){
					if($paramClass->isSubclassOf("pocketmine\\event\\Event") and !$paramClass->isAbstract()){
						$this->registeredHandles[$paramClass->getName()][$minigame->getName()] = new MinigameHandler($minigame, $listener, $method->getName());
						$this->getServer()->getPluginManager()->registerEvent($paramClass->getName(), $listener, $priority, new MinigameEventExecutor(), $this, $ignoreCancelled);
					}
				}
			}
		}
	}
	public function handleEvent(Event $event){
		if(!isset($this->registeredHandles[get_class($event)])){
			return;
		}
		/** @var string|bool $world */
		$world = true;
		/** @var event\MinigameHandler[] $handles */
		$handles = $this->registeredHandles[get_class($event)];
		if($event instanceof BlockEvent){
			$world = $event->getBlock()->getLevel()->getName();
		}
		if($event instanceof DataPacketReceiveEvent){
			$world = $event->getPlayer()->getLevel()->getName();
		}
		if($event instanceof DataPacketSendEvent){
			$world = $event->getPlayer()->getLevel()->getName();
		}
		if($event instanceof EntityEvent){
			$world = $event->getEntity()->getLevel()->getName();
		}
		if($event instanceof InventoryEvent){
			$holder = $event->getInventory()->getHolder();
			find_world_by_holder:
			if($holder instanceof Entity){
				$world = $holder->getLevel()->getName();
			}
			elseif($holder instanceof Tile){
				$world = $holder->getLevel()->getName();
			}
			elseif($holder instanceof Inventory){
				$holder = $holder->getHolder();
				goto find_world_by_holder;
			}
		}
		if($event instanceof LevelEvent){
			$world = $event->getLevel()->getName();
		}
		if($event instanceof PlayerEvent){
			$world = $event->getPlayer()->getLevel()->getName();
		}
		foreach($handles as $handle){
			if($handle->getMinigame()->ownsWorld($world)){
				$handle->callEvent($event);
				break;
			}
		}
	}
	/**
	 * @param Server $server
	 * @return self
	 */
	public static function getSafeInstance(Server $server){ // thread-safe if $server is thread-safe
		return $server->getPluginManager()->getPlugin("DynamicHub");
	}
	public static function registerMinigame(Server $server, Minigame $minigame){
		self::getSafeInstance($server)->register($minigame);
	}
}
