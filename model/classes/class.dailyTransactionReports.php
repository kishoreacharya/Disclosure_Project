<?php

class DailyTransaction extends DBGrid {

    public $CURRENT_SURNAME = 3;
    public $CURRENT_FORNAME = 4;
    public $ReportFilterDays = 30;

    function __construct() {
        parent::__construct();
        return true;
    }

    /*
     * Function to fetch Total Applications Entered Report
     */

    public function getTotalAppEnterReport($report_type, $formstatus, $sectionXVeirfied, $sqlSelectq, $orgId=null) {

        $qTitles =
                array('tableCaption' => 'Total Applications Submitted', 'exportTypes' => 'xls',
                    'app_ref_no' => array('alias' => 'Form Reference Number', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'fsname' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
                    'submitdate' => array('alias' => 'Submit Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'position' => array('alias' => 'Position', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'dob' => array('alias' => 'Date of Birth', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'cancelled' => array('alias' => 'Cancel', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'application_id' => array('alias' => 'Unique ID', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'application_status' => array('alias' => 'Status', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'bandname' => array('alias' => 'Organisation Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'orgType' => array('alias' => 'Organisation Type', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'discType' => array('alias' => 'Level of Disclosure', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'psdate' => array('alias' => 'Processed & Sent On Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'dispatched' => array('alias' => 'Dispatch to '.DBS, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'result_date' => array('alias' => 'Result Received On', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'certno' => array('alias' => 'Certificate Number', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'appURN' => array('alias' => 'Application URN', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'sectionXVeirfied' => array('alias' => 'ID Verified', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'));
        
        $commonQuery = '';
        $commonQuery = "select STRAIGHT_JOIN $formstatus,o.name bandname,CONCAT(a.work_force,' ',a.position) position,upper(s.discType) discType,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,from_unixtime(a.submit_time,'%d/%m/%Y') submitdate,f.app_ref_no,if(f.`p&sDate`>0, from_unixtime(f.`p&sDate`,'%d/%m/%Y'), NULL)  AS psdate,if(f.dCrbDate>0, from_unixtime(f.dCrbDate,'%d/%m/%Y'), NULL) AS dispatched,if(f.rRdate>0, from_unixtime(f.rRdate,'%d/%m/%Y'),NULL) AS result_date,if(f.certno <> '' and f.certno is NOT NULL,f.certno,'-') as certno,u.unique_key as appURN,$sectionXVeirfied
        from applications a inner join organisation o on a.org_id=o.org_id INNER JOIN company c ON c.company_id =  o.company_id inner join sectionx s on a.application_id = s.application_id left join form_status f on {JOINEXPR} inner join app_person_name apnf on a.application_id = apnf.application_id inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4 inner join app_person_name apns on a.application_id = apns.application_id inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3 inner join reqdocs rqd on a.application_id = rqd.app_id inner join users u on rqd.user_id = u.user_id  where o.company_id in (1,2,3) and {EBULKCONDITION} and a.cancelled<>'Y' and a.sectionx_complete_time is not NULL";

        if (!empty($orgId))
            $commonQuery.=" and o.org_id='$orgId'";


        $joinexpr = " f.app_ref_no=s.app_ref_no";
        $ebulkconfition = " a.ebulkapp <> 'Y'";
        $content_var = array("{JOINEXPR}", "{EBULKCONDITION}");
        $content_value = array($joinexpr, $ebulkconfition);
        $pquery = str_replace($content_var, $content_value, $commonQuery);

#Query for Ebulk applications - Not printed
        $joinexpr = " s.application_id = f.application_id";
        $ebulkconfition = " a.ebulkapp = 'Y'";
        $content_var = array("{JOINEXPR}", "{EBULKCONDITION}");
        $content_value = array($joinexpr, $ebulkconfition);
        $equery = str_replace($content_var, $content_value, $commonQuery);
        $query = $pquery . $sqlSelectq . " UNION " . $equery . $sqlSelectq;
        //echo $query;exit;

        $this->query_to_table_source($query, $qTitles);
    }

    /*
     * Function to fetch Total Applications Cancelled Report
     */

    public function getTotalCanAppReport($report_type, $sqlSelectq, $sectionXVeirfied, $orgId=null) {

        $qTitles =
                array('tableCaption' => 'Total Applications Cancelled', 'exportTypes' => 'xls',
                    'app_ref_no' => array('alias' => 'Form Reference Number', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'fsname' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
                    'checked_by' => array('alias' => 'ID Checked By', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'submitdate' => array('alias' => 'Submit Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'position' => array('alias' => 'Position', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'dob' => array('alias' => 'Date of Birth', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'cancelled' => array('alias' => 'Cancel', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'application_status' => array('alias' => 'Status', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'cancelledon' => array('alias' => 'Cancel Date', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'bandname' => array('alias' => 'Organisation Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'orgType' => array('alias' => 'Organisation Type', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'discType' => array('alias' => 'Level of Disclosure', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'psdate' => array('alias' => 'Processed & Sent On Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'reason' => array('alias' => 'Reason for Cancellation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'appURN' => array('alias' => 'Application URN', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'sectionXVeirfied' => array('alias' => 'Status', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'));

        $commonQuery = '';
        $commonQuery = "select STRAIGHT_JOIN CONCAT_WS(' ',upper(pnf.name) ,upper(pns.name) ) AS fsname,o.name bandname, f.app_ref_no,CONCAT(a.work_force,' ',a.position) position,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,s.checked_by,from_unixtime(a.cancelled_date,'%d/%m/%Y') cancelledon,canc.comment as reason,u.unique_key as appURN,$sectionXVeirfied
        from applications a inner join organisation o on a.org_id=o.org_id INNER JOIN company c ON c.company_id = o.company_id inner join sectionx s on a.application_id = s.application_id left join form_status f on {JOINEXPR} inner join app_person_name apnf on a.application_id = apnf.application_id inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4 inner join app_person_name apns on a.application_id = apns.application_id inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3 LEFT JOIN cancellation_reason canc ON canc.application_id = a.application_id inner join reqdocs rqd on a.application_id = rqd.app_id inner join users u on rqd.user_id = u.user_id where o.company_id in (1,2,3) and {EBULKCONDITION} and a.cancelled='Y'";

        if (!empty($orgId))
            $commonQuery.=" and o.org_id='$orgId'";

        $joinexpr = " f.app_ref_no=s.app_ref_no";
        $ebulkconfition = " a.ebulkapp <> 'Y'";
        $content_var = array("{JOINEXPR}", "{EBULKCONDITION}");
        $content_value = array($joinexpr, $ebulkconfition);
        $pquery = str_replace($content_var, $content_value, $commonQuery);

#Query for Ebulk applications - Not printed
        $joinexpr = " s.application_id = f.application_id";
        $ebulkconfition = " a.ebulkapp = 'Y'";
        $content_var = array("{JOINEXPR}", "{EBULKCONDITION}");
        $content_value = array($joinexpr, $ebulkconfition);
        $equery = str_replace($content_var, $content_value, $commonQuery);
        $query = $pquery . $sqlSelectq . " UNION " . $equery . $sqlSelectq;

        $this->query_to_table_source($query, $qTitles);
    }

    /*
     * Function to fetch Applications Not Completed Report
     */
//
//    public function getAppNotCompleted($report_type, $sqlSelectq, $orgId=null) {
//
//        $qTitles =
//                array('tableCaption' => 'Application Not Completed', 'exportTypes' => 'xls',
//                    'application_id' => array('alias' => 'Application Id', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
//                    'oname' => array('alias' => 'Organisation Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
//                    'deptname' => array('alias' => 'Dept. Name', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
//                    'date_of_submission' => array('alias' => 'Date Of Submission', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
//                    'no_of_days_since_submitted' => array('alias' => 'No. Days Since submission', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
//                    'app_name' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
//                    'position' => array('alias' => 'Position applied for', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
//                    //'chkBox' => array('alias' => 'Choose', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
//                    'personalNumber' => array('alias' => 'Personnel Number', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
//                    'surreyCostCentre' => array('alias' => 'Cost Centre Number', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
//                    'appURN' => array('alias' => 'Application URN', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
//                    'Surname' => array('alias' => 'Surname', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'));
//
//        //$inputField = "CONCAT('<input type=\"checkbox\" name=\"checkBox[]\" id=\"checkBox[]\" value=\"',a.application_id,'_',u.user_id,'\" onclick=\"showCancelButton();\">') as chkBox";
////        $query = "select straight_join concat(pnf.name, ' ' , pns.name) app_name,o.name oname,u.unique_key as appURN, a.position
////              from applications a
////              inner join organisation o on a.org_id = o.org_id and a.cancelled <> 'Y'
////              INNER JOIN company c ON c.company_id = o.company_id
////              INNER JOIN sectionx s on a.application_id = s.application_id
////              inner join app_person_name apnf on a.application_id = apnf.application_id
////              inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4
////              inner join app_person_name apns on a.application_id = apns.application_id
////              inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3
////              inner join reqdocs r on a.application_id = r.app_id left join users u on r.username = u.username";
//        $query = "select straight_join upper(concat(u.name, ' ' , u.surname)) app_name,o.name oname,u.unique_key as appURN
//              from users u
//              inner join org_users ou ON u.user_id = ou.user_id 
//              inner join organisation o on ou.org_id = o.org_id
//              INNER JOIN company c ON c.company_id = o.company_id
//              LEFT JOIN reqdocs r on u.user_id = r.user_id";
//
//        $query.= " where o.company_id in (1,2,3) AND u.active = 'Y' AND r.id IS NULL";
//
//        if (!empty($orgId))
//            $query.=" AND ou.org_id='" . $orgId . "'";
//
//        if (!empty($sqlSelectq))
//            $query.=$sqlSelectq;
//
//
//
//        $this->query_to_table_source($query, $qTitles);
//    }

    public function getResultReceived($report_type, $formstatus, $orgId=null, $resultpopup, $resultpopupprinted) {
        $qTitles =
                array('tableCaption' => 'Result Received', 'exportTypes' => 'xls',
                    'fsname' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
                    'bandname' => array('alias' => 'Organisation Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'orgType' => array('alias' => 'Organisation Type', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'appURN' => array('alias' => 'Application URN', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'position' => array('alias' => 'Position', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'discType' => array('alias' => 'Level of Disclosure', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'checked_by' => array('alias' => 'ID Checked By', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'dob' => array('alias' => 'Date of Birth', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'submitdate' => array('alias' => 'Submit Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'app_ref_no' => array('alias' => 'Form Reference Number', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'psdate' => array('alias' => 'Processed & Sent On Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'dispatched' => array('alias' => 'Dispatch to '.DBS, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'result_date' => array('alias' => 'Result Received On', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'application_id' => array('alias' => 'Unique ID', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'application_status' => array('alias' => 'Status', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'certno' => array('alias' => 'Certificate Number', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'result_date1' => array('alias' => 'Verify Result', 'type' => 'string', 'parseHTML' => '1', 'visible' => '1', 'dontSum' => '0'));
        ;

        $commonQuery = '';
        $commonQuery = "select $formstatus,o.name bandname,CONCAT(a.work_force,' ',a.position) position,upper(s.discType) discType,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,s.checked_by,from_unixtime(a.submit_time,'%d/%m/%Y') submitdate,f.app_ref_no,if(f.`p&sDate`>0, from_unixtime(f.`p&sDate`,'%d/%m/%Y'), NULL)  AS psdate,if(f.dCrbDate>0, from_unixtime(f.dCrbDate,'%d/%m/%Y'), NULL) AS dispatched,if(f.rRdate>0, from_unixtime(f.rRdate,'%d/%m/%Y'),NULL) AS result_date,if(f.certno='','-',f.certno) as certno,if(a.ebulkapp='Y',$resultpopup,$resultpopupprinted)  AS result_date1,u.unique_key as appURN from applications a inner join organisation o on a.org_id=o.org_id inner join sectionx s on a.application_id = s.application_id left join form_status f on {JOINEXPR} inner join app_person_name apnf on a.application_id = apnf.application_id inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4 inner join app_person_name apns on a.application_id = apns.application_id inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3 inner join reqdocs rqd on a.application_id = rqd.app_id inner join users u on rqd.user_id = u.user_id where o.company_id in (1,2,3) AND f.dCrbDate is not NULL AND f.rRdate is not NULL and {EBULKCONDITION} ";

        if (!empty($orgId))
            $commonQuery.=" and o.org_id='$orgId'";


        $joinexpr = " f.app_ref_no=s.app_ref_no";
        $ebulkconfition = " a.ebulkapp <> 'Y'";
        $content_var = array("{JOINEXPR}", "{EBULKCONDITION}");
        $content_value = array($joinexpr, $ebulkconfition);
        $pquery = str_replace($content_var, $content_value, $commonQuery);

#Query for Ebulk applications - Not printed
        $joinexpr = " s.application_id = f.application_id";
        $ebulkconfition = " a.ebulkapp = 'Y'";
        $content_var = array("{JOINEXPR}", "{EBULKCONDITION}");
        $content_value = array($joinexpr, $ebulkconfition);
        $equery = str_replace($content_var, $content_value, $commonQuery);
        $query = $pquery . " UNION " . $equery;
        $this->query_to_table_source($query, $qTitles);
    }

    public function getLPF($report_type, $formstatus, $orgId) {
        $qTitles =
                array('tableCaption' => 'At LPF (Local Police Force)', 'exportTypes' => 'xls',
                    'fsname' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
                    'bandname' => array('alias' => 'Organisation Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'orgType' => array('alias' => 'Organisation Type', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'appURN' => array('alias' => 'Application URN', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'position' => array('alias' => 'Position', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'discType' => array('alias' => 'Level of Disclosure', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'checked_by' => array('alias' => 'ID Checked By', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'dob' => array('alias' => 'Date of Birth', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'submitdate' => array('alias' => 'Submit Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'app_ref_no' => array('alias' => 'Form Reference Number', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'psdate' => array('alias' => 'Processed & Sent On Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'dispatched' => array('alias' => 'Dispatch to '.DBS, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'pnc_stage' => array('alias' => 'PNC Date', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'application_id' => array('alias' => 'Unique ID', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'application_status' => array('alias' => 'Status', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
        );
        ;

        $commonQuery = '';
        $commonQuery = "select $formstatus,o.name bandname,CONCAT(a.work_force,' ',a.position) position,upper(s.discType) discType,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,s.checked_by,from_unixtime(a.submit_time,'%d/%m/%Y') submitdate,f.app_ref_no,if(f.`p&sDate`>0, from_unixtime(f.`p&sDate`,'%d/%m/%Y'), NULL)  AS psdate,if(f.dCrbDate>0, from_unixtime(f.dCrbDate,'%d/%m/%Y'), NULL) AS dispatched,if(f.rRdate>0, from_unixtime(f.rRdate,'%d/%m/%Y'),NULL) AS result_date,if(f.certno='','-',f.certno) as certno,from_unixtime(f.pnc_stage,'%d/%m/%Y') pnc_stage,u.unique_key as appURN from applications a inner join organisation o on a.org_id=o.org_id inner join sectionx s on a.application_id = s.application_id left join form_status f on {JOINEXPR} inner join app_person_name apnf on a.application_id = apnf.application_id inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4 inner join app_person_name apns on a.application_id = apns.application_id inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3 inner join reqdocs rqd on a.application_id = rqd.app_id inner join users u on rqd.user_id = u.user_id where o.company_id in (1,2,3) and f.dCrbDate is not NULL and f.lpfdate=0 and f.rRdate is NULL and f.crb_system is NOT NULL and f.initial_check is NOT NULL and f.pnc_stage is NOT NULL and f.cert_dispatched IS NULL and {EBULKCONDITION} ";

        if (!empty($orgId))
            $commonQuery.=" and o.org_id='$orgId'";


        $joinexpr = " f.app_ref_no=s.app_ref_no";
        $ebulkconfition = " a.ebulkapp <> 'Y'";
        $content_var = array("{JOINEXPR}", "{EBULKCONDITION}");
        $content_value = array($joinexpr, $ebulkconfition);
        $pquery = str_replace($content_var, $content_value, $commonQuery);

#Query for Ebulk applications - Not printed
        $joinexpr = " s.application_id = f.application_id";
        $ebulkconfition = " a.ebulkapp = 'Y'";
        $content_var = array("{JOINEXPR}", "{EBULKCONDITION}");
        $content_value = array($joinexpr, $ebulkconfition);
        $equery = str_replace($content_var, $content_value, $commonQuery);
        $query = $pquery . " UNION " . $equery;
        $this->query_to_table_source($query, $qTitles);
    }

    /*
     * Function to fetch Application Rejected Report
     */

    public function getAppRejected() {

    }

    public function getPNC($report_type, $formstatus, $orgId) {
        $qTitles =
                array('tableCaption' => 'At PNC (Police National Computer)', 'exportTypes' => 'xls',
                    'fsname' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
                    'bandname' => array('alias' => 'Organisation Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'orgType' => array('alias' => 'Organisation Type', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'appURN' => array('alias' => 'Application URN', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'position' => array('alias' => 'Position', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'discType' => array('alias' => 'Level of Disclosure', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'checked_by' => array('alias' => 'ID Checked By', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'dob' => array('alias' => 'Date of Birth', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'submitdate' => array('alias' => 'Submit Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'app_ref_no' => array('alias' => 'Form Reference Number', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'psdate' => array('alias' => 'Processed & Sent On Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'dispatched' => array('alias' => 'Dispatch to '.DBS, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'pnc_stage' => array('alias' => 'PNC Date', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'application_id' => array('alias' => 'Unique ID', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'application_status' => array('alias' => 'Status', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
        );
        ;

        $commonQuery = '';
        $commonQuery = "select $formstatus,o.name bandname,a.position,upper(s.discType) discType,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,s.checked_by,from_unixtime(a.submit_time,'%d/%m/%Y') submitdate,f.app_ref_no,if(f.`p&sDate`>0, from_unixtime(f.`p&sDate`,'%d/%m/%Y'), NULL)  AS psdate,if(f.dCrbDate>0, from_unixtime(f.dCrbDate,'%d/%m/%Y'), NULL) AS dispatched,if(f.rRdate>0, from_unixtime(f.rRdate,'%d/%m/%Y'),NULL) AS result_date,if(f.certno='','-',f.certno) as certno,from_unixtime(f.pnc_stage,'%d/%m/%Y') pnc_stage,u.unique_key as appURN from applications a inner join organisation o on a.org_id=o.org_id inner join sectionx s on a.application_id = s.application_id left join form_status f on {JOINEXPR} inner join app_person_name apnf on a.application_id = apnf.application_id inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4 inner join app_person_name apns on a.application_id = apns.application_id inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3 inner join reqdocs rqd on a.application_id = rqd.app_id inner join users u on rqd.user_id = u.user_id where o.company_id in (1,2,3) and f.dCrbDate is not NULL
and f.lpfdate=0 and f.rRdate is NULL and f.pnc_stage is NULL and f.initial_check is NOT NULL and f.crb_system is NOT NULL and f.cert_dispatched IS NULL and {EBULKCONDITION} ";

        if (!empty($orgId))
            $commonQuery.=" and o.org_id='$orgId'";


        $joinexpr = " f.app_ref_no=s.app_ref_no";
        $ebulkconfition = " a.ebulkapp <> 'Y'";
        $content_var = array("{JOINEXPR}", "{EBULKCONDITION}");
        $content_value = array($joinexpr, $ebulkconfition);
        $pquery = str_replace($content_var, $content_value, $commonQuery);

#Query for Ebulk applications - Not printed
        $joinexpr = " s.application_id = f.application_id";
        $ebulkconfition = " a.ebulkapp = 'Y'";
        $content_var = array("{JOINEXPR}", "{EBULKCONDITION}");
        $content_value = array($joinexpr, $ebulkconfition);
        $equery = str_replace($content_var, $content_value, $commonQuery);
        $query = $pquery . " UNION " . $equery;
        $this->query_to_table_source($query, $qTitles);
    }

    public function getAwaitingIDCheck($report_type, $formstatus, $orgId) {
        $qTitles =
                array('tableCaption' => 'Awaiting ID Check', 'exportTypes' => 'xls',
                    'fsname' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
                    'bandname' => array('alias' => 'Organisation Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'orgType' => array('alias' => 'Organisation Type', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'appURN' => array('alias' => 'Application URN', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'position' => array('alias' => 'Position', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'discType' => array('alias' => 'Level of Disclosure', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'dob' => array('alias' => 'Date of Birth', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'application_id' => array('alias' => 'Unique ID', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'application_status' => array('alias' => 'Status', 'type' => 'string', 'visible' => '1', 'dontSum' => '1')
        );



        $commonQuery = '';
        $commonQuery = "select STRAIGHT_JOIN CONCAT_WS(' ', upper(pnf.name) , upper(pns.name) ) AS fsname ,o.name bandname,CONCAT(a.work_force,' ',a.position) position,upper(s.discType) discType,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,u.unique_key as appURN from applications a inner join organisation o on a.org_id=o.org_id inner join sectionx s on a.application_id = s.application_id  inner join app_person_name apnf on a.application_id = apnf.application_id inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4 inner join app_person_name apns on a.application_id = apns.application_id inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3 inner join reqdocs rqd on a.application_id = rqd.app_id inner join users u on rqd.user_id = u.user_id where o.company_id in (1,2,3) and a.submit_time is NOT NULL and a.sectionx_complete_time is NULL  ";

        if (!empty($orgId))
            $commonQuery.=" and o.org_id='$orgId'";



        $query = $commonQuery;

#Query for Ebulk applications - Not printed

        $this->query_to_table_source($query, $qTitles);
    }

    public function getAwaitingAppInfo($report_type, $formstatus, $orgId) {
        $qTitles =
                array('tableCaption' => 'Awaiting Applicant Information', 'exportTypes' => 'xls',
                    'fsname' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
                    'bandname' => array('alias' => 'Organisation Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'orgType' => array('alias' => 'Organisation Type', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'appURN' => array('alias' => 'Application URN', 'type' => 'string', 'visible' => '2', 'dontSum' => '1'),
                    'application_status' => array('alias' => 'Status', 'type' => 'string', 'visible' => '1', 'dontSum' => '1')
        );


        $query = "SELECT STRAIGHT_JOIN
                               CONCAT_WS(' ', upper(ur.firstname) , upper(ur.lastname) ) AS fsname ,upper(ur.orgname) bandname,upper(ot.description) orgType,u.unique_key as appURN
                  FROM users u INNER JOIN org_users ou ON u.user_id = ou.user_id INNER JOIN organisation o on ou.org_id = o.org_id
                  INNER JOIN user_registration ur ON u.unique_key = ur.username
                  INNER JOIN organisation_type ot ON ur.orgtype = ot.id
                  WHERE u.user_id NOT IN (SELECT user_id FROM reqdocs) order by fsname";

        $this->query_to_table_source($query, $qTitles);
    }

    /*
     * Function to get All Counts
     */

    public function getAllReportCount($orgId=NULL, $getType=NULL) {
        if (empty($getType) && $getType != "AwaitAppInfo") {
            $commonQuery = "select

		 sum(if(a.application_id,1,0)) total_applications,

		 sum(if(a.cancelled<>'Y' and a.sectionx_complete_time is not NULL,1,0)) app_entered,

		 sum(if(a.cancelled='Y',1,0)) app_cancelled,

		 sum(if(f.application_id is NULL, 0, if(a.cancelled<>'Y' and f.dCrbDate is not NULL and f.rRDate is NULL,1,0))) total_app_inprogress_CRB,

		 sum(if(a.cancelled<>'Y' and a.sectionx_complete_time is not NULL and f.dCrbDate is NULL,1,0)) total_app_not_inprogress_CRB,

		 sum(if(f.application_id is NULL, 0, if(a.cancelled<>'Y' and f.dCrbDate is not NULL and f.rRDate is not NULL,1,0))) total_app_resultrecevied,

		 sum(if(f.application_id is NULL, 0, if(a.cancelled<>'Y' and f.dCrbDate is not NULL and f.lpfdate=0 and f.rRdate is NULL and f.crb_system is NOT NULL and f.initial_check is NOT NULL and f.pnc_stage is NOT NULL and f.cert_dispatched IS NULL,1,0))) total_app_at_lpf,

		 sum(if(f.application_id is NULL, 0, if(a.cancelled<>'Y' and f.`dCrbDate` IS NOT NULL and f.rRDate is NULL and f.crb_system is NOT NULL and f.initial_check is NOT NULL and f.pnc_stage is NULL and f.lpfdate=0 and f.cert_dispatched IS NULL,1,0))) total_app_at_pnc,

		 sum(if(a.cancelled<>'Y' and a.sectionx_complete_time is not NULL and a.submit_time is NULL,1,0)) total_app_info

       from applications a inner join organisation o on a.org_id=o.org_id ";

            if (!empty($orgId))
                $commonQuery.=" and o.activated='Y' and o.org_id='$orgId'";
            else
                $commonQuery.=" and o.company_id in (1,2,3) ";

            $commonQuery.=" inner join sectionx s on a.application_id = s.application_id and {EBULKCONDITION}  left join form_status f on {JOINEXPR} inner join reqdocs rqd on a.application_id = rqd.app_id inner join users u on rqd.user_id = u.user_id";

#Query for normal applications - printed
            $joinexpr = "f.app_ref_no=s.app_ref_no";
            $ebulkconfition = "a.ebulkapp <> 'Y'";
            $content_var = array("{JOINEXPR}", "{EBULKCONDITION}");
            $content_value = array($joinexpr, $ebulkconfition);
            $pquery = str_replace($content_var, $content_value, $commonQuery);

#Query for Ebulk applications - Not printed
            $joinexpr = "s.application_id = f.application_id";
            $ebulkconfition = " a.ebulkapp = 'Y'";
            $content_var = array("{JOINEXPR}", "{EBULKCONDITION}");
            $content_value = array($joinexpr, $ebulkconfition);
            $equery = str_replace($content_var, $content_value, $commonQuery);
            $query1 = "select sum(total_applications) total_applications,sum(app_entered) app_entered,sum(app_cancelled) app_cancelled,sum(total_app_inprogress_CRB) total_app_inprogress_CRB,sum(total_app_not_inprogress_CRB) total_app_not_inprogress_CRB,sum(total_app_resultrecevied) total_app_resultrecevied, sum(total_app_at_lpf) total_app_at_lpf,sum(total_app_at_pnc) total_app_at_pnc, sum(total_app_info)total_app_info ";
            $query = $query1 . " from (" . $pquery . " UNION " . $equery . ") z";
         
            $arrayCounts = $this->getDBRecords($query);
        } else {
            $query = "SELECT STRAIGHT_JOIN
                               CONCAT_WS(' ', upper(ur.firstname) , upper(ur.lastname) ) AS fsname ,upper(ur.orgname) bandname,upper(ot.description) orgType,u.unique_key as appURN
                  FROM users u INNER JOIN org_users ou ON u.user_id = ou.user_id INNER JOIN organisation o on ou.org_id = o.org_id
                  INNER JOIN user_registration ur ON u.unique_key = ur.username
                  INNER JOIN organisation_type ot ON ur.orgtype = ot.id
                  WHERE u.user_id NOT IN (SELECT user_id FROM reqdocs) order by fsname";
      
            $result = $this->getDBRecords($query);
            $arrayCounts = count($result);
        }
        return $arrayCounts;
    }

    /*
     * Function to Application not completed count
     */

    public function getAppNotCompCount($orgId=NULL) {

        $query = "select count(u.user_id) as total
              from users u
              inner join org_users ou ON u.user_id = ou.user_id 
              inner join organisation o on ou.org_id = o.org_id
              INNER JOIN company c ON c.company_id = o.company_id
              LEFT JOIN reqdocs r on u.user_id = r.user_id";

        $query.= " where o.company_id in (1,2,3) AND u.active = 'Y' AND r.id IS NULL";
        if (!empty($orgId))
            $query .= " AND ou.org_id='" . $orgId . "'";

        $arrayCounts = $this->getDBRecords($query);

        return $arrayCounts;
    }

    /*
     * Function to Await ID Checks count
     */

    public function getAwaitIDCount($orgId=NULL) {
        $aquery = "select count(a.application_id) as cnt from applications a inner join organisation o on a.org_id=o.org_id inner join sectionx s on a.application_id = s.application_id inner join app_person_name apnf on a.application_id = apnf.application_id
          inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4
          inner join app_person_name apns on a.application_id = apns.application_id
          inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3
          inner join reqdocs rqd on a.application_id = rqd.app_id inner join users u on rqd.user_id = u.user_id
          where a.submit_time is NOT NULL and a.sectionx_complete_time is NULL";
        if (!empty($orgId))
            $aquery.=" and o.activated='Y' and o.org_id='$orgId'";
        else
            $aquery.=" and o.company_id in (1,2,3) ";

        $app_awaiting_count = $this->getDBRecords($aquery);
        return $app_awaiting_count;
    }
    
    function getCount($fromdate,$todate,$listComp)
    {
        if(!empty($fromdate)){ 
            $fromdate =$this->convertToTimestamp1($fromdate);
        }

        if(!empty($todate)) {
            $todate=$this->convertToTimestamp2($todate);
        }
        
        if(isset($fromdate) && !empty($fromdate) && !empty($todate)) {
        if($todate > $fromdate)
         $toVal = $todate;#-(60*60*24);
          else
         $toVal = $todate;

        $fromVal = $fromdate;#+(60*60*24);
        $commonQuerysubmitted.=" and a.submit_time between $fromVal and $toVal";
        $resultreceived .=" and f.rRDate between $fromVal and $toVal";
        $atprogresswithcrb.=" and f.dCrbDate between $fromVal and $toVal";
        $resultrechecked3years .=" and f.rRDate between unix_timestamp(date_sub(from_unixtime($fromVal),interval 3 year)) and unix_timestamp(date_sub(from_unixtime($toVal),interval 3 year))";
        $manual_application_submitted.=" and a.sectionx_complete_time between $fromVal and $toVal";
        $twentydayQuery.=" and u.reg_email_sent_date between $fromVal and $toVal";
       $dif = $todate - $fromdate;
     }
		else if(!empty($fromdate)) {
		    $commonQuerysubmitted.=" and a.submit_time > $fromdate";
		    $resultreceived.=" and f.rRDate > $fromdate";
		    $atprogresswithcrb.=" and f.dCrbDate > $fromdate"; 
		    $resultrechecked3years.=" and f.rRDate < unix_timestamp(date_sub(from_unixtime($fromdate),interval 3 year)) ";
		    $manual_application_submitted.=" and a.sectionx_complete_time > $fromdate";
                    $twentydayQuery.=" and u.reg_email_sent_date >= $fromdate";
		    $todate = "";
		}
                else if(!empty($todate)) {
		    $commonQuerysubmitted.=" and a.submit_time <= $todate";
		    $resultreceived.=" and f.rRDate <= $todate";
		    $atprogresswithcrb.=" and f.dCrbDate <= $todate"; 
		    $resultrechecked3years.=" and f.rRDate <= unix_timestamp(date_sub(from_unixtime($todate),interval 3 year)) ";
		    $manual_application_submitted.=" and a.sectionx_complete_time <= $todate";
                    $twentydayQuery.=" and u.reg_email_sent_date <= $todate";
		    $fromdate = "";
		}
$commonQuery = '';
$commonQuery="select 
	 
		 sum(if(a.cancelled<>'Y' and a.sectionx_complete_time is not NULL $commonQuerysubmitted,1,0)) app_entered,

		 sum(if(f.application_id is NULL, 0, if(a.cancelled<>'Y' and f.dCrbDate is not NULL and f.rRDate is NULL $atprogresswithcrb,1,0))) total_app_inprogress_CRB,

		 sum(if(f.application_id is NULL, 0, if(a.cancelled<>'Y' and f.dCrbDate is not NULL and f.rRDate is not NULL $resultreceived,1,0))) total_app_resultrecevied,
     sum(if(f.application_id is NULL, 0, if(a.cancelled<>'Y' and f.dCrbDate is not NULL and f.rRDate is not NULL $resultrechecked3years,1,0))) total_app_rechecked3years,
     sum(if(a.cancelled<>'Y' and a.sectionx_complete_time is not NULL $manual_application_submitted AND a.manual_application='Y',1,0)) total_manual_application
       from applications a inner join organisation o on a.org_id=o.org_id ";

         if(!empty($orgId))
       $commonQuery.=" and o.activated='Y' and o.org_id='$orgId'";
       else 
       $commonQuery.=" and o.company_id in ($listComp)  ";

       $commonQuery.=" inner join sectionx s on a.application_id = s.application_id and {EBULKCONDITION}  left join form_status f on {JOINEXPR} "; 

         #Query for normal applications - printed
      $joinexpr="f.app_ref_no=s.app_ref_no";
      $ebulkconfition="a.ebulkapp <> 'Y'";
      $content_var=array("{JOINEXPR}","{EBULKCONDITION}");
      $content_value=array($joinexpr,$ebulkconfition);
      $pquery=str_replace($content_var,$content_value,$commonQuery);
     
      #Query for Ebulk applications - Not printed
      $joinexpr="s.application_id = f.application_id";
      $ebulkconfition=" a.ebulkapp = 'Y'";
      $content_var=array("{JOINEXPR}","{EBULKCONDITION}");
      $content_value=array($joinexpr,$ebulkconfition);
      $equery=str_replace($content_var,$content_value,$commonQuery);
      $query1 = "select sum(app_entered) app_entered,sum(total_app_inprogress_CRB) total_app_inprogress_CRB,sum(total_app_resultrecevied) total_app_resultrecevied,sum(total_app_rechecked3years) total_app_rechecked3years, sum(total_manual_application) total_manual_application  ";
      $query= $query1 . " from (" . $pquery." UNION ".$equery . ") z";
     

if(!empty($fromdate) || !empty($todate))
      $app_counts = $this->getDBRecords($query);


$query="SELECT company.name, date_format(from_unixtime(app.hold_contacted),'%d-%m-%Y') as hold_contacted,date_format(from_unixtime(app.print_hold),'%d-%m-%Y') as hold_date, app.pulled_stage,app.application_id, app.date_of_birth, app.org_id, secx.checker_id,app.title, app.other_title_text, app.supliment_required sup1, secx.supliment_required sup2, app.org_id
	FROM applications app, sectionx secx, organisation, company
	WHERE app.org_id = organisation.org_id
	AND app.application_id = secx.application_id
	AND organisation.company_id = company.company_id
	AND organisation.test_account<>'Y'
	AND secx.app_ref_no IS NULL 
	AND app.printed='N' 
	AND app.ebulkapp = 'Y'
	AND app.cancelled <> 'Y'";
	
	if(!empty($orgId))
       $query.=" and organisation.activated='Y' and organisation.org_id='$orgId'";
       else 
       $query.=" and organisation.company_id in ($listComp)  ";


 if(isset($fromdate) && !empty($fromdate) && !empty($todate))
	 {
	   if($todate > $fromdate)
		$toVal = $todate;#-(60*60*24);
	   else
		$toVal = $todate;
	
	   $fromVal = $fromdate;#+(60*60*24);
	   $query.=" and app.print_hold between $fromVal and $toVal";
	   $dif = $todate - $fromdate;
	
	 }else if(!empty($fromdate))
	 {
	  $query.=" and app.print_hold > $fromdate";
	  $todate = "";
	 }
         else if(!empty($todate))
	 {
	  $query.=" and app.print_hold <= $todate";
	  $fromdate = "";
	 }

 $query.=" AND app.good_to_print in ('Q','B','E')  order by app.application_id";
 if(!empty($fromdate) || !empty($todate))
 $onhold_applications = $this->getDBRecords($query);

        ########################################################################################################################
      


        $countersign_query="select a.ebulkapp, a.application_id, a.release_date, a.date_of_birth, b.name, CONCAT(a.work_force,' ',a.position) position, a.national_insurance, c.remuneration,form_status.`p&sDate`,ecs.counter_signatory_name,ecs.counter_signatory_id,ea.disc_application_id,ea.counter_signatory_id from applications a, organisation b, sectionx c,form_status,ebulk_counter_signatory ecs,ebulk_applications ea where a.application_id=c.application_id and a.org_id=b.org_id and a.application_id=form_status.application_id and a.good_to_print='Y' and a.application_id=ea.disc_application_id and ea.counter_signatory_id=ecs.counter_signatory_id and a.cancelled<>'Y' and b.test_account<>'Y' and a.ebulkapp='Y'";
	
	 if(isset($fromdate) && !empty($fromdate) && !empty($todate))
	 {
	   if($todate > $fromdate)
		$toVal = $todate;#-(60*60*24);
	   else
		$toVal = $todate;
	
	   $fromVal = $fromdate;#+(60*60*24);
	   $countersign_query.=" and form_status.`p&sDate` between $fromVal and $toVal";
	   $dif = $todate - $fromdate;
	
	 }
	 else if(!empty($fromdate))
	 {
	  $countersign_query.=" and form_status.`p&sDate` > $fromdate";
	  $todate = "";
	 }
         else if(!empty($todate))
	 {
	  $query.=" and form_status.`p&sDate` <= $todate";
	  $fromdate = "";
	 }
	 
	 if(!empty($orgId))
         $countersign_query.=" and b.activated='Y' and o.org_id='$orgId'";
         else 
         $countersign_query.=" and b.company_id in ($listComp)";
	 
	 $countersign_query.=" ORDER BY form_status.`p&sDate` asc";
	 if(!empty($fromdate) || !empty($todate))
	 $countersign_result = $this->getDBRecords($countersign_query);

            ########################################################################################################################
         

           $query = "select count(u.user_id) as total
              from users u
              inner join org_users ou ON u.user_id = ou.user_id 
              inner join organisation o on ou.org_id = o.org_id
              INNER JOIN company c ON c.company_id = o.company_id
              LEFT JOIN reqdocs r on u.user_id = r.user_id";

        $query.= " where o.company_id in (1,2,3) AND u.active = 'Y' AND r.id IS NULL";
        if (!empty($orgId))
            $query .= " AND ou.org_id='" . $orgId . "'";
            //        if(!empty($fromdate))
            //        {
            //            $query.=" and a.sectionx_complete_time >= $fromdate ";
            //        }
            //        if(!empty($todate))
            //        {
            //            $query.=" and a.sectionx_complete_time < $todate ";
            //        }
                    $query.=" and o.company_id in ($listComp)  $twentydayQuery";

            if(!empty($fromdate) || !empty($todate))
                 $twentyResult = $this->getDBRecords($query);


            ########################################################################################################################
            if(empty($app_counts[0]['total_app_resultrecevied']))
            $arrTotalCount['total_app_resultrecevied']=0;
            else
            $arrTotalCount['total_app_resultrecevied']=$app_counts[0]['total_app_resultrecevied'];

            if(empty($app_counts[0]['app_entered']))
            $arrTotalCount['app_entered']=0;
            else 
            $arrTotalCount['app_entered']=$app_counts[0]['app_entered'];

            if(empty($app_counts[0]['total_app_inprogress_CRB']))
            $arrTotalCount['total_app_inprogress_CRB']=0;
            else
            $arrTotalCount['total_app_inprogress_CRB']=$app_counts[0]['total_app_inprogress_CRB'];

            if(empty($app_counts[0]['total_app_rechecked3years']))
            $arrTotalCount['total_app_rechecked3years']=0;
            else
            $arrTotalCount['total_app_rechecked3years']=$app_counts[0]['total_app_rechecked3years'];

            if(empty($app_counts[0]['total_manual_application']))
            $arrTotalCount['total_manual_application']=0;
            else
            $arrTotalCount['total_manual_application']=$app_counts[0]['total_manual_application'];

            if(empty($twentyResult[0]['total']))
            $arrTotalCount['twentyResult']=0;
            else
             $arrTotalCount['twentyResult']=$twentyResult[0]['total'];
            
            $arrTotalCount['onhold_applications'] = $onhold_applications;
            $arrTotalCount['countersign_result']  = $countersign_result;

            $query="select count(a.application_id) as totalapps from (applications a, organisation b, company c,sectionx s)
                    LEFT JOIN log_work_at_home_modified lwahm ON a.application_id = lwahm.application_id
                    where a.org_id=b.org_id and b.activated='Y' and good_to_print='S' and a.cnt_sign_local='N'
                    and c.company_id=b.company_id and a.application_id=s.application_id and s.workingathomeaddress='Y' and lwahm.application_id is null $commonQuerysubmitted ";

            if(!empty($orgId))
            $query.=" and b.activated='Y' and b.org_id='$orgId'";
            else
            $query.=" and b.company_id in ($listComp)  ";
       
            $rsWorkAtHomeUnSuc=$this->getDBRecords($query);
            if(empty($rsWorkAtHomeUnSuc[0]["totalapps"]))
            $arrTotalCount['total_working_from_home'] = 0;
            else
            $arrTotalCount['total_working_from_home']  = $rsWorkAtHomeUnSuc[0]["totalapps"];
            
            return $arrTotalCount;
    }
    
    function getResultReceivedReport($formstatus, $orgId,$resultpopup,$resultpopupprinted,$resultreceived,$listComp)
    {
         $qTitles = array(
            'tableCaption'=>'Result Received','exportTypes'=>'xls',
	        'app_ref_no'=>array('alias'=>'Form Ref','type'=>'string','visible'=>'1','dontSum'=>'1'),
	        'fsname'=>array('alias'=>'Applicant Name','type'=>'string','visible'=>'1','parseHTML'=>'1','dontSum'=>'1'),
	        'deptname'=>array('alias'=>'Dept. Name','type'=>'string','visible'=>'0','dontSum'=>'1'),
	        'bandname'=>array('alias'=>$bandGrade,'type'=>'string','visible'=>'0','dontSum'=>'1'),
	        'position'=>array('alias'=>'Position','type'=>'string','visible'=>'0','dontSum'=>'1'),
	        'checked_by'=>array('alias'=>'ID Checked By','type'=>'string','visible'=>'0','dontSum'=>'1'),
	        'dob'=>array('alias'=>'DOB','type'=>'string','visible'=>'0','dontSum'=>'1'),
            'orgname' => array('alias' => 'Actual Organisation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'job' => array('alias' => 'Job Title', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'works_with_children' => array('alias' => 'Works with Children', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'works_with_adult' => array('alias' => 'Works with '.VG_FULL, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
	        'discType'=>array('alias'=>'Level of Disclosure','type'=>'string','visible'=>'0','dontSum'=>'1'),
	        'submitdate'=>array('alias'=>'Submit Date','type'=>'string','visible'=>'0','dontSum'=>'1'),
	        'app_ref_no'=>array('alias'=>'Form Ref','type'=>'string','visible'=>'0','dontSum'=>'1'),
	        'psdate'=>array('alias'=>'Processed & Sent on','type'=>'string','visible'=>'0','dontSum'=>'1'),
	        'dispatched'=>array('alias'=>'Dispatch to '.DBS.' on','type'=>'string','visible'=>'1','dontSum'=>'1'),
	        'result_date'=>array('alias'=>'Result received on','type'=>'string','visible'=>'1','dontSum'=>'1'),
	        'certno'=>array('alias'=>'Certificate no.','type'=>'string','visible'=>'1','dontSum'=>'1'),
            'appURN' => array('alias' => 'Application URN', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'result_date1'=>array('alias'=>'Verify Result','type'=>'string','parseHTML'=>'1', 'visible'=>'1','dontSum'=>'0')
         );


$query="SELECT  $formstatus,c.name deptname,o.name bandname,CONCAT(a.work_force,' ',a.position) position,j.job,ur.orgname,if(s.jobwork = 1 OR s.jobwork = 3,'Yes','No') works_with_adult,if(s.jobwork = 2 OR s.jobwork = 3,'Yes','No') works_with_children,upper(s.discType) discType,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,s.checked_by,from_unixtime(a.submit_time,'%d/%m/%Y') submitdate, f.app_ref_no,
       if(f.`p&sDate`>0, from_unixtime(f.`p&sDate`,'%d/%m/%Y'), NULL)  AS psdate ,
       if(f.dCrbDate>0, from_unixtime(f.dCrbDate,'%d/%m/%Y'), NULL) AS dispatched,u.unique_key as appURN,
       if(f.rRdate>0, from_unixtime(f.rRdate,'%d/%m/%Y'),NULL) AS result_date,f.certno,if(a.ebulkapp='Y',$resultpopup,$resultpopupprinted)  AS result_date1
        FROM applications a
        INNER JOIN organisation o ON a.org_id=o.org_id
	INNER JOIN company c ON o.company_id=c.company_id
        INNER JOIN sectionx s ON a.application_id = s.application_id
        INNER JOIN form_status f ON s.application_id = f.application_id
        INNER JOIN app_person_name apnf ON a.application_id = apnf.application_id
        INNER JOIN person_names pnf ON apnf.name_id = pnf.name_id and pnf.name_type_id = 4
        INNER JOIN app_person_name apns ON a.application_id = apns.application_id
        INNER JOIN person_names pns ON apns.name_id = pns.name_id and pns.name_type_id = 3
        LEFT OUTER join reqdocs rqd on a.application_id = rqd.app_id LEFT OUTER join users u on rqd.user_id = u.user_id 
         left outer join user_registration ur on u.username = ur.username left outer join jobs j on ur.job_role = j.sno
        WHERE a.cancelled<>'Y' AND f.dCrbDate is not NULL AND f.rRdate is not NULL $resultreceived";
        
$query.= " and o.company_id in ($listComp)";
if(!empty($orgId))
    $query.=" and o.org_id='$orgId'";


$query.=" order by fsname";

$this->query_to_table_source($query,$qTitles);
    }

    function getTotalAppSubmittedReport($formstatus,$orgId,$commonQuerysubmitted,$listComp)
    {
        $qTitles = array(
            'tableCaption'=>'Total Applications Submitted','exportTypes'=>'xls',
	        'app_ref_no'=>array('alias'=>'Form Ref','type'=>'string','visible'=>'1','dontSum'=>'1'),
	        'fsname'=>array('alias'=>'Applicant Name','type'=>'string','visible'=>'1','parseHTML'=>'1','dontSum'=>'1'),
	        'id_date'=>array('alias'=>'ID Checked on','type'=>'string','visible'=>'1','dontSum'=>'1'),
	        'checked_by'=>array('alias'=>'ID Checked By','type'=>'string','visible'=>'0','dontSum'=>'1'),
	        'submitdate'=>array('alias'=>'Submit Date','type'=>'string','visible'=>'0','dontSum'=>'1'),
	        'position'=>array('alias'=>'Position','type'=>'string','visible'=>'0','dontSum'=>'1'),
	        'dob'=>array('alias'=>'DOB','type'=>'string','visible'=>'0','dontSum'=>'1'),
            'orgname' => array('alias' => 'Actual Organisation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'deptname'=>array('alias'=>'Dept. Name','type'=>'string','visible'=>'0','dontSum'=>'1'),
            'job' => array('alias' => 'Job Title', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'works_with_children' => array('alias' => 'Works with Children', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'works_with_adult' => array('alias' => 'Works with '.VG_FULL, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
	        'application_id'=>array('alias'=>'Unique ID','type'=>'string','visible'=>'0','dontSum'=>'1'),
            'appURN' => array('alias' => 'Application URN', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
	        'name'=>array('alias'=>$bandGrade,'type'=>'string','visible'=>'0','dontSum'=>'1')
        );
            

$commonQuery="select o.name,c.name deptname,$formstatus,from_unixtime(a.sectionx_complete_time,'%d/%m/%Y') id_date,j.job,ur.orgname,if(s.jobwork = 1 OR s.jobwork = 3,'Yes','No') works_with_adult,if(s.jobwork = 2 OR s.jobwork = 3,'Yes','No') works_with_children, from_unixtime(a.submit_time,'%d/%m/%Y') submitdate,s.checked_by, f.app_ref_no,CONCAT(a.work_force,' ',a.position) position,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,a.application_id,u.unique_key as appURN from applications a inner join organisation o on a.org_id=o.org_id INNER JOIN company c ON o.company_id=c.company_id inner join sectionx s on a.application_id = s.application_id left join form_status f on {JOINEXPR} inner join app_person_name apnf on a.application_id = apnf.application_id inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4 inner join app_person_name apns on a.application_id = apns.application_id inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3  inner join reqdocs rqd on a.application_id = rqd.app_id inner join users u on rqd.user_id = u.user_id  left outer join user_registration ur on u.username = ur.username left outer join jobs j on ur.job_role = j.sno where o.company_id in ($listComp) and {EBULKCONDITION} and a.cancelled<>'Y' and a.sectionx_complete_time is not NULL $commonQuerysubmitted";

if(!empty($orgId))
$commonQuery.=" and o.org_id='$orgId'";
else if(!empty($branchAccess))
    $commonQuery.=" and o.org_id IN ($branchAccess)";

      $joinexpr=" f.app_ref_no=s.app_ref_no";
      $ebulkconfition=" a.ebulkapp <> 'Y'";
      $content_var=array("{JOINEXPR}","{EBULKCONDITION}");
      $content_value=array($joinexpr,$ebulkconfition);
      $pquery=str_replace($content_var,$content_value,$commonQuery);
     
      #Query for Ebulk applications - Not printed
      $joinexpr=" s.application_id = f.application_id";
      $ebulkconfition=" a.ebulkapp = 'Y'";
      $content_var=array("{JOINEXPR}","{EBULKCONDITION}");
      $content_value=array($joinexpr,$ebulkconfition);
      $equery=str_replace($content_var,$content_value,$commonQuery);
      $query= $pquery." UNION ".$equery." order by fsname" ;


     $this->query_to_table_source($query,$qTitles);
    }
    
    function getAppCountersigned($orgId,$countersign_query,$listComp)
    {
        $qTitles = array(
            'tableCaption'=>'Total Countersigned Applications','exportTypes'=>'xls',
            'fsname'=>array('alias'=>'Applicant Name','type'=>'string','visible'=>'1','parseHTML'=>'1','dontSum'=>'1'),
            'release_date'=>array('alias'=>'Countersigned Date','type'=>'string','visible'=>'1','dontSum'=>'1'),
            'counter_signatory_name'=>array('alias'=>'Counter Signatory Name','type'=>'string','visible'=>'1','dontSum'=>'1'),
            'submitdate'=>array('alias'=>'Submit Date','type'=>'string','visible'=>'0','dontSum'=>'1'),
            'dob'=>array('alias'=>'DOB','type'=>'string','visible'=>'0','dontSum'=>'1'),
            'orgname' => array('alias' => 'Actual Organisation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'deptname'=>array('alias'=>'Dept. Name','type'=>'string','visible'=>'0','dontSum'=>'1'),
            'job' => array('alias' => 'Job Title', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'works_with_children' => array('alias' => 'Works with Children', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'works_with_adult' => array('alias' => 'Works with '.VG_FULL, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'application_id'=>array('alias'=>'Unique ID','type'=>'string','visible'=>'0','dontSum'=>'1'),
            'appURN' => array('alias' => 'Application URN', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'name'=>array('alias'=>$bandGrade,'type'=>'string','visible'=>'0','dontSum'=>'1')
        );

       $countersign_query = "select CONCAT_WS(' ', upper(pnf.name) ,upper(pns.name) ) AS fsname,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,date_format(from_unixtime(a.release_date),'%d-%m-%Y') as release_date,ecs.counter_signatory_name,from_unixtime(a.submit_time,'%d/%m/%Y') submitdate,a.application_id, o.name name,c.name deptname,u.unique_key as appURN,j.job,ur.orgname,if(s.jobwork = 1 OR s.jobwork = 3,'Yes','No') works_with_adult,if(s.jobwork = 2 OR s.jobwork = 3,'Yes','No') works_with_children
        from ebulk_counter_signatory ecs,ebulk_applications ea ,applications a
            INNER JOIN organisation o ON a.org_id=o.org_id
            INNER JOIN company c ON o.company_id=c.company_id
       INNER JOIN sectionx s ON a.application_id = s.application_id
       INNER JOIN form_status f ON s.application_id = f.application_id
       INNER JOIN app_person_name apnf ON a.application_id = apnf.application_id
       INNER JOIN person_names pnf ON apnf.name_id = pnf.name_id and pnf.name_type_id = 4
       INNER JOIN app_person_name apns ON a.application_id = apns.application_id
       INNER JOIN person_names pns ON apns.name_id = pns.name_id and pns.name_type_id = 3  
         inner join reqdocs rqd on a.application_id = rqd.app_id inner join users u on rqd.user_id = u.user_id 
        left outer join user_registration ur on u.username = ur.username left outer join jobs j on ur.job_role = j.sno
        where a.good_to_print='Y' and a.application_id=ea.disc_application_id and ea.counter_signatory_id=ecs.counter_signatory_id and a.cancelled<>'Y' and o.test_account<>'Y' and a.ebulkapp='Y' $countersign_query";
            if(!empty($orgId))
       $countersign_query.=" and o.activated='Y' and o.org_id='$orgId'";
       else 
       $countersign_query.=" and o.company_id in ($listComp) $branchQuery ";
            $countersign_query.=" ORDER BY f.`p&sDate` asc"; 
       $this->query_to_table_source($countersign_query,$qTitles);
    }
    
    function getAppOnHold($orgId,$onhold_query,$listComp)
    {
        $qTitles = array(
            'tableCaption'=>'Total Applications On hold','exportTypes'=>'xls',
	        'fsname'=>array('alias'=>'Applicant Name','type'=>'string','visible'=>'1','parseHTML'=>'1','dontSum'=>'1'),
	        'hold_date'=>array('alias'=>'Hold Date','type'=>'string','visible'=>'1','dontSum'=>'1'),
	        'hold_contacted'=>array('alias'=>'Contacted Date','type'=>'string','visible'=>'1','dontSum'=>'1'),
	        'pulled_stage'=>array('alias'=>'Pulled out At','type'=>'string','visible'=>'1','dontSum'=>'1'),
	        'submitdate'=>array('alias'=>'Submit Date','type'=>'string','visible'=>'0','dontSum'=>'1'),
	        'dob'=>array('alias'=>'DOB','type'=>'string','visible'=>'0','dontSum'=>'1'),
            'orgname' => array('alias' => 'Actual Organisation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'deptname'=>array('alias'=>'Dept. Name','type'=>'string','visible'=>'0','dontSum'=>'1'),
            'job' => array('alias' => 'Job Title', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'works_with_children' => array('alias' => 'Works with Children', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'works_with_adult' => array('alias' => 'Works with '.VG_FULL, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
	        'application_id'=>array('alias'=>'Unique ID','type'=>'string','visible'=>'0','dontSum'=>'1'),
            'appURN' => array('alias' => 'Application URN', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
	        'name'=>array('alias'=>$bandGrade,'type'=>'string','visible'=>'0','dontSum'=>'1')
        );

$query="SELECT CONCAT_WS(' ', upper(pnf.name) ,upper(pns.name) ) AS fsname,date_format(from_unixtime(a.print_hold),'%d-%m-%Y') as hold_date,date_format(from_unixtime(a.hold_contacted),'%d-%m-%Y') as hold_contacted, a.pulled_stage,from_unixtime(a.submit_time,'%d/%m/%Y') submitdate,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,a.application_id, o.name name,c.name deptname,u.unique_key as appURN,j.job,ur.orgname,if(s.jobwork = 1 OR s.jobwork = 3,'Yes','No') works_with_adult,if(s.jobwork = 2 OR s.jobwork = 3,'Yes','No') works_with_children
	FROM applications a
	INNER JOIN organisation o ON a.org_id=o.org_id
		INNER JOIN company c ON o.company_id=c.company_id
        INNER JOIN sectionx s ON a.application_id = s.application_id
        LEFT JOIN form_status f ON s.application_id = f.application_id
        INNER JOIN app_person_name apnf ON a.application_id = apnf.application_id
        INNER JOIN person_names pnf ON apnf.name_id = pnf.name_id and pnf.name_type_id = 4
        INNER JOIN app_person_name apns ON a.application_id = apns.application_id
        INNER JOIN person_names pns ON apns.name_id = pns.name_id and pns.name_type_id = 3 
        INNER JOIN reqdocs rqd on a.application_id = rqd.app_id 
        INNER JOIN users u on rqd.user_id = u.user_id 
    left outer join user_registration ur on u.username = ur.username left outer join jobs j on ur.job_role = j.sno
	WHERE o.test_account<>'Y'
	AND s.app_ref_no IS NULL 
	AND a.printed='N' 
	AND a.ebulkapp = 'Y'
	AND a.cancelled <> 'Y'";
	
	if(!empty($orgId))
           $query.=" and o.activated='Y' and o.org_id='$orgId'";
        else 
           $query.=" and o.company_id in ($listComp)  ";
 
            $query.=" $onhold_query AND a.good_to_print in ('Q','B','E')  order by a.application_id";

      $this->query_to_table_source($query,$qTitles);
    }
    
    function getAppinCRB($formstatus, $orgId,$listComp,$atprogresswithcrb)
    {
        $qTitles = array(
            'tableCaption'=>'Total Applications in Progress with '.DBS,'exportTypes'=>'xls',
	        'app_ref_no'=>array('alias'=>'Form Ref','type'=>'string','visible'=>'1','dontSum'=>'1'),
	        'fsname'=>array('alias'=>'Applicant Name','type'=>'string','visible'=>'1','parseHTML'=>'1','dontSum'=>'1'),
	        'id_date'=>array('alias'=>'ID Checked on','type'=>'string','visible'=>'0','dontSum'=>'1'),
	        'checked_by'=>array('alias'=>'ID Checked By','type'=>'string','visible'=>'0','dontSum'=>'1'),
            'orgname' => array('alias' => 'Actual Organisation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'deptname'=>array('alias'=>'Dept. Name','type'=>'string','visible'=>'0','dontSum'=>'1'),
            'job' => array('alias' => 'Job Title', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'works_with_children' => array('alias' => 'Works with Children', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'works_with_adult' => array('alias' => 'Works with '.VG_FULL, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
	        'submitdate'=>array('alias'=>'Submit Date','type'=>'string','visible'=>'1','dontSum'=>'1'),
	        'dispatched'=>array('alias'=>'Sent to '.DBS,'type'=>'string','visible'=>'1','dontSum'=>'1'),
	        'position'=>array('alias'=>'Position','type'=>'string','visible'=>'0','dontSum'=>'1'),
	        'dob'=>array('alias'=>'DOB','type'=>'string','visible'=>'0','dontSum'=>'1'),
	        'application_id'=>array('alias'=>'Unique ID','type'=>'string','visible'=>'0','dontSum'=>'1'),
            'appURN' => array('alias' => 'Application URN', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
	        'name'=>array('alias'=>$bandGrade,'type'=>'string','visible'=>'0','dontSum'=>'1')
        );
        
$commonQuery = '';
$commonQuery="select o.name,c.name deptname,$formstatus,from_unixtime(a.sectionx_complete_time,'%d/%m/%Y') id_date, from_unixtime(a.submit_time,'%d/%m/%Y') submitdate,from_unixtime(f.dCrbDate,'%d/%m/%Y') dispatched,s.checked_by, f.app_ref_no,CONCAT(a.work_force,' ',a.position) position,u.unique_key as appURN,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,j.job,ur.orgname,if(s.jobwork = 1 OR s.jobwork = 3,'Yes','No') works_with_adult,if(s.jobwork = 2 OR s.jobwork = 3,'Yes','No') works_with_children,a.application_id from applications a inner join organisation o on a.org_id=o.org_id INNER JOIN company c ON o.company_id=c.company_id inner join sectionx s on a.application_id = s.application_id left join form_status f on {JOINEXPR} inner join app_person_name apnf on a.application_id = apnf.application_id inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4 inner join app_person_name apns on a.application_id = apns.application_id inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3  inner join reqdocs rqd on a.application_id = rqd.app_id inner join users u on rqd.user_id = u.user_id left outer join user_registration ur on u.username = ur.username left outer join jobs j on ur.job_role = j.sno  where o.company_id in ($listComp) and {EBULKCONDITION} and a.cancelled<>'Y' and f.dCrbDate is not NULL and f.rRDate is NULL $atprogresswithcrb";

if(!empty($orgId))
$commonQuery.=" and o.org_id='$orgId'";

      $joinexpr=" f.app_ref_no=s.app_ref_no";
      $ebulkconfition=" a.ebulkapp <> 'Y'";
      $content_var=array("{JOINEXPR}","{EBULKCONDITION}");
      $content_value=array($joinexpr,$ebulkconfition);
      $pquery=str_replace($content_var,$content_value,$commonQuery);
     
      #Query for Ebulk applications - Not printed
      $joinexpr=" s.application_id = f.application_id";
      $ebulkconfition=" a.ebulkapp = 'Y'";
      $content_var=array("{JOINEXPR}","{EBULKCONDITION}");
      $content_value=array($joinexpr,$ebulkconfition);
      $equery=str_replace($content_var,$content_value,$commonQuery);
      $query= $pquery." UNION ".$equery." order by fsname" ;


      $this->query_to_table_source($query,$qTitles);
    }
    
    function getAppNotCompleted($orgId,$twentydayQuery,$listComp)
    {
        $qTitles = array(
            'tableCaption' => 'Application Not Completed', 'exportTypes' => 'xls',
            'application_id' => array('alias' => 'Application Id', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'oname' => array('alias' => 'Organisation Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'deptname' => array('alias' => 'Dept. Name', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'date_of_submission' => array('alias' => 'Date Of Submission', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'no_of_days_since_submitted' => array('alias' => 'No. Days Since submission', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'app_name' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'position' => array('alias' => 'Position applied for', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'orgname' => array('alias' => 'Actual Organisation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'deptname'=>array('alias'=>'Dept. Name','type'=>'string','visible'=>'0','dontSum'=>'1'),
            'job' => array('alias' => 'Job Title', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            //'chkBox' => array('alias' => 'Choose', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
            'personalNumber' => array('alias' => 'Personnel Number', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'surreyCostCentre' => array('alias' => 'Cost Centre Number', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'appURN' => array('alias' => 'Application URN', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'Surname' => array('alias' => 'Surname', 'type' => 'string', 'visible' => '0', 'dontSum' => '1')
        );
//
//        $inputField1 = "CONCAT('<input type=\"checkbox\" name=\"checkBox[]\" id=\"checkBox_',a.application_id,'\" value=\"',a.application_id,'_',u.user_id,'\" onclick=\"uncheck_other(this.id); showCancelButton();\">') as chkBox";
//        $inputField2 = "CONCAT('<input type=\"checkbox\" name=\"checkBoxmsg[]\" id=\"checkBoxmsg_',a.application_id,'\" value=\"',a.application_id,'_',u.user_id,'_',CONCAT_WS(' ' ,pnf.name, pns.name),'\" onclick=\"uncheck_other(this.id); showCancelButton();\">') as chkBox_msg";

         $query = "select straight_join upper(concat(u.name, ' ' , u.surname)) app_name,o.name oname,u.unique_key as appURN,j.job,ur.orgname,c.name deptname
              from users u
              inner join org_users ou ON u.user_id = ou.user_id 
              inner join organisation o on ou.org_id = o.org_id
              INNER JOIN company c ON c.company_id = o.company_id
              LEFT JOIN reqdocs r on u.user_id = r.user_id
              left outer join user_registration ur on u.username = ur.username left outer join jobs j on ur.job_role = j.sno";

        $query.= " where o.company_id in ($listComp) AND u.active = 'Y' AND r.id IS NULL $twentydayQuery ";

    if(!empty($orgId))
    $query.=" and o.org_id='$orgId'";
   

    $this->query_to_table_source($query,$qTitles);
    }

    function getAppWorkingFromHome($verifyWorkingfromhome,$orgId,$commonQuerysubmitted,$listComp)
    {
        $qTitles = array(
            'tableCaption'=>'Applicants working from home','exportTypes'=>'xls',
	        'fsname'=>array('alias'=>'Applicant Name','type'=>'string','visible'=>'1','dontSum'=>'1'),
	        'dob'=>array('alias'=>'DOB','type'=>'string','visible'=>'1','dontSum'=>'1'),
                'deptname'=>array('alias'=>'Dept. Name','type'=>'string','visible'=>'1','dontSum'=>'1'),
                'jobname' => array('alias' => 'Job Title', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                'regorg' => array('alias' => 'Actual Organisation', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                'working_from' => array('alias' => 'Working from home', 'type' => 'string', 'parseHTML' => '1', 'visible' => '1', 'dontSum' => '0'));

$query = "select CONCAT_WS(' ', upper(pnf.name) ,upper(pns.name) ) AS fsname,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,c.name deptname,j.job jobname, ur.orgname regorg, $verifyWorkingfromhome as working_from from (applications a ,sectionx s,organisation b, company c, jobs j, reqdocs rd, users u, user_registration ur, app_person_name apnf, person_names pnf, app_person_name apns, person_names pns)
                LEFT JOIN log_work_at_home_modified lwahm ON a.application_id = lwahm.application_id
                where a.application_id = apnf.application_id and apnf.name_id = pnf.name_id and pnf.name_type_id = 4 and a.application_id = apns.application_id and apns.name_id = pns.name_id and pns.name_type_id = 3 and a.application_id=s.application_id and a.good_to_print ='S' and a.cnt_sign_local='N' and b.activated='Y' and s.workingathomeaddress='Y' and lwahm.application_id IS NULL and
                a.org_id=b.org_id  AND a.application_id=rd.app_id  AND rd.user_id=u.user_id AND u.username=ur.username  AND ur.job_role=j.sno AND c.company_id=b.company_id $commonQuerysubmitted";

if(!empty($orgId))
           $query.=" and b.activated='Y' and b.org_id='$orgId'";
        else
           $query.=" and b.company_id in ($listComp)  ";

        
      $query.=" order by fsname" ;


     $this->query_to_table_source($query,$qTitles);
    }
}

?>
