<?php

/*
 *
 *       _____      _     _      __  __  _____
 *      |  __ \    (_)   | |    |  \/  |/ ____|
 *      | |__) | __ _  __| | ___| \  / | |
 *      |  ___/ '__| |/ _` |/ _ \ |\/| | |
 *      | |   | |  | | (_| |  __/ |  | | |____
 *      |_|   |_|  |_|\__,_|\___|_|  |_|\_____|
 *            A minecraft bedrock server.
 *
 *      This project and it’s contents within
 *     are copyrighted and trademarked property
 *   of PrideMC Network. No part of this project or
 *    artwork may be reproduced by any means or in
 *   any form whatsoever without written permission.
 *
 *  Copyright © PrideMC Network - All Rights Reserved
 *                     Season #5
 *
 *  www.mcpride.tk                 github.com/PrideMC
 *  twitter.com/PrideMC         youtube.com/c/PrideMC
 *  discord.gg/PrideMC           facebook.com/PrideMC
 *               bit.ly/JoinInPrideMC
 *  #PrideGames                           #PrideMonth
 *
 */

declare(strict_types=1);

namespace PrideMC\Guardian\Modules;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\Packet;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use PrideMC\Guardian\Anticheat;

use function assert;
use function microtime;

class Timer extends Anticheat implements Listener
{
	public function __construct()
	{
		parent::__construct(Anticheat::TIMER);
	}

	private array $time = [];
	private array $timer = [];
	private array $ticks = [];

	public function handleEvent(DataPacketReceiveEvent $event) : void
	{
		$this->handlePacket($event->getPacket(), $event->getOrigin());
	}

	public function handlePacket(DataPacket|Packet $packet, NetworkSession $session) : void
	{
		assert($packet instanceof PlayerAuthInputPacket);
		if(($player = $session->getPlayer()) === null) {
			return;
		}

		if(!isset($this->time[$player->getUniqueId()->__toString()])) {
			$this->time[$player->getUniqueId()->__toString()] = 0;
		}
		if(!isset($this->ticks[$player->getUniqueId()->__toString()])) {
			$this->ticks[$player->getUniqueId()->__toString()] = 0;
		}
		if(!isset($this->timer[$player->getUniqueId()->__toString()])) {
			$this->timer[$player->getUniqueId()->__toString()] = 0;
		}

		$time = $this->time[$player->getUniqueId()->__toString()];
		$ticks = $this->ticks[$player->getUniqueId()->__toString()];
		$timer = $this->timer[$player->getUniqueId()->__toString()];

		if(microtime(true) - $time > 1) {
			if($ticks > 20) {
				$timer++;
				if($timer % 10 === 0) {
					$this->fail($player);
				}
			} else {
				$timer = 0;
			}

			$ticks = 0;
			$time = microtime(true);
		}

		$timer = 0;
		$time = microtime(true);
		$ticks = ++$ticks;
	}
}
