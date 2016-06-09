<?php
ob_start();
include_once MODEL_PATH . 'Authenticate.php';
require  'classes/class.referralLetter.php';
require CLASS_PATH . 'mail.htmlMime.inc.php';

$value = $_REQUEST;
$CRN = $value['CRN'];
if(isset ($_REQUEST['appId']) && !empty($_REQUEST['appId']))
{
    $objReferralLetter = new ReferralLetter();
    if($CRN != '')
    $objReferralLetter->processRefLetter($_REQUEST['appId'],$_REQUEST['remuneration'],$_REQUEST['CRN'],$_REQUEST['orgId'],$_REQUEST['actionType'],$company_id);
    else
    {
        $objReferralLetter->sendEmailToADL($_REQUEST['appId']);
        echo 'Due to technical reasons, referral letter could not be generated. Please login again and try printing your referral letter after some time.';     
    }
        
}


//$objReferralLetter = new ReferralLetter();
//$objReferralLetter->processRefLetter(53);





?>
