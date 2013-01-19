<?php defined('EE_PATH') or die;

abstract class EEHtml extends JHtml
{
	/**
	 * Includes assets from media directory, looking in the
	 * template folder for a style override to include.
	 *
	 * @param   string  $filename   Path to file.
	 * @param   string  $extension  Current extension name. Will auto detect component name if null.
	 *
	 * @return  mixed  False if asset type is unsupported, nothing if a css or js file, and a string if an image
	 */
	public static function asset($filename, $extension = null, $attributes = array())
	{
		if (is_null($extension))
		{
			$extension = array_pop(explode(DIRECTORY_SEPARATOR, JPATH_COMPONENT));
		}

		$toLoad = "$extension/$filename";

		// Discover the asset type from the file name
		$type = substr($filename, (strrpos($filename, '.') + 1));

		switch (strtoupper($type))
		{
			case 'CSS':
				self::stylesheet($toLoad, false, true, false);
				break;
			case 'JS':
				self::script($toLoad, false, true);
				break;
			case 'GIF':
			case 'JPG':
			case 'JPEG':
			case 'PNG':
			case 'BMP':
				return self::image($toLoad, null, $attributes, true);
				break;
			default:
				return false;
		}
	}
}
