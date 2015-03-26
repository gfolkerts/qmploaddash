<?php
// general functions
//

function GetVisitorIP() {
	$ipaddress='';
	if (isset($_SERVER["REMOTE_ADDR"])) {
		$ipaddress=$_SERVER["REMOTE_ADDR"];
	} else if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
		$ipaddress=$_SERVER["HTTP_X_FORWARDED_FOR"];
	} else if (isset($_SERVER["HTTP_CLIENT_IP"])) { 
		$ipaddress=$_SERVER["HTTP_CLIENT_IP"];
	}
	return $ipaddress;
} 

function CheckString($myparam) {
  // myparam may only consist of "word" characters (letters, numbers, and the underscore)
  if(preg_match("/^[-a-z0-9_]/i",$myparam)) {
    return true;
  } else {
    return false;
  }	
}

function CheckNum($myparam) {
  $myCheckedParam=0;
  if(is_numeric($myparam)) {
    $myCheckedParam=$myparam;
  }	
  return $myCheckedParam;
}

function error($myparam) {
  echo $myparam;
} 

?>