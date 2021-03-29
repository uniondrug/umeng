<?php
namespace Uniondrug\Umeng\Android_b;
use Uniondrug\Umeng\AndroidNotification;

/**
 * Class AndroidUnicast
 * @package Uniondrug\Umeng\Android
 */
class AndroidUnicast extends AndroidNotification {
	function __construct() {
		parent::__construct();
		$this->data["type"] = "unicast";
		$this->data["device_tokens"] = NULL;
	}

}