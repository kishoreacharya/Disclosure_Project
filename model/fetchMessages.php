<?php


include_once MODEL_PATH . 'Authenticate.php';

require CLASS_PATH . 'class.messages.php';
$objMessage = new Messages();

$objMessage->smarty = $this->smarty;

foreach ($_REQUEST as $kPost => $vVal) {
    $$kPost = $vVal;
}

if(empty($setSearchFlag))
    $setSearchFlag = 'N';


$view = $_POST["view"];
$userid = $this->common->getUserId($username);
$access_level = $accessLevel;
$compId = $company_id;
//print_r($_POST);
#get the query limit
if (isset($_POST['ilimit']))
    $ilimit = $_POST['ilimit']; //to initialise the query limit
else
    $ilimit=0;

$number_of_rows_allowed = 10;

$responseMessage = "";
if (!empty($view)) {
    $responseMessage = $objMessage->getMessageInfo($view, $userid, $sorttype, $sortby, $ilimit, $number_of_rows_allowed,$period,$search,$searchby,$setSearchFlag);
} else {
    $responseMessage = $this->smarty->fetch("home.html");
}

echo $responseMessage;
?>
