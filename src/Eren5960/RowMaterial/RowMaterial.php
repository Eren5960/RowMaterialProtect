<?php 

declare(strict_types=1);

namespace Eren5960\RowMaterial;

use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\permission\Permission;
use pocketmine\event\block\BlockBreakEvent;

class RowMaterial extends PluginBase implements Listener {

	/** @var Config[] */
	protected $config = [];
	/** @var string[] */
	protected $levels = [];
	/** @var string[] */
	protected $blocks = [];

	/**
	 * @return void
	 */
	public function onLoad(): void{
		$this->initPermissions();
	}

	/**
	 * @return void
	 */
	public function onEnable(): void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->initConfig();
		$this->initLevels();
		$this->initBlocks();
		$this->getLogger()->info("\n§ewww.github.com/Eren5960\n§c Please follow §b@Eren5960 §cin github.\n");
	}

	/**
	 * @return void
	 */
	private function initConfig(): void{
		$this->saveDefaultConfig();
		$this->config = (new Config($this->getDataFolder() . "config.yml", Config::YAML))->getAll();
	}

	/**
	 * @return void
	 */
	private function initLevels(): void{
		$this->levels = $this->config["levels"];
	}

	/**
	 * @return void
	 */
	private function initBlocks(): void{
		$this->blocks = $this->config["blocks"];
	}

	/**
	 * @return void
	 */
	private function initPermissions(): void{
		$perms = [];
		foreach ($this->blocks as $block) {
			$perms[$this->getPermission($block)]["default"] = "op";
		}
		Permission::loadPermissions($perms);
	}

	/**
	 * @param string $blockname
	 *
	 * @return string
	 */
	private function getPermission(string $blockname): string{
		return "area." . strtolower(str_replace(" ", ".", $blockname));
	}

	/**
	 * @param Player $player
	 *
	 * @return bool
	 */
	public function inLevel(Player $player): bool{
		return in_array($player->getLevel()->getFolderName(), $this->levels);
	}

	/**
	 * @param Block $block
	 *
	 * @return bool
	 */
	public function inBlock(Block $block): bool{
		return in_array($block->getName(), $this->blocks);
	}

	/**
	 * @param Player $player
	 * @param Block $block
	 *
	 * @return bool
	 */
	public function hasPermission(Player $player, Block $block): bool{
		return $player->hasPermission($this->getPermission($block->getName()));
	}

	/**
	 * @param Player $player
	 *
	 * @return void
	 */
	public function sendMessage(Player $player): void{
		$message = $this->config["message"];
		switch ($this->config["message-type"]) {
			case 'popup':
				$player->sendPopup($message);
				break;
			case 'title':
				$player->addTitle($message);
				break;
			case 'chat':
			default:
				$player->sendMessage($message);
				break;
		}
	}

	/**
	 * @param BlockBreakEvent $event
	 */
	public function onBreak(BlockBreakEvent $event): void{
		$player = $event->getPlayer();
		$block = $event->getBlock();
		if(!$this->inLevel($player)) return;

		if($this->inBlock($block)){
			if(!$this->hasPermission($player, $block)){
				$event->setCancelled();
				$this->sendMessage($player);
			}
		}
	}
}
