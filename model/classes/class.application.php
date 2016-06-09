<?php

class Application extends CommonLib {

    public $CURRENT_SURNAME = 3;
    public $CURRENT_FORNAME = 4;
    public $CURRENT_MIDDLENAME = 9;
    public $CURRENT_ADDRESS = 1;
    public $ADDITIONAL_ADDRESS = 2;
    public $NEWUSER = "newuser";
    public $CURRENTREGUSERSTATE = "Registered Application";
    public $ACCESSTYPE = "CU";
    public $ACCESSLEVEL = "cqcuser";
    public $intWorksChildren = 2;

    function __construct() {
        parent::__construct();
        return true;
    }

    /*
     * Function to fetch available ID Docs
     */

    function getAllIdDocs($category="",$diplayType=null) {
        if (!empty($category))
            $condition = " AND category = '$category'";
        if (!empty($diplayType))
            $display_section = " AND display_section = '$diplayType'";
        $sqlAllIdDocs = "SELECT * FROM id_document WHERE active = 'Y' $condition $display_section ORDER BY doc_value ASC";
        $arrAllIdDocs = $this->getDBRecords($sqlAllIdDocs);
        return $arrAllIdDocs;
    }

    /*
     * Function to insert into req_docs table
     */

    public function initiateReqDocs($documents_selected) {

        $selectedDocument = array();

        $selectedDocument['reqdocs'] = $documents_selected;

        #Insert All Posted Value to the table "reqdocs" table
        $result = $this->Insert("reqdocs", $selectedDocument);

        $query = "select max(id) as rid from reqdocs where (username = '' or username is NULL) and reqdocs='$documents_selected'";
        $res = $this->getDBRecords($query);
        $rid = $res[0]["rid"];

        return $rid;
    }

    public function updateReqDocsUser($euid, $rid) {

        $selectReqDocsUser = "select username from users where user_id='$euid'";
        $resultReqDocsUser = $this->getDBRecords($selectReqDocsUser);

        $arrUpdateReqDocs = array();

        $arrUpdateReqDocs['username'] = $resultReqDocsUser[0]['username'];
        $arrUpdateReqDocs['user_id'] = $euid;


        $condition = " id='$rid' limit 1";

        $updateReqDocs = $this->Update('reqdocs', $arrUpdateReqDocs, $condition);
    }

    public function updateReqDocs($documents_selected, $rid) {
        if ($rid != '') {
            $condition = " id='$rid' limit 1";
            if (!is_array($documents_selected)) {
                $arrUpdateReqDocs = array();
                $arrUpdateReqDocs['reqdocs'] = $documents_selected;
                $updateReqDocs = $this->Update('reqdocs', $arrUpdateReqDocs, $condition);
            } else {
                $updateReqDocs = $this->Update('reqdocs', $documents_selected, $condition);
            }
            return $updateReqDocs;
        }
    }

    public function getReqDocs($rid) {

        $selectReqDocs = "select reqdocs from reqdocs where id='$rid'";
        $resultReqDocs = $this->getDBRecords($selectReqDocs);

        $reqs = $resultReqDocs[0]["reqdocs"];

        return $reqs;
    }

    public function getSelectedDocument($doc_value) {
        $value = array();
        $query = "select * from id_document where doc_value='$doc_value'";
        $res = $this->getDBRecords($query);

        for ($i = 0; $i < count($res); $i++) {
            $value["doc_id"] = $res[0]['doc_id'];
            $value["doc_value"] = $res[0]['doc_value'];
            $value["doc_name"] = $this->correctstring($res[0]['doc_name']);
            $value["doc_category"] = $res[0]['category'];
            $value["doc_address"] = $res[0]['address'];
        }
        return $value;
    }

    function searchApplication($arrParam,$certseen,$certnotseen) {

        $allapp = array();
        $j = 0;
        $responseMessage = "";

        if (!empty($arrParam['app_surname']) || !empty($arrParam['app_forename'])) {
            #------------------Query for Application Submited [Application + ID section completed]--------------------------
            $listComp = $this->getChildCompanies($arrParam['company_id']);
            $orgId = $arrParam['orgId'];
            $commonQuery = "SELECT
                                    DISTINCT ap.ebulkapp,ap.application_id, o.name oname,o.org_id,o.povafirst,o.list99 list99req,c.company_id, c.name cname, sx.app_ref_no, sx.pova pova,sx.id_verified_on, sx.list99 list99,fs.certno, fs.formstatus, fs.`p&sDate` psdt, fs.`s&rDate` srdt, fs.dCrbDate dcrbdt, fs.current_status,fs.rRDate rrdt, fs.povaStart povaStart,fs.povaEnd povaEnd, fs.povaResult povaResult,fs.lpfdate, fs.list99Start list99start,fs.list99End list99End,fs.list99Result list99Result,ap.application_id, ap.cancelled,sx.discType,sx.category_code,sx.remuneration,ap.sectionx_complete_time,ap.submit_time ,fs.last_updated,ap.manual_application as manualApp,fs.recruitment_decision, upper(pnf.name) as UserForename,fs.result, upper(pns.name) as UserSurname,u.unique_key as uniqueRefNo,ur.orgname,ur.answer,sq.question,j.job,u.password,fs.certificate_seen_date csdate,fs.disc_issue_date,fs.info_requested_by_adl,fs.info_requested_by_dbs
                          FROM
                                    organisation o,company c, applications ap
                                    INNER JOIN app_person_name apnf ON ap.application_id = apnf.application_id
                                    INNER JOIN person_names pnf ON apnf.name_id = pnf.name_id
                                    AND pnf.name_type_id =4
                                    INNER JOIN app_person_name apns ON ap.application_id = apns.application_id
                                    INNER JOIN person_names pns ON apns.name_id = pns.name_id
                                    AND pns.name_type_id =3
                                    left outer join sectionx sx on ap.application_id=sx.application_id
                                    left outer join form_status fs on {JOINEXPR}
                                    inner join reqdocs rqd on ap.application_id = rqd.app_id inner join users u on rqd.user_id = u.user_id
                                    inner join user_registration ur on u.unique_key = ur.username 
                                    left join security_questions sq on ur.securityQuestion = sq.id
                                    inner join jobs j on ur.job_role = j.sno
                                    
                          WHERE
                                    ap.submit_time IS NOT NULL and o.activated='Y' and ap.org_id=o.org_id and o.company_id=c.company_id and ap.cancelled <> 'Y' $fetchDateAfter8thJune and c.company_id in ($listComp)
            ";

            $cdob = ereg_replace("/", "", $arrParam['app_dob']);
            if (isset($arrParam['app_dob']) && !empty($arrParam['app_dob']))
                $commonQuery.=" and ap.date_of_birth='$cdob'";

            $commonQuery.=" and pnf.name like '%" . $arrParam['app_forename'] . "%'";

            $commonQuery.=" and pns.name like '%" . $arrParam['app_surname'] . "%'";



            if (!empty($arrParam['app_email']))
                $commonQuery.=" and ur.email='" . addslashes($arrParam['app_email']) . "'";

            $commonQuery.=" {EBULKCONDITION}";

            #Query for normal applications - printed
            $joinexpr = "fs.app_ref_no=sx.app_ref_no";
            $ebulkconfition = "and ap.ebulkapp <> 'Y'";
            $content_var = array("{JOINEXPR}", "{EBULKCONDITION}");
            $content_value = array($joinexpr, $ebulkconfition);
            $pquery = str_replace($content_var, $content_value, $commonQuery);

            #Query for Ebulk applications - Not printed
            $joinexpr = "fs.application_id=sx.application_id";
            $ebulkconfition = "and ap.ebulkapp = 'Y'";
            $content_var = array("{JOINEXPR}", "{EBULKCONDITION}");
            $content_value = array($joinexpr, $ebulkconfition);
            $equery = str_replace($content_var, $content_value, $commonQuery);

            $query = $pquery . " UNION " . $equery . " ORDER by UserForename,UserSurname asc";
            $res = $this->getDBRecords($query);

            #---------------------------------------------Query for E-Invitation Sent-------------------------------
            /*$queryForEInvitation = "SELECT o.org_id,u.name as forename,u.surname as surname,u.user_id,u.access_level,u.passReminder,u.answer ,lo_users.createdate, upper(u.name) as UserForename, upper(u.surname) as UserSurname,u.unique_key as uniqueRefNo,ur.orgname,ur.answer,sq.question,j.job,u.password
	             FROM users u, org_users orgusr, organisation o,company c,lo_users,user_registration ur,security_questions sq,jobs j WHERE o.company_id=c.company_id and  u.access_level = 'cqcuser' AND u.user_id = orgusr.user_id AND o.org_id = orgusr.org_id AND u.active = 'Y' AND u.used = 'N' AND u.archived<>'Y'  AND u.user_id NOT IN (SELECT user_id FROM reqdocs) and lo_users.user_id=u.user_id and o.activated='Y' and u.unique_key = ur.username and  ur.securityQuestion = sq.id and ur.job_role = j.sno";*/
            
            $queryForEInvitation = "SELECT o.org_id,u.name as forename,u.surname as surname,u.user_id,u.access_level,u.passReminder,u.answer ,lo_users.createdate, upper(u.name) as UserForename, upper(u.surname) as UserSurname,u.unique_key as uniqueRefNo,ur.orgname,ur.answer,sq.question,j.job,u.password
	             FROM 
                     users u 
                     inner join org_users orgusr on u.user_id = orgusr.user_id
                     inner join organisation o on orgusr.org_id = o.org_id and o.activated='Y' 
                     inner join company c on o.company_id = c.company_id
                     inner join lo_users on u.user_id = lo_users.user_id
                     inner join user_registration ur on u.unique_key = ur.username 
                     inner join jobs j on ur.job_role = j.sno 
                     left join security_questions sq on ur.securityQuestion = sq.id
                     WHERE 
                     u.access_level = 'cqcuser' AND u.active = 'Y' AND u.used = 'N' AND u.archived<>'Y'  AND u.user_id NOT IN (SELECT user_id FROM reqdocs) ";

            $queryForEInvitation.= " and c.company_id in ($listComp)";

            if (!empty($orgId))
                $queryForEInvitation.=" and o.org_id='$orgId'";

            if (!empty($arrParam['app_surname']))
                $queryForEInvitation.=" AND u.surname like '%" . $arrParam['app_surname'] . "%'";

            if (!empty($arrParam['app_forename']))
                $queryForEInvitation.=" and u.name like '%" . $arrParam['app_forename'] . "%'";

            $cdob = ereg_replace("/", "", $arrParam['app_dob']);
            if (!empty($app_dob))
                $queryForEInvitation.=" and lo_users.dob='$cdob'";

            if (!empty($arrParam['app_email']))
                $queryForEInvitation.=" and ur.email='" . addslashes($arrParam['app_email']) . "'";

            $queryForEInvitation.="  ORDER by UserForename,UserSurname asc ";
  
            $res2 = $this->getDBRecords($queryForEInvitation);

            #---------------------------------------Query for Pending ID Check-------------------------------------
            $queryPending = "select  o.org_id,u.user_id,u.name as forename,u.surname as surname,u.access_level,u.passReminder,u.answer, r.id as rid , r.reqdocs,r.datentime,lo_users.createdate, upper(u.name) as UserForename, upper(u.surname) as UserSurname,u.unique_key as uniqueRefNo,ur.orgname,ur.answer,sq.question,j.job,u.password
	            from organisation o,company co,users u,lo_users,reqdocs r,
                        user_registration ur,security_questions sq,jobs j 
				where lo_users.org_id=o.org_id
				and u.user_id=lo_users.user_id
				and r.user_id=u.user_id
                                and u.unique_key = ur.username
                                and  ur.securityQuestion = sq.id
                                and ur.job_role = j.sno
				and u.access_level='" . $this->ACCESSLEVEL . "'
			    and u.active='Y'
				and r.app_id='0'
				and u.archived<>'Y'
				and o.company_id=co.company_id and o.activated='Y' and  co.company_id in ($listComp) ";
            if (!empty($orgId))
                $queryPending.=" and o.org_id='$orgId'";
            else if (!empty($branchQuery))
                $queryPending.= $branchQuery;

            if (!empty($arrParam['app_surname']))
                $queryPending.=" AND u.surname like '" . $arrParam['app_surname'] . "%'";

            if (!empty($arrParam['app_surname']))
                $queryPending.=" and u.name like '" . $arrParam['app_forename'] . "%'";
                
            if (!empty($arrParam['app_email']))
                $queryPending.=" and ur.email like '" . addslashes(addslashes($arrParam['app_email'])) . "%'";
           
		
            $cdob = ereg_replace("/", "", $arrParam['app_dob']);
            if (!empty($app_dob))
                $queryPending.=" and lo_users.dob='$cdob'";
                
           
           
            $queryPending.="  ORDER by UserForename,UserSurname asc ";

            $res1 = $this->getDBRecords($queryPending);

            for ($i = 0; $i < count($res); $i++) {
                $surName = $this->getApplicantName($res[$i]["application_id"], $this->CURRENT_SURNAME);
                $forName = $this->getApplicantName($res[$i]["application_id"], $this->CURRENT_FORNAME);

                $applicantName = $this->correctcase($forName[0]['name']) . " " . $this->correctcase($surName[0]['name']);

                $eBulkApplication = $res[$i]["ebulkapp"];
                $orgid = $res[$i]["org_id"];
                $userid = "";
                $rid = "";
                $applicationid = $res[$i]["application_id"];
                $app_ref_no = $res[$i]["app_ref_no"];
                $povaFlag = $res[$i]["povafirst"];
                $list99org = $res[$i]["list99"];
                $manualApplicationRes = $res[$i]["manualApp"];
                $recruitment_decision = $res[$i]["recruitment_decision"];
                $uniqueRefNo = $res[$i]["uniqueRefNo"];
                $orgname = $res[$i]["orgname"];
                $answer = $res[$i]["answer"];
                $question = $res[$i]["question"];
                $job = $res[$i]["job"];
                $password = $res[$i]["password"];

                #Populate current stage
                $appatstage = "";
                $current_stage = "N/A";
                $lastUpdated = "";

                if ($eBulkApplication <> 'Y') {
                    if (!empty($res[$i]["submit_time"]) && empty($res[$correctcasei]["psdt"])) {
                        $lastUpdated = date("d/m/Y", $res[$i]["submit_time"]);
                        $appatstage = "appsubmited";
                        $current_stage = "Application Submitted";
                    } elseif (!empty($res[$i]["psdt"]) && empty($res[$i]["srdt"])) {
                        $lastUpdated = date("d/m/Y", $res[$i]["psdt"]);
                        $appatstage = "printedandsent";
                        $current_stage = "Printed & Sent";
                    } elseif (empty($res[$i]["dcrbdt"]) && !empty($res[$i]["srdt"])) {
                        $lastUpdated = date("d/m/Y", $res[$i]["srdt"]);
                        $appatstage = "indispatchqueue";
                        $current_stage = "In Dispatch Queue";
                    } elseif (!empty($res[$i]["dcrbdt"]) && empty($res[$i]["rrdt"])) {
                        $lastUpdated = date("d/m/Y", $res[$i]["dcrbdt"]);
                        $appatstage = "dispatchedtocrb";

                        #If alreday dispatched to CRB and the application has moved to different stage within CRB
                        if (!empty($res[$i]["current_status"]) && $res[$i]["current_status"] <> "")
                            $current_stage = trim($res[$i]["current_status"]);
                        else
                            $current_stage="Dispatched to ".DBS;

                        if (!empty($res[$i]["last_updated"]))
                            $lastUpdated = date("d/m/Y", $res[$i]["last_updated"]);
                    }elseif ((!empty($res[$i]["rrdt"]) && ($res[$i]["rrdt"] != 'NULL'))) {
                       
                       
                        $lastUpdated = date("d/m/Y", $res[$i]["rrdt"]);
                        $appatstage = "resultreceived";
                        if ($res[$i]["rrdt"] < PDFRESULT_DATE) {
                             $current_stage=$res[$i]["result"];
                        } else {
                              $cert_Seen = $this->appendCertSeenStatus(VERIFY_CERTIFICATE,$res[$i]['result'],$res[$i]["csdate"],$certseen,$certnotseen);
                                if(!empty($cert_Seen))
                                    $cert_Seen=" - ".$cert_Seen;
                                $current_stage=$res[$i]["result"].$cert_Seen;
                        }
                        if ($res[$i]["result"] == 'Invalid Disclosure')
                            $current_stage = "Invalid Disclosure";
                    }
                }
                else {
                    if (!empty($res[$i]["submit_time"]) && empty($res[$i]["sectionx_complete_time"])) {
                        $lastUpdated = date("d/m/Y", $res[$i]["submit_time"]);
                        $appatstage = "appsubmited";
                         if($res[0]["info_requested_by_adl"] == "Y") $current_status=INFO_REQUESTED_STATUS;
	                                    else $current_status="Application Submitted";
                    } elseif (!empty($res[$i]["psdt"]) && empty($res[$i]["srdt"])) {
                        $lastUpdated = date("d/m/Y", $res[$i]["psdt"]);
                         if($res[$i]["info_requested_by_adl"] == "Y") {
                                            $appatstage="countersigned";
                                            $current_stage=INFO_REQUESTED_STATUS;
                                        }else{
                                            $appatstage="countersigned";	
                                            $current_stage="Countersigned";
                                        }
                    } elseif (empty($res[$i]["dcrbdt"]) && !empty($res[$i]["srdt"])) {
                        $lastUpdated = date("d/m/Y", $res[$i]["srdt"]);
                        $appatstage = "indispatchqueue";
                        $current_stage = "In Dispatch Queue";
                    } elseif (!empty($res[$i]["dcrbdt"]) && empty($res[$i]["rrdt"])) {
                        $lastUpdated = date("d/m/Y", $res[$i]["dcrbdt"]);
                        $appatstage = "dispatchedtocrb";

                       if($res[$i]["info_requested_by_dbs"] == "Y") 
                                            $current_stage=INFO_REQUESTED_STATUS_DBS;
					elseif(!empty($res[$i]["current_status"]) && $res[$i]["current_status"]<>"") 
                                            $current_stage= trim($res[$i]["current_status"]);
					else 
                                            $current_stage="Dispatched to ".DBS;

                        if (!empty($res[$i]["last_updated"]))
                            $lastUpdated = date("d/m/Y", $res[$i]["last_updated"]);
                    }
                    elseif ((!empty($res[$i]["rrdt"]) && ($res[$i]["rrdt"] != 'NULL'))) {
                       
                        $lastUpdated = date("d/m/Y", $res[$i]["rrdt"]);
                        $appatstage = "resultreceived";
                        if ($res[$i]["rrdt"] < PDFRESULT_DATE) {
                            $current_stage=$res[$i]["result"];
                        } else {
                             $cert_Seen = $this->appendCertSeenStatus(VERIFY_CERTIFICATE,$res[$i]['result'],$res[$i]["csdate"],$certseen,$certnotseen);
                                    if(!empty($cert_Seen))
                                        $cert_Seen=" - ".$cert_Seen;
                                    $current_stage=$res[$i]["result"].$cert_Seen;
                        }
                        if ($res[$i]["result"] == 'Invalid Disclosure')
                            $current_stage = "Invalid Disclosure";
                    }
                }
                $lastUpdated_dcrbdt = "";
                $lastUpdated_rrdate = "";
                $lastUpdated_lpf = "";
                $lastUpdated_idcheck = "";
                $lastUpdated_eApplication = "";
                $disclosureReceivedDate = "";
                $resultReceivedDate = "";

                if ((!empty($res[$i]["lpfdate"]) && ($res[$i]["lpfdate"] != 'NULL'))) {
                    $lastUpdated_lpf = date("d/m/Y", $res[$i]["lpfdate"]);
                }
                if ((!empty($res[$i]["sectionx_complete_time"]) && ($res[$i]["sectionx_complete_time"] != 'NULL'))) {
                    $lastUpdated_idcheck = date("d/m/Y", $res[$i]["sectionx_complete_time"]);
                }
                if ((!empty($res[$i]["submit_time"]) && ($res[$i]["submit_time"] != 'NULL'))) {
                    $lastUpdated_eApplication = date("d/m/Y", $res[$i]["submit_time"]);
                }
                if ((!empty($res[$i]["dcrbdt"]) && ($res[$i]["dcrbdt"] != 'NULL'))) {
                    $lastUpdated_dcrbdt = date("d/m/Y", $res[$i]["dcrbdt"]);
                    $disclosureReceivedDate = $res[$i]["dcrbdt"];
                }
                if ((!empty($res[$i]["rrdt"]) && ($res[$i]["rrdt"] != 'NULL'))) {
                    $lastUpdated_rrdate = date("d/m/Y", $res[$i]["rrdt"]);
                    $resultReceivedDate = $res[$i]["rrdt"];
                }

                // Pova request
                if ($res[$i]["pova"] == "Y") {
                    if (!empty($res[$i]["povaStart"]) && $res[$i]["povaStart"] <> "NULL") {
                        $todisp = "Requested";
                    } else {
                        $todisp = "Requested";
                    }
                    $today = time();
                    $atime = $res[$i]["povaStart"] + 172800;

                    if (($today > $atime && $res[$i]["povaStart"] <> "NULL" && !empty($res[$i]["povaStart"])) || $res[$i]["povaResult"] == "Pova_sent" || $res[$i]["povaResult"] == "E" || $res[$i]["povaResult"] == "Application_not_received" || $res[$i]["povaResult"] == "D") {
                        $todisp = "Requested";
                    }

                    if (!empty($res[$i]["povaEnd"]) && $res[$i]["povaEnd"] <> "NULL" && $res[$i]["povaResult"] <> 'Pova_sent' && $res[$i]["povaResult"] <> "E" && $res[$i]["povaResult"] <> "Application_not_received" && $res[$i]["povaResult"] <> "N" && $res[$i]["povaResult"] <> "D") {
                        $todisp = "Completed";
                    }
                } //if condition ends for pova
                else {
                    $todisp = "Not Requested";
                } //else condition ends for pova
                // List99 request
                if ($res[$i]["list99"] == 'Y') {
                    if (!empty($res[$i]["list99End"])) {
                        $list99status = "Completed";
                    } else {
                        $list99status = "Requested";
                    }
                } else {
                    $list99status = "Not Requested";
                }//List99 condition ends

                $allapp[$j]['applicantName'] = $applicantName;
                $allapp[$j]['status'] = $current_stage;
                $allapp[$j]['orgid'] = $orgid;
                $allapp[$j]['userid'] = $userid;
                $allapp[$j]['applicationId'] = $applicationid;
                $allapp[$j]['app_ref_no'] = $app_ref_no;
                $allapp[$j]['appatstage'] = $appatstage;
                $allapp[$j]['rid'] = $rid;
                $allapp[$j]['lastUpdated'] = $lastUpdated;
                $allapp[$j]['lastUpdated_einvite'] = "";
                $allapp[$j]['lastUpdated_idcheck'] = $lastUpdated_idcheck;
                $allapp[$j]['lastUpdated_lpf'] = $lastUpdated_lpf;
                $allapp[$j]['lastUpdated_rrdate'] = $lastUpdated_rrdate;
                $allapp[$j]['lastUpdated_dcrbdt'] = $lastUpdated_dcrbdt;
                $allapp[$j]['lastUpdated_eApplication'] = $lastUpdated_eApplication;
                $allapp[$j]['access_level'] = "";
                $allapp[$j]['passreminder'] = "";
                $allapp[$j]['answer'] = "";
                $allapp[$j]['eBulkApplication'] = $eBulkApplication;
                $allapp[$j]['povaReq'] = $povaFlag;
                $allapp[$j]['povastatus'] = $todisp;
                $allapp[$j]['list99req'] = $list99org;
                $allapp[$j]['list99status'] = $list99status;
                $allapp[$j]['resultRecivedDate'] = $resultReceivedDate;
                $allapp[$j]['disclosureRecivedDate'] = $disclosureReceivedDate;
                $allapp[$j]['manualApp'] = $manualApplicationRes;
                $allapp[$j]['recruitment_decision'] = $recruitment_decision;
                $allapp[$j]['uniqueRefNo'] = $uniqueRefNo;
                $allapp[$j]['orgname'] = $orgname;
                $allapp[$j]['answer'] = $answer;
                $allapp[$j]['question'] = $question;
                $allapp[$j]['job'] = $job;
                $allapp[$j]['password'] = $password;


                $j++;
            }
            for ($i = 0; $i < count($res1); $i++) {

                $applicantName = $this->correctcase($res1[$i]["forename"]) . " " . $this->correctcase($res1[$i]["surname"]);
                $orgid = $res1[$i]["org_id"];
                $userid = $res1[$i]["user_id"];
                $rid = $res1[$i]["rid"];
                $applicationid = "";
                $appatstage = "pendingid";
                $current_stage = "Pending ID Check";
                $uniqueRefNo = $res1[$i]["uniqueRefNo"];
                $orgname = $res1[$i]["orgname"];
                $answer = $res1[$i]["answer"];
                $question = $res1[$i]["question"];
                $job = $res1[$i]["job"];
                $password = $res1[$i]["password"];

                $lastUpdated = "";
                if (!empty($res1[$i]["datentime"])) {
                    $year = substr($res1[$i]["datentime"], 0, 4);
                    $month = substr($res1[$i]["datentime"], 5, 2);
                    $day = substr($res1[$i]["datentime"], 8, 2);
                    $lastUpdated = $day . "/" . $month . "/" . $year;
                }

                if (!empty($res1[$i]["createdate"])) {
                    $lastUpdated_einvite = date("d/m/Y", $res1[$i]["createdate"]);
                }

                $allapp[$j]['applicantName'] = $applicantName;
                $allapp[$j]['status'] = $current_stage;
                $allapp[$j]['orgid'] = $orgid;
                $allapp[$j]['userid'] = $userid;
                $allapp[$j]['applicationId'] = $applicationid;
                $allapp[$j]['app_ref_no'] = "";
                $allapp[$j]['appatstage'] = $appatstage;
                $allapp[$j]['rid'] = $rid;
                $allapp[$j]['lastUpdated'] = $lastUpdated;
                $allapp[$j]['lastUpdated_einvite'] = $lastUpdated_einvite;
                $allapp[$j]['lastUpdated_idcheck'] = "";
                $allapp[$j]['lastUpdated_dcrbdt'] = "";
                $allapp[$j]['lastUpdated_rrdate'] = "";
                $allapp[$j]['lastUpdated_lpf'] = "";
                $allapp[$j]['lastUpdated_eApplication'] = "";
                $allapp[$j]['access_level'] = "";
                $allapp[$j]['passreminder'] = "";
                $allapp[$j]['answer'] = "";
                $allapp[$j]['eBulkApplication'] = "Y";
                $allapp[$j]['povaReq'] = 'N';
                $allapp[$j]['povastatus'] = '';
                $allapp[$j]['list99req'] = 'N';
                $allapp[$j]['list99status'] = "";
                $allapp[$j]['uniqueRefNo'] = $uniqueRefNo;
                $allapp[$j]['orgname'] = $orgname;
                $allapp[$j]['answer'] = $answer;
                $allapp[$j]['question'] = $question;
                $allapp[$j]['job'] = $job;
                $allapp[$j]['password'] = $password;
                $j++;
            }

            for ($i = 0; $i < count($res2); $i++) {
                $applicantName = $this->correctcase($res2[$i]["forename"]) . " " . $this->correctcase($res2[$i]["surname"]);
                $orgid = $res2[$i]["org_id"];
                $userid = $res2[$i]["user_id"];
                $applicationid = "";
                $appatstage = $this->NEWUSER;
                $current_stage = $this->CURRENTREGUSERSTATE;
                $uniqueRefNo = $res2[$i]["uniqueRefNo"];
                $orgname = $res2[$i]["orgname"];
                $answer = $res2[$i]["answer"];
                $question = $res2[$i]["question"];
                $job = $res2[$i]["job"];
                $password = $res2[$i]["password"];

                $lastUpdated = "";
                if (!empty($res2[$i]["createdate"])) {
                    $lastUpdated = date("d/m/Y", $res2[$i]["createdate"]);
                    $lastUpdated_einvite = date("d/m/Y", $res2[$i]["createdate"]);
                }

                $allapp[$j]['applicantName'] = $applicantName;
                $allapp[$j]['status'] = $current_stage;
                $allapp[$j]['orgid'] = $orgid;
                $allapp[$j]['userid'] = $userid;
                $allapp[$j]['applicationId'] = $applicationid;
                $allapp[$j]['app_ref_no'] = "";
                $allapp[$j]['appatstage'] = $appatstage;
                $allapp[$j]['rid'] = "";
                $allapp[$j]['lastUpdated'] = $lastUpdated;
                $allapp[$j]['lastUpdated_einvite'] = $lastUpdated_einvite;
                $allapp[$j]['lastUpdated_idcheck'] = "";
                $allapp[$j]['lastUpdated_dcrbdt'] = "";
                $allapp[$j]['lastUpdated_rrdate'] = "";
                $allapp[$j]['lastUpdated_lpf'] = "";
                $allapp[$j]['lastUpdated_eApplication'] = "";
                $allapp[$j]['access_level'] = "";
                $allapp[$j]['passreminder'] = "";
                $allapp[$j]['answer'] = "";
                $allapp[$j]['eBulkApplication'] = "Y";
                $allapp[$j]['povaReq'] = "N";
                $allapp[$j]['povastatus'] = "";
                $allapp[$j]['list99req'] = "N";
                $allapp[$j]['list99status'] = "";
                $allapp[$j]['uniqueRefNo'] = $uniqueRefNo;
                $allapp[$j]['orgname'] = $orgname;
                $allapp[$j]['answer'] = $answer;
                $allapp[$j]['question'] = $question;
                $allapp[$j]['job'] = $job;
                $allapp[$j]['password'] = $password;
                $j++;
            }
        }

        return $allapp;
    }

