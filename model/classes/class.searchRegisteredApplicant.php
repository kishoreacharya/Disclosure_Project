<?php
class SearchApplicant extends CommonLib {

    public $CURRENT_SURNAME = 3;
    public $CURRENT_FORNAME = 4;

    function __construct() {
        parent::__construct();
        return true;
    }

    /*
     * Function to fetch available ID Docs
     */

    function getAllIdDocs($category="") {
        if(!empty($category)) $condition = " and category = '$category'";
        $sqlAllIdDocs = "SELECT * FROM id_document WHERE active = 'Y' $condition ORDER BY doc_value ASC";
        $arrAllIdDocs = $this->getDBRecords($sqlAllIdDocs);
        return $arrAllIdDocs;
    }

   
    function getApplicantName($applicationId,$nameType)
    {
        $query = "select psn.* from person_names psn,app_person_name appsn ";
        $query.=" where psn.name_id=appsn.name_id and ";
        $query.=" appsn.application_id='$applicationId' and psn.name_type_id='$nameType'";
        $names = $this->getDBRecords($query);
        return $names;
    }

    function correctcase($str)
    {
        return $str;
    }
    
    
    /*
     * Function to get registered users
     * param1  : arrParam : Contains detains entered
     * return  : true     : Returns applicant details
     */
    function getRegisteredApplicant($arrParam)
    {
         $allapp=array();
        $j=0;
        $responseMessage="";

        if(!empty($arrParam['app_surname']) || !empty($arrParam['app_forename']))
        {
            #------------------Query for Application Submited [Application + ID section completed]--------------------------
            $listComp = '1,2,3,4,5';
            $commonQuery="SELECT CONCAT( ureg.firstname,  '  ', ureg.lastname ) AS name,ureg.*,sq.question 
                          FROM
                                    user_registration ureg
                                    INNER JOIN security_questions sq ON ureg.securityQuestion = sq.id
                                   
                          WHERE 1
                                    ";
                                  
            
             
            if(!empty ($arrParam['app_forename']))
            {
                $commonQuery.=" and ureg.email like '%".$arrParam['app_forename']."%'";
            }
             
            if(!empty ($arrParam['app_surname']))
            {
                $commonQuery.=" and ureg.email like '%".$arrParam['app_surname']."%'";
            }
            if(!empty ($arrParam['app_email']))
            {
                $commonQuery.=" and ureg.email like '%".$arrParam['app_email']."%'";
            }
            if(!empty($arrParam['app_organisation']))
            {
                 $commonQuery.=" and ureg.orgname like '%".$arrParam['app_organisation']."%'";
            }
            
            
           
            $res=$this->getDBRecords($commonQuery);
            
           
        }
        return $res;
    }
    
    /*
     * Function to get registered users end
     */
    
    /*
     * Function to get all registered users
     * return : true  : Returns applicant details
     * 
     */
    function getAllRegisteredApplicant()
    {
        $allapp=array();
        $j=0;
        $responseMessage="";

        
            #------------------Query for Application Submited [Application + ID section completed]--------------------------
            $commonQuery="SELECT CONCAT( ureg.firstname,  '  ', ureg.lastname ) AS name,ureg.*,sq.question 
                          FROM
                                    user_registration ureg
                                    INNER JOIN security_questions sq ON ureg.securityQuestion = sq.id";         
           
            $res=$this->getDBRecords($commonQuery);
            
           
       
        return $res;
    }
}
?>
