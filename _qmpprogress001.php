<?php
require_once 'config.php';
require_once 'lib/qmwise.php';
require_once 'lib/functions.php';

$soap=qmwConnect();

$assessmentid='0';
if(IsSet($_GET['assessmentid'])) {
	if(CheckString($_GET['assessmentid'])==true) {
		$assessmentid=$_GET['assessmentid']; 
	}
}

if($CFG->debug==1) { error_log($assessmentid); }

if($assessmentid=='0') {
	ProgressError('','No assessment id');
} else {	
	$aProgress=array();
	$aProgressRecords=array();
	try {
		$aProgress=$soap->get_result_list_by_assessment($assessmentid);
		if($aProgress) {			
			$nrProgress=count($aProgress);
			for($i=0;$i<$nrProgress;$i++) {
				$key=$aProgress[$i]->Result_ID.$aProgress[$i]->Assessment_ID;
				$aProgressRecords[$key]['Participant']=$aProgress[$i]->Participant;
				$aProgressRecords[$key]['Member_Group']=$aProgress[$i]->Member_Group;
				$aProgressRecords[$key]['IP_Address']=$aProgress[$i]->IP_Address;
				$aProgressRecords[$key]['Still_Going']=$aProgress[$i]->Still_Going;
				$tmpdate=explode("T",$aProgress[$i]->When_Started);
				$aProgressRecords[$key]['When_Started_Date']=$tmpdate[0];
				$aProgressRecords[$key]['When_Started_Time']=$tmpdate[1];
				$tmpdate=explode("T",$aProgress[$i]->Session_Last_Modified);
				$aProgressRecords[$key]['Last_Modified_Date']=$tmpdate[0];
				$aProgressRecords[$key]['Last_Modified_Time']=$tmpdate[1];
				$tmpdate=explode("T",$aProgress[$i]->When_Finished);				
				$aProgressRecords[$key]['When_Finished_Date']=$tmpdate[0];
				$aProgressRecords[$key]['When_Finished_Time']=$tmpdate[1];
			}
		
			$j=1;
			// transform the array to JSON output.
			header('Content-type: application/json');		
			echo '{';
			echo '"total": '.count($aProgressRecords).',';
			echo '"page": 0,';
			echo '"records": [';
		
			foreach ($aProgressRecords as $key => $value) {
				echo '{"recid": '.$j.',';
				echo '"participant": "'.$value['Participant'].'",';
				echo '"member_group": "'.$value['Member_Group'].'",';
				echo '"ip_address": "'.$value['IP_Address'].'",';
				echo '"still_going": "'.$value['Still_Going'].'",';
				echo '"when_started_date": "'.$value['When_Started_Date'].'",';
				echo '"when_started_time": "'.$value['When_Started_Time'].'",';
				echo '"last_modified_date": "'.$value['Last_Modified_Date'].'",';
				echo '"last_modified_time": "'.$value['Last_Modified_Time'].'",';
				echo '"when_finished_date": "'.$value['When_Finished_Date'].'",';
				echo '"when_finished_time": "'.$value['When_Finished_Time'].'"';
				echo '}';
				if($j<count($aProgressRecords)) {
					echo ',';
				}	
				$j++;
			}
			
			echo ']';
			echo '}';
		}	
		unset($aProgress);
		unset($aProgressRecords);
	} catch(Exception $e) {
		ProgressError($e,'');
	}
}

function ProgressError($e,$mess) {
	if(isset($e)) {
		$code=str_replace(chr(34),'\'',$e->getCode());
		$message=str_replace(chr(34),'\'',$e->getMessage());
		$message=str_replace(chr(10),'\'',$message);	
	} else {
		$code='0';
		$message=$mess;
	}
	
	header('Content-type: application/json');		
	echo '{';
	echo '"total": 1,';
	echo '"page": 0,';
	echo '"records": [';
	echo '{"recid": 0,';
	echo '"participant": "An error occured (code ' . $code . '): ' . $message.'"';
	echo '}';
	echo ']';
	echo '}';
}

?>