    function correctcase($str) {
        return $str;
    }

    # get the Month  Drop down list.

    function getNationalityDropdownList($name, $initVal="", $js="") {
        $dList = "<SELECT id=\"$name\" name=\"$name\" $js class=\"select\"><option value=\"\">Select from List</option>";
        $dList.="<option value=\"British\">British</option>";
        $nationalities = $this->getDBRecords("select label as name,value as id from nationality order by label");
        for ($i = 0; $i < count($nationalities); $i++) {

            if (rtrim($initVal) == rtrim($nationalities[$i]["id"])) {
                $sel = "selected";
            }
            else
                $sel=null;
            $dList.= "<option $sel value=\"" . rtrim($nationalities[$i]["id"]) . "\">" . $nationalities[$i]["name"] . "</option>";
        }
        $dList.= "</SELECT>";

        return $dList;
    }

    function getCountersignStats($arrParam,$counter_sign_all) {
        $arrCountersignApps = array();
         
       
        $listComp = $this->getChildCompanies($arrParam['company_id']);
        if($counter_sign_all != 'Y') $deptAccess = ' AND b.company_id IN ('.$listComp.') ';
        $sqlPassApps = "SELECT
                                COUNT(a.application_id) AS totalapps
                        FROM
                                (applications a, organisation b, company c,sectionx s)
        LEFT JOIN log_work_at_home_modified lwahm ON a.application_id = lwahm.application_id
                        WHERE
                                a.org_id = b.org_id AND a.good_to_print = 'S' AND a.cancelled = 'N' AND  a.cnt_sign_local = 'N' AND b.activated='Y'
                                AND c.company_id = b.company_id AND a.application_id = s.application_id and (s.workingathomeaddress = 'N' or lwahm.application_id IS NOT NULL)  $deptAccess";
        $rsPassApps = $this->getDBRecords($sqlPassApps);

        $sqlFailByName = " SELECT
                                    COUNT(app.application_id) AS totalapps
                          FROM
                                    applications app ,sectionx secx,organisation b, company c
                          WHERE
                                    app.application_id = secx.application_id AND app.cancelled = 'N' AND app.good_to_print IN ('B','E','Q') AND app.cnt_sign_local = 'N' AND
                                    app.pulled_stage = 'Names' AND b.activated = 'Y' AND 
                                    app.org_id = b.org_id and c.company_id = b.company_id $deptAccess ";
        $rsFailByName = $this->getDBRecords($sqlFailByName);

        $sqlFailByDOB = "SELECT
                                    COUNT(app.application_id) AS totalapps
                          FROM
                                    applications app ,sectionx secx,organisation b, company c
                          WHERE
                                    app.application_id = secx.application_id AND app.cancelled = 'N' AND  app.good_to_print IN ('B','E','Q') AND app.cnt_sign_local = 'N' AND
                                    app.pulled_stage = 'Place Of Birth' AND b.activated = 'Y' AND 
                                    app.org_id = b.org_id and c.company_id = b.company_id $deptAccess";
        $rsFailByDOB = $this->getDBRecords($sqlFailByDOB);

        $sqlFailByAddress = "SELECT
                                    COUNT(app.application_id) AS totalapps
                             FROM
                                    applications app ,sectionx secx,organisation b, company c
                             WHERE
                                    app.application_id = secx.application_id AND app.cancelled = 'N' AND  (app.good_to_print IN ('B','E','Q') OR  app.address_check_good IN ('B','E','Q')) AND app.cnt_sign_local = 'N' AND
                                    app.pulled_stage = 'Addresses' AND b.activated = 'Y' AND 
                                    app.org_id = b.org_id and c.company_id = b.company_id $deptAccess ";
        $rsFailByAddress = $this->getDBRecords($sqlFailByAddress);

        $sqlFailByID = " SELECT
                                    COUNT(app.application_id) AS totalapps
                          FROM
                                    applications app ,sectionx secx,organisation b, company c
                          WHERE
                                    app.application_id = secx.application_id AND app.cancelled = 'N' AND  app.good_to_print IN ('B','E','Q') AND app.cnt_sign_local = 'N' AND
                                    app.pulled_stage = 'ID' AND b.activated = 'Y' AND 
                                    app.org_id = b.org_id and c.company_id = b.company_id $deptAccess ";
        $rsFailByID = $this->getDBRecords($sqlFailByID);

        // Working from Home Address Query
$query="select count(applications.application_id) as totalapps from applications, organisation b, company c,sectionx s,log_work_at_home_modified lwahm
        where applications.org_id=b.org_id and b.activated='Y' and good_to_print='S' and applications.cnt_sign_local='N' and address_check_good='Y'
        and c.company_id=b.company_id and applications.application_id=s.application_id and s.workingathomeaddress='Y' and applications.application_id = lwahm.application_id $deptAccess ";
$rsWorkAtHomeSuc=$this->getDBRecords($query);

$query="select count(applications.application_id) as totalapps from (applications, organisation b, company c,sectionx s)
        LEFT JOIN log_work_at_home_modified lwahm ON applications.application_id = lwahm.application_id
        where applications.org_id=b.org_id and b.activated='Y' and good_to_print='S' and applications.cnt_sign_local='N'
        and c.company_id=b.company_id and applications.application_id=s.application_id and s.workingathomeaddress='Y' and lwahm.application_id is null $deptAccess ";
$rsWorkAtHomeUnSuc=$this->getDBRecords($query);

        $arrCountersignApps['totCorrectApps'] = $rsPassApps[0]["totalapps"];
        $arrCountersignApps['totNamesApps'] = $rsFailByName[0]["totalapps"];
        $arrCountersignApps['totBirthApps'] = $rsFailByDOB[0]["totalapps"];
        $arrCountersignApps['totAddressApps'] = $rsFailByAddress[0]["totalapps"];
        $arrCountersignApps['totIdApps'] = $rsFailByID[0]["totalapps"];
        $arrCountersignApps['totalWorkingFromHome'] = $rsWorkAtHomeSuc[0]["totalapps"];
        $arrCountersignApps['totalWorkingFromHomeReqPreVer'] = $rsWorkAtHomeUnSuc[0]["totalapps"];

        if (empty($arrCountersignApps['totCorrectApps']))
            $arrCountersignApps['totCorrectApps'] = 0;
        if (empty($arrCountersignApps['totNamesApps']))
            $arrCountersignApps['totNamesApps'] = 0;
        if (empty($arrCountersignApps['totBirthApps']))
            $arrCountersignApps['totBirthApps'] = 0;
        if (empty($arrCountersignApps['totAddressApps']))
            $arrCountersignApps['totAddressApps'] = 0;
        if (empty($arrCountersignApps['totIdApps']))
            $arrCountersignApps['totIdApps'] = 0;
        if (empty($arrCountersignApps['totalWorkingFromHome']))
            $arrCountersignApps['totalWorkingFromHome'] = 0;
        if (empty($arrCountersignApps['totalWorkingFromHomeReqPreVer']))
            $arrCountersignApps['totalWorkingFromHomeReqPreVer'] = 0;

        $arrCountersignApps['totFailApps'] = $arrCountersignApps['totNamesApps'] + $arrCountersignApps['totBirthApps'] + $arrCountersignApps['totAddressApps'] + $arrCountersignApps['totIdApps']+$arrCountersignApps['totalWorkingFromHomeReqPreVer'];

        return $arrCountersignApps;
    }

    function getCountersignatoryInfo($username) {
        $arrCounterSignInfo = array();
        $adminid = $this->getUserId($username);
        $query = "SELECT * FROM ebulk_counter_signatory WHERE user_id='$adminid'";
        $user = $this->getDBRecords($query);
        $arrCounterSignInfo['countersignName'] = $user[0]["counter_signatory_name"];
        $arrCounterSignInfo['countersignNo'] = $user[0]["counter_signatory_number"];
        $arrCounterSignInfo['countersignId'] = $user[0]["counter_signatory_id"];
        $arrCounterSignInfo['adminId'] = $adminid;
        $arrCounterSignInfo['company_id'] = $_SESSION['company_id_C'];
        return $arrCounterSignInfo;
    }

    function countersignApps($arrParam,$counter_sign_all) {
        $time = time();
        $listComp = $this->getChildCompanies($arrParam['company_id']);
        if($counter_sign_all != 'Y')  $deptAccess = 'AND b.company_id IN ('.$listComp.')';
        $sqlCounterSignApps = "SELECT a.application_id 
        FROM (applications a, sectionx s,organisation b, company c) 
        LEFT JOIN log_work_at_home_modified lwahm ON a.application_id = lwahm.application_id
        WHERE a.application_id = s.application_id AND  a.good_to_print = 'S' AND a.address_check_good='Y'  AND a.cnt_sign_local = 'N' AND s.checked_by <> '' AND b.activated='Y' AND a.org_id = b.org_id AND c.company_id = b.company_id AND (s.workingathomeaddress = 'N' OR lwahm.application_id IS NOT NULL)  $deptAccess ";
        $rsCountersignApps = $this->getDBRecords($sqlCounterSignApps);

        $totalApp = count($rsCountersignApps);
        for ($i = 0; $i < $totalApp; $i++) {
            $sqlUpdateApp = " UPDATE
                                    applications SET good_to_print = 'Y',print_hold='$time',good_updated_by='" . $arrParam['adminId'] . "',release_date='$time',
                                    released_by='" . $arrParam['adminId'] . "', app_countersigned_by='" . $arrParam['adminId'] . "'
                              WHERE
                                    good_to_print = 'S' AND address_check_good = 'Y' AND applications.application_id = '" . $rsCountersignApps[$i]['application_id'] . "'";
            $this->Query($sqlUpdateApp);

            $fieldArray = array('disc_application_id' => $rsCountersignApps[$i]['application_id'], 'xml_generated_yn' => 0, 'xml_error' => 0, 'xml_content' => '', 'sent_to_crb_yn' => 0, 'message_id' => 0,
                'counter_signatory_id' => $arrParam['countersignId']);
            $this->Insert('ebulk_applications', $fieldArray);

            $sqlInsert = "INSERT INTO form_status (`application_id`,`p&sDate`) VALUES ('" . $rsCountersignApps[$i]['application_id'] . "', unix_timestamp(now()))";
            $this->Query($sqlInsert);

            $fieldArray = array('application_id' => $rsCountersignApps[$i]['application_id'], 'released_by' => $arrParam['adminId'], 'released_date' => $time, 'released_stage' => Admin);
            $this->Insert('countersign_log', $fieldArray);
        }
    }

