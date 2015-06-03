<?php

use pocketmine\Server;

use pocketmine\event\Listener;

class CoreAttack implements Listener{
	private $option;
	private $plugin;
	private $server;
	public __construct(array $options,PluginMain $plugin,Server $server){
		$this->option=$option;
		$this->plugin=$plugin;
		$this->server=$server;
		$server->getPluginManager()->registerEvents($server,$this);
	}
}