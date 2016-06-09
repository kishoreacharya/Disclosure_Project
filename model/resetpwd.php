<?php
include_once MODEL_PATH . 'Authenticate.php';
require CLASS_PATH . 'class.User.php';
$objUser = new User();

foreach ($_REQUEST as $kPost => $vVal) {
    $$kPost = $vVal;
}

$user_id = $this->common->getUserId($username);

$this->smarty->assign('user_id',$user_id);
$this->smarty->assign('username',$username);
$this->smarty->display($this->actionMode.".html");
?>