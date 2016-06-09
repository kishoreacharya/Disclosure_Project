<?php

class RejectionByLPF extends CommonLib {

    public $MESSAGE = 14;
    public $COUNTERSIGN = 6;

    function __construct() {
        parent::__construct();
        return true;
    }
    
public function rejectionbylpf($values,$completed_by)
{
$application_id = $values['applicationId'];
$workathome = $values['workathome'];
$error_ids = $values['error_ids'];
$works_with = $values['works_with'];
$discType = $values['discType'];
$curdate=date("YmdHis");
// Update Application Woking Home Address
if(!empty($workathome)){
	$this->updateApplicantWorkingHomeAddress($application_id,'301',$workathome);
        
 $logged_in_user = $completed_by;
 
 $selectEbulkXml = "select application_id from log_ebulk_xml_error where application_id='$application_id' and error_type_id='301'";
 $resultEbulkXml = $this->getDBRecords($selectEbulkXml);

if(count($resultEbulkXml)==0){
    $fieldArray = null;
    $fieldArray['error_type_id'] = '301';
    $fieldArray['application_id'] = $application_id;
    $fieldArray['created_datentime'] = $curdate;
    $fieldArray['answered_datentime'] = $curdate;
    $fieldArray['answered_by'] = $logged_in_user;
    $this->Insert('log_ebulk_xml_error', $fieldArray);
}
 else {
      $fieldArray = null;

    $fieldArray['answered_datentime'] = $curdate;
    $fieldArray['answered_by'] = $logged_in_user;
    $condition = " application_id = '$application_id' AND error_type_id = '301'";
    $this->Update('log_ebulk_xml_error', $fieldArray, $condition);
    
//    $query = "UPDATE log_ebulk_xml_error SET answered_datentime = '$curdate',answered_by='$logged_in_user' WHERE application_id = '$application_id' AND error_type_id = '301'";
//     $res = updateDBRecord($query);
}
  
}

// Update Application Work Force
if(!empty($works_with)){
   
$this->updateApplicantWorkForce($application_id,'307',$works_with);

 $logged_in_user = $completed_by;
 
 $selectEbulkXml = "select application_id from log_ebulk_xml_error where application_id='$application_id' and error_type_id='307'";
 $resultEbulkXml = $this->getDBRecords($selectEbulkXml);

if(count($resultEbulkXml)==0){
      $fieldArray = null;
    $fieldArray['error_type_id'] = '307';
    $fieldArray['application_id'] = $application_id;
    $fieldArray['created_datentime'] = $curdate;
    $fieldArray['answered_datentime'] = $curdate;
    $fieldArray['answered_by'] = $logged_in_user;
    $this->Insert('log_ebulk_xml_error', $fieldArray);
}
 else {
         $fieldArray = null;
         $fieldArray['answered_datentime'] = $curdate;
    $fieldArray['answered_by'] = $logged_in_user;
    $condition = " application_id = '$application_id' AND error_type_id = '307'";
    $this->Update('log_ebulk_xml_error', $fieldArray, $condition);    
}
}

// Insert Application Legacy Log
$this->insertLegacyLog($application_id,$completed_by);

// Information Requested By DBS Flag Update
 $fieldArray = null;
$fieldArray['info_requested_by_dbs'] = 'N';
$condition = " application_id='$application_id'";
$this->Update('form_status', $fieldArray, $condition);    

}


/**
 *function to update print log
 * 
 * @param arr $apps
 * @return boolean 
 */
function logPrintApplication($apps,$completed_by){
    if(!is_array($apps))
        return false;
    
    for($i=0;$i<count($apps);$i++){
        
        $app_id = $apps[$i];
        $timeNow = time();
        
        $fieldArray = null;
        $fieldArray["application_id"] = $app_id;
        $fieldArray["print_by"] = $completed_by;
        $fieldArray["print_datetime"] = $timeNow;
        $this->Insert("log_print_dbsinforequest",$fieldArray);
//        $insert_query = "insert into log_print_dbsinforequest (application_id,print_by,print_datetime) values ($app_id,$completed_by,$timeNow)";
//        $res = $this->common->query($insert_query);
        
    }
    
    return $res;
}



}

?>
