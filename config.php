<?php  /// QMP module Configuration File 

date_default_timezone_set('Europe/Amsterdam');
unset($CFG);

$server='QMP5test';
//$server='QMP5summ';
//$server='QMP5form';
//$server='QMP5dev';


$CFG = new stdClass();
$CFG->debug						= 0;

// these settings are copied from the Test Harnass
$CFG->perception_use_trust		= 0;
$CFG->perception_encoding		= '';
$CFG->perception_trustedkey		= '';
$CFG->perception_use_security	= 1;
$CFG->perception_client_id		= '';

switch ($server) {
	case 'QMP5test':
		$CFG->perception_server_domain 	= ''; // hostname without http://. Ex: server1.questionmark.com
		$CFG->server_description		= 'test';
		$CFG->perception_webshare		= 'qmwise5';
		$CFG->perception_hostport		= '80';
		$CFG->perception_checksum		= ''; // calculated in the test harnass
		$CFG->perception_client_id		= '';
		break;
	case 'QMP5dev':
		$CFG->perception_server_domain 	= ''; // hostname without http://. Ex: server1.questionmark.com
		$CFG->server_description		= 'dev';
		$CFG->perception_webshare		= 'qmwise5';
		$CFG->perception_hostport		= '80';
		$CFG->perception_checksum		= ''; // calculated in the test harnass
		$CFG->perception_client_id		= '';
		break;
	case 'QMP5form':
		$CFG->perception_server_domain 	= ''; // hostname without http://. Ex: server1.questionmark.com
		$CFG->server_description		= 'formative';
		$CFG->perception_webshare		= 'qmwise5';
		$CFG->perception_hostport		= '80';
		$CFG->perception_checksum		= ''; // calculated in the test harnass
		$CFG->perception_client_id		= '';
		break;
	case 'QMP5summ':
		$CFG->perception_server_domain 	= ''; // hostname without http://. Ex: server1.questionmark.com
		$CFG->server_description		= 'summative';
		$CFG->perception_webshare		= 'qmwise5';
		$CFG->perception_hostport		= '80';
		$CFG->perception_checksum		= ''; // calculated in the test harnass
		$CFG->perception_client_id		= '';
		break;
}		
?>