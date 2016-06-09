<?php

class User extends CommonLib {

    public $MESSAGE = 14;
    public $COUNTERSIGN = 6;

    function __construct() {
        parent::__construct();
        return true;
    }

    function getSystemUsers($arrParam) {
        $sqlAdmins = "SELECT u.officer_type,c.company_id,u.user_id, upper(concat(u.name,' ', u.surname)) as name, upper(ot.type_name) type_name, if(active='Y','ACTIVE','DISABLED') status FROM users u INNER JOIN comp_users cu ON u.user_id = cu.user_id
                      INNER JOIN company c ON cu.company_id = c.company_id
                      INNER JOIN officer_types ot ON u.officer_type = ot.type_id
                      WHERE u.access_level = 'company1' AND u.accnt_locked = 'N' AND u.accnt_removed = 'N'";

        if (is_array($arrParam) && count($arrParam) > 0 && $arrParam['searchType'] != 'viewAll') {
            if (!empty($arrParam['app_forename']))
                $sqlAdmins.=" and u.name like '%" . $arrParam['app_forename'] . "%'";

            if (!empty($arrParam['app_surname']))
                $sqlAdmins.=" and u.surname like '%" . $arrParam['app_surname'] . "%'";
        }

        //$users[$i]["editable"]=editUserRights($access_people,$res[$i]["officer_type"]);

        $sqlAdmins .= " order by name ";

        $res = $this->getDBRecords($sqlAdmins);



        $access_type_id = $arrParam['access_type_id'];


        $users = Array();
        #List of people i have access to
        $lower_access_ids = $this->getLowerAccessLevel($access_type_id, "A");
        $officerTypes = $this->getAccessLevelLists($lower_access_ids, $access_type_id, "A");
        $access_people = array();
        for ($j = 0; $j < count($officerTypes); $j++) {
            $access_people[] = $officerTypes[$j]['type_id'];
        }

        for ($i = 0; $i < count($res); $i++) {
            $users[$i]["user_id"] = $res[$i]["user_id"];
            $users[$i]["name"] = $res[$i]["name"];

            $users[$i]["name"] = str_replace("\\", '', $res[$i]["name"]);

            $users[$i]["compname"] = $this->getCompName($res[$i]["company_id"]);
            $users[$i]["compid"] = $res[$i]["company_id"];
            $users[$i]["type_name"] = $res[$i]["type_name"];
            /*
              if($res[$i]["access_level"]=="company1")
              {
              $users[$i]["access"]="ADMIN";
              }elseif($res[$i]["access_level"]=="company2")
              {
              $users[$i]["access"]="TEAM";
              }elseif($res[$i]["access_level"]=="company3")
              {
              $users[$i]["access"]="LIMITED ACCESS";
              }
              elseif($res[$i]["access_level"]=="atlantic")
              {
              $users[$i]["access"]="SNR ADMIN";
              }
             */

            //$users[$i]["editable"]=editUserRights($myRights,$users[$i]["access"]);
            $users[$i]["editable"] = $this->editUserRights($access_people, $res[$i]["officer_type"]);

            $users[$i]["status"] = $res[$i]["status"];


            // Encrypt the data and store the results in a variable
            //$users[$i]["unique_ref"] = $Secrypt->Encrypt($res[$i]['user_id'], $PrivateKey, $PublicKey);
            //$users[$i]["unique_ref"] = $res[$i]['auth'];
        }

        return $users;
    }

    function getAccessLevels() {
        $sqlGetAccess = "SELECT type_id, type_name FROM officer_types WHERE is_active = 'Y' AND officer_type = 'A'";
        $rsGetAccess = $this->getDBRecords($sqlGetAccess);
        return $rsGetAccess;
    }

