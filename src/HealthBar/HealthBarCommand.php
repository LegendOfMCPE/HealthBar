<?php
namespace HealthBar;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class HealthBarCommand extends Command implements PluginIdentifiableCommand{
    public $plugin;

    public function __construct(Loader $plugin){
        parent::__construct("healthbar", "Toggle HealthBar", "Usage: /healthbar <style|position|toggle>", ["hbar"]);
        $this->setPermission("healthbar.command");
        $this->plugin = $plugin;
    }

    public function getPlugin(){
        return $this->plugin;
    }

    public function execute(CommandSender $sender, $alias, array $args){
        if(!$this->testPermission($sender)){
            return false;
        }
        if(count($args) < 1 || count($args) > 3){
            $sender->sendMessage(TextFormat::RED . "Usage: " . $this->getUsage());
            return false;
        }
        switch(count($args)){
            case 1:
                switch($args[0]){
                    case "style":
                        if(!$sender instanceof Player){
                            $sender->sendMessage(TextFormat::RED . "Please run this command in-game");
                        }else{
                            $sender->sendMessage(TextFormat::RED . "Usage: /healthbar style <desired style>");
                        }
                        return true;
                        break;
                    case "position":
                        if(!$sender instanceof Player){
                            $sender->sendMessage(TextFormat::RED . "Please run this command in-game");
                        }else{
                            $sender->sendMessage(TextFormat::RED . "Usage: /healthbar position <desired position>");
                        }
                        return true;
                        break;
                    case "toggle":
                        if(!$sender instanceof Player){
                            $sender->sendMessage(TextFormat::RED . "Usage: /healthbar toggle <on|off> <player>");
                        }else{
                            $sender->sendMessage(TextFormat::RED . "Usage: /healthbar toggle <on|off> [player]");
                        }
                        return true;
                        break;
                    default:
                        $sender->sendMessage(TextFormat::RED . "Usage: " . $this->getUsage());
                        break;
                }
                return true;
                break;
            case 2:
                switch($args[0]){
                    case "style":
                        switch($args[1]){
                            case "default":
                                $this->plugin->setStyle($args[1]);
                                $sender->sendMessage(TextFormat::YELLOW . "[HealthBar] Updating style...");
                                return true;
                                break;
                            default:
                                $sender->sendMessage(TextFormat::RED . "Unknown style given, HealthBar will not be updated.");
                                break;
                        }
                        return true;
                        break;
                    case "position":
                        switch($args[1]){
                            case "above":
                            case "under":
                            case "left":
                            case "right":
                                $this->plugin->setPosition($args[1]);
                                $sender->sendMessage(TextFormat::YELLOW . "[HealthBar] Updating position...");
                                return true;
                                break;
                            default:
                                $sender->sendMessage(TextFormat::RED . "Unknown position given, HealthBar will not be updated.");
                                break;
                        }
                        return true;
                        break;
                    case "toggle":
                        if(!$sender instanceof Player){
                            $sender->sendMessage(TextFormat::RED . "Usage: /healthbar toggle <on|off> <player>");
                        }
                        switch($args[1]){
                            case "on":
                                $this->plugin->updateHealthBar($sender);
                                return true;
                                break;
                            case "off":
                                $this->plugin->removeHealthBar($sender);
                                return true;
                                break;
                            default:
                                $sender->sendMessage(TextFormat::RED . "Usage: /healthbar toggle <on|off> [player]");
                                break;
                        }
                        return true;
                        break;
                    default:
                        $sender->sendMessage(TextFormat::RED . "Usage: " . $this->getUsage());
                        break;
                }
                return true;
                break;
            case 3:
                if($args[0] != "toggle"){
                    $sender->sendMessage(TextFormat::RED . "Usage: " . $this->getUsage());
                    return false;
                }else{
                    $player = $this->plugin->getPlayer($args[2]);
                    if($player === false){
                        $sender->sendMessage(TextFormat::RED . "[Error] Player not found.");
                    }else{
                        switch($args[1]){
                            case "on":
                                $this->plugin->updateHealthBar($player);
                                return true;
                                break;
                            case "off":
                                $this->plugin->removeHealthBar($player);
                                return true;
                                break;
                            default:
                                if(!$sender instanceof Player){
                                    $sender->sendMessage(TextFormat::RED . "Usage: /healthbar toggle <on|off> <player>");
                                }else{
                                    $sender->sendMessage(TextFormat::RED . "Usage: /healthbar toggle <on|off> [player]");
                                }
                                break;
                        }
                    }
                }
                return true;
                break;
        }
        return true;
    }
} 