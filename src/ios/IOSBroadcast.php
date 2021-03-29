<?php
namespace Uniondrug\Umeng\Ios;
use Uniondrug\Umeng\IOSNotification;

class IOSBroadcast extends IOSNotification {
	function  __construct() {
		parent::__construct();
		$this->data["type"] = "broadcast";
	}
}