<?php
namespace Controllers;

/* 矯正歯科,インプラント,審美歯科ネットプラス（接続管理） */
class Connection extends Base
{
	use \Values\Meta;

    public function handler()
    {
    	if ( $this->_request->isGet() ) {
			$pathname = $this->_request->getUri()->getPath();
			if ( strpos($pathname,'login') !== false )  {
				$func_name = 'show_login';
			} elseif ( strpos($pathname,'logout') !== false ) {
				$func_name = 'action_logout';
			}
			return $this->execute_method($func_name);
		} elseif ( $this->_request->isPost() ) {
			$func_name = 'action_login';
			return $this->execute_method($func_name);
		}
	}

	protected function show_login()
	{
		$path_array = explode('/',$this->_request->getUri()->getPath());
		$site_id = $path_array[3];
		if ( empty($site_id) ) {
			return $this->redirect_404();
		}

		$info = \Services\Factory::get_instance('site')->get_by_id((int)$site_id);
		$value['site_id'] = (int)$site_id;
		$value['site_name'] = $info->site_name;
		$value['site_pathname'] = $info->site_pathname;
		$value['request_path'] = isset($_SESSION["requeset_path"]) ? $_SESSION["requeset_path"]:"";

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'connection/cmslogin.twig', [
			'meta' => $this->get_meta(),
			'value'  => $value
		]);
	}

	protected function action_login()
	{
		$request = $this->_request->getParsedBody();
		$info = \Services\Factory::get_instance('site')->get_by_id((int)$request['site_id']);
		$account = \Services\Factory::get_instance('account')->validate_account($request);

		if (!empty($account)) {
			$_SESSION['account_info_'.$account->site_id] = $account;
			$url = (!empty($request['request_path'])) ? $request['request_path'] : '/'.$info->site_pathname.'_cms/';
			return \Services\Render::redirect($this->_response, $url);
		}

		$value['site_id'] = (int)$request['site_id'];
		$value['site_name'] = $info->site_name;
		$value['site_pathname'] = $info->site_pathname;
		$value['request_path'] = $request['request_path'];
		$value['error'] = 'ログインに失敗しました。';

		/* 出力 */
		\Services\Render::render($this->_view, $this->_response, 'connection/cmslogin.twig', [
			'meta' => $this->get_meta(),
			'value'  => $value
		]);
	}

	protected function action_logout()
	{
		$path_array = explode('/',$this->_request->getUri()->getPath());
		$site_id = $path_array[3];
		if ( empty($site_id) ) {
			return $this->redirect_404();
		}

		$_SESSION['account_info_'.$site_id] = null;
		return \Services\Render::redirect($this->_response, '/cms/login/'.$site_id);
	}

	private function show_window($f, $d)
	{
        \Services\Render::render($this->_view, $this->_response, $f, $d);
	}


}
