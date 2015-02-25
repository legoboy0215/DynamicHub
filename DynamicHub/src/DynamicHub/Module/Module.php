<?php

namespace DynamicHub\Module;

use DynamicHub\Session\Session;

abstract class Module{
	/**
	 * @return string
	 */
	public abstract function getUniqueName();
	/**
	 * @return \pocketmine\plugin\Plugin
	 */
	public abstract function getContext();

	public abstract function onJoin(Session $session);
	public abstract function onQuit(Session $session);
	public abstract function onDisable();
}
