<?php

namespace supermaxalex\EverybodyThonk;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChangeSkinEvent;
use pocketmine\event\player\PlayerJoinEvent;

class EventListener implements Listener{

	/**
	 * @param PlayerJoinEvent $event
	 */
	public function onPlayerJoin(PlayerJoinEvent $event) : void{
		Main::thonk($event->getPlayer());
	}

	/**
	 * @param PlayerChangeSkinEvent $event
	 */
	public function onPlayerChangeSkin(PlayerChangeSkinEvent $event) : void{
		Main::thonk($event->getPlayer(), $event->getNewSkin());

	}
}