    function getChildCompanies($company_id, $company_list="") {
        if (empty($company_list))
            $company_list = $company_id;
        else
            $company_list.="," . $company_id;

        $query = "SELECT company_id FROM company WHERE parent_id IN ($company_id)";
        $res = $this->getDBRecords($query);

        if (count($res) > 0) {
            $str = "";
            for ($i = 0; $i < count($res); $i++) {
                if (!empty($str))
                    $str.=",";
                $str.=$res[$i]['company_id'];
            }
            $company_list = $this->getChildCompanies($str, $company_list);
        }
        $company_list = explode(",", $company_list);
        $company_list = array_unique($company_list);
        sort($company_list);

        return implode(",", $company_list);
    }

    function getCountersignApps($arrParam,$counter_sign_all) {
        $arrCountersignApps = array();

        $listComp = $this->getChildCompanies($arrParam['company_id']);
        if($counter_sign_all != 'Y') $depAccess = 'AND b.company_id IN ('.$listComp.') ';
        $sqlPassApps = " SELECT  a.application_id, a.date_of_birth, b.name,c.name as dept_name,b.name , j.job jobname, ur.orgname regorg,s.workingathomeaddress 
                             FROM (applications a, organisation b, company c , jobs j, reqdocs rd, users u, user_registration ur ,sectionx s)
                             LEFT JOIN log_work_at_home_modified lwahm ON a.application_id = lwahm.application_id
                             WHERE a.org_id = b.org_id  AND a.application_id=rd.app_id  AND rd.user_id=u.user_id  AND u.username=ur.username   AND ur.job_role=j.sno 
        AND a.good_to_print = 'S' AND address_check_good='Y' AND a.cancelled = 'N' AND  a.cnt_sign_local = 'N' AND b.activated='Y'
                             AND c.company_id = b.company_id AND a.application_id=s.application_id and (s.workingathomeaddress = 'N' or lwahm.application_id IS NOT NULL) $depAccess ";

        $sqlFailByName = " SELECT app.application_id,app.org_id,app.date_of_birth,secx.checker_id, c.company_id,c.name as dept_name,b.name, j.job jobname, ur.orgname regorg 
                                FROM  applications app ,sectionx secx,organisation b, company c , jobs j, reqdocs rd, users u, user_registration ur 
                                WHERE  app.application_id = secx.application_id  AND app.application_id=rd.app_id AND rd.user_id=u.user_id   AND u.username=ur.username AND ur.job_role=j.sno   AND app.cancelled = 'N' AND app.good_to_print IN ('B','E','Q') AND app.cnt_sign_local = 'N' AND
                                    app.pulled_stage = 'Names' AND b.activated = 'Y' AND 
                                    app.org_id = b.org_id and c.company_id = b.company_id $depAccess ";

        $sqlFailByDOB = " SELECT  app.application_id,app.org_id,app.date_of_birth,secx.checker_id, c.company_id,c.name as dept_name,b.name , j.job jobname, ur.orgname regorg 
                               FROM  applications app ,sectionx secx,organisation b, company c , jobs j, reqdocs rd, users u, user_registration ur 
                               WHERE app.application_id = secx.application_id AND app.application_id=rd.app_id AND rd.user_id=u.user_id  AND u.username=ur.username AND ur.job_role=j.sno AND app.cancelled = 'N' AND  app.good_to_print IN ('B','E','Q') AND app.cnt_sign_local = 'N' AND  app.pulled_stage = 'Place Of Birth' AND b.activated = 'Y' AND app.org_id = b.org_id and c.company_id = b.company_id $depAccess";

        $sqlFailByAddress = "SELECT  app.application_id,app.org_id,app.date_of_birth,secx.checker_id, c.company_id,c.name as dept_name,b.name, j.job jobname, ur.orgname regorg 
                               FROM  applications app ,sectionx secx,organisation b, company c , jobs j, reqdocs rd, users u, user_registration ur         
                              WHERE   app.application_id = secx.application_id  AND app.application_id=rd.app_id AND rd.user_id=u.user_id AND u.username=ur.username  AND ur.job_role=j.sno AND app.cancelled = 'N' AND ( app.good_to_print IN ('B','E','Q') OR app.address_check_good in ('B','E','Q')) AND app.cnt_sign_local = 'N' AND  app.pulled_stage = 'Addresses' AND b.activated = 'Y' AND  app.org_id = b.org_id and c.company_id = b.company_id $depAccess ";

        $sqlFailByID = " SELECT app.application_id,app.org_id,app.date_of_birth,secx.checker_id, c.company_id,c.name as dept_name,b.name, j.job jobname, ur.orgname regorg 
                          FROM  applications app ,sectionx secx,organisation b, company c, jobs j, reqdocs rd, users u, user_registration ur 
                          WHERE app.application_id = secx.application_id AND app.application_id=rd.app_id  AND rd.user_id=u.user_id AND u.username=ur.username AND ur.job_role=j.sno AND app.cancelled = 'N' AND  app.good_to_print IN ('B','E','Q') AND app.cnt_sign_local = 'N' AND app.pulled_stage = 'ID' AND b.activated = 'Y' AND  app.org_id = b.org_id and c.company_id = b.company_id $depAccess ";

        $sqlFailTotal = " SELECT app.application_id,app.org_id,app.date_of_birth,secx.checker_id, c.company_id,c.name as dept_name,b.name, j.job jobname, ur.orgname regorg,secx.workingathomeaddress,app.good_to_print 
                          FROM (applications app ,sectionx secx,organisation b, company c , jobs j, reqdocs rd, users u, user_registration ur )
                         LEFT JOIN log_work_at_home_modified lwahm ON app.application_id = lwahm.application_id
                          WHERE app.application_id = secx.application_id AND app.cancelled = 'N' AND  (app.good_to_print IN ('B','E','Q') OR app.address_check_good in ('B','E','Q') OR (lwahm.application_id is null and secx.workingathomeaddress = 'Y' and app.good_to_print = 'S')) AND app.cnt_sign_local = 'N' AND  b.activated = 'Y' AND app.org_id = b.org_id and c.company_id = b.company_id AND app.application_id=rd.app_id 
                          AND rd.user_id=u.user_id AND u.username=ur.username  AND ur.job_role=j.sno $depAccess ";
        
         $sqlWorkAtHomeSuccessful ="select a.application_id, a.date_of_birth, b.name,c.name as dept_name,s.workingathomeaddress, j.job jobname, ur.orgname regorg from (applications a, organisation b, company c,sectionx s, jobs j, reqdocs rd, users u, user_registration ur )
      LEFT JOIN log_work_at_home_modified lwahm ON a.application_id = lwahm.application_id
       where a.org_id=b.org_id and  a.good_to_print='S' and a.address_check_good='Y' and a.cnt_sign_local='N'  AND b.activated='Y'
       and c.company_id=b.company_id and a.application_id=s.application_id and s.workingathomeaddress='Y'  and lwahm.application_id IS NOT NULL   AND a.application_id=rd.app_id  AND rd.user_id=u.user_id AND u.username=ur.username  AND ur.job_role=j.sno $depAccess";
        
          $sqlWorkAtHomeUnsuccessful ="select app.application_id,app.org_id,app.date_of_birth,secx.checker_id, c.company_id,c.name as dept_name,b.name, j.job jobname, ur.orgname regorg,secx.workingathomeaddress from (applications app ,sectionx secx,organisation b, company c, jobs j, reqdocs rd, users u, user_registration ur )
                LEFT JOIN log_work_at_home_modified lwahm ON app.application_id = lwahm.application_id  
                where app.application_id=secx.application_id and app.good_to_print ='S' and app.cnt_sign_local='N' and b.activated='Y' and secx.workingathomeaddress='Y' and lwahm.application_id IS NULL and 
                    app.org_id=b.org_id  AND app.application_id=rd.app_id  AND rd.user_id=u.user_id AND u.username=ur.username  AND ur.job_role=j.sno AND c.company_id=b.company_id $depAccess";

        $rsPassApps = '';
        $rsFailByName = '';
        $rsFailByDOB = '';
        $rsFailByAddress = '';
        $rsFailByID = '';

        switch ($arrParam['resultType']) {
            case 'Success' : $rsPassApps = $this->getDBRecords($sqlPassApps);
                break;

            case 'FailName' : $rsFailByName = $this->getDBRecords($sqlFailByName);
                break;

            case 'FailDOB' : $rsFailByDOB = $this->getDBRecords($sqlFailByDOB);
                break;

            case 'FailAddress' : $rsFailByAddress = $this->getDBRecords($sqlFailByAddress);
                break;

            case 'FailID' : $rsFailByID = $this->getDBRecords($sqlFailByID);
                break;

            case 'workingAtHomeAddressSuc' : $rsWorkAtHomeSuc= $this->getDBRecords($sqlWorkAtHomeSuccessful);
                break;
            
             case 'workingAtHomeAddressUnSuc' : $rsWorkAtHomeUnSuc= $this->getDBRecords($sqlWorkAtHomeUnsuccessful);
                break;

            case 'FailTotal':
                //  echo $sqlFailTotal;exit;
                $rsFailTotal = $this->getDBRecords($sqlFailTotal);
        }

        $arrApps = array();
        $j = 0;
        //Success Apps
        if (!empty($rsPassApps) && count($rsPassApps) > 0) {
            for ($i = 0; $i < count($rsPassApps); $i++) {
                $surName = $this->getApplicantName($rsPassApps[$i]["application_id"], $this->CURRENT_SURNAME);
                $forName = $this->getApplicantName($rsPassApps[$i]["application_id"], $this->CURRENT_FORNAME);
                $arrApps[$j]['personname'] = $this->correctstring(strtoupper($forName[0]["name"]) . " " . strtoupper($surName[0]["name"]));
                $arrApps[$j]['dateofbirth'] = $this->getFormatedDate($rsPassApps[$i]["date_of_birth"], $format = 3);
                $arrApps[$j]['appid'] = $rsPassApps[$i]["application_id"];
                $arrApps[$j]['name'] = $rsPassApps[$i]["name"];
                $arrApps[$j]['dept_name'] = $rsPassApps[$i]["dept_name"];
                $arrApps[$j]['jobname'] = $rsPassApps[$i]["jobname"];
                $arrApps[$j]['regorg'] = $rsPassApps[$i]["regorg"];
                $arrApps[$j]['workingathomeaddress'] = $rsPassApps[$i]["workingathomeaddress"];
                $j++;
            }
        }

        //Fail Apps By Name
        if (!empty($rsFailByName) && count($rsFailByName) > 0) {
            for ($i = 0; $i < count($rsFailByName); $i++) {
                $surName = $this->getApplicantName($rsFailByName[$i]["application_id"], $this->CURRENT_SURNAME);
                $forName = $this->getApplicantName($rsFailByName[$i]["application_id"], $this->CURRENT_FORNAME);

                $arrApps[$j]['personname'] = $this->correctstring(strtoupper($forName[0]["name"]) . " " . strtoupper($surName[0]["name"]));
                $arrApps[$j]['dateofbirth'] = $this->getFormatedDate($rsFailByName[$i]["date_of_birth"], $format = 3);
                $arrApps[$j]['appid'] = $rsFailByName[$i]["application_id"];
                $arrApps[$j]['name'] = $rsFailByName[$i]["name"];
                $arrApps[$j]['orgId'] = $rsFailByName[$i]["org_id"];
                $arrApps[$j]['checker_id'] = $rsFailByName[$i]["checker_id"];
                $arrApps[$j]['dept_name'] = $rsFailByName[$i]["dept_name"];
                $arrApps[$j]['jobname'] = $rsFailByName[$i]["jobname"];
                $arrApps[$j]['regorg'] = $rsFailByName[$i]["regorg"];
                $j++;
            }
        }

        //Fail Apps By DOB
        if (!empty($rsFailByDOB) && count($rsFailByDOB) > 0) {
            for ($i = 0; $i < count($rsFailByDOB); $i++) {
                $surName = $this->getApplicantName($rsFailByDOB[$i]["application_id"], $this->CURRENT_SURNAME);
                $forName = $this->getApplicantName($rsFailByDOB[$i]["application_id"], $this->CURRENT_FORNAME);

                $arrApps[$j]['personname'] = $this->correctstring(strtoupper($forName[0]["name"]) . " " . strtoupper($surName[0]["name"]));
                $arrApps[$j]['dateofbirth'] = $this->getFormatedDate($rsFailByDOB[$i]["date_of_birth"], $format = 3);
                $arrApps[$j]['appid'] = $rsFailByDOB[$i]["application_id"];
                $arrApps[$j]['name'] = $rsFailByDOB[$i]["name"];
                $arrApps[$j]['orgId'] = $rsFailByDOB[$i]["org_id"];
                $arrApps[$j]['checker_id'] = $rsFailByDOB[$i]["checker_id"];
                $arrApps[$j]['dept_name'] = $rsFailByDOB[$i]["dept_name"];
                  $arrApps[$j]['jobname'] = $rsFailByDOB[$i]["jobname"];
                $arrApps[$j]['regorg'] = $rsFailByDOB[$i]["regorg"];
                $j++;
            }
        }

        //Fail Apps By Address
        if (!empty($rsFailByAddress) && count($rsFailByAddress) > 0) {
            for ($i = 0; $i < count($rsFailByAddress); $i++) {
                $surName = $this->getApplicantName($rsFailByAddress[$i]["application_id"], $this->CURRENT_SURNAME);
                $forName = $this->getApplicantName($rsFailByAddress[$i]["application_id"], $this->CURRENT_FORNAME);

                $arrApps[$j]['personname'] = $this->correctstring(strtoupper($forName[0]["name"]) . " " . strtoupper($surName[0]["name"]));
                $arrApps[$j]['dateofbirth'] = $this->getFormatedDate($rsFailByAddress[$i]["date_of_birth"], $format = 3);
                $arrApps[$j]['appid'] = $rsFailByAddress[$i]["application_id"];
                $arrApps[$j]['name'] = $rsFailByAddress[$i]["name"];
                $arrApps[$j]['orgId'] = $rsFailByAddress[$i]["org_id"];
                $arrApps[$j]['checker_id'] = $rsFailByAddress[$i]["checker_id"];
                $arrApps[$j]['dept_name'] = $rsFailByAddress[$i]["dept_name"];
                  $arrApps[$j]['jobname'] = $rsFailByAddress[$i]["jobname"];
                $arrApps[$j]['regorg'] = $rsFailByAddress[$i]["regorg"];
                $j++;
            }
        }

        //Fail Apps By ID
        if (!empty($rsFailByID) && count($rsFailByID) > 0) {
            for ($i = 0; $i < count($rsFailByID); $i++) {
                $surName = $this->getApplicantName($rsFailByID[$i]["application_id"], $this->CURRENT_SURNAME);
                $forName = $this->getApplicantName($rsFailByID[$i]["application_id"], $this->CURRENT_FORNAME);

                $arrApps[$j]['personname'] = $this->correctstring(strtoupper($forName[0]["name"]) . " " . strtoupper($surName[0]["name"]));
                $arrApps[$j]['dateofbirth'] = $this->getFormatedDate($rsFailByID[$i]["date_of_birth"], $format = 3);
                $arrApps[$j]['appid'] = $rsFailByID[$i]["application_id"];
                $arrApps[$j]['name'] = $rsFailByID[$i]["name"];
                $arrApps[$j]['orgId'] = $rsFailByID[$i]["org_id"];
                $arrApps[$j]['checker_id'] = $rsFailByID[$i]["checker_id"];
                $arrApps[$j]['dept_name'] = $rsFailByID[$i]["dept_name"];
                $arrApps[$j]['jobname'] = $rsFailByID[$i]["jobname"];
                $arrApps[$j]['regorg'] = $rsFailByID[$i]["regorg"];
                $j++;
            }
        }

        if (!empty($rsFailTotal) && count($rsFailTotal) > 0) {
            for ($i = 0; $i < count($rsFailTotal); $i++) {
                $surName = $this->getApplicantName($rsFailTotal[$i]["application_id"], $this->CURRENT_SURNAME);
                $forName = $this->getApplicantName($rsFailTotal[$i]["application_id"], $this->CURRENT_FORNAME);

                $arrApps[$j]['personname'] = $this->correctstring(strtoupper($forName[0]["name"]) . " " . strtoupper($surName[0]["name"]));
                $arrApps[$j]['dateofbirth'] = $this->getFormatedDate($rsFailTotal[$i]["date_of_birth"], $format = 3);
                $arrApps[$j]['appid'] = $rsFailTotal[$i]["application_id"];
                $arrApps[$j]['name'] = $rsFailTotal[$i]["name"];
                $arrApps[$j]['orgId'] = $rsFailTotal[$i]["org_id"];
                $arrApps[$j]['checker_id'] = $rsFailTotal[$i]["checker_id"];
                $arrApps[$j]['dept_name'] = $rsFailTotal[$i]["dept_name"];
                $arrApps[$j]['jobname'] = $rsFailTotal[$i]["jobname"];
                $arrApps[$j]['regorg'] = $rsFailTotal[$i]["regorg"];
                $arrApps[$j]['workingathomeaddress'] = $rsFailTotal[$i]["workingathomeaddress"];
                $arrApps[$j]['good_to_print'] = $rsFailTotal[$i]["good_to_print"];
                $j++;
            }
        }
        
         if (!empty($rsWorkAtHomeSuc) && count($rsWorkAtHomeSuc) > 0) {
            for ($i = 0; $i < count($rsWorkAtHomeSuc); $i++) {
                $surName = $this->getApplicantName($rsWorkAtHomeSuc[$i]["application_id"], $this->CURRENT_SURNAME);
                $forName = $this->getApplicantName($rsWorkAtHomeSuc[$i]["application_id"], $this->CURRENT_FORNAME);

                $arrApps[$j]['personname'] = $this->correctstring(strtoupper($forName[0]["name"]) . " " . strtoupper($surName[0]["name"]));
                $arrApps[$j]['dateofbirth'] = $this->getFormatedDate($rsWorkAtHomeSuc[$i]["date_of_birth"], $format = 3);
                $arrApps[$j]['appid'] = $rsWorkAtHomeSuc[$i]["application_id"];
                $arrApps[$j]['name'] = $rsWorkAtHomeSuc[$i]["name"];
                $arrApps[$j]['orgId'] = $rsWorkAtHomeSuc[$i]["org_id"];
                $arrApps[$j]['checker_id'] = $rsWorkAtHomeSuc[$i]["checker_id"];
                $arrApps[$j]['dept_name'] = $rsWorkAtHomeSuc[$i]["dept_name"];
                $arrApps[$j]['workingathomeaddress'] = $rsWorkAtHomeSuc[$i]["workingathomeaddress"];
                  $arrApps[$j]['jobname'] = $rsWorkAtHomeSuc[$i]["jobname"];
                $arrApps[$j]['regorg'] = $rsWorkAtHomeSuc[$i]["regorg"];
                $j++;
            }
        }
        
         if (!empty($rsWorkAtHomeUnSuc) && count($rsWorkAtHomeUnSuc) > 0) {
            for ($i = 0; $i < count($rsWorkAtHomeUnSuc); $i++) {
                $surName = $this->getApplicantName($rsWorkAtHomeUnSuc[$i]["application_id"], $this->CURRENT_SURNAME);
                $forName = $this->getApplicantName($rsWorkAtHomeUnSuc[$i]["application_id"], $this->CURRENT_FORNAME);

                $arrApps[$j]['personname'] = $this->correctstring(strtoupper($forName[0]["name"]) . " " . strtoupper($surName[0]["name"]));
                $arrApps[$j]['dateofbirth'] = $this->getFormatedDate($rsWorkAtHomeUnSuc[$i]["date_of_birth"], $format = 3);
                $arrApps[$j]['appid'] = $rsWorkAtHomeUnSuc[$i]["application_id"];
                $arrApps[$j]['name'] = $rsWorkAtHomeUnSuc[$i]["name"];
                $arrApps[$j]['orgId'] = $rsWorkAtHomeUnSuc[$i]["org_id"];
                $arrApps[$j]['checker_id'] = $rsWorkAtHomeUnSuc[$i]["checker_id"];
                $arrApps[$j]['dept_name'] = $rsWorkAtHomeUnSuc[$i]["dept_name"];
                $arrApps[$j]['workingathomeaddress'] = $rsWorkAtHomeUnSuc[$i]["workingathomeaddress"];
                $arrApps[$j]['jobname'] = $rsWorkAtHomeUnSuc[$i]["jobname"];
                $arrApps[$j]['regorg'] = $rsWorkAtHomeUnSuc[$i]["regorg"];
                $j++;
            }
        }

        return $arrApps;
    }

