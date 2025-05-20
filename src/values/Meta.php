<?php

namespace values;

trait Meta
{
    private function get_meta()
    {
        $unixtime = time();

		$this->_request->getUri()->getUserInfo() !== '' ?
		$base_url = str_replace($this->_request->getUri()->getUserInfo().'@','',$this->_request->getUri()->getBaseUrl()) :
		$base_url = $this->_request->getUri()->getBaseUrl();

        $m = [
			'unixtime' => $unixtime,
			'base_url' => $base_url,
			'request_path' => $this->_request->getUri()->getPath(),
			'common_image_path' => $base_url.'/image/'
		];
        return $m;
    }

}
