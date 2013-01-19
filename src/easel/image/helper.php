<?php defined('EE_PATH') or die;

defined('JIMAGE_MAX_UPLOAD_WIDTH') or define('JIMAGE_MAX_UPLOAD_WIDTH', 600);

abstract class EEImageHelper
{
	/**
	 * An array of image sizes for the JImage::createThumbs method.
	 */
	protected static $thumbSizes = array(
		JImage::CROP_RESIZE => array(
			'660x450',
			'296x242',
			'199x160',
			'165x130',
			'150x150',
			'100x80',
			'60x60'
		)
	);

	/**
	 * Filename format to save the generated thumbnails as.
	 */
	protected static $thumbFilenameFormat = '{width}x{height}_{filename}';

	/**
	 * Create a JImage resource 
	 *
	 * @param   mixed  $source  A valid GD image resource link, or a file path to an image.
	 *
	 * @return  object  JImage instance for easy method chaining.
	 *
	 */
	public static function getInstance($source = null)
	{
		$new = new JImage($source);

		return $new;
	}

	/**
	 * Get mage sizes for resizeImage method
	 *
	 * @return  array  Resize and crop sizes to use with self::resizeImages
	 *
	 */
	public static function getThumbSizes()
	{
		return self::$thumbSizes;
	}

	/**
	 * Set image sizes for resizeImage method
	 *
	 * @param   array  $sizes  A multi dimensional array of image resize types and sizes.
	 *                         Can include one or more arrays with keys that match sizing
	 *                         method names in the {@link JImage} class.
	 *
	 * @return  void
	 *
	 * @see     self::$imageSizes
	 */
	public static function setThumbSizes(array $sizes)
	{
		foreach ($sizes as $method => $sizes)
		{
			self::$thumbSizes[$method] = $sizes;
		}
	}

	/**
	 * Get a thumbnail of the specified size
	 *
	 * @param   string  $path  The path to the image
	 * @param   string  $size  The size of the thumbnail to get
	 *
	 * @return  string  <img> tag
	 *
	 */
	public static function getThumb($path, $size)
	{
		$src = self::getThumbPath($path, $size);

		return '<img src="' . $src . '" />';
	}

	/**
	 * Get the src path to an image thumbnail
	 *
	 * @param   string  $path  The path to the image
	 * @param   string  $size  The size of the thumbnail to get
	 *
	 * @return  string  Source to image path.
	 *
	 */
	public static function getThumbPath($path, $size)
	{
		$info = pathinfo(JPATH_SITE . '/media/' . $path);

		return substr($info['dirname'], strlen(JPATH_SITE)) . '/thumbs/' . $info['filename'] . '_' . $size . '.' . $info['extension'];
	}

	/**
	 * Method to resize uploaded images
	 *
	 * @param   string  $full_dir  Directory where file is saved
	 * @param   string  $name      File name
	 * @param   array   $info      File info
	 *
	 * @return  void
	 *
	 */
	public static function resizeImage($full_dir, $name, $info = null)
	{
		if (is_null($info))
		{
			$info = JImage::getImageFileProperties($full_dir . $name);
		}

		// If the image is too wide, size it down.
		if ($info->width > JIMAGE_MAX_UPLOAD_WIDTH)
		{
			self::getInstance($full_dir.$name)->resize(JIMAGE_MAX_UPLOAD_WIDTH, null)->toFile($full_dir.$name, $info->type);
		}

		foreach (self::getThumbSizes() as $method => $sizes)
		{
			self::getInstance($full_dir.$name)->createThumbs($sizes, $method, null, self::$thumbFilenameFormat);
		}
	}

	/**
	 * Method to save uploaded images
	 *
	 * @param   string  $full_dir
	 * @param   array   $files
	 * @param   array   $data
	 * @param   bool    $resize    Whether or not to resize after upload
	 *
	 * @return  void
	 *
	 */
	public static function saveImages(&$full_dir, &$files, &$data, $resize = true)
	{
		foreach ($files['name'] as $field => $val)
		{
			if (empty($val))
			{
				continue;
			}
			$file = new stdClass;
			foreach ($files as $key => $values)
			{
				$file->$key = $values[$field];
			}
			if (!$file->error)
			{
				$parts = explode('.', $file->name);
				$file->ext = strtolower(array_pop($parts));
				$allowed_ext = explode(',', 'jpg,jpeg,png,gif,pdf');
				if (in_array($file->ext, $allowed_ext))
				{
					$file->ok = true;
				}

				$file->name = JFile::makeSafe(strtolower($file->name));

				if ($field === 'pdf')
				{
					if ($file->ok == true)
					{
						JFile::upload($file->tmp_name, $full_dir.$file->name);
						$data[$field] = $file->name;
					}
				}
				else
				{
					$file->tmp_info = JImage::getImageFileProperties($file->tmp_name);

					if (is_int($file->tmp_info->width) && is_int($file->tmp_info->height) || preg_match("/image/i", $file->tmp_info->mime))
					{
						if (is_file($full_dir.$file->name))
						{
							JFile::delete($full_dir.$file->name);
						}
						if (JFile::upload($file->tmp_name, $full_dir.$file->name))
						{
							if ($resize === true)
							{
								self::resizeImage($full_dir, $file->name, $file->tmp_info);
							}
							$data[$field] = $file->name;
						}
					}
				}
			}
		}
	}

}