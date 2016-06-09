<?php

class DeactivateUser extends CommonLib {

    public $CURRENT_SURNAME = 3;
    public $CURRENT_FORNAME = 4;
    public $CURRENT_ADDRESS = 1;
    public $ADDITIONAL_ADDRESS = 2;
    public $NEWUSER = "newuser";
    public $CURRENTREGUSERSTATE = "Registered Application";
    public $ACCESSTYPE = "CU";

    function __construct() {
        parent::__construct();
        return true;
    }

    /*
     * Function to get deactivated results
     * param 1  : listComp    : List of company ids
     * param 2  : branchQuery :
     */

  function getDeactivatedUsers($company_id,$branchQuery='')
    {
      $listComp=$this->getChildCompanies_1($company_id);

if(empty($listComp))$listComp=1;


        /*$sqlGetDeaactivUsers="SELECT u.user_id,u.username,ot.type_name access_level,u.name fname,if(u.surname<>'',u.surname,'-') surname,if(u.accnt_locked <>'N','Failed Login 3 Times',if(u.6months_deactivation<>'N','Not Logged in for 6 Months',if(u.active<>'Y','Manually Deactivated by Admin','-'))) reason,c.name region,'-' branch
                FROM users u,company c,comp_users cu,officer_types ot
                WHERE u.user_id=cu.user_id and (u.access_level='company1' or u.access_level='company2' or u.access_level='company3' or u.access_level='company4')
                and u.officer_type=ot.type_id and c.company_id=cu.company_id and u.active = 'N' and u.accnt_locked  = 'Y' and c.company_id IN ($listComp) GROUP BY u.user_id
                UNION
                SELECT u.user_id,u.username,'Officer' access_level,u.name fname,if(u.surname<>'',u.surname,'-') surname,if(u.accnt_locked <>'N','Failed Login 3 Times',if(u.6months_deactivation<>'N','Not Logged in for 6 Months',if(u.active<>'Y','Manually Deactivated by Admin','-'))) reason,c.name region,o.name branch
                FROM users u,liason_officer lo1,lo_org lo2,organisation o,company c
                WHERE u.user_id=lo1.user_id and lo1.lo_id=lo2.lo_id and lo2.org_id=o.org_id and o.company_id=c.company_id and u.access_level='officer' and u.active = 'N' and u.accnt_locked  = 'Y' $branchQuery
                GROUP BY u.user_id
                ";*/

        $sqlGetDeaactivUsers="SELECT u.user_id,u.username,ot.type_name access_level,u.name fname,if(u.surname<>'',u.surname,'-') surname,if(u.accnt_locked <>'N','Failed Login 3 Times',if(u.6months_deactivation<>'N','Not Logged in for 6 Months',if(u.active<>'Y','Manually Deactivated by Admin','-'))) reason,c.name region,'-' branch
                FROM users u,company c,comp_users cu,officer_types ot
                WHERE u.user_id=cu.user_id and (u.access_level='company1' or u.access_level='company2' or u.access_level='company3' or u.access_level='company4')
                and u.officer_type=ot.type_id and c.company_id=cu.company_id and u.active = 'N' and (u.accnt_locked = 'N' OR u.accnt_removed = 'N') and c.company_id IN ($listComp) GROUP BY u.user_id";
   
        $result=$this->getDBRecords($sqlGetDeaactivUsers);
      
        return $result;
    }
    
