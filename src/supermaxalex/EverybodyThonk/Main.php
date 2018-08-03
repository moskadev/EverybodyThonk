<?php

namespace supermaxalex\EverybodyThonk;

use pocketmine\entity\Skin;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener{

	private const PREFIX = "[EverybodyThonk]";

	//TODO: config - make possible to change thonk head's skin, exclude a player...

	public function onEnable() : void{
		ThonkHead::init();

		$this->getServer()->getLogger()->info(self::PREFIX . " Everybody will now T H O N K.");
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
	}

	public function onDisable() : void{
		$this->getServer()->getLogger()->info(self::PREFIX . " Stopping thonk is bad for health.");
	}

	/**
	 * @param Player $player
	 * @param Skin $skin
	 */
	public static function thonk(Player $player, ?Skin $skin = null) : void{
		$thonk = ThonkHead::setThonkHead($skin ?? $player->getSkin());

		//hack for 1.5 #blamemojang
		$player->getServer()->removePlayerListData($player->getUniqueId());
		$player->getServer()->updatePlayerListData($player->getUniqueId(), $player->getId(), $player->getName(), $thonk, $player->getXuid());

		/*$player->setSkin($thonk);
		$player->sendSkin();*/
	}
}