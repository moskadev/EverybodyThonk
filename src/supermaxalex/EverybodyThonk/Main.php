<?php

namespace supermaxalex\EverybodyThonk;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener{

	private const PREFIX = "[EverybodyThonk] ";

	//TODO: config - make possible to change thonk head's skin, exclude a player...

	public function onEnable() : void{
		ThonkHead::init();

		$this->getServer()->getLogger()->info(self::PREFIX . "Everybody will now thonk.");
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
	}

	public function onDisable() : void{
		$this->getServer()->getLogger()->info(self::PREFIX . "Everybody are stopping to thonk.");
	}
}