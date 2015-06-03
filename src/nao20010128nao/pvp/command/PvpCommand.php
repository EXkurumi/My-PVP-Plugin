<?php

use pocketmine\plugin\PluginBase;

use pocketmine\Player;

class PvpCommand extends CommandBase{
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
		if($this->plugin->turnOnPvP($sender)){
			$sender->sendMessage($this->plugin->system["messages"]["turnedOnPvP"]);
			$sender->sendMessage($this->plugin->system["messages"]["teleporting"]);
			$sender->sendMessage(str_replace("{team}",$this->plugin->system["teamShowName"][$this->plugin->teamInfo[mb_strtolower($sender->getName())]],$this->plugin->system["messages"]["reportTeam"]));
		}else{
			
		}
		return true;
	}

	public function getPlugin(){
		return $this->plugin;
	}
}