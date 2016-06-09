<?php
include_once MODEL_PATH . 'Authenticate.php';
require CLASS_PATH . 'class.application.php';
$objApps = new Application();
$arrParam = array('company_id'=>$_SESSION['company_id_C']);
$arrParam['resultType'] = 'Success';
$arrVerifyApps = $objApps->getVerifyApps($arrParam);

$this->smarty->assign("accesstype","verify_applications");
$this->smarty->assign("resdata",$arrVerifyApps);
$this->smarty->assign("cntResData",count($arrVerifyApps));
$this->smarty->display($this->actionMode.".html");
?>
