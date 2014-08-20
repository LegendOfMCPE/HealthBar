<?php
namespace HealthBar;

use HealthBar\OtherEvents\EssentialsPEEvents;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Loader extends PluginBase{
    private $canRemove;

    public function onEnable(){
        $this->saveDefaultConfig();
        $this->getServer()->getCommandMap()->register("healthbar", new HealthBarCommand($this));
        $this->getServer()->getPluginManager()->registerEvents(new EventHandler($this), $this);
        $this->checkConfiguration();

        $ess = $this->getServer()->getPluginManager()->getPlugin("EssentialsPE");
        if($ess instanceof Plugin and $ess->isEnabled()){
            $this->getServer()->getPluginManager()->registerEvents(new EssentialsPEEvents($this), $this);
        }
    }

    public function onDisable(){
        if($this->canRemove === true){
            foreach($this->getServer()->getOnlinePlayers() as $p){
                $this->setHealthBar($p, false);
            }
        }
    }

    private function checkConfiguration(){
        if(!$this->getStyle()){
            $this->setStyle("default");
        }elseif(!$this->getPosition()){
            $this->setPosition("above");
        }else{
           $this->enableHealthBar();
        }
    }

    private function enableHealthBar(){
        $this->canRemove = true;
        foreach($this->getServer()->getOnlinePlayers() as $p){
            $this->setHealthBar($p, true);
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

    protected $players = [];

    public function getPlayer($player){
        $player = strtolower($player);
        $r = false;
        foreach($this->getServer()->getOnlinePlayers() as $p){
            if(strtolower($p->getName()) == $player || strtolower($p->getDisplayName()) == $player){
                $r = $p;
            }
        }
        return $r;
    }

    public function getStyle(){
        $style = strtolower($this->getConfig()->get("style"));
        switch(strtolower($style)){
            case "default":
                return $style;
                break;
        }
        return false;
    }

    public function getPosition(){
        $position = strtolower($this->getConfig()->get("position"));
        switch($position){
            case "above":
            case "under":
            case "left":
            case "right":
                return $position;
                break;
        }
        return false;
    }

    public function setStyle($style){
        $style = strtolower($style);
        switch($style){
            case "default":
                $this->getConfig()->set("style", $style);
                $this->getConfig()->save();
                break;
        }
        foreach($this->getServer()->getOnlinePlayers() as $p){
            $this->updateHealthBar($p, $p->getMaxHealth(), $p->getDisplayName());
        }
        $this->getConfig()->reload();
        return true;
    }

    public function setPosition($position){
        $position = strtolower($position);
        switch($position){
            case "above":
            case "under":
            case "left":
            case "right":
                $this->getConfig()->set("position", $position);
                $this->getConfig()->save();
                break;
        }
        foreach($this->getServer()->getOnlinePlayers() as $p){
            $this->updateHealthBar($p, $p->getMaxHealth(), $p->getDisplayName());
        }
        $this->getConfig()->reload();
        return true;
    }

    public function updateHealthBar(Player $player, $health = false, $name = false){
        if(!$this->isHealthBarEnabled($player)){
            return false;
        }
        $style = $this->getStyle();
        $position = $this->getPosition();
        if($style === false || $position === false){
            return false;
        }

        $maxhealth = $player->getMaxHealth();
        if($health === false){
            $health = $player->getHealth();
        }elseif($name === false){
            $name = $player->getDisplayName();
        }

        $bar = $this->getHealthBar();
        $bar = str_replace("maxhealth", $maxhealth, $bar);
        $bar = str_replace("health", $health, $bar);
        $bar = str_replace("name", $name, $bar);

        $player->setNameTag($bar);
        return true;
    }

    public function removeHealthBar(Player $player){
        if($this->canRemove === true){
            $player->setNameTag($player->getDisplayName());
        }
    }

    public function getHealthBar(){
        $style = $this->getStyle();
        $position = $this->getPosition();
        $maxhealth = "maxhealth";
        $health = "health";
        $name = "name";

        if($style === false || $position === false){
            return false;
        }

        switch($style){
            case "default":
                $style = "[" . $health . "/" . $maxhealth . "]";
                break;
        }

        $bar = "";
        switch($position){
            case "above":
                $bar = $style . "\n" . $name;
                break;
            case "under":
                $bar = $name . "\n" . $style;
                break;
            case "left":
                $bar = $style . " " . $name;
                break;
            case "right":
                $bar = $name . " " . $style;
                break;
        }
        return $bar;
    }

    public function isHealthBarEnabled(Player $player){
        if(!isset($this->players[$player->getName()])){
            $this->players[$player->getName()] = true;
        }
        if($this->players[$player->getName()] === false){
            return false;
        }else{
            return true;
        }
    }

    public function setHealthBar(Player $player, $value = true, $health = false){
        if(!is_bool($value)){
            return false;
        }
        $this->players[$player->getName()] = $value;
        if($value === false){
            $this->removeHealthBar($player);
        }else{
            $this->updateHealthBar($player, $health);
        }
        return true;
    }
}
