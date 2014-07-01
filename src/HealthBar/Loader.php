<?php
namespace HealthBar;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Loader extends PluginBase implements Listener{
    private $style;
    private $styles;
    public function onEnable(){
        $this->saveDefaultConfig();
        $this->saveResource("styles.yml");
        $style = $this->getConfig()->get("style");
        $this->styles = new Config($this->getDataFolder()."styles.yml", Config::YAML);
        if(!$this->styles->exists($style)){
            $this->getLogger()->error("Style \"$style\" doesn't exist! The default style \"default\" will be used instead.");
            $style = "default";
        }
        if(!$this->styles->exists($style)) $style = ["@cur@ / @max@ hearts", "@name"];
        else $style = $this->styles->get($style);
        $this->style = (array) $style;
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
        $tag = $this->formatNametag($player);
        $tag->setNameTag($tag);
        return true;
        /*$config = $this->getConfig();
        $style = $config->get("style");
        $position = $config->get("position");
        if($style == "default"){
            $style = $player->getHealth() . "/" . $player->getMaxHealth();
        }else{
            $this->getServer()->getLogger()->error("[HealthBar] Unknown style");
            return false;
        }
        if($position == "above"){
            $player->setNameTag($style . "\n" . $player->getDisplayName());
        }
        elseif($position == "under"){
            $player->setNameTag($player->getDisplayName() . "\n" . $style);
        }else{
            $this->getServer()->getLogger()->error("[HealthBar] Unknown position");
            return false;
        }
        return true;*/
    }
    private function formatNametag(Player $player){
        $style = implode("\n", $this->style);
        $max = $player->getMaxHealth() / 2;
        $cur = $player->getHealth() / 2;
        $rem = $max - $cur;
        $tag = preg_replace_callback("#@([a-zA-Z0-9]{1,})@#", function($match) use($cur, $max, $player, $rem){
            switch($match[1]){
                case "cur": case "current": return "$cur";
                case "max": return "$max";
                case "lb": case "linebreak": return "\n";
                case "name": return $player->getName();
                case "chatname": return $player->getDisplayName();
                case "rem": return "$rem";
                default: return "@$match[1]@";
            }
        }, $style);
        $tag = preg_replace_callback("/#([^#~]{1,})#([0-9\\.]{1,})#/", function($match){
            return str_repeat($match[1], (int) $match[2]);
        }, $tag);
        $tag = preg_replace_callback("/#([^#~]{1,})~([^#]{1,})#([0-9\\.]{1,})#/", function($match){
            $times = floatval($match[3]);
            $int = (int) floor($times);
            $out = str_repeat($match[1], $int);
            if($times - $int > 0) $out .= $match[2];
            return $out;
        }, $tag);
        return $tag;
    }
}
