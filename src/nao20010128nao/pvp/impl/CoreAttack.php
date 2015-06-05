<?php
namespace nao20010128nao\pvp\impl;

use pocketmine\Server;

use pocketmine\event\Listener;

use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\item\Tool;

use pocketmine\utils\TextFormat;

use pocketmine\network\protocol\RespawnPacket;

use pocketmine\network\Network; 

use nao20010128nao\pvp\PluginMain;

class CoreAttack implements Listener{
	private $option;
	private $plugin;
	private $server;
	private $coreHealths;
	public function __construct(array $options,PluginMain $plugin,Server $server){
		$this->option=$options;
		$this->plugin=$plugin;
		$this->server=$server;
		$this->coreHealths=array();
		foreach($options as $key=>$value){
			$this->coreHealths=array_merge($this->coreHealths,array($key=>$options["coreDefaultHealth"]));
		}
		$server->getPluginManager()->registerEvents($this,$plugin);
	}
	public function onPlayerInteract(PlayerInteractEvent $event){
		$where=$event->getTouchVector();
		$teamBelong=false;
		foreach($option as $team=>$axis){
			if(($axis["x"]==$where.getX()) and ($axis["y"]==$where.getY()) and ($axis["z"]==$where.getZ())){
				$teamBelong=$team;
			}
		}
		if($teamBelong===false){
			//the block is not a core.
			return;
		}
		if($event->getBlock()->getId()==0){
			//problem in setup!
			return;
		}
		$player=$event->getPlayer();
		$teamPlayer=$this->plugin->teamInfo[mb_strtolower($player->getName())];
		if(!isset($teamPlayer)){
			$player->sendMessage($this->plugin->system["messages"]["notInTeam"]);
			return;
		}
		if($teamBelong==$teamPlayer){
			//the player attacks its own core.
			$player->sendMessage($this->plugin->system["messages"]["yourCore"]);
			return;
		}
		$action=$event->getAction();
		if($action!=PlayerInteractEvent::LEFT_CLICK_BLOCK or $action!=PlayerInteractEvent::RIGHT_CLICK_BLOCK){
			return;
		}
		$inv=$player->getInventory();
		$item=$inv->getItemInHand();
		$handItemId=$item->getId();
		$damage=5;
		switch($handItemId){
		case 283://gold sword
			$damage=55;
			break;
		case 284://gold tools
		case 285:
		case 286:
			$damage=50;
			break;
			
		case 276://diamond sword
			$damage=45;
			break;
		case 275://diamond tools
		case 274:
		case 273:
			$damage=40;
			break;
			
		case 276://iron sword
			$damage=35;
			break;
		case 256://iron tools
		case 257:
		case 258:
			$damage=30;
			break;
		
		case 276://stone sword
			$damage=25;
			break;
		case 256://stone tools
		case 257:
		case 258:
			$damage=20;
			break;
		
		case 283://wood sword
			$damage=15;
			break;
		case 284://wood tools
		case 285:
		case 286:
			$damage=10;
			break;
			
		case 290://wood hoe
		case 291://stone hoe
		case 292://iron hoe
		case 293://diamond hoe
		case 294://gold hoe
			$damage=10;
			break;
		}
		processDamageCore($teamBelong,$damage);
	}
	public function processDamageCore($team,$damage){
		foreach($this->plugin->teamInfo as $player=>$pTeam){
			if($pTeam==$team){
				$this->server->getPlayer($player)->sendMessage(TextFormat::RED.$this->plugin->system["messages"]["coreUnderAttack"]);
			}
		}
		$calcHealth=$this->coreHealths[$team]-$amount;
		if($calcHealth<=0){
			foreach($this->plugin->teamInfo as $player=>$pTeam){
				if($pTeam==$team){
					$this->server->getPlayer($player)->sendMessage($this->plugin->system["messages"]["coreEliminated"]);
				}else{
					$this->server->getPlayer($player)->sendMessage(str_replace("{team}",$this->plugin->system["teamShowName"][$team],$this->plugin->system["messages"]["coreEliminated2"]));
				}
			}
			$this->server->broadcastMessage($this->plugin->system["messages"]["kickAll"]);
			foreach($this->plugin->teamInfo as $player=>$pTeam){
				$p=$this->server->getPlayer($player);
				$pk = new RespawnPacket();
				$pos = $this->getSpawn();
				$pk->x = $pos->x;
				$pk->y = $pos->y;
				$pk->z = $pos->z;
				$p->dataPacket($pk->setChannel(Network::CHANNEL_WORLD_CHUNKS));
			}
			foreach($this->plugin->teamInfo as $key=>$value){
				$this->plugin->teamInfo=array_diff($this->plugin->teamInfo,array($key=>null));
			}
			foreach($option as $key=>$value){
				$this->coreHealths=array_merge($this->coreHealths,array($key=>$option["coreDefaultHealth"]));
			}
		}else{
			$this->coreHealths[$team]=$coreHealth;
		}
	}
}