    function updateDeactivatedUser($flag, $selUsers, $username,$arrLangData)
    {
        $intLoggedInUser = $this->getUserId($username);
        require CLASS_PATH . 'class.User.php';
        $usrObj = new User();
        
        
        switch($flag)
        {
            case 'active':
                            $sql = 'UPDATE users set accnt_locked = "N", active = "Y", used = "N", locked_datetime = "" WHERE user_id IN ('.$selUsers.')';
                            $result=$this->getDBRecords($sql);

                            $sqlInsert = 'INSERT INTO  useracntactivity (user_id, action_type, action_taken_by) VALUES ';
                            $arrUsers = @explode(',',$selUsers);
                            $intCnt = 0;
                            foreach($arrUsers as $intUserId)
                            {
                                if($intCnt > 0)
                                    $sqlInsert .= ', ';

                                $sqlInsert .= ' ('.$intUserId.', "A", '.$intLoggedInUser.') ';
                                
                                
                            $user_query = "select * from users where user_id='".$intUserId."'";
                            $userDetails = $this->getDBRecords($user_query);

                            $comp_user_query = "select company_id from comp_users where user_id='".$intUserId."'";
                            $compuserDetails = $this->getDBRecords($comp_user_query);
                            $compID = $compuserDetails[0]['company_id'];
                            $arrParam['selectedcompid'] = $compID;
                            
                             ########### Send Activation Email##################
                            $email = $userDetails[0]['email'];
                            $forename = $userDetails[0]['name'];
                            $surname = $userDetails[0]['surname'];
                            $user_name = $userDetails[0]['username'];
                            $password = $userDetails[0]['password'];
                            $uid=$intUserId;

                            $authcode = md5($userDetails[0]['username']);


                            if (!empty($email)) {
                                    $email = stripslashes(strtolower($email));
                                if (preg_match("/^[_a-z0-9'-]+(\.[_a-z0-9'-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $email)) {
                                    $systemUserName = $this->correctcase($forename) . " " . $this->correctcase($surname);
                                    $usrObj->sendactivationEmail($systemUserName, $authcode, $user_name, $password, $email, $uid, $arrLangData, $arrParam);
                                }
                            }
                            ######################### Send Activation Email  ##################  
                                $intCnt++;
                            }
                            $result=$this->getDBRecords($sqlInsert);
                            
                            

                            $strMessage = 'Users activated succesfully.';


            break;

            case 'remove':
                            $sql = 'UPDATE users set accnt_removed="Y",accnt_locked = "Y", active = "N", used = "Y" WHERE user_id IN ('.$selUsers.')';
                            $result=$this->getDBRecords($sql);

                            $sqlInsert = 'INSERT INTO  useracntactivity (user_id, action_type, action_taken_by) VALUES ';
                            $arrUsers = @explode(',',$selUsers);
                            $intCnt = 0;
                            foreach($arrUsers as $intUserId)
                            {
                                if($intCnt > 0)
                                    $sqlInsert .= ', ';

                                $sqlInsert .= ' ('.$intUserId.', "R", '.$intLoggedInUser.') ';
                                $intCnt++;
                            }
                            $result=$this->getDBRecords($sqlInsert);

                            $strMessage = 'Users removed succesfully.';
            break;

            default:
                            $strMessage = 'Please try again.';
            break;
        }
        
        return $strMessage;
        
    }
    
    
    function blockedStatus($user_id)
    {
        $res=array(); $j=0;
        $query="SELECT * FROM user_deactivate_comments WHERE user_id =".$user_id." and type='D' ORDER BY id DESC LIMIT 1";
        $res2=$this->getDBRecords($query);
        $userIDinfo=$res2[0]['action_taken_by'];
        $queryToSelectUser="select surname,name from users where user_id='$userIDinfo'";
        $getUserNames=$this->getDBRecords($queryToSelectUser);
        $action_taken_by=$getUserNames[0]['name']." ".$getUserNames[0]['surname'];
        for($i=0;$i<count($res2);$i++)
        {

            $query2="SELECT * FROM organisation WHERE org_id =".$res2[$i]['org_id'];
            $res3=$this->getDBRecords($query2);
            $org_name = $res3[0]['name'];
            $res[$j]['org_name']=$org_name;
            $res[$j]['comments']=$res2[$i]["comments"];
            $j++;
         }
         
         $arrRes['res'] = $res;
         $arrRes['action_taken_by'] = $action_taken_by;
         
         return $arrRes;
    }
    
    
    function auditTrails($user_id)
    {
        $res=array(); $j=0;
        $query="SELECT username FROM users WHERE user_id =".$user_id." and accnt_locked<>'N'";
        $res2=$this->getDBRecords($query);
        $userIDinfo=$res2[0]['username'];
        $queryToSelectUser="select username, password, login_time from user_logs where username='$userIDinfo' and login_failed='Y' ORDER BY id DESC LIMIT 3";
        $getUserNames=$this->getDBRecords($queryToSelectUser);
        
        return $getUserNames;
    }    
}

?>
