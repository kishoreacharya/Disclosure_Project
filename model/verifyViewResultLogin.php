<?php

include_once MODEL_PATH . 'Authenticate.php';

#Get All Application objects.
require CLASS_PATH . 'class.authenticate.php';
$verifyLoginObj = new VerifyLogin();


foreach ($_REQUEST as $kPost => $vVal) {
    $$kPost = $vVal;
}

if (!empty($userid) && !empty($password)) {
    $res = $verifyLoginObj->verifyResultLoginDetails($userid, $password);
    if (!empty($res) && count($res) > 0) {
        $accesstype = "showappresult";
        $postParams = array("accesstype" => $accesstype, "confid" => $confid, "eapp" => $eapp, "confid" => $confid, "applicationId" => $applicationId, "appRefNo" => $appRefNo);
        require_once 'formSubmission.php';
        die();
    } else {
        $verifyLoginObj->logLoginFailure($userid, $password);
         $accesstype = "initiatelogin";
        $postParams = array("accesstype" => $accesstype, "page" => $page, "appref" => $appref, "confid" => $confid, "applicationId" => $applicationId, "appRefNo" => $appRefNo,"errorOnPage" =>"set");
        require_once 'formSubmission.php';
        die();
    }
} else {
    $accesstype = "initiatelogin";
    $postParams = array("accesstype" => $accesstype, "page" => $page, "appref" => $appref, "confid" => $confid, "applicationId" => $applicationId, "appRefNo" => $appRefNo);
    require_once 'formSubmission.php';
    die();
}
?>