<?php

declare(strict_types=1);

namespace supermaxalex\EverybodyThonk;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChangeSkinEvent;
use pocketmine\event\player\PlayerJoinEvent;

class EventListener implements Listener{

	/**
	 * @param PlayerJoinEvent $event
	 */
	public function onPlayerJoin(PlayerJoinEvent $event) : void{
	    ThonkHead::applyTo($event->getPlayer());
	}

	/**
	 * @param PlayerChangeSkinEvent $event
	 */
	public function onPlayerChangeSkin(PlayerChangeSkinEvent $event) : void{
		if(!$event->isCancelled()){
			ThonkHead::applyTo($event->getPlayer(), $event->getNewSkin());
		}
	}
}