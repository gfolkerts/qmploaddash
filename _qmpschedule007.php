<?php
require_once 'config.php';
require_once 'lib/qmwise.php';
require_once 'lib/functions.inc';

try {
	$soap=qmwConnect();
} catch(Exception $e) {
	ScheduleError($e);
}
if(IsSet($soap)) {
	$aSchedules=array();
	$aGroupedSchedules=array();
	$aScheduleCount=array();
	try {
		try {
			$aSchedules=$soap->get_schedule_list();
		} catch(Exception $e) {
			ScheduleError($e);
		}
	
	$today=date('Ymd');
	if(isset($_GET['filter'])) {
		if(CheckString($_GET['filter'])==true) {
			if($_GET['filter']=='next7') { $date = strtotime('+7 day'); $today=date('Ymd'); }
			if($_GET['filter']=='next14') { $date = strtotime('+14 day'); $today=date('Ymd'); }
			if($_GET['filter']=='nodate') { $today=0;}
			if($_GET['filter']=='all') { $today=0;}
		}	
	}  
	if($aSchedules) {			
		$nrSchedules=count($aSchedules);
		for($i=1;$i<$nrSchedules;$i++) {
			// Only add the schedules asked for
			$SchedDate=substr($aSchedules[$i]->Schedule_Stops,0,10);
			$SchedDate=str_replace('-','',$SchedDate)+0;				
			if($SchedDate >= $today) {

			// key is groep + assessmentid		
			$key=$aSchedules[$i]->Group_Name.$aSchedules[$i]->Assessment_ID;
			if($aSchedules[$i]->Group_Name=='') {
				$key=$aSchedules[$i]->Participant_Name.$aSchedules[$i]->Assessment_ID;
			}	
			
			if(array_key_exists($key,$aGroupedSchedules)) {
				$aGroupedSchedules[$key]['Count']=$aGroupedSchedules[$key]['Count']+1;
			} else {	
				$aGroupedSchedules[$key]['Count']=1;
				$aGroupedSchedules[$key]['Assessment_ID']=$aSchedules[$i]->Assessment_ID;
				$aGroupedSchedules[$key]['Schedule_Name']=$aSchedules[$i]->Schedule_Name;
				$aGroupedSchedules[$key]['Max_Attempts']=$aSchedules[$i]->Max_Attempts;
				$aGroupedSchedules[$key]['Monitored']=$aSchedules[$i]->Monitored;
				$aGroupedSchedules[$key]['Time_Limit']=$aSchedules[$i]->Time_Limit;
				$aGroupedSchedules[$key]['Participant_Name']=$aSchedules[$i]->Participant_Name;
				$aGroupedSchedules[$key]['Group_Name']=$aSchedules[$i]->Group_Name;
				$aGroupedSchedules[$key]['Delivery']='';
				if($aSchedules[$i]->Web_Delivery==1) {
					$aGroupedSchedules[$key]['Delivery'].=' W';
				}
				if($aSchedules[$i]->Offline_Delivery==1) {
					$aGroupedSchedules[$key]['Delivery'].=' O';
				}
				$tmpdate=explode("T",$aSchedules[$i]->Schedule_Starts);
				$aGroupedSchedules[$key]['Schedule_Start_Date']=$tmpdate[0];
				$aGroupedSchedules[$key]['Schedule_Start_Time']=$tmpdate[1];
				$tmpdate=explode("T",$aSchedules[$i]->Schedule_Stops);
				$aGroupedSchedules[$key]['Schedule_Stop_Date']=$tmpdate[0];
				$aGroupedSchedules[$key]['Schedule_Stop_Time']=$tmpdate[1];
				$aGroupedSchedules[$key]['Restrict_Times']=$aSchedules[$i]->Restrict_Times;
				$aGroupedSchedules[$key]['Restrict_Attempts']=$aSchedules[$i]->Restrict_Attempts;
			}
			}
		}
		
		// With the filtered list of schedules get the details for each assessment
		foreach ($aGroupedSchedules as $key => $value) {
			try {
				$AssessmentSettings=$soap->get_assessment_definition($value['Assessment_ID']);
				if($AssessmentSettings) {
					$aGroupedSchedules[$key]['Session_Name']=$AssessmentSettings->Assessment->Session_Name;
					$aGroupedSchedules[$key]['Save_Answers']=$AssessmentSettings->Assessment->Save_Answers;
					$aGroupedSchedules[$key]['Save_Answer_Data']=$AssessmentSettings->Assessment->Save_Answer_Data;
					$aGroupedSchedules[$key]['Open_Session']=$AssessmentSettings->Assessment->Open_Session;
					$aGroupedSchedules[$key]['Session_Timed']=$AssessmentSettings->Assessment->Session_Timed;
					$aGroupedSchedules[$key]['Time_Limit']=$AssessmentSettings->Assessment->Time_Limit;
					$aGroupedSchedules[$key]['Template_Name']=$AssessmentSettings->Assessment->Template_Name;
					$aGroupedSchedules[$key]['Permit_External_Call']=$AssessmentSettings->Assessment->Permit_External_Call;
					$aGroupedSchedules[$key]['Modified_Date']=$AssessmentSettings->Assessment->Modified_Date;
					$aGroupedSchedules[$key]['Monitor_Required']=$AssessmentSettings->Assessment->Monitored;
					$aGroupedSchedules[$key]['Block_Count']=count($AssessmentSettings->AssessmentBlockList->AssessmentBlock);
				}
			} catch(Exception $e) {
				$aGroupedSchedules[$key]['Session_Name']='Failed to load assessment';
				$aGroupedSchedules[$key]['Save_Answers']='';
				$aGroupedSchedules[$key]['Save_Answer_Data']='';
				$aGroupedSchedules[$key]['Open_Session']='';
				$aGroupedSchedules[$key]['Session_Timed']='';
				$aGroupedSchedules[$key]['Time_Limit']='';
				$aGroupedSchedules[$key]['Template_Name']='';
				$aGroupedSchedules[$key]['Permit_External_Call']='';
				$aGroupedSchedules[$key]['Modified_Date']='';
				$aGroupedSchedules[$key]['Monitor_Required']='';
				$aGroupedSchedules[$key]['Block_Count']='';		
			}
		}
		
		$j=1;
		// JSON output genereren.
		header('Content-type: application/json');		
		echo '{';
		echo '"total": '.count($aGroupedSchedules).',';
		echo '"page": 0,';
		echo '"records": [';
		
		foreach ($aGroupedSchedules as $key => $value) {
			echo '{"recid": '.$j.',';
			echo '"assessment_id": "'.$value['Assessment_ID'].'",';
			echo '"schedule_name": "'.$value['Schedule_Name'].'",';
			echo '"schedule_count": "'.$value['Count'].'",';
			echo '"max_attempts": "'.$value['Max_Attempts'].'",';
			echo '"monitored": "'.$value['Monitored'].'",';
			echo '"time_limit": "'.$value['Time_Limit'].'",';
			echo '"participant_name": "';
			if($value['Count']==1)
				echo $value['Participant_Name'];
			echo '",';
			echo '"group_name": "'.$value['Group_Name'].'",';
			echo '"delivery": "'.$value['Delivery'].'",';
			echo '"restrict_times": "'.$value['Restrict_Times'].'",';
			echo '"schedule_start_date": "'.$value['Schedule_Start_Date'].'",';
			echo '"schedule_start_time": "'.$value['Schedule_Start_Time'].'",';
			echo '"schedule_stop_date": "'.$value['Schedule_Stop_Date'].'",';
			echo '"schedule_stop_time": "'.$value['Schedule_Stop_Time'].'",';
			echo '"restrict_attempts": "'.$value['Restrict_Attempts'].'",';
			echo '"session_name": "'.$value['Session_Name'].'",';
			echo '"monitor_required": "'.$value['Monitor_Required'].'",';
			echo '"save_answers": "'.$value['Save_Answers'].'",';
			echo '"save_answer_data": "'.$value['Save_Answer_Data'].'",';
			echo '"open_session": "'.$value['Open_Session'].'",';
			echo '"session_timed": "'.$value['Session_Timed'].'",';
			echo '"time_limit": "'.$value['Time_Limit'].'",';
			echo '"template_name": "'.$value['Template_Name'].'",';
			echo '"permit_external_call": "'.$value['Permit_External_Call'].'",';
			echo '"modified_date": "'.$value['Modified_Date'].'",';
			echo '"block_count": "'.$value['Block_Count'].'"';
			echo '}';
			if($j<count($aGroupedSchedules)) {
				echo ',';
			}	
			$j++;
		}
		
		echo ']';
		echo '}';
	}	
} catch(Exception $e) {
	ScheduleError($e);
}

unset($aSchedules);
}

function ScheduleError($e) {
	$code=str_replace(chr(34),'\'',$e->getCode());
	$message=str_replace(chr(34),'\'',$e->getMessage());
	$message=str_replace(chr(10),'\'',$message);	
	header('Content-type: application/json');		
	echo '{';
	echo '"total": 1,';
	echo '"page": 0,';
	echo '"records": [';
	echo '{"recid": 0,';
	echo '"schedule_name": "An error occured (code ' . $code . '): ' . $message.'"';
	echo '}';
	echo ']';
	echo '}';
}

?>