<?php
namespace Services;

class Image
{
	public static function move_image_file($data, $directory, $filename)
	{
		if  ( is_dir($directory) ) {
			file_put_contents($directory.$filename, $data);

			return true;
		} else {
			if ( mkdir($directory, 0777) ) {

				chmod($directory, 0777);

				file_put_contents($directory.$filename, $data);
				return true;
			} else {
				return false;
			}
		}
	}

	public static function move_image_file_as_webp($data, $directory, $filename)
	{
		$extension = pathinfo($filename, PATHINFO_EXTENSION);
		$basename = str_replace('.'.$extension, '', $filename);

		if  ( is_dir($directory) ) {
			file_put_contents($directory.$filename, $data);

			if ($extension !== 'webp') {
				$filename = \Services\FileMove::create_webp_image($directory, $basename, $filename);
			}
			return $filename;
		} else {
			if ( mkdir($directory, 0777) ) {

				chmod($directory, 0777);

				file_put_contents($directory.$filename, $data);
				if ($extension !== 'webp') {
					$filename = \Services\FileMove::create_webp_image($directory, $basename, $filename);
				}
				return $filename;
			} else {
				return null;
			}
		}
	}

}
