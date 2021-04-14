<?php
namespace Uniondrug\Umeng;
use Uniondrug\Umeng\Android\AndroidBroadcast;
use Uniondrug\Umeng\Android\AndroidCustomizedcast;
use Uniondrug\Umeng\Android\AndroidFilecast;
use Uniondrug\Umeng\Android\AndroidUnicast;
use Uniondrug\Umeng\Ios\IOSBroadcast;
use Uniondrug\Umeng\Ios\IOSCustomizedcast;
use Uniondrug\Umeng\Ios\IOSFilecast;
use Uniondrug\Umeng\Ios\IOSGroupcast;
use Uniondrug\Umeng\Ios\IOSUnicast;

/**
 * Class UmengClient
 * @package Uniondrug\Umeng
 */
class UmengClient {
    protected $appkey           = NULL;
    protected $appMasterSecret  = NULL;
    protected $timestamp        = NULL;
    protected $validation_token = NULL;
    protected $miActivity       = NULL;
    protected $environment      = NULL;

    const TICKTER = '药联';

    function __construct($key, $secret) {
        $this->appkey            = $key;
        $this->appMasterSecret   = $secret;
        $this->timestamp         = strval(time());
        if (function_exists('config')){
            $config = config();
            $this->miActivity = $config['config']['umeng_mi_activity'];
        }
        if (method_exists(\app(), 'environment')){
            $this->environment = \app()->environment();
        }
    }

    function sendAndroidBroadcast($params) {
        try {
            $brocast = new AndroidBroadcast();
            $brocast->setAppMasterSecret($this->appMasterSecret);
            $brocast->setPredefinedKeyValue("appkey",           $this->appkey);
            if ($this->miActivity) {
                $brocast->setPredefinedKeyValue("mi_activity",  $this->miActivity);
            }
            $brocast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            $brocast->setPredefinedKeyValue("ticker",           SELF::TICKTER);
            $brocast->setPredefinedKeyValue("title",            $params['title']);
            $brocast->setPredefinedKeyValue("text",             $params['body']);
            $brocast->setPredefinedKeyValue("after_open",       "go_app");
            // Set 'production_mode' to 'false' if it's a test device.
            // For how to register a test device, please see the developer doc.
            //只允许生产群发
            $environment = $this->environment == 'production' ? 'true' : 'false';
            $brocast->setPredefinedKeyValue("production_mode", $environment);			// Set extra fields
            // [optional]Set extra fields
            if ($params['linkUrl']){
                $brocast->setExtraField("linkUrl", $params['linkUrl']);
            }
            return $brocast->send();
        } catch (\Exception $e) {
            return "Caught exception: " . $e->getMessage();
        }
    }

