<?php

$sid = $this->common->getFromSession("sid_C");
$accessLevel = $this->common->getFromSession("accessLevel_C");
$username = $this->common->getFromSession("username_C");
$company_id = $this->common->getFromSession("company_id_C");
$this->smarty->assign("compID", $company_id);
//$page_names=getFromSession("page_names");
$uploadFile = $this->common->getFromSession("uploadYN");
$access_type_id = $this->common->getFromSession("access_type_id");

$file_ids = $this->common->getFromSession("file_ids");
$accessArray = $this->common->getFromSession("accessArray");
$restrict_links = $this->common->getFromSession("restrict_links");
$arr_restrict_links = $this->common->getFromSession("arr_restrict_links");

$appAlreadyComp = $this->common->getFromSession("appCompleted_C");
$counter_sign_all = $this->common->getFromSession("counter_sign_all");

if ($accessLevel == "ebulkuser" || $accessLevel == "eremote" || $accessLevel == "officer" || $accessLevel == "cqcuser")
    $orgId = $this->common->getFromSession("orgId_C");
$primelocontact = $this->common->getFromSession("primecontact_C");

$logedIn = false;
if (!empty($sid) && !empty($username) && (!empty($company_id) || !empty($orgId)))
    $logedIn = true;
if ($logedIn == false) {
    $accesstype = "admin_login";
    $postParams = array("accesstype" => $accesstype);
    require_once 'formSubmission.php';
    die();
}

if ($appAlreadyComp == 'Y' && $accessLevel == 'cqcuser') {
    $accesstype = "appAlreadySubmitted";
    $postParams = array("accesstype" => $accesstype);
    require_once 'formSubmission.php';
    die();
}

#get TrustNane
/* $NHSDetails=getTrustDetails($company_id);
  $NHSName=$NHSDetails["name"];
  $smarty->assign("NHSName",$NHSName); */

$adminid = $this->common->getUserId($username);
$this->smarty->assign("adminid", $adminid);

if ($this->common->getFromSession('messages') == "Y") {
    $max_limit = $this->common->getMessageCount($adminid);
    $max_limit = $max_limit[0]['max_limit'];
    $this->smarty->assign("Msgcount", $max_limit);
}

#To get the message count
/* if(getFromSession('messages')=="Y"){
  $query="select count(*) as max_limit from messaging m,msg_sent_to s where m.msg_id=s.msg_id and s.user_id='$adminid' and s.checked = 'N' and s.deleted = 'N' and s.msg_read='N' and if(m.msg_type='Notification',if(now() >= m.valid_from_dt and now() <= m.valid_to_dt,1,0),1) ";
  $max_limit=getDBRecords($query);
  $max_limit=$max_limit[0]['max_limit'];
  $smarty->assign("Msgcount",$max_limit);
  } */

#file name of the current page
$urlarray = parse_url($_SERVER['PHP_SELF']);
//$file_name = basename($urlarray['path']);
$file_name = basename($_REQUEST['accesstype']);

#Fetch my access rights
if ($accessLevel == "company")
    $myRights = "ATLANTIC ADMIN";
elseif ($accessLevel == "atlantic")
    $myRights = "SNR ADMIN";
elseif ($accessLevel == "company1")
    $myRights = "ADMIN";
elseif ($accessLevel == "company2")
    $myRights = "TEAM";
elseif ($accessLevel == "officer")
    $myRights = "OFFICER";
elseif ($accessLevel == "ebulkuser" || $accessLevel == "eremote" || $accessLevel == "cqcuser")
    $myRights = "REMOTE APPLICANT";
else
    $myRights="LIMITED ACCESS";

$this->smarty->assign('myRights', $myRights);

/* -------- */
#based on access level display links
if ($accessLevel == "company" || $accessLevel == "company1" || $accessLevel == "company2" || $accessLevel == "company3" || $accessLevel == "atlantic" || $accessLevel == "officer")
    $user_access = 'admin';
else
    $user_access='applicant';

$this->smarty->assign("user_access", $user_access);

#Get Department Id
$deptAccess = "";
$branchAccess = "";
if ($accessLevel == "company1" || $accessLevel == "company2" || $accessLevel == "company3" || $accessLevel == "officer") {
    $queryDept = "SELECT comp_id  FROM user_dept_level WHERE user_id=$adminid";
    $resultDept = $this->common->getDBRecords($queryDept);
    $deptAccess = trim($resultDept[0]['comp_id']);
}


$this->smarty->assign("deptAccess", $deptAccess);
$this->smarty->assign("arr_restrict_links", $arr_restrict_links);
$this->smarty->assign("accessArray", $accessArray);
$this->smarty->assign("file_ids", $file_ids);
$this->smarty->assign("restrict_links", $restrict_links);
$this->smarty->assign("file_name", $file_name);

$visibilityText = $this->common->getVisibilityText('visibilityText');
$textonly =  $this->common->getTextOnly('textOnly');
$this->smarty->assign('visibilityText', $visibilityText);
$this->smarty->assign('textOnly',$textonly);

#Validation for non-persistent Cross Site Scripting (XSS) attacks
//include("../php/xss_protection.php");
#Included for Generating Bread Crumbs
//include("../php/breadCrumbs.php");


if ($file_name != "messages" && $this->common->getFromSession('activate_flag') == 'Y' && $this->common->getFromSession('messages') == "Y"){

    include_once MODEL_PATH . 'message_priority_redirect.php';
}

$this->smarty->assign('VERIFY_CERTIFICATE',VERIFY_CERTIFICATE);
if(VERIFY_CERTIFICATE!='N'){
    $certseen="Certificate Seen";
    $certnotseen="Certificate Not Seen";
}else{
    $certseen="Result";
    $certnotseen="Result";
}
$this->smarty->assign('DBS_MISSING_INFO_OPTION',DBS_MISSING_INFO_OPTION);
require CLASS_PATH . 'terminology.inc.php';

?>
