<?php

class editRegEmail extends CommonLib {

    public $USERACCESS = "cqcuser";
    public $ACCESSTYPE = "CU";

    function __construct() {
        parent::__construct();
        return true;
    }

    public function getRegEmail($userId,$userType) {
        if($userType == 'Applicant')
        {    
            $query = "SELECT ur.id,CONCAT( ur.firstname, ' ', ur.lastname ) AS name, ur.email from users u
                  inner join user_registration ur on u.username = ur.username
                  where u.user_id=$userId";
        }
        else
        {
            $query = "SELECT id,CONCAT( firstname, ' ', lastname ) AS name, email from user_registration where id=$userId";
        }    
        $userDetails = $this->getDBRecords($query);
        return $userDetails;
    }

   
    public function updateEditRegEmail($email, $userId,$modified_by,$old_email,$userType) {
 
       $query = "UPDATE user_registration SET email = '$email' WHERE id = $userId";
            $this->Query($query);
            
            if($old_email != $email)
            {
                  $fieldArray = '';
                    $fieldArray['user_reg_id']             =$userId;
                    $fieldArray['old_email']                =$old_email;
                    $fieldArray['new_email']               =$email;
                    $fieldArray['modified_user_id']        =$modified_by;
                    $fieldArray['modified_datentime']     =time();
                    $tableName                            = 'log_email_modified';
                    
                     $this->Insert($tableName, $fieldArray);
            }
            
       if($userType == 'Applicant')
       {
           $query = "SELECT u.user_id from user_registration ur
                  inner join users u on ur.username = u.username
                  where ur.id=$userId";
           $userDetails = $this->getDBRecords($query);
           $user_id = $userDetails[0]['user_id'];
           
           $query = "UPDATE users SET email = '$email' WHERE user_id = $user_id";
           $this->Query($query);
       }    
    }

}
?>
