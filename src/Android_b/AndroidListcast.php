<?php
namespace Uniondrug\Umeng\Android_b;
use Uniondrug\Umeng\AndroidNotification;

class AndroidListcast extends AndroidNotification {
	function __construct() {
		parent::__construct();
		$this->data["type"] = "listcast";
		$this->data["device_tokens"] = NULL;
	}

}