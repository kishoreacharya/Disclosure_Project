<?php

class Crbtracker extends DBGrid {

    public $CURRENT_SURNAME = 3;
    public $CURRENT_FORNAME = 4;
    public $CURRENT_ADDRESS = 1;
    public $ADDITIONAL_ADDRESS = 2;
    public $FORMSTATUS = 'form_status';

    function __construct() {
        parent::__construct();
        return true;
    }

    /*
     * Function to get CRB Tracker count
     */

    public function getCRBTrackerCount($listComp) {
        $commonQuery = "Select

			  sum(if(f.application_id is NULL, 0, if(a.cancelled<>'Y' and f.dCrbDate is not NULL and f.rRdate is NULL and f.crb_system is NULL and f.initial_check is NULL and f.pnc_stage is NULL and f.lpfdate=0 and f.cert_printing is NULL and f.cert_dispatched IS NULL and f.current_status not in ('Application Withdrawn') and f.dCrbDate < '" . $this->getSpecifiedNonWorkingDays(8) . "',1,0))) total_app_not_updated,

			  sum(if(f.application_id is NULL, 0, if(a.cancelled<>'Y' and f.dCrbDate is not NULL and f.rRdate is NULL and f.crb_system is NOT NULL and f.initial_check is NULL and f.pnc_stage is NULL and f.lpfdate=0 and f.cert_printing is NULL and f.cert_dispatched IS NULL and f.current_status not in ('Application Withdrawn') and f.crb_system < '" . $this->getSpecifiedWorkingDays(3) . "',1,0))) total_app_not_progressed_init,

              sum(if(f.application_id is NULL, 0, if(a.cancelled<>'Y' and f.dCrbDate is not NULL and f.rRdate is NULL and f.crb_system is NOT NULL and f.initial_check is NOT NULL and f.pnc_stage is NULL and f.lpfdate=0 and f.cert_printing is NULL and f.cert_dispatched IS NULL and f.current_status not in ('Application Withdrawn') and f.initial_check < '" . $this->getSpecifiedWorkingDays(3) . "',1,0))) total_app_not_progressed_lpf,

			  sum(if(f.application_id is NULL, 0, if(a.cancelled<>'Y' and f.dCrbDate is not NULL and f.rRdate is NULL and f.crb_system is NOT NULL and f.initial_check is NOT NULL and f.pnc_stage is NOT NULL and f.ppl_stage is NOT NULL  and f.lpfdate=0 and f.cert_printing is NULL and f.cert_dispatched IS NULL and f.current_status not in ('Application Withdrawn') and f.ppl_stage < '" . $this->getSpecifiedNonWorkingDays(65) . "',1,0))) total_app_pending_at_lpf,

			  sum(if(f.application_id is NULL, 0, if(a.cancelled<>'Y' and f.dCrbDate is not NULL and f.rRDate is NULL AND f.cert_dispatched IS NOT NULL AND f.cert_dispatched < unix_timestamp( date_sub( now( ) , INTERVAL 14 DAY ) ) AND f.current_status not in ('Application Withdrawn'),1,0))) total_app_pending_result,

			  sum(if(f.application_id is NULL, 0, if(a.cancelled<>'Y' and f.dCrbDate is not NULL and f.rRDate is NULL and f.current_status not in ('Application Withdrawn'),1,0))) total_app_inprogress_CRB

              from applications a inner join organisation o on a.org_id=o.org_id and o.company_id in ($listComp) inner join sectionx s on a.application_id = s.application_id and {EBULKCONDITION}  left join form_status f on {JOINEXPR}
             ";

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

        $query = 'select
          sum(total_app_not_updated) total_app_not_updated ,
		  sum(total_app_not_progressed_init) total_app_not_progressed_init ,
		  sum(total_app_not_progressed_lpf) total_app_not_progressed_lpf,
		  sum(total_app_pending_at_lpf) total_app_pending_at_lpf,
		  sum(total_app_pending_result) total_app_pending_result,
		  sum(total_app_inprogress_CRB) total_app_inprogress_CRB
		  from (' . $pquery . ' UNION ' . $equery . ') a';

        $res = $this->getDBRecords($query);
        return $res;
    }

    # Function to get Not Updated onto CRB System Reports

    public function getTotalAppNotUpdatedCRB($formstatus, $updateHistory, $listComp, $orgId=null) {
        
        $qTitles = array(
            'tableCaption' => 'Not Updated onto '.DBS.' System', 'exportTypes' => 'xls',
            'fsname' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
            'deptname' => array('alias' => 'Dept. Name', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'bandname' => array('alias' => 'School', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'dob' => array('alias' => 'DOB', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'app_ref_no' => array('alias' => 'Form Ref', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'submitdate' => array('alias' => 'Submit Date', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'dispatched' => array('alias' => 'Sent to '.DBS, 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'last_updated' => array('alias' => 'Last Updated', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
            'orgname' => array('alias' => 'Actual Organisation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'job' => array('alias' => 'Job Title', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'works_with_children' => array('alias' => 'Works with Children', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'works_with_adult' => array('alias' => 'Works with '.VG_FULL, 'type' => 'string', 'visible' => '0', 'dontSum' => '1')
        );

        $query = "select $formstatus, concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob, f.app_ref_no, o.name bandname, c.name deptname, from_unixtime(a.submit_time,'%d/%m/%Y') submitdate, from_unixtime(f.dCrbDate,'%d/%m/%Y') dispatched,  $updateHistory, ur.orgname, j.job,if(s.jobwork = 1 OR s.jobwork = 3,'Yes','No') works_with_adult,if(s.jobwork = 2 OR s.jobwork = 3,'Yes','No') works_with_children
      from applications a
	  inner join organisation o on a.org_id=o.org_id
	  INNER JOIN company c ON o.company_id=c.company_id
	  inner join sectionx s on a.application_id = s.application_id
	  inner join form_status f on s.application_id = f.application_id
	  inner join app_person_name apnf on a.application_id = apnf.application_id
	  inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4
	  inner join app_person_name apns on a.application_id = apns.application_id
	  inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3
	  INNER JOIN reqdocs rqd on a.application_id = rqd.app_id 
      INNER JOIN users u on rqd.user_id = u.user_id 
      LEFT OUTER JOIN user_registration ur on u.username = ur.username 
      LEFT OUTER JOIN jobs j on ur.job_role = j.sno
	  where a.cancelled<>'Y'
	  AND f.`dCrbDate` IS NOT NULL
	  AND f.`rRdate` IS NULL
	  and f. crb_system IS NULL
	  and f.initial_check IS NULL
	  and f.pnc_stage IS NULL
	  and f.cert_printing is NULL
	  and f.cert_dispatched is NULL
	  and f.dCrbDate < '" . $this->getSpecifiedNonWorkingDays(8) . "'";

        $query.= " and o.company_id in ($listComp) and f.current_status not in ('Application Withdrawn') ";
        if (!empty($orgId))
            $query.=" and o.org_id='$orgId'";

        $query.=" order by fsname";
        $this->query_to_table_source($query, $qTitles);
    }

    # Function to get Not Progressed to Initial Check Reports

    public function getTotalAppNotProgIntCheck($formstatus, $updateHistory, $listComp, $orgId=null) {
        $qTitles = array(
            'tableCaption' => 'Not Progressed to Initial Check', 'exportTypes' => 'xls',
            'fsname' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
            'deptname' => array('alias' => 'Dept. Name', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'bandname' => array('alias' => 'School', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'dob' => array('alias' => 'DOB', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'app_ref_no' => array('alias' => 'Form Ref', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'submitdate' => array('alias' => 'Submit Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'dispatched' => array('alias' => 'Sent to '.DBS, 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'crb_system' => array('alias' => DBS.' System', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'last_updated' => array('alias' => 'Last Updated', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
            'orgname' => array('alias' => 'Actual Organisation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'job' => array('alias' => 'Job Title', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'works_with_children' => array('alias' => 'Works with Children', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'works_with_adult' => array('alias' => 'Works with '.$VG_FULL, 'type' => 'string', 'visible' => '0', 'dontSum' => '1')
        );

        $query = "select $formstatus, concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob, f.app_ref_no, o.name bandname, c.name deptname, from_unixtime(a.submit_time,'%d/%m/%Y') submitdate, from_unixtime(f.dCrbDate,'%d/%m/%Y') dispatched, from_unixtime(f.crb_system,'%d/%m/%Y') crb_system,  $updateHistory, ur.orgname, j.job,if(s.jobwork = 1 OR s.jobwork = 3,'Yes','No') works_with_adult,if(s.jobwork = 2 OR s.jobwork = 3,'Yes','No') works_with_children
      from applications a
	  inner join organisation o on a.org_id=o.org_id
	  INNER JOIN company c ON o.company_id=c.company_id
	  inner join sectionx s on a.application_id = s.application_id
	  inner join form_status f on s.application_id = f.application_id
	  inner join app_person_name apnf on a.application_id = apnf.application_id
	  inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4
	  inner join app_person_name apns on a.application_id = apns.application_id
	  inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3
	  INNER JOIN reqdocs rqd on a.application_id = rqd.app_id 
      INNER JOIN users u on rqd.user_id = u.user_id 
      LEFT OUTER JOIN user_registration ur on u.username = ur.username 
      LEFT OUTER JOIN jobs j on ur.job_role = j.sno
	  where a.cancelled<>'Y'
	  AND f.`dCrbDate` IS NOT NULL
	  AND f.`rRdate` IS NULL
	  and f. crb_system IS NOT NULL
	  and f.initial_check IS NULL
	  and f.pnc_stage IS NULL
	  and f.lpfdate=0
	  and f.cert_printing is NULL
	  and f.cert_dispatched is NULL
	  and f.crb_system < '" . $this->getSpecifiedWorkingDays(3) . "'";

        $query.= " and o.company_id in ($listComp) and f.current_status not in ('Application Withdrawn') ";
        if (!empty($orgId))
            $query.=" and o.org_id='$orgId'";

        $query.=" order by fsname";

        $this->query_to_table_source($query, $qTitles);
    }

    # Function to get Not Progressed to LPF (Local Police Force) Reports

    public function getTotalAppNotProgLPF($formstatus, $updateHistory, $listComp, $orgId=null) {
        $qTitles = array(
            'tableCaption' => 'Not Progressed to LPF (Local Police Force)', 'exportTypes' => 'xls',
            'fsname' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
            'deptname' => array('alias' => 'Dept. Name', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'bandname' => array('alias' => 'School', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'dob' => array('alias' => 'DOB', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'app_ref_no' => array('alias' => 'Form Ref', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'submitdate' => array('alias' => 'Submit Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'dispatched' => array('alias' => 'Sent to '.DBS, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'crb_system' => array('alias' => DBS.' System', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'initial_check' => array('alias' => 'Initial Check', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'last_updated' => array('alias' => 'Last Updated', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
            'orgname' => array('alias' => 'Actual Organisation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'job' => array('alias' => 'Job Title', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'works_with_children' => array('alias' => 'Works with Children', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'works_with_adult' => array('alias' => 'Works with '.VG_FULL, 'type' => 'string', 'visible' => '0', 'dontSum' => '1')
        );

        $query = "select $formstatus, concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob, f.app_ref_no, o.name bandname, c.name deptname, from_unixtime(a.submit_time,'%d/%m/%Y') submitdate, from_unixtime(f.dCrbDate,'%d/%m/%Y') dispatched, from_unixtime(f.crb_system,'%d/%m/%Y') crb_system, from_unixtime(f.initial_check,'%d/%m/%Y') initial_check, $updateHistory, ur.orgname, j.job,if(s.jobwork = 1 OR s.jobwork = 3,'Yes','No') works_with_adult,if(s.jobwork = 2 OR s.jobwork = 3,'Yes','No') works_with_children
      from applications a
	  inner join organisation o on a.org_id=o.org_id
	  INNER JOIN company c ON o.company_id=c.company_id
	  inner join sectionx s on a.application_id = s.application_id
	  inner join form_status f on s.application_id = f.application_id
	  inner join app_person_name apnf on a.application_id = apnf.application_id
	  inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4
	  inner join app_person_name apns on a.application_id = apns.application_id
	  inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3
	  INNER JOIN reqdocs rqd on a.application_id = rqd.app_id 
      INNER JOIN users u on rqd.user_id = u.user_id 
      LEFT OUTER JOIN user_registration ur on u.username = ur.username 
      LEFT OUTER JOIN jobs j on ur.job_role = j.sno
	  where a.cancelled<>'Y'
	  AND f.`dCrbDate` IS NOT NULL
	  AND f.`rRdate` IS NULL
	  and f. crb_system IS NOT NULL
	  and f.initial_check IS NOT NULL
	  and f.pnc_stage IS NULL
	  and f.lpfdate=0
	  and f.cert_printing is NULL
	  and f.cert_dispatched is NULL
	  and f.initial_check < '" . $this->getSpecifiedWorkingDays(3) . "'";

        $query.= " and o.company_id in ($listComp) and f.current_status not in ('Application Withdrawn') ";
        if (!empty($orgId))
            $query.=" and o.org_id='$orgId'";

        $query.=" order by fsname";

        $this->query_to_table_source($query, $qTitles);
    }

    # Function to get Pending at LPF (Local Police Force) Reports

    public function getTotalAppPendLPF($formstatus, $updateHistory, $listComp, $orgId=null) {
        $qTitles = array(
            'tableCaption' => 'Pending at LPF (Local Police Force)', 'exportTypes' => 'xls',
            'fsname' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
            'deptname' => array('alias' => 'Dept. Name', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'bandname' => array('alias' => 'School', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'dob' => array('alias' => 'DOB', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'app_ref_no' => array('alias' => 'Form Ref', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'submitdate' => array('alias' => 'Submit Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'dispatched' => array('alias' => 'Sent to '.DBS, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'crb_system' => array('alias' => DBS.' System', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'initial_check' => array('alias' => 'Initial Check', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'pnc_stage' => array('alias' => 'PNC Stage', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'ppl_stage' => array('alias' =>  DCBL.'/'.DABL, 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'days_at_lpf' => array('alias' => 'Days at LPF', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'last_updated' => array('alias' => 'Last Updated', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
            'orgname' => array('alias' => 'Actual Organisation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'job' => array('alias' => 'Job Title', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'works_with_children' => array('alias' => 'Works with Children', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'works_with_adult' => array('alias' => 'Works with '.VG_FULL, 'type' => 'string', 'visible' => '0', 'dontSum' => '1')
        );

        $query = "select $formstatus, concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob, f.app_ref_no, o.name bandname, c.name deptname, from_unixtime(a.submit_time,'%d/%m/%Y') submitdate, from_unixtime(f.dCrbDate,'%d/%m/%Y') dispatched, from_unixtime(f.crb_system,'%d/%m/%Y') crb_system, from_unixtime(f.initial_check,'%d/%m/%Y') initial_check, from_unixtime(f.pnc_stage,'%d/%m/%Y') pnc_stage, if(f.pnc_stage is not null, datediff(now(),from_unixtime(f.pnc_stage)) ,'N/A') days_at_lpf, from_unixtime(f.ppl_stage,'%d/%m/%Y') ppl_stage,  $updateHistory , ur.orgname, j.job,if(s.jobwork = 1 OR s.jobwork = 3,'Yes','No') works_with_adult,if(s.jobwork = 2 OR s.jobwork = 3,'Yes','No') works_with_children
      from applications a
	  inner join organisation o on a.org_id=o.org_id
	  INNER JOIN company c ON o.company_id=c.company_id
	  inner join sectionx s on a.application_id = s.application_id
	  inner join form_status f on s.application_id = f.application_id
	  inner join app_person_name apnf on a.application_id = apnf.application_id
	  inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4
	  inner join app_person_name apns on a.application_id = apns.application_id
	  inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3
	  INNER JOIN reqdocs rqd on a.application_id = rqd.app_id 
      INNER JOIN users u on rqd.user_id = u.user_id 
      LEFT OUTER JOIN user_registration ur on u.username = ur.username 
      LEFT OUTER JOIN jobs j on ur.job_role = j.sno
	  where a.cancelled<>'Y'
	  AND f.`dCrbDate` IS NOT NULL
	  AND f.`rRdate` IS NULL
	  and f. crb_system IS NOT NULL
	  and f.initial_check IS NOT NULL
	  and f.pnc_stage IS NOT NULL
	  and f.ppl_stage is NOT NULL
	  and f.lpfdate=0
	  and f.cert_printing is NULL
	  and f.cert_dispatched is NULL
	  and f.ppl_stage < '" . $this->getSpecifiedNonWorkingDays(65) . "'
	  ";

        $query.= " and o.company_id in ($listComp) and f.current_status not in ('Application Withdrawn') ";
        if (!empty($orgId))
            $query.=" and o.org_id='$orgId'";

        $query.=" order by fsname";


        $this->query_to_table_source($query, $qTitles);
    }

    # Function to get Certificates not yet Received Reports

    public function getTotalAppCertRec($formstatus, $updateHistory, $listComp, $orgId=null) {
        $qTitles = array(
            'tableCaption' => 'Certificates not yet Received', 'exportTypes' => 'xls',
            'fsname' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
            'deptname' => array('alias' => 'Dept. Name', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'bandname' => array('alias' => 'School', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'dob' => array('alias' => 'DOB', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'app_ref_no' => array('alias' => 'Form Ref', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'submitdate' => array('alias' => 'Submit Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'dispatched' => array('alias' => 'Sent to '.DBS, 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'crb_system' => array('alias' => DBS.' System', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'initial_check' => array('alias' => 'Initial Check', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'pnc_stage' => array('alias' => 'PNC Stage', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'ppl_stage' => array('alias' => DCBL.'/'.DABL, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'lpfdate' => array('alias' => 'LPF Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'cert_dispatched' => array('alias' => 'Cert. Dispatched', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'last_updated' => array('alias' => 'Last Updated', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
            'orgname' => array('alias' => 'Actual Organisation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'job' => array('alias' => 'Job Title', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'works_with_children' => array('alias' => 'Works with Children', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'works_with_adult' => array('alias' => 'Works with '.VG_FULL, 'type' => 'string', 'visible' => '0', 'dontSum' => '1')
        );

        $query = "select $formstatus, concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob, f.app_ref_no, o.name bandname, c.name deptname, from_unixtime(a.submit_time,'%d/%m/%Y') submitdate, from_unixtime(f.dCrbDate,'%d/%m/%Y') dispatched, from_unixtime(f.crb_system,'%d/%m/%Y') crb_system, from_unixtime(f.initial_check,'%d/%m/%Y') initial_check, from_unixtime(f.pnc_stage,'%d/%m/%Y') pnc_stage, from_unixtime(f.ppl_stage,'%d/%m/%Y') ppl_stage , if(f.lpfdate = 0 , ' - ' ,from_unixtime(f.lpfdate,'%d/%m/%Y')) lpfdate,from_unixtime(f.cert_dispatched,'%d/%m/%Y') cert_dispatched,  $updateHistory, ur.orgname, j.job,if(s.jobwork = 1 OR s.jobwork = 3,'Yes','No') works_with_adult,if(s.jobwork = 2 OR s.jobwork = 3,'Yes','No') works_with_children 
      from applications a
	  inner join organisation o on a.org_id=o.org_id
	  INNER JOIN company c ON o.company_id=c.company_id
	  inner join sectionx s on a.application_id = s.application_id
	  inner join form_status f on s.application_id = f.application_id
	  inner join app_person_name apnf on a.application_id = apnf.application_id
	  inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4
	  inner join app_person_name apns on a.application_id = apns.application_id
	  inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3
	  INNER JOIN reqdocs rqd on a.application_id = rqd.app_id 
      INNER JOIN users u on rqd.user_id = u.user_id 
      LEFT OUTER JOIN user_registration ur on u.username = ur.username 
      LEFT OUTER JOIN jobs j on ur.job_role = j.sno
	  where a.cancelled<>'Y'
	  AND f.`dCrbDate` IS NOT NULL
	  AND f.`rRdate` IS NULL
	  AND f.cert_dispatched IS NOT NULL
	  AND f.cert_dispatched < unix_timestamp( date_sub( now( ) , INTERVAL 14 DAY ) )";

        $query.= " and o.company_id in ($listComp) and f.current_status not in ('Application Withdrawn') ";
        if (!empty($orgId))
            $query.=" and o.org_id='$orgId'";

        $query.=" order by fsname";

        $this->query_to_table_source($query, $qTitles);
    }

    # Function to get Total Applications in Progress with CRB Reports

    public function getTotalAppProgCRB($formstatus, $appstatusfield, $listComp, $orgId=null) {
        $qTitles = array(
            'tableCaption' => 'Total Applications in Progress with '.DBS, 'exportTypes' => 'xls',
            'fsname' => array('alias' => 'Applicant Name', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
            'deptname' => array('alias' => 'Dept. Name', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'bandname' => array('alias' => 'School', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'dob' => array('alias' => 'DOB', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'app_ref_no' => array('alias' => 'Form Ref', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'submitdate' => array('alias' => 'Submit Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'dispatched' => array('alias' => 'Sent to '.DBS, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'crb_system' => array('alias' => DBS.' System', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'initial_check' => array('alias' => 'Initial Check', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'pnc_stage' => array('alias' => 'PNC Stage', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'ppl_stage' => array('alias' => DCBL.'/'.DABL, 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'lpfdate' => array('alias' => 'LPF Date', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'cert_dispatched' => array('alias' => 'Cert. Dispatched', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'last_updated' => array('alias' => 'Last Updated', 'type' => 'string', 'visible' => '1', 'parseHTML' => '1', 'dontSum' => '1'),
            'application_status' => array('alias' => 'Status', 'type' => 'string', 'visible' => '1', 'dontSum' => '1'),
            'orgname' => array('alias' => 'Actual Organisation', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'job' => array('alias' => 'Job Title', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'works_with_children' => array('alias' => 'Works with Children', 'type' => 'string', 'visible' => '0', 'dontSum' => '1'),
            'works_with_adult' => array('alias' => 'Works with '.VG_FULL, 'type' => 'string', 'visible' => '0', 'dontSum' => '1')
        );

        $query = "select $formstatus, concat(substr(a.date_of_birth,1,2),'/',substr(a.date_of_birth,3,2),'/',substr(a.date_of_birth,5,4)) dob, f.app_ref_no, o.name bandname, c.name deptname, from_unixtime(a.submit_time,'%d/%m/%Y') submitdate, from_unixtime(f.dCrbDate,'%d/%m/%Y') dispatched, from_unixtime(f.crb_system,'%d/%m/%Y') crb_system, from_unixtime(f.initial_check,'%d/%m/%Y') initial_check, from_unixtime(f.pnc_stage,'%d/%m/%Y') pnc_stage, from_unixtime(f.ppl_stage,'%d/%m/%Y') ppl_stage, if(f.lpfdate = 0 , ' - ' ,from_unixtime(f.lpfdate,'%d/%m/%Y')) lpfdate,from_unixtime(f.cert_dispatched,'%d/%m/%Y') cert_dispatched,  $appstatusfield, , ur.orgname, j.job,if(s.jobwork = 1 OR s.jobwork = 3,'Yes','No') works_with_adult,if(s.jobwork = 2 OR s.jobwork = 3,'Yes','No') works_with_children 
      from applications a
	  inner join organisation o on a.org_id=o.org_id
	  INNER JOIN company c ON o.company_id=c.company_id
	  inner join sectionx s on a.application_id = s.application_id
	  inner join form_status f on s.application_id = f.application_id
	  inner join app_person_name apnf on a.application_id = apnf.application_id
	  inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4
	  inner join app_person_name apns on a.application_id = apns.application_id
	  inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3
	  INNER JOIN reqdocs rqd on a.application_id = rqd.app_id 
      INNER JOIN users u on rqd.user_id = u.user_id 
      LEFT OUTER JOIN user_registration ur on u.username = ur.username 
      LEFT OUTER JOIN jobs j on ur.job_role = j.sno
	  where a.cancelled<>'Y'
	  AND f.`dCrbDate` IS NOT NULL
	  AND f.`rRdate` IS NULL
	  ";

        $query.= " and o.company_id in ($listComp) and f.current_status not in ('Application Withdrawn') ";
        if (!empty($orgId))
            $query.=" and o.org_id='$orgId'";

        $query.=" order by fsname";

        $this->query_to_table_source($query, $qTitles);
    }

    #Check For Supliments

    public function checkSupliments($applicationId) {
        $query = "select a.supliment_required as sup1, b.supliment_required as sup2 from applications a, sectionx b where a.application_id=b.application_id and a.application_id='$applicationId'";
        $sups = $this->getDBRecords($query);
        if ($sups[0]["sup1"] == 'Y' || $sups[0]["sup2"] == 'Y')
            return true;
        else
            return false;
    }

    public function getApplicatinonDetails($applicationId) {       
        $query = "select ebulkapp,poca_check,poca_result,submit_time,sectionx_complete_time from applications where application_id='$applicationId'";
        $res = $this->getDBRecords($query);
        return $res;
    }

    public function getAppComments($applicationId) {
        $query = "select * from application_comments where application_id='$applicationId' order by entered_on desc";
        $commentsres = $this->getDBRecords($query);
        return $commentsres;
    }

    public function getAllAppDetails($applicationId, $appRefNo=null) {
        if (empty($appRefNo)) {
            $query = "select last_updated,updaterequested,request_completed,lpf_pending_stage, crberror,current_status,initial_check,pnc_stage,ppl_stage,cert_printing,cert_dispatched,lpfdate,lpfStartDate,crb_system,crn_number,dcrbDate as dCrbDate,rRdate,`p&sDate` as psdt,`s&rDate` as srdate,lpfdate,stop_updates,rejection_date,povaStart,povaEnd,list99Start,list99End,init_check_status,pnc_status,ppl_status,lpf_status,cert_printing_status,push_back_init_check,push_back_pnc,push_back_ppl,push_back_lpf,push_back_printing,in_conflict,result,certificate_seen_date,info_requested_by_adl,info_requested_by_dbs from form_status where application_id='$applicationId'";
        } else {
            $query = "select last_updated,updaterequested,request_completed,lpf_pending_stage, crberror,current_status,initial_check,pnc_stage,ppl_stage,cert_printing,cert_dispatched,lpfdate,lpfStartDate,crb_system,crn_number,dcrbDate as dCrbDate,rRdate,`p&sDate` as psdt,`s&rDate` as srdate,lpfdate,stop_updates,rejection_date,povaStart,povaEnd,list99Start,list99End,init_check_status,pnc_status,ppl_status,lpf_status,cert_printing_status,push_back_init_check,push_back_pnc,push_back_ppl,push_back_lpf,push_back_printing,in_conflict,result,certificate_seen_date,info_requested_by_adl,info_requested_by_dbs from form_status where app_ref_no='$appRefNo'";
        }
        $res = $this->getDBRecords($query);
        return $res;
    }

    public function assignCRBAppValues($applicationId, $appRefNo, $res, $eBulkApplication, $submit_time, $sectionx_complete_time) {
        $timeNow = time();

        $crberror = $res[0]["crberror"];
        $current_status = $res[0]["current_status"];
        if (!empty($res[0]["psdt"]))
            $psdate = $res[0]["psdt"];
        if (!empty($res[0]["srdate"]))
            $srDate = $res[0]["srdate"];
        if (!empty($res[0]["rRdate"]))
            $rrdate = $res[0]["rRdate"];
        $stop_updates = $res[0]["stop_updates"];
        $lpf_pending_stage = $res[0]["lpf_pending_stage"];
        $crn_number = $res[0]["crn_number"];
        $crbupdate = $res[0]["updaterequested"];
        $crbupdateCompleted = $res[0]["request_completed"];
        if (!empty($res[0]["in_conflict"]))
            $in_conflict = $res[0]["in_conflict"];
        if (!empty($res[0]["last_updated"]))
            $last_update_date = date("d-m-Y", $res[0]["last_updated"]);

        if (!empty($res[0]["povaStart"]))
            $povastartdate = date("d/m/Y", $res[0]["povaStart"]);
        if (!empty($res[0]["povaEnd"]))
            $povaenddate = date("d/m/Y", $res[0]["povaEnd"]);
        if (!empty($res[0]["list99Start"]))
            $list99startdate = date("d/m/Y", $res[0]["list99Start"]);
        if (!empty($res[0]["list99End"]))
            $list99enddate = date("d/m/Y", $res[0]["list99End"]);

        if($res[0]["rRdate"] < PDFRESULT_DATE){
    $cert_seen="N/A";
}else{
    if(!empty($res[0]["certificate_seen_date"]))
        $cert_seen=date("d-m-Y",$res[0]["certificate_seen_date"]);
}

#Tracking CRB Dates
        if (!empty($res[0]["initial_check"])) {
            if ($res[0]["push_back_init_check"] == "M")
                $init_check_date = "<font color='red'>" . date("d-m-Y", $res[0]["initial_check"]) . "</font>";
            else
                $init_check_date=date("d-m-Y", $res[0]["initial_check"]);
        }else {
            if (empty($res[0]["cert_dispatched"])) {
                if ($res[0]["push_back_init_check"] == "Y")
                    $init_check_date = "<font color='red'><b>Push Back</b></font>";
                else
                    $init_check_date="<font color='blue'><b>" . $res[0]["init_check_status"] . "</b></font>";
            }
        }

        if (!empty($res[0]["pnc_stage"])) {
            if ($res[0]["push_back_pnc"] == "M")
                $pnc_check_date = "<font color='red'>" . date("d-m-Y", $res[0]["pnc_stage"]) . "</font>";
            else
                $pnc_check_date=date("d-m-Y", $res[0]["pnc_stage"]);
        }else {
            if (empty($res[0]["cert_dispatched"])) {
                if ($res[0]["push_back_pnc"] == "Y")
                    $pnc_check_date = "<font color='red'><b>Push Back</b></font>";
                else
                    $pnc_check_date="<font color='blue'><b>" . $res[0]["pnc_status"] . "</b></font>";
            }
        }

        if (!empty($res[0]["ppl_stage"])) {
            if ($res[0]["push_back_ppl"] == "M")
                $ppl_check_date = "<font color='red'>" . date("d-m-Y", $res[0]["ppl_stage"]) . "</font>";
            else
                $ppl_check_date=date("d-m-Y", $res[0]["ppl_stage"]);
        }else {
            if (empty($res[0]["cert_dispatched"])) {
                if ($res[0]["push_back_ppl"] == "Y")
                    $ppl_check_date = "<font color='red'><b>Push Back</b></font>";
                else
                    $ppl_check_date="<font color='blue'><b>" . $res[0]["ppl_status"] . "</b></font>";
            }
        }

        if (!empty($res[0]["lpfdate"]) && $res[0]["lpfdate"] != 0) {
            if ($res[0]["push_back_lpf"] == "M")
                $lpf_check_date = "<font color='red'>" . date("d-m-Y", $res[0]["lpfdate"]) . "</font>";
            else
                $lpf_check_date=date("d-m-Y", $res[0]["lpfdate"]);
        }else {
            if (empty($res[0]["cert_dispatched"])) {
                if ($res[0]["push_back_lpf"] == "Y")
                    $lpf_check_date = "<font color='red'><b>Push Back</b></font>";
                else
                    $lpf_check_date="<font color='blue'><b>" . $res[0]["lpf_status"] . "</b></font>";
            }
        }

        if (!empty($res[0]["cert_printing"])) {
            if ($res[0]["push_back_printing"] == "M")
                $printing_check_date = "<font color='red'>" . date("d-m-Y", $res[0]["cert_printing"]) . "</font>";
            else
                $printing_check_date=date("d-m-Y", $res[0]["cert_printing"]);
        }else {
            if (empty($res[0]["cert_dispatched"])) {
                if ($res[0]["push_back_printing"] == "Y")
                    $printing_check_date = "<font color='red'><b>Push Back</b></font>";
                else
                    $printing_check_date="<font color='blue'><b>" . $res[0]["cert_printing_status"] . "</b></font>";
            }
        }

#get Current status
        if ($eBulkApplication <> 'Y') {
            if ((!empty($submit_time) ) && (empty($sectionx_complete_time)))
                $current_status = "Application Submitted";

            if ((!empty($sectionx_complete_time) ) && (empty($appRefNo)))
                $current_status = "Awaiting Print";

            if ((!empty($sectionx_complete_time) ) && (!empty($appRefNo)) && (empty($res[0]["p&sDate"])))
                $current_status = "Printed but Not Sent";

            if ((!empty($res[0]["p&sDate"]) && ($res[0]["p&sDate"] != 'NULL')) && (($res[0]["s&rDate"] == 'NULL')
                    || (empty($res[0]["s&rDate"]))))
                $current_status = "Print & Send";

            if ((!empty($res[0]["s&rDate"]) && ($res[0]["s&rDate"] != 'NULL')) && (($res[0]["dCrbDate"] == 'NULL')
                    || (empty($res[0]["dCrbDate"]))))
                $current_status = "Dispatch Pending";

            if ((!empty($res[0]["dCrbDate"]) && ($res[0]["dCrbDate"] != 'NULL')) && (($res[0]["rRdate"] == 'NULL')
                    || (empty($res[0]["rRdate"]))))
                            {
                    if($res[0]["info_requested_by_dbs"] == "Y") 
                        $current_status=INFO_REQUESTED_STATUS_DBS;
                    elseif(!empty($res[0]["current_status"]) && $res[0]["current_status"]<>"") 
                        $current_status= trim($res[0]["current_status"]);
                    else
                        $current_status = "Dispatched to ".DBS;
                }    

            if ((!empty($res[0]["rRdate"]) && ($res[0]["rRdate"] != 'NULL')))
               $current_status = $res[0]["result"];
        } else {

            if ((empty($submit_time) ) && (!empty($sectionx_complete_time)))
                $current_status = "ID Verified";

            if (!(empty($submit_time) ) && empty($res[0]["p&sDate"]))
                $current_status = "Application Submitted";

            if (!empty($res[0]["p&sDate"]) && empty($res[0]["s&rDate"]))
                $current_status = "Quality Check Completed";

            if (empty($res[0]["dCrbDate"]) && !empty($res[0]["s&rDate"]))
                $current_status = "In Dispatch Queue";

            if (!empty($res[0]["dCrbDate"]) && empty($res[0]["rRdate"]))
                 {
                    if($res[0]["info_requested_by_dbs"] == "Y") 
                        $current_status=INFO_REQUESTED_STATUS_DBS;
                    elseif(!empty($res[0]["current_status"]) && $res[0]["current_status"]<>"") 
                        $current_status= trim($res[0]["current_status"]);
                    else
                        $current_status = "Dispatched to ".DBS;
                }    

            if ((!empty($res[0]["rRdate"]) && ($res[0]["rRdate"] != 'NULL')))
                $current_status = $res[0]["result"];
        }

        if (!empty($res[0]["current_status"]) && empty($res[0]["rRdate"]))
            $current_status = $res[0]["current_status"];


// calculate the duration of the application entered into CRB system till date
        if (!empty($res[0]["crb_system"]) && empty($res[0]["cert_dispatched"])) {
            $CRBentered = $this->dateDiff("/", date("m/d/Y", $timeNow), date("m/d/Y", $res[0]["crb_system"]));
        }

// calculate the duration when LPF entry date was entered from today
        if (!empty($res[0]["lpfStartDate"]) && $res[0]["lpfStartDate"] != '-1') {
            $timenw = time();
            $endDate = date("m/d/Y", $timenw);
            $beginDate = date("m/d/Y", $res[0]["lpfStartDate"]);

            $datediff = $this->dateDiff("/", $endDate, $beginDate);
            if ($datediff == 0

                )$datediffer = "";
            else
                $datediffer="[" . $datediff . "days]";
        }

#Check if CRN number was entered in old system
        if (empty($crn_number)) {
            $query = "select crbrefno from form_comments where appRefNo='$appRefNo' and crbrefno<>'' order by id desc";
            $oldSys = $this->getDBRecords($query);
            $crn_number = $oldSys[0]["crbrefno"];
        }

        if (!empty($res[0]["dCrbDate"])) {
            $dcrbDate = date("d-m-Y", $res[0]["dCrbDate"]);
        }

        if (!empty($res[0]["lpfdate"]) && $res[0]["lpfdate"] != 0) {
            $lpfdatevalue = date("d-m-Y", $res[0]["lpfdate"]);
        }



#calculate the date difference b/w entered into crb system and all stages
        if (!empty($res[0]["crb_system"]) && !empty($res[0]["initial_check"])) {
            $initentered = "(" . $this->dateDiff("/", date("m/d/Y", $res[0]["initial_check"]), date("m/d/Y", $res[0]["crb_system"])) . ")";
        }
        if (!empty($res[0]["crb_system"]) && !empty($res[0]["pnc_stage"])) {
            $pncentered = "(" . $this->dateDiff("/", date("m/d/Y", $res[0]["pnc_stage"]), date("m/d/Y", $res[0]["crb_system"])) . ")";
        }
        if (!empty($res[0]["crb_system"]) && !empty($res[0]["ppl_stage"])) {
            $pplentered = "(" . $this->dateDiff("/", date("m/d/Y", $res[0]["ppl_stage"]), date("m/d/Y", $res[0]["crb_system"])) . ")";
        }
        if (!empty($res[0]["crb_system"]) && !empty($res[0]["lpfdate"]) && $res[0]["lpfdate"] != 0) {
            $lpfentered = "(" . $this->dateDiff("/", date("m/d/Y", $res[0]["lpfdate"]), date("m/d/Y", $res[0]["crb_system"])) . ")";
        }
        if (!empty($res[0]["crb_system"]) && !empty($res[0]["cert_printing"])) {
            $printingentered = "(" . $this->dateDiff("/", date("m/d/Y", $res[0]["cert_printing"]), date("m/d/Y", $res[0]["crb_system"])) . ")";
        }
        if (!empty($res[0]["crb_system"]) && !empty($res[0]["cert_dispatched"])) {
            $dispatchedentered = "(" . $this->dateDiff("/", date("m/d/Y", $res[0]["cert_dispatched"]), date("m/d/Y", $res[0]["crb_system"])) . ")";
        }



        if ($appRefNo == '') {
            $query = "select a.ebulkapp,CONCAT(a.work_force,' ',a.position) position, a.home_phone, a.prefered_phone, a.org_id,u.email from applications a
            INNER JOIN reqdocs r ON a.application_id = r.app_id
            INNER JOIN users u ON r.user_id = u.user_id where a.application_id='$applicationId'";
        } else {
            $query = "select a.ebulkapp,CONCAT(a.work_force,' ',a.position) position, a.home_phone, a.prefered_phone, a.org_id,u.email from (applications a, sectionx x)
            INNER JOIN reqdocs r ON a.application_id = r.app_id
            INNER JOIN users u ON r.user_id = u.user_id
            where a.application_id=x.application_id and (a.application_id='$applicationId' or x.app_ref_no='$appRefNo')";
        }
        $res1 = $this->getDBRecords($query);
        $position = $res1[0]["position"];
        $home_phone = $res1[0]["home_phone"];
        $prefered_phone = $res1[0]["prefered_phone"];
        $email = $res1[0]["email"];
        $orgid = $res1[0]["org_id"];
        $orgnam = $this->getOrgName($orgid);
        $eBulkApplication1 = $res1[0]["ebulkapp"];


//check whether the applicant has requested for POVA
        $qry = "select pova,list99,scotwork from sectionx where application_id='$applicationId'";
        $povares1 = $this->getDBRecords($qry);
        $povares = $povares1[0]['pova'];
        $list99 = $povares1[0]['list99'];
        $scotwork = $povares1[0]['scotwork'];



        $surName = $this->getApplicantName($applicationId, $this->CURRENT_SURNAME);
        $forName = $this->getApplicantName($applicationId, $this->CURRENT_FORNAME);
        $name = $forName[0]["name"] . " " . $surName[0]["name"];

        $dob = $this->getApplicantDob($applicationId);

        if ($this->checkSupliments($applicationId) == true)
            $contd = "C";

        $errors = $this->getFormErrors($appRefNo);


        $formstatus[appRefNo] = $appRefNo;
        $formstatus[email] = $email;
        $formstatus[contd] = $contd;
        $formstatus[name] = $this->correctcase($name);
        $formstatus[dob] = $dob;
        $formstatus[position] = $this->correctcase($position);
        $formstatus[home_phone] = $home_phone;
        $formstatus[prefered_phone] = $prefered_phone;
        $formstatus[crn_number] = $crn_number;
        $formstatus[last_update_date] = $last_update_date;
        $formstatus[orgnam] = $orgnam;
        $formstatus[current_status] = $current_status;
        $formstatus[povares] = $povares;
        $formstatus[list99] = $list99;
        $formstatus[povastartdate] = $povastartdate;
        $formstatus[povaenddate] = $povaenddate;
        $formstatus[list99startdate] = $list99startdate;
        $formstatus[list99enddate] = $list99enddate;
        $formstatus[eBulkApplication] = $eBulkApplication;
        $formstatus[submit_time] = $submit_time;
        $formstatus[sectionx_complete_time] = $sectionx_complete_time;

        $formstatus[initentered] = $initentered;
        $formstatus[pncentered] = $pncentered;
        $formstatus[pplentered] = $pplentered;
        $formstatus[lpfentered] = $lpfentered;
        $formstatus[printingentered] = $printingentered;
        $formstatus[dispatchedentered] = $dispatchedentered;

        $formstatus[init_check_date] = $init_check_date;
        $formstatus[pnc_check_date] = $pnc_check_date;
        $formstatus[ppl_check_date] = $ppl_check_date;
        $formstatus[lpf_check_date] = $lpf_check_date;
        $formstatus[printing_check_date] = $printing_check_date;
        $formstatus[CRBentered] = $CRBentered;
        $formstatus['cert_seen']=$cert_seen;
                /* Result */
        $formstatus['resultState'] = $res[0]['result'];
        /* Result */

        if (!empty($psdate)) {
            $psdate = date("d-m-Y", $psdate);
            $formstatus[psdate] = $psdate;
        }
        if (!empty($srDate)) {
            $srDate = date("d-m-Y", $srDate);
            $formstatus[srDate] = $srDate;
        }
        if (!empty($submit_time)) {
            $submit_time = date("d-m-Y", $submit_time);
            $formstatus[submit_time] = $submit_time;
        }
        if (!empty($rrdate)) {
            $rrdate = date("d-m-Y", $rrdate);
            $formstatus[rrdate] = $rrdate;
        }
        if (!empty($in_conflict)) {
            $in_conflict = date("d-m-Y", $in_conflict);
            $formstatus[in_conflict] = $in_conflict;
        }
        $formstatus[dcrbDate] = $dcrbDate;
        $formstatus[datediffer] = $datediffer;
        return $formstatus;
    }

    #Function to update Form Status Table

    public function updateAppFormStatus($tablename, $fieldArray, $app_ref_no) {

        $condition = " app_ref_no='$app_ref_no'";

        $this->Update($tablename, $fieldArray, $condition);
    }

    #function to check if CRB have changed or have reset the dates

    public function trackCRBDates($uparray, $applicationId, $appRefNo) {
        $timenow = time();
        $dateAltered = false;
        $query = "select * from form_status where app_ref_no='$appRefNo'";
        $res = $this->getDBRecords($query);

        for ($z = 0; $z < count($uparray); $z++) {
            $cstage = $uparray[$z]["stage"];

            #If current Stage is Certificate Dispatched then CRB shows a different page
            #In This case dotn compare with the other dates (only cert dispatched dates)
            if ($cstage == "Certificate Dispatched") {
                if ($this->compareSystemDates($res[0]['cert_dispatched'], $uparray[$z]["date"])) {
                    $dateAltered = true;
                    if (!empty($uparray[$z]["date"])) {
                        $status = "Date Modified by ".DBS.".";
                        $curDate = date('d-m-Y', $uparray[$z]["date"]);
                    } else {
                        $status = "Date Reset by ".DBS.".";
                        $curDate = "";
                    }

                    $memo = "Updated Application - At Certificate Dispatched Stage. " . $status . " Previous Date:" . date('d-m-Y', $res[0]['cert_dispatched']) . "&nbsp;&nbsp;Current Date:" . $curDate;

                    $query = "insert into application_comments (application_id, app_ref_no,comments,entered_by,entered_on,date_revised,move_from_report) values ('$applicationId','$appRefNo','" . $memo . "','-1','$timenow','Y','N')";
                    $result = $this->Query($query);
                }
            } else {
                #Do not check the CRB system dates cos we dont overwrite CRB system date
                #Check rest of the dates
                if ($cstage == "Initial Checking Stage") {
                    if ($this->compareSystemDates($res[0]['initial_check'], $uparray[$z]["date"])) {
                        $dateAltered = true;
                        if (!empty($uparray[$z]["date"])) {
                            $status = "Date Modified by ".DBS.".";
                            $curDate = date('d-m-Y', $uparray[$z]["date"]);
                            $uparray[$z]["column"] = "Modified";
                        } else {
                            $status = "Date Reset by ".DBS.".";
                            $curDate = "";
                            $uparray[$z]["column"] = "Push Back";
                        }

                        $memo = "Updated Application - At Initial Checking Stage. " . $status . " Previous Date:" . date('d-m-Y', $res[0]['initial_check']) . "&nbsp;&nbsp;Current Date:" . $curDate;

                        $query = "insert into application_comments (application_id, app_ref_no,comments,entered_by,entered_on,date_revised,move_from_report) values ('$applicationId','$appRefNo','" . $memo . "','-1','$timenow','Y','N')";
                        $result = $this->Query($query);
                    }
                }

                if ($cstage == "At PNC") {
                    if ($this->compareSystemDates($res[0]['pnc_stage'], $uparray[$z]["date"])) {
                        $dateAltered = true;
                        if (!empty($uparray[$z]["date"])) {
                            $status = "Date Modified by ".DBS.".";
                            $curDate = date('d-m-Y', $uparray[$z]["date"]);
                            $uparray[$z]["column"] = "Modified";
                        } else {
                            $status = "Date Reset by ".DBS.".";
                            $curDate = "";
                            $uparray[$z]["column"] = "Push Back";
                        }

                        $memo = "Updated Application - At PNC Stage. " . $status . " Previous Date:" . date('d-m-Y', $res[0]['pnc_stage']) . "&nbsp;&nbsp;Current Date:" . $curDate;

                        $query = "insert into application_comments (application_id, app_ref_no,comments,entered_by,entered_on,date_revised,move_from_report) values ('$applicationId','$appRefNo','" . $memo . "','-1','$timenow','Y','N')";
                        $result = $this->Query($query);
                    }
                }

                if ($cstage == "At POCA/POCA/List99" || $cstage == "At POCA/POCA/".DCBL) {
                    if ($this->compareSystemDates($res[0]['ppl_stage'], $uparray[$z]["date"])) {
                        $dateAltered = true;
                        if (!empty($uparray[$z]["date"])) {
                            $status = "Date Modified by ".DBS.".";
                            $curDate = date('d-m-Y', $uparray[$z]["date"]);
                            $uparray[$z]["column"] = "Modified";
                        } else {
                            $status = "Date Reset by ".DBS.".";
                            $curDate = "";
                            $uparray[$z]["column"] = "Push Back";
                        }

                        $memo = "Updated Application - At ".DCBL."/".DABL." Stage. " . $status . " Previous Date:" . date('d-m-Y', $res[0]['ppl_stage']) . "&nbsp;&nbsp;Current Date:" . $curDate;

                        $query = "insert into application_comments (application_id, app_ref_no,comments,entered_by,entered_on,date_revised,move_from_report) values ('$applicationId','$appRefNo','" . $memo . "','-1','$timenow','Y','N')";
                        $result = $this->Query($query);
                    }
                }

                if ($cstage == "At LPF") {
                    if ($res[0]['lpfdate'] == 0)
                        $res[0]['lpfdate'] = "";
                    if ($this->compareSystemDates($res[0]['lpfdate'], $uparray[$z]["date"])) {
                        $dateAltered = true;
                        if (!empty($uparray[$z]["date"])) {
                            $status = "Date Modified by ".DBS.".";
                            $curDate = date('d-m-Y', $uparray[$z]["date"]);
                            $uparray[$z]["column"] = "Modified";
                        } else {
                            $status = "Date Reset by ".DBS.".";
                            $curDate = "";
                            $uparray[$z]["column"] = "Push Back";
                        }

                        $memo = "Updated Application - At LPF Stage. " . $status . " Previous Date:" . date('d-m-Y', $res[0]['lpfdate']) . "&nbsp;&nbsp;Current Date:" . $curDate;

                        $query = "insert into application_comments (application_id, app_ref_no,comments,entered_by,entered_on,date_revised,move_from_report) values ('$applicationId','$appRefNo','" . $memo . "','-1','$timenow','Y','N')";
                        $result = $this->Query($query);
                    }
                }

                if ($cstage == "Disclosure printed") {
                    if ($this->compareSystemDates($res[0]['cert_printing'], $uparray[$z]["date"])) {
                        $dateAltered = true;
                        if (!empty($uparray[$z]["date"])) {
                            $status = "Date Modified by ".DBS.".";
                            $curDate = date('d-m-Y', $uparray[$z]["date"]);
                            $uparray[$z]["column"] = "Modified";
                        } else {
                            $status = "Date Reset by ".DBS.".";
                            $curDate = "";
                            $uparray[$z]["column"] = "Push Back";
                        }

                        $memo = "Updated Application - At Disclosure printed Stage. " . $status . " Previous Date:" . date('d-m-Y', $res[0]['cert_printing']) . "&nbsp;&nbsp;Current Date:" . $curDate;

                        $query = "insert into application_comments (application_id, app_ref_no,comments,entered_by,entered_on,date_revised,move_from_report) values ('$applicationId','$appRefNo','" . $memo . "','-1','$timenow','Y','N')";
                        $result = $this->Query($query);
                    }
                }
            }
        }

        if ($dateAltered) {
            #Store previous dates in seperate table
            $lpfdate = $res[0]['lpfdate'];
            $ppl_stage = $res[0]['ppl_stage'];
            $pnc_stage = $res[0]['pnc_stage'];
            $initial_check = $res[0]['initial_check'];
            $cert_dispatched = $res[0]['cert_dispatched'];
            $cert_printing = $res[0]['cert_printing'];

            $query = "insert into crb_revised_date (application_id,app_ref_no, initial_check,pnc_stage,ppl_stage,lpfdate,cert_printing,cert_dispatched,cron_run_time) values ('$applicationId','$appRefNo','$initial_check','$pnc_stage','$ppl_stage','$lpfdate','$cert_printing','$cert_dispatched','$timenow')";
            $result = $this->Query($query);
        }
    }

    #Update Current status function

    public function updateCurrentCRBStatus($uparray, $applicationId, $appRefNo, $updated_stage, $updated_by, $updated_user) {
        $timeNow = time();
        $fieldUpdated = "";
        $inprogress = 0;
        $currentstg = "";
        for ($z = 0; $z < count($uparray); $z++) {
            $cstage = $uparray[$z]["stage"];

            if ($cstage == "CRB System" || $cstage == DBS." System") {
                if (!empty($uparray[$z]["date"]) && $uparray[$z]["date"] > 0) {
                    $query = "update form_status set crb_system='" . $uparray[$z]["date"] . "',last_updated='$timeNow' where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                    $currentstg = "Entered on ".DBS." System";
                    if (!empty($fieldUpdated))
                        $fieldUpdated.=",";
                    $fieldUpdated.="crb_system";
                }else {
                    $query = "update form_status set crb_system = NULL,last_updated='$timeNow' where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                }
            }

            if ($cstage == "Initial Checking Stage") {
                if (!empty($uparray[$z]["date"]) && $uparray[$z]["date"] > 0) {
                    $addQuery = "";
                    if ($uparray[$z]["column"] == "Modified")
                        $addQuery = " , push_back_init_check='M'";

                    $query = "update form_status set initial_check='" . $uparray[$z]["date"] . "',last_updated='$timeNow'" . $addQuery . " where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                    $currentstg = "Initial Checking Stage";
                    if (!empty($fieldUpdated))
                        $fieldUpdated.=",";
                    $fieldUpdated.="initial_check";
                }elseif ($uparray[$z]["column"] == "In Progress") {
                    $currentstg = "Initial Checking Stage";
                    $inprogress = $inprogress + 1;
                    $query = "update form_status set initial_check = NULL,init_check_status = '" . $uparray[$z]["column"] . "',last_updated='$timeNow' where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                } elseif ($uparray[$z]["column"] == "Push Back") {
                    $query = "update form_status set initial_check = NULL,push_back_init_check='Y',init_check_status = '" . $uparray[$z]["column"] . "',last_updated='$timeNow' where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                } else {
                    $query = "update form_status set initial_check = NULL,init_check_status = NULL ,last_updated='$timeNow' where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                }
            }

            if ($cstage == "At PNC") {
                if (!empty($uparray[$z]["date"]) && $uparray[$z]["date"] > 0) {
                    $addQuery = "";
                    if ($uparray[$z]["column"] == "Modified")
                        $addQuery = " , push_back_pnc='M'";

                    $query = "update form_status set pnc_stage='" . $uparray[$z]["date"] . "',last_updated='$timeNow'" . $addQuery . " where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                    $currentstg = "At PNC";
                    if (!empty($fieldUpdated))
                        $fieldUpdated.=",";
                    $fieldUpdated.="pnc_stage";
                }elseif ($uparray[$z]["column"] == "In Progress") {
                    $currentstg = "At PNC";
                    $inprogress = $inprogress + 1;
                    $query = "update form_status set pnc_stage = NULL ,pnc_status='" . $uparray[$z]["column"] . "',last_updated='$timeNow' where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                } elseif ($uparray[$z]["column"] == "Push Back") {
                    $query = "update form_status set pnc_stage = NULL ,push_back_pnc='Y',pnc_status='" . $uparray[$z]["column"] . "',last_updated='$timeNow' where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                } else {
                    $query = "update form_status set pnc_stage = NULL ,pnc_status = NULL,last_updated='$timeNow' where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                }
            }

            if ($cstage == "At POCA/POCA/List99" || $cstage == "At POCA/POCA/".DCBL) {
                if (!empty($uparray[$z]["date"]) && $uparray[$z]["date"] > 0) {
                    $addQuery = "";
                    if ($uparray[$z]["column"] == "Modified")
                        $addQuery = " , push_back_ppl='M'";

                    $query = "update form_status set ppl_stage='" . $uparray[$z]["date"] . "',last_updated='$timeNow'" . $addQuery . " where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                    $currentstg = "At ".DCBL."/".DABL." Stage";
                    if (!empty($fieldUpdated))
                        $fieldUpdated.=",";
                    $fieldUpdated.="ppl_stage";
                }elseif ($uparray[$z]["column"] == "In Progress") {
                    $currentstg = "At ".DCBL."/".DABL." Stage";
                    $inprogress = $inprogress + 1;
                    $query = "update form_status set ppl_stage = NULL,ppl_status='" . $uparray[$z]["column"] . "',last_updated='$timeNow' where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                } elseif ($uparray[$z]["column"] == "Push Back") {
                    $query = "update form_status set ppl_stage = NULL,push_back_ppl='Y',ppl_status='" . $uparray[$z]["column"] . "',last_updated='$timeNow' where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                } else {
                    $query = "update form_status set ppl_stage = NULL,ppl_status= NULL ,last_updated='$timeNow' where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                }
            }

            if ($cstage == "At LPF") {
                if (!empty($uparray[$z]["date"]) && $uparray[$z]["date"] > 0) {
                    $addQuery = "";
                    if ($uparray[$z]["column"] == "Modified")
                        $addQuery = " , push_back_lpf='M'";

                    $query = "update form_status set lpfdate='" . $uparray[$z]["date"] . "',last_updated='$timeNow'" . $addQuery . " where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                    $currentstg = "At LPF";
                    if (!empty($fieldUpdated))
                        $fieldUpdated.=",";
                    $fieldUpdated.="lpfdate";
                }elseif ($uparray[$z]["column"] == "In Progress") {
                    $currentstg = "At LPF";
                    $inprogress = $inprogress + 1;
                    $query = "update form_status set lpfdate='',lpf_status='" . $uparray[$z]["column"] . "',last_updated='$timeNow' where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                } elseif ($uparray[$z]["column"] == "Push Back") {
                    $query = "update form_status set lpfdate='',push_back_lpf='Y',lpf_status='" . $uparray[$z]["column"] . "',last_updated='$timeNow' where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                } else {
                    $query = "update form_status set lpfdate='',lpf_status= NULL,last_updated='$timeNow' where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                }
            }

            if ($cstage == "Disclosure printed") {
                if (!empty($uparray[$z]["date"]) && $uparray[$z]["date"] > 0) {
                    $addQuery = "";
                    if ($uparray[$z]["column"] == "Modified")
                        $addQuery = " , push_back_printing='M'";

                    $query = "update form_status set cert_printing='" . $uparray[$z]["date"] . "',last_updated='$timeNow'" . $addQuery . " where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                    $currentstg = "Requested for Printing";
                    if (!empty($fieldUpdated))
                        $fieldUpdated.=",";
                    $fieldUpdated.="cert_printing";
                }elseif ($uparray[$z]["column"] == "In Progress") {
                    $currentstg = "Requested for Printing";
                    $inprogress = $inprogress + 1;
                    $query = "update form_status set cert_printing = NULL ,cert_printing_status='" . $uparray[$z]["column"] . "',last_updated='$timeNow' where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                } elseif ($uparray[$z]["column"] == "Push Back") {
                    $query = "update form_status set cert_printing = NULL ,push_back_printing='Y',cert_printing_status='" . $uparray[$z]["column"] . "',last_updated='$timeNow' where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                } else {
                    $query = "update form_status set cert_printing = NULL ,cert_printing_status= NULL,last_updated='$timeNow' where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                }
            }

            if ($cstage == "Certificate Dispatched") {
                if (!empty($uparray[$z]["date"]) && $uparray[$z]["date"] > 0) {
                    $query = "update form_status set cert_dispatched='" . $uparray[$z]["date"] . "',last_updated='$timeNow' where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                    $currentstg = "Completed certificate dispatched";
                    if (!empty($fieldUpdated))
                        $fieldUpdated.=",";
                    $fieldUpdated.="cert_dispatched";
                }else {
                    $query = "update form_status set cert_dispatched = NULL,last_updated='$timeNow' where app_ref_no='$appRefNo'";
                    $result = $this->Query($query);
                }
            }

            if (!empty($currentstg)) {
                $query = "update form_status set current_status='$currentstg',in_progress='$inprogress' where app_ref_no='$appRefNo'";
                $result = $this->Query($query);
            }
        }#end inner loop
        #Log the Update details
        if (!empty($fieldUpdated)) {
            $updatedTime = time();
            $query = "insert into update_details (stage,app_ref_no,updated,fields_updated,update_time,script_run_time,updated_by,updated_user) values ('$updated_stage','$appRefNo','Y','$fieldUpdated','$updatedTime','$timeNow','$updated_by',$updated_user)";
            $this->Query($query);
        } else {
            $query = "insert into update_details (stage,app_ref_no,updated,script_run_time,updated_by,updated_user) values ('$updated_stage','$appRefNo','N','$timeNow','$updated_by',$updated_user)";
            $this->Query($query);
        }
    }

    #Show who update dthe system

    public function detailsUpdatedBy($app_ref_no) {
        $query = "select updated_by from update_details where updated_by is not null and app_ref_no='$app_ref_no'  order by update_id desc limit 1";
        $res = $this->getDBRecords($query);

        if (count($res) > 0)
            $updated_by = $res[0]['updated_by'];
        else
            $updated_by="";

        return $updated_by;
    }

}

?>
