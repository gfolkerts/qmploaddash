<?php
require_once 'config.php';
require_once 'lib/qmwise.php';
require_once 'lib/functions.php';

try {
	$soap=qmwConnect();
} catch(Exception $e) {
	InfoError($e,$CFG);
}
if(IsSet($soap)) {
	$aVersion=array();
	$aVersion=$soap->get_about();
	echo "Questionmark Perception ".$aVersion->BuildString." build on ".$aVersion->BuildDate."\n";
	echo "Running on ".$GLOBALS['CFG']->perception_server_domain." (".$GLOBALS['CFG']->server_description.")";
	unset($aVersion);
}

function InfoError($e) {
	$code=str_replace(chr(34),'\'',$e->getCode());
	$message=str_replace(chr(34),'\'',$e->getMessage());
	$message=str_replace(chr(10),'\'',$message);
	if($GLOBALS['CFG']->debug==0) {
		echo "Failed to connect to QMP server:\n";
		if ($GLOBALS['CFG']->perception_hostport=='443') {
			echo "https://";
		} else {	
			echo "http://";
		}	
		echo $GLOBALS['CFG']->perception_server_domain."/".$GLOBALS['CFG']->perception_webshare;
	} else {
		echo 'Fatal error: code ' . $code . ' ' . $message.'"';
	}
}


?>