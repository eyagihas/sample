<?php
namespace Controllers;

/* プラス インビザライン記事入力フォーム */
class Invisalign extends Base
{
	use \Values\Meta;
	use \Values\Invisalign;

	protected $_type = 'portal';
	protected $_site = null;

    public function handler()
    {
    	if ($this->authorize() !== '') {
    		return \Services\Render::redirect($this->_response, $this->authorize());
    	}

    	try {
    		$path_array = explode('/',$this->_request->getUri()->getPath());
    		if (strpos($path_array[1], 'cms') === false) {
    			$this->_type = 'portal';
    		} else {
    			$this->_type = 'cms';
    			$site_pathname = str_replace('_cms', '', $path_array[1]);
    			$this->_site = \Services\Factory::get_instance('site')->get_by_pathname($site_pathname);
    		}

			if ( $this->_request->isGet() ) {
				$func_name = 'show_form';
				return $this->execute_method($func_name);
			} elseif ( $this->_request->isPost() ) {
				$mode = $this->_request->getParam('mode');
				return $this->execute_method($mode);
			}
		} catch (\Exception $e) {
			throw new \Exceptions\ErrorException($e);
		}
	}

	protected function show_form()
	{
		if ($this->_request->isPost()) {
			$request = $this->_request->getParsedBody();
			$clinic_id = $request['clinic_id'];
			$clinic = \Services\Factory::get_instance('kyousei_clinic')->get_by_id($clinic_id);
			$isPrev = true;
		} else if ($this->_type === 'cms') {
			$path_array = explode('/',$this->_request->getUri()->getPath());
			$clinic_id = $path_array[3];
			$clinic = \Services\Factory::get_instance('kyousei_clinic')->get_by_id($clinic_id);
			$isPrev = false;
		} else {
			$clinic_id = null;
			$clinic = null;
			$isPrev = false;
		}

		$header = new \stdClass();
		$header->js_list = $this->get_js_list($this->_type.'_form');
		$header->css_list = $this->get_css_list($this->_type.'_form');

		$value = new \stdClass();
		$value->headline = $this->get_values($this->_type.'_form', $clinic);
		if ($this->_type === 'portal') $value->includes = $this->get_form_includes();
		if ($this->_type === 'cms') $this->set_site_values($value, $this->_site);
		$value->isPrev = $isPrev;

		/* 「戻る」からの画面遷移用　データ取得 */
		$self_payments = new \stdClass();
		$value->info = \Services\Factory::get_instance('kyousei_self_clinic')->get_by_id($clinic_id);
		$value->operation_times = \Services\Factory::get_instance('kyousei_self_operation_time')->get_by_clinic_id_cms($clinic_id);
		$self_payments = \Services\Factory::get_instance('kyousei_self_payment')->get_by_clinic_id_cms($clinic_id);
		$payments = \Services\Factory::get_instance('payment')->get_all();
		$this->set_payments($payments, $self_payments);
		$value->payments = $payments;

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'invisalign/info_form.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value
		]);
	}

	protected function check_tel()
	{
		$request = $this->_request->getParsedBody();
		$tel = $request['tel1'].$request['tel2'].$request['tel3'];
		$clinic = \Services\Factory::get_instance('kyousei_clinic')->exists_by_tel($tel);
		

		if ($clinic) {
			$value = new \stdClass();
			$value->headline = $this->get_values($this->_type.'_form', $clinic);

			$exists = \Services\Factory::get_instance('kyousei_self_clinic')->exists($clinic->clinic_id);
			if (!$exists) {
				\Services\Factory::get_instance('kyousei_self_clinic')->insert($clinic->clinic_id);
			}
			$self_payments = new \stdClass();
			$value->info = \Services\Factory::get_instance('kyousei_self_clinic')->get_by_id($clinic->clinic_id);
			$value->operation_times = \Services\Factory::get_instance('kyousei_self_operation_time')->get_by_clinic_id_cms($clinic->clinic_id);
			$self_payments = \Services\Factory::get_instance('kyousei_self_payment')->get_by_clinic_id_cms($clinic->clinic_id);
			$payments = \Services\Factory::get_instance('payment')->get_all();
			$this->set_payments($payments, $self_payments);
			$value->payments = $payments;

			/* 出力 */
			$file = 'invisalign/basic_info.html';
			\Services\Render::render($this->_view, $this->_response, $file, ['value'=>$value]);
		} else {
			$data = ['exists'=>false];
			return \Services\Render::to_json($this->_response, $data);
		}
		
	}

	protected function show_attribute_form()
	{
		$request = $this->_request->getParsedBody();

		/* 入力された基本情報の更新 */
		if (!isset($request['is_prev']) && $this->_type === 'portal') {
			$this->update_basic_info($request);
		}

		/* 画面遷移用　データ取得 */
		$self_attributes = array();
		$data = \Services\Factory::get_instance('kyousei_self_clinic')->get_attributes_by_id($request['clinic_id']);
		$self_attributes = explode(',', $data->attribute_id_list);

		$header = new \stdClass();
		$header->js_list = $this->get_js_list($this->_type.'_form');
		$header->css_list = $this->get_css_list($this->_type.'_form');

		$value = new \stdClass();
		$clinic = \Services\Factory::get_instance('kyousei_clinic')->get_by_id($request['clinic_id']);
		$value->headline = $this->get_values($this->_type.'_form', $clinic);
		if ($this->_type === 'portal') $value->includes = $this->get_form_includes();
		if ($this->_type === 'cms') $this->set_site_values($value, $this->_site);

		$attributes = \Services\Factory::get_instance('attribute')->get_list(['type'=>'kyousei', 'is_self_visible'=>true]);
		$this->set_attributes($attributes, $self_attributes);
		$value->attributes = $attributes;
		$value->clinic_id = $request['clinic_id'];

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'invisalign/attribute_form.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value
		]);

	}

	protected function show_guideline_form()
	{
		$request = $this->_request->getParsedBody();

		/* 入力された治療項目の更新 */
		if (!isset($request['is_prev']) && $this->_type === 'portal') {
			$this->update_attribute_list($request);
		}

		$header = new \stdClass();
		$header->js_list = $this->get_js_list($this->_type.'_form');
		$header->css_list = $this->get_css_list($this->_type.'_form');

		$value = new \stdClass();
		$clinic = \Services\Factory::get_instance('kyousei_clinic')->get_by_id($request['clinic_id']);
		$value->headline = $this->get_values($this->_type.'_form', $clinic);
		if ($this->_type === 'portal') $value->includes = $this->get_form_includes();
		if ($this->_type === 'cms') $this->set_site_values($value, $this->_site);
		$value->clinic_id = $request['clinic_id'];

		/* 画面遷移用　データ取得 */
		$value->invisalign_fees = \Services\Factory::get_instance('kyousei_self_invisalign_fee')->get_by_clinic_id_cms($request['clinic_id']);
		$value->guideline = \Services\Factory::get_instance('kyousei_self_clinic')->get_guideline_by_id($request['clinic_id']);
		$value->exterior_image = $this->get_image_url('/image/'.$request['clinic_id'], 'exterior');
		$value->interior_image = $this->get_image_url('/image/'.$request['clinic_id'], 'interior');

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'invisalign/guideline_form.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value
		]);

	}

	protected function show_feature_form()
	{
		$request = $this->_request->getParsedBody();

		/* 入力された記事項目の更新 */
		if ($this->_type === 'portal') {
			if ($request['next_feature_id'] == 1) {
				if (!isset($request['is_prev'])) $this->update_fee_info($request);
			} else {
				if (!isset($request['is_prev'])) $this->update_feature_info($request);
			}			
		}

		$header = new \stdClass();
		$header->js_list = $this->get_js_list($this->_type.'_form');
		$header->css_list = $this->get_css_list($this->_type.'_form');

		$value = new \stdClass();
		$clinic = \Services\Factory::get_instance('kyousei_clinic')->get_by_id($request['clinic_id']);
		$value->headline = $this->get_values($this->_type.'_form', $clinic);
		if ($this->_type === 'portal') $value->includes = $this->get_form_includes();
		if ($this->_type === 'cms') $this->set_site_values($value, $this->_site);

		$features = \Services\Factory::get_instance('feature_type')->get_all();
		$value->features = $features;
		$value->feature_id = $request['next_feature_id'];
		$value->feature_id_circle = $request['next_feature_id_circle'];
		$value->clinic_id = $request['clinic_id'];

		/* 画面遷移用　データ取得 */
		$value->feature = \Services\Factory::get_instance('kyousei_self_clinic_feature')->get_by_clinic_feature_id($request['clinic_id'], $request['next_feature_id']);
		$value->feature->feature_id_circle = $request['next_feature_id_circle'];
		if ($value->feature->feature_type_id == 1){
			$value->feature_image = $this->get_image_url('/image/'.$request['clinic_id'], 'feature_'.$value->feature->feature_id);
		} else {
			$value->case_before_image = $this->get_image_url('/image/'.$request['clinic_id'], 'case_'.$value->feature->feature_id.'_before');
			$value->case_after_image = $this->get_image_url('/image/'.$request['clinic_id'], 'case_'.$value->feature->feature_id.'_after');
		}

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'invisalign/feature_form.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value
		]);

	}

	protected function get_feature_form()
	{
		$request = $this->_request->getParsedBody();

		$value = new \stdClass();
		$clinic = \Services\Factory::get_instance('kyousei_clinic')->get_by_id($request['clinic_id']);
		$value->headline = $this->get_values($this->_type.'_form', $clinic);

		$feature_type = \Services\Factory::get_instance('feature_type')->get_feature_type_name($request['feature_type_id']);
		$value->feature = [
			'feature_id' => $request['feature_id'],
			'feature_id_circle' => $request['feature_id_circle'],
			'feature_type_id' => $request['feature_type_id'],
			'feature_type_name' => $feature_type->feature_type_name
		];
		$value->clinic_id = $request['clinic_id'];

		$file = ($request['feature_type_id'] == 1) ? 'invisalign/basic_feature_form.html' : 'invisalign/case_feature_form.html' ;

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, $file, ['value'  => $value]);

	}

	protected function show_thanks()
	{
		$request = $this->_request->getParsedBody();

		$this->update_feature_info($request);
		\Services\Factory::get_instance('kyousei_self_clinic')->fix_data($request['clinic_id']);
		
		$clinic = \Services\Factory::get_instance('kyousei_clinic')->get_cms_clinic_name($request['clinic_id']);
		$this->send_mail_to_operation($clinic);

		$header = new \stdClass();
		$header->js_list = $this->get_js_list($this->_type.'_form');
		$header->css_list = $this->get_css_list($this->_type.'_form');

		$value = new \stdClass();
		$clinic = \Services\Factory::get_instance('kyousei_clinic')->get_by_id($request['clinic_id']);
		$value->headline = $this->get_values($this->_type.'_form', $clinic);
		if ($this->_type === 'portal') $value->includes = $this->get_form_includes();
		$value->clinic_id = $request['clinic_id'];

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'invisalign/thanks.twig', [
			'meta' => $this->get_meta(),
			'header' => $header,
			'value'  => $value
		]);
	}

	protected function upload_file($element_type)
	{
		$request = $this->_request->getParsedBody();
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

		$name = $request['element_type'];
		$filename = \Services\FileMove::move_uploaded_file('.'.$directory, '', $name, $uploaded_file);

		$value = new \stdClass();
		$value->image_path = $directory;
		$value->image_file = $filename;
		$value->unixtime = time();

		$file = 'invisalign/image.html';
		$data = ['value'=>$value];
		\Services\Render::render($this->_view, $this->_response, $file, $data);
	}

	protected function update_basic_info($request)
	{
		\Services\Factory::get_instance('kyousei_self_clinic')->update($request);
		\Services\Factory::get_instance('kyousei_self_operation_time')->delete_by_clinic_id($request['clinic_id']);
		$operation_times = $this->get_operation_time_array($request);
		foreach ($operation_times as $operation_time) {
			\Services\Factory::get_instance('kyousei_self_operation_time')->insert($operation_time);
		}
		\Services\Factory::get_instance('kyousei_self_payment')->delete_by_clinic_id($request['clinic_id']);
		$payments = $this->get_payment_array($request);
		foreach ($payments as $payment) {
			\Services\Factory::get_instance('kyousei_self_payment')->insert($payment);
		}
	}

	protected function update_attribute_list($request)
	{
		$attribute_id_list = (!empty($request['attribute_id'])) ? implode(',', $request['attribute_id']) : '';
		\Services\Factory::get_instance('kyousei_self_clinic')->update_attribute_id_list($attribute_id_list, $request['clinic_id']);
	}

	protected function update_fee_info($request)
	{
		\Services\Factory::get_instance('kyousei_self_invisalign_fee')->delete_by_clinic_id($request['clinic_id']);
		$fees = $this->get_fee_array($request);
		foreach ($fees as $fee) {
			\Services\Factory::get_instance('kyousei_self_invisalign_fee')->insert($fee);
		}
		\Services\Factory::get_instance('kyousei_self_clinic')->update_guideline_column($request);		
	}

	protected function update_feature_info($request)
	{
		\Services\Factory::get_instance('kyousei_self_clinic_feature')->delete_by_clinic_feature_id($request);

		if ($request['feature_type_id'] == 1) {
			\Services\Factory::get_instance('kyousei_self_clinic_feature')->insert_basic_feature($request);
		} else if ($request['feature_type_id'] == 2) {
			\Services\Factory::get_instance('kyousei_self_clinic_feature')->insert_case_feature($request);
		}	
	}

	protected function authorize()
    {
        if ( empty($_SESSION["clinic_id"]) ) {
        	/*
            $_SESSION["requeset_path"] = $pathname;
            return '/cms/login/'.$info->site_id;
            */
            return '';
        } else {
        	return '';
        }
    }

    private function set_payments(&$payments, $self_payments)
	{
		foreach ($self_payments as $key => $value) {
			foreach ($payments as $p_key => $parent) {
				foreach ($parent->payments as $c_key => $payment) {
					if ($value->payment_id == $payment->payment_id) {
						$payment->checked = 'checked';
						$payment->free_text = $value->free_text;
					}
				}
			}
		}
	}

    private function set_attributes(&$attributes, $self_atatributes)
	{
		foreach ($self_atatributes as $value) {
			foreach ($attributes as $attribute) {
				if ($value == $attribute->attribute_id) {
					$attribute->checked = 'checked';
				}
			}
		}
	}

	private function get_image_url($directory, $name)
	{
		$filename = ($this->_type === 'portal') ? 
		\Services\FileMove::exists_file('.'.$directory, $name):
		\Services\FileMove::exists_file_header($this->_site->plus_url.$directory.'/'.$name.'.webp');

		if (!empty($filename)) {
			$url = ($this->_type === 'portal') ? ltrim($filename, '.') : $this->_site->plus_url.$directory.'/'.$name.'.webp';
		} else {
			$url = '';
		}
		return $url;
	}

	private function get_operation_time_array($request)
	{
		$list = [];
		for ($i = 0; $i < count($request['start_at']); $i++) {
			if (!empty($request['start_at'][$i]) || !empty($request['end_at'][$i])) {
				$list[$i] = [
					'clinic_id' => $request['clinic_id'],
					'start_at' => $request['start_at'][$i],
					'end_at' => $request['end_at'][$i],
					'is_mon_open' => $request['is_mon_open'][$i],
					'is_tue_open' => $request['is_tue_open'][$i],
					'is_wed_open' => $request['is_wed_open'][$i],
					'is_thu_open' => $request['is_thu_open'][$i],
					'is_fri_open' => $request['is_fri_open'][$i],
					'is_sat_open' => $request['is_sat_open'][$i],
					'is_sun_open' => $request['is_sun_open'][$i]
				];
			}
		}
		return $list;

	}

	private function get_payment_array($request)
	{
		$list = [];
		if (!empty($request['payment_id'])) {
			for ($i = 0; $i < count($request['payment_id']); $i++) {
				$free_text = ($request['payment_id'][$i] == 299 || $request['payment_id'][$i] == 399) ?
				$request['free_text_ex'.$request['payment_id'][$i]] : null;
				$list[$i] = [
					'clinic_id' => $request['clinic_id'],
					'payment_id' => $request['payment_id'][$i],
					'free_text' => $free_text
				];
			}
		}

		return $list;
	}

	private function get_fee_array($request)
	{
		$list = [];
		for ($i = 0; $i < count($request['fee_name']); $i++) {
			$list[$i] = [
				'clinic_id' => $request['clinic_id'],
				'fee_id' => $i+1,
				'fee_name' => $request['fee_name'][$i],
				'fee' => $request['fee'][$i]
			];
		}
		return $list;

	}

	//運営宛てメール送信処理
	private function send_mail_to_operation($clinic)
	{
		$subject = '['.$clinic->clinic_id.' '.$clinic->clinic_name.']インビザラインフォーム送信';
		$body = '医院ID：'.$clinic->clinic_id.' '.$clinic->clinic_name.'がインビザラインフォームを送信しました。';

		$to = \Services\Mail::$operationAddress;
		$cc = \Services\Mail::$operationCcAddress;

		$header = \Services\Mail::getHeader(\Services\Mail::$siteName, \Services\Mail::$siteAddress, \Services\Mail::$siteAddress, \Services\Mail::$siteAddress, $cc);
		\Services\Mail::sendMail($to, $header, $subject, $body, "UTF-8");
		return true;
	}
}
