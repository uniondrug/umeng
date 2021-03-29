<?php
namespace Uniondrug\Umeng\Ios_b;
use Uniondrug\Umeng\IOSNotification;

class IOSGroupcast extends IOSNotification {
	function  __construct() {
		parent::__construct();
		$this->data["type"] = "groupcast";
		$this->data["filter"]  = NULL;
	}
}