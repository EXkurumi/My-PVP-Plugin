<?php
namespace nao20010128nao\pvp\impl;

use pocketmine\Server;

use pocketmine\event\Listener;

use pocketmine\event\entity\EntityDeathEvent;

class SimpleTeamBattle implements Listener{
	private $option;
	private $plugin;
	private $server;
	public __construct(array $options,PluginMain $plugin,Server $server){
		$this->option=$option;
		$this->plugin=$plugin;
		$this->server=$server;
		$server->getPluginManager()->registerEvents($server,$this);
	}
	public function onPlayerDeath2(EntityDeathEvent $event){
		$entity = $event->getEntity();
        $cause = $entity->getLastDamageCause();
        $killer = $cause->getDamager();
        if(($killer instanceof Player) and ($entity instanceof Player)){
            $killer->sendMessage(str_replace(array("{player}"),array($entity->getName()),$this->plugin->system["messages"]["whenKill"]));
			$entity->sendMessage(str_replace(array("{killer}"),array($killer->getName()),$this->plugin->system["messages"]["whenDeath"]));
			$this->plugin->prepareStat($entity->getName());
			$this->plugin->prepareStat($killer->getName());
			$username=mb_strtolower($entity->getName());
			$entity->plugin->stats[$username]["death"]=$this->plugin->stats[$username]["death"]+1;
			$username=mb_strtolower($killer->getName());
			$entity->plugin->stats[$username]["kill"]=$this->plugin->stats[$username]["kill"]+1;
			$entity->plugin->stats[$username]["money"]=$this->plugin->stats[$username]["money"]+$this->plugin->system["moneyAdd"];
			$levelUp=$this->plugin->processGiveExp($username);
			$killer->sendMessage(str_replace("{exp}",$this->plugin->system["expAdd"],$this->plugin->system["messages"]["gotExp"]));
			if($levelUp!=0){
				$killer->sendMessage(str_replace("{level}",$this->plugin->stats[$username]["level"],$this->plugin->system["messages"]["levelUp"]));
			}
        }
	}
}