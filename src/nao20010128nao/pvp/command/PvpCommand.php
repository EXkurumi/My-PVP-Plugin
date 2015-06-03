<?php
use pocketmine\plugin\PluginBase;

class PvpCommand extends CommandBase{
	public function __construct(xSudo $plugin, $name, $description)
	{
		$this->plugin = $plugin;
		parent::__construct($name, $description);
	}
	
	public function execute(CommandSender $sender, $label, array $args)
	{
		
		return true;
	}

	public function getPlugin(){
		return $this->plugin;
	}
}