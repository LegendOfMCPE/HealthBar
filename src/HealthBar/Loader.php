<?php
namespace HealthBar;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Loader extends PluginBase implements Listener{
    public function onEnable(){
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        foreach($this->getServer()->getOnlinePlayers() as $player){
            $this->updateHealthBar($player);
        }
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function onPlayerJoin(PlayerJoinEvent $event){
        $this->updateHealthBar($event->getPlayer());
    }

    //TODO!
    public function onPlayerHealthChange($event){
        //$this->updateHealthBar($event->getPlayer());
    }

    public function updateHealthBar(Player $player){
        $config = $this->getConfig();
        $style = $config->get("style");
        $position = $config->get("position");
        if($style == "default"){
            $style = $player->getHealth() . "/" . $player->getMaxHealth();
        }else{
            $this->getLogger()->error("Unknown style");
            return false;
        }
        if($position == "above"){
            $player->setNameTag($style . "\n" . $player->getDisplayName());
        }elseif($position == "under"){
            $player->setNameTag($player->getDisplayName() . "\n" . $style);
        }else{
            $this->getLogger()->error("Unknown position");
            return false;
        }
        return true;
    }
}
