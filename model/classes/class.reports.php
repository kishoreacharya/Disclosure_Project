<?php

class Reports extends DBGrid {

    public $CURRENT_SURNAME = 3;
    public $CURRENT_FORNAME = 4;    
    public $listComp = 0;

    function __construct() {
        parent::__construct();
        $this->listComp = $this->getChildCompanies($_SESSION['company_id_C']);
        return true;
    }

    /*
     * Function to fetch Total Applications Entered Report
     */

    public function getTotalAppEnterReport($report_type, $formstatus, $sectionXVeirfied, $sqlSelectq, $orgId=null,$appstatusfield,$sectionxtime_period) {

        $qTitles =
                array('tableCaption' => 'Total Applications Entered', 'exportTypes' => 'xls',
                    'app_ref_no' => array('alias' => 'Form Reference Number', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'fsname' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
                    'submitdate' => array('alias' => 'Submit Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'position' => array('alias' => 'Position', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'dob' => array('alias' => 'Date of Birth', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'volunteer' => array('alias' => 'Volunteer', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'cancelled' => array('alias' => 'Cancel', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'application_id' => array('alias' => 'Unique ID', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'application_status' => array('alias' => 'Status', 'type' => 'string', 'visible' => '1','parseHTML' => '1', 'dontSum' => '1'),
                    'bandname' => array('alias' => 'Organisation Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'compname' => array('alias' => 'Department Name', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'orgType' => array('alias' => 'Organisation Type', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'orgname' => array('alias' => 'Actual Organisation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'job' => array('alias' => 'Job Title', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'works_with_children' => array('alias' => 'Works with Children', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'works_with_adult' => array('alias' => 'Works with '.VG_FULL, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'discType' => array('alias' => 'Level of Disclosure', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'psdate' => array('alias' => 'Processed & Sent On Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'dispatched' => array('alias' => 'Dispatch to '.DBS, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'result_date' => array('alias' => 'Result Received On', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'certno' => array('alias' => 'Certificate Number', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'appURN' => array('alias' => 'Application URN', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'sectionXVeirfied' => array('alias' => 'ID Verified', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'));

        $commonQuery = '';
          $commonQuery = "select STRAIGHT_JOIN $formstatus,o.name bandname,c.name compname, CONCAT(a.work_force,' ',a.position) position,upper(s.discType) discType,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,if(s.remuneration = 'N','Yes','No') as volunteer,from_unixtime(a.submit_time,'%d/%m/%Y') submitdate,f.app_ref_no,if(f.`p&sDate`>0, from_unixtime(f.`p&sDate`,'%d/%m/%Y'), NULL)  AS psdate,if(f.dCrbDate>0, from_unixtime(f.dCrbDate,'%d/%m/%Y'), NULL) AS dispatched,if(f.rRdate>0, from_unixtime(f.rRdate,'%d/%m/%Y'),NULL) AS result_date,if(f.certno <> '' and f.certno is NOT NULL,f.certno,'-') as certno,u.unique_key as appURN,j.job,ur.orgname,if(s.jobwork = 1 OR s.jobwork = 3,'Yes','No') works_with_adult,if(s.jobwork = 2 OR s.jobwork = 3,'Yes','No') works_with_children,$sectionXVeirfied,$appstatusfield

        from applications a inner join organisation o on a.org_id=o.org_id INNER JOIN company c ON c.company_id =  o.company_id inner join sectionx s on a.application_id = s.application_id left join form_status f on {JOINEXPR} inner join app_person_name apnf on a.application_id = apnf.application_id inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4 inner join app_person_name apns on a.application_id = apns.application_id inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3 inner join reqdocs rqd on a.application_id = rqd.app_id inner join users u on rqd.user_id = u.user_id left outer join user_registration ur on u.username = ur.username left outer join jobs j on ur.job_role = j.sno   where o.company_id in ($this->listComp) and {EBULKCONDITION} and a.cancelled<>'Y' and  a.sectionx_complete_time is not NULL $sectionxtime_period";


        if (!empty($orgId))
            $commonQuery.=" and o.org_id='$orgId'";


        #Query for Ebulk applications - Not printed
        $joinexpr = " s.application_id = f.application_id";
        $ebulkconfition = " a.ebulkapp = 'Y'";
        $content_var = array("{JOINEXPR}", "{EBULKCONDITION}");
        $content_value = array($joinexpr, $ebulkconfition);
        $equery = str_replace($content_var, $content_value, $commonQuery);
        $query = $equery . $sqlSelectq;
        
        $this->query_to_table_source($query, $qTitles);
    }

    /*
     * Function to fetch Total Applications Cancelled Report
     */

    public function getTotalCanAppReport($report_type, $sqlSelectq, $sectionXVeirfied, $orgId=null,$app_cancelled_period) {

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
                    'compname' => array('alias' => 'Department Name', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'orgType' => array('alias' => 'Organisation Type', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'orgname' => array('alias' => 'Actual Organisation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'job' => array('alias' => 'Job Title', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'works_with_children' => array('alias' => 'Works with Children', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'works_with_adult' => array('alias' => 'Works with '.VG_FULL, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'discType' => array('alias' => 'Level of Disclosure', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'psdate' => array('alias' => 'Processed & Sent On Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'reason' => array('alias' => 'Reason for Cancellation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'appURN' => array('alias' => 'Application URN', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'sectionXVeirfied' => array('alias' => 'Status', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'));

        $commonQuery = '';
        $commonQuery = "select STRAIGHT_JOIN CONCAT_WS(' ',upper(pnf.name) ,upper(pns.name) ) AS fsname,o.name bandname, c.name compname, f.app_ref_no,CONCAT(a.work_force,' ',a.position) position,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,s.checked_by,from_unixtime(a.cancelled_date,'%d/%m/%Y') cancelledon,canc.comment as reason,u.unique_key as appURN,j.job,ur.orgname,if(s.jobwork = 1 OR s.jobwork = 3,'Yes','No') works_with_adult,if(s.jobwork = 2 OR s.jobwork = 3,'Yes','No') works_with_children,$sectionXVeirfied

        from applications a inner join organisation o on a.org_id=o.org_id INNER JOIN company c ON c.company_id = o.company_id inner join sectionx s on a.application_id = s.application_id left join form_status f on {JOINEXPR} inner join app_person_name apnf on a.application_id = apnf.application_id inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4 inner join app_person_name apns on a.application_id = apns.application_id inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3 LEFT JOIN cancellation_reason canc ON canc.application_id = a.application_id inner join reqdocs rqd on a.application_id = rqd.app_id inner join users u on rqd.user_id = u.user_id left outer join user_registration ur on u.username = ur.username left outer join jobs j on ur.job_role = j.sno where o.company_id in ($this->listComp) and {EBULKCONDITION} and a.cancelled='Y' $app_cancelled_period";


        if (!empty($orgId))
            $commonQuery.=" and o.org_id='$orgId'";

        #Query for Ebulk applications - Not printed
        $joinexpr = " s.application_id = f.application_id";
        $ebulkconfition = " a.ebulkapp = 'Y'";
        $content_var = array("{JOINEXPR}", "{EBULKCONDITION}");
        $content_value = array($joinexpr, $ebulkconfition);
        $equery = str_replace($content_var, $content_value, $commonQuery);
        $query = $equery . $sqlSelectq;

        $this->query_to_table_source($query, $qTitles);
    }

    /*
     * Function to fetch Applications Not Completed Report
     */

    public function getAppNotCompleted($report_type, $sqlSelectq, $orgId=null,$awiting_app_info_period) {

        $qTitles =
                array('tableCaption' => 'Application Not Completed', 'exportTypes' => 'xls',
                    'application_id' => array('alias' => 'Application Id', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'oname' => array('alias' => 'Organisation Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'compname' => array('alias' => 'Department Name', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'date_of_submission' => array('alias' => 'Date Of Submission', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'no_of_days_since_submitted' => array('alias' => 'No. Days Since submission', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'orgname' => array('alias' => 'Actual Organisation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'job' => array('alias' => 'Job Title', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'app_name' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'position' => array('alias' => 'Position applied for', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    //'chkBox' => array('alias' => 'Choose', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
                    'personalNumber' => array('alias' => 'Personnel Number', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'surreyCostCentre' => array('alias' => 'Cost Centre Number', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'appURN' => array('alias' => 'Application URN', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'Surname' => array('alias' => 'Surname', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'));

        //$inputField = "CONCAT('<input type=\"checkbox\" name=\"checkBox[]\" id=\"checkBox[]\" value=\"',a.application_id,'_',u.user_id,'\" onclick=\"showCancelButton();\">') as chkBox";
//        $query = "select straight_join concat(pnf.name, ' ' , pns.name) app_name,o.name oname,u.unique_key as appURN, a.position
//              from applications a
//              inner join organisation o on a.org_id = o.org_id and a.cancelled <> 'Y'
//              INNER JOIN company c ON c.company_id = o.company_id
//              INNER JOIN sectionx s on a.application_id = s.application_id
//              inner join app_person_name apnf on a.application_id = apnf.application_id
//              inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4
//              inner join app_person_name apns on a.application_id = apns.application_id
//              inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3
//              inner join reqdocs r on a.application_id = r.app_id left join users u on r.username = u.username";
        $query = "select straight_join upper(concat(u.name, ' ' , u.surname)) app_name,o.name oname, c.name compname, u.unique_key as appURN,j.job,ur.orgname
              from users u
              inner join org_users ou ON u.user_id = ou.user_id 
              inner join organisation o on ou.org_id = o.org_id
              INNER JOIN company c ON c.company_id = o.company_id
                INNER JOIN lo_users lu ON u.user_id = lu.user_id
              LEFT JOIN reqdocs r on u.user_id = r.user_id
              left outer join user_registration ur on u.username = ur.username left outer join jobs j on ur.job_role = j.sno";


        $query.= " where o.company_id in ($this->listComp) AND u.active = 'Y' AND r.id IS NULL $awiting_app_info_period";


        if (!empty($orgId))
            $query.=" AND ou.org_id='" . $orgId . "'";

        if (!empty($sqlSelectq))
            $query.=$sqlSelectq;



        $this->query_to_table_source($query, $qTitles);
    }

    public function getResultReceived_before17($report_type, $formstatus, $orgId=null, $resultpopup, $resultpopupprinted,$appstatusfield,$resultreceived_period) {
        $qTitles =
                array('tableCaption' => 'Result Received', 'exportTypes' => 'xls',
                    'fsname' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
                    'bandname' => array('alias' => 'Organisation Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'compname' => array('alias' => 'Department Name', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'orgType' => array('alias' => 'Organisation Type', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'appURN' => array('alias' => 'Application URN', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'position' => array('alias' => 'Position', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'discType' => array('alias' => 'Level of Disclosure', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'checked_by' => array('alias' => 'ID Checked By', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'dob' => array('alias' => 'Date of Birth', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'submitdate' => array('alias' => 'Submit Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'orgname' => array('alias' => 'Actual Organisation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    //'job' => array('alias' => 'Job Title', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'works_with_children' => array('alias' => 'Works with Children', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'works_with_adult' => array('alias' => 'Works with '.VG_FULL, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'app_ref_no' => array('alias' => 'Form Reference Number', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'psdate' => array('alias' => 'Processed & Sent On Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'dispatched' => array('alias' => 'Dispatch to '.DBS, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'result_date' => array('alias' => 'Result Received On', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'application_id' => array('alias' => 'Unique ID', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'application_status' => array('alias' => 'Certificate received', 'type' => 'string', 'visible' => '1','parseHTML' => '1', 'dontSum' => '1'),
                    'certno' => array('alias' => 'Certificate Number', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'));
    
             
            
        $commonQuery = '';

        $commonQuery = "select $formstatus,o.name bandname,c.name compname,CONCAT(a.work_force,' ',a.position) position,upper(s.discType) discType,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,s.checked_by,ur.orgname,if(s.jobwork = 1 OR s.jobwork = 3,'Yes','No') works_with_adult,if(s.jobwork = 2 OR s.jobwork = 3,'Yes','No') works_with_children,from_unixtime(a.submit_time,'%d/%m/%Y') submitdate,f.app_ref_no,if(f.`p&sDate`>0, from_unixtime(f.`p&sDate`,'%d/%m/%Y'), NULL)  AS psdate,if(f.dCrbDate>0, from_unixtime(f.dCrbDate,'%d/%m/%Y'), NULL) AS dispatched,if(f.rRdate>0, from_unixtime(f.rRdate,'%d/%m/%Y'),NULL) AS result_date,if(f.certno='','-',f.certno) as certno,u.unique_key as appURN,$appstatusfield from applications a inner join organisation o on a.org_id=o.org_id INNER JOIN company c ON c.company_id = o.company_id inner join sectionx s on a.application_id = s.application_id left join form_status f on {JOINEXPR} inner join app_person_name apnf on a.application_id = apnf.application_id inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4 inner join app_person_name apns on a.application_id = apns.application_id inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3 inner join reqdocs rqd on a.application_id = rqd.app_id inner join users u on rqd.user_id = u.user_id left outer join user_registration ur on u.username = ur.username left outer join jobs j on ur.job_role = j.sno where o.company_id in ($this->listComp) AND f.dCrbDate is not NULL AND f.rRdate is not NULL and a.cancelled <> 'Y' and f.rRdate <= ".PDFRESULT_DATE." $resultreceived_period and {EBULKCONDITION} ";

        if (!empty($orgId))
            $commonQuery.=" and o.org_id='$orgId'";


        #Query for Ebulk applications - Not printed
        $joinexpr = " s.application_id = f.application_id";
        $ebulkconfition = " a.ebulkapp = 'Y'";
        $content_var = array("{JOINEXPR}", "{EBULKCONDITION}");
        $content_value = array($joinexpr, $ebulkconfition);
        $equery = str_replace($content_var, $content_value, $commonQuery);
        $query = $equery;
       $query .= " order by f.rRdate DESC ,fsname ASC";
        $this->query_to_table_source($query, $qTitles);
    }
    
     public function getResultReceived_after17($report_type, $formstatus, $orgId=null, $resultpopup, $resultpopupprinted,$appstatusfield,$resultreceived_period) {
        $qTitles =
                array('tableCaption' => 'Result Received', 'exportTypes' => 'xls',
                    'fsname' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
                    'bandname' => array('alias' => 'Organisation Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'compname' => array('alias' => 'Department Name', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'orgType' => array('alias' => 'Organisation Type', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'appURN' => array('alias' => 'Application URN', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'position' => array('alias' => 'Position', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'discType' => array('alias' => 'Level of Disclosure', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'checked_by' => array('alias' => 'ID Checked By', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'dob' => array('alias' => 'Date of Birth', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'volunteer' => array('alias' => 'Volunteer', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'submitdate' => array('alias' => 'Submit Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'orgname' => array('alias' => 'Actual Organisation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    //'job' => array('alias' => 'Job Title', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'works_with_children' => array('alias' => 'Works with Children', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'works_with_adult' => array('alias' => 'Works with '.VG_FULL, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'app_ref_no' => array('alias' => 'Form Reference Number', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'psdate' => array('alias' => 'Processed & Sent On Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'dispatched' => array('alias' => 'Dispatch to '.DBS, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'result_date' => array('alias' => 'Result Received On', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'application_id' => array('alias' => 'Unique ID', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'application_status' => array('alias' => 'Certificate received', 'type' => 'string', 'visible' => '1','parseHTML' => '1', 'dontSum' => '1'),
                    'certno' => array('alias' => 'Certificate Number', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'));
        ;

             $strVerifyCert = "";
             
            if(VERIFY_CERTIFICATE != 'N')
            {
                $qTitles['result_date1'] =array('alias' => 'Applicant cert seen?', 'type' => 'string', 'parseHTML' => '1', 'visible' => '1', 'dontSum' => '0');
                $strVerifyCert = " ,if(a.ebulkapp='Y',$resultpopup,$resultpopupprinted)  AS result_date1 ";
            }
        $commonQuery = '';

        $commonQuery = "select $formstatus,o.name bandname,c.name compname,CONCAT(a.work_force,' ',a.position) position,upper(s.discType) discType,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,if(s.remuneration = 'N','Yes','No') as volunteer,s.checked_by,ur.orgname,if(s.jobwork = 1 OR s.jobwork = 3,'Yes','No') works_with_adult,if(s.jobwork = 2 OR s.jobwork = 3,'Yes','No') works_with_children,from_unixtime(a.submit_time,'%d/%m/%Y') submitdate,f.app_ref_no,if(f.`p&sDate`>0, from_unixtime(f.`p&sDate`,'%d/%m/%Y'), NULL)  AS psdate,if(f.dCrbDate>0, from_unixtime(f.dCrbDate,'%d/%m/%Y'), NULL) AS dispatched,if(f.rRdate>0, from_unixtime(f.rRdate,'%d/%m/%Y'),NULL) AS result_date,if(f.certno='','-',f.certno) as certno,u.unique_key as appURN,$appstatusfield $strVerifyCert from applications a inner join organisation o on a.org_id=o.org_id INNER JOIN company c ON c.company_id = o.company_id inner join sectionx s on a.application_id = s.application_id left join form_status f on {JOINEXPR} inner join app_person_name apnf on a.application_id = apnf.application_id inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4 inner join app_person_name apns on a.application_id = apns.application_id inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3 inner join reqdocs rqd on a.application_id = rqd.app_id inner join users u on rqd.user_id = u.user_id left outer join user_registration ur on u.username = ur.username left outer join jobs j on ur.job_role = j.sno where o.company_id in ($this->listComp) AND f.dCrbDate is not NULL AND f.rRdate is not NULL and a.cancelled <> 'Y' AND f.rRdate > ".PDFRESULT_DATE." $resultreceived_period and {EBULKCONDITION} ";

        if (!empty($orgId))
            $commonQuery.=" and o.org_id='$orgId'";


        #Query for Ebulk applications - Not printed
        $joinexpr = " s.application_id = f.application_id";
        $ebulkconfition = " a.ebulkapp = 'Y'";
        $content_var = array("{JOINEXPR}", "{EBULKCONDITION}");
        $content_value = array($joinexpr, $ebulkconfition);
        $equery = str_replace($content_var, $content_value, $commonQuery);
        $query = $equery;
        $query .= " order by f.rRdate DESC ,fsname ASC";
        $this->query_to_table_source($query, $qTitles);
    }

    public function getLPF($report_type, $formstatus, $orgId,$atprogresswithcrb) {
        $qTitles =
                array('tableCaption' => 'At LPF (Local Police Force)', 'exportTypes' => 'xls',
                    'fsname' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
                    'bandname' => array('alias' => 'Organisation Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'compname' => array('alias' => 'Department Name', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'orgType' => array('alias' => 'Organisation Type', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'appURN' => array('alias' => 'Application URN', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'position' => array('alias' => 'Position', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'discType' => array('alias' => 'Level of Disclosure', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'checked_by' => array('alias' => 'ID Checked By', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'dob' => array('alias' => 'Date of Birth', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'orgname' => array('alias' => 'Actual Organisation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'job' => array('alias' => 'Job Title', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'works_with_children' => array('alias' => 'Works with Children', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'works_with_adult' => array('alias' => 'Works with '.VG_FULL, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'submitdate' => array('alias' => 'Submit Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'app_ref_no' => array('alias' => 'Form Reference Number', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'psdate' => array('alias' => 'Processed & Sent On Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'dispatched' => array('alias' => 'Dispatch to '.DBS, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'pnc_stage' => array('alias' => 'PNC Date', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'application_id' => array('alias' => 'Unique ID', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'result_date' => array('alias' => 'Result received Date', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'certno' => array('alias' => 'Certificate Number', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'application_status' => array('alias' => 'Status', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
        );
        ;

        $commonQuery = '';
        $commonQuery = "select $formstatus,o.name bandname,c.name compname,CONCAT(a.work_force,' ',a.position) position,

j.job,ur.orgname,if(s.jobwork = 1 OR s.jobwork = 3,'Yes','No') works_with_adult,if(s.jobwork = 2 OR s.jobwork = 3,'Yes','No') works_with_children,upper(s.discType) discType,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,s.checked_by,from_unixtime(a.submit_time,'%d/%m/%Y') submitdate,f.app_ref_no,if(f.`p&sDate`>0, from_unixtime(f.`p&sDate`,'%d/%m/%Y'), NULL)  AS psdate,if(f.dCrbDate>0, from_unixtime(f.dCrbDate,'%d/%m/%Y'), NULL) AS dispatched,if(f.rRdate>0, from_unixtime(f.rRdate,'%d/%m/%Y'),NULL) AS result_date,if(f.certno='','-',f.certno) as certno,from_unixtime(f.pnc_stage,'%d/%m/%Y') pnc_stage,u.unique_key as appURN from applications a inner join organisation o on a.org_id=o.org_id INNER JOIN company c ON c.company_id = o.company_id inner join sectionx s on a.application_id = s.application_id left join form_status f on {JOINEXPR} inner join app_person_name apnf on a.application_id = apnf.application_id inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4 inner join app_person_name apns on a.application_id = apns.application_id inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3 inner join reqdocs rqd on a.application_id = rqd.app_id inner join users u on rqd.user_id = u.user_id left outer join user_registration ur on u.username = ur.username left outer join jobs j on ur.job_role = j.sno where o.company_id in ($this->listComp) and a.cancelled<>'Y' and f.dCrbDate is not NULL and f.lpfdate=0 and f.rRdate is NULL and f.crb_system is NOT NULL and f.initial_check is NOT NULL and f.pnc_stage is NOT NULL and f.cert_dispatched IS NULL $atprogresswithcrb and {EBULKCONDITION} ";

        if (!empty($orgId))
            $commonQuery.=" and o.org_id='$orgId'";


        #Query for Ebulk applications - Not printed
        $joinexpr = " s.application_id = f.application_id";
        $ebulkconfition = " a.ebulkapp = 'Y'";
        $content_var = array("{JOINEXPR}", "{EBULKCONDITION}");
        $content_value = array($joinexpr, $ebulkconfition);
        $equery = str_replace($content_var, $content_value, $commonQuery);
        $query = $equery;
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
                    'compname' => array('alias' => 'Department Name', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'orgType' => array('alias' => 'Organisation Type', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'appURN' => array('alias' => 'Application URN', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'position' => array('alias' => 'Position', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'discType' => array('alias' => 'Level of Disclosure', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'checked_by' => array('alias' => 'ID Checked By', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'dob' => array('alias' => 'Date of Birth', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'orgname' => array('alias' => 'Actual Organisation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'job' => array('alias' => 'Job Title', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'works_with_children' => array('alias' => 'Works with Children', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'works_with_adult' => array('alias' => 'Works with '.VG_FULL, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
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

        $commonQuery = "select $formstatus,o.name bandname,c.name compname,CONCAT(a.work_force,' ',a.position) position,j.job,ur.orgname,if(s.jobwork = 1 OR s.jobwork = 3,'Yes','No') works_with_adult,if(s.jobwork = 2 OR s.jobwork = 3,'Yes','No') works_with_children,upper(s.discType) discType,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,s.checked_by,from_unixtime(a.submit_time,'%d/%m/%Y') submitdate,f.app_ref_no,if(f.`p&sDate`>0, from_unixtime(f.`p&sDate`,'%d/%m/%Y'), NULL)  AS psdate,if(f.dCrbDate>0, from_unixtime(f.dCrbDate,'%d/%m/%Y'), NULL) AS dispatched,if(f.rRdate>0, from_unixtime(f.rRdate,'%d/%m/%Y'),NULL) AS result_date,if(f.certno='','-',f.certno) as certno,from_unixtime(f.pnc_stage,'%d/%m/%Y') pnc_stage,u.unique_key as appURN from applications a inner join organisation o on a.org_id=o.org_id INNER JOIN company c ON c.company_id = o.company_id inner join sectionx s on a.application_id = s.application_id left join form_status f on {JOINEXPR} inner join app_person_name apnf on a.application_id = apnf.application_id inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4 inner join app_person_name apns on a.application_id = apns.application_id inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3 inner join reqdocs rqd on a.application_id = rqd.app_id inner join users u on rqd.user_id = u.user_id left outer join user_registration ur on u.username = ur.username left outer join jobs j on ur.job_role = j.sno where o.company_id in ($this->listComp)and f.dCrbDate is not NULL and f.lpfdate=0 and f.rRdate is NULL and f.pnc_stage is NULL and f.initial_check is NOT NULL and f.crb_system is NOT NULL and f.cert_dispatched IS NULL $atprogresswithcrb and {EBULKCONDITION} ";

        if (!empty($orgId))
            $commonQuery.=" and o.org_id='$orgId'";


        #Query for Ebulk applications - Not printed
        $joinexpr = " s.application_id = f.application_id";
        $ebulkconfition = " a.ebulkapp = 'Y'";
        $content_var = array("{JOINEXPR}", "{EBULKCONDITION}");
        $content_value = array($joinexpr, $ebulkconfition);
        $equery = str_replace($content_var, $content_value, $commonQuery);
        $query = $equery;
        $this->query_to_table_source($query, $qTitles);
    }

    public function getAwaitingIDCheck($report_type, $formstatus, $orgId,$awiting_idcheck_period) {
        $qTitles =
                array('tableCaption' => 'Awaiting ID Check', 'exportTypes' => 'xls',
                    'fsname' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
                    'bandname' => array('alias' => 'Organisation Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'compname' => array('alias' => 'Department Name', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'orgType' => array('alias' => 'Organisation Type', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'appURN' => array('alias' => 'Application URN', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'position' => array('alias' => 'Position', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'orgname' => array('alias' => 'Actual Organisation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'job' => array('alias' => 'Job Title', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'works_with_children' => array('alias' => 'Works with Children', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'works_with_adult' => array('alias' => 'Works with '.VG_FULL, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'discType' => array('alias' => 'Level of Disclosure', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'dob' => array('alias' => 'Date of Birth', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'application_id' => array('alias' => 'Unique ID', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'application_status' => array('alias' => 'Status', 'type' => 'string', 'visible' => '1', 'dontSum' => '1')
        );



        $commonQuery = '';

        $commonQuery = "select STRAIGHT_JOIN CONCAT_WS(' ', upper(pnf.name) , upper(pns.name) ) AS fsname ,o.name bandname,c.name compname,CONCAT(a.work_force,' ',a.position) position,j.job,ur.orgname,if(s.jobwork = 1 OR s.jobwork = 3,'Yes','No') works_with_adult,if(s.jobwork = 2 OR s.jobwork = 3,'Yes','No') works_with_children,upper(s.discType) discType,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,u.unique_key as appURN from applications a inner join organisation o on a.org_id=o.org_id INNER JOIN company c ON c.company_id = o.company_id inner join sectionx s on a.application_id = s.application_id  inner join app_person_name apnf on a.application_id = apnf.application_id inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4 inner join app_person_name apns on a.application_id = apns.application_id inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3 inner join reqdocs rqd on a.application_id = rqd.app_id inner join users u on rqd.user_id = u.user_id left outer join user_registration ur on u.username = ur.username left outer join jobs j on ur.job_role = j.sno where o.company_id in ($this->listComp) and a.submit_time is NOT NULL and a.sectionx_complete_time is NULL and a.cancelled <> 'Y' $awiting_idcheck_period ";

        if (!empty($orgId))
            $commonQuery.=" and o.org_id='$orgId'";



        $query = $commonQuery;

#Query for Ebulk applications - Not printed

        $this->query_to_table_source($query, $qTitles);
    }

    public function getAwaitingAppInfo($report_type, $formstatus, $orgId,$awiting_app_info_period) {
        $qTitles =
                array('tableCaption' => 'Awaiting Applicant Information', 'exportTypes' => 'xls',
                    'fsname' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
                    'bandname' => array('alias' => 'Organisation Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'compname' => array('alias' => 'Department Name', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'orgname' => array('alias' => 'Actual Organisation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'job' => array('alias' => 'Job Title', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'orgType' => array('alias' => 'Organisation Type', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'appURN' => array('alias' => 'Application URN', 'type' => 'string', 'visible' => '2', 'dontSum' => '1'),
                    'application_status' => array('alias' => 'Status', 'type' => 'string', 'visible' => '1', 'dontSum' => '1')
        );


        $query = "SELECT STRAIGHT_JOIN
                               CONCAT_WS(' ', upper(ur.firstname) , upper(ur.lastname) ) AS fsname ,upper(ur.orgname) bandname,c.name compname,j.job,ur.orgname,upper(ot.description) orgType,u.unique_key as appURN
                  FROM users u 
                  INNER JOIN org_users ou ON u.user_id = ou.user_id 
                  INNER JOIN organisation o on ou.org_id = o.org_id
                  LEFT OUTER JOIN user_registration ur ON u.unique_key = ur.username
                  LEFT OUTER JOIN jobs j on ur.job_role = j.sno
                  LEFT JOIN organisation_type ot ON ur.orgtype = ot.id
                  INNER JOIN lo_users lu ON u.user_id = lu.user_id 
                  INNER JOIN company c ON o.company_id = c.company_id 
                  WHERE 
                  u.access_level = 'cqcuser' and u.user_id NOT IN (SELECT user_id FROM reqdocs) AND u.active = 'Y' AND u.used = 'N' AND u.archived<>'Y' 
                  and o.activated='Y' and o.company_id in ($this->listComp) $awiting_app_info_period order by fsname";

        $this->query_to_table_source($query, $qTitles);
    }
    
    function getCanceledRegUser($report_type,$cancelledUser_period) {
        $qTitles =
                array('tableCaption' => 'Cancelled Registrations report', 'exportTypes' => 'xls',
                    'fsname' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
                    'bandname' => array('alias' => 'Organisation Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'compname' => array('alias' => 'Department Name', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'orgname' => array('alias' => 'Actual Organisation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'job' => array('alias' => 'Job Title', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'orgType' => array('alias' => 'Organisation Type', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'email' => array('alias' => 'Email', 'type' => 'string', 'visible' => '2', 'dontSum' => '1')
        );


        $query = "SELECT STRAIGHT_JOIN
                               CONCAT_WS(' ', upper(ur.firstname) , upper(ur.lastname) ) AS fsname ,upper(ur.orgname) bandname,c.name compname,ur.email ,upper(ot.description) orgType,j.job,ur.orgname
                  FROM  user_registration ur
                  INNER JOIN organisation_type ot ON ur.orgtype = ot.id
                  left outer join jobs j on ur.job_role = j.sno
                  left join organisation o on o.org_provides = j.org_provide_id
                  LEFT JOIN company c ON c.company_id = o.company_id
                  WHERE cancel_applicant = 'Y' and o.company_id in ($this->listComp) $cancelledUser_period order by fsname";
        
        $this->query_to_table_source($query, $qTitles);
    }
    
        function getConfirmationOfPayment($report_type,$sectionxtime_period) {
        $qTitles =
                array('tableCaption' => 'Confirmation of payment', 'exportTypes' => 'xls',
                    'app_ref_no' => array('alias' => 'Application Reference', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
                    'apcustrefnumber_cqc' => array('alias' => 'AP Customer Ref Number', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'customerrefnum' => array('alias' => 'Customer Ref Number', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'deptName' => array('alias' => 'Organisation Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'compname' => array('alias' => 'Department Name', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'appName' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'dob' => array('alias' => 'Date of Birth', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'volunteer' => array('alias' => 'Volunteer', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'dispatched' => array('alias' => 'Date submitted to DBS', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'result_date' => array('alias' => 'Result received Date', 'type' => 'string', 'visible' => '1', 'dontSum' => '1')
        );


        $query = "select s.app_ref_no,substring(bd.initiating_barcode,12,20) as apcustrefnumber_cqc,bd.application_ref_number as customerrefnum,o.name as deptName,c.name compname,CONCAT(ucase(pnf.name),' ',ucase(pns.name)) as appName,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,if(s.remuneration = 'N','Yes','No') as volunteer,if(f.dCrbDate>0, from_unixtime(f.dCrbDate,'%d/%m/%Y'), NULL) AS dispatched,if(f.rRdate>0, from_unixtime(f.rRdate,'%d/%m/%Y'),NULL) AS result_date FROM applications a
INNER JOIN sectionx s ON a.application_id = s.application_id
LEFT JOIN form_status f ON  s.application_id = f.application_id
inner join app_person_name apnf on a.application_id = apnf.application_id 
inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4 
inner join app_person_name apns on a.application_id = apns.application_id 
inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3
INNER JOIN organisation o ON a.org_id = o.org_id
INNER JOIN company c ON o.company_id = c.company_id
INNER JOIN barcode_detail bd ON a.application_id = bd.application_id
WHERE a.cancelled <> 'Y' and o.company_id in ($this->listComp) and  a.sectionx_complete_time is NOT NULL and s.checked_by = 'Post Office' $sectionxtime_period  group by bd.application_id order by f.rRdate desc";

        $this->query_to_table_source($query, $qTitles,true);
    }
    
     function getReleasedApplicant($report_type, $createddate_period) {
        $qTitles =
                array('tableCaption' => 'Released Applicants', 'exportTypes' => 'xls',
                    'appname' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'compname' => array('alias' => 'Department Name', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
                    'createdby' => array('alias' => 'Released By', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
                    'releaseddate' => array('alias' => 'Released Date', 'type' => 'string', 'visible' => '1', 'dontSum' => '1')
        );


        $query = "SELECT CONCAT(ucase(u.name), ' ',ucase(u.surname)) as appname,c.name compname,CONCAT(ucase(ad.name),' ',ucase(ad.surname)) as createdby,from_unixtime(lu.createdate,'%d/%m/%Y') as releaseddate
        from users u
        INNER JOIN lo_users lu ON u.user_id = lu.user_id
        INNER JOIN users ad on lu.created_by = ad.user_id
        INNER JOIN org_users ou ON u.user_id = ou.user_id
        INNER JOIN organisation o ON ou.org_id = o.org_id
        INNER JOIN user_registration ureg ON u.username = ureg.username 
        INNER JOIN company c on o.company_id = c.company_id 
        WHERE ureg.user_registration_type <> 'A' AND 
        u.active = 'Y' and o.company_id in ($this->listComp) $createddate_period ORDER BY createdate DESC";

        $this->query_to_table_source($query, $qTitles);
    }
    
     function getAppsAwaitCountersign($report_type, $appWithCounterSign) {
        $qTitles =
               array(
        'tableCaption'=>'Applications Awaiting Countersign','exportTypes'=>'xls',
        'fsname'=>array('alias'=>'Applicant Name','type'=>'string','visible'=>'1','parseHTML'=>'1','dontSum'=>'1'),
        'dob'=>array('alias'=>'Date Of Birth','type'=>'string','visible'=>'1','parseHTML'=>'1','dontSum'=>'1'),
        'workingathomeaddress'=>array('alias'=>'Applicants working from home','type'=>'string','visible'=>'1','parseHTML'=>'1','dontSum'=>'1'),            
        'deptname' => array('alias' => 'Dept. Name', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
        'bandname' => array('alias' => 'Branch', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'));


        $formstatus = "CONCAT('<a title=\"Applications Awaiting Countersign\" href=\"javascript:void(0);\" onClick=\"thickBoxLink(this, 800, 600); return false;\" value=\"index.php?accesstype=showApplicantDetails&application_id=',a.application_id,'&closetype=tb\" ', ' class=\"Verdana_10_b_blue\">',upper(pnf.name) ,' ', upper(pns.name),'</a>') AS fsname";
        
        $query = "select $formstatus,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,if(s.workingathomeaddress='Y','Yes','No') workingathomeaddress,c.name deptname,o.name bandname 
        from (applications a, organisation o, company c,sectionx s)        
        INNER JOIN app_person_name apnf ON a.application_id = apnf.application_id
        INNER JOIN person_names pnf ON apnf.name_id = pnf.name_id and pnf.name_type_id = 4
        INNER JOIN app_person_name apns ON a.application_id = apns.application_id
        INNER JOIN person_names pns ON apns.name_id = pns.name_id and pns.name_type_id = 3 
        LEFT JOIN log_work_at_home_modified lwahm ON a.application_id = lwahm.application_id
        where 
        a.org_id=o.org_id and o.activated='Y' and a.good_to_print='S' and a.cnt_sign_local='N'  and a.address_check_good='Y'
        and c.company_id=o.company_id and a.application_id = s.application_id and (s.workingathomeaddress = 'N' or lwahm.application_id IS NOT NULL) 
        and o.company_id in ($this->listComp) $appWithCounterSign ORDER BY a.submit_time DESC";

        $this->query_to_table_source($query, $qTitles);
    }

    /*
     * Function to get All Counts
     */

    function getAwaitingAppInfoCount($orgId=NULL, $getType=NULL,$fromdate,$todate)
    {
        if (isset($fromdate) && !empty($fromdate) && !empty($todate)) {
            if ($todate > $fromdate)
                $toVal = $todate;#-(60*60*24);
            else
                $toVal = $todate;

            $fromVal = $fromdate; #+(60*60*24);
             $awiting_app_info_period=" and lu.createdate between $fromVal and $toVal";
            $dif = $todate - $fromdate;
        }
        else if (!empty($fromdate)) {
           $awiting_app_info_period=" and lu.createdate >= $fromdate";
            $todate = "";
        }
        $query = "SELECT count(u.user_id) as total
                  FROM 
                  users u 
                  INNER JOIN org_users ou ON u.user_id = ou.user_id 
                  INNER JOIN organisation o on ou.org_id = o.org_id
                  INNER JOIN user_registration ur ON u.unique_key = ur.username                                    
                  INNER JOIN lo_users lu ON u.user_id = lu.user_id 
                  INNER join company c on o.company_id = c.company_id 
                  LEFT OUTER JOIN jobs j on ur.job_role = j.sno 
                  LEFT JOIN organisation_type ot ON ur.orgtype = ot.id
                  WHERE 
                  u.access_level = 'cqcuser' and u.user_id NOT IN (SELECT user_id FROM reqdocs) AND u.active = 'Y' AND u.used = 'N' AND u.archived<>'Y' 
        and o.activated='Y' and o.company_id in ($this->listComp) $awiting_app_info_period";
        
         if (!empty($orgId))
            $query .= " AND ou.org_id='" . $orgId . "'";

        $arrayCounts = $this->getDBRecords($query);

        return $arrayCounts;
    }
    
    
    public function getAllReportCount($orgId=NULL, $getType=NULL,$fromdate,$todate) {
        
          if (isset($fromdate) && !empty($fromdate) && !empty($todate)) {
            if ($todate > $fromdate)
                $toVal = $todate;#-(60*60*24);
            else
                $toVal = $todate;

            $fromVal = $fromdate; #+(60*60*24);
            $commonQuerysubmitted_period.=" and a.submit_time between $fromVal and $toVal";
            $app_entered_period.=" and a.sectionx_complete_time between $fromVal and $toVal";
            $app_cancelled_period.=" and a.cancelled_date between $fromVal and $toVal";
            $resultreceived_period .=" and f.rRDate between $fromVal and $toVal";
            $atprogresswithcrb_period.=" and f.dCrbDate between $fromVal and $toVal";
            $einvitationquery_period=" and lo_users.createdate between $fromVal and $toVal";
            $route_period=" and evd.submit_time between $fromVal and $toVal";
            $awiting_app_info_period=" and lu.createdate between $fromVal and $toVal";
            $dif = $todate - $fromdate;
        }
        else if (!empty($fromdate)) {
            $commonQuerysubmitted_period.=" and a.submit_time > $fromdate";
            $app_entered_period.=" and a.sectionx_complete_time > $fromdate";
            $app_cancelled_period.=" and a.cancelled_date >= $fromdate";
            $resultreceived_period.=" and f.rRDate > $fromdate";
            $atprogresswithcrb_period.=" and f.dCrbDate > $fromdate";
            $einvitationquery_period=" and lo_users.createdate >= $fromdate";
            $route_period=" and evd.submit_time >= $fromdate";
            $awiting_app_info_period=" and lu.createdate >= $fromdate";
            $todate = "";
        }

        if (empty($getType) && $getType != "AwaitAppInfo") {
            $commonQuery = "select

		 sum(if(a.application_id $commonQuerysubmitted_period,1,0)) total_applications,

		 sum(if(a.cancelled<>'Y' and a.sectionx_complete_time is not NULL $app_entered_period,1,0)) app_entered,

		 sum(if(a.cancelled='Y'  $app_cancelled_period,1,0)) app_cancelled,
 
		 sum(if(f.application_id is NULL, 0, if(a.cancelled<>'Y' and f.dCrbDate is not NULL and f.rRDate is NULL $atprogresswithcrb_period,1,0))) total_app_inprogress_CRB,

		 sum(if(a.cancelled<>'Y' and a.sectionx_complete_time is not NULL and f.dCrbDate is NULL $app_entered_period,1,0)) total_app_not_inprogress_CRB,

		 sum(if(f.application_id is NULL, 0, if(a.cancelled<>'Y' and f.dCrbDate is not NULL and f.rRDate is not NULL AND f.rRDate <= ".PDFRESULT_DATE." $resultreceived_period,1,0))) total_app_resultrecevied_before17,
		 sum(if(f.application_id is NULL, 0, if(a.cancelled<>'Y' and f.dCrbDate is not NULL and f.rRDate is not NULL AND f.rRDate > ".PDFRESULT_DATE." $resultreceived_period,1,0))) total_app_resultrecevied_after17,

		 sum(if(f.application_id is NULL, 0, if(a.cancelled<>'Y' and f.dCrbDate is not NULL and f.lpfdate=0 and f.rRdate is NULL and f.crb_system is NOT NULL and f.initial_check is NOT NULL and f.pnc_stage is NOT NULL and f.cert_dispatched IS NULL $atprogresswithcrb_period,1,0))) total_app_at_lpf,

		 sum(if(f.application_id is NULL, 0, if(a.cancelled<>'Y' and f.`dCrbDate` IS NOT NULL and f.rRDate is NULL and f.crb_system is NOT NULL and f.initial_check is NOT NULL and f.pnc_stage is NULL and f.lpfdate=0 and f.cert_dispatched IS NULL $atprogresswithcrb_period,1,0))) total_app_at_pnc,

		 sum(if(a.cancelled<>'Y' and a.sectionx_complete_time is not NULL and a.submit_time is NULL $app_entered_period,1,0)) total_app_info

       from applications a inner join organisation o on a.org_id=o.org_id ";

            if (!empty($orgId))
                $commonQuery.=" and o.activated='Y' and o.org_id='$orgId'";
            else
                $commonQuery.=" and o.company_id in ($this->listComp) ";


            $commonQuery.=" inner join sectionx s on a.application_id = s.application_id and {EBULKCONDITION}  left join form_status f on {JOINEXPR} inner join reqdocs rqd on a.application_id = rqd.app_id inner join users u on rqd.user_id = u.user_id";

            #Query for Ebulk applications - Not printed
            $joinexpr = "s.application_id = f.application_id";
            $ebulkconfition = " a.ebulkapp = 'Y'";
            $content_var = array("{JOINEXPR}", "{EBULKCONDITION}");
            $content_value = array($joinexpr, $ebulkconfition);
            $equery = str_replace($content_var, $content_value, $commonQuery);
            $query1 = "select sum(total_applications) total_applications,sum(app_entered) app_entered,sum(app_cancelled) app_cancelled,sum(total_app_inprogress_CRB) total_app_inprogress_CRB,sum(total_app_not_inprogress_CRB) total_app_not_inprogress_CRB,sum(total_app_resultrecevied_before17) total_app_resultrecevied_before17,sum(total_app_resultrecevied_after17) total_app_resultrecevied_after17, sum(total_app_at_lpf) total_app_at_lpf,sum(total_app_at_pnc) total_app_at_pnc, sum(total_app_info)total_app_info ";
            $query = $query1 . " from (" .$equery . ") z";
 
            $arrayCounts = $this->getDBRecords($query);
        } else {
            $query = "SELECT STRAIGHT_JOIN
                               CONCAT_WS(' ', upper(ur.firstname) , upper(ur.lastname) ) AS fsname ,upper(ur.orgname) bandname,upper(ot.description) orgType,u.unique_key as appURN
                  FROM users u INNER JOIN org_users ou ON u.user_id = ou.user_id INNER JOIN organisation o on ou.org_id = o.org_id
                  INNER JOIN user_registration ur ON u.unique_key = ur.username
                  INNER JOIN organisation_type ot ON ur.orgtype = ot.id
                  INNER JOIN lo_users lu ON u.user_id = lu.user_id
                  WHERE u.user_id NOT IN (SELECT user_id FROM reqdocs) $awiting_app_info_period order by fsname";
      
            $result = $this->getDBRecords($query);
            $arrayCounts = count($result);
        }
        return $arrayCounts;
    }

    /*
     * Function to Application not completed count
     */

    public function getAppNotCompCount($orgId=NULL,$fromdate,$todate) {
      if (isset($fromdate) && !empty($fromdate) && !empty($todate)) {
            if ($todate > $fromdate)
                $toVal = $todate;#-(60*60*24);
            else
                $toVal = $todate;

            $fromVal = $fromdate; #+(60*60*24);
             $awiting_app_info_period=" and lu.createdate between $fromVal and $toVal";
            $dif = $todate - $fromdate;
        }
        else if (!empty($fromdate)) {
           $awiting_app_info_period=" and lu.createdate >= $fromdate";
            $todate = "";
        }
        $query = "select count(u.user_id) as total
              from users u
              inner join org_users ou ON u.user_id = ou.user_id 
              inner join organisation o on ou.org_id = o.org_id
              INNER JOIN company c ON c.company_id = o.company_id
              INNER JOIN lo_users lu ON u.user_id = lu.user_id
              LEFT JOIN reqdocs r on u.user_id = r.user_id";

        $query.= " where o.company_id in ($this->listComp) AND u.active = 'Y' AND r.id IS NULL $awiting_app_info_period ";

        if (!empty($orgId))
            $query .= " AND ou.org_id='" . $orgId . "'";

        $arrayCounts = $this->getDBRecords($query);

        return $arrayCounts;
    }

    /*
     * Function to Await ID Checks count
     */

    public function getAwaitIDCount($orgId=NULL,$fromdate,$todate) {
         if (isset($fromdate) && !empty($fromdate) && !empty($todate)) {
            if ($todate > $fromdate)
                $toVal = $todate;#-(60*60*24);
            else
                $toVal = $todate;

            $fromVal = $fromdate; #+(60*60*24);
             $awiting_idcheck_period=" and a.submit_time between $fromVal and $toVal";
            $dif = $todate - $fromdate;
        }
        else if (!empty($fromdate)) {
           $awiting_idcheck_period=" and a.submit_time>= $fromdate";
            $todate = "";
        }
        $aquery = "select count(a.application_id) as cnt from applications a inner join organisation o on a.org_id=o.org_id inner join sectionx s on a.application_id = s.application_id inner join app_person_name apnf on a.application_id = apnf.application_id
          inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4
          inner join app_person_name apns on a.application_id = apns.application_id
          inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3
          inner join reqdocs rqd on a.application_id = rqd.app_id inner join users u on rqd.user_id = u.user_id
          where a.submit_time is NOT NULL and a.sectionx_complete_time is NULL and a.cancelled <> 'Y' $awiting_idcheck_period";
        if (!empty($orgId))
            $aquery.=" and o.activated='Y' and o.org_id='$orgId'";
        else
            $aquery.=" and o.company_id in ($this->listComp)";

        $app_awaiting_count = $this->getDBRecords($aquery);
        return $app_awaiting_count;
    }
    
    function getCanceledUserCount($orgId=NULL,$fromdate,$todate)
    {
         if (isset($fromdate) && !empty($fromdate) && !empty($todate)) {
            if ($todate > $fromdate)
                $toVal = $todate;#-(60*60*24);
            else
                $toVal = $todate;

            $fromVal = $fromdate; #+(60*60*24);
             $cancelledUser_period=" and UNIX_TIMESTAMP(ur.created_on) between $fromVal and $toVal";
            $dif = $todate - $fromdate;
        }
        else if (!empty($fromdate)) {
           $cancelledUser_period=" and UNIX_TIMESTAMP(ur.created_on) >= $fromdate";
            $todate = "";
        }
        $aquery = "select count(ur.id) as cnt from user_registration ur  
            INNER JOIN organisation_type ot ON ur.orgtype = ot.id
        left outer join jobs j on ur.job_role = j.sno
                  left outer join organisation o on j.org_provide_id = o.org_provides
          where cancel_applicant = 'Y' and o.company_id in ($this->listComp) $cancelledUser_period";
        if (!empty($orgId))
            $aquery.=" and o.activated='Y' and o.org_id='$orgId'";
       

        $regUserCanceled = $this->getDBRecords($aquery);
        return $regUserCanceled;
    }
    function getConfirmationOfPaymentCount($orgId=NULL,$fromdate,$todate)
    {
        
         if (isset($fromdate) && !empty($fromdate) && !empty($todate)) {
            if ($todate > $fromdate)
                $toVal = $todate;#-(60*60*24);
            else
                $toVal = $todate;

            $fromVal = $fromdate; #+(60*60*24);
             $sectionxtime_period=" and a.sectionx_complete_time between $fromVal and $toVal";
            $dif = $todate - $fromdate;
        }
        else if (!empty($fromdate)) {
           $sectionxtime_period=" and a.sectionx_complete_time >= $fromdate";
            $todate = "";
        }
        $aquery = "select a.application_id FROM applications a 
INNER JOIN sectionx s ON a.application_id = s.application_id
LEFT JOIN form_status f ON  s.application_id = f.application_id
inner join app_person_name apnf on a.application_id = apnf.application_id 
inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4 
inner join app_person_name apns on a.application_id = apns.application_id 
inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3
INNER JOIN organisation o ON a.org_id = o.org_id
INNER JOIN company c ON o.company_id and c.company_id
INNER JOIN barcode_detail bd ON a.application_id = bd.application_id
WHERE a.cancelled <> 'Y' and o.company_id in ($this->listComp) and  a.sectionx_complete_time is NOT NULL and s.checked_by = 'Post Office' $sectionxtime_period ";

        if (!empty($orgId))
            $aquery.=" and o.activated='Y' and o.org_id='$orgId'";
        $aquery .= " group by bd.application_id ";
       

        $confirmationOfPaymentCount = $this->getDBRecords($aquery);
        return count($confirmationOfPaymentCount);
    }
    
        function getreleasedApplicantCount($orgId=NULL,$fromdate,$todate)
    {
             if (isset($fromdate) && !empty($fromdate) && !empty($todate)) {
            if ($todate > $fromdate)
                $toVal = $todate;#-(60*60*24);
            else
                $toVal = $todate;

            $fromVal = $fromdate; #+(60*60*24);
             $createddate_period=" and lu.createdate between $fromVal and $toVal";
            $dif = $todate - $fromdate;
        }
        else if (!empty($fromdate)) {
           $createddate_period=" and lu.createdate >= $fromdate";
            $todate = "";
        }
        $aquery = "SELECT count(u.user_id) as cnt from users u
INNER JOIN lo_users lu ON u.user_id = lu.user_id
INNER JOIN users ad on lu.created_by = ad.user_id
INNER JOIN org_users ou ON u.user_id = ou.user_id
INNER JOIN organisation o ON ou.org_id = o.org_id
INNER JOIN user_registration ureg ON u.username = ureg.username 
        INNER JOIN company c ON o.company_id = c.company_id
WHERE ureg.user_registration_type <>  'A' AND u.active = 'Y' and o.company_id in ($this->listComp) $createddate_period "; 
        $releasedApplicantCount = $this->getDBRecords($aquery);
        return $releasedApplicantCount;
    }
        function getAppsAwaitCountersignCount($orgId=NULL,$fromdate,$todate)
    {
             if (isset($fromdate) && !empty($fromdate) && !empty($todate)) {
            if ($todate > $fromdate)
                $toVal = $todate;#-(60*60*24);
            else
                $toVal = $todate;

            $fromVal = $fromdate; #+(60*60*24);
             $createddate_period=" and a.submit_time between $fromVal and $toVal";
            $dif = $todate - $fromdate;
        }
        else if (!empty($fromdate)) {
           $createddate_period=" and a.submit_time>= $fromdate";
            $todate = "";
        }
        $aquery = "SELECT count(a.application_id) as cnt
        FROM (applications a, sectionx s,organisation b, company c) 
        LEFT JOIN log_work_at_home_modified lwahm ON a.application_id = lwahm.application_id
        WHERE a.application_id = s.application_id AND  a.good_to_print = 'S' AND a.address_check_good='Y'  AND a.cnt_sign_local = 'N' AND s.checked_by <> '' AND b.activated='Y' AND a.org_id = b.org_id AND c.company_id = b.company_id AND (s.workingathomeaddress = 'N' OR lwahm.application_id IS NOT NULL)  AND b.company_id IN ($this->listComp) $createddate_period "; 
        $appsAwaitCountersignCount = $this->getDBRecords($aquery); 
        return $appsAwaitCountersignCount;
    }
    
    function getSpecifiedTime($dayBack)
    {
        $timedayback = time();
        $counter=1;

        while($counter <=$dayBack)
        {
                $oneDayBack=date("l",$timedayback-86400);
                $timedayback-=86400;
                $counter++;

        }
        return $timedayback;
    }
    
    function convertToTimestamp1($date)
    {    
      $date_array=explode("/",$date);
      $day=intval(trim($date_array[0]));
      $month=intval(trim($date_array[1]));
      $year=intval(trim($date_array[2]));
      //echo $day."-".$month."-".$year;
      return mktime(0,0,0,$month,$day,$year);
    }
    
    function convertToTimestamp2($date)
    {    
      $date_array=explode("/",$date);
      $day=intval(trim($date_array[0]));
      $month=intval(trim($date_array[1]));
      $year=intval(trim($date_array[2]));
      //echo $day."-".$month."-".$year;
      return mktime(23,59,59,$month,$day,$year);
    }
    
    public function getAdditionalNamesCount($orgId=NULL,$fromdate,$todate)
    {
          if (isset($fromdate) && !empty($fromdate) && !empty($todate)) {
            if ($todate > $fromdate)
                $toVal = $todate;#-(60*60*24);
            else
                $toVal = $todate;

            $fromVal = $fromdate; #+(60*60*24);
            $resultreceived_period .=" and f.rRDate between $fromVal and $toVal";        
            $dif = $todate - $fromdate;
        }
        else if (!empty($fromdate)) {           
            $resultreceived_period.=" and f.rRDate > $fromdate";
            $todate = "";
        }
        
        $sqlAdditionalNamesCount = " SELECT sum(if(f.application_id is NULL, 0, if(a.cancelled<>'Y' and f.dCrbDate is not NULL AND er.export_result_id IS NULL and f.rRDate is not NULL AND f.rRDate > ".PDFRESULT_DATE." $resultreceived_period,1,0))) totcount  from applications a inner join organisation o on a.org_id=o.org_id ";

            if (!empty($orgId))
                $sqlAdditionalNamesCount.=" and o.activated='Y' and o.org_id='$orgId'";
            else
                $sqlAdditionalNamesCount.=" and o.company_id in ($this->listComp) ";


            $sqlAdditionalNamesCount.=" inner join sectionx s on a.application_id = s.application_id 
                                               left join form_status f on s.application_id = f.application_id
                                               inner join reqdocs rqd on a.application_id = rqd.app_id 
                                               inner join users u on rqd.user_id = u.user_id
                                               LEFT JOIN export_report er ON a.application_id = er.application_id";
            
            $res = $this->getDBRecords($sqlAdditionalNamesCount);
           
            return $res[0]['totcount'];

    }
    public function getAdditionalNamesDetails($orgId=NULL,$fromdate,$todate,$appstatusfield)
    {
          if (isset($fromdate) && !empty($fromdate) && !empty($todate)) {
            if ($todate > $fromdate)
                $toVal = $todate;#-(60*60*24);
            else
                $toVal = $todate;

            $fromVal = $fromdate; #+(60*60*24);
            $resultreceived_period .=" and f.rRDate between $fromVal and $toVal";        
            $dif = $todate - $fromdate;
        }
        else if (!empty($fromdate)) {           
            $resultreceived_period.=" and f.rRDate > $fromdate";
            $todate = "";
        }
        
          $sqlAdditionalNamesDetails = " SELECT a.application_id,from_unixtime(a.release_date,'%d/%m/%Y') countersignedon,concat(pnf.name,' ',pns.name) as currentname,o.name bandname,c.name compname,CONCAT(a.work_force,' ',a.position) position,upper(s.discType) discType,concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob,s.checked_by,ur.orgname,if(s.jobwork = 1 OR s.jobwork = 3,'Yes','No') works_with_adult,if(s.jobwork = 2 OR s.jobwork = 3,'Yes','No') works_with_children,from_unixtime(a.submit_time,'%d/%m/%Y') submitdate,f.app_ref_no,if(f.`p&sDate`>0, from_unixtime(f.`p&sDate`,'%d/%m/%Y'), NULL)  AS psdate,if(f.dCrbDate>0, from_unixtime(f.dCrbDate,'%d/%m/%Y'), NULL) AS dispatched,if(f.rRdate>0, from_unixtime(f.rRdate,'%d/%m/%Y'),NULL) AS result_date,if(f.certno='','-',f.certno) as certno,u.unique_key as appURN,upper(ot.description) orgType,$appstatusfield from applications a inner join organisation o on a.org_id=o.org_id ";

            if (!empty($orgId))
                $sqlAdditionalNamesDetails.=" and o.activated='Y' and o.org_id='$orgId'";
            else
                $sqlAdditionalNamesDetails.=" and o.company_id in ($this->listComp) ";


            $sqlAdditionalNamesDetails.=" inner join sectionx s on a.application_id = s.application_id 
                                               left join form_status f on s.application_id = f.application_id
                                               inner join reqdocs rqd on a.application_id = rqd.app_id 
                                               inner join company c on o.company_id = c.company_id
                                               inner join users u on rqd.user_id = u.user_id
                                               inner join app_person_name apnf on a.application_id = apnf.application_id 
                                               inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4
                                               inner join app_person_name apns on a.application_id = apns.application_id 
                                               inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3                                               
                                               LEFT JOIN export_report er ON a.application_id = er.application_id
                                               left outer join user_registration ur on u.username = ur.username 
                                               left outer join jobs j on ur.job_role = j.sno
                                               LEFT JOIN organisation_type ot ON ur.orgtype = ot.id
                                               where
                                               a.cancelled<>'Y' and f.dCrbDate is not NULL AND er.export_result_id IS NULL and f.rRDate is not NULL AND f.rRDate > ".PDFRESULT_DATE." $resultreceived_period";
            
            $res = $this->getDBRecords($sqlAdditionalNamesDetails);
            $arrAdditionalName = array();
           for($i =0;$i < count($res) ;$i++)
           {
                  $sqlSelect = '';
                  $arrRes = array();
                  $DBSResNames = array();
                  $sqlSelect = "SELECT  pn.name,pn.name_type_id FROM applications a
                            inner join app_person_name apn on a.application_id = apn.application_id 
                            inner join person_names pn on apn.name_id = pn.name_id 
                            where a.application_id = ".$res[$i]['application_id']." and pn.name_type_id in (5,6,7,9,10) ORDER BY FIELD (pn.name_type_id,6,5,7,9,10),pn.name_id";
                   $arrRes = $this->getDBRecords($sqlSelect);
                  
                 $DBSsqlname = "SELECT Concat(erb.app_forname,' ',erb.app_surname) app_name,if(erbp.result_name<>'None Declared',erbp.result_name,' ') additional_names FROM ebulk_result_batch erb
                               INNER JOIN ebulk_result_batch_person_names erbp on erbp.rb_ref_no=erb.rb_app_ref_no
                               WHERE erb.app_frm_ref_no='".$res[$i]['app_ref_no']."'";
                  $DBSResNames = $this->getDBRecords($DBSsqlname);            

                  $arrAdditionalName[$i]['application_id'] = $res[$i]['application_id'];
                  $arrAdditionalName[$i]['currentname']  = $res[$i]['currentname'];
                  $arrAdditionalName[$i]['bandname']  = $res[$i]['bandname'];
                  $arrAdditionalName[$i]['compname']  = $res[$i]['compname'];
                  $arrAdditionalName[$i]['position']  = $res[$i]['position'];
                  $arrAdditionalName[$i]['discType']  = $res[$i]['discType'];
                  $arrAdditionalName[$i]['dob']  = $res[$i]['dob'];
                  $arrAdditionalName[$i]['checked_by']  = $res[$i]['checked_by'];
                  $arrAdditionalName[$i]['orgname']  = str_replace (",", " ", $res[$i]['orgname']);
                  $arrAdditionalName[$i]['works_with_adult']  = $res[$i]['works_with_adult'];
                  $arrAdditionalName[$i]['works_with_children']  = $res[$i]['works_with_children'];
                  $arrAdditionalName[$i]['submitdate']  = $res[$i]['submitdate'];
                  $arrAdditionalName[$i]['app_ref_no']  = $res[$i]['app_ref_no'];
                  $arrAdditionalName[$i]['countersignedon']  = $res[$i]['countersignedon'];
                  $arrAdditionalName[$i]['psdate']  = $res[$i]['psdate'];
                  $arrAdditionalName[$i]['dispatched']  = $res[$i]['dispatched'];
                  $arrAdditionalName[$i]['result_date']  = $res[$i]['result_date'];
                  $arrAdditionalName[$i]['certno']  = $res[$i]['certno'];
                  $arrAdditionalName[$i]['appURN']  = $res[$i]['appURN'];
                  $arrAdditionalName[$i]['application_status']  = $res[$i]['application_status'];
                  $arrAdditionalName[$i]['orgType']  = $res[$i]['orgType'];
                  $addMidCount = 1;
                  $addSurCount = 1;
                  $addForeCount = 1;
                  for($j =0;$j < count($arrRes) ;$j++)
                  {
                      if($arrRes[$j]['name_type_id'] == 5 || $arrRes[$j]['name_type_id'] == 6)
                      {
                           $arrAdditionalName[$i]['surname'.$addSurCount] = $arrRes[$j]['name'];
                           $addSurCount =$addSurCount + 1;
                      }
                       if($arrRes[$j]['name_type_id'] == 9 || $arrRes[$j]['name_type_id'] == 10)
                      {
                           $arrAdditionalName[$i]['middlename'.$addMidCount] = $arrRes[$j]['name'];
                           $addMidCount = $addMidCount + 1;
                      }
                      if($arrRes[$j]['name_type_id'] == 7)
                      {
                           $arrAdditionalName[$i]['forename'.$addForeCount] = $arrRes[$j]['name'];
                           $addForeCount = $addForeCount + 1;
                      }
                      
                     
                  }
                  
                  
            $arrAdditionalName[$i]['app_name'] = $DBSResNames[0]['app_name'];
            
            $DBSAddNames = 1;

            for ($k = 0; $k < count($DBSResNames); $k++) {
                $arrAdditionalName[$i]['additional_names' . $DBSAddNames] = $DBSResNames[$k]['additional_names'];
                $DBSAddNames = $DBSAddNames + 1;
            }
                  
           }

           return $arrAdditionalName;

    }
    
    public function getLastExportDetails($orgId)
    {
        $sql = "SELECT er.exported_datetime,concat(u.name,' ',u.surname) as exported_by FROM export_report er 
                  INNER JOIN applications a ON er.application_id = a.application_id
                  INNER JOIN users u ON er.exported_by = u.user_id
                  INNER JOIN organisation o ON  a.org_id=o.org_id where";
          if (!empty($orgId))
                $sql.="  o.activated='Y' and o.org_id='$orgId'";
            else
                $sql.="  o.company_id in ($this->listComp) ";
                $sql.=  " ORDER BY er.export_result_id DESC LIMIT 0 , 1";
                
               $res = $this->getDbRecords($sql);
               return $res;
    }
    
    public function setExportedResult($orgId,$fromdate,$todate,$username)
    {
        $sql = 'SELECT user_id from users where username = "'.$username.'"';
        $resUser = $this->getDBRecords($sql);
        $userId = $resUser[0]['user_id'];
         if (isset($fromdate) && !empty($fromdate) && !empty($todate)) {
            if ($todate > $fromdate)
                $toVal = $todate;#-(60*60*24);
            else
                $toVal = $todate;

            $fromVal = $fromdate; #+(60*60*24);
            $resultreceived_period .=" and f.rRDate between $fromVal and $toVal";        
            $dif = $todate - $fromdate;
        }
        else if (!empty($fromdate)) {           
            $resultreceived_period.=" and f.rRDate > $fromdate";
            $todate = "";
        }
        
          $sqlAdditionalNamesDetails = " SELECT a.application_id,concat(pnf.name,' ',pns.name) as currentname from applications a inner join organisation o on a.org_id=o.org_id ";

            if (!empty($orgId))
                $sqlAdditionalNamesDetails.=" and o.activated='Y' and o.org_id='$orgId'";
            else
                $sqlAdditionalNamesDetails.=" and o.company_id in ($this->listComp) ";


            $sqlAdditionalNamesDetails.=" inner join sectionx s on a.application_id = s.application_id 
                                               left join form_status f on s.application_id = f.application_id
                                               inner join reqdocs rqd on a.application_id = rqd.app_id 
                                               inner join users u on rqd.user_id = u.user_id
                                               inner join app_person_name apnf on a.application_id = apnf.application_id 
                                               inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4
                                               inner join app_person_name apns on a.application_id = apns.application_id 
                                               inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3                                               
                                                LEFT JOIN export_report er ON a.application_id = er.application_id
                                               where
                                               a.cancelled<>'Y' and f.dCrbDate is not NULL AND er.export_result_id IS NULL and f.rRDate is not NULL AND f.rRDate > ".PDFRESULT_DATE." $resultreceived_period";
            
            $res = $this->getDBRecords($sqlAdditionalNamesDetails);
            
               for($i =0;$i < count($res) ;$i++)
             {
                   $fieldArray  = null;
                    $fieldArray = array (
                        'application_id'=>$res[$i]['application_id'],
                        'exported_datetime'=>  date('Y-m-d H:i:s'),
                        'exported_by'=> $userId
                    );
                    $this->Insert('export_report', $fieldArray);
               }
    }
    }

?>
