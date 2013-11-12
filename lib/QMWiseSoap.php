<?php

/**
 * PerceptionSoap
 * @author Bart Nagel
 * Accesses the various QMWise methods which will be useful for the Moodle 
 * Perception connector project
 * Requires the QMWiseException class
 * https://www.questionmark.com/Developer/guides/qmwise_quick_ref/default.htm
 */
require_once "QMWiseException.php";
class PerceptionSoap {
	private $debug;
	private $soap;
	private $http;
	private $header;
	/**
	 * constructor
	 * Get the WSDL file from the Perception server and set up the Soap client 
	 * with the available methods
	 * Throws whatever exception the Soap constructor might throw, for instance 
	 * if it can't get the WSDL file
	 * Parameters are the Perception server's domain and an array of options 
	 * (purposes of which are obvious from the source code below)
	 */
	public function __construct($perception_server_domain, $options = array()) {
		$qmwise_webshare_name = isset($options["qmwise_webshare_name"]) ? $options["qmwise_webshare_name"] : "QMWISe4";
		$perception_server_port = isset($options["perception_server_port"]) ? $options["perception_server_port"] : 80;
		if($perception_server_port==443) { $http='https'; } else { $http='http'; }
		
		$security_client_id = isset($options["security_client_id"]) ? $options["security_client_id"] : null;
		$security_checksum = isset($options["security_checksum"]) ? $options["security_checksum"] : null;
		$trust_key = isset($options["trust_key"]) ? $options["trust_key"] : null;
		$trust_use = isset($options["trust_use"]) ? $options["trust_use"] : null;
		$trust_encoding = isset($options["trust_encoding"]) ? $options["trust_encoding"] : null;
		$trust_digest = isset($options["trust_digest"]) ? $options["trust_digest"] : null;
		$trust_signature = isset($options["trust_signature"]) ? $options["trust_signature"] : null;
		$this->debug = isset($options["debug"]) ? $options["debug"] : false;
		
		if($this->debug) {
			print "Server: $perception_server_domain\n";
			print "QMWise: $qmwise_webshare_name\n";
			print "Port: $perception_server_port\n";
			print "http: $http\n";
			print "ClientID: $security_client_id\n";
			print "Checksum: $security_checksum\n";
            print "Trust-use: $trust_use\n";
			print "Trust-encoding: $trust_encoding\n";
			print "Trust-key: $trust_key\n";
			print "Trust-digest: $trust_digest\n";
			print "Trust-signature: $trust_signature\n";
		}	
		
		try {
			$this->soap = new SoapClient("{$http}://{$perception_server_domain}/{$qmwise_webshare_name}/qmwise.asmx?wsdl", array(			
				"user_agent"	=>	"WUR QMWise Scripts",
				"trace"			=>	$this->debug
			));
			if(!is_null($security_client_id) || !is_null($security_checksum)) {
				if($this->debug) {
					print "Constructing security header\n";
				}	
				if(is_null($security_client_id)) {
					trigger_error("Expected perception security clientID along with checksum -- cancelling security", E_USER_WARNING);
				} else if(is_null($security_checksum)) {
					trigger_error("Expected perception security checksum along with clientID -- cancelling security", E_USER_WARNING);
				} else {
					$header=array();
					if($this->debug) {
						print "Setting security header values\n";
					}	
					$header[]=new SoapHeader("http://questionmark.com/QMWISe/", "Security", array(
						"ClientID"	=>	$security_client_id,
						"Checksum"	=>	$security_checksum
					));
					if($trust_use) {
						if($this->debug) {
							print "Setting trust header values\n";
						}						
						$header[]=new SoapHeader("http://questionmark.com/QMWISe/", "Trust", array(
							"Digest"	=>	$trust_digest,
							"Encoding"	=>	$trust_encoding,
							"Signature"	=>  $trust_signature
						));
						if($this->debug) {
							print_r($header);
						}
					}
					$this->soap->__setSoapHeaders($header);
				}
			}
		} catch(Exception $e) {
			throw $e;
		}
	}