    public function setAppsToPreEbulk($arrParam) {
        $intLoggedInUser = $this->getFromSession("user_id_M");
        $timeValue = time();
        for ($i = 0; $i < $arrParam['totalApps']; $i++) {
            $applicationId = null;
            $appRefNo = null;
            if (isset($arrParam["app_id" . $i])) {
                $application_id = $arrParam["app_id" . $i];
                if($arrParam['appProcess'] == 'onhold')
                {
                     $workingAtHomeAddress='Y';
                     //$query="update applications set good_to_print='H',cnt_sign_local='Y' where application_id='$application_id' limit 1";
                     $fieldArray = '';
                     $fieldArray['good_to_print'] = 'H';
                     $fieldArray['cnt_sign_local'] = 'Y';
                     $condition = " application_id='$application_id'";
                     $tableName = 'applications';
                     $result=$this->Update($tableName, $fieldArray, $condition);
                        if(!empty($arrParam['comments'])){
//                            $query = "INSERT INTO `application_onhold_reason` (`application_id` ,`user_id` ,`comment`) VALUES ('$application_id', '$intLoggedInUser','$comments')";
                             $fieldArray = '';
                            $fieldArray['application_id'] = $application_id;
                            $fieldArray['user_id']        =$intLoggedInUser;
                            $fieldArray['comment']       =$arrParam['comments'];
                            $tableName = 'application_onhold_reason';
                            $res = $this->Insert($tableName, $fieldArray);
                        }
                }
                else
                {
               $query="update applications set good_to_print='N',address_check_good='N',address_check=NULL,first_check=NULL,second_check=NULL where application_id='$application_id' ";
               $this->Query($query);

                //Delete entry if unsuccessful app moved back again.
                if ($arrParam['fromUnsuccApp'] == 1) {
                    $strDelRule = " application_id='$application_id' ";
                    $this->Delete("precheck_problems", $strDelRule);
                }

                $arrDataInsert = array('application_id' => $application_id, 'reset_user_id' => $intLoggedInUser, 'reset_datentime' => $timeValue);
                $logresult = $this->Insert("applications_preebulk_reset_log", $arrDataInsert);
            }
        }
    }
    }

    function getOnHoldApps($arrParam) {
        $arrCountersignApps = array();

        $listComp = $this->getChildCompanies_1($arrParam['company_id']);

        $sqlApps = " SELECT
                                straight_join a.application_id appid,a.org_id,s.checker_id,c.company_id, pnf.name fname, pns.name sname, o.name oname,
                                u.name ufname, u.surname usname, date_format(from_unixtime(a.print_hold),'%d/%m/%Y') holddate,
                                date_format(from_unixtime(a.hold_contacted),'%d/%m/%Y') as hold_contacted,a.pulled_stage,a.ebulkapp,a.completed_by, s.remuneration,a.first_check,a.second_check 
                         FROM
                                applications a
                                INNER JOIN sectionx s ON a.application_id = s.application_id
                                INNER JOIN organisation o ON a.org_id = o.org_id AND o.company_id IN ($listComp)
                                INNER JOIN company c ON o.company_id = c.company_id
                                INNER JOIN app_person_name apnf ON a.application_id = apnf.application_id
                                INNER JOIN person_names pnf ON apnf.name_id = pnf.name_id AND pnf.name_type_id = 4
                                INNER JOIN app_person_name apns ON a.application_id = apns.application_id
                                INNER JOIN person_names pns ON apns.name_id = pns.name_id AND pns.name_type_id = 3
                                LEFT JOIN application_onhold_reason aor on a.application_id = aor.application_id
                                LEFT JOIN users u ON aor.user_id = u.user_id
                         WHERE
                                a.cnt_sign_local = 'Y' AND (a.good_to_print IN ('Q','E','H') OR a.address_check_good in ('B','E','Q')) AND a.cancelled <> 'Y' ";

        if (!empty($arrParam['orgId']))
            $sqlApps.=" and o.org_id='" . $arrParam['orgId'] . "' ";

        $sqlApps .= " ORDER BY a.print_hold ASC ";

        $rsApps = $this->getDBRecords($sqlApps);

        $arrApps = array();
        $j = 0;

        if (!empty($rsApps) && count($rsApps) > 0) {
            for ($i = 0; $i < count($rsApps); $i++) {
                $arrApps[$i]['appid'] = $rsApps[$i]['appid'];
                $arrApps[$i]['org_id'] = $rsApps[$i]['org_id'];
                $arrApps[$i]['checker_id'] = $rsApps[$i]['checker_id'];
                $arrApps[$i]['oname'] = $rsApps[$i]['oname'];
                $arrApps[$i]['pulled_stage'] = $rsApps[$i]['pulled_stage'];
                $arrApps[$i]['holddate'] = $rsApps[$i]['holddate'];
                $arrApps[$j]['appname'] = $this->correctstring(strtoupper($rsApps[$i]["fname"]) . " " . strtoupper($rsApps[$i]["sname"]));
                $arrApps[$j]['dateofbirth'] = $this->getFormatedDate($rsApps[$i]["date_of_birth"], $format = 3);
                $arrApps[$i]['hold_by'] = $rsApps[$i]['ufname'] . " " . $rsApps[$i]['usname'];
                $arrApps[$i]['messageSent'] = $this->GetMessageSentTime($rsApps[$i]['appid']);
                $arrApps[$i]['lastUpdated'] = $this->getAppLastUpdated($rsApps[$i]['appid'], $rsApps[$i]['ebulkapp']);
                $arrApps[$i]['companyID'] = $rsApps[$i]['company_id'];
                if (in_array($rsApps[$i]['company_id'], $arrParam['deptAccessList']))
                    $arrApps[$i]['submittedBy'] = "True";
                else if ($arrParam['deptAccessList'][0] == "")
                    $arrApps[$i]['submittedBy'] = "True";
                $strVolunteer = ($rsApps[$i]['remuneration'] == 'N') ? 'Yes' : 'No';
                $arrApps[$j]['volunteer'] = $strVolunteer;
                $arrApps[$i]['first_check'] = $rsApps[$i]['first_check'];
                $arrApps[$i]['second_check'] = $rsApps[$i]['second_check'];
                $j++;
            }
        }

        return $arrApps;
    }

    /*
     * Function to insert into Application table
     */

    public function initiateApplication($QueryResult) {


        #Insert All Posted Value to the table "reqdocs" table
        $result = $this->Query($QueryResult);

        return $result;
    }

    public function getMaxApplicationId($unique_id="") {

        if (!empty($unique_id))
            $condition = " WHERE unique_key = '$unique_id'";

        $sqlMaxApp = "select max(application_id) as applicationId,org_id from applications $condition";
        $resultMaxApp = $this->getDBRecords($sqlMaxApp);

        return $resultMaxApp;
    }

    public function updateAppBrowserDetails($arrAppDetails, $appId) {


        $condition = " application_id='$appId' limit 1";

        $updateAppDetails = $this->Update('applications', $arrAppDetails, $condition);

        return $updateAppDetails;
    }

    #check if the generated username exists in the system

    public function getMaxApplicantUser($useridname) {


        $sqlMaxAppUser = "select user_id from users where username='$useridname'";

        $resultMaxAppUser = $this->getDBRecords($sqlMaxAppUser);

        if (count($resultMaxAppUser) > 0) {
            $j = 1;
            do {
                $new_username = $useridname . $j;
                $query = "select user_id from users where username='$new_username'";
                $usrresult = $this->getDBRecords($query);
                $username1 = $new_username;
                $j++;
            } while (count($usrresult) > 0);
        } else {
            $username1 = $useridname;
        }

        return $username1;
    }

    public function updateAppAdditionalDetails($userDetails) {


        #Insert All Posted Value to the table "users" table
        $result = $this->Insert("users", $userDetails);

        return $result;
    }

    public function insertupdateIntoConsent($consentDetails, $appId="") {

        if (!empty($appId)) {
            $condition = " app_id='$appId' and lo_consenttime IS NULL";
            #update All Posted Value to the table "consent" table
            $result = $this->Update('consent', $consentDetails, $condition);
        } else {
            #Insert All Posted Value to the table "consent" table
            $result = $this->Insert("consent", $consentDetails);
        }

        return $result;
    }

    public function insertIntoSectionX($arrSectionX) {


        #Insert All Posted Value to the table "consent" table
        $result = $this->Insert("sectionx", $arrSectionX);

        return $result;
    }

    public function insertVolQueLog($arrVolLog) {

        #Insert All Posted Value to the table "consent" table
        $result = $this->Insert("volunteer_questionnaire_log", $arrVolLog);

        return $result;
    }

    public function setSuplimentFlag($applicationId) {
        $query = "update applications set supliment_required='Y' where application_id='$applicationId'";
        $res = $this->Query($query);
    }

    public function deactivateApplicantUser($maxuserid) {
        $query = "update users set used='Y',active='Y',messages='Y',message_reply='Y' where user_id='$maxuserid' limit 1 ";
        $res = $this->Query($query);
    }

    #function to get the count of children below a company

    function scanDeptLevelTree($x, &$arr, $level="") {
        if (empty($level))
            $level = 0;

        $query = "SELECT company_id FROM company WHERE parent_id=" . $x . " ORDER BY name";
        $result = $this->getDBRecords($query);

        if (count($result) > 0) {
            $level++;
            for ($i = 0; $i < count($result); $i++) {
                $arr[$level]['company_id'][] = $result[$i]['company_id'];
                $this->scanDeptLevelTree($result[$i]['company_id'], $arr, $level);
            }
        }

        $childrens = count($arr);
        return $childrens;
    }

    function getLevel($compId, $level=0) {
        $query = "SELECT parent_id FROM company WHERE company_id='$compId'";
        $res = $this->getDBRecords($query);

        if ($res[0]['parent_id'] != 0) {
            $level++;
            $level = $this->getLevel($res[0]['parent_id'], $level);
        }
        return $level;
    }

    function getCompName($compId) {
        $query = "SELECT * FROM company WHERE company_id='$compId' ORDER BY name ASC";
        $res = $this->getDBRecords($query);
        $name = $res[0]["name"];
        return $name;
    }

    # this function returns the list of all child companies of the given company irrespective of department level for Single Central Report

    function getChildCompanies_1($company_id, $company_list="") {
        if (empty($company_list))
            $company_list = $company_id;
        else
            $company_list.="," . $company_id;

        $query = "SELECT company_id FROM company WHERE parent_id in ($company_id)";
        $res = $this->getDBRecords($query);

        if (count($res) > 0) {
            $str = "";
            for ($i = 0; $i < count($res); $i++) {
                if (!empty($str))
                    $str.=",";
                $str.=$res[$i]['company_id'];
            }
            $company_list = $this->getChildCompanies_1($str, $company_list);
        }
        $company_list = explode(",", $company_list);
        $company_list = array_unique($company_list);
        sort($company_list);

        $list = implode(",", $company_list);
        return $list;
    }

    /* To get Message Set Time */

    function GetMessageSentTime($application_id) {

        $queryres = "select m.msg_sent_dt  from application_messages app,messaging m where app.msg_id = m.msg_id and  app.application_id='" . $application_id . "'  order by m.msg_id desc";
        $msgres = $this->getDBRecords($queryres);
        $msgdate = '';
        if (count($msgres) > 0) {
            $sent_year = substr($msgres[0]['msg_sent_dt'], 0, 4);
            $sent_month = substr($msgres[0]['msg_sent_dt'], 5, 2);
            $sent_day = substr($msgres[0]['msg_sent_dt'], 8, 2);
            $msgdate = $sent_day . "/" . $sent_month . "/" . $sent_year;
        }
        return $msgdate;
    }

    /* To get Message Set Time */

    /* To Get Application Last Updated Time */

    function getAppLastUpdated($appId, $eBulkApplication) {
        $commonQuery = "select DISTINCT ap.ebulkapp,ap.application_id, o.name oname,o.org_id,o.povafirst,o.list99 list99req,c.company_id, c.name cname, sx.app_ref_no, sx.pova pova,sx.id_verified_on, sx.list99 list99,fs.certno, fs.formstatus, fs.`p&sDate` psdt, fs.`s&rDate` srdt, fs.dCrbDate dcrbdt, fs.current_status,fs.rRDate rrdt, fs.povaStart povaStart,fs.povaEnd povaEnd, fs.povaResult povaResult,fs.lpfdate, fs.list99Start list99start,fs.list99End list99End,fs.list99Result list99Result,ap.application_id, ap.cancelled,sx.discType,sx.category_code,sx.remuneration,ap.sectionx_complete_time,ap.submit_time ,fs.last_updated,ap.manual_application as manualApp,fs.recruitment_decision, upper(pnf.name) as UserForename,fs.result, upper(pns.name) as UserSurname
              FROM organisation o,company c, applications ap
              INNER JOIN app_person_name apnf ON ap.application_id = apnf.application_id
          INNER JOIN person_names pnf ON apnf.name_id = pnf.name_id
          AND pnf.name_type_id =4
          INNER JOIN app_person_name apns ON ap.application_id = apns.application_id
          INNER JOIN person_names pns ON apns.name_id = pns.name_id
          AND pns.name_type_id =3
              left outer join sectionx sx on ap.application_id=sx.application_id
              left outer join form_status fs on {JOINEXPR}
              where  ap.submit_time IS NOT NULL AND ap.sectionx_complete_time IS NOT NULL and o.activated='Y' and ap.org_id=o.org_id and o.company_id=c.company_id and ap.cancelled <> 'Y' and ap.application_id='$appId'";

        $commonQuery.=" {EBULKCONDITION}";

        #Query for normal applications - printed
        $joinexpr = "fs.app_ref_no=sx.app_ref_no";
        $ebulkconfition = "and ap.ebulkapp <> 'Y'";
        $content_var = array("{JOINEXPR}", "{EBULKCONDITION}");
        $content_value = array($joinexpr, $ebulkconfition);
        $pquery = str_replace($content_var, $content_value, $commonQuery);

        #Query for Ebulk applications - Not printed
        $joinexpr = "fs.application_id=sx.application_id";
        $ebulkconfition = "and ap.ebulkapp = 'Y'";
        $content_var = array("{JOINEXPR}", "{EBULKCONDITION}");
        $content_value = array($joinexpr, $ebulkconfition);
        $equery = str_replace($content_var, $content_value, $commonQuery);

        $query = $pquery . " UNION " . $equery . "";

        $res = $this->getDBRecords($query);

        $lastUpdated = "";
        if ($eBulkApplication <> 'Y') {
            if (!empty($res[0]["submit_time"]) && empty($res[0]["psdt"])) {
                $lastUpdated = date("d/m/Y", $res[0]["submit_time"]);
                $appatstage = "appsubmited";
                $current_stage = "Application Submitted";
            } elseif (!empty($res[0]["psdt"]) && empty($res[0]["srdt"])) {
                $lastUpdated = date("d/m/Y", $res[0]["psdt"]);
                $appatstage = "printedandsent";
                $current_stage = "Printed & Sent";
            } elseif (empty($res[0]["dcrbdt"]) && !empty($res[0]["srdt"])) {
                $lastUpdated = date("d/m/Y", $res[0]["srdt"]);
                $appatstage = "indispatchqueue";
                $current_stage = "In Dispatch Queue";
            } elseif (!empty($res[0]["dcrbdt"]) && empty($res[0]["rrdt"])) {
                $lastUpdated = date("d/m/Y", $res[0]["dcrbdt"]);
                $appatstage = "dispatchedtocrb";

                #If alreday dispatched to CRB and the application has moved to different stage within CRB
                if (!empty($res[0]["current_status"]) && $res[0]["current_status"] <> "")
                    $current_stage = trim($res[0]["current_status"]);
                else
                    $current_stage="Dispatched to ".DBS;

                if (!empty($res[0]["last_updated"]))
                    $lastUpdated = date("d/m/Y", $res[0]["last_updated"]);
            }elseif ((!empty($res[0]["rrdt"]) && ($res[0]["rrdt"] != 'NULL'))) {
                $lastUpdated = date("d/m/Y", $res[0]["rrdt"]);
                $appatstage = "resultreceived";
                $current_stage = "Result Received";
                if ($res[0]["result"] == 'Invalid Disclosure')
                    $current_stage = "Invalid Disclosure";
            }
        }else {
            if (!empty($res[0]["submit_time"]) && empty($res[0]["psdt"])) {
                $lastUpdated = date("d/m/Y", $res[0]["submit_time"]);
                $appatstage = "appsubmited";
                $current_stage = "Application Submitted";
            } elseif (!empty($res[0]["psdt"]) && empty($res[0]["srdt"])) {
                $lastUpdated = date("d/m/Y", $res[0]["psdt"]);
                $appatstage = "qualitycompleted";
                $current_stage = "Quality Check Completed";
            } elseif (empty($res[0]["dcrbdt"]) && !empty($res[0]["srdt"])) {
                $lastUpdated = date("d/m/Y", $res[0]["srdt"]);
                $appatstage = "indispatchqueue";
                $current_stage = "In Dispatch Queue";
            } elseif (!empty($res[0]["dcrbdt"]) && empty($res[0]["rrdt"])) {
                $lastUpdated = date("d/m/Y", $res[0]["dcrbdt"]);
                $appatstage = "dispatchedtocrb";

                #If alreday dispatched to CRB and the application has moved to different stage within CRB
                if (!empty($res[0]["current_status"]) && $res[0]["current_status"] <> "")
                    $current_stage = trim($res[0]["current_status"]);
                else
                    $current_stage="Dispatched to ".DBS;

                if (!empty($res[0]["last_updated"]))
                    $lastUpdated = date("d/m/Y", $res[0]["last_updated"]);
            }elseif ((!empty($res[0]["rrdt"]) && ($res[0]["rrdt"] != 'NULL'))) {
                $lastUpdated = date("d/m/Y", $res[0]["rrdt"]);
                $appatstage = "resultreceived";
                $current_stage = "Result Received";
                if ($res[0]["result"] == 'Invalid Disclosure')
                    $current_stage = "Invalid Disclosure";
            }
        }
        return $lastUpdated;
    }

