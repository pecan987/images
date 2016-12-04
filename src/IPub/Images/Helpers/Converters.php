<?php
/**
 * Converters.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Images!
 * @subpackage     Helpers
 * @since          2.0.0
 *
 * @date           13.05.16
 */

declare(strict_types = 1);

namespace IPub\Images\Helpers;

use Nette;
use Nette\Utils;

/**
 * Attributes parsers
 *
 * @package        iPublikuj:Images!
 * @subpackage     Helpers
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class Converters
{
	/**
	 * Parse size string into width and height
	 *
	 * @param string $size
	 *
	 * @return array
	 */
	public static function parseSizeString(string $size) : array
	{
		$width = $height = 0;

		$size = Utils\Strings::lower($size);

		// Extract size
		if (strpos($size, 'x') !== FALSE) {
			list($width, $height) = explode('x', $size);

			settype($width, 'int');
			settype($height, 'int');

		} elseif ($size !== 'original') {
			$width = (int) $size;

		} elseif ($size === 'original') {
			$width = $height = NULL;
		}

		return [$width, $height];
	}

	/**
	 * Create size string for provider eg.: original, 50x150, 50
	 *
	 * @param string|int|NULL $size
	 *
	 * @return string
	 */
	public static function createSizeString($size)
	{
		if (empty($size) || $size === NULL) {
			return 'original';
		}

		if (strpos($size, 'x') !== FALSE) {
			list($width, $height) = explode('x', $size);

			$size = (int) $width;

			if ((int) $height > 0) {
				$size = (int) $width . 'x' . (int) $height;
			}

			return (string) $size;
		}

		return (string) $size;
	}

	/**
	 * @param string|int $algorithm
	 *
	 * @return int|NULL
	 */
	public static function parseAlgorithm($algorithm)
	{
		if (empty($algorithm) || $algorithm === NULL) {
			return NULL;
		}

		if (!is_int($algorithm) && !is_array($algorithm)) {
			switch (strtolower($algorithm)) {
				case 'fit':
					return Utils\Image::FIT;

				case 'fill':
					return Utils\Image::FILL;

				case 'exact':
					return Utils\Image::EXACT;

				case 'shrink_only':
				case 'shrinkonly':
				case 'shrink-only':
					return Utils\Image::SHRINK_ONLY;

				case 'stretch':
					return Utils\Image::STRETCH;

				default:
					return NULL;
			}

		}

		if (is_int($algorithm) && in_array($algorithm, [Utils\Image::FIT, Utils\Image::FILL, Utils\Image::EXACT, Utils\Image::SHRINK_ONLY, Utils\Image::STRETCH], TRUE)) {
			return $algorithm;
		}

		return NULL;
	}

	/**
	 * Convert algorithm to test representation
	 *
	 * @param int|string $algorithm
	 *
	 * @return string|NULL
	 */
	public static function createAlgorithmString($algorithm)
	{
		if (is_numeric($algorithm)) {
			switch ($algorithm) {
				case Utils\Image::FIT:
					return 'fit';

				case Utils\Image::FILL:
					return 'fill';

				case Utils\Image::EXACT:
					return 'exact';

				case Utils\Image::SHRINK_ONLY:
					return 'shrink-only';

				case Utils\Image::STRETCH:
					return 'stretch';

				default:
					return NULL;
			}
		}

		if (is_string($algorithm)) {
			$algorithm = strtolower($algorithm);

			if (in_array($algorithm, ['shrink_only', 'shrinkonly', 'shrink-only'], TRUE)) {
				return 'shrink-only';
			}

			if (!in_array($algorithm, ['fit', 'fill', 'exact', 'stretch'], TRUE)) {
				return NULL;
			}
		}

		return $algorithm;
	}

	/**
	 * @param string $file
	 *
	 * @return array
	 */
	public static function parseImageString(string $file) : array
	{
		// Extract info from file string
		preg_match('/\b(?:(?P<provider>[a-zA-Z]+)\:)?(?P<storage>[a-zA-Z_]+)\:\/\/(?:(?<namespace>.+)\/)?(?<name>.+)\.{0,1}(?P<extension>[a-zA-Z]{0,3}+)/i', $file, $matches);

		$filename = NULL;
		if(isset($matches['name']) && isset($matches['extension']) && $matches['extension'] != '') {
			$filename = $matches['name'] . '.' . $matches['extension'];
		} elseif(isset($matches['name'])) {
			$filename = $matches['name'];
		}

		$arguments = [
			'provider'  => isset($matches['provider']) && !empty($matches['provider']) ? $matches['provider'] : NULL,
			'storage'   => isset($matches['storage']) && !empty($matches['storage']) ? $matches['storage'] : NULL,
			'namespace' => isset($matches['namespace']) && trim(trim(trim($matches['namespace']), '/'), DIRECTORY_SEPARATOR) ? $matches['namespace'] : NULL,
			'filename'  => $filename,
		];

		return $arguments;
	}
}
