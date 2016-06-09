<?php

include_once MODEL_PATH . 'Authenticate.php';

require CLASS_PATH . 'mail.htmlMime.inc.php';

//  Languages For status
require LANGUAGE_PATH . 'lang.common.php';

require CLASS_PATH . 'class.messages.php';
$objMessage = new Messages();

foreach ($_REQUEST as $kPost => $vVal) {
    $$kPost = $vVal;
}



$userid = $this->common->getUserId($username);
$access_level = $accessLevel;
$compId = $company_id;

$subject = addslashes($subject);
$mailsubject = $subject;
$contents = addslashes(urldecode($contents));
$mailcontents = $contents;


$div_name = addslashes($div_name);
$name_display = $to;

if ($subject == "")
    $subject = "No Subject";
$curdate = date("YmdHis");
$from = $from_year . $from_month . $from_day . date("His");
$upto = $upto_year . $upto_month . $upto_day . date("His");

if ($access_level == "officer") {
    $msg_type = "Message";
    $priority = "normal";
}


#---------------------insert into messages-------------------

$msg_type = "Message";
$msg_id = $objMessage->composeUserMessage($msg_type, $contents, $subject, $userid, $div_name, $priority, $from, $upto, $curdate);
#--------------------------------------------------------------
#------------------------Receipient Lists----------------------
$received_as = explode(',', $to1);
$hidto = explode(',', $hid_to);

$fromEmail = $lang["emailFrom"];
$replyEmail = $lang["emailReplyTo"];

$objMessage->getReceipientLists($hidto,$received_as,$msg_id,$subject,$fromEmail,$replyEmail);


$objMessage->checkReceiverIsIn($hidto, $received_as, $subject, $curdate, $msg_id, $userid, $div_name);

#--------------------------------------------------------------
$accesstype = "messages";
$postParams = array("accesstype" => $accesstype, "view" =>"message","success" => "Y");
require_once 'formSubmission.php';
die();
?>