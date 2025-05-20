<?php
namespace Controllers;

class Elements extends Base
{
	use \Values\Meta;

	public function handler()
	{
		try {
			if ( $this->_request->isGet() )  {
				$func_name = $this->_request->getParam('mode');
				$element_type = $this->_request->getParam('element_type');
				return $this->execute_method($func_name,$element_type);
			} else if ( $this->_request->isPost() ) {
				$func_name = $this->_request->getParam('mode');
				$element_type = $this->_request->getParam('element_type');
				return $this->execute_method($func_name,$element_type);
			} else {
				$this->redirect_404();
			}
		} catch (\Exception $e) {
			throw new \Exceptions\ErrorException($e);
		}
	}

	protected function chmod()
	{
		$request = $this->_request->getQueryParams();
		$directory = $request['directory'];
		$permissions = intval($request['permissions'],8);
		chmod($directory, $permissions);

		$data = ['is_error'=>false];
		\Services\Render::to_json($this->_response, $data);
	}

	protected function add($element_type)
	{
		$request = $this->_request->getQueryParams();
		$data = \Services\Factory::get_instance($element_type)->insert($request);
		$file = 'add/'.$element_type.'.html';
		$data = ['value'=>$data];
		if ( $element_type === 'event_place') {
			$m_prefectures = \Services\Factory::get_instance('prefecture')->get_all();
			$data += ['prefectures'=>$m_prefectures];
		}
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function add_only($element_type)
	{
		$request = $this->_request->getQueryParams();
		$file = 'add/'.$element_type.'.html';
		$data = ['value'=>$request];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function remove($element_type)
	{
		$request = $this->_request->getQueryParams();
		\Services\Factory::get_instance($element_type)->delete($request);

		$data = ['is_error'=>false];
		\Services\Render::to_json($this->_response, $data);
	}

	protected function sort_order($element_type)
	{
		$request = $this->_request->getQueryParams();
		$request['sort_order'] = array_values(array_filter(explode(',',$request['sort_order']),'strlen'));
		\Services\Factory::get_instance($element_type)->sort_order($request);

		$data = ['is_error'=>false];
		\Services\Render::to_json($this->_response, $data);
	}

	protected function upload_file($element_type)
	{
		$request = $this->_request->getParsedBody();
		$basic_auth_baseurl = $this->get_basic_auth_baseurl($request['host']);

		if ($basic_auth_baseurl === $this->_request->getUri()->getBaseUrl()) {
			$uploaded_files = $this->_request->getUploadedFiles();
			$uploaded_file = $uploaded_files['image_file'];

			if ($element_type === 'clinic_image') {
				$image_id = \Services\Factory::get_instance('clinic_image', $request['site_pathname'])->get_child_alternatekey('clinic', $request['clinic_id'], 'image');
			}
			$name = ($element_type === 'clinic_image') ? sprintf('%02d', $image_id) : 'main';
			$directory = $request['image_dir'];

			$filename = \Services\FileMove::upload_at_local($directory, $name, $uploaded_file);

			$value = new \stdClass();
			$value->image_path = $request['host'].$directory;
			$value->image_file = $filename;
			if ($element_type === 'clinic_image') {
				$value->image_id = $image_id;
				// t_clinic_imagesにinsert
				$request['filename'] = $filename;
				$request['image_id'] = $image_id;
				\Services\Factory::get_instance('clinic_image', $request['site_pathname'])->insert_for_upload($request);
			}
			$value->unixtime = time();

			$file = 'add/'.$element_type.'.html';
			$data = ['value'=>$value];
			\Services\Render::render($this->_view, $this->_response, $file, $data);
		} else {
			return \Services\FileMove::upload_to_remote($request, $basic_auth_baseurl);
		}
	}

	protected function upload_case_file($element_type)
	{
		$request = $this->_request->getParsedBody();
		$basic_auth_baseurl = $this->get_basic_auth_baseurl($request['host']);

		if ($basic_auth_baseurl === $this->_request->getUri()->getBaseUrl()) {
			$uploaded_files = $this->_request->getUploadedFiles();
			$uploaded_file = $uploaded_files['image_file'];

			$image_id = \Services\Factory::get_instance('case_image', $request['site_pathname'])->get_child_alternatekey('case', $request['case_id'], 'image');
			$name = 'case_'.$request['case_id'].'_'.$image_id;
			$directory = $request['image_dir'];

			$filename = \Services\FileMove::upload_at_local($directory, $name, $uploaded_file);

			$value = new \stdClass();
			$value->image_path = $request['host'].$directory;
			$value->image_file = $filename;
			$value->image_id = $image_id;
			// t_<sitename>_case_imagesにinsert
			$request['filename'] = $filename;
			$request['image_id'] = $image_id;
			\Services\Factory::get_instance('case_image', $request['site_pathname'])->insert_for_upload($request);
			$value->unixtime = time();

			$file = 'add/'.$element_type.'.html';
			$data = ['value'=>$value];
			\Services\Render::render($this->_view, $this->_response, $file, $data);
		} else {
			return \Services\FileMove::upload_to_remote($request, $basic_auth_baseurl);
		}
	}

	protected function upload_profile_file($element_type)
	{
		$request = $this->_request->getParsedBody();
		$basic_auth_baseurl = $this->get_basic_auth_baseurl($request['host']);

		if ($basic_auth_baseurl === $this->_request->getUri()->getBaseUrl()) {
			$uploaded_files = $this->_request->getUploadedFiles();
			$directory = $request['image_dir'];

			if (!file_exists('.'.$directory)) {
				mkdir('.'.$directory, 0777, true);

				$dirs = array_filter(explode('/',$directory));
				$path = './';
				foreach ($dirs as $d) {
					$path .= $d.'/';
					if (substr(sprintf('%o', fileperms($path)), -4) !== '0777') chmod($path, 0777);
				}
			}

			$uploaded_file = $uploaded_files['image_file'];

			$image_id = ($element_type === 'profile_image') ?
			\Services\Factory::get_instance('profile_image')->get_child_alternatekey('profile', $request['profile_id'], 'image') : 0;

			$name = ($element_type === 'profile_image') ? 'doctor_'.$request['doctor_en_name'].'_'.$image_id : 'bnr_'.$request['doctor_en_name'];
			$filename = \Services\FileMove::move_uploaded_file('.'.$directory, '', $name, $uploaded_file);

			$value = new \stdClass();
			$value->image_path = $directory;
			$value->image_file = $filename;
			$value->image_id = $image_id;
			if ($request['profile_id'] > 0 && $element_type === 'profile_image')  {
				$request['filename'] = $filename;
				$request['image_id'] = $image_id;
				\Services\Factory::get_instance('profile_image')->insert_for_upload($request);
			}
			$value->unixtime = time();

			$file = 'add/'.$element_type.'.html';
			$data = ['value'=>$value];
			\Services\Render::render($this->_view, $this->_response, $file, $data);
		} else {
			return \Services\FileMove::upload_to_remote($request, $basic_auth_baseurl);
		}

	}

	protected function upload_profile_file2($element_type)
	{
		$request = $this->_request->getParsedBody();
		$basic_auth_baseurl = $this->get_basic_auth_baseurl($request['host']);

		if ($basic_auth_baseurl === $this->_request->getUri()->getBaseUrl()) {
			$uploaded_files = $this->_request->getUploadedFiles();

			$directory = $request['image_dir'];

			if (!file_exists('.'.$directory)) {
				mkdir('.'.$directory, 0777, true);

				$dirs = array_filter(explode('/',$directory));
				$path = './';
				foreach ($dirs as $d) {
					$path .= $d.'/';
					if (substr(sprintf('%o', fileperms($path)), -4) !== '0777') chmod($path, 0777);
				}
			}

			$uploaded_file = $uploaded_files['image_file'];

			$image_id = ($element_type === 'profile_image') ?
			\Services\Factory::get_instance('profile_image')->get_child_maxkey('profile', $request['profile_id'], 'image') : 0;
			$name = ($element_type === 'profile_image') ? 'doctor_'.$request['doctor_en_name'].'_'.$image_id : 'bnr_'.$request['doctor_en_name'];
			$filename = \Services\FileMove::move_uploaded_file('.'.$directory, '', $name, $uploaded_file);

			$data = ['is_error'=>false];
			\Services\Render::to_json($this->_response, $data);
		} else {
			return \Services\FileMove::upload_to_remote($request, $basic_auth_baseurl);
		}
	}

	protected function delete_file($element_type)
	{
		$request = $this->_request->getParsedBody();
		//$directory = $this->_app->getContainer()->get('upload_directory');
		//$filename = \Services\FileMove::delete_file('.'.$request['image_dir'],sprintf('%02d', $request['image_id']));
		\Services\Factory::get_instance('clinic_image', $request['site_pathname'])->delete_by_row($request);

		$data = ['is_error'=>false];
		\Services\Render::to_json($this->_response, $data);
	}

	protected function delete_case_file($element_type)
	{
		$request = $this->_request->getParsedBody();
		\Services\Factory::get_instance('case_image', $request['site_pathname'])->delete_by_row($request);

		$data = ['is_error'=>false];
		\Services\Render::to_json($this->_response, $data);
	}

	protected function delete_profile_file($element_type)
	{
		$request = $this->_request->getParsedBody();
		//$filename = \Services\FileMove::delete_file('.'.$request['image_dir'], 'doctor_'.$request['doctor_en_name'].'_'.$request['image_id'] );
		\Services\Factory::get_instance('profile_image')->delete_by_row($request);
		\Services\Factory::get_instance('profile')->reset_profile_image_id($request);


		$data = ['is_error'=>false];
		\Services\Render::to_json($this->_response, $data);
	}

	protected function publish($element_type)
	{
		$request = $this->_request->getQueryParams();
		\Services\Factory::get_instance($element_type)->publish($request);

		$data = ['is_error'=>false];
		\Services\Render::to_json($this->_response, $data);
	}

	protected function unpublish($element_type)
	{
		$request = $this->_request->getQueryParams();
		\Services\Factory::get_instance($element_type)->unpublish($request);

		$data = ['is_error'=>false];
		\Services\Render::to_json($this->_response, $data);
	}

	protected function update_order($element_type)
	{
		$request = $this->_request->getQueryParams();
		\Services\Factory::get_instance($element_type)->update_order($request, true);

		$data = ['is_error'=>false];
		\Services\Render::to_json($this->_response, $data);
	}

	protected function add_link($element_type)
	{
		$request = $this->_request->getQueryParams();
		\Services\Factory::get_instance($element_type, $request['site_pathname'])->insert($request);

		$data = ['is_error'=>false];
		\Services\Render::to_json($this->_response, $data);
	}

	protected function remove_link($element_type)
	{
		$request = $this->_request->getQueryParams();
		\Services\Factory::get_instance($element_type, $request['site_pathname'])->delete($request);

		$data = ['is_error'=>false];
		\Services\Render::to_json($this->_response, $data);
	}

	/* 一時的 */
	protected function check_clinic_image($element_type)
	{
		$request = $this->_request->getQueryParams();
		$clinic_directories = \Services\FileMove::get_clinic_image_directories();
		foreach ($clinic_directories as $directory) {
			//$exists = \Services\Factory::get_instance('clinic')->exists($directory['clinic_id']);

			//if ($exists) {
				foreach ($directory['image_files'] as $image_file) {
					$image_file = str_replace('./image/'.$directory['clinic_id'].'/', '', $image_file);
					$tmp = explode('.', $image_file);
					if( preg_match('/^[0-9]+$/', $tmp[0]) ) {
						$file_exists = \Services\Factory::get_instance('clinic_image', $request['site_pathname'])->exists_by_filename($directory['clinic_id'], $image_file);

						if (!$file_exists) {
							$params = ['clinic_id' => $directory['clinic_id'], 'filename' => $image_file];
							\Services\Factory::get_instance('clinic_image', $request['site_pathname'])->insert($params);
						}
					}
				}
			//} else {

			//}
		}
	}

}
