<?php
namespace Uniondrug\Umeng\Ios;
use Uniondrug\Umeng\IOSNotification;

class IOSListcast extends IOSNotification {
	function __construct() {
		parent::__construct();
		$this->data["type"] = "listcast";
		$this->data["device_tokens"] = NULL;
	}

}