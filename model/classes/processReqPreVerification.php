<?php
include_once MODEL_PATH . 'Authenticate.php';
require CLASS_PATH . 'class.application.php';
$objApps = new Application();

 $intLoggedInUser =  $this->common->getFromSession("user_id_M");
  $timeValue = time();
  
  foreach($_REQUEST as $k => $v)
    $$k = $v;
  
   for($i=0;$i<=$cbxCount;$i++)
    {
       $applicationId = null;
       if(isset($_POST["chkBox_".$i]))
       {
           $workingAtHomeAddress = $_POST["wfh_" . $i]; 
           $application_id=$_POST["chkBox_".$i];
           
           // Update Sectionx Table
//           $query="update sectionx set workingathomeaddress='$workingAtHomeAddress' where application_id='$application_id'";
//            $result=$this->common->Query($query);
            
            $fieldArray = '';
            $fieldArray['workingathomeaddress'] = $workingAtHomeAddress;
            $tableName = "sectionx";
            $condition = " application_id='$application_id'";
            $result  = $objApps->Update($tableName, $fieldArray, $condition);
           // Update log_work_at_home_modified Table
//            $query = "insert into log_work_at_home_modified (application_id,modified_user_id,old_value,new_value,modified_datentime) values ('$application_id','$intLoggedInUser','Y','$workingAtHomeAddress','$timeValue')";
//            updateDBRecord($query); 
            
           $fieldArray = '';
            $fieldArray['application_id'] = $application_id;
            $fieldArray['modified_user_id'] = $intLoggedInUser;
            $fieldArray['old_value'] = 'Y';
            $fieldArray['new_value'] = $workingAtHomeAddress;
            $fieldArray['modified_datentime'] = $timeValue;
            $tableName = "log_work_at_home_modified";
             $result  = $objApps->Insert($tableName, $fieldArray);
       }
    }

    $targetUrl = "index.php?accesstype=unsuccessfullapp&stage=workingAtHomeAddress&wfhProcessed=Y";
    header("Location:$targetUrl");
?>