<?php
namespace Uniondrug\Umeng\Ios_b;
use Uniondrug\Umeng\IOSNotification;

/**
 * Class IOSBroadcast
 * @package Uniondrug\Umeng\Ios
 */
class IOSBroadcast extends IOSNotification {
	function  __construct() {
		parent::__construct();
		$this->data["type"] = "broadcast";
	}
}