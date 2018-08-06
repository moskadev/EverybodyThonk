<?php

namespace supermaxalex\EverybodyThonk;

use pocketmine\entity\Skin;
use pocketmine\Player;
use pocketmine\utils\UUID;

class ThonkHead{

	//All of those datas were made and generated thanks to Cubik Studio.
	private const THONK_HEAD_GEOMETRY_DATA = "eNqVkMEOgjAMht+l50K20qHyKmQH1EV3EIwIBwnvbuECDIgxvWxNv///2w7K4uEgg7srroDw9G31hixXSIzKIlyas6ul0UH18jdfyjPimAxSGh8MRkZmav8RiVyjlHybdhRIbY9rijHiCTmi1ISoLSTRC5dk6aIDRCOZMBjNEVEMkqlhnk77u/D+LvNgY2/QmrwYTUCOU4n6x4w2bv3jCmrDZY3Y/gv4H4vb";
	private const THONK_HEAD_SKIN_DATA = "eNrt1csJgDAQQMGAVmQBFmMxFmdR0ZMg4ueQCBucgb3vWyGm9GxdhlwyqQHT2Oer+UP/Xfs+NfrXucvHiXaDp71K+8/t0W7wttcX3z/SDb7uv2uPcoOrfWq+f631137/wveX9jX+f9SvX79+/fr/2A8AAAAAAAAAAAAAAMS3AemUKNA=";

	/** @var string */
	private static $geometryData = "";

	/** @var string */
	private static $skinData = "";

	public static function init() : void{
		self::$geometryData = self::decompress(self::THONK_HEAD_GEOMETRY_DATA);
		self::$skinData = SkinManager::skinToPNG(self::decompress(self::THONK_HEAD_SKIN_DATA));
	}

	/**
	 * @return string
	 */
	public static function getGeometryData() : string{
		return self::$geometryData;
	}

	/**
	 * @return string
	 */
	public static function getSkinData() : string{
		return self::$skinData;
	}

	/**
	 * @param Player $player
	 * @param Skin $playerSkin
	 */
	public static function applyTo(Player $player, ?Skin $playerSkin = null) : void{
		$skin = $playerSkin ?? $player->getSkin();
		$modifiedSkin = self::modifySkin($skin);

		SkinManager::setOriginalPlayerSkin($player->getRawUniqueId(), $skin);

		//hack for 1.5 #blamemojang
		$player->getServer()->removePlayerListData($player->getUniqueId());
		$player->getServer()->updatePlayerListData($player->getUniqueId(), $player->getId(), $player->getName(), $modifiedSkin, $player->getXuid());

		//old code #blamemojang
		//$player->setSkin($modifiedSkin);
		//$player->sendSkin();
	}

	/**
	 * @param Player $player
	 */
	public static function removeFrom(Player $player) : void{
		$oldSkin = SkinManager::getOriginalPlayerSkin($player->getRawUniqueId());
		//hack for 1.5 #blamemojang
		$player->getServer()->removePlayerListData($player->getUniqueId());
		$player->getServer()->updatePlayerListData($player->getUniqueId(), $player->getId(), $player->getName(), $oldSkin, $player->getXuid());

		//old code #blamemojang
		//$player->setSkin($oldSkin);
		//$player->sendSkin();
	}

	/**
	 * TODO: make this code better
	 *
	 * @param Skin $skin
	 * @return Skin
	 */
	public static function modifySkin(Skin $skin) : Skin{
		$geometryData = json_decode($skin->getGeometryData(), true);
		if(!is_array($geometryData)){
			return $skin; //avoid errors
		}
		$geometryName = $originalGeometryName = "";
		$skinGeometryName = $skin->getGeometryName();
		foreach(array_keys($geometryData) as $name){
			if(strpos($name, ":")){
				$subnames = explode(":", $name);
				if(in_array($skinGeometryName, $subnames)){
					$geometryName = $subnames[1]; //get the original and extended geometry
					break;
				}
			}
			if($skinGeometryName === $name){
				$geometryName = $name;
				break;
			}
		}
		if($geometryName === ""){
			return $skin; //avoid errors
		}
		$newGeometryData = [];
		$newGeometryName = $geometryName . random_int(1000, 100000000); //little hack to avoid problems with others geometries
		$geometry = $newGeometryData[$newGeometryName] = $geometryData[$geometryName];

		foreach($geometry["bones"] as $i => $bone){
			$name = $bone["name"];
			if($name === "head"){
				$parent = null;
				if(isset($bone["parent"])){
				   $parent = $bone["parent"];
				}
				$head = json_decode(self::$geometryData, true);
				if($parent !== null){
					$head["parent"] = $parent;
				}
				$geometry["bones"][$i] = $head;
				$newGeometryData[$newGeometryName] = $geometry;
				break;
			}
		}

		$skinPNG = SkinManager::skinToPNG($skin->getSkinData());
		if(!imagecopymerge($skinPNG, self::$skinData, 0, 0, 0, 0, 32, 16, 100)){ //apply the thonk head to the actual skin
			return $skin; //avoid errors
		}

		return new Skin(
			"Thonk" . UUID::fromRandom(),
			SkinManager::skinToBinary($skinPNG),
			$skin->getCapeData(),
			$newGeometryName,
			json_encode($newGeometryData)
		);
	}

	/**
	 * I'm the laziest guy of the world to save files into plugin's resources.
	 * I'll certainly change this code. Maybe. I said maybe.
	 *
	 * @param string $compressedString
	 * @return string
	 */
	private static function decompress(string $compressedString) : string{
		return gzuncompress(base64_decode($compressedString));
	}

}