    function getVerifyStats($arrParam) {
        $arrVerifyApps = array();

        $listComp = $this->getChildCompanies($arrParam['company_id']);

        $sqlPassApps = "SELECT
                                COUNT(a.application_id) AS totalapps
                        FROM
                                applications a, organisation b, company c, sectionx s
                        WHERE
                                a.application_id = s.application_id AND a.org_id = b.org_id AND a.cancelled = 'N' AND a.good_to_print = 'S' AND a.address_check_good = 'Y' AND a.cnt_sign_local = 'Y' AND b.activated='Y'
                                AND c.company_id = b.company_id AND b.company_id IN ($listComp) AND s.checked_by <> '' AND (s.remuneration != 'N' OR a.volunteer_release = 'Y') AND (s.jobwork != '" . $this->intWorksChildren . "' OR a.works_with_children_release = 'Y')";

        $rsPassApps = $this->getDBRecords($sqlPassApps);

        $sqlWorksChildren = "SELECT
                                COUNT(a.application_id) AS totChildrenApps
                        FROM
                                applications a, organisation b, company c, sectionx s
                        WHERE
                                a.application_id = s.application_id AND a.org_id = b.org_id AND a.cancelled = 'N' AND a.good_to_print = 'S' AND a.address_check_good = 'Y' AND a.cnt_sign_local = 'Y' AND b.activated='Y'
                                AND c.company_id = b.company_id AND b.company_id IN ($listComp) AND s.checked_by <> '' AND s.jobwork = '" . $this->intWorksChildren . "' AND a.works_with_children_release = 'N'";
        $rsWorksChildren = $this->getDBRecords($sqlWorksChildren);

        $sqlVolunteer = "SELECT
                                COUNT(a.application_id) AS totVolunteerApps
                        FROM
                                applications a, organisation b, company c, sectionx s
                        WHERE
                                a.application_id = s.application_id AND a.org_id = b.org_id AND a.cancelled = 'N' AND a.good_to_print = 'S' AND a.address_check_good = 'Y' AND a.cnt_sign_local = 'Y' AND b.activated='Y'
                                AND c.company_id = b.company_id AND b.company_id IN ($listComp) AND s.checked_by <> '' AND s.remuneration = 'N' AND a.volunteer_release = 'N'";
        $rsVolunteer = $this->getDBRecords($sqlVolunteer);


        $sqlFailByName = "SELECT
                                    COUNT(app.application_id) AS totalapps
                          FROM
                                    applications app ,sectionx secx,organisation b, company c
                          WHERE
                                    app.application_id = secx.application_id AND app.good_to_print IN ('B','E','Q') AND app.cnt_sign_local = 'Y' AND
                                    app.pulled_stage = 'Names' AND b.activated = 'Y' AND app.cancelled = 'N' AND
                                    app.org_id = b.org_id and c.company_id = b.company_id AND b.company_id IN ($listComp) ";
        $rsFailByName = $this->getDBRecords($sqlFailByName);

        $sqlFailByDOB = "SELECT
                                    COUNT(app.application_id) AS totalapps
                          FROM
                                    applications app ,sectionx secx,organisation b, company c
                          WHERE
                                    app.application_id = secx.application_id AND app.cancelled = 'N' AND app.good_to_print IN ('B','E','Q') AND app.cnt_sign_local = 'Y' AND
                                    app.pulled_stage = 'Place Of Birth' AND b.activated = 'Y' AND 
                                    app.org_id = b.org_id and c.company_id = b.company_id AND b.company_id IN ($listComp) ";
        $rsFailByDOB = $this->getDBRecords($sqlFailByDOB);

        $sqlFailByAddress = "SELECT
                                    COUNT(app.application_id) AS totalapps
                             FROM
                                    applications app ,sectionx secx,organisation b, company c
                             WHERE
                                    app.application_id = secx.application_id AND app.cancelled = 'N' AND (app.good_to_print IN ('B','E','Q') OR app.address_check_good IN ('B','E','Q')) AND app.cnt_sign_local = 'Y' AND
                                    app.pulled_stage = 'Addresses' AND b.activated = 'Y' AND
                                    app.org_id = b.org_id and c.company_id = b.company_id AND b.company_id IN ($listComp) ";
        $rsFailByAddress = $this->getDBRecords($sqlFailByAddress);

        $sqlFailByID = " SELECT
                                    COUNT(app.application_id) AS totalapps
                         FROM
                                    applications app ,sectionx secx,organisation b, company c
                         WHERE
                                    app.application_id = secx.application_id AND app.cancelled = 'N' AND app.good_to_print IN ('B','E','Q') AND app.cnt_sign_local = 'Y' AND
                                    app.pulled_stage = 'ID' AND b.activated = 'Y' AND
                                    app.org_id = b.org_id and c.company_id = b.company_id AND b.company_id IN ($listComp) ";
        $rsFailByID = $this->getDBRecords($sqlFailByID);

        $arrCountersignApps['totCorrectApps'] = $rsPassApps[0]["totalapps"];
        $arrCountersignApps['totChildrenApps'] = $rsWorksChildren[0]["totChildrenApps"];
        $arrCountersignApps['totNamesApps'] = $rsFailByName[0]["totalapps"];
        $arrCountersignApps['totBirthApps'] = $rsFailByDOB[0]["totalapps"];
        $arrCountersignApps['totAddressApps'] = $rsFailByAddress[0]["totalapps"];
        $arrCountersignApps['totIdApps'] = $rsFailByID[0]["totalapps"];
        $arrCountersignApps['totVolunteerApps'] = $rsVolunteer[0]["totVolunteerApps"];

        if (empty($arrCountersignApps['totCorrectApps']))
            $arrCountersignApps['totCorrectApps'] = 0;
        if (empty($arrCountersignApps['totChildrenApps']))
            $arrCountersignApps['totChildrenApps'] = 0;
        if (empty($arrCountersignApps['totNamesApps']))
            $arrCountersignApps['totNamesApps'] = 0;
        if (empty($arrCountersignApps['totBirthApps']))
            $arrCountersignApps['totBirthApps'] = 0;
        if (empty($arrCountersignApps['totAddressApps']))
            $arrCountersignApps['totAddressApps'] = 0;
        if (empty($arrCountersignApps['totIdApps']))
            $arrCountersignApps['totIdApps'] = 0;
        if (empty($arrCountersignApps['totVolunteerApps']))
            $arrCountersignApps['totVolunteerApps'] = 0;

        $arrCountersignApps['totFailApps'] = $arrCountersignApps['totNamesApps'] + $arrCountersignApps['totBirthApps'] + $arrCountersignApps['totAddressApps'] + $arrCountersignApps['totIdApps'];

        return $arrCountersignApps;
    }

    function getVerifyApps($arrParam) {
        $arrCountersignApps = array();

        $listComp = $this->getChildCompanies($arrParam['company_id']);

        $sqlPassApps = "SELECT
                                a.application_id, a.date_of_birth, b.name,c.name as dept_name, b.name, s.remuneration 
        , j.job jobname, ur.orgname regorg 
                        FROM
                                applications a, organisation b, company c, sectionx s
        , jobs j, reqdocs rd, users u, user_registration ur 
                         WHERE
                                a.application_id = s.application_id AND a.org_id = b.org_id 
                            AND a.application_id=rd.app_id 
                            AND rd.user_id=u.user_id
                            AND u.username=ur.username
                            AND ur.job_role=j.sno 
                                  AND a.cancelled = 'N' AND a.good_to_print = 'S' AND a.address_check_good = 'Y' AND a.cnt_sign_local = 'Y' AND b.activated='Y'
                                AND c.company_id = b.company_id AND b.company_id IN ($listComp) AND s.checked_by <> ''AND (s.remuneration != 'N' OR a.volunteer_release = 'Y') AND (s.jobwork != '" . $this->intWorksChildren . "' OR a.works_with_children_release = 'Y')";

        $sqlWorksChildren = "SELECT
                                a.application_id, a.date_of_birth, b.name,c.name as dept_name, b.name, s.remuneration
        , j.job jobname, ur.orgname regorg 
                        FROM
                                applications a, organisation b, company c, sectionx s
         , jobs j, reqdocs rd, users u, user_registration ur 
                        WHERE
                                a.application_id = s.application_id AND a.org_id = b.org_id 
                            AND a.application_id=rd.app_id 
                            AND rd.user_id=u.user_id
                            AND u.username=ur.username
                            AND ur.job_role=j.sno 
                    AND a.cancelled = 'N' AND a.good_to_print = 'S' AND a.address_check_good = 'Y'  AND a.cnt_sign_local = 'Y' AND b.activated='Y'
                                AND c.company_id = b.company_id AND b.company_id IN ($listComp) AND s.checked_by <> '' AND s.jobwork = '" . $this->intWorksChildren . "' AND a.works_with_children_release = 'N'";

        $sqlVolunteer = "SELECT
                                a.application_id, a.date_of_birth, b.name,c.name as dept_name, b.name, s.remuneration
        , j.job jobname, ur.orgname regorg 
                        FROM
                                applications a, organisation b, company c, sectionx s
         , jobs j, reqdocs rd, users u, user_registration ur 
                        WHERE
                                a.application_id = s.application_id AND a.org_id = b.org_id 
                            AND a.application_id=rd.app_id 
                            AND rd.user_id=u.user_id
                            AND u.username=ur.username
                            AND ur.job_role=j.sno 
                    AND a.cancelled = 'N' AND a.good_to_print = 'S' AND a.address_check_good = 'Y'  AND a.cnt_sign_local = 'Y' AND b.activated='Y'
                                AND c.company_id = b.company_id AND b.company_id IN ($listComp) AND s.checked_by <> '' AND s.remuneration = 'N' AND a.volunteer_release = 'N'";

        $sqlFailByName = "SELECT
                                    app.application_id,app.org_id,app.date_of_birth,secx.checker_id, c.company_id,c.name as dept_name, b.name, secx.remuneration 
                , j.job jobname, ur.orgname regorg 
                          FROM
                                    applications app ,sectionx secx,organisation b, company c
        , jobs j, reqdocs rd, users u, user_registration ur 
                          WHERE
                                    app.application_id = secx.application_id 
                            AND app.application_id=rd.app_id 
                            AND rd.user_id=u.user_id
                            AND u.username=ur.username
                            AND ur.job_role=j.sno 
        AND app.good_to_print IN ('B','E','Q') AND app.cnt_sign_local = 'Y' AND
                                    app.pulled_stage = 'Names' AND b.activated = 'Y' AND app.cancelled = 'N' AND
                                    app.org_id = b.org_id and c.company_id = b.company_id AND b.company_id IN ($listComp) ";

        $sqlFailByDOB = " SELECT
                                    app.application_id,app.org_id,app.date_of_birth,secx.checker_id, c.company_id,c.name as dept_name, b.name, secx.remuneration
                        , j.job jobname, ur.orgname regorg 
                          FROM
                                    applications app ,sectionx secx,organisation b, company c
                , jobs j, reqdocs rd, users u, user_registration ur 
                          WHERE
                                    app.application_id = secx.application_id 
                            AND app.application_id=rd.app_id 
                            AND rd.user_id=u.user_id
                            AND u.username=ur.username
                            AND ur.job_role=j.sno 
        AND app.cancelled = 'N' AND app.good_to_print IN ('B','E','Q') AND app.cnt_sign_local = 'Y' AND
                                    app.pulled_stage = 'Place Of Birth' AND b.activated = 'Y' AND 
                                    app.org_id = b.org_id and c.company_id = b.company_id AND b.company_id IN ($listComp) ";

        $sqlFailByAddress = "SELECT
                                    app.application_id,app.org_id,app.date_of_birth,secx.checker_id, c.company_id,c.name as dept_name, b.name, secx.remuneration 
                                , j.job jobname, ur.orgname regorg 
                          FROM
                                    applications app ,sectionx secx,organisation b, company c
                        , jobs j, reqdocs rd, users u, user_registration ur 
                          WHERE
                                    app.application_id = secx.application_id
                                    AND app.application_id=rd.app_id 
                            AND rd.user_id=u.user_id
                            AND u.username=ur.username
                            AND ur.job_role=j.sno 
        AND app.cancelled = 'N' AND (app.good_to_print IN ('B','E','Q') OR app.address_check_good in ('B','E','Q')) AND app.cnt_sign_local = 'Y' AND
                                    app.pulled_stage = 'Addresses' AND b.activated = 'Y' AND 
                                    app.org_id = b.org_id and c.company_id = b.company_id AND b.company_id IN ($listComp) ";

        $sqlFailByID = "  SELECT
                                    app.application_id,app.org_id,app.date_of_birth,secx.checker_id, c.company_id,c.name as dept_name, b.name, secx.remuneration 
         , j.job jobname, ur.orgname regorg 
                          FROM
                                    applications app ,sectionx secx,organisation b, company c
         , jobs j, reqdocs rd, users u, user_registration ur 
                          WHERE
                                    app.application_id = secx.application_id 
        AND app.application_id=rd.app_id 
                            AND rd.user_id=u.user_id
                            AND u.username=ur.username
                            AND ur.job_role=j.sno 
        AND app.cancelled = 'N' AND app.good_to_print IN ('B','E','Q') AND app.cnt_sign_local = 'Y' AND
                                    app.pulled_stage = 'ID' AND b.activated = 'Y' AND
                                    app.org_id = b.org_id and c.company_id = b.company_id AND b.company_id IN ($listComp) ";

        $sqlFailTotal = " SELECT
                                    app.application_id,app.org_id,app.date_of_birth,secx.checker_id, c.company_id,c.name as dept_name, b.name, secx.remuneration
                 , j.job jobname, ur.orgname regorg 
                          FROM
                                    applications app ,sectionx secx,organisation b, company c
                 , jobs j, reqdocs rd, users u, user_registration ur
                          WHERE
                                    app.application_id = secx.application_id
        AND app.application_id=rd.app_id 
                            AND rd.user_id=u.user_id
                            AND u.username=ur.username
                            AND ur.job_role=j.sno 
        AND app.cancelled = 'N' AND ( app.good_to_print IN ('B','E','Q') OR app.address_check_good in ('B','E','Q')) AND app.cnt_sign_local = 'Y' AND
                                    b.activated = 'Y' AND app.org_id = b.org_id and c.company_id = b.company_id AND b.company_id IN ($listComp) ";

        $rsPassApps = '';
        $rsWorksChildren = '';
        $rsVolunteer = '';
        $rsFailByName = '';
        $rsFailByDOB = '';
        $rsFailByAddress = '';
        $rsFailByID = '';

        switch ($arrParam['resultType']) {
            case 'Success' : $rsPassApps = $this->getDBRecords($sqlPassApps);
                break;

            case 'Children' : $rsWorksChildren = $this->getDBRecords($sqlWorksChildren);
                break;

            case 'Volunteer' : $rsVolunteer = $this->getDBRecords($sqlVolunteer);
                break;

            case 'FailName' : $rsFailByName = $this->getDBRecords($sqlFailByName);
                break;

            case 'FailDOB' : $rsFailByDOB = $this->getDBRecords($sqlFailByDOB);
                break;

            case 'FailAddress' : $rsFailByAddress = $this->getDBRecords($sqlFailByAddress);
                break;

            case 'FailID' : $rsFailByID = $this->getDBRecords($sqlFailByID);
                break;

            case 'FailTotal':
                $rsFailTotal = $this->getDBRecords($sqlFailTotal);
        }

        $arrApps = array();
        $j = 0;
        //Success Apps
        if (!empty($rsPassApps) && count($rsPassApps) > 0) {
            for ($i = 0; $i < count($rsPassApps); $i++) {
                $surName = $this->getApplicantName($rsPassApps[$i]["application_id"], $this->CURRENT_SURNAME);
                $forName = $this->getApplicantName($rsPassApps[$i]["application_id"], $this->CURRENT_FORNAME);
                $middleName = $this->getApplicantName($rsPassApps[$i]["application_id"], $this->CURRENT_MIDDLENAME);

                $arrApps[$j]['personname'] = $this->correctstring(strtoupper($forName[0]["name"]) . " " . strtoupper($middleName[0]["name"]) . " " . strtoupper($surName[0]["name"]));
                $arrApps[$j]['dateofbirth'] = $this->getFormatedDate($rsPassApps[$i]["date_of_birth"], $format = 3);
                $arrApps[$j]['appid'] = $rsPassApps[$i]["application_id"];
                $arrApps[$j]['name'] = $rsPassApps[$i]["name"];
                $arrApps[$j]['dept_name'] = $rsPassApps[$i]["dept_name"];
                $strVolunteer = ($rsPassApps[$i]['remuneration'] == 'N') ? 'Yes' : 'No';
                $arrApps[$j]['volunteer'] = $strVolunteer;
                $arrApps[$j]['jobname'] = $rsPassApps[$i]["jobname"];
                $arrApps[$j]['regorg'] = $rsPassApps[$i]["regorg"];
                $j++;
            }
        }

        //Children Only Success Apps
        if (!empty($rsWorksChildren) && count($rsWorksChildren) > 0) {
            for ($i = 0; $i < count($rsWorksChildren); $i++) {
                $surName = $this->getApplicantName($rsWorksChildren[$i]["application_id"], $this->CURRENT_SURNAME);
                $forName = $this->getApplicantName($rsWorksChildren[$i]["application_id"], $this->CURRENT_FORNAME);
                $middleName = $this->getApplicantName($rsWorksChildren[$i]["application_id"], $this->CURRENT_MIDDLENAME);

                $arrApps[$j]['personname'] = $this->correctstring(strtoupper($forName[0]["name"]) . " " . strtoupper($middleName[0]["name"]) . " " . strtoupper($surName[0]["name"]));
                $arrApps[$j]['dateofbirth'] = $this->getFormatedDate($rsWorksChildren[$i]["date_of_birth"], $format = 3);
                $arrApps[$j]['appid'] = $rsWorksChildren[$i]["application_id"];
                $arrApps[$j]['name'] = $rsWorksChildren[$i]["name"];
                $arrApps[$j]['dept_name'] = $rsWorksChildren[$i]["dept_name"];
                $strVolunteer = ($rsWorksChildren[$i]['remuneration'] == 'N') ? 'Yes' : 'No';
                $arrApps[$j]['volunteer'] = $strVolunteer;
                $arrApps[$j]['jobname'] = $rsWorksChildren[$i]["jobname"];
                $arrApps[$j]['regorg'] = $rsWorksChildren[$i]["regorg"];
                $j++;
            }
        }

        //Volunteer  Success Apps
        if (!empty($rsVolunteer) && count($rsVolunteer) > 0) {
            for ($i = 0; $i < count($rsVolunteer); $i++) {
                $surName = $this->getApplicantName($rsVolunteer[$i]["application_id"], $this->CURRENT_SURNAME);
                $forName = $this->getApplicantName($rsVolunteer[$i]["application_id"], $this->CURRENT_FORNAME);

                $arrApps[$j]['personname'] = $this->correctstring(strtoupper($forName[0]["name"]) . " " . strtoupper($surName[0]["name"]));
                $arrApps[$j]['dateofbirth'] = $this->getFormatedDate($rsVolunteer[$i]["date_of_birth"], $format = 3);
                $arrApps[$j]['appid'] = $rsVolunteer[$i]["application_id"];
                $arrApps[$j]['name'] = $rsVolunteer[$i]["name"];
                $arrApps[$j]['dept_name'] = $rsVolunteer[$i]["dept_name"];
                $strVolunteer = ($rsVolunteer[$i]['remuneration'] == 'N') ? 'Yes' : 'No';
                $arrApps[$j]['volunteer'] = $strVolunteer;
                $arrApps[$j]['jobname'] = $rsVolunteer[$i]["jobname"];
                $arrApps[$j]['regorg'] = $rsVolunteer[$i]["regorg"];
                $j++;
            }
        }

        //Fail Apps By Name
        if (!empty($rsFailByName) && count($rsFailByName) > 0) {
            for ($i = 0; $i < count($rsFailByName); $i++) {
                $surName = $this->getApplicantName($rsFailByName[$i]["application_id"], $this->CURRENT_SURNAME);
                $forName = $this->getApplicantName($rsFailByName[$i]["application_id"], $this->CURRENT_FORNAME);
                $middleName = $this->getApplicantName($rsFailByName[$i]["application_id"], $this->CURRENT_MIDDLENAME);


                $arrApps[$j]['personname'] = $this->correctstring(strtoupper($forName[0]["name"]) . " " . strtoupper($middleName[0]["name"]) . " " . strtoupper($surName[0]["name"]));
                $arrApps[$j]['dateofbirth'] = $this->getFormatedDate($rsFailByName[$i]["date_of_birth"], $format = 3);
                $arrApps[$j]['appid'] = $rsFailByName[$i]["application_id"];
                $arrApps[$j]['name'] = $rsFailByName[$i]["name"];
                $arrApps[$j]['orgId'] = $rsFailByName[$i]["org_id"];
                $arrApps[$j]['checker_id'] = $rsFailByName[$i]["checker_id"];
                $arrApps[$j]['dept_name'] = $rsFailByName[$i]["dept_name"];
                $strVolunteer = ($rsFailByName[$i]['remuneration'] == 'N') ? 'Yes' : 'No';
                $arrApps[$j]['volunteer'] = $strVolunteer;
                $arrApps[$j]['jobname'] = $rsFailByName[$i]["jobname"];
                $arrApps[$j]['regorg'] = $rsFailByName[$i]["regorg"];
                $j++;
            }
        }

        //Fail Apps By DOB
        if (!empty($rsFailByDOB) && count($rsFailByDOB) > 0) {
            for ($i = 0; $i < count($rsFailByDOB); $i++) {
                $surName = $this->getApplicantName($rsFailByDOB[$i]["application_id"], $this->CURRENT_SURNAME);
                $forName = $this->getApplicantName($rsFailByDOB[$i]["application_id"], $this->CURRENT_FORNAME);
                $middleName = $this->getApplicantName($rsFailByDOB[$i]["application_id"], $this->CURRENT_MIDDLENAME);


                $arrApps[$j]['personname'] = $this->correctstring(strtoupper($forName[0]["name"]) . " " . strtoupper($middleName[0]["name"]) . " " . strtoupper($surName[0]["name"]));
                $arrApps[$j]['dateofbirth'] = $this->getFormatedDate($rsFailByDOB[$i]["date_of_birth"], $format = 3);
                $arrApps[$j]['appid'] = $rsFailByDOB[$i]["application_id"];
                $arrApps[$j]['name'] = $rsFailByDOB[$i]["name"];
                $arrApps[$j]['orgId'] = $rsFailByDOB[$i]["org_id"];
                $arrApps[$j]['checker_id'] = $rsFailByDOB[$i]["checker_id"];
                $arrApps[$j]['dept_name'] = $rsFailByDOB[$i]["dept_name"];
                $strVolunteer = ($rsFailByDOB[$i]['remuneration'] == 'N') ? 'Yes' : 'No';
                $arrApps[$j]['volunteer'] = $strVolunteer;
                $arrApps[$j]['jobname'] = $rsFailByDOB[$i]["jobname"];
                $arrApps[$j]['regorg'] = $rsFailByDOB[$i]["regorg"];
                $j++;
            }
        }

        //Fail Apps By Address
        if (!empty($rsFailByAddress) && count($rsFailByAddress) > 0) {
            for ($i = 0; $i < count($rsFailByAddress); $i++) {
                $surName = $this->getApplicantName($rsFailByAddress[$i]["application_id"], $this->CURRENT_SURNAME);
                $forName = $this->getApplicantName($rsFailByAddress[$i]["application_id"], $this->CURRENT_FORNAME);
                $middleName = $this->getApplicantName($rsFailByAddress[$i]["application_id"], $this->CURRENT_MIDDLENAME);


                $arrApps[$j]['personname'] = $this->correctstring(strtoupper($forName[0]["name"]) . " " . strtoupper($middleName[0]["name"]) . " " . strtoupper($surName[0]["name"]));
                $arrApps[$j]['dateofbirth'] = $this->getFormatedDate($rsFailByAddress[$i]["date_of_birth"], $format = 3);
                $arrApps[$j]['appid'] = $rsFailByAddress[$i]["application_id"];
                $arrApps[$j]['name'] = $rsFailByAddress[$i]["name"];
                $arrApps[$j]['orgId'] = $rsFailByAddress[$i]["org_id"];
                $arrApps[$j]['checker_id'] = $rsFailByAddress[$i]["checker_id"];
                $arrApps[$j]['dept_name'] = $rsFailByAddress[$i]["dept_name"];
                $strVolunteer = ($rsFailByAddress[$i]['remuneration'] == 'N') ? 'Yes' : 'No';
                $arrApps[$j]['volunteer'] = $strVolunteer;
                $arrApps[$j]['jobname'] = $rsFailByAddress[$i]["jobname"];
                $arrApps[$j]['regorg'] = $rsFailByAddress[$i]["regorg"];
                $j++;
            }
        }

        //Fail Apps By ID
        if (!empty($rsFailByID) && count($rsFailByID) > 0) {
            for ($i = 0; $i < count($rsFailByID); $i++) {
                $surName = $this->getApplicantName($rsFailByID[$i]["application_id"], $this->CURRENT_SURNAME);
                $forName = $this->getApplicantName($rsFailByID[$i]["application_id"], $this->CURRENT_FORNAME);
                $middleName = $this->getApplicantName($rsFailByID[$i]["application_id"], $this->CURRENT_MIDDLENAME);


                $arrApps[$j]['personname'] = $this->correctstring(strtoupper($forName[0]["name"]) . " " . strtoupper($middleName[0]["name"]) . " " . strtoupper($surName[0]["name"]));
                $arrApps[$j]['dateofbirth'] = $this->getFormatedDate($rsFailByID[$i]["date_of_birth"], $format = 3);
                $arrApps[$j]['appid'] = $rsFailByID[$i]["application_id"];
                $arrApps[$j]['name'] = $rsFailByID[$i]["name"];
                $arrApps[$j]['orgId'] = $rsFailByID[$i]["org_id"];
                $arrApps[$j]['checker_id'] = $rsFailByID[$i]["checker_id"];
                $arrApps[$j]['dept_name'] = $rsFailByID[$i]["dept_name"];
                $strVolunteer = ($rsFailByID[$i]['remuneration'] == 'N') ? 'Yes' : 'No';
                $arrApps[$j]['volunteer'] = $strVolunteer;
                $arrApps[$j]['jobname'] = $rsFailByID[$i]["jobname"];
                $arrApps[$j]['regorg'] = $rsFailByID[$i]["regorg"];
                $j++;
            }
        }

        if (!empty($rsFailTotal) && count($rsFailTotal) > 0) {
            for ($i = 0; $i < count($rsFailTotal); $i++) {
                $surName = $this->getApplicantName($rsFailTotal[$i]["application_id"], $this->CURRENT_SURNAME);
                $forName = $this->getApplicantName($rsFailTotal[$i]["application_id"], $this->CURRENT_FORNAME);
                $middleName = $this->getApplicantName($rsFailTotal[$i]["application_id"], $this->CURRENT_MIDDLENAME);


                $arrApps[$j]['personname'] = $this->correctstring(strtoupper($forName[0]["name"]) . " " . strtoupper($middleName[0]["name"]) . " " . strtoupper($surName[0]["name"]));
                $arrApps[$j]['dateofbirth'] = $this->getFormatedDate($rsFailTotal[$i]["date_of_birth"], $format = 3);
                $arrApps[$j]['appid'] = $rsFailTotal[$i]["application_id"];
                $arrApps[$j]['name'] = $rsFailTotal[$i]["name"];
                $arrApps[$j]['orgId'] = $rsFailTotal[$i]["org_id"];
                $arrApps[$j]['checker_id'] = $rsFailTotal[$i]["checker_id"];
                $arrApps[$j]['dept_name'] = $rsFailTotal[$i]["dept_name"];
                $strVolunteer = ($rsFailTotal[$i]['remuneration'] == 'N') ? 'Yes' : 'No';
                $arrApps[$j]['volunteer'] = $strVolunteer;
                $arrApps[$j]['jobname'] = $rsFailTotal[$i]["jobname"];
                $arrApps[$j]['regorg'] = $rsFailTotal[$i]["regorg"];
                $j++;
            }
        }

        return $arrApps;
    }

