<?php
namespace nao20010128nao\pvp;

use pocketmine\Server;
use pocketmine\plugin\PluginBase;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginIdentifiableCommand;

use pocketmine\Player;

use pocketmine\math\Vector3;
use pocketmine\level\Position;

use pocketmine\utils\TextFormat;

use pocketmine\event\Listener;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerDeathEvent;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;

use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;

use nao20010128nao\pvp\command\PvpCommand;
use nao20010128nao\pvp\command\RouletteCommand;
use nao20010128nao\pvp\command\statsCommand;

class PluginMain extends PluginBase implements Listener{
	private $csender;
	public $system;
	public $cheaters;
	public $money;
	public $teamInfo;
	private $judge;
	public $stats;
	private $chatTime;
	private $battleImpl;
	public function onEnable(){
		$this->csender=new ConsoleCommandSender();
		$this->csender->sendMessage(TextFormat::GREEN."Loading...");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->judge=new BannableWordDetector($this->getFile()."/resources/bannableWords");
		$this->pvps=array();
		$this->chatTime=array();
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
				"pvpTeleportTo"=>array("x"=>0,"y"=>0,"z"=>0,"level"=>"world"),
				"antiCheat"=>true,
				"acceptCheatTime"=>3,//仏の顔も三度まで(The Buddha allows bad doing for third time.)
				"disallowTeamFire"=>true,
				"blockFasterChat"=>true,
				"blockFasterChatToreshold"=>100,//ミリ秒(milliseconds)
				"moneyUnit"=>"GM",
				"moneyAdd"=>100,
				"expUnit"=>"exp",
				"expAdd"=>20,
				"rouletteNeed"=>150,
				"messages"=>array(
					"turnedOnPvP"=>"You have turned on PvP mode!",
					//"reportTeam"=>"You are {team}!",
					"teleporting"=>"Teleporting...",
					"fastChat"=>"Slow down, your chat is so fast!",
					"cheat"=>"DO NOT USE CHEAT! YOU WILL BE BANNED IF YOU DO IT THREE TIMES!",
					"banFirst"=>"{player} was banned because he/she used cheat {times} times.",
					"banSecond"=>"{player}'s IP address:{ip}",
					"badCheater"=>"YOU ARE A BAD CHEATER!",
					"denyCommands"=>"This command has been disabled by admin.",
					"whenDeath"=>"You are killed by {killer}. Added one on your deaths.",
					"whenKill"=>"You killed {player}. Added one on your kills.",
					"notInTeam"=>"You are not in the team! Turn on the PvP mode, and join the team!",
					"targetNotInTeam"=>"The target is not in the team! Your attack was cancelled.",
					"yourCore"=>"The core is your team's core! Please protect it!",
					"teamFire"=>"Team Fire is now allowed! Don't worry, this damage was cancelled, and not counted as a spam!",
					"gotExp"=>"You got {exp} exp!",
					"levUp"=>"Your level is now {level}!",
					"coreUnderAttack"=>"Your core is under attack!",
					"coreEliminated"=>"Your core has been eliminated!",
					"coreEliminated2"=>"{team}'s core has been eliminated!",
					"kickAll"=>"Kicking all players from the battle...",
					"descPvp"=>"Turn on the PvP mode!",
					"descRoulette"=>"Get a random item randomly for {rouletteNeed}{moneyUnit}!",
					"descStats"=>"Show your stats!",
					"inGameOnly"=>"Run this command in-game.",
					"moneyNotEnough"=>"Your money is not enough!",
					"rouletteHit"=>"Check your inventory!",
					"statsSplit"=>TextFormat::GREEN."==========================",
					"statsStyle"=>TextFormat::YELLOW."{title}".TextFormat::RESET.":".TextFormat::AQUA."{value}",
					"kills" =>"     Kills",
					"deaths"=>"    Deaths",
					"money"=> "     Money",
					"level"=> "     Level",
					"exp"=>   "Experiment",
					),
				"joinMessages"=>array(
					"Welcome to the server!",
					"Here is a PvP server!",
					"Play! Battle! Chat!",
					"This server can detect some cheatings,",
					"so don't use cheat!",
					),
				"denyCommands"=>array(
					"op",
					"stop",
					"deop",
					"whitelist",
					),
				"style"=>array(
					"impl"=>"nao20010128nao\\pvp\\impl\\DefaultC",
					"options"=>array(),
					),
				"expCalc"=>array(
					"baseValue"=>100,
					),
				);
			yaml_emit_file($this->getDataFolder()."system.yml",$temp);
			$this->csender->sendMessage(TextFormat::RED."Starting stopping...");
			$this->getServer()->shutdown();
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
		$this->csender->sendMessage(TextFormat::GREEN."Preparing some...");
		$battleClass=$this->system["style"]["impl"];
		$this->battleImpl=new $battleClass($this->system["style"]["options"],$this,$this->getServer());
		$commandMap = $this->getServer()->getCommandMap();
		$commandMap->register(
			"pvp", 
			new PvpCommand($this, "pvp", $this->system["messages"]["descPvp"])
		);
		/*$commandMap->register(
			"roulette", 
			new RouletteCommand($this, "roulette", str_replace(array("{rouletteNeed}","{moneyUnit}"),array($this->system["rouletteNeed"],$this->system["moneyUnit"]),$this->system["messages"]["descRoulette"]))
		);*/
		$commandMap->register(
			"stats", 
			new StatsCommand($this, "stats", $this->system["messages"]["descStats"])
		);
		$this->csender->sendMessage(TextFormat::GREEN."Done! Continuing enabling next plugins...");
	}
	
	public function onDisable(){
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
		$send->teleport($send->getSpawn());
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
		$cmdBody=explode(" ",substr($text,1))[0];
		$tmp=array_diff($this->system["denyCommands"],array($cmdBody));
		if(count($tmp)!=count($this->system["denyCommands"])){
			$event->setCancelled(true);
			$player->sendMessage($this->system["messages"]["denyCommands"]);
		}
	}
	public function onPlayerChat(PlayerChatEvent $event){
		$player = $event->getPlayer();
		$username = $player->getName();
		if($this->system["antiCheat"]){
			if($this->judge->test($event->getMessage())){
				$event->setCancelled(true);
				if(!array_key_exists(mb_strtolower($username),$this->cheaters)){
					$this->cheaters=array_merge($this->cheaters,array(mb_strtolower($username)=>0));
				}
				$this->cheaters[mb_strtolower($username)]=$this->cheaters[mb_strtolower($username)]+1;
				if($this->cheaters[mb_strtolower($username)]>=$this->system["acceptCheatTime"]){
					//Do IPBAN and BAN!!! PUNISH FOR CHEATERS!!!
					$this->processIPBan($player->getAddress(),$player,TextFormat::RED.$this->system["messages"]["badCheater"]);
					$player->setBanned(true);
					$str=$this->system["messages"]["banFirst"];
					$str=str_replace("{player}",$username,$str);
					$str=str_replace("{times}",$this->system["acceptCheatTime"],$str);
					$this->csender->sendMessage(TextFormat::RED.$str);
					
					$str=$this->system["messages"]["banSecond"];
					$str=str_replace("{player}",$username,$str);
					$str=str_replace("{ip}",$player->getAddress(),$str);
					$this->csender->sendMessage(TextFormat::RED.$this->system["messages"]["banSecond"]);
					return;
				}
				$player->sendMessage(TextFormat::RED.$this->system["messages"]["cheat"]);
			}
		}
		if($this->system["blockFasterChat"]){
			$time=microtime()/1000;
			if(!array_key_exists(mb_strtolower($username),$this->chatTime)){
				$this->chatTime=array_merge($this->chatTime,array(mb_strtolower($username)=>$time));
			}else{
				$last=$this->chatTime[mb_strtolower($username)];
				if(($time-$last)<=$this->system["blockFasterChatToreshold"]){
					$event->setCancelled(true);
					$player->sendMessage($this->system["messages"]["fastChat"]);
				}
			}
		}
	}
	public function onPlayerMove(PlayerMoveEvent $event){
		$player = $event->getPlayer();
		$username = $player->getName();
		
	}
	private function processIPBan($ip, CommandSender $sender,$mes="IP banned."){
		$sender->getServer()->getIPBans()->addBan($ip, "", null, $sender->getName());
		foreach($sender->getServer()->getOnlinePlayers() as $player){
			if($player->getAddress() === $ip){
				$player->kick($mes);
			}
		}
		$sender->getServer()->blockAddress($ip, -1);
	}
	public function onPlayerQuit(PlayerQuitEvent $event){
		$player = $event->getPlayer();
		$username = $player->getName();
		//remove player info from teamInfo
		$this->kickFromPvP($player);
	}
	public function onPlayerDeath(PlayerDeathEvent $event){
		$player = $event->getEntity();
		$username = $player->getName();
		$event->setKeepInventory(true);
		$this->kickFromPvP($player);
	}
	/*public function onPlayerDamageBlock(EntityDamageByBlockEvent $event){
		$event->setCancelled(true);
	}
	public function onPlayerDamagePlayer(EntityDamageByEntityEvent $event){
		switch($event->getCause()){
		case EntityDamageEvent::CAUSE_FIRE:
		case EntityDamageEvent::CAUSE_FALL:
		case EntityDamageEvent::CAUSE_FIRE_TICK:
		case EntityDamageEvent::CAUSE_LAVA:
		case EntityDamageEvent::CAUSE_FIRE:
		case EntityDamageEvent::CAUSE_BLOCK_EXPLOSION:
		case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
			$event->setCancelled(true);
			return;
		}
	}*/
	private function processGiveExp($player,$amount=-1){
		if($amount<=0){
			$amount=$this->system["expAdd"];
		}
		$player=mb_strtolower($player);
		$this->prepareStat($player);
		$maxExp=$this->system["calcExp"]["baseValue"];
		$calcExp=$this->stats[$player]["exp"]+$amount;
		
		$aht=$this->stats[$player]["exp"]+$amount;
		$this->stats[$player]["level"]=$this->stats[$player]["level"]+floor($aht/$this->system["calcExp"]["baseValue"]);
		$this->stats[$player]["exp"]=$aht%$this->system["calcExp"]["baseValue"];
	}
	private function prepareStat($name){
		$player=$this->getServer()->getPlayerExact($name);
		if(!isset($this->stats[$this->getPlayerIdentifer($player)])){
			$this->stats[$this->getPlayerIdentifer($player)]=array(
				"death"=>0,
				"kill"=>0,
				"level"=>0,
				"exp"=>0,
				);
		}
		if(!isset($this->money[$this->getPlayerIdentifer($player)])){
			$this->money[$this->getPlayerIdentifer($player)]=0;
		}
	}
	public function turnOnPvP($player){
		$this->pvps[$this->getPlayerIdentifer($player)]=$player;
		$pos=new Position($this->system["pvpTeleportTo"]["x"],$this->system["pvpTeleportTo"]["y"],$this->system["pvpTeleportTo"]["z"],$this->getServer()->getLevelByName($this->system["pvpTeleportTo"]["level"]));
		$player->teleport($pos);
	}
	public function kickFromPvP($player){
		unset($this->pvps[$this->getPlayerIdentifer($player)]);
		$player->teleport($player->getSpawn());
	}
	public function getPlayerIdentifer($player){
		return mb_strtolower($player->getName());
	}
}