<?php
namespace Services;

use Slim\Http\UploadedFile;

class FileMove
{
	public static function move_uploaded_file($directory, $original_filename, $basename, UploadedFile $uploaded_file)
	{
		if ($uploaded_file->getError() === UPLOAD_ERR_OK) {
			//$del_file = self::delete_file($directory, $basename);

			$extension = pathinfo($uploaded_file->getClientFilename(), PATHINFO_EXTENSION);
			$filename = sprintf('%s.%0.8s', $basename, $extension);

			$uploaded_file->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
			//self::set_image_compression_quality($directory, $filename, 50);
			if ($extension !== 'webp') {
				$filename = self::create_webp_image($directory, $basename, $filename);
			}
		} else {
			$filename = $original_filename;
		}
		return $filename;
	}

	public static function upload_at_local($directory, $name, $uploaded_file)
	{
		if (!file_exists('.'.$directory)) {
			mkdir('.'.$directory, 0777, true);

			$dirs = array_filter(explode('/',$directory));
			$path = './';
			foreach ($dirs as $d) {
				$path .= $d.'/';
				if (substr(sprintf('%o', fileperms($path)), -4) !== '0777') chmod($path, 0777);
			}
		}
		$filename = \Services\FileMove::move_uploaded_file('.'.$directory, '', $name, $uploaded_file);
		return $filename;
	}

	public static function upload_to_remote($request, $basic_auth_baseurl, $from_local = false)
	{
		$curl = curl_init();
		$api_url = $basic_auth_baseurl.'/cms/element/';

		$cfile = ($from_local) ?
		new \CURLFile($request['filename']) : new \CURLFile($_FILES["image_file"]["tmp_name"]);

		$params = ['image_file' => $cfile];
		foreach ($request as $key => $value) { 
			$params[$key] = $value;
		}

		curl_setopt($curl, CURLOPT_URL, $api_url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $params );

		$data = curl_exec($curl);
		return $data;
	}

	public static function delete_file($directory, $name)
    {
        $del_file = self::exists_file($directory, $name);
        if ($del_file !== '') {
            unlink($del_file);
        }

        return $del_file;
    }

	public static function exists_file($directory, $name)
    {
        $files = glob($directory.DIRECTORY_SEPARATOR.'*');
        $filename = '';
        foreach ($files as $file) {
            if (strpos($file, $name) !== false) {
				$filename = $file;
                break;
            }
        }
        return $filename;
    }

	public static function exists_file_header($url)
	{
		$headers = get_headers($url);
		if (is_array($headers) && count($headers) > 0) {
			if (strpos($headers[0],'OK')) {
            	return $url;
        	}
    	}
    	return false;
	}

	/* 画像ファイル名取得 */
	public static function get_filename($directory,$name) {
		$filename = self::exists_file($directory,(string)$name);
		if ( $filename !== '' ) {
			$filename = str_replace($directory.DIRECTORY_SEPARATOR,'',$filename);
		}
		return $filename;
	}

	/* ImageMagickで画像の圧縮 */
	public static function set_image_compression_quality($directory, $filename, $quality) {
    	$imagick = new \Imagick(realpath($directory . DIRECTORY_SEPARATOR . $filename));
    	$imagick->setImageCompressionQuality($quality);
    	$imagick->writeImage($directory . DIRECTORY_SEPARATOR . $filename);
	}

	/* ImageMagickでwebp生成 */
	public static function create_webp_image($directory, $basename, $filename) {
    	$imagick = new \Imagick(realpath($directory . DIRECTORY_SEPARATOR . $filename));
    	$imagick->setImageFormat('webp');

    	$webp_filename = $basename.'.webp';
    	$imagick->writeImage($directory . DIRECTORY_SEPARATOR . $webp_filename);
    	unlink(realpath($directory . DIRECTORY_SEPARATOR . $filename));
    	return $webp_filename;
	}

	/* 一時的（矯正歯科ネットプラス本番公開用) */
	public static function get_clinic_image_directories()
	{
		$dir = './image/';
		$list = glob($dir.'*', GLOB_ONLYDIR);

		$directories = [];
		$id = 0;
		foreach ($list as $directory) {
			$directory = str_replace($dir, '', $directory);
			if (preg_match("/^[0-9]+$/", $directory)) {
				$child_dir = $dir.$directory.'/';
				$file_list = glob($child_dir.'*');

				$directories[$id]['clinic_id'] = $directory;
				$directories[$id]['image_files'] = $file_list;
				$id++;
			}
		}
		return $directories;
	}
}
