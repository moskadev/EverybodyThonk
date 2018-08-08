<?php

namespace supermaxalex\EverybodyThonk;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener{

	//TODO: config - make possible to change thonk head's skin, exclude a player...

	public function onEnable() : void{
		ThonkHead::init();

		$this->getLogger()->info("Everybody will now thonk.");
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
	}

	public function onDisable() : void{
		$this->getLogger()->info("Everybody are stopping to thonk.");
	}
}