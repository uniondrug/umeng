<?php
namespace Uniondrug\Umeng\Android_b;
use Uniondrug\Umeng\AndroidNotification;

class AndroidBroadcast extends AndroidNotification {
	function  __construct() {
		parent::__construct();
		$this->data["type"] = "broadcast";
	}
}