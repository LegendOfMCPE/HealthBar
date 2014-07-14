<?php
namespace HealthBar;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Loader extends PluginBase implements Listener{
    private $canRemove;

    public function onEnable(){
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->checkConfiguration();
    }

    public function onDisable(){
        if($this->canRemove === true){
            foreach($this->getServer()->getOnlinePlayers() as $p){
                $this->removeHealthBar($p);
            }
        }
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function onPlayerJoin(PlayerJoinEvent $event){
        $this->updateHealthBar($event->getPlayer());
    }

    public function onRegainHealth(EntityRegainHealthEvent $event){
        $entity = $event->getEntity();
        if($entity instanceof Player){
            $this->updateHealthBar($entity);
        }
    }

    public function onHealthLose(EntityDamageEvent $event){
        $entity = $event->getEntity();
        if($entity instanceof Player){
            $this->updateHealthBar($entity);
        }
    }

    public function onAttack(EntityDamageByEntityEvent $event){
        $entity = $event->getEntity();
        if($entity instanceof Player){
            $this->updateHealthBar($entity);
        }
    }

    private function checkConfiguration(){
        if(!$this->getStyle()){
            $this->getLogger()->info(TextFormat::YELLOW . "[HealthBar] " . TextFormat::RED . "Unknown style given, HealthBar will be disabled!");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            $this->setEnabled(false);
            return false;
        }elseif(!$this->getPosition()){
            $this->getLogger()->info(TextFormat::YELLOW . "[HealthBar] " . TextFormat::RED . "Unknown position given, HealthBar will be disabled!");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            $this->setEnabled(false);
            return false;
        }else{
           $this->enableHealthBar();
            return true;
        }
    }

    private function enableHealthBar(){
        $this->canRemove = true;
        foreach($this->getServer()->getOnlinePlayers() as $p){
            $this->updateHealthBar($p);
        }
    }

    /*
     *  .----------------.  .----------------.  .----------------.
     * | .--------------. || .--------------. || .--------------. |
     * | |      __      | || |   ______     | || |     _____    | |
     * | |     /  \     | || |  |_   __ \   | || |    |_   _|   | |
     * | |    / /\ \    | || |    | |__) |  | || |      | |     | |
     * | |   / ____ \   | || |    |  ___/   | || |      | |     | |
     * | | _/ /    \ \_ | || |   _| |_      | || |     _| |_    | |
     * | ||____|  |____|| || |  |_____|     | || |    |_____|   | |
     * | |              | || |              | || |              | |
     * | '--------------' || '--------------' || '--------------' |
     *  '----------------'  '----------------'  '----------------'
     *
     */

    public function getPlayer($player){
        $r = "";
        foreach($this->getServer()->getOnlinePlayers() as $p){
            if(strtolower($p->getDisplayName()) == strtolower($player) || strtolower($p->getName()) == strtolower($player)){
                $r = $this->getServer()->getPlayerExact($p->getName());
            }
        }
        if($r == ""){
            return false;
        }else{
            return $r;
        }
    }

    public function getStyle(){
        $style = $this->getConfig()->get("style");
        if($style == "default"){
            return "default";
        }else{
            return false;
        }
    }

    public function getPosition(){
        $position = $this->getConfig()->get("position");
        if($position == "above" || $position == "under" || $position == "left" || $position == "right"){
            return $position;
        }else{
            return false;
        }
    }

    public function setStyle($style){
        switch($style){
            case "default":
                $this->getConfig()->set("style", $style);
                break;
        }
        $this->getConfig()->save();
        foreach($this->getServer()->getOnlinePlayers() as $p){
            $this->updateHealthBar($p);
        }
        return true;
    }

    public function setPosition($position){
        switch($position){
            case "above":
            case "under":
            case "left":
            case "right":
                $this->getConfig()->set("position", $position);
                break;
        }
        $this->getConfig()->save();
        foreach($this->getServer()->getOnlinePlayers() as $p){
            $this->updateHealthBar($p);
        }
        return true;
    }

    public function updateHealthBar(Player $player){
        $style = $this->getStyle();
        $position = $this->getPosition();

        if($style === false || $position === false){
            return false;
        }

        switch($style){
            case "default":
                $style = "[" . $player->getHealth() . "/" . $player->getMaxHealth() . "]";
                break;
        }

        switch($position){
            case "above":
                $player->setNameTag($style . "\n" . $player->getDisplayName());
                break;
            case "under":
                $player->setNameTag($style . "\n" . $player->getDisplayName());
                break;
            case "left":
                $player->setNameTag($style . " " . $player->getDisplayName());
                break;
            case "right":
                $player->setNameTag($style . " " . $player->getDisplayName());
                break;
        }
        return true;
    }

    public function removeHealthBar(Player $player){
        if($this->canRemove === true){
            $player->setNameTag($player->getDisplayName());
        }
    }
}
