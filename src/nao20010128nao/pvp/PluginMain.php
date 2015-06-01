<?php

use pocketmine\Server;
use pocketmine\plugin\PluginBase;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginIdentifiableCommand;

use pocketmine\Player;

use pocketmine\utils\TextFormat;

use pocketmine\event\Listener;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerMoveEvent;

use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;

class PluginMain extends PluginBase implements Listener{
	private $csender;
	private $system;
	private $cheaters;
	private $money;
	private $teamInfo;
	private $judge;
	private $stats;
	public function onEnable(){
		$this->csender=new ConsoleCommandSender();
		$this->csender->sendMessage(TextFormat::GREEN."Loading...");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->judge=new BannableWordsDetector($this->getFile()."/resources/bannableWords");
		$this->teamInfo=array();
		if(!file_exists($this->getDataFolder())){
			mkdir($this->getDataFolder(),755);
		}
		if(!file_exists($this->getDataFolder()."system.yml")){
			$this->csender->sendMessage(TextFormat::RED."An unrecovable error found!");
			$this->csender->sendMessage(TextFormat::RED."system.yml not found!");
			$this->csender->sendMessage(TextFormat::RED."Creating template...");
			$temp=array(
				"pvpStartBlock"=>array(
					array("x"=>0,"y"=>0,"z"=>0),
					array("x"=>0,"y"=>0,"z"=>0),
					array("x"=>0,"y"=>0,"z"=>0),
					array("x"=>0,"y"=>0,"z"=>0),
					array("x"=>0,"y"=>0,"z"=>0),
					array("x"=>0,"y"=>0,"z"=>0),
					),
				"pvpTeleportTo"=>array(
					"RED"=>array("x"=>0,"y"=>0,"z"=>0),
					"GREEN"=>array("x"=>0,"y"=>0,"z"=>0),
					"BLUE"=>array("x"=>0,"y"=>0,"z"=>0),
					"YELLOW"=>array("x"=>0,"y"=>0,"z"=>0),
					),
				"antiCheat"=>true,
				"acceptCheatTime"=>3,//仏の顔も三度まで(The Buddha allows bad doing for third time.)
				"blockFasterChat"=>true,
				"moneyUnit"=>"GM",
				"messages"=>array(
					"turnedOnPvP"=>"You have turned on PvP mode!",
					"teleporting"=>"Teleporting...",
					"fastChat"=>"Slow down, your chat is so fast!",
					"cheat"=>"DO NOT USE CHEAT! YOU WILL BE BANNED IF YOU DO IT THREE TIMES!",
					),
				"joinMessages"=>array(
					"Welcome to the server!",
					"Here is a PvP server!",
					"Play! Battle! Chat!",
					"This server can detect some cheatings,",
					"so don't use cheat!",
					),
				"teamName"=>array(
					"RED",
					"GREEN",
					"BLUE",
					"YELLOW",
					),
				"denyCommands"=>array(
					"op",
					"stop",
					"deop",
					"whitelist",
					),
				);
			yaml_emit_file($this->getDataFolder()."system.yml",$this->system);
			$this->csender->sendMessage(TextFormat::RED."Starting stopping...");
			$this->getServer()->stop();
			return;
		}else{
			$this->system=yaml_parse_file($this->getDataFolder()."system.yml");
		}
		$this->csender->sendMessage(TextFormat::GREEN."Loading cheaters.yml...");
		if(file_exists($this->getDataFolder()."cheaters.yml")){
			$this->cheaters=yaml_parse_file($this->getDataFolder()."cheaters.yml");
		}else{
			$this->cheaters=array();
		}
		$this->csender->sendMessage(TextFormat::GREEN."Loading money.yml...");
		if(file_exists($this->getDataFolder()."money.yml")){
			$this->money=yaml_parse_file($this->getDataFolder()."money.yml");
		}else{
			$this->money=array();
		}
		$this->csender->sendMessage(TextFormat::GREEN."Loading stats.yml...");
		if(file_exists($this->getDataFolder()."stats.yml")){
			$this->stats=yaml_parse_file($this->getDataFolder()."stats.yml");
		}else{
			$this->stats=array();
		}
		$this->csender->sendMessage(TextFormat::GREEN."Done! Continuing enabling next plugins...");
	}
	
	public function onDisable(){
		yaml_emit_file($this->getDataFolder()."system.yml",$this->system);
		yaml_emit_file($this->getDataFolder()."cheaters.yml",$this->cheaters);
		yaml_emit_file($this->getDataFolder()."money.yml",$this->money);
		yaml_emit_file($this->getDataFolder()."stats.yml",$this->stats);
	}
	
	public function onBlockPlace(BlockPlaceEvent $event){
		$player = $event->getPlayer();
		if($player->getGamemode()!==Player::CREATIVE){
			$event->setCancelled(true);
		}
	}
	public function onBlockBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		if($player->getGamemode()!==Player::CREATIVE){
			$event->setCancelled(true);
		}
	}
	public function onPlayerJoin(PlayerJoinEvent $event){
		$send=$event->getPlayer();
		foreach($this->system["joinMessages"] as $mes){
			$send->sendMessage($mes);
		}
	}
	public function onCommandEvent(PlayerCommandPreprocessEvent $event){
		$player = $event->getPlayer();
		$username = $player->getName();
		$text = $event->getMessage();
		if($text[0]!=="/"){
			return null;
		}
		
	}
	public function onPlayerChat(PlayerChatEvent $event){
		$player = $event->getPlayer();
		$username = $player->getName();
		if($this->system["antiCheat"]){
			
		}
	}
	public function onPlayerMove(PlayerMoveEvent $event){
		$player = $event->getPlayer();
		$username = $player->getName();
		
	}
}