	/**
	 * Debugging functions -- interfaces to Soap debugging functions
	 * Only available if debug var is set to true and so trace is active in the 
	 * Soap client
	 */
	public function __getLastRequest() {
		if(!$this->debug) {
			trigger_error("debugging functions not available unless debug is set to true", E_USER_WARNING);
			return null;
		}
		return $this->soap->__getLastRequest();
	}
	public function __getLastRequestHeaders() {
		if(!$this->debug) {
			trigger_error("debugging functions not available unless debug is set to true", E_USER_WARNING);
			return null;
		}
		return $this->soap->__getLastRequestHeaders();
	}
	public function __getLastResponse() {
	/*
		if(!$this->debug) {
			trigger_error("debugging functions not available unless debug is set to true", E_USER_WARNING);
			return null;
		}
		*/
		return $this->soap->__getLastResponse();
	}
	public function __getLastResponseHeaders() {
		if(!$this->debug) {
			trigger_error("debugging functions not available unless debug is set to true", E_USER_WARNING);
			return null;
		}
		return $this->soap->__getLastResponseHeaders();
	}

	/**
	 * get_about
	 * Get information about the Perception server
	 * Tries the GetAbout2 method (which isn't available on older versions of 
	 * Perception) and, if that fails, then tries GetAbout
	 */
	public function get_about() {
	
		try {
			$response = $this->soap->getAbout2();
		} catch(SoapFault $e) {
			try {
				$response = $this->soap->getAbout();
			} catch(SoapFault $e) {
				throw new QMWiseException($e);
			}
			return $response->GetAboutResult;
		}
		return $response->GetAbout2Result;
	}

	/**
	 * get_administrator_by_name ($moodle_userid)
	 * Get an administrator's details from perception,
	 * we are especially interested in administrator id
	 */
	public function get_administrator_by_name($moodle_userid) {
		try {			
			$administrator = $this->soap->getAdministratorByName(array(
				"Administrator_Name" => $moodle_userid
			));

		}			
		 catch(SoapFault $e) {			 
			
			throw new QMWiseException($e);
		}		
		return $administrator->Administrator;
	}
		
	/**
	 * get_assessment_list_by_administrator
	 * 
	 * Get an array of available assessments and their details.
	 * 
	 * Processes administrator id and returns all assessments that the
	 * administrator can schedule if the passed Administrator ID exists. 
	 */
	
	public function get_assessment_list_by_administrator($admin_id) {
		try {
			$list = $this->soap->getAssessmentListByAdministrator(array(
				"Administrator_ID" => $admin_id
			));
		} catch(SoapFault $e) {
			throw new QMWiseException($e);
		}
		if(!is_array($list->AssessmentList->Assessment)) return array($list->AssessmentList->Assessment);
		return $list->AssessmentList->Assessment;
	}	
	
	public function get_assessment_tree_by_administrator($admin_id, $parent_id, $only_run_from_integration) {
		try {
			$list = $this->soap->getAssessmentListByAdministrator(array(
				"Administrator_ID" => $admin_id,
				"Parent_ID" => $parent_id, 
				"OnlyRunFromIntegration" => $only_run_from_integration
			));
		} catch(SoapFault $e) {
			throw new QMWiseException($e);
		}
		if(!is_array($list->AssessmentList->Assessment)) return array($list->AssessmentList->Assessment);
		return $list->AssessmentList->Assessment;
	}	
	
	/**
	 * get_assessment_list
	 * Get an array of available assessments and their details
	 */
	public function get_assessment_list() {
		try {
			$list = $this->soap->getAssessmentList();
		} catch(SoapFault $e) {
			throw new QMWiseException($e);
		}
		if(!is_array($list->AssessmentList->Assessment)) return array($list->AssessmentList->Assessment);
		return $list->AssessmentList->Assessment;
	}

	/**
	 * get_assessment
	 * Get an assessment's details
	 */
	public function get_assessment($assessment_id) {
		try {
			$assessment = $this->soap->getAssessment(array(
				"Assessment_ID" => $assessment_id
			));
		} catch(SoapFault $e) {
			throw new QMWiseException($e);
		}
		return $assessment->Assessment;
	}

	/**
	 * get_assessment_url
	 * Get an access URL for a particular assessment, participant name, user ID, 
	 * activity ID and course ID
	 * If Pip is active and Content-Type is set correctly in it a script 
	 * ($notify_url) is notified with these details as POST vars when the test 
	 * is completed. Note that a query string on the end of $notify_url 
	 * sometimes works -- in some versions of Perception the ampersands get lost 
	 * and so only one GET var can be used without unexpected results.
	 * If Pip is active and the USEHOME setting is switch on, the HOME button at 
	 * the end of the test is set to $home_url with the details as GET vars. 
	 * Note that no query string is allowed at the end of $home_url since 
	 * Perception doesn't check for one and just adds its own questionmark and 
	 * query string at the end.
	 */
	public function get_assessment_url($assessment_id, $participant_name, $user_id, $activity_id, $course_id, $notify_url, $home_url) {
		try {
			$access_assessment = $this->soap->getAccessAssessmentNotify(array(
				"PIP" => "moodle.pip",
				"Assessment_ID" => $assessment_id,
				"Participant_Name" => $participant_name,
				"Notify" => $notify_url,
				"ParameterList" => array(
					"Parameter" => array(
						array("Name" => "home", "Value" => $home_url),
						array("Name" => "moodle_userid", "Value" => $user_id),
						array("Name" => "moodle_activityid", "Value" => $activity_id),
						array("Name" => "moodle_courseid", "Value" => $course_id),
					)
				) //at least one parameter (can be empty) required to avoid error "Server was unable to process request. --> Object reference not set to an instance of an object."
			));
		} catch(SoapFault $e) {
			throw new QMWiseException($e);
		}
		return $access_assessment->URL;
	}

	/**
	 * get_report_url
	 * Return the URL of a report for a given result ID
	 */
	public function get_report_url($result_id) {
		try {
			$access_report = $this->soap->getAccessReport(array(
				"Result_ID" => $result_id
			));
		} catch(SoapFault $e) {
			throw new QMWiseException($e);
		}
		return $access_report->URL;
	}

	//Results -- get general result info
	//--------------------------------------------------------------------------

	/**
	 * get_assessment_result_info_list
	 * Get an array of assessments with results
	 */
	public function get_assessment_result_info_list() {
		try {
			$list = $this->soap->getAssessmentResultInfoList2();
		} catch(SoapFault $e) {
			throw new QMWiseException($e);
		}
		if(!is_array($list->AssessmentResultInfoList2->AssessmentResultInfo2)) return array($list->AssessmentResultInfoList2->AssessmentResultInfo2);
		return $list->AssessmentResultInfoList2->AssessmentResultInfo2;
	}
	
	/**
	 * get_results_by_assessment
	 * Get a list of results for a particular assessment
	 */
	public function get_results_by_assessment($assessment_id) {
		try {
			$results = $this->soap->getResultListByAssessment(array(
				"Assessment_ID" => $assessment_id
			));
		} catch(SoapFault $e) {
			throw new QMWiseException($e);
		}
		if(!is_array($results->ResultList->Result)) return array($results->ResultList->Result);
		return $results->ResultList->Result;
	}

	/**
	 * get_results_by_group
	 * Get a list of results for a particular group name
	 */
	public function get_results_by_group($group_name) {
		try {
			$results = $this->soap->getResultListByGroup(array(
				"Group_Name" => $group_name
			));
		} catch(SoapFault $e) {
			throw new QMWiseException($e);
		}
		if(!is_array($results->ResultList->Result)) return array($results->ResultList->Result);
		return $results->ResultList->Result;
	}

	/**
	 * get_results_by_participant
	 * Get a list of results for a particular participant
	 */
	public function get_results_by_participant($participant_name) {
		try {
			$results = $this->soap->getResultListByParticipant(array(
				"Participant_Name" => $participant_name
			));
		} catch(SoapFault $e) {
			throw new QMWiseException($e);
		}
		if(!is_array($results->ResultList->Result)) return array($results->ResultList->Result);
		return $results->ResultList->Result;
	}

	/**
	 * get_results_by_group_and_assessment
	 * Get a list of results for a particular group name and assessment
	 */
	public function get_results_by_group_and_assessment($group_name, $assessment_id) {
		try {
			$results = $this->soap->getResultListByGroup(array(
				"Group_Name" => $group_name
			));
		} catch(SoapFault $e) {
			throw new QMWiseException($e);
		}
		$results = $results->ResultList->Result;
		if(!is_array($results)) $results = array($results);
		$relevantresults = array();
		foreach($results as $result) {
			if($result->Assessment_ID == $assessment_id) $relevantresults[] = $result;
		}
		return $relevantresults;
	}

	//Full results -- get general result info, feedback and answer lists
	//--------------------------------------------------------------------------

	/**
	 * get_full_results_by_assessment
	 * Get a list of full results for a particular assessment
	 */
	public function get_full_results_by_assessment($assessment_id) {
		try {
			$results = $this->soap->getAssessmentResultListByAssessment(array(
				"Assessment_ID" => $assessment_id
			));
		} catch(SoapFault $e) {
			throw new QMWiseException($e);
		}
		if(!is_array($results->AssessmentResultList->AssessmentResult)) return array($results->AssessmentResultList->AssessmentResult);
		return $results->AssessmentResultList->AssessmentResult;
	}

	/**
	 * get_full_results_by_group
	 * Get a list of full results for a particular group name
	 */
	public function get_full_results_by_group($group_name) {
		try {
			$results = $this->soap->getAssessmentResultListByGroup(array(
				"Group_Name" => $group_name
			));
		} catch(SoapFault $e) {
			throw new QMWiseException($e);
		}
		if(!is_array($results->AssessmentResultList->AssessmentResult)) return array($results->AssessmentResultList->AssessmentResult);
		return $results->AssessmentResultList->AssessmentResult;
	}

	/**
	 * get_full_results_by_participant
	 * Get a list of full results for a particular participant
	 */
	public function get_full_results_by_participant($participant_name) {
		try {
			$results = $this->soap->getAssessmentResultListByParticipant(array(
				"Participant_Name" => $participant_name
			));
		} catch(SoapFault $e) {
			throw new QMWiseException($e);
		}
		if(!is_array($results->AssessmentResultList->AssessmentResult)) return array($results->AssessmentResultList->AssessmentResult);
		return $results->AssessmentResultList->AssessmentResult;
	}

	/**
	 * get_full_results_by_group_and_assessment
	 * Get a list of full results for a particular group name and assessment
	 */
	public function get_full_results_by_group_and_assessment($group_name, $assessment_id) {
		try {
			$results = $this->soap->getAssessmentResultListByGroup(array(
				"Group_Name" => $group_name
			));
		} catch(SoapFault $e) {
			throw new QMWiseException($e);
		}
		$results = $results->AssessmentResultList->AssessmentResult;
		if(!is_array($results)) $results = array($results);
		$relevantresults = array();
		foreach($results as $result) {
			if($result->Result->Assessment_ID == $assessment_id) $relevantresults[] = $result;
		}
		return $relevantresults;
	}

/** extra functies toegevoegd door Gerard */

	/**
	 * get_question_list
	 * This method returns a list giving the full details of each question in the A_Question table 
	 * in the repository database, if any questions exist in the database. If not, an empty list is
	 * returned. If a list is returned, it contains another list within it that gives details of all
	 * the question outcomes
	 */
	public function get_question_list() {
		try {
			$list = $this->soap->GetQuestionResultInfoList();
		} catch(SoapFault $e) {
			throw new QMWiseException($e);
		}
		if(!is_array($list->QuestionResultInfoList->QuestionResultInfo)) return array($list->QuestionResultInfoList->QuestionResultInfo);
		return $list->QuestionResultInfoList->QuestionResultInfo;
	}

	/**
	 * get_question_presentation
	 */
	public function get_question_presentation($question_id) {
		try {
			$response = $this->soap->GetQuestionPresentation(array(
				"Question_ID" => $question_id
			));
		} catch(SoapFault $e) {
			throw new QMWiseException($e);
		}
		return $response->GetQuestionPresentationResult;
	}
	
	/**
	 * get_schedule_list
	 */	
	public function get_schedule_list() {
		try {
			$list = $this->soap->getScheduleListV42();
		} catch(Exception $e) {
			throw $e;
		}
		if(!is_array($list->GetScheduleListV42Result->ScheduleV42)) return array($list->GetScheduleListV42Result->ScheduleV42);
		return $list->GetScheduleListV42Result->ScheduleV42;
	}	
	
	/**
	 * Get an assessment definition
	 */
	public function get_assessment_definition($assessment_id) {
		try {
			$assessment = $this->soap->getAssessmentDefinition(array(
				"Assessment_ID" => $assessment_id
			));
		} catch(SoapFault $e) {
			throw new QMWiseException($e);
		}
		return $assessment->AssessmentDefinition;
	}
	
	public function get_result_list_by_assessment($assessment_id) {
		try {
			$list = $this->soap->getResultListByAssessment(array(
				"Assessment_ID" => $assessment_id
			));
		} catch(SoapFault $e) {
			throw new QMWiseException($e);
		}
		if(isset($list) && isset($list->ResultList->Result)) {
			if(!is_array($list->ResultList->Result)) {
				return array($list->ResultList->Result);
			} else {	
				return $list->ResultList->Result;
			}
		}
	}
}	

?>