    function sendAndroidUnicast($params) {
        try {
            $unicast = new AndroidUnicast();
            $unicast->setAppMasterSecret($this->appMasterSecret);
            $unicast->setPredefinedKeyValue("appkey",           $this->appkey);
            if ($this->miActivity) {
                $unicast->setPredefinedKeyValue("mi_activity",  $this->miActivity);
            }
            $unicast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            // Set your device tokens here
            if (is_array($params['deviceTokens'])  && count($params['deviceTokens']) < 500){
                $unicast->setPredefinedKeyValue("type",          'listcast');
                $unicast->setPredefinedKeyValue("device_tokens", implode(',', $params['deviceTokens']));
            }else{
                $unicast->setPredefinedKeyValue("type",          'unicast');
                $unicast->setPredefinedKeyValue("device_tokens", $params['deviceTokens']);
            }
            $unicast->setPredefinedKeyValue("ticker",           self::TICKTER);
            $unicast->setPredefinedKeyValue("title",            $params['title']);
            $unicast->setPredefinedKeyValue("text",             $params['body']);
            $unicast->setPredefinedKeyValue("after_open",       'go_app');
            // Set 'production_mode' to 'false' if it's a test device.
            // For how to register a test device, please see the developer doc.
            //只允许生产群发
            $environment = $this->environment == 'production' ? 'true' : 'false';
            $unicast->setPredefinedKeyValue("production_mode", $environment);			// Set extra fields
            if ($params['linkUrl']){
                $unicast->setExtraField("linkUrl", $params['linkUrl']);
            }
            return $unicast->send();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    function sendAndroidFilecast($params) {
        try {
            $filecast = new AndroidFilecast();
            $filecast->setAppMasterSecret($this->appMasterSecret);
            if ($this->miActivity) {
                $filecast->setPredefinedKeyValue("mi_activity",  $this->miActivity);
            }
            $filecast->setPredefinedKeyValue("appkey",           $this->appkey);
            $filecast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            $filecast->setPredefinedKeyValue("ticker",           self::TICKTER);
            $filecast->setPredefinedKeyValue("title",            $params['title']);
            $filecast->setPredefinedKeyValue("text",             $params['body']);
            $filecast->setPredefinedKeyValue("after_open",       'go_app');
            if ($params['linkUrl']){
                $filecast->setExtraField("linkUrl", $params['linkUrl']);
            }
            $deviceTokens = trim(implode("\n", $params['deviceTokens']));
            $environment = in_array($this->environment, ['release', 'production']) ? 'true' : 'false';
            $filecast->setPredefinedKeyValue("production_mode", $environment);
            $filecast->uploadContents($deviceTokens);
            return $filecast->send();
        } catch (\Exception $e) {
            return "Caught exception: " . $e->getMessage();
        }
    }

    function sendAndroidGroupcast($params) {
        try {
            /*
              *  Construct the filter condition:
              *  "where":
              *	{
              *		"and":
              *		[
                *			{"tag":"test"},
                *			{"tag":"Test"}
              *		]
              *	}
              */
            $filter = 	$params['filter'];

            $groupcast = new AndroidGroupcast();
            $groupcast->setAppMasterSecret($this->appMasterSecret);
            $groupcast->setPredefinedKeyValue("appkey",           $this->appkey);
            $groupcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            // Set the filter condition
            $groupcast->setPredefinedKeyValue("filter",           $filter);
            $groupcast->setPredefinedKeyValue("ticker",           self::TICKTER);
            $groupcast->setPredefinedKeyValue("title",            $params['title']);
            $groupcast->setPredefinedKeyValue("text",             $params['body']);
            $groupcast->setPredefinedKeyValue("after_open",       'go_app');
            if ($params['linkUrl']){
                $groupcast->setExtraField("linkUrl", $params['linkUrl']);
            }
            // Set 'production_mode' to 'false' if it's a test device.
            // For how to register a test device, please see the developer doc.
            $environment = in_array($this->environment, ['release', 'production']) ? 'true' : 'false';
            $groupcast->setPredefinedKeyValue("production_mode", $environment);
            $groupcast->send();
        } catch (\Exception $e) {
            return "Caught exception: " . $e->getMessage();
        }
    }

    function sendAndroidCustomizedcast($params) {
        try {
            $customizedcast = new AndroidCustomizedcast();
            $customizedcast->setAppMasterSecret($this->appMasterSecret);
            if ($this->miActivity) {
                $customizedcast->setPredefinedKeyValue("mi_activity",  $this->miActivity);
            }
            $customizedcast->setPredefinedKeyValue("appkey",           $this->appkey);
            $customizedcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            // Set your alias here, and use comma to split them if there are multiple alias.
            // And if you have many alias, you can also upload a file containing these alias, then
            // use file_id to send customized notification.
            $customizedcast->setPredefinedKeyValue("alias",            $params['alias']);
            // Set your alias_type here
            $customizedcast->setPredefinedKeyValue("alias_type",       $params['aliasType']);
            $customizedcast->setPredefinedKeyValue("ticker",           self::TICKTER);
            $customizedcast->setPredefinedKeyValue("title",            $params['title']);
            $customizedcast->setPredefinedKeyValue("text",             $params['body']);
            $customizedcast->setPredefinedKeyValue("after_open",       'go_app');
            if ($params['linkUrl']){
                $customizedcast->setExtraField("linkUrl", $params['linkUrl']);
            }
            $customizedcast->send();
        } catch (\Exception $e) {
            return "Caught exception: " . $e->getMessage();
        }
    }

    function sendAndroidCustomizedcastFileId($params) {
        try {
            $customizedcast = new AndroidCustomizedcast();
            $customizedcast->setAppMasterSecret($this->appMasterSecret);
            if ($this->miActivity) {
                $customizedcast->setPredefinedKeyValue("mi_activity",  $this->miActivity);
            }
            $customizedcast->setPredefinedKeyValue("appkey",           $this->appkey);
            $customizedcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            // if you have many alias, you can also upload a file containing these alias, then
            // use file_id to send customized notification.
            $customizedcast->uploadContents($params['alias']);
            // Set your alias_type here
            $customizedcast->setPredefinedKeyValue("alias_type",       $params['aliasType']);
            $customizedcast->setPredefinedKeyValue("ticker",           self::TICKTER);
            $customizedcast->setPredefinedKeyValue("title",            $params['title']);
            $customizedcast->setPredefinedKeyValue("text",             $params['body']);
            $customizedcast->setPredefinedKeyValue("after_open",       'go_app');
            if ($params['linkUrl']){
                $customizedcast->setExtraField("linkUrl", $params['linkUrl']);
            }
            $environment = in_array($this->environment, ['release', 'production']) ? 'true' : 'false';
            $customizedcast->setPredefinedKeyValue("production_mode", $environment);
            $customizedcast->send();
        } catch (\Exception $e) {
            return "Caught exception: " . $e->getMessage();
        }
    }

    function sendIOSBroadcast($params) {
        try {
            $brocast = new IOSBroadcast();
            $brocast->setAppMasterSecret($this->appMasterSecret);
            if ($this->miActivity) {
                $brocast->setPredefinedKeyValue("mi_activity",  $this->miActivity);
            }
            $brocast->setPredefinedKeyValue("appkey",           $this->appkey);
            $brocast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            $brocast->setPredefinedKeyValue("alert",            [
                'title'      => $params['title'],
                //                'subtitle'   => $params['subtitle'],
                'body'       => $params['body'],
            ]);
            $brocast->setPredefinedKeyValue("badge",            0);
            $brocast->setPredefinedKeyValue("sound",            "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $environment = in_array($this->environment, ['release', 'production']) ? 'true' : 'false';
            $brocast->setPredefinedKeyValue("production_mode", $environment);
            // Set customized fields
            if ($params['linkUrl']){
                $brocast->setCustomizedField("linkUrl", $params['linkUrl']);
            }
            $brocast->send();
        } catch (\Exception $e) {
            return "Caught exception: " . $e->getMessage();
        }
    }

    function sendIOSUnicast($params) {
        try {
            $unicast = new IOSUnicast();
            $unicast->setAppMasterSecret($this->appMasterSecret);
            if ($this->miActivity) {
                $unicast->setPredefinedKeyValue("mi_activity",  $this->miActivity);
            }
            $unicast->setPredefinedKeyValue("appkey",           $this->appkey);
            $unicast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            // Set your device tokens here
            if (is_array($params['deviceTokens'])){
                $unicast->setPredefinedKeyValue("type", 'listcast');
                $unicast->setPredefinedKeyValue("device_tokens", implode(',', $params['deviceTokens']));
            }else{
                $unicast->setPredefinedKeyValue("type", 'unicast');
                $unicast->setPredefinedKeyValue("device_tokens", $params['deviceTokens']);
            }
            $unicast->setPredefinedKeyValue("alert",            [
                'title'      => $params['title'],
//                                                                    'subtitle'   => $params['subTitle'],
                'body'       => $params['body'],
            ]);
            $unicast->setPredefinedKeyValue("badge", 0);
            $unicast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $environment = in_array($this->environment, ['release', 'production']) ? 'true' : 'false';
            $unicast->setPredefinedKeyValue("production_mode", $environment);
            // Set customized fields

            if ($params['linkUrl']){
                $unicast->setCustomizedField("linkUrl", $params['linkUrl']);
            }
            return $unicast->send();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    function sendIOSFilecast($params) {
        try {
            $filecast = new IOSFilecast();
            $filecast->setAppMasterSecret($this->appMasterSecret);
            $filecast->setPredefinedKeyValue("appkey",           $this->appkey);
            $filecast->setPredefinedKeyValue("timestamp",        $this->timestamp);

            $filecast->setPredefinedKeyValue("alert", [
                'title'      => $params['title'],
                //                'subtitle'   => $params['subtitle'],
                'body'       => $params['body'],
            ]);
            $filecast->setPredefinedKeyValue("badge", 0);
            $filecast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $environment = in_array($this->environment, ['release', 'production']) ? 'true' : 'false';
            $filecast->setPredefinedKeyValue("production_mode", $environment);
            if ($params['linkUrl']){
                $filecast->setCustomizedField("linkUrl", $params['linkUrl']);
            }
            // Upload your device tokens, and use '\n' to split them if there are multiple tokens
            $deviceTokens = implode("\n", $params['deviceTokens']);
//            echo $deviceTokens.PHP_EOL;
            $filecast->uploadContents($deviceTokens);
            return $filecast->send();
        } catch (\Exception $e) {
            return "Caught exception: " . $e->getMessage();
        }
    }

    function sendIOSGroupcast($params) {
        try {
            /*
              *  Construct the filter condition:
              *  "where":
              *	{
              *		"and":
              *		[
                *			{"tag":"iostest"}
              *		]
              *	}
              */
            $filter = 	$params['filter'];

            $groupcast = new IOSGroupcast();
            $groupcast->setAppMasterSecret($this->appMasterSecret);
            if ($this->miActivity) {
                $groupcast->setPredefinedKeyValue("mi_activity",    $this->miActivity);
            }
            $groupcast->setPredefinedKeyValue("appkey",           $this->appkey);
            $groupcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            // Set the filter condition
            $groupcast->setPredefinedKeyValue("filter",           $filter);
            $groupcast->setPredefinedKeyValue("alert",            [
                'title'      => $params['title'],
                //                'subtitle'   => $params['subtitle'],
                'body'       => $params['body'],
            ]);
            $groupcast->setPredefinedKeyValue("badge", 0);
            $groupcast->setPredefinedKeyValue("sound", "chime");
            if ($params['linkUrl']){
                $groupcast->setCustomizedField("linkUrl", $params['linkUrl']);
            }
            // Set 'production_mode' to 'true' if your app is under production mode
            $environment = in_array($this->environment, ['release', 'production']) ? 'true' : 'false';
            $groupcast->setPredefinedKeyValue("production_mode", $environment);
            $groupcast->send();
        } catch (\Exception $e) {
            return "Caught exception: " . $e->getMessage();
        }
    }

    function sendIOSCustomizedcast($params) {
        try {
            $customizedcast = new IOSCustomizedcast();
            $customizedcast->setAppMasterSecret($this->appMasterSecret);
            if ($this->miActivity) {
                $customizedcast->setPredefinedKeyValue("mi_activity",  $this->miActivity);
            }
            $customizedcast->setPredefinedKeyValue("appkey",           $this->appkey);
            $customizedcast->setPredefinedKeyValue("timestamp",        $this->timestamp);

            // Set your alias here, and use comma to split them if there are multiple alias.
            // And if you have many alias, you can also upload a file containing these alias, then
            // use file_id to send customized notification.
            $customizedcast->setPredefinedKeyValue("alias",           $params['alias']);
            // Set your alias_type here
            $customizedcast->setPredefinedKeyValue("alias_type",      $params['alias_type']);
            $customizedcast->setPredefinedKeyValue("alert",           [
                'title'      => $params['title'],
                //                'subtitle'   => $params['subtitle'],
                'body'       => $params['body'],
            ]);
            $customizedcast->setPredefinedKeyValue("badge",           0);
            $customizedcast->setPredefinedKeyValue("sound",           "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $environment = in_array($this->environment, ['release', 'production']) ? 'true' : 'false';
            $customizedcast->setPredefinedKeyValue("production_mode", $environment);
            if ($params['linkUrl']){
                $customizedcast->setCustomizedField("linkUrl", $params['linkUrl']);
            }
            $customizedcast->send();
        } catch (\Exception $e) {
            return "Caught exception: " . $e->getMessage();
        }
    }
}
