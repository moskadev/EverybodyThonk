<?php

namespace supermaxalex\EverybodyThonk;

use pocketmine\entity\Skin;
use pocketmine\utils\BinaryStream;

class SkinManager{

	private static $originalPlayerSkins = [];

	/**
	 * Thanks to SalmonDE and Muqsit for their code.
	 *
	 * @param string $skinData
	 * @return resource
	 */
	public static function skinToPNG(string $skinData){
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
	public static function skinToBinary($img) : string{
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
	 * @param string $playerUniqueId
	 * @param Skin $skin
	 */
	public static function setOriginalPlayerSkin(string $playerUniqueId, Skin $skin) : void{
		self::$originalPlayerSkins[$playerUniqueId] = $skin;
	}

	/**
	 * @param string $playerUniqueId
	 * @return null|Skin
	 */
	public static function getOriginalPlayerSkin(string $playerUniqueId) : ?Skin{
		return self::$originalPlayerSkins[$playerUniqueId];
	}
}