    function verifyApps($arrParam) {
        $time = time();
        $listComp = $this->getChildCompanies($arrParam['company_id']);

        $sqlVerifyApps = "SELECT
                                        a.application_id, s.jobwork, s.remuneration, a.volunteer_release, a.works_with_children_release 
                                FROM
                                        applications a, sectionx s,organisation b, company c
                                WHERE
                                        a.application_id = s.application_id And a.org_id = b.org_id AND b.activated='Y' AND a.cancelled = 'N' AND a.good_to_print = 'S' AND a.cnt_sign_local = 'Y' AND s.checked_by <> ''
                                        AND b.activated='Y' AND a.org_id = b.org_id AND c.company_id = b.company_id AND b.company_id IN ($listComp) ";
        $rsVerifyApps = $this->getDBRecords($sqlVerifyApps);

        for ($i = 0; $i < count($rsVerifyApps); $i++) {
            $flagVol = true;
            $flagChil = true;
            if ($rsVerifyApps[$i]['remuneration'] == 'N' && $rsVerifyApps[$i]['volunteer_release'] == 'Y') {
                $flagVol = true;
            } elseif ($rsVerifyApps[$i]['remuneration'] == 'N' && $rsVerifyApps[$i]['volunteer_release'] == 'N') {
                $flagVol = false;
            }

            if ($rsVerifyApps[$i]['jobwork'] == $this->intWorksChildren && $rsVerifyApps[$i]['works_with_children_release'] == 'Y') {
                $flagChil = true;
            } elseif ($rsVerifyApps[$i]['jobwork'] == $this->intWorksChildren && $rsVerifyApps[$i]['works_with_children_release'] == 'N') {
                $flagChil = false;
            }

            if ($flagVol == true && $flagChil == true) {
                $sqlUpdateApp = " UPDATE
                                        applications SET cnt_sign_local = 'N',app_verified_by='" . $arrParam['adminId'] . "'
                                  WHERE
                                        application_id = '" . $rsVerifyApps[$i]['application_id'] . "'";
                $this->Query($sqlUpdateApp);

                $fieldArray = array('application_id' => $rsVerifyApps[$i]['application_id'], 'released_by' => $arrParam['adminId'], 'released_date' => $time, 'released_stage' => 'local');
                $this->Insert('countersign_log', $fieldArray);
            }
        }
    }

    #Check For Supliments

    function checkSupliments($applicationId) {
        $query = "select a.supliment_required as sup1, b.supliment_required as sup2 from applications a, sectionx b where a.application_id=b.application_id and a.application_id='$applicationId'";
        $sups = $this->getDBRecords($query);
        if ($sups[0]["sup1"] == 'Y' || $sups[0]["sup2"] == 'Y')
            return true;
        else
            return false;
    }

