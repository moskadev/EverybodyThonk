<?php

namespace supermaxalex\EverybodyThonk;

use pocketmine\entity\Skin;
use pocketmine\utils\BinaryStream;
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
		self::$skinData = self::skinToPNG(self::decompress(self::THONK_HEAD_SKIN_DATA));
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
	 * TODO: make this code better
	 *
	 * @param Skin $skin
	 * @return Skin
	 */
	public static function setThonkHead(Skin $skin) : Skin{
		$geometryData = json_decode($skin->getGeometryData(), true);
		if(!is_array($geometryData)){
			return $skin;
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
			return $skin;
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

		$skinPNG = self::skinToPNG($skin->getSkinData());
		if(!imagecopymerge($skinPNG, self::$skinData, 0, 0, 0, 0, 32, 16, 100)){ //apply the thonk head to the actual skin
			return $skin;
		}

		return new Skin(
			"Thonk" . UUID::fromRandom(),
			self::skinToBinary($skinPNG),
			$skin->getCapeData(),
			$newGeometryName,
			json_encode($newGeometryData)
		);
	}


	/**
	 * Thanks to SalmonDE and Muqsit for their code.
	 *
	 * @param string $skinData
	 * @return resource
	 */
	private static function skinToPNG(string $skinData){
		switch(strlen($skinData)){
			case 8192:
				$maxX = 64;
				$maxY = 32;
				break;

			case 16384:
				$maxX = 64;
				$maxY = 64;
				break;

			case 65536:
				$maxX = 128;
				$maxY = 128;
				break;

			default:
				throw new \InvalidArgumentException('Inappropriate skinData length: ' . strlen($skinData));
		}

		$img = imagecreatetruecolor($maxX, $maxY);
		imagealphablending($img, false);
		imagesavealpha($img, true);
		$stream = new BinaryStream($skinData);

		for($y = 0; $y < $maxY; ++$y){
			for($x = 0; $x < $maxX; ++$x){
				$r = $stream->getByte();
				$g = $stream->getByte();
				$b = $stream->getByte();
				$a = 127 - (int) floor($stream->getByte() / 2);

				$colour = imagecolorallocatealpha($img, $r, $g, $b, $a);
				imagesetpixel($img, $x, $y, $colour);
			}
		}

		return $img;
	}

	/**
	 * https://gist.github.com/jasonwynn10/4124fe36b8e6c8ae1adc3c8af468d38f
	 *
	 * @param resource $img
	 * @return string
	 */
	private static function skinToBinary($img) : string{
		$bytes = '';
		$maxY = imagesy($img);
		$maxX = imagesx($img);

		for ($y = 0; $y < $maxY; ++$y) {
			for ($x = 0; $x < $maxX; ++$x) {
				$rgba = imagecolorat($img, $x, $y);
				$a = ((~((int)($rgba >> 24))) << 1) & 0xff;
				$r = ($rgba >> 16) & 0xff;
				$g = ($rgba >> 8) & 0xff;
				$b = $rgba & 0xff;
				$bytes .= chr($r) . chr($g) . chr($b) . chr($a);
			}
		}
		imagedestroy($img);

		return $bytes;
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