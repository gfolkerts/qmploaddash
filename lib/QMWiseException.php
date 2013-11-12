<?php

/**
 * QMWiseException
 * @author Bart Nagel
 * Turn SoapFault objects into QMWiseException objects for easier access to the 
 * QMWise error code and message
 */
class QMWiseException extends Exception {
	/**
	 * constructor
	 * Takes a SoapFault as an argument, takes useful details from it
	 */
	public function __construct($e) {		
		if(isset($e->detail) && !empty($e->detail)) {			
			parent::__construct($e->detail->message, intval($e->detail->error));
		} else {			
			parent::__construct($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * errorType
	 * Return a string for the type of error
	 * From QMWise API Guide - Error Codes
	 * 
	 *
	 */
	public function errorType() {
		if($this->getCode() <= 0) return "unknown";
		else if($this->getCode() < 100) return "security";
		else if($this->getCode() < 1000) return "parameter";
		else if($this->getCode() < 2000) return "operation";
		else if($this->getCode() < 3000) return "configuration";
		else if($this->getCode() < 4000) return "database";
		else if($this->getCode() < 5000) return "internal";		
		else return "unknown";
	}
	
	
}

?>
