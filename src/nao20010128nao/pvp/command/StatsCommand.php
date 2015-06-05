<?php
namespace nao20010128nao\pvp\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\Player;

use nao20010128nao\pvp\PluginMain;

class StatsCommand extends Command{
	public function __construct(PluginMain $plugin, $name, $description)
	{
		$this->plugin = $plugin;
		parent::__construct($name, $description);
	}
	
	public function execute(CommandSender $sender, $label, array $args)
	{
		if(!($sender instanceof Player)){
			$sender->sendMessage($this->plugin->system["messages"]["inGame"]);
			return false;
		}
		$player=mb_strtolower($sender->getName());
		$statLines=array(
			$this->plugin->system["messages"]["kills"]=>$this->plugin->stats[$player]["kill"],
			$this->plugin->system["messages"]["deaths"]=>$this->plugin->stats[$player]["death"],
			$this->plugin->system["messages"]["money"]=>$this->plugin->money[$player],
			$this->plugin->system["messages"]["kills"]=>$this->plugin->stats[$player]["kill"],
			$this->plugin->system["messages"]["level"]=>$this->plugin->stats[$player]["level"],
			$this->plugin->system["messages"]["exp"]=>$this->plugin->stats[$player]["exp"],
			);
		$sender->sendMessage($this->plugin->system["messages"]["statsSplit"]);
		foreach($statLines as $title=>$value){
			$sender->sendMessage(str_replace(array("{title}","{value}"),array($title,$value),$this->plugin->system["messages"]["statsStyle"]));
		}
		$sender->sendMessage($this->plugin->system["messages"]["statsSplit"]);
		return true;
	}

	public function getPlugin(){
		return $this->plugin;
	}
}