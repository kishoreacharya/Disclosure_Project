<?php
//Assign variables
include_once MODEL_PATH . 'Authenticate.php';
include_once MODEL_PATH . 'Authenticate_Page_Access.php';

function Random_Password($length)
{
srand(date("s"));
$possible_charactors = "abcdefghkmnprstuvwxy";
$string = "";
while(strlen($string)<$length)
 {
$string.= substr($possible_charactors,rand()%(strlen($possible_charactors)),1);
}
return($string);
} 
//echo "$username";
$query="select * from users where username='$username'";
//$questions=getDBRecords($query);
$profile = $this->common->getDBRecords($query);
$showSecQuestions = $this->common->getFromSession("activate_flag");

$query="select * from security_questions";
$questions = $this->common->getDBRecords($query);
$query="select * from user_security_questions where user_id='".$profile[0]['user_id']."'";
$useranswer=$this->common->getDBRecords($query);

$activateres_val=$profile[0]['initiate_activate'];
$password = $profile[0]['password'];
//$pass=Random_Password(7).rand(10,99);
//$pass=CreateRandomToken();
//$new_password = $password ? $password : $pass;

$this->smarty->assign("pass",$new_password);
$this->smarty->assign("error",$_POST['error']);
$this->smarty->assign("usrprofile", $profile);
$this->smarty->assign("questions", $questions);
$this->smarty->assign("useranswer", $useranswer);
$this->smarty->assign("showSecQuestions", $showSecQuestions);
$this->smarty->assign("activateres_val", $activateres_val);
$this->smarty->display($this->actionMode . ".html");
?>
