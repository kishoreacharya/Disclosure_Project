<?php

require CLASS_PATH . 'class.authenticate.php';

foreach($_REQUEST as $KeyPost => $valPost){
    $$KeyPost = $valPost;
}

# Generate a Unique machine id and assign it to a session.
$verifyLoginObj = new VerifyLogin();
$machine_id_string = $verifyLoginObj->generateUniqueUID();
$_SESSION["PHPUSERID"] = $machine_id_string;

// Get IP address
$host_ip_address = $this->common->trackIp();

// Check if more than 10 submission is done within 5 mins from same IP
$start_time = mktime(date('H'),date('i')-5,date('s')-1,date('m'),date('d'),date('Y'));
$end_time = time();
$query="SELECT count(id) login_attempt_cnt from user_logs where ip_address='$host_ip_address' and login_time BETWEEN $start_time AND $end_time and login_failed = 'Y'";
$login_attempt_cnt = $this->common->getDBRecords($query);

if( $login_attempt_cnt[0]['login_attempt_cnt'] > 9 ) {     
    $postParams = array("accesstype" => 'exceedLimit');
    require_once("formSubmission.php");
    die();
}


$accesstype = '';
$username = $_POST['username'];
$password = $_POST['password'];
$username = rtrim($username);

if (!empty($username) && !empty($password)) {
    $userid = $verifyLoginObj->getUserId($username);
    #reset password
    $intResetPasswd = $verifyLoginObj->getResetPwdAlert($userid);
		
    $strLoginFlag = $verifyLoginObj->processLoginDetails($username, $password);
    if (!$strLoginFlag) {
        $verifyLoginObj->logLoginFailure($username, $password);
        $strLoginFlag = "admin_login";
        $postParams = array("accesstype" => $strLoginFlag,"error" => "1");
    }else
        {
            if($intResetPasswd==0)
            {
                $verifyLoginObj->addToSession("resetpass", 'Y');
                $strLoginFlag =  "resetpwd";
                $postParams = array("accesstype" => $strLoginFlag);
            }  
						
						//$is_user_logged_in=$verifyLoginObj->is_user_logged_in($_POST["username"]);
            if( $is_user_logged_in == 1 ) {

                 $strLoginFlag =  "admin_login";
                 $postParams = array("accesstype" => $strLoginFlag,"error" => "-101");

                require_once("formSubmission.php");
                die();
            }

            $activate_flag = $_SESSION["activate_flag"];
//            if($activate_flag == 'N')$accesstype = 'qsg_index';
//            else $accesstype = $strLoginFlag;
            $accesstype = $strLoginFlag;
            
            $postParams = array("accesstype" => $accesstype);
    }

    require_once("formSubmission.php");
    die();
} else if (!empty($_GET['key'])) {
    $strLoginFlag = $verifyLoginObj->processAutoLogin($_GET['key']);
    if (!$strLoginFlag) {
        $strLoginFlag = "login";
    }
    $accesstype = $strLoginFlag;
    $postParams = array("accesstype" => $accesstype);

    require_once("formSubmission.php");
    die();
}
?>