    function getSystemUserInfo($intUserId) {
        if (!empty($intUserId)) {
            $sqlGetInfo = "SELECT c.company_id,u.used,u.active,u.user_id, upper(u.name) fname, upper(u.surname) as sname, u.dob, u.email, u.phone, u.position, u.username, u.password, ot.type_id  FROM users u INNER JOIN comp_users cu ON u.user_id = cu.user_id
                      INNER JOIN company c ON cu.company_id = c.company_id
                      INNER JOIN officer_types ot ON u.officer_type = ot.type_id
                      WHERE u.user_id = '" . $intUserId . "' AND u.access_level = 'company1' AND u.accnt_locked = 'N' AND u.accnt_removed = 'N'";
            $rsGetInfo = $this->getDBRecords($sqlGetInfo);

            $query = "SELECT ud . *,dd.dept_id FROM user_division ud LEFT JOIN dept_division dd ON ud.division = dd.division WHERE ud.user_id ='" . $intUserId . "'";
            $dept_division = $this->getDBRecords($query);
            $rsGetInfo[0]['selected_division'] = $dept_division[0]['division'];

            #Counter Signatory
            $query = "select counter_signatory_number from ebulk_counter_signatory where user_id = '$intUserId'";
            $cntres = $this->getDBRecords($query);
            $rsGetInfo[0]['counter_signatory_no'] = $cntres[0]['counter_signatory_number'];
            
            
            ### check for Counter Signatory
            $officer_type_id = $rsGetInfo[0]['type_id'];

            $query = "select user_access,access_type from officer_types where type_id='" . $officer_type_id . "' limit 1";
            $user_access_res = $this->getDBRecords($query);
            $chkboxvalues_access = $user_access_res[0]['access_type'];

            $accessArray_chk = explode(",", $chkboxvalues_access);
            # Countersignatory
            if (in_array($this->COUNTERSIGN, $accessArray_chk)) {
                $rsGetInfo[0]['counter_signatory_access'] = 'Y';
            } else {
                $rsGetInfo[0]['counter_signatory_access'] = 'N';
            }
            ### check for Counter Signatory
             
            return $rsGetInfo;
        }
    }

    function getMessageDivision() {
        $query = "select (d.dept_id * -1) dept_div_id,d.division_name as uname,d.division as divname ,c.company_id company_id from dept_division d ,company_dept c where d.dept_id=c.dept_id and c.company_id='1'";
        $division = $this->getDBRecords($query);
        return $division;
    }

    function getUserSecurityInfo($user_id=null) {
        if (!empty($user_id)) {
            $query = "select b.*,a.* from security_questions a left join user_security_questions b on a.id=b.question_id and b.user_id='$user_id' ";
        } else {
            $query = "select * from security_questions";
        }
        $questions = $this->getDBRecords($query);
        return $questions;
    }

    function createSystemUser($arrParam, $arrLangData) {
        $today = time();
        $authcode = md5($arrParam['username']);
        $enc_pass = md5($arrParam['password']);

        $query = "select user_access,access_type from officer_types where type_id='" . $arrParam['accessLevel'] . "' limit 1";
        $user_access_res = $this->getDBRecords($query);
        $access = $user_access_res[0]['user_access'];
        $chkboxvalues_access = $user_access_res[0]['access_type'];

        $query = "INSERT INTO users (surname,name,email,access_level,dob,position,phone,username,password,auth,enc_pass,officer_type,inline_help) VALUES
                  ('" . $arrParam['lastname'] . "','" . $arrParam['firstname'] . "','" . $arrParam['app_email'] . "','company1',
                  '" . $arrParam['app_dob'] . "','" . addslashes($arrParam['position']) . "','" . $arrParam['phone'] . "','" . $arrParam['username'] . "'
                  ,'" . $arrParam['password'] . "','$authcode','$enc_pass','" . $arrParam['accessLevel'] . "','N')";
        $this->Query($query);

        $maxquery = "SELECT max(user_id) AS maxid FROM users WHERE username='" . $arrParam['username'] . "'";
        $maxres = $this->getDBRecords($maxquery);
        $maxid = $maxres[0]['maxid'];

        $selectedcompid = $arrParam['selectedcompid'];
        $query = "INSERT INTO comp_users (company_id,user_id) VALUES ($selectedcompid,'$maxid')";
        $result = $this->Query($query);

        #added for messages
        $query = "INSERT INTO add_user_list(user_id,name,surname,email) VALUES ('$maxid','" . $arrParam['firstname'] . "','" . $arrParam['lastname'] . "','" . $arrParam['app_email'] . "')";
        $this->Query($query);
        $query = "INSERT INTO user_division(user_id,division,is_admin) VALUES ('$maxid','" . $arrParam['messagediv'] . "','N')";
        $this->Query($query);

        $accessArray_chk = explode(",", $chkboxvalues_access);
        if (in_array($this->MESSAGE, $accessArray_chk)) {
            $query = "update users set messages='Y', message_compose='Y',message_reply='Y',message_email='Y' where user_id='$maxid'";
            $this->Query($query);
        } else {
            $query = "update users set messages='N', message_compose='N',message_reply='N',message_email='N' where user_id='$maxid'";
            $this->Query($query);
        }

        # Countersignatory
        $counter_signatory_no = $arrParam['counter_signatory_no'];
        if (in_array($this->COUNTERSIGN, $accessArray_chk) && !empty($counter_signatory_no)) {
            $_sql_rb_id = 'select rb_detail_id from ebulk_rb_detail limit 1';
            $_res_rb_id = $this->getDBRecords($_sql_rb_id);
            $_rb_id = $_res_rb_id[0]['rb_detail_id'];
            $_counter_signatory_name = $arrParam['firstname'] . " " . $arrParam['lastname'];
            $_sql_add_cnt_sig = 'insert into ebulk_counter_signatory(rb_detail_id,user_id,counter_signatory_number,counter_signatory_name) values(' . $_rb_id . ',' . $maxid . ',"' . $counter_signatory_no . '","' . $_counter_signatory_name . '")';
            $this->Query($_sql_add_cnt_sig);
        }

        #Security Question Deatils

        $res = $this->getUserSecurityInfo();
        for ($i = 0; $i < count($res); $i++) {
            $qid = $res[$i]["id"];
            $question = $_POST["question_id_" . $qid];
            $answer = $_POST["answer_" . $qid];

            $query = "select * from user_security_questions where question_id='$qid' and user_id='$maxid'";
            $ures = $this->getDBRecords($query);

            if (!empty($ures)) {
                $query = "update user_security_questions set question='" . $res[$i]["question"] . "', answer='$answer', datentime='$today' where question_id='$qid' and user_id='$maxid'";
                $result = $this->Query($query);
            } else {
                $query = "insert into user_security_questions (question_id, user_id, question, answer, datentime) values ('$qid', '$maxid', '" . $res[$i]["question"] . "', '$answer', '$today')";
                $result = $this->Query($query);
            }
        }
        
        /* Insert into user department level user_dept_level*/
        $fieldArrayUserDept = '';
        $fieldArrayUserDept['user_id'] = $maxid;
        $fieldArrayUserDept['comp_id'] = $selectedcompid;
        $fieldArrayUserDept['datentime'] = $today;
        $tableName = 'user_dept_level';
        $this->Insert($tableName, $fieldArrayUserDept);
        /* Insert into user department level user_dept_level*/
        
        #Send Activation Email
        $email = $arrParam['app_email'];
        $forename = $arrParam['firstname'];
        $surname = $arrParam['lastname'];
        $user_name = $arrParam['username'];
        $password = $arrParam['password'];
        if (!empty($email)) {
        	$email = stripslashes(strtolower($email));
            if (preg_match("/^[_a-z0-9'-]+(\.[_a-z0-9'-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $email)) {
                $systemUserName = $this->correctcase($forename) . " " . $this->correctcase($surname);
                $this->sendactivationEmail($systemUserName, $authcode, $user_name, $password, $email, $maxid, $arrLangData, $arrParam);
            }
        }
    }

    function updateSystemUser($arrParam, $arrLangData) {
		    $oldDate = mktime(0, 0, 0, date("m"), date("d") - 91, date("Y"));
        $authcode = md5($arrParam['username']);
        
        if(!empty($arrParam['uid']) && empty($arrParam['password'])){
            $getUserPass = "select password from users where user_id='".$arrParam['uid']."'";
            $getResult = $this->getDBRecords($getUserPass);
            $arrParam['password'] = $getResult[0]['password'];
        }
        $enc_pass = md5($arrParam['password']);
        

        $query = "select user_access,access_type from officer_types where type_id='" . $arrParam['accessLevel'] . "' limit 1";
        $user_access_res = $this->getDBRecords($query);
        $access = $user_access_res[0]['user_access'];
        $chkboxvalues_access = $user_access_res[0]['access_type'];

        $query = "UPDATE users SET surname = '" . $arrParam['lastname'] . "',name = '" . $arrParam['firstname'] . "',email = '" . $arrParam['app_email'] . "',
                  dob = '" . $arrParam['app_dob'] . "', position = '" . addslashes($arrParam['position']) . "',phone = '" . $arrParam['phone'] . "',
                  username = '" . $arrParam['username'] . "',password = '" . $arrParam['password'] . "',auth = '" . $authcode . "',enc_pass = '" . $enc_pass . "',
                  officer_type = '" . $arrParam['accessLevel'] . "',last_updated = '" . $oldDate . "' WHERE user_id = '" . $arrParam['uid'] . "'";
        $this->Query($query);

        #query for messages
        $query = "SELECT user_id FROM add_user_list WHERE user_id='" . $arrParam['uid'] . "'";
        $userres = $this->getDBRecords($query);
        if (empty($userres)) {
            $query = "INSERT INTO add_user_list(user_id,name,surname,email) VALUES ('" . $arrParam['uid'] . "','" . $arrParam['firstname'] . "','" . $arrParam['lastname'] . "','" . $arrParam['app_email'] . "')";
            $this->Query($query);
            $query = "INSERT INTO user_division(user_id,division,is_admin) VALUES ('$user_id','general','N')";
            $this->Query($query);
        } else {
            $query = "UPDATE add_user_list SET name='" . $arrParam['firstname'] . "',surname='" . $arrParam['lastname'] . "',email='" . $arrParam['app_email'] . "' where user_id='" . $arrParam['uid'] . "'";
            $this->Query($query);
            $query = "UPDATE user_division SET division='" . $arrParam['messagediv'] . "',is_admin='N' where user_id='" . $arrParam['uid'] . "'";
            $this->Query($query);
        }

        $counter_signatory_no = $arrParam['counter_signatory_no'];
        $accessArray_chk = explode(",", $chkboxvalues_access);
        
        # Countersignatory
        if (in_array($this->COUNTERSIGN, $accessArray_chk) && !empty($counter_signatory_no)) {
            $_sql_rb_id = 'select rb_detail_id from ebulk_rb_detail limit 1';
            $_res_rb_id = $this->getDBRecords($_sql_rb_id);
            $_rb_id = $_res_rb_id[0]['rb_detail_id'];
            $_counter_signatory_name = $this->correctcase($arrParam['firstname'] . " " . $arrParam['lastname']);

            $query = "select counter_signatory_id from ebulk_counter_signatory where user_id = '" . $arrParam['uid'] . "'";
            $cntres = $this->getDBRecords($query);

            if (count($cntres) > 0) {
                $_sql_add_cnt_sig = "update ebulk_counter_signatory set rb_detail_id = '$_rb_id',counter_signatory_number='$counter_signatory_no',counter_signatory_name='$_counter_signatory_name' where user_id ='" . $arrParam['uid'] . "'";
                $this->Query($_sql_add_cnt_sig);
            } else {
                $_sql_add_cnt_sig = 'insert into ebulk_counter_signatory(rb_detail_id,user_id,counter_signatory_number,counter_signatory_name) values(' . $_rb_id . ',' . $arrParam['uid'] . ',"' . $counter_signatory_no . '","' . $_counter_signatory_name . '")';

                $this->Query($_sql_add_cnt_sig);
        }
        }


        if (in_array($this->MESSAGE, $accessArray_chk)) {
            $query = "update users set messages='Y', message_compose='Y',message_reply='Y',message_email='Y' where user_id='" . $arrParam['uid'] . "'";
            $this->Query($query);
        } else {
            $query = "update users set messages='N', message_compose='N',message_reply='N',message_email='N' where user_id='" . $arrParam['uid'] . "'";
            $this->Query($query);
        }
        
        
        
        #Send Activation Email
        $email = $arrParam['app_email'];
        $forename = $arrParam['firstname'];
        $surname = $arrParam['lastname'];
        $user_name = $arrParam['username'];
        $password = $arrParam['password'];
        $uid=$arrParam['uid'];
        
        if ($arrParam['user_active'] == 'Y') {  ###  only if user is active
            if (!empty($email)) {
                $email = stripslashes(strtolower($email));
                if (preg_match("/^[_a-z0-9'-]+(\.[_a-z0-9'-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $email)) {
                    $systemUserName = $this->correctcase($forename) . " " . $this->correctcase($surname);
                    $this->sendactivationEmail($systemUserName, $authcode, $user_name, $password, $email, $uid, $arrLangData, $arrParam);
                }
            }
        }
    }

    public function checkUserNameAvailability($username) {
        $message = "TRUE";
        $query = "select username from users where username='$username'";

        $userexists = $this->getDBRecords($query);

        if (count($userexists) > 0) {
            $i = 1;

            do {
                $username_new = $username . $i;
                $query = "select username from users where username='$username_new'";
                $userexists = $this->getDBRecords($query);
                $message = $username_new;
                $i++;
            } while (count($userexists) > 0);
        }
        return $message;
    }

    public function getOfficerTypeDetails($typeId) {
        $sqlSelect = "SELECT * FROM `officer_types` WHERE type_id='$typeId'";
        $results = $this->getDBRecords($sqlSelect);
        return $results[0]['user_access'] . "||" . $results[0]['access_type'];
    }

    #Function to Deactivate Users

    public function deactivateUser($deactive_user_id, $intLoggedInUser) {

        $query_user = "update users set active ='N',used='Y' where user_id='" . $deactive_user_id . "'";
        $res = $this->Query($query_user);

        $query = "INSERT into useracntactivity (user_id,action_type,action_taken_by)VALUES('$deactive_user_id','D','$intLoggedInUser')";
        $res_insert = $this->Query($query);

        $comments = "Deactivated By Admin";

        $query_insertComments = "INSERT INTO user_deactivate_comments ( org_id, user_id, comments,type,action_taken_by) VALUES ( '" . $orgId . "', '" . $deactive_user_id . "', '" . $comments . "', 'D','" . $intLoggedInUser . "')";
        $res_insertComments = $this->Query($query_insertComments);
    }

    #Function to Activate Users

    public function activateUser($active_user_id, $intLoggedInUser,$arrLangData) {
        
        $query_user = "update users set active ='Y',used='N',accnt_locked='N', locked_datetime = ''  where user_id='" . $active_user_id . "'";
        $res = $this->Query($query_user);

        $query = "INSERT into useracntactivity (user_id,action_type,action_taken_by)VALUES('$active_user_id','A','$intLoggedInUser')";
        $res_insert = $this->Query($query);
        
        $user_query = "select * from users where user_id='".$active_user_id."'";
        $userDetails = $this->getDBRecords($user_query);

        $user_comp_query = "select company_id from comp_users where user_id='".$active_user_id."'";
        $compuserDetails = $this->getDBRecords($user_comp_query);
        $compID = $compuserDetails[0]['company_id'];
        $arrParam['selectedcompid'] = $compID;
        
        #Send Activation Email
        $email = $userDetails[0]['email'];
        $forename = $userDetails[0]['name'];
        $surname = $userDetails[0]['surname'];
        $user_name = $userDetails[0]['username'];
        $password = $userDetails[0]['password'];
        $uid=$active_user_id;
        
         $authcode = md5($userDetails[0]['username']);
        
        
        if (!empty($email)) {
        	$email = stripslashes(strtolower($email));
            if (preg_match("/^[_a-z0-9'-]+(\.[_a-z0-9'-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $email)) {
                $systemUserName = $this->correctcase($forename) . " " . $this->correctcase($surname);
                $this->sendactivationEmail($systemUserName, $authcode, $user_name, $password, $email, $uid, $arrLangData, $arrParam);
            }
        }
       
    }

    #Function to get Lower Level Access Rights

    public function getLowerAccessLevel($access_id, $officer_type, $access_id_list="") {
        if (empty($access_id_list))
            $access_id_list = $access_id;
        else
            $access_id_list.="," . $access_id;

        $query = "select type_id from officer_types where is_active = 'Y' and parent_id in ($access_id) and officer_type='$officer_type'";
        $res = $this->getDBRecords($query);

        if (count($res) > 0) {
            $str = "";
            for ($i = 0; $i < count($res); $i++) {
                if (!empty($str))
                    $str.=",";
                $str.=$res[$i]['type_id'];
            }
            $access_id_list = $this->getLowerAccessLevel($str, $officer_type, $access_id_list);
        }
        $access_id_list = explode(",", $access_id_list);
        $access_id_list = array_unique($access_id_list);
        sort($access_id_list);

        return implode(",", $access_id_list);
    }

    #To Fetch User Access Rights

    public function getAccessLevelLists($access_id, $my_access_id, $officer_type) {
        $access_array = array();
        $i = 0;

        $query = "select type_id,type_name,parent_id,officer_type from officer_types where type_id in ($access_id) and type_id = $my_access_id and officer_type='$officer_type'";
        $res = $this->getDBRecords($query);
        $my_access_id = $res[0]["parent_id"];

        do {
            $access_array[$i]["type_id"] = $res[0]["type_id"];
            $access_array[$i]["type_name"] = $res[0]["type_name"];
            $access_array[$i]["parent_id"] = $res[0]["parent_id"];
            $access_array[$i]["officer_type"] = $res[0]["officer_type"];
            $i++;

            $my_access_id = $res[0]["type_id"];

            $query = "select type_id,type_name,parent_id,officer_type from officer_types where type_id in ($access_id) and parent_id = $my_access_id and is_active='Y' and officer_type='$officer_type'";
            $res = $this->getDBRecords($query);
        } while (count($res) > 0);

        $formated_array = array();
        for ($i = 0; $i < count($access_array) - 1; $i++) {
            $formated_array[$i] = $access_array[$i + 1];
        }

        return $formated_array;
    }

    #send Invitation to user
    public function sendactivationEmail($name, $code, $username, $password, $email, $usersid, $arrLangData, $arrParam) {

        $selectedcompid = $arrParam['selectedcompid'];
        $msg = "Dear " . str_replace("\\", '', $name) . "<br><br>
        Thank you for taking the time to move your ".DBS_CERT." application process to the new " . $arrLangData["trustHead"] . " secure online service.<br /> <br />
        " . $arrLangData["trustHead"] . "  now provides to you the new E-bulk technology which will dramatically improve the way we process ".DBS_CERT." applications.<br /> <br />
        By taking advantage of this e-service we hope you will see instant improvement to the ".DBS." process and overall turnaround time of applications. This is accomplished by removing most of obstacles which delay applications:<br /><br />
        <ul>
        <li>Hand writing legibility</li>
        <li>Errors on forms</li>
        <li>Missing or incomplete information</li>
        <li>Delayed or lost post</li>
        <li>Transcribing of applicant details onto ".DBS." system</li>
        </ul>
        <br />

        What does this mean:<br /><br />
        <ul>
        <li>Fully online service from start to finish</li>
        <li>Full online training</li>
        <li>Greater Data Accuracy</li>
        <li>Faster Results</li>
        <li>Completely paperless process, NO POST!</li>
        <li>Online Results</li>
        </ul>
        <br/>
        
        To access and setup your online account please visit the secure website below and enter the username and password provided, you will then be guided through the online registration process:<br /><br />

        <a href=\"" . $arrLangData["urlLink"] . "\">" . $arrLangData["urlLink"] . "</a>

        <br /><br />
        User name : <b>$username</b><br>Password : <b>$password</b><br><br>

        Kind regards<br />"
                . $arrLangData["kindRegards"] . "";

        $msg2 = "
        Dear " . $name . "

        Thank you for taking the time to move your ".DBS_CERT." application process to the new " . $arrLangData["trustHead"] . " online service.

        " . $arrLangData["trustHead"] . " now provides to you the new E-bulk technology which will dramatically improve the way we process ".DBS_CERT." applications.

        By taking advantage of this new e-service we hope you will see instant improvement to the ".DBS." process and overall turnaround time of applications. This is accomplished by removing most of obstacles which delay applications:

        1. Hand writing legibility
        2. Errors on forms
        3. Missing or incomplete information
        4. Delayed or lost post
        5. Transcribing of applicant details onto ".DBS." system

        What does this mean:

        1. Fully online service from start to finish
        2. Full online training
        3. Greater Data Accuracy
        4. Faster Results
        5. Completely paperless process, NO POST!
        6. Online Results

        To access and setup your online account please visit the secure website below and enter the username and password provided, you will then be guided through the online registration process:

        " . $arrLangData["urlLink"] . "

        Username : $username
        Password : $password


        Kind regards"
                . $arrLangData["kindRegards"] . "";



        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
        /* additional headers */
        $headers .= "From: Disclosures.co.uk\r\n";

        $mail = new htmlMimeMail();

        $text = $msg2;
        $html = $msg;

        $mail->setHtml($html, $text);
        if($selectedcompid==3)
        {
            $returnpath = "dbshr@cqc.disclosures.co.uk";
            $frompath = "dbshr@cqc.disclosures.co.uk";
        }
      else
       {
          $returnpath = "dbs@cqc.disclosures.co.uk";
          $frompath = "dbs@cqc.disclosures.co.uk";

        }
        $mail->setReturnPath($returnpath);
        $mail->setFrom($frompath);
        //$mail->setSubject('Activation of ' . $arrLangData["messageHead"] . ' online account');
        $mail->setSubject('Activation of ' . $arrLangData["trustHead"] . ' online account');
        $mail->setHeader('X-Mailer', 'HTML Mime mail class');
        $result = $mail->send(array($email), 'smtp');
        
        $today = time();
        $arrUser = array();
        $arrUser['email_sentdate'] = $today;
        $condition = " user_id='$usersid'";
        $this->Update('users', $arrUser,$condition);
    }

    #To Fetch User Access Rights

    public function getUsernameDetials($username) {
           $query = "select name,surname,dob,email,middle_name from users where username='$username' limit 1";
           $res = $this->getDBRecords($query);
           return $res;
    }

    #To Fetch User Access Rights

    public function checkOldPassword($oldpass,$username) {

        $message = "TRUE";
        $enc_pass = md5($oldpass);
        $query = "select username from users where username='$username' and enc_pass='$enc_pass'";
        $userexists = $this->getDBRecords($query);

        if (count($userexists) > 0) {
            $message = "";
        }
        return $message;
    }

}

?>
