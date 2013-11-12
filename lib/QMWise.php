<?php  
require_once "QMWiseException.php";
require_once 'QMWiseSoap.php';
/**
 * Based on the library from Questionmark for Moodle. 
 * 
 */

/*
 * Connect to the Perception server
 */
function qmwConnect() {
	try {
		$soap = new PerceptionSoap($GLOBALS['CFG']->perception_server_domain, array(
				"qmwise_webshare_name"		=>	$GLOBALS['CFG']->perception_webshare,
				"perception_server_port"	=>	$GLOBALS['CFG']->perception_hostport,
				"security_client_id"		=>	$GLOBALS['CFG']->perception_use_security ? $GLOBALS['CFG']->perception_client_id : null,
				"security_checksum"			=>	$GLOBALS['CFG']->perception_use_security ? $GLOBALS['CFG']->perception_checksum : null,
				"trust_use"					=>	$GLOBALS['CFG']->perception_use_trust,
				"trust_key"					=>	$GLOBALS['CFG']->perception_use_trust ? $GLOBALS['CFG']->perception_trustedkey : null,
				"trust_encoding"			=>	$GLOBALS['CFG']->perception_encoding,
				"debug"						=>	$GLOBALS['CFG']->debug				
			));
		return $soap;
	} catch(Exception $e) {
		throw $e;
	}
}

function qmwAbout($soap) {
	$aVersion=array();
	$rVersion=array();
	try {
		$aVersion=$soap->get_about();
		$rVersion['BuildString']=$aVersion->BuildString;
		$rVersion['BuildDate']=$aVersion->BuildDate;	
		$rVersion['Server']=$GLOBALS['CFG']->perception_server_domain;
		unset($aVersion);
		return $rVersion;
	} catch(Exception $e) {
		throw $e;
	}	
}

function qmwAssessments($soap,$course) {
	$result=array();
	try {
		$AssessmentResults=$soap->get_assessment_result_info_list();
		if($AssessmentResults) {
			$nrAssessment=count($AssessmentResults);
			for($i=1;$i<$nrAssessment;$i++) {
				if(strpos($AssessmentResults[$i]->Session_Name,$course)!==false) {
					$result[$AssessmentResults[$i]->Session_Name]['ID']=$AssessmentResults[$i]->Assessment_ID;
				}
			}
			ksort($result);
		}	
	} catch(Exception $e) {
		throw $e;
	}
	return $result;	
}

function qmwGroupResults($soap,$searchgroup,$assessmentid) {
	$result=array();
	try {
		$GroupResults=$soap->get_full_results_by_group($searchgroup);
		if($GroupResults) {
			$nrResults=count($GroupResults);
			$j=1;
			for($i=1;$i<$nrResults;$i++) {
				if($GroupResults[$i]->Result->Assessment_ID==$assessmentid) {
					$result[$j]['participant']=$GroupResults[$i]->Result->Participant;
					$result[$j]['result_id']=$GroupResults[$i]->Result->Result_ID;
					$result[$j]['when_started']=$GroupResults[$i]->Result->When_Started;
					$result[$j]['when_finished']=$GroupResults[$i]->Result->When_Finished;
					$result[$j]['max_score']=$GroupResults[$i]->Result->Max_Score;
					$result[$j]['total_score']=$GroupResults[$i]->Result->Total_Score;
					$result[$j]['still_going']=$GroupResults[$i]->Result->Still_Going;
					if (property_exists($GroupResults[$i]->AnswerList, 'Answer')) {
						foreach($GroupResults[$i]->AnswerList->Answer as $answer) {
							$result[$j]['answers'][$answer->Question_ID]['question_id']=$answer->Question_ID;
							$result[$j]['answers'][$answer->Question_ID]['actual_score']=$answer->Actual_Score;
							$result[$j]['answers'][$answer->Question_ID]['max_score']=$answer->Max_Score;
						}
					}
					$j++;
				}
			}
			usort($result, function($a, $b) { return $a['participant'] - $b['participant']; });
		}
	} catch(Exception $e) {
		throw $e;
	}
	return $result;	
}

function qmwGroupResultsMatrix($soap,$searchgroup,$assessmentid) {
	$result=array();
	try {
		$GroupResults=$soap->get_full_results_by_group($searchgroup);
		if($GroupResults) {
			$nrResults=count($GroupResults);
			$j=1;
			for($i=1;$i<$nrResults;$i++) {
				if($GroupResults[$i]->Result->Assessment_ID==$assessmentid) {
					$participant=$GroupResults[$i]->Result->Participant;
					$result[$participant]['participant']=$participant;
					if(array_key_exists('attempts',$result[$participant])) { 
						$result[$participant]['attempts']=$result[$participant]['attempts']+1;
					} else {
						$result[$participant]['attempts']=1;
					}
					if(array_key_exists('scoremax',$result[$participant])) {
						if($result[$participant]['scoremax'] < $GroupResults[$i]->Result->Total_Score) {
							$result[$participant]['scoremax']=$GroupResults[$i]->Result->Total_Score;
						}
					} else {
						$result[$participant]['scoremax']=$GroupResults[$i]->Result->Total_Score;
					}
					if(array_key_exists('scoremin',$result[$participant])) {
						if($result[$participant]['scoremin'] > $GroupResults[$i]->Result->Total_Score) {
							$result[$participant]['scoremin']=$GroupResults[$i]->Result->Total_Score;
						}
					} else {
						$result[$participant]['scoremin']=$GroupResults[$i]->Result->Total_Score;
					}
					$result[$participant]['results'][$GroupResults[$i]->Result->When_Started]['result_id']=$GroupResults[$i]->Result->Result_ID;
					$result[$participant]['results'][$GroupResults[$i]->Result->When_Started]['when_started']=$GroupResults[$i]->Result->When_Started;
					$result[$participant]['results'][$GroupResults[$i]->Result->When_Started]['when_finished']=$GroupResults[$i]->Result->When_Finished;
					$result[$participant]['results'][$GroupResults[$i]->Result->When_Started]['max_score']=$GroupResults[$i]->Result->Max_Score;
					$result[$participant]['results'][$GroupResults[$i]->Result->When_Started]['total_score']=$GroupResults[$i]->Result->Total_Score;
					$result[$participant]['results'][$GroupResults[$i]->Result->When_Started]['still_going']=$GroupResults[$i]->Result->Still_Going;
					if (property_exists($GroupResults[$i]->AnswerList, 'Answer')) {
						foreach($GroupResults[$i]->AnswerList->Answer as $answer) {
							$result[$participant]['results'][$GroupResults[$i]->Result->When_Started]['answers'][$answer->Question_ID]['question_id']=$answer->Question_ID;
							$result[$participant]['results'][$GroupResults[$i]->Result->When_Started]['answers'][$answer->Question_ID]['actual_score']=$answer->Actual_Score;
							$result[$participant]['results'][$GroupResults[$i]->Result->When_Started]['answers'][$answer->Question_ID]['max_score']=$answer->Max_Score;
						}
					}
					$j++;
				}
			}
			ksort($result);
		}
	} catch(Exception $e) {
		throw $e;
	}
	return $result;	
}

function qmwQuestionInfo($soap,$myassessmentid) {
	$result=array();
	try { 
		try {
			$soap->trace=true;
			$QuestionResults=$soap->get_question_list();
		} catch (Exception $e) {
			print $e->getCode();
			var_dump($soap->__getLastResponse());
		}
/*
		
		var_dump($QuestionResults);
		if($QuestionResults) {
			$nrResults=count($QuestionResults);
			for($i=1;$i<$nrResults;$i++) {
			

			}
			ksort($result);			
		}
*/		
	} catch(Exception $e) {
		throw $e;
	}
	return $result;	
}

?>