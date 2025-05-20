<?php
namespace Controllers;

/* 矯正歯科ネットプラス（その他コントローラー） */
class Kyousei extends Base
{
	use \Values\Meta;

    public function handler()
    {
    	if ($this->authorize() !== '') {
    		return \Services\Render::redirect($this->_response, $this->authorize());
    	}

		$f = '';
		$d = '';
		if ( $this->_request->getUri()->getPath() === '/kyousei_cms/')  {
			$f = 'top/cmstop.twig';
			$info = \Services\Factory::get_instance('site')->get_by_id(3);
			$h['site_id'] = 3;
			$h['site_name'] = $info->site_name;
			$h['site_pathname'] = $info->site_pathname;
			$h['plus_url'] = $info->plus_url;
			$d = [
				'value' => $h,
				'meta' => $this->get_meta(),
				'header' => ['js_list' => ['cms/top.js']]
			];
			$this->show_window($f, $d);
		}elseif ( $this->_request->getUri()->getPath() === '/notfound/')  {
			return $this->redirect_404();
		} elseif ( $this->_request->getUri()->getPath() === '/' ) {
			$this->show_portal_top();
		}

	}

	private function show_window($f, $d)
	{
        \Services\Render::render($this->_view, $this->_response, $f, $d);
	}


}
