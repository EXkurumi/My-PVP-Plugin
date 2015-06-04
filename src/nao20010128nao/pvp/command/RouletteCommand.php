<?php
namespace nao20010128nao\pvp\impl;

use pocketmine\plugin\PluginBase;

use pocketmine\Player;

use pocketmine\utils\TextFormat;

use pocketmine\item\Item;

class RouletteCommand extends CommandBase{
	private $canBeGiven=array(
		256,257,258,
		260,267,268,
		269,270,271,
		272,273,274,
		275,276,277,
		278,279,280,
		283,284,285,
		287,291,292,
		293,294,297,
		298,299,300,
		301,302,303,
		304,305,306,
		307,308,309,
		310,311,312,
		313,314,315,
		316,317,320,
		321,332,364,
		365,366,391,
		393);
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
		$user=mb_strtolower($sender->getName());
		if($this->plugin->money[$user]<$this->plugin->system["rouletteNeed"]){
			$sender->sendMessage(TextFormat::RED.$this->plugin->system["messages"]["moneyNotEnough"]);
			return false;
		}
		$this->plugin->money[$user]=$this->plugin->money[$user]-$this->plugin->system["rouletteNeed"];
		$inv=$sender->getInventory();
		$itemId=$this->canBeGiven[random(count($this->canBeGiven)+1)];
		$item=Item::get($itemId);
		$inv->addItem(clone $item);
		$sender->sendMessage(TextFormat::GREEN.$this->plugin->system["messages"]["rouletteHit"]);
		return true;
	}

	public function getPlugin(){
		return $this->plugin;
	}
	
	public function random($max){
		return (mt_rand(0,$max)^mt_rand(0,$max)^mt_rand(0,$max))%$max;
	}
}