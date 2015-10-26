<?php
namespace nao20010128nao\pvp\impl;

use pocketmine\Server;

use pocketmine\event\Listener;

use pocketmine\event\entity\EntityDeathEvent;

use nao20010128nao\pvp\PluginMain;

class Default implements Listener{
	private $option;
	private $plugin;
	private $server;
	public function __construct(array $options,PluginMain $plugin,Server $server){
		$this->option=$options;
		$this->plugin=$plugin;
		$this->server=$server;
		$server->getPluginManager()->registerEvents($this,$plugin);
	}
	public function onPlayerDeath2(EntityDeathEvent $event){
		$entity = $event->getEntity();
        $cause = $entity->getLastDamageCause();
        $killer = $cause->getDamager();
        
	}
}