    function getAppdetailedHistory($applicationId, $appRefNo=null) {

        $timeNow = time();

        #get application details (Pre CRB stages)
        $query = "select ebulkapp,poca_check,poca_result,submit_time,second_check,sectionx_complete_time from applications where application_id='$applicationId'";
        $res = $this->getDBRecords($query);

        if (!empty($res[0]["submit_time"]))
            $submit_time = $res[0]["submit_time"];
        if (!empty($res[0]["sectionx_complete_time"]))
            $sectionx_complete_time = date("d-m-Y", $res[0]["sectionx_complete_time"]);
        $eBulkApplication = $res[0]["ebulkapp"];
        if (!empty($res[0]["second_check"]))
            $quality_check = $res[0]["second_check"];

        if (empty($appRefNo)) {
            $query = "select last_updated,updaterequested,request_completed,lpf_pending_stage, crberror,current_status,initial_check,pnc_stage,ppl_stage,cert_printing,cert_dispatched,lpfdate,lpfStartDate,crb_system,crn_number,dcrbDate as dCrbDate,rRdate,`p&sDate` as psdt,`s&rDate` as srdate,lpfdate,stop_updates,rejection_date,povaStart,povaEnd,list99Start,list99End,init_check_status,pnc_status,ppl_status,lpf_status,cert_printing_status,push_back_init_check,push_back_pnc,push_back_ppl,push_back_lpf,push_back_printing,in_conflict,result,disc_issue_date,certificate_seen_date,info_requested_by_adl,info_requested_by_dbs from form_status where application_id='$applicationId'";
        } else {
            $query = "select last_updated,updaterequested,request_completed,lpf_pending_stage, crberror,current_status,initial_check,pnc_stage,ppl_stage,cert_printing,cert_dispatched,lpfdate,lpfStartDate,crb_system,crn_number,dcrbDate as dCrbDate,rRdate,`p&sDate` as psdt,`s&rDate` as srdate,lpfdate,stop_updates,rejection_date,povaStart,povaEnd,list99Start,list99End,init_check_status,pnc_status,ppl_status,lpf_status,cert_printing_status,push_back_init_check,push_back_pnc,push_back_ppl,push_back_lpf,push_back_printing,in_conflict,result,disc_issue_date,certificate_seen_date,info_requested_by_adl,info_requested_by_dbs from form_status where app_ref_no='$appRefNo'";
        }
        $res = $this->getDBRecords($query);
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

        if($res[0]["rRdate"]< PDFRESULT_DATE){
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
            if (empty($res[0]["rRdate"])) {
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
            if (empty($res[0]["rRdate"])) {
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
            if (empty($res[0]["rRdate"])) {
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
            if (empty($res[0]["rRdate"])) {
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
            if (empty($res[0]["rRdate"])) {
                if ($res[0]["push_back_printing"] == "Y")
                    $printing_check_date = "<font color='red'><b>Push Back</b></font>";
                else
                    $printing_check_date="<font color='blue'><b>" . $res[0]["cert_printing_status"] . "</b></font>";
            }
        }

        #get Current status
        if ($eBulkApplication <> 'Y') {
            if ((!empty($submit_time) ) && (empty($sectionx_complete_time)))
                $current_status = "Awaiting ID Verification";

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
            {
                $current_status = $res[0]["result"];
            }
               

            if ($res[0]["result"] == 'Invalid Disclosure')
                $current_stage = "Invalid Disclosure";
        } else {

            if (!(empty($submit_time) ) && (!empty($sectionx_complete_time)))
                $current_status = "Application Submitted";

            if (!(empty($submit_time) ) && empty($sectionx_complete_time))
                 {
                           if($res[0]["info_requested_by_adl"] == "Y") $current_status=INFO_REQUESTED_STATUS;
	               else $current_status="Awaiting ID Verification";
                                }

            if (!empty($res[0]["second_check"]))
                $current_status = "Quality Check Completed";

            if (empty($res[0]["dCrbDate"]) && !empty($res[0]["s&rDate"]))
                $current_status = "In Dispatch Queue";

            if (!empty($res[0]["dCrbDate"]) && empty($res[0]["rRdate"]))
                {
                        if($res[0]["info_requested_by_dbs"] == "Y") $current_status=INFO_REQUESTED_STATUS_DBS;
		  elseif(!empty($res[0]["current_status"]) && $res[0]["current_status"]<>"") $current_status= trim($res[0]["current_status"]);
		  else $current_status="Dispatched to ".DBS;
	  }

            if ((!empty($res[0]["rRdate"]) && ($res[0]["rRdate"] != 'NULL')))
            {
                 $current_status = $res[0]["result"];
                    
            }

            if ($res[0]["result"] == 'Invalid Disclosure')
                $current_stage = "Invalid Disclosure";
        }

  


        // calculate the duration of the application entered into CRB system till date
        if (!empty($res[0]["crb_system"])) {
            if (empty($res[0]["cert_dispatched"]) && empty($res[0]["rRdate"]))
                $CRBentered = $this->dateDiff("/", date("m/d/Y", $timeNow), date("m/d/Y", $res[0]["crb_system"]));
            elseif (!empty($res[0]["cert_dispatched"]))
                $CRBentered = $this->dateDiff("/", date("m/d/Y", $res[0]["cert_dispatched"]), date("m/d/Y", $res[0]["crb_system"]));
            elseif (!empty($res[0]["rRdate"]))
                $CRBentered = $this->dateDiff("/", date("m/d/Y", $res[0]["rRdate"]), date("m/d/Y", $res[0]["crb_system"]));
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
            $initentered = "(" . $this->dateDiff("/", date("m/d/Y", $res[0]["initial_check"]), date("m/d/Y", $res[0]["crb_system"])) . " days at ".DBS.")";
        }
        if (!empty($res[0]["crb_system"]) && !empty($res[0]["pnc_stage"])) {
            $pncentered = "(" . $this->dateDiff("/", date("m/d/Y", $res[0]["pnc_stage"]), date("m/d/Y", $res[0]["crb_system"])) . " days at ".DBS.")";
        }
        if (!empty($res[0]["crb_system"]) && !empty($res[0]["ppl_stage"])) {
            $pplentered = "(" . $this->dateDiff("/", date("m/d/Y", $res[0]["ppl_stage"]), date("m/d/Y", $res[0]["crb_system"])) . " days at ".DBS.")";
        }
        if (!empty($res[0]["crb_system"]) && !empty($res[0]["lpfdate"]) && $res[0]["lpfdate"] != 0) {
            $lpfentered = "(" . $this->dateDiff("/", date("m/d/Y", $res[0]["lpfdate"]), date("m/d/Y", $res[0]["crb_system"])) . " days at ".DBS.")";
        }
        if (!empty($res[0]["crb_system"]) && !empty($res[0]["cert_printing"])) {
            $printingentered = "(" . $this->dateDiff("/", date("m/d/Y", $res[0]["cert_printing"]), date("m/d/Y", $res[0]["crb_system"])) . " days at ".DBS.")";
        }
        if (!empty($res[0]["crb_system"]) && !empty($res[0]["cert_dispatched"])) {
            $dispatchedentered = "(" . $this->dateDiff("/", date("m/d/Y", $res[0]["cert_dispatched"]), date("m/d/Y", $res[0]["crb_system"])) . " days at ".DBS.")";
        }




        if ($appRefNo == '') {
            $query = "select a.ebulkapp,CONCAT(a.work_force,' ',a.position) position, a.home_phone, a.prefered_phone,a.prefered_time,u.email, a.org_id from applications a 
            INNER JOIN reqdocs r ON a.application_id = r.app_id
            INNER JOIN users u ON r.user_id = u.user_id where a.application_id='$applicationId'";
        } else {
            $query = "select a.ebulkapp,CONCAT(a.work_force,' ',a.position) position, a.home_phone, a.prefered_phone,a.prefered_time,u.email, a.org_id from (applications a, sectionx x)
             INNER JOIN reqdocs r ON a.application_id = r.app_id
            INNER JOIN users u ON r.user_id = u.user_id
            where a.application_id=x.application_id and (a.application_id='$applicationId' or x.app_ref_no='$appRefNo')";
        }
        $res1 = $this->getDBRecords($query);
        $position = $res1[0]["position"];
        $home_phone = $res1[0]["home_phone"];
        $prefered_phone = $res1[0]["prefered_phone"];
        $prefered_time = $res1[0]["prefered_time"];
        $email = $res1[0]["email"];
        $orgid = $res1[0]["org_id"];
        $orgnam = $this->getOrgName($orgid);
        $companyID = $this->getCompId($orgid);
        $sqlgetcompname = "SELECT c.name compname FROM company c WHERE c.company_id = '$companyID'";
        $resgetcompname = $this->getDBRecords($sqlgetcompname);
        $compName = $resgetcompname[0]['compname'];
        $eBulkApplication1 = $res1[0]["ebulkapp"];


        //check whether the applicant has requested for POVA
        $qry = "select pova,list99,scotwork from sectionx where application_id='$applicationId'";
        $povares1 = $this->getDBRecords($qry);
        $povares = $povares1[0]['pova'];
        $list99 = $povares1[0]['list99'];
        $scotwork = $povares1[0]['scotwork'];

        /* Checked By */
        $idCheckedBy = "select u.name,u.surname,a.manual_application,s.checked_by from users u,sectionx s,applications a where a.application_id=s.application_id and a.application_id='$applicationId' and s.checker_id=u.user_id";

        $idCheckedByResult = $this->getDBRecords($idCheckedBy);
        $idCheckByUSerName = $idCheckedByResult[0]['name'] . " " . $idCheckedByResult[0]['surname'];
        $idCheckByUSerName = str_replace("\\", '', $idCheckByUSerName);
        $idSubmittedBy = $idCheckedByResult[0]['checked_by'];
        $manuallApp = $idCheckedByResult[0]['manual_application'];

        $address = $this->getApplicantAddress($applicationId, $this->CURRENT_ADDRESS);

        $surName = $this->getApplicantName($applicationId, $this->CURRENT_SURNAME);
        $forName = $this->getApplicantName($applicationId, $this->CURRENT_FORNAME);
        $name = $this->correctstring($forName[0]["name"] . " " . $surName[0]["name"]);
        $middleNameDisplay = $this->getApplicantName($applicationId, $this->CURRENT_MIDDLENAME);
        $middleName = $middleNameDisplay[0]["name"];
        $dob = $this->getApplicantDob($applicationId);

        if ($this->checkSupliments($applicationId) == true)
            $contd = "C";

        $errors = $this->getFormErrors($appRefNo);


        $formstatus['appRefNo'] = $appRefNo;
        $formstatus['contd'] = $contd;
        $formstatus['name'] = $name;
        $formstatus['middleName'] = $middleName;
        $formstatus['dob'] = $dob;
        $formstatus['position'] = $position;
        $formstatus['home_phone'] = $home_phone;
        $formstatus['prefered_phone'] = $prefered_phone;
        $formstatus['prefered_time'] = $prefered_time;
        $formstatus['email'] = $email;
        $formstatus['crn_number'] = $crn_number;
        $formstatus['last_update_date'] = $last_update_date;
        $formstatus['orgnam'] = $orgnam;
        $formstatus['compnam'] = $compName;
        $formstatus['current_status'] = $current_status;
        $formstatus['povares'] = $povares;
        $formstatus['list99'] = $list99;
        $formstatus['povastartdate'] = $povastartdate;
        $formstatus['povaenddate'] = $povaenddate;
        $formstatus['list99startdate'] = $list99startdate;
        $formstatus['list99enddate'] = $list99enddate;
        $formstatus['eBulkApplication'] = $eBulkApplication;
        $formstatus['submit_time'] = $submit_time;
        $formstatus['quality_check'] = $quality_check;
        $formstatus['sectionx_complete_time'] = $sectionx_complete_time;
        $formstatus['idCheckByUSerName'] = $idCheckByUSerName;
        $formstatus['idSubmittedBy'] = $idSubmittedBy;
        $formstatus['manuallApp'] = $manuallApp;

        $formstatus['initentered'] = $initentered;
        $formstatus['pncentered'] = $pncentered;
        $formstatus['pplentered'] = $pplentered;
        $formstatus['lpfentered'] = $lpfentered;
        $formstatus['printingentered'] = $printingentered;
        $formstatus['dispatchedentered'] = $dispatchedentered;

        $formstatus['init_check_date'] = $init_check_date;
        $formstatus['pnc_check_date'] = $pnc_check_date;
        $formstatus['ppl_check_date'] = $ppl_check_date;
        $formstatus['lpf_check_date'] = $lpf_check_date;
        $formstatus['printing_check_date'] = $printing_check_date;
        $formstatus['CRBentered'] = $CRBentered;
        $formstatus['cert_seen']=$cert_seen;

        /* Result */
        $formstatus['resultState'] = $res[0]['result'];
        /* Result */

        if (!empty($psdate)) {
            $psdate = date("d-m-Y", $psdate);
            $formstatus['psdate'] = $psdate;
        }
        if (!empty($srDate)) {
            $srDate = date("d-m-Y", $srDate);
            $formstatus['srDate'] = $srDate;
        }
        if (!empty($submit_time)) {
            $submit_date = date("d-m-Y", $submit_time);
            $formstatus['submit_date'] = $submit_date;
        }
        if (!empty($submit_time)) {
            $submit_time = date("H:i:s", $submit_time);
            $formstatus['submit_time'] = $submit_time;
        }
        if (!empty($quality_check)) {
            $quality_check = date("d-m-Y", $quality_check);
            $formstatus['quality_check'] = $quality_check;
        }
        if (!empty($rrdate)) {
            $rrdate = date("d-m-Y", $rrdate);
            $formstatus['rrdate'] = $rrdate;
        }
        if (!empty($in_conflict)) {
            $in_conflict = date("d-m-Y", $in_conflict);
            $formstatus['in_conflict'] = $in_conflict;
        }
        $formstatus['dcrbDate'] = $dcrbDate;
        $formstatus['datediffer'] = $datediffer;


        /* Check for Countersign,Verified Date and completed By */
        $selectCountVer = "select f.`p&sDate` counterCompleted,if(countLog.released_stage='local', countLog.released_date,'') verificationDate,if(a.app_verified_by<>'0',CONCAT(usrV.name,' ',usrV.surname),'') verifiedBy,if(a.app_countersigned_by<>'0',CONCAT(usrC.name,' ',usrC.surname),'') countersignedBY FROM applications a
                                INNER JOIN sectionx s ON a.application_id = s.application_id
                                INNER JOIN organisation o ON a.org_id = o.org_id
                                INNER JOIN form_status f ON a.application_id=f.application_id
                                LEFT JOIN countersign_log countLog ON a.application_id = countLog.application_id and released_stage = 'local' 
                                LEFT JOIN users usrV ON a.app_verified_by = usrV.user_id
                                LEFT JOIN users usrC ON a.app_countersigned_by = usrC.user_id WHERE a.application_id='$applicationId'";
        $resultCountVer = $this->getDBRecords($selectCountVer);
        if (!empty($resultCountVer[0]['counterCompleted']))
            $couterCompletedDate = date("d-m-Y", $resultCountVer[0]['counterCompleted']);
        if (!empty($resultCountVer[0]['verificationDate']))
            $verificationDateVer = date("d-m-Y", $resultCountVer[0]['verificationDate']);

        $verifiedByUserName = $resultCountVer[0]['verifiedBy'];
        $countersignedBY = $resultCountVer[0]['countersignedBY'];

        $arrayDetails['formstatus'] = $formstatus;
        $arrayDetails['address'] = $address;
        $arrayDetails['couterCompletedDate'] = $couterCompletedDate;
        $arrayDetails['verificationDateVer'] = $verificationDateVer;
        $arrayDetails['verifiedByUserName'] = $verifiedByUserName;
        $arrayDetails['countersignedBY'] = $countersignedBY;
        $arrayDetails['res'] = $res;

        $query = "select * from application_comments where application_id='$applicationId' order by entered_on desc";
        $commentsres = $this->getDBRecords($query);

        $arrayDetails['cntcommentsres'] = count($commentsres);
        $arrayDetails['commentsres'] = $commentsres;


        $queryFormComments = "select * from form_comments where application_id='$applicationId' order by id desc";
        $resultFormComments = $this->getDBRecords($queryFormComments);

        $listComp = $this->getChildCompanies($company_id);
        $query = "select organisation.*,address.* from organisation,company , org_address, address where organisation.org_id=org_address.org_id and address.address_id=org_address.address_id and organisation.org_id='$orgid' and organisation.company_id=company.company_id and company.company_id in ($listComp)";
        $orgDetails = $this->getDBRecords($query);
        $compId = $orgDetails[0]['company_id'];

        $restulDisplay = $this->getChildCompaniesHeirachy($orgDetails[0]['company_id']);
        $strOrg = explode(',', $restulDisplay);
        $arrayList = array();
        /* for ($i = 0; $i < count($strOrg); $i++) {
          $loSelect = "SELECT company_id,name FROM company WHERE company_id='".$strOrg[$i]."'";
          $loSelectResult=getDBRecords($loSelect);
          $arrayList[$i]['name']=$loSelectResult[0]['name'];
          } */
        if ($restulDisplay != '') {
            $loSelect = "SELECT company_id,name,parent_id FROM company WHERE company_id IN (" . $restulDisplay . ") order by parent_id";
            $loSelectResult = $this->getDBRecords($loSelect);

            $arrayList[0]['name'] = '';
            for ($i = 0; $i < count($loSelectResult); $i++) {
                $arrayList[$i + 1]['name'] = $loSelectResult[$i]['name'];
            }
        }

        $arrayDetails['hierchy'] = $arrayList;
        $arrayDetails['cntresultFormComments'] = count($resultFormComments);


        return $arrayDetails;
    }

    function getWortAtHomeAddressLog($applicationId)
    {
        //Query to check Application Working from Home Changed
        $queryWfh="select Concat(u.name, ' ',u.surname) as adminName,from_unixtime(lwhm.modified_datentime, '%d/%m/%Y') as modDate from log_work_at_home_modified lwhm INNER JOIN users u On lwhm.modified_user_id = u.user_id where lwhm.application_id='$applicationId' and lwhm.new_value = 'N'";
        $resultWfh=$this->getDBRecords($queryWfh);  
        return $resultWfh;
    }
    
    public function insertIntoLoUsers($arrLoUserDetails) {

        #Insert All Posted Value to the table "lo_users" table
        $result = $this->Insert("lo_users", $arrLoUserDetails);

        return $result;
    }

    public function insertIntoOrgUser($arrOrgUserDetails) {

        #Insert All Posted Value to the table "org_users" table
        $result = $this->Insert("org_users", $arrOrgUserDetails);

        return $result;
    }

    function cancelApplication($appId, $reason=null, $userName=null) {
        $tableName = 'applications';
        $fieldArray['cancelled'] = 'Y';
        $fieldArray['cancelled_date'] = time();
        $condition = "application_id = '$appId'";

        $this->Update($tableName, $fieldArray, $condition);

        $userId = $this->getUserId($userName);
        $fieldArray = null;
        $tableName = 'cancellation_reason';
        $fieldArray['application_id'] = $appId;
        $fieldArray['user_id'] = $userId;
        $fieldArray['comment'] = $reason;

        $this->Insert($tableName, $fieldArray);
    }

    function updateDisputedResults($appId, $appref) {
        $query = "select a.application_id,a.date_of_birth,secx.app_ref_no,a.org_id,f.rRdate,f.certno,f.result,f.disc_issue_date from applications a,sectionx secx,form_status f where f.app_ref_no=secx.app_ref_no and a.application_id=secx.application_id and a.application_id='$appId' and secx.app_ref_no='$appref'";
        $AppDetails = $this->getDBRecords($query);

        $dob = $this->getFormatedDate($AppDetails[0]['date_of_birth'], 3);
        if (!empty($AppDetails[0]["rRdate"])) {
            $rrdate = date("d/m/Y", $AppDetails[0]["rRdate"]);
        } else {
            $rrdate = '';
        }
        $certno = $AppDetails[0]['certno'];
        $app_ref_no = $AppDetails[0]['app_ref_no'];

        /* Comment Result */
        $queryComment = "select * from positive_result_log WHERE app_ref_no='$app_ref_no'";
        $resultComment = $this->getDBRecords($queryComment);
        /* Comment Result */
        /* form comment */
        $queryFormComments = "select * from form_comments where application_id='$appId' order by id desc";
        $resultFormComments = $this->getDBRecords($queryFormComments);
        /* form comments */

        $surName = $this->getApplicantName($appId, 3);
        $forName = $this->getApplicantName($appId, 4);

        $arrayResDetails['rrdate'] = $rrdate;
        $arrayResDetails['dob'] = $dob;
        $arrayResDetails['certno'] = $certno;
        $arrayResDetails['surName'] = $this->correctcase($surName[0]['name']);
        $arrayResDetails['forName'] = $this->correctcase($forName[0]['name']);
        $arrayResDetails['result'] = $AppDetails[0]['result'];
        $arrayResDetails['commentsCnt'] = count($resultComment);
        $arrayResDetails['cntresultFormComments'] = count($resultFormComments);
        $arrayResDetails['disc_issue_date'] = $AppDetails[0]['disc_issue_date'];

        return $arrayResDetails;
    }

    function processDisputedResults($username, $appRefNo, $crbresult, $applicationId, $certno, $timeNow,$cert_seen,$arrLangData) {
        $userid = $this->getUserId($username);
        #store previous cert number
        $query = "select certno,rRdate,result from form_status where  app_ref_no ='$appRefNo'";
        $olddata = $this->getDBRecords($query);
        $oldcertno = $olddata[0]['certno'];
        $oldrRdate = $olddata[0]['rRdate'];
        $oldResult = $olddata[0]['result'];


        $fieldArray = null;
        $fieldArray['app_ref_no'] = $appRefNo;
        $fieldArray['old_certno'] = $oldcertno;
        $fieldArray['prev_rRdate'] = $oldrRdate;
        $fieldArray['prev_result'] = $oldResult;
        $fieldArray['user_id'] = $adminid;
        $fieldArray['name'] = $uname;
        $tableName = 'certificate_history';
        $this->Insert($tableName, $fieldArray);


        $fieldArray = null;
        if ($crbresult == "Error on Certificate") {

            $fieldArray['rRdate'] = $timeNow;
            $fieldArray['certno'] = $certno;
            $fieldArray['result'] = $crbresult;
            $tableName = 'form_status';
            $condition = "app_ref_no='$appRefNo'";
        } else {
            $fieldArray['rRdate'] = $timeNow;
            $fieldArray['certno'] = $certno;
            $fieldArray['result'] = $crbresult;
            $fieldArray['formstatus'] = 'CR';
            $tableName = 'form_status';
            $condition = "app_ref_no='$appRefNo'";
        }

        $this->Update($tableName, $fieldArray, $condition);



        #save form comments for this applicant
        $query = "select * from certificate_history where  app_ref_no ='$appRefNo' order by cert_id";
        $olddata = $this->getDBRecords($query);

        $memo = "Replacement certificate received<br>
                            New cert no: " . $certno . "<br>
                            New result received date: " . date('d-m-Y', $timeNow) . "<br>
                            New Result : " . $crbresult . "<br><br>
                            Previous cert no: " . $oldcertno . "<br>
                            Previous result received date: " . date('d-m-Y', $oldrRdate) . "<br>
                            Previous Result : " . $oldResult . "<br><br>
                            Edited by : System";

        $fieldArray = null;
        $fieldArray['appRefNo'] = $appRefNo;
        $fieldArray['memo'] = $memo;
        $fieldArray['application_id'] = $applicationId;
        $fieldArray['action_taken_by'] = $adminid;
        $tableName = 'form_comments';
        $this->Insert($tableName, $fieldArray);

        $querySelectAppID = "select sectionx_id,application_id,checker_id,checked_by from sectionx where app_ref_no ='$appRefNo'";
        $querySelectAppIDResult = $this->getDBRecords($querySelectAppID);
        $AppId = $querySelectAppIDResult[0]['application_id'];
        $AppIduser = $querySelectAppIDResult[0]['checker_id'];
        $AppIduserCheckedBy = $querySelectAppIDResult[0]['checked_by'];
        $queryUsers = "select email,surname,name from users where user_id = '$AppIduser'";
        $queryUsersResult = $this->getDBRecords($queryUsers);
        $email = $queryUsersResult[0]['email'];
        $name = $queryUsersResult[0]['name'];
        $surname = $queryUsersResult[0]['surname'];
        $appForone = $this->getApplicantName($AppId, 4);
        $appFor = $appForone[0]['name'];
        $appSurone = $this->getApplicantName($AppId, 3);
        $appSur = $appSurone[0]['name'];
        $appName = $this->correctcase(addslashes($appFor)) . " " . $this->correctcase(addslashes($appSur));
        if (!empty($email)) {
            if (eregi("^[A-Za-z0-9'\.\\-_]+@[A-Za-z0-9\.\\-_]+\.[A-Za-z]+$", $email)) {
                $loname = $this->correctcase(addslashes($name)) . " " . $this->correctcase(mysql_escape_string(str_replace("\\", '', $surname)));
                $this->sendUpdateDesputedMail($loname, $appName, $email, $arrLangData);
            }
        }
        #---------------------insert into messages-------------------
//            global $username;
//            $userid=getUserId($username);
        $timeNow = date("YmdHis");
        $msg = $this->message_content($arrLangData, $appName);

        $priority = "normal";
        $msg_type = "Message";
        $contents = addslashes($msg);
        $subject = "Update Disputed Result";

        $sent_by = $userid;
        $sent_as = "" . $arrLangData['kindRegards'];

        if(VERIFY_CERTIFICATE != 'N') {
            if($cert_seen=='Y') {
                $timenow=time();
              $fieldArray = null;
                $fieldArray["certificate_seen_date"] = $timenow;
                $tableName = "form_status";
                $condition = " app_ref_no='$appRefNo' and application_id='$applicationId'";
                $this->Update($tableName,$fieldArray,$condition);
                $this->certificate_seen_log($timenow,$applicationId,$userid);
            }else {
                $fieldArray = null;
                 $fieldArray["certificate_seen_date"] = '0';
                $tableName = "form_status";
                $condition = " app_ref_no='$appRefNo' and application_id='$applicationId'";
                 $this->Update($tableName,$fieldArray,$condition);
            }
}
        /* $query = "insert into messaging(message,subject,msg_type,sent_by,sent_as,priority,msg_sent_dt) values('$contents','$subject','$msg_type','$sent_by','$sent_as','$priority','$timeNow')";
          updateDBRecord($query);

          $query = "select max(msg_id) as msgid from messaging where sent_by='$sent_by'";
          $value = $this->getDBRecords($query);
          $msg_id = $value[0]['msgid'];

          $query = "update messaging set parent_msgid='$msg_id' where msg_id='$msg_id'";
          updateDBRecord($query);

          $query = "insert into msg_sent_to(msg_id,user_id,received_as) values ('" . $msg_id . "','" . $AppIduser . "','" . $AppIduserCheckedBy . "')";
          updateDBRecord($query); */
    }
    
    function processPositiveResults($username, $appRefNo,$certno, $crbresult,$discissue,$comments, $application_Id,$timeNow,$cert_seen)
    {
        $logged_in_person = $this->getUserId($username);
        
        $fieldArray = null;
        $fieldArray['current_status'] = 'Result Received';
        $fieldArray['rRdate']         = $timeNow;
        $fieldArray['certno']         = $certno;
        $fieldArray['result']         = $crbresult;
        $fieldArray['disc_issue_date']= $discissue;
        $tableName                    = 'form_status';
        $condition                    = "app_ref_no='$appRefNo'";
        
        
        $res = $this->Update($tableName, $fieldArray, $condition);
    
        $timeLog = time();
        
        $fieldArray = null;
        $fieldArray['certno']         = $certno;
        $fieldArray['result']         = $crbresult;
        $fieldArray['app_ref_no']     = $appRefNo;
        $fieldArray['action_taken_by']= $logged_in_person;
        $fieldArray['time']           = $timeLog;
        $fieldArray['comments']       = $comments;
        $tableName                    = 'positive_result_log';
      
        $resAuditLog = $this->Insert($tableName, $fieldArray);
        
        if (VERIFY_CERTIFICATE != 'N') {
            if ($cert_seen == 'Y') {
                $timenow = time();
                $fieldArray = null;
                $fieldArray["certificate_seen_date"] = $timeLog;
                $condition = " app_ref_no='$appRefNo' and application_id='$application_Id' ";
                $tableName = "form_status";
                $this->Update($tableName,$fieldArray,$condition);

               
                $this->certificate_seen_log($timeLog, $application_Id, $logged_in_person);
            } else {
                $fieldArray = null;
                $fieldArray["certificate_seen_date"] = '0';
                $condition = "  app_ref_no='$appRefNo' and application_id='$application_Id'";
                $tableName = "form_status";
                $this->Update($tableName, $fieldArray,$condition);
            }
        }


}

#Send Notification to email
function sendNewMessageNotification($to_user_id,$to_name,$subject,$fromEmail,$replyEmail)
{
   #check if the user enabled this option
   $query="select message_email,email from users where user_id ='$to_user_id' and access_level NOT IN ('ebulkuser','eremote') limit 1";
   $email_option=getDBRecords($query); 

   if($email_option[0]['message_email'] == "Y")
   { 
         $email = $email_option[0]['email'];
         $html="<p><font size=\"2\" face=\"Verdana, Arial, Helvetica, sans-serif\">Dear ".$to_name.",<br><br>
               You have been sent a message via the online ".DBS." system regarding <b>".$subject."</b>, please login to your secure online account to read the message.
			   <br><br>Thank You</font></p>";


		$text="Dear ".$to_name.",
               
			   You have been sent a message via the online ".DBS." system regarding ".$subject.", please login to your secure online account to read the message.
			   
			   Thank You";

		 
		$from = $fromEmail;
		$mail = new htmlMimeMail();
		$mail->setHtml($html, $text);
		$mail->setReturnPath($replyEmail);
        $mail->setFrom($from);
        $mail->setSubject("Important Message");
        $mail->setHeader('X-Mailer', 'HTML Mime mail class');

        if(!empty($email))
	    {
           $result = $mail->send(array($email), 'smtp');	
		}
   }  	
}

    function message_content($arrLangData, $appName) {
        $msg = "Result updated for an online application for the applicant <strong>" . $this->correctcase(addslashes($appName)) . "</strong><br /><br />Kind regards <br><br>" . $arrLangData['kindRegards'];
        return $msg;
    }

    function sendUpdateDesputedMail($loname, $appName, $email, $arrLangData) {
        $msg = "Dear " . str_replace("\\", '', $loname) . "<br><br>

    Result updated for an online application for the applicant <strong>" . $this->correctcase($appName) . "</strong><br><br>

			Kind regards<br><br>"
                . $arrLangData["kindRegards"] . "";


        $msg2 = "Dear " . str_replace("\\", '', $loname) . "

 Result updated for an online application for the applicant " . $this->correctcase($appName) . "

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
        $mail->setReturnPath($arrLangData["emailReplyTo"]);
        $mail->setFrom($arrLangData["emailFrom"]);
        $mail->setSubject('Update Disputed Result');
        $mail->setHeader('X-Mailer', 'HTML Mime mail class');
        $result = $mail->send(array($email), 'smtp');
    }

    /* function to update user registraiton table once applicatin is submitted */

    public function appRegCompleted($username) {

        $selectUser = "select unique_key from users where username='$username'";
        $resUserDetail = $this->getDBRecords($selectUser);
        $username = $resUserDetail[0]['unique_key'];

        $arrComp = array();
        $arrComp['appCompleted'] = 'Y';
        $condition = " username='$username'";
        $this->Update("user_registration", $arrComp, $condition);
    }

    /* function to update user registraiton table once applicatin is submitted */

    public function getEbulkApp($appId) {

        $selectQuery = "select ebulkapp from applications where application_id='$appId'";
        $res = $this->getDBRecords($selectQuery);
        return $res;
    }

    /* function to get application refrence number form form_status table */

    public function getAppRefNo($confid) {
        $selectAppRefNo = 'select app_ref_no from form_status where application_id = ' . $confid . ' limit 1';
        $resultAppRefNo = $this->getDBRecords($selectAppRefNo);
        return $resultAppRefNo;
    }

    /* function to get applicant PDF information  */

    public function getAppPDFInfo($_app_ref_no) {
        $pdfquery = "select if(erb.file_id < 372,1,0) isaflag, erb.rb_app_ref_no,erb.disclosure_type,erb.disclosure_number,concat(substr(erb.disclosure_issue_date,9,2),'/',substr(erb.disclosure_issue_date,6,2),'/',substr(erb.disclosure_issue_date,1,4)) disclosure_issue_date,erb.emp_position_applied_for, erb.cntsig_rb_name,erb.cntsig_full_name,erb.emp_org_name, erb.app_surname ,erb.app_forname, concat(substr( erb.app_dob,9,2),'/',substr( erb.app_dob,6,2),'/',substr( erb.app_dob,1,4)) app_dob,concat(substr(erb.disclosure_issue_date,9,2),'/',substr(erb.disclosure_issue_date,6,2),'/',substr(erb.disclosure_issue_date,1,4)),erb.app_place_of_birth, erb.app_gender, erb.app_addr_line1, erb.app_addr_line2, erb.app_addr_town, erb.app_addr_country, erb.app_addr_postcode,
erb.app_addr_country_code,erb.disc_police_records_of_convictions,erb.disc_edu_act_list, erb.disc_protection_child_act, erb.disc_vulnerable_adult,disc_isa_child_barred_list,disc_isa_vulnerable_adult_barred_list,erb.ecert_other_relevant_infn
from ebulk_result_batch erb where erb.app_frm_ref_no = '$_app_ref_no'";

        $pdfres = $this->getDBRecords($pdfquery);
        return $pdfres;
    }

    public function getAdditionalNames_EbulkResult($rb_app_ref_no) {
	$pdfres = '';	
	if($rb_app_ref_no != '')
        {        
		$pdfquery = "select erbpn.result_name FROM ebulk_result_batch_person_names erbpn where erbpn.result_name != 'None Declared' AND erbpn.rb_ref_no = '$rb_app_ref_no'";
        	$pdfres = $this->getDBRecords($pdfquery);
	}        
	return $pdfres;
    }

    /*
     * Get Registered body name
     */
    public function getRbName() {
        $rbNameQuery = "select rb_number_name rbname from ebulk_rb_detail where rb_detail_id=1";
        $rbName = $this->getDBRecords($rbNameQuery);
        return $rbName[0]['rbname'];
    }

    /* function to get applicant form status infomration */

    public function getAppFormStatusInfo($confid) {
        $query = "select if(disc_issue_date > unix_timestamp(date_sub(now(), interval 6 month)),'Y','N') disp_res_flg,b.app_ref_no,b.certno,a.photoimage,b.result,b.rRdate,CONCAT(app.work_force, ' ',app.position) position,app.national_insurance,app.org_id,a.discType,a.jobwork,b.disc_issue_date,b.certificate_seen_date,if(a.remuneration = 'N','Yes','No') as confirm_volunteer,app.title from applications app,sectionx a,form_status b where a.application_id=app.application_id and a.app_ref_no=b.app_ref_no and a.application_id='$confid'";
        $res = $this->getDBRecords($query);
        return $res;
    }
    
    /*
     * Replacement certiticate Details
     */
    public function getReplacementCertDetails($confid) {
        $query = "SELECT application_Id,from_unixtime(date_of_reprint,'%d/%m/%Y') as date_of_reprint,requested_by,replacing_certificate_number FROM log_replacement_certificate WHERE application_Id ='$confid'";
        $res = $this->getDBRecords($query);
        return $res;
    }

    /* function to get applicant Works With infomration */

    public function getWorksWith($jobwork) {
        if ($jobwork == 1)
            $workswith = VG_FULL;
        elseif ($jobwork == 2)
            $workswith = "CHILDREN";
        elseif ($jobwork == 3)
            $workswith = "BOTH ".VG_FULL." AND CHILDREN";
        else
            $workswith="NONE";

        return $workswith;
    }

    /*
     * Function to get application details
     * param 1  : appId : Application Id
     */

    function getApplicationDetails($appId) {
        $query = "SELECT UPPER(pnf.name) as fname, UPPER(pns.name) as sname,a.date_of_birth as dob,s.app_ref_no from applications a
              inner join sectionx s ON a.application_id = s.application_id
              inner join app_person_name apnf on a.application_id = apnf.application_id
              inner join person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4
              inner join app_person_name apns on a.application_id = apns.application_id
              inner join person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3 
              WHERE a.application_id = '$appId'";
        $res = $this->getDBRecords($query);

        $arrRes['fname'] = $res[0]["fname"];
        $arrRes['sname'] = $res[0]["sname"];
        $arrRes['dob'] = $this->getFormatedDate($res[0]["dob"], $format = 3);
        $arrRes['app_ref_no'] = $res[0]["app_ref_no"];

        return $arrRes;
    }

    /* Get Lo User Id */

    public function getLoUserId($userName) {
        $query = "select lo_id from lo_users where user_id='" . $this->getUserId($userName) . "'";
        $res = $this->getDBRecords($query);
        $louserID = $res[0]['lo_id'];
        return $louserID;
    }

    public function getLOUserDeatils($orgId) {
        $query = "select a.lo_id from liason_officer a,lo_org b where a.lo_id=b.lo_id and b.org_id='$orgId' order by a.primecontact desc LIMIT 0,1";
        $res = $this->getDBRecords($query);
        $officer_id = $res[0]["lo_id"];
        return $officer_id;
    }

    #set LO ID

    public function setLOUserId($officer_id, $orgId, $maxuserid) {

        $condition = " user_id ='$maxuserid' limit 1";

        $arrDetail = array();
        $arrDetail['lo_id'] = $officer_id;

        $updateLoUser = $this->Update("lo_users", $arrDetail,$condition);

        unset($arrDetail);
        $arrDetail = array();
        $arrDetail['org_id'] = $orgId;

        $updateLoUser = $this->Update("org_users", $arrDetail, $condition);
    }

    #Get Invitiation Refrence

    public function getInvitationReference($maxUserId) {
        $query = "select invitation_reference from lo_users WHERE user_id = '$maxUserId' LIMIT 0,1";
        $res = $this->getDBRecords($query);
        $invitation_reference = $res[0]['invitation_reference'];
        return $invitation_reference;
    }

    #Insert into Req Docs

    public function insertIntoReqDocs($selectedDocument) {


        #Insert All Posted Value to the table "reqdocs" table
        $result = $this->Insert("reqdocs", $selectedDocument);

        $query = "select max(id) as rid from reqdocs where user_id='" . $selectedDocument['user_id'] . "'";
        $res = $this->getDBRecords($query);
        $rid = $res[0]["rid"];

        return $rid;
    }

    #fetch ReqDocs Details

    public function getAllReqDocsDetails($rid) {

        $selectReqDocs = "select * from reqdocs where id='$rid'";
        $resultReqDocs = $this->getDBRecords($selectReqDocs);
        return $resultReqDocs;
    }

    public function setComments($arrParam) {
        $result = $this->Insert("application_comments", $arrParam);
    }

    //Function to confirm send updates

    function processSendUpdates($application_id, $contents, $subject, $msg_type, $send_by, $send_as, $priority, $curdate, $send_to, $orgId) {
        if (!empty($application_id)) {
            $time = time();
            $fieldArray['hold_contacted'] = $time;
            $tableName = 'applications';
            $condition = "application_id='$application_id'";

            $this->Update($tableName, $fieldArray, $condition);
        }

        #------------insert into messages---------------

        $fieldArray = '';
        $fieldArray['message'] = addslashes($contents);
        $fieldArray['subject'] = addslashes($subject);
        $fieldArray['msg_type'] = $msg_type;
        $fieldArray['sent_by'] = $send_by;
        $fieldArray['sent_as'] = $send_as;
        $fieldArray['priority'] = $priority;
        $fieldArray['msg_sent_dt'] = $curdate;
        $tableName = 'messaging';
        $this->Insert($tableName, $fieldArray);


        $query = "select max(msg_id) as msgid from messaging where sent_as='$send_as'";
        $value = $this->getDBRecords($query);
        $msg_id = $value[0]['msgid'];

        $fieldArray = '';
        $fieldArray['parent_msgid'] = $msg_id;
        $tableName = 'messaging';
        $condition = "msg_id='$msg_id'";
        $this->Update($tableName, $fieldArray, $condition);



        if ($send_to == 'ALL') {
            $query = "select concat(u.name,' ',u.surname) name,u.user_id from  users u  where u.active='Y' and access_level = 'company1'";
            $res = $this->getDBRecords($query);
            for ($i = 0; $i < count($res); $i++) {
                $fieldArray = '';
                $fieldArray['msg_id'] = $msg_id;
                $fieldArray['user_id'] = $res[$i]['user_id'];
                $fieldArray['received_as'] = addslashes($res[$i]['name']);
                $tableName = 'msg_sent_to';
                $this->Insert($tableName, $fieldArray);
            }
        } else {
            $res = "select concat(u.name,' ',u.surname) name from users u where u.user_id='$send_to'";
            $qryres = $this->getDBRecords($res);
            $received_as = $qryres[0]['name'];

            $fieldArray = '';
            $fieldArray['msg_id'] = $msg_id;
            $fieldArray['user_id'] = $send_to;
            $fieldArray['received_as'] = $received_as;
            $tableName = 'msg_sent_to';
            $this->Insert($tableName, $fieldArray);
        }
        #Link application to messages

        $fieldArray = '';
        $fieldArray['msg_id'] = $msg_id;
        $fieldArray['application_id'] = $application_id;
        $tableName = 'application_messages';
        $this->Insert($tableName, $fieldArray);
    }

    public function setAppsToHold($arrParam) {
        $intLoggedInUser = $this->getFromSession("user_id_M");
        $timeValue = time();
        for ($i = 0; $i < $arrParam['totalApps']; $i++) {
            $applicationId = null;
            $appRefNo = null;
            if (isset($arrParam["app_id" . $i])) {
                $application_id = $arrParam["app_id" . $i];
                $arrDataUpdate = array('good_to_print' => 'H', 'cnt_sign_local' => 'Y');
                $condition = " application_id='$application_id' ";
                $result = $this->Update("applications", $arrDataUpdate, $condition);

                $comments = "Application put on hold";
                $query = "INSERT INTO `application_onhold_reason` (`application_id` ,`user_id` ,`comment`) VALUES ('$application_id', '$intLoggedInUser','$comments')";
                $this->Query($query);
            }
        }
    }
    
    function getUserInfo($user_id) {
    $user = "";
    if (!empty($user_id) && $user_id != 0) {
        if ($user_id == "-1")
            $user = "Updated By System";
        else {
            $query = "select username,name,surname from users where user_id='$user_id'";
            $res = $this->getDBRecords($query);
            $user = $res[0]['name'] . " " . $res[0]['surname'];
        }
    }
    return $user;
}


public function nameFormat($name)
{
    $arrName = explode(' ', $name);
    $newName = '';
    
    for($i =0;$i < count($arrName);$i++)
    {
        $newName .= ucfirst(strtolower($arrName[$i])).' ';
    }
    return $newName;
}

public function getCRN($appId)
{
    $db_list_id = CENTRAL_DB_ID;
        if($db_list_id != '')
        {
             $this->postoffice_db();
             $sqlGetCRN = "call get_postoffice_reference ($db_list_id, $appId)";
            $resGetCRN = $this->getDBRecords($sqlGetCRN);
            $CRN = $resGetCRN[0]['client_ref'];
            
            $this->constructDB();

            //$deleteCRNApplication = "DELETE FROM log_postoffice_referece where application_id = '$application_id'";
            $res = $this->Delete('log_postoffice_referece', "application_id = '$appId'");

            //$updateCRNApplication = "INSERT INTO log_postoffice_referece  (client_ref,application_id) values ('$CRN','$application_id')";
            $fieldArray = "";
            $fieldArray["client_ref"] = $CRN;
            $fieldArray["application_id"] = $appId;
            $tableName = "log_postoffice_referece";
            $res = $this->Insert($tableName, $fieldArray);
        }
        return $CRN;
}

public function getApplicantLetter($username)
{
    $userid= $this->getUserId($username);
    $getAppDetails = "SELECT lpr.client_ref,lpr.application_id from log_postoffice_referece lpr
                   INNER JOIN applications a ON lpr.application_id = a.application_id
                   INNER JOIN reqdocs r ON a.application_id = r.app_id
                   WHERE r.user_id = '$userid' and a.cancelled <> 'Y'";
    $resAppDetails = $this->getDBRecords($getAppDetails);
    $application_id = $resAppDetails[0]['application_id'];
    $CRN = $resAppDetails[0]['client_ref'];
    if($CRN != '' && $application_id != '')
    {
         $getAppName = "SELECT pnf.name as fname,pns.name as sname,pnm.name as mname,s.remuneration FROM applications a 
             INNER JOIN sectionx s ON a.application_id = s.application_id 
             INNER JOIN app_person_name apnf on a.application_id = apnf.application_id
                    INNER JOIN person_names pnf on apnf.name_id = pnf.name_id and pnf.name_type_id = 4
                    INNER JOIN app_person_name apns on a.application_id = apns.application_id
                    INNER JOIN person_names pns on apns.name_id = pns.name_id and pns.name_type_id = 3
                    INNER JOIN app_person_name apnm on a.application_id = apnm.application_id
                    INNER JOIN person_names pnm on apnm.name_id = pnm.name_id and pnm.name_type_id = 9
                    WHERE a.application_id = '$application_id'";
         $resAppName = $this->getDBRecords($getAppName);
         $fullName = stripslashes($resAppName[0]['fname']);
        if ($resAppName[0]['mname'] != '')
            $fullName .= ' ' . stripslashes($resAppName[0]['mname']);
        $fullName .= ' ' . stripslashes($resAppName[0]['sname']);
        $remuneration = $resAppName[0]['remuneration'];
        
         
        $postParams = array("accesstype" => "printApplication", "application_id" => $application_id, "fullName" => $fullName, "remuneration" => $remuneration, "CRN" => $CRN);
       return $postParams;
    }
    else
    {
        return false;
    }
}

    public function getHomeBasedQuestion($username)
    {
            $userid= $this->getUserId($username);
            
            $sqlHomebasedquestion = 'SELECT if(homebasedquestion IS NULL,"N",homebasedquestion) as workathome FROM lo_users WHERE user_id ='.$userid;
            $resHomeBasedQuestion = $this->getDBRecords($sqlHomebasedquestion);
            return $resHomeBasedQuestion[0]['workathome'];
    }
}

?>
