<?php

use pocketmine\plugin\PluginBase;

use pocketmine\Player;

class RouletteCommand extends CommandBase{
	public function __construct(PluginMain $plugin, $name, $description)
	{
		$this->plugin = $plugin;
		parent::__construct($name, $description);
	}
	
	public function execute(CommandSender $sender, $label, array $args)
	{
		if(!($sender instanceof Player)){
			$sender->sendMessage($this->plugin->system["messages"]["inGame"]);
		}
		
		return true;
	}

	public function getPlugin(){
		return $this->plugin;
	}
}