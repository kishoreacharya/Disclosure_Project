<?php

class Messages extends CommonLib {

    public $USERACCESS = "cqcuser";
    public $ACCESSTYPE = "CU";
    public $smarty = "";

    function __construct() {
        parent::__construct();
        return true;
    }

    #Function to get Appended Signature of the User

    public function initateAppendSignature($userid) {
        $query = "select append_signature,signature from add_user_list where user_id='$userid'";
        $signature = $this->getDBRecords($query);
        if ($signature[0]['append_signature'] == "Y") {
            $contents = "<br><br><hr><br>" . nl2br(htmlspecialchars($this->correctstring($signature[0]['signature']))) . "<br>";
        }
        return $contents;
    }

    #Function To get Username Info

    public function getMessageUserName($userid) {
        $query = "select name,middle_name,surname from add_user_list where user_id='$userid'";
        $get_name = $this->getDBRecords($query);
        $userNameInfo = $this->correctstring($get_name[0]['name'] . " " . $get_name[0]['middle_name'] . " " . $get_name[0]['surname']);
        return $userNameInfo;
    }

    #Functin to Get Message Pool Information

    public function getMessageInfo($view, $userid, $sorttype, $sortby, $ilimit=null, $number_of_rows_allowed=null, $period=null, $search=null, $searchby=null, $setSearchFlag=null) {
        switch ($view) {
            case "pool": {
                    #Get User Division Information
                    $user_division = $this->getUserDivision($userid);
                    $division_id = $user_division[0]['dept_id'];
                    #get the maximum number od record
                    $max_limit = $this->getMaxPoolMsgRecord($division_id);
                    $max_limit = $max_limit[0]['max_limit']; //to get total count of records
                    #set the sort type
                    $this->sortInfo($sorttype, $sortby);
                    #Fetch inbox messages
                    $this->CasePoolMsgs($division_id, $sortby, $sorttype, $ilimit, $number_of_rows_allowed, $max_limit);
                    #Fetch Pool File
                    $responseMessage = $this->smarty->fetch("message_pool.html");
                    break;
                }

            case "inbox": {
                    #get the maximum number od record
                    $max_limit = $this->getMaxInboxMsgRecord($userid);
                    $max_limit = $max_limit[0]['max_limit']; //to get total count of records
                    #set the sort type
                    $this->sortInfo($sorttype, $sortby);
                    #Fetch inbox messages
                    $this->CaseInobxMsgs($userid, $sortby, $sorttype, $ilimit, $number_of_rows_allowed, $max_limit);
                    #Fetch Inbox File
                    $responseMessage = $this->smarty->fetch("inbox.html");
                    break;
                }
            case "outbox": {
                    #get the maximum number od record
                    $max_limit = $this->getMaxOutboxMsgRecord($userid);
                    $max_limit = $max_limit[0]['max_limit']; //to get total count of records
                    #set the sort type
                    $this->sortInfo($sorttype, $sortby);
                    #Fetch inbox messages
                    $this->CaseOutboxMsgs($userid, $sortby, $sorttype, $ilimit, $number_of_rows_allowed, $max_limit);
                    #Fetch Inbox File
                    $responseMessage = $this->smarty->fetch("outbox.html");

                    break;
                }

            case "profile": {
                    #Fetch Case Profile
                    $profile = $this->CaseProfile($userid);
                    #Fetch Case Profile Email
                    $email_option = $this->CaseProfileEmail($userid);
                    #Assign Variables
                    $this->smarty->assign('profile', $profile);
                    $this->smarty->assign('email_option', $email_option);
                    #Fetch User Profile File
                    $responseMessage = $this->smarty->fetch("user_profile.html");
                    break;
                }

            case "received_archives": {
                    $period = $_POST['period'];
                    #set the sort type
                    $this->sortInfo($sorttype, $sortby);

                    #create tempory table
                    $this->createTempTable($userid);

                    #to get all those msg which r there in archives
                    $this->CaseReceivedArchiveMsgs($userid, $sortby, $sorttype, $ilimit, $number_of_rows_allowed, $period);
                    #Fetch Received Archives File
                    $responseMessage = $this->smarty->fetch("received_archives.html");

                    break;
                }

            case "sent_archives": {
                    $period = $_POST['period'];
                    #get the maximum number od record
                    $max_limit = $this->getMaxSentArchiveMsgRecord($userid);
                    $max_limit = $max_limit[0]['max_limit']; //to get total count of records
                    #set the sort type
                    $this->sortInfo($sorttype, $sortby);
                    #Fetch inbox messages
                    $this->CaseSentArchiveMsgs($userid, $sortby, $sorttype, $ilimit, $number_of_rows_allowed, $period, $max_limit);
                    #Fetch Sent Archives File
                    $responseMessage = $this->smarty->fetch("sent_archives.html");

                    break;
                }
            case "searchmsg": {
                    $period = $_POST['period'];
                    $totalCount = 0;

                    #Get Search Period Query
                    $period_query = $this->getPeriodQuery($period);

                    #Cal the Main tpl
                    $this->smarty->assign('search', $search);
                    $this->smarty->assign('searchby', $searchby);
                    $this->smarty->assign('period', $period);
                    if ($setSearchFlag != 'Y')
                        $responseMessage = $this->smarty->fetch("search_message.html");
                    //---- 1st tpl Starts here------
                    $cnt = 0;
                    if (!empty($search)) {
                        $cnt = $this->getMessageFirstSearch($cnt, $userid, $search, $searchby, $period_query);
                    }
                    $totalCount+=$cnt;
                    if ($cnt > 0) {
                        #Call the second TPL
                        //$this->smarty->assign('results', $results);
                        $this->smarty->assign('messages_count', $cnt);
                        $responseMessage.= $this->smarty->fetch("showInbox.html");
                    }
                    //---- 1st tpl Ends here------
                    //---- 2nd tpl Starts here------
                    $cnt = 0;
                    if (!empty($search)) {
                        $cnt = $this->getMessageSecondSearch($cnt, $userid, $search, $searchby, $period_query);
                    }
                    $totalCount+=$cnt;
                    if ($cnt > 0) {
                        #Call the second TPL
                        //$this->smarty->assign('results', $results);
                        $this->smarty->assign('messages_count', $cnt);
                        $responseMessage.= $this->smarty->fetch("showOutbox.html");
                    }
                    //------2nd TPL ends here--------
                    //---- 3nd tpl Starts here------
                    $cnt = 0;
                    if (!empty($search)) {
                        $cnt = $this->getMessageThirdSearch($cnt, $userid, $search, $searchby, $period_query);
                    }

                    $totalCount+=$cnt;
                    if ($cnt > 0) {
                        #Call the third TPL
                        //$this->smarty->assign('results', $results);
                        $this->smarty->assign('messages_count', $cnt);
                        $responseMessage.= $this->smarty->fetch("ShowRarchived.html");
                    }
                    //------3nd TPL ends here--------
                    //---- 4nd tpl Starts here-------
                    $cnt = 0;
                    if (!empty($search)) {
                        $cnt = $this->getMessageFourthSearch($cnt, $userid, $search, $searchby, $period_query);
                    }
                    $totalCount+=$cnt;
                    if ($cnt > 0) {
                        #Call the third TPL
                        //$this->smarty->assign('results', $results);
                        $this->smarty->assign('messages_count', $cnt);
                        $responseMessage.= $this->smarty->fetch("ShowSarchived.html");
                    }

                    //------4nd TPL ends here--------
                    #If no result found
                    if ($totalCount == 0 && !empty($search)) {     #Sorry one HTML inside PHP allowed
                        $responseMessage.="<div class='appfield'><br /><div class='form_item_error'><p class='error'>No Match Found</p></div></div>";
                    }
                    break;
                }
        }
        return $responseMessage;
    }

    #Function To Fetch User Division Information

    public function getUserDivision($userid) {
        $query = "SELECT ud . *,dd.dept_id FROM user_division ud LEFT JOIN dept_division dd ON ud.division = dd.division WHERE ud.user_id ='$userid'";
        $user_division = $this->getDBRecords($query);
        return $user_division;
    }

    #Function To Fetch Maximum Number Of Message Records For Case Pool

    public function getMaxPoolMsgRecord($division_id) {
        $query = "select count(*) as max_limit from messaging m,msg_sent_to s where m.msg_id=s.msg_id and s.user_id ='-$division_id' and s.checked = 'N' and s.deleted = 'N'";
        $query.=" and if(m.msg_type='Notification',if(now() >= m.valid_from_dt and now() <= m.valid_to_dt,1,0),1) ";
        $max_limit = $this->getDBRecords($query);
        return $max_limit;
    }

    #Function To Fetch Inbox Messages For Case Pool

    public function CasePoolMsgs($division_id, $sortby, $sorttype, $ilimit, $number_of_rows_allowed, $max_limit) {
        $query = "select m.*,s.msg_read from messaging m,msg_sent_to s where m.msg_id=s.msg_id and s.user_id  ='-$division_id' and s.checked = 'N' and s.deleted ='N'";
        $query.=" and if(m.msg_type='Notification',if(now() >= m.valid_from_dt and now() <= m.valid_to_dt,1,0),1) ";

        if ($sortby == "subject")
            $query.=" ORDER BY m.subject $sorttype";
        elseif ($sortby == "sent_date")
            $query.=" ORDER BY m.msg_sent_dt $sorttype";
        elseif ($sortby == "name")
            $query.=" ORDER BY m.sent_as $sorttype";
        else
            $query.="  order by m.msg_id desc";


        $query.=" limit $ilimit,$number_of_rows_allowed";

        $value = $this->getDBRecords($query);
        $cnt = count($value); //to get all messages

        $results = array();
        for ($i = 0; $i < $cnt; $i++) {
            $results[$i]["msg_id"] = $value[$i]['msg_id'];
            $results[$i]["msg_type"] = $value[$i]['msg_type'];
            $results[$i]["subject"] = stripslashes($value[$i]['subject']);
            $results[$i]["message"] = stripslashes($value[$i]['message']);
            $results[$i]["priority"] = $value[$i]['priority'];
            $results[$i]["sent_date"] = $this->getMessageDate($value[$i]['msg_sent_dt']);
            $results[$i]["sent_by_user_id"] = $value[$i]['sent_by'];
            $results[$i]["sent_by_name"] = $this->correctstring($value[$i]['sent_as']);
            $results[$i]["parent_msgid"] = $value[$i]['parent_msgid'];

            if ($value[$i]['msg_read'] == 'Y')
                $status = "read";
            else
                $status="unread";
            $results[$i]["status"] = $status;
        }

        $this->smarty->assign('results', $results);
        $this->smarty->assign('messages_count', $cnt);
        $this->smarty->assign('ilimit', $ilimit);
        $this->smarty->assign('number_of_rows_allowed', $number_of_rows_allowed);
        $this->smarty->assign('max_limit', $max_limit);
    }

    #Function To fecth Sort Type Information

    public function sortInfo($sorttype, $sortby) {
        #set the sort type
        if ($sorttype == "asc")
            $sorttype = "desc";
        else
            $sorttype="asc";

        $sortClass1 = '';
        $sortClass2 = '';
        $sortClass3 = '';

        if (($sortby == "name") and ($sorttype == 'asc' )) {
            $sortClass1 = 'sortedAsc';
        }
        if (($sortby == "name") and ($sorttype == 'desc' )) {
            $sortClass1 = 'sortedDesc';
        }

        if (($sortby == "subject") and ($sorttype == 'asc' )) {
            $sortClass2 = 'sortedAsc';
        }
        if (($sortby == "subject") and ($sorttype == 'desc' )) {
            $sortClass2 = 'sortedDesc';
        }

        if (($sortby == "sent_date") and ($sorttype == 'asc')) {
            $sortClass3 = 'sortedAsc';
        }
        if (($sortby == "sent_date") and ($sorttype == 'desc')) {
            $sortClass3 = 'sortedDesc';
        }

        $this->smarty->assign('sorttype', $sorttype);
        $this->smarty->assign('sortby', $sortby);
        $this->smarty->assign('sortClass1', $sortClass1);
        $this->smarty->assign('sortClass2', $sortClass2);
        $this->smarty->assign('sortClass3', $sortClass3);
    }

    #Function to Fetch Message Date

    public function getMessageDate($date) {
        $sent_year = substr($date, 0, 4);
        $sent_month = substr($date, 5, 2);
        $sent_day = substr($date, 8, 2);
        $date = $sent_day . "/" . $sent_month . "/" . $sent_year;
        return $date;
    }

    #Fetch Maximum Number of Message Records For Case Inbox

    public function getMaxInboxMsgRecord($userid) {
        $query = "select count(*) as max_limit from messaging m,msg_sent_to s where m.msg_id=s.msg_id and s.user_id='$userid' and s.checked = 'N' and s.deleted = 'N'";
        $query.=" and if(m.msg_type='Notification',if(now() >= m.valid_from_dt and now() <= m.valid_to_dt,1,0),1) ";

        $max_limit = $this->getDBRecords($query);
        return $max_limit;
    }

    #Function To Fetch Inbox Messages For Case Inbox

    public function CaseInobxMsgs($userid, $sortby, $sorttype, $ilimit, $number_of_rows_allowed, $max_limit) {
        $query = "select m.*,s.msg_read from messaging m,msg_sent_to s where m.msg_id=s.msg_id and s.user_id='$userid' and s.checked = 'N' and s.deleted ='N'";
        $query.=" and if(m.msg_type='Notification',if(now() >= m.valid_from_dt and now() <= m.valid_to_dt,1,0),1) ";

        if ($sortby == "subject")
            $query.=" ORDER BY m.subject $sorttype";
        elseif ($sortby == "sent_date")
            $query.=" ORDER BY m.msg_sent_dt $sorttype";
        elseif ($sortby == "name")
            $query.=" ORDER BY m.sent_as $sorttype";
        else
            $query.="  order by m.msg_id desc";

        $query.=" limit $ilimit,$number_of_rows_allowed";

        $value = $this->getDBRecords($query);
        $cnt = count($value); //to get all messages
        $results = array();
        for ($i = 0; $i < $cnt; $i++) {
            $results[$i]["msg_id"] = $value[$i]['msg_id'];
            $results[$i]["msg_type"] = $value[$i]['msg_type'];
            $results[$i]["subject"] = stripslashes($value[$i]['subject']);
            $results[$i]["message"] = stripslashes($value[$i]['message']);
            $results[$i]["priority"] = $value[$i]['priority'];
            $results[$i]["sent_date"] = $this->getMessageDate($value[$i]['msg_sent_dt']);
            $results[$i]["sent_by_user_id"] = $value[$i]['sent_by'];
            $results[$i]["sent_by_name"] = $this->correctstring($value[$i]['sent_as']);
            $results[$i]["parent_msgid"] = $value[$i]['parent_msgid'];
            if ($value[$i]['msg_read'] == 'Y')
                $status = "read";
            else
                $status="unread";
            $results[$i]["status"] = $status;
        }

        $this->smarty->assign('results', $results);
        $this->smarty->assign('max_count', $max_limit);
        $this->smarty->assign('messages_count', $cnt);
        $this->smarty->assign('ilimit', $ilimit);
        $this->smarty->assign('number_of_rows_allowed', $number_of_rows_allowed);
        $this->smarty->assign('max_limit', $max_limit);
    }

    #Function To Fetch Maximum Number Of Records For Case "Outbox"

    public function getMaxOutboxMsgRecord($userid) {
        $query = "select count(*) as max_limit from messaging m,msg_sent_to s where m.msg_id=s.msg_id and m.sent_by='$userid' and s.sent_to_archive='N' AND m.msg_type = 'Message' and s.deleted = 'N'";
        $max_limit = $this->getDBRecords($query);
        return $max_limit;
    }

    #Function To Fetch Out Box Messages for Case "Outbox"

    public function CaseOutboxMsgs($userid, $sortby, $sorttype, $ilimit, $number_of_rows_allowed, $max_limit) {
        $query = "select m.*,s.user_id,s.received_as,s.msg_read  from messaging m,msg_sent_to s where m.msg_id=s.msg_id and m.sent_by='$userid' and s.sent_to_archive='N' and s.deleted = 'N' AND m.msg_type = 'Message'";

        if ($sortby == "subject")
            $query.=" ORDER BY m.subject $sorttype";
        elseif ($sortby == "sent_date")
            $query.=" ORDER BY m.msg_sent_dt $sorttype";
        elseif ($sortby == "name")
            $query.=" ORDER BY s.received_as $sorttype";
        else
            $query.="  order by m.msg_id desc";


        $query.=" limit $ilimit,$number_of_rows_allowed";

        $value = $this->getDBRecords($query);
        $cnt = count($value); //to get all messages

        $results = array();
        for ($i = 0; $i < $cnt; $i++) {
            $results[$i]["msg_id"] = $value[$i]['msg_id'];
            $results[$i]["msg_type"] = $value[$i]['msg_type'];
            $results[$i]["subject"] = stripslashes($value[$i]['subject']);
            $results[$i]["message"] = stripslashes($value[$i]['message']);
            $results[$i]["priority"] = $value[$i]['priority'];
            $results[$i]["sent_date"] = $this->getMessageDate($value[$i]['msg_sent_dt']);
            $results[$i]["sent_by_user_id"] = $value[$i]['sent_by'];
            $results[$i]["sent_by_name"] = $this->correctstring($value[$i]['sent_as']);
            $results[$i]["parent_msgid"] = $value[$i]['parent_msgid'];
            $results[$i]["recipient_user_id"] = $value[$i]['user_id'];
            $results[$i]["recipient_name"] = $value[$i]['received_as'];

            if ($value[$i]['msg_read'] == 'Y')
                $status = "read";
            else
                $status="unread";
            $results[$i]["status"] = $status;
        }

        $this->smarty->assign('results', $results);
        $this->smarty->assign('max_count', $max_limit);
        $this->smarty->assign('messages_count', $cnt);
        $this->smarty->assign('ilimit', $ilimit);
        $this->smarty->assign('number_of_rows_allowed', $number_of_rows_allowed);
        $this->smarty->assign('max_limit', $max_limit);
    }

    #Fetch Case Profile Option

    public function CaseProfile($userid) {
        $query = "select * from add_user_list where user_id='$userid'";
        $profile = $this->getDBRecords($query);
        return $profile;
    }

    #Fucntion to Fetch Case Profile Email Option

    public function CaseProfileEmail($userid) {
        $query = "select email,message_email from users where user_id='$userid'";
        $email_option = $this->getDBRecords($query);
        return $email_option;
    }

    #Function To Create tempory table

    public function createTempTable($userid) {
        $query = "create temporary table qwer123 as SELECT a.user_id, b.parent_msgid pmid, b.sent_by,a.received_as,b.sent_as, b.msg_sent_dt, count( b.parent_msgid ) pmcnt FROM msg_sent_to a, messaging b WHERE a.msg_id = b.msg_id AND a.user_id ='$userid' AND a.checked = 'Y' GROUP BY b.parent_msgid";
        $this->Query($query);
    }

    #Function To Fetch Received Archive Messages for Case "received_archives"

    public function CaseReceivedArchiveMsgs($userid, $sortby, $sorttype, $ilimit, $number_of_rows_allowed, $period) {
        #to get all those msg which r there in archives
        $query = "select m.*,s.received_as,s.msg_read,qwer123.pmcnt thread_cnt from msg_sent_to s,messaging m left outer join qwer123 on qwer123.pmid=m.parent_msgid where m.msg_id=s.msg_id and s.user_id='$userid' and s.checked = 'Y'";

        #get all the records based on the time period
        if ($period == "4to3m") {
            $query.=" and s.deleted ='N' and unix_timestamp( date_format( m.msg_sent_dt, '%Y%m%d' ) ) > unix_timestamp( DATE_FORMAT( DATE_SUB( CURRENT_DATE, INTERVAL 3 MONTH ) , '%Y%m%d' ) ) ";
        } elseif ($period == "1to4") {
            $query.=" and s.deleted ='N' and unix_timestamp( date_format( m.msg_sent_dt, '%Y%m%d' ) ) > unix_timestamp( DATE_FORMAT( DATE_SUB( CURRENT_DATE, INTERVAL 21 DAY ) , '%Y%m%d' ) ) ";
        } elseif ($period == "old") {
            $query.=" and s.deleted ='N' and unix_timestamp( date_format( m.msg_sent_dt, '%Y%m%d' ) ) < unix_timestamp( DATE_FORMAT( DATE_SUB( CURRENT_DATE, INTERVAL 3 MONTH ) , '%Y%m%d' ) )  ";
        } else {
            $query.=" and s.deleted ='N' AND unix_timestamp( date_format( m.msg_sent_dt, '%Y%m%d' ) ) > unix_timestamp( DATE_FORMAT( DATE_SUB( CURRENT_DATE, INTERVAL 7 DAY ) , '%Y%m%d' ) )   ";
        }

        if ($sortby == "subject")
            $query.=" ORDER BY m.subject $sorttype";
        elseif ($sortby == "sent_date")
            $query.=" ORDER BY m.msg_sent_dt $sorttype";
        elseif ($sortby == "name")
            $query.=" ORDER BY m.sent_as $sorttype";
        else
            $query.="  order by m.msg_id desc";

        #to get the total number of records within the period
        $value1 = $this->getDBRecords($query);
        $max_limit = count($value1);

        $query.=" limit $ilimit,$number_of_rows_allowed";
        $value = $this->getDBRecords($query);
        $cnt = count($value);


        $results = array();
        for ($i = 0; $i < $cnt; $i++) {
            $results[$i]["msg_id"] = $value[$i]['msg_id'];
            $results[$i]["msg_type"] = $value[$i]['msg_type'];
            $results[$i]["subject"] = stripslashes($value[$i]['subject']);
            $results[$i]["message"] = stripslashes($value[$i]['message']);
            $results[$i]["priority"] = $value[$i]['priority'];
            $results[$i]["sent_date"] = $this->getMessageDate($value[$i]['msg_sent_dt']);
            $results[$i]["sent_by_user_id"] = $value[$i]['sent_by'];
            $results[$i]["sent_by_name"] = $this->correctstring($value[$i]['sent_as']);
            $results[$i]["parent_msgid"] = $value[$i]['parent_msgid'];

            if ($value[$i]['msg_read'] == 'Y')
                $status = "read";
            else
                $status="unread";
            $results[$i]["status"] = $status;

            if ($value[$i]['thread_cnt'] > 0)
                $results[$i]["thread_cnt"] = "(" . $value[$i]['thread_cnt'] . ")";
            else
                $results[$i]["thread_cnt"] = "";
        }


        $this->smarty->assign('results', $results);
        $this->smarty->assign('max_count', $max_limit);
        $this->smarty->assign('messages_count', $cnt);
        $this->smarty->assign('ilimit', $ilimit);
        $this->smarty->assign('number_of_rows_allowed', $number_of_rows_allowed);
        $this->smarty->assign('max_limit', $max_limit);
        $this->smarty->assign('period', $period);
    }

    #Function To fetch Maximum Number of Sent Archive Records

    public function getMaxSentArchiveMsgRecord($userid) {
        $query = "select count(*) as max_limit from messaging m,msg_sent_to s where m.msg_id=s.msg_id and m.sent_by='$userid' and s.sent_to_archive='Y' AND m.msg_type = 'Message' and s.deleted = 'N'";
        $max_limit = $this->getDBRecords($query);
        return $max_limit;
    }

    #Function To Fetch Received Archive Messages for Case "received_archives"

    public function CaseSentArchiveMsgs($userid, $sortby, $sorttype, $ilimit, $number_of_rows_allowed, $period, $max_limit) {
        #to get all those msg which r there in archives
        $query = "select m.*,s.user_id,s.received_as,s.msg_read  from messaging m,msg_sent_to s where m.msg_id=s.msg_id and m.sent_by='$userid' and s.sent_to_archive='Y' and s.deleted = 'N' AND m.msg_type = 'Message'";

        #get all the records based on the time period
        if ($period == "4to3m") {
            $query.=" and s.deleted ='N' and unix_timestamp( date_format( m.msg_sent_dt, '%Y%m%d' ) ) > unix_timestamp( DATE_FORMAT( DATE_SUB( CURRENT_DATE, INTERVAL 3 MONTH ) , '%Y%m%d' ) ) ";
        } elseif ($period == "1to4") {
            $query.=" and s.deleted ='N' and unix_timestamp( date_format( m.msg_sent_dt, '%Y%m%d' ) ) > unix_timestamp( DATE_FORMAT( DATE_SUB( CURRENT_DATE, INTERVAL 28 DAY ) , '%Y%m%d' ) ) ";
        } elseif ($period == "old") {
            $query.=" and s.deleted ='N' and unix_timestamp( date_format( m.msg_sent_dt, '%Y%m%d' ) ) < unix_timestamp( DATE_FORMAT( DATE_SUB( CURRENT_DATE, INTERVAL 3 MONTH ) , '%Y%m%d' ) )  ";
        } else {
            $query.=" and s.deleted ='N' AND unix_timestamp( date_format( m.msg_sent_dt, '%Y%m%d' ) ) > unix_timestamp( DATE_FORMAT( DATE_SUB( CURRENT_DATE, INTERVAL 7 DAY ) , '%Y%m%d' ) )   ";
        }
        if ($sortby == "subject")
            $query.=" ORDER BY m.subject $sorttype";
        elseif ($sortby == "sent_date")
            $query.=" ORDER BY m.msg_sent_dt $sorttype";
        elseif ($sortby == "name")
            $query.=" ORDER BY s.received_as $sorttype";
        else
            $query.="  order by m.msg_id desc";


        $query.=" limit $ilimit,$number_of_rows_allowed";

        $value = $this->getDBRecords($query);
        $cnt = count($value); //to get all messages

        $results = array();
        for ($i = 0; $i < $cnt; $i++) {
            $results[$i]["msg_id"] = $value[$i]['msg_id'];
            $results[$i]["msg_type"] = $value[$i]['msg_type'];
            $results[$i]["subject"] = stripslashes($value[$i]['subject']);
            $results[$i]["message"] = stripslashes($value[$i]['message']);
            $results[$i]["priority"] = $value[$i]['priority'];
            $results[$i]["sent_date"] = $this->getMessageDate($value[$i]['msg_sent_dt']);
            $results[$i]["sent_by_user_id"] = $value[$i]['sent_by'];
            $results[$i]["sent_by_name"] = $this->correctstring($value[$i]['sent_as']);
            $results[$i]["parent_msgid"] = $value[$i]['parent_msgid'];
            $results[$i]["recipient_user_id"] = $value[$i]['user_id'];
            $results[$i]["recipient_name"] = $value[$i]['received_as'];

            if ($value[$i]['msg_read'] == 'Y')
                $status = "read";
            else
                $status="unread";
            $results[$i]["status"] = $status;
        }

        $this->smarty->assign('results', $results);
        $this->smarty->assign('max_count', $max_limit);
        $this->smarty->assign('messages_count', $cnt);
        $this->smarty->assign('ilimit', $ilimit);
        $this->smarty->assign('number_of_rows_allowed', $number_of_rows_allowed);
        $this->smarty->assign('max_limit', $max_limit);
        $this->smarty->assign('period', $period);
    }

    #Function To Fetch Period Query

    public function getPeriodQuery($period) {
        switch ($period) {
            case "4to3m": {
                    $period_query = " and s.deleted ='N'  and unix_timestamp( date_format( m.msg_sent_dt, '%Y%m%d' ) ) > unix_timestamp( DATE_FORMAT( DATE_SUB( CURRENT_DATE, INTERVAL 3 MONTH ) , '%Y%m%d' ) ) ";
                    break;
                }
            case "1to4": {
                    $period_query = " and s.deleted ='N'  and unix_timestamp( date_format( m.msg_sent_dt, '%Y%m%d' ) ) > unix_timestamp( DATE_FORMAT( DATE_SUB( CURRENT_DATE, INTERVAL 28 DAY ) , '%Y%m%d' ) ) ";
                    break;
                }
            case "old": {
                    $period_query = " and s.deleted ='N'  and unix_timestamp( date_format( m.msg_sent_dt, '%Y%m%d' ) ) < unix_timestamp( DATE_FORMAT( DATE_SUB( CURRENT_DATE, INTERVAL 3 MONTH ) , '%Y%m%d' ) )  ";
                    break;
                }
            case "1week" : {
                    $period_query = " and s.deleted ='N'  AND unix_timestamp( date_format( m.msg_sent_dt, '%Y%m%d' ) ) > unix_timestamp( DATE_FORMAT( DATE_SUB( CURRENT_DATE, INTERVAL 7 DAY ) , '%Y%m%d' ) )   ";
                }
        }
        return $period_query;
    }

    #Function to Search First Part of Message

    public function getMessageFirstSearch($cnt, $userid, $search, $searchby, $period_query) {

        if (!empty($search)) {
            if (!empty($searchby) && $searchby != "organisation") {
                //for name
                $query = "SELECT m.*,s.received_as,s.msg_read, a.name, a.middle_name, a.surname,s.user_id
					FROM messaging m, msg_sent_to s, add_user_list a
					WHERE m.msg_id = s.msg_id
					AND s.user_id = a.user_id";
                $query.=" " . "AND s.user_id = '$userid'";
                $query.=" " . "AND s.checked = 'N'";
                if ($searchby == "subject") {
                    $query.=" " . "AND m.subject LIKE '%$search%'";
                } elseif ($searchby == "message") {
                    $query.=" " . "AND m.message LIKE '%$search%'";
                } elseif ($searchby == "sender") {
                    $query.=" " . "AND (m.sent_as LIKE '%$search%')";
                } elseif ($searchby == "receiver") {
                    $query.=" " . "AND (s.received_as LIKE '%$search%')";
                }

                $query.=" " . $period_query;
                $query.=" " . "ORDER BY m.msg_id DESC";
            } elseif (!empty($searchby) && $searchby == "organisation") {
                $query = "SELECT m. * ,s.received_as,s.msg_read, o.name,s.user_id
					FROM messaging m, msg_sent_to s, organisation o, liason_officer l1, lo_org l2
					WHERE m.msg_id = s.msg_id
					AND l1.lo_id = l2.lo_id
					AND l2.org_id = o.org_id
					AND l1.user_id = m.sent_by";
                $query.=" " . "AND s.user_id = '$userid'";
                $query.=" " . "AND s.checked = 'N'";
                $query.=" " . "AND o.name LIKE '%$search%' " . $period_query . " " . "
					ORDER BY m.msg_id DESC";
            }
            $value = $this->getDBRecords($query);
            $cnt = count($value);
            $results = array();
            for ($i = 0; $i < $cnt; $i++) {
                $results[$i]["msg_id"] = $value[$i]['msg_id'];
                $results[$i]["msg_type"] = $value[$i]['msg_type'];
                $results[$i]["priority"] = $value[$i]['priority'];
                $results[$i]["subject"] = $value[$i]['subject'];
                if ($value[$i]['msg_read'] == 'Y')
                    $status = "read"; else
                    $status="unread";
                $results[$i]["status"] = $status;
                $results[$i]["sent_date"] = $this->getMessageDate($value[$i]['msg_sent_dt']);
                $results[$i]["sent_by_name"] = $this->correctstring($value[$i]['sent_as']);

                $from = $value[$i]['sent_by'];
                include MODEL_PATH . "getCompanydet.php";
                $this->smarty->assign('orgname', $orgname);
            }
            $this->smarty->assign('results', $results);
            return $cnt;
        }
    }

    #Fetch Company Level
#----------------------------------------------------------------

    public function getLevel($compId, $level=0) {
        $query = "select parent_id from company where company_id='$compId'";
        $res = $this->getDBRecords($query);

        if ($res[0]['parent_id'] != 0) {
            $level++;
            $level = $this->getLevel($res[0]['parent_id'], $level);
        }

        return $level;
    }

    #Function to Search Second Part of Message

    public function getMessageSecondSearch($cnt, $userid, $search, $searchby, $period_query) {

        if (!empty($search)) {
            if (!empty($searchby) && $searchby != "organisation") {
                //for name
                $query = "SELECT m. * ,s.received_as, s.user_id, a.name, a.middle_name, a.surname, s.msg_read
					FROM messaging m, msg_sent_to s, add_user_list a
					WHERE m.msg_id = s.msg_id
					AND m.sent_by = a.user_id";

                $query.=" " . "AND m.sent_by = '$userid'";

                $query.=" " . "AND s.sent_to_archive = 'N'
					AND m.msg_type = 'Message'";
                if ($searchby == "subject") {
                    $query.=" " . "AND m.subject LIKE '%$search%'";
                } elseif ($searchby == "message") {
                    $query.=" " . "AND m.message LIKE '%$search%'";
                } elseif ($searchby == "sender") {
                    $query.=" " . "AND (m.sent_as LIKE '%$search%')";
                } elseif ($searchby == "receiver") {
                    $query.=" " . "AND (s.received_as LIKE '%$search%')";
                }
                $query.=" " . $period_query;
                $query.=" " . "ORDER BY m.msg_id DESC";
            } elseif (!empty($searchby) && $searchby == "organisation") {
                $query = "SELECT m. *,s.received_as, s.user_id, o.name, s.msg_read
					FROM messaging m, msg_sent_to s, organisation o, liason_officer l1, lo_org l2
					WHERE m.msg_id = s.msg_id
					AND l1.lo_id = l2.lo_id
					AND l2.org_id = o.org_id
					AND l1.user_id = s.user_id";
                $query.=" " . "AND m.sent_by = '$userid'";

                $query.=" " . "AND s.sent_to_archive = 'N'
					AND m.msg_type = 'Message'
					AND o.name LIKE '%$search%' " . $period_query . " " . "
					ORDER BY m.msg_id DESC";
            }
            $value = $this->getDBRecords($query);
            $cnt = count($value);
            $results = array();
            for ($i = 0; $i < $cnt; $i++) {
                $results[$i]["msg_id"] = $value[$i]['msg_id'];
                $results[$i]["msg_type"] = $value[$i]['msg_type'];
                $results[$i]["priority"] = $value[$i]['priority'];
                $results[$i]["subject"] = $value[$i]['subject'];
                $results[$i]["received_as"] = $value[$i]['received_as'];
                $results[$i]["user_id"] = $value[$i]['user_id'];
                if ($value[$i]['msg_read'] == 'Y')
                    $status = "read"; else
                    $status="unread";
                $results[$i]["status"] = $status;
                $results[$i]["sent_date"] = $this->getMessageDate($value[$i]['msg_sent_dt']);
                $results[$i]["sent_by_name"] = $this->correctstring($value[$i]['sent_as']);
                $from = $value[$i]['user_id'];
                include MODEL_PATH . "getCompanydet.php";
                $this->smarty->assign('orgname', $orgname);
            }
        }
        $this->smarty->assign('results', $results);
        return $cnt;
    }

    #Function to Search Third Part of Message

    public function getMessageThirdSearch($cnt, $userid, $search, $searchby, $period_query) {

        if (!empty($search)) {
            if (!empty($searchby) && $searchby != "organisation") {
                //for name
                $query = "SELECT m. *,s.received_as, a.name, a.middle_name, s.msg_read, a.surname,s.user_id
			FROM messaging m, msg_sent_to s, add_user_list a
			WHERE m.msg_id = s.msg_id
			AND m.user_id = a.user_id";
                $query.=" " . "AND s.user_id = '$userid'";
                $query.=" " . "AND s.checked = 'Y'";
                if ($searchby == "subject") {
                    $query.=" " . "AND m.subject LIKE '%$search%'";
                } elseif ($searchby == "message") {
                    $query.=" " . "AND m.message LIKE '%$search%'";
                } elseif ($searchby == "sender") {
                    $query.=" " . "AND (m.sent_as LIKE '%$search%')";
                } elseif ($searchby == "receiver") {
                    $query.=" " . "AND (s.received_as LIKE '%$search%')";
                }
                $query.=" " . $period_query;
                $query.=" " . "ORDER BY m.msg_id DESC";
            } elseif (!empty($searchby) && $searchby == "organisation") {
                $query = "SELECT m. * ,s.received_as, o.name,s.user_id,s.msg_read
			FROM messaging m, msg_sent_to s, organisation o, liason_officer l1, lo_org l2
			WHERE m.msg_id = s.msg_id
			AND l1.lo_id = l2.lo_id
			AND l2.org_id = o.org_id
			AND l1.user_id = m.sent_by";
                $query.=" " . "AND s.user_id = '$userid'";
                $query.=" " . "AND s.checked = 'Y'";
                $query.=" " . "AND o.name LIKE '%$search%' " . $period_query . " " . "
			ORDER BY m.msg_id DESC";
            }
            $value = $this->getDBRecords($query);
            $cnt = count($value);
            $results = array();
            for ($i = 0; $i < $cnt; $i++) {
                $results[$i]["msg_id"] = $value[$i]['msg_id'];
                $results[$i]["msg_type"] = $value[$i]['msg_type'];
                $results[$i]["priority"] = $value[$i]['priority'];
                $results[$i]["subject"] = $value[$i]['subject'];
                $results[$i]["user_id"] = $value[$i]['user_id'];
                if ($value[$i]['msg_read'] == 'Y')
                    $status = "read"; else
                    $status="unread";
                $results[$i]["status"] = $status;
                $results[$i]["sent_date"] = $this->getMessageDate($value[$i]['msg_sent_dt']);
                $results[$i]["sent_by_name"] = $this->correctstring($value[$i]['sent_as']);

                $from = $value[$i]['sent_by'];
                include MODEL_PATH . "getCompanydet.php";
                $this->smarty->assign('orgname', $orgname);
            }
        }
        $this->smarty->assign('results', $results);
        return $cnt;
    }

    #Function to Search Third Part of Message

    public function getMessageFourthSearch($cnt, $userid, $search, $searchby, $period_query) {

        if (!empty($search)) {
            if (!empty($searchby) && $searchby != "organisation") {
                //for name
                $query = "SELECT m. * ,s.received_as, s.user_id, s.msg_read, a.name, a.middle_name, a.surname
			FROM messaging m, msg_sent_to s, add_user_list a
			WHERE m.msg_id = s.msg_id
			AND s.sent_by = a.user_id";
                $query.=" " . "AND m.sent_by = '$userid'";

                $query.=" " . "AND s.sent_to_archive = 'Y'
			AND m.msg_type = 'Message'";
                if ($searchby == "subject") {
                    $query.=" " . "AND m.subject LIKE '%$search%'";
                } elseif ($searchby == "message") {
                    $query.=" " . "AND m.message LIKE '%$search%'";
                } elseif ($searchby == "sender") {
                    $query.=" " . "AND (m.sent_as LIKE '%$search%')";
                } elseif ($searchby == "receiver") {
                    $query.=" " . "AND (s.received_as LIKE '%$search%')";
                }
                $query.=" " . $period_query;
                $query.=" " . "ORDER BY m.msg_id DESC";
            } elseif (!empty($searchby) && $searchby == "organisation") {
                $query = "SELECT m. * ,s.received_as, s.user_id, s.msg_read, o.name
			FROM messaging m, msg_sent_to s, organisation o, liason_officer l1, lo_org l2
			WHERE m.msg_id = s.msg_id
			AND l1.lo_id = l2.lo_id
			AND l2.org_id = o.org_id
			AND l1.user_id = s.user_id";
                $query.=" " . "AND m.sent_by = '$userid'";

                $query.=" " . "AND s.sent_to_archive = 'N'
			AND m.msg_type = 'Message'
			AND o.name LIKE '%$search%' " . $period_query . " " . "
			ORDER BY m.msg_id DESC";
            }
            $value = $this->getDBRecords($query);
            $cnt = count($value);
            $results = array();
            for ($i = 0; $i < $cnt; $i++) {
                $results[$i]["msg_id"] = $value[$i]['msg_id'];
                $results[$i]["msg_type"] = $value[$i]['msg_type'];
                $results[$i]["priority"] = $value[$i]['priority'];
                $results[$i]["subject"] = $value[$i]['subject'];
                $results[$i]["user_id"] = $value[$i]['user_id'];
                if ($value[$i]['msg_read'] == 'Y')
                    $status = "read"; else
                    $status="unread";
                $results[$i]["status"] = $status;
                $results[$i]["sent_date"] = $this->getMessageDate($value[$i]['msg_sent_dt']);
                $results[$i]["sent_by_name"] = $this->correctstring($value[$i]['sent_as']);
                $from = $value[$i]['user_id'];
                include MODEL_PATH . "getCompanydet.php";
                $this->smarty->assign('orgname', $orgname);
            }
        }
        $this->smarty->assign('results', $results);
        return $cnt;
    }

    #Function to Fetch Records From messaging Table

    public function getMessagingRecords($msgid) {
        $query = "select * from messaging where msg_id='$msgid'";
        $value = $this->getDBRecords($query);
        return $value;
    }

    #Function to Fetch Msg Sent To Table

    public function getMsgSentTo($msgid, $userid=null) {
        $whereCond = "";
        if (!empty($userid))
            $whereCond = " and user_id='$userid'";
        $query = "select sent_to_archive,received_as from msg_sent_to where msg_id='$msgid' $whereCond";
        $resultRecPerson = $this->getDBRecords($query);
        return $resultRecPerson;
    }

    #Function to Update Message Sent To Table

    public function updateMsgSentTo($arrMsgSentTo, $msgid, $userid) {
        $condition = " msg_id='$msgid' and user_id='$userid'";
        $this->Update('msg_sent_to', $arrMsgSentTo, $condition);
    }

    #Function to Fetch Previous messages

    public function getPreviousMessages($parent_id, $msgid, $accessLevel) {
        $query = "select m.*,s.received_as from messaging m,msg_sent_to s where m.parent_msgid='$parent_id' and m.msg_id=s.msg_id and m.msg_id < '$msgid' group by msg_id order by msg_id desc";
        $replyRes = $this->getDBRecords($query);
        $replytext = "";
        if (count($replyRes) > 0) {
            $replytext = "<br><hr>";
            for ($i = 0; $i < count($replyRes); $i++) {
                $org_name_reply = $this->getOrgNameFromUser($replyRes[$i]['sent_by']);
                $replytext.="<b>From :</b> " . $this->correctstring($replyRes[$i]['sent_as']) . "<br>";
                if (!empty($org_name_reply)) {
                    if ($accessLevel <> "officer") {
                        $replytext.="<b>Organisation name :</b> " . $this->correctstring($org_name_reply) . "<br>";
                    }
                }
                $replytext.="<b>To :</b> " . $this->correctstring($replyRes[$i]['received_as']) . "<br>";
                $replytext.="<b>Subject :</b> " . $this->correctstring($replyRes[$i]['subject']) . "<br><br>";
                $replytext.= ( $this->correctstring($replyRes[$i]['message'])) . "<br>";
                $replytext.="<hr>" . "<br>";
            }
        }
        return $replytext;
    }

    #Function To fetch Reply Messaging Records

    public function getReplyRecords($parent_id, $from_id, $userid, $msgid) {

        $query = "select m.*,s.received_as from messaging m,msg_sent_to s where m.parent_msgid='$parent_id' and ((m.sent_by ='$from_id' AND s.user_id ='$userid') OR (m.sent_by ='$userid' AND s.user_id ='$from_id')) and m.msg_id=s.msg_id and m.msg_id <= '$msgid' order by msg_id  desc";
        $replyRes = $this->getDBRecords($query);

        $replytext = "<font  size='1' face='Verdana, Arial, Helvetica, sans-serif'>";
        for ($i = 0; $i < count($replyRes); $i++) {
            $replytext.="<b>From :</b> " . $this->correctstring($replyRes[$i]['sent_as']) . "<br>";
            $replytext.="<b>To :</b> " . $this->correctstring($replyRes[$i]['received_as']) . "<br>";
            $replytext.="<b>Subject :</b> " . $this->correctstring($replyRes[$i]['subject']) . "<br><br>";
            $replytext.= ( $this->correctstring($replyRes[$i]['message'])) . "<br>";
            $replytext.="<hr>" . "<br>";
        }
        return $replytext;
    }

    #Function to Insert in Messaging

    public function insertIntoMessaging($arrInserMsg, $hid_to, $to) {

        $this->Insert('messaging', $arrInserMsg);

        $getMaxMsgId = "select max(msg_id) as msgid from messaging";
        $value = $this->getDBRecords($getMaxMsgId);
        $msg_id = $value[0]['msgid'];

        $arrMsgSentTo = array();
        $arrMsgSentTo['msg_id'] = $msg_id;
        $arrMsgSentTo['user_id'] = $hid_to;
        $arrMsgSentTo['received_as'] = $to;

        $this->Insert('msg_sent_to', $arrMsgSentTo);
    }

    #Function to Send New Msg Notification

    public function sendNewMessageNotification($to_user_id, $to_name, $subject, $fromEmail, $replyEmail) {
        #check if the user enabled this option
        $query = "select message_email,email from users where user_id ='$to_user_id' limit 1";
        $email_option = $this->getDBRecords($query);

        if ($email_option[0]['message_email'] == "Y") {
            $email = $email_option[0]['email'];
            $html = "<p><font size=\"2\" face=\"Verdana, Arial, Helvetica, sans-serif\">Dear " . $to_name . ",<br><br>
               You have been sent a message via the online ".DBS." system regarding <b>" . $subject . "</b>, please login to your secure online account to read the message.
			   <br><br>Thank You</font></p>";


            $text = "Dear " . $to_name . ",

			   You have been sent a message via the online ".DBS." system regarding " . $subject . ", please login to your secure online account to read the message.

			   Thank You";


            $from = $fromEmail;
            $mail = new htmlMimeMail();
            $mail->setHtml($html, $text);
            $mail->setReturnPath($replyEmail);
            $mail->setFrom($from);
            $mail->setSubject("Important Message");
            $mail->setHeader('X-Mailer', 'HTML Mime mail class');

            if (!empty($email)) {
                $result = $mail->send(array($email), 'smtp');
            }
        }
    }

    #Function to Update User Message Profile

    public function updateMsgProfile($tablename, $arrVal, $userid) {

        $condition = " user_id='$userid'";

        $this->Update($tablename, $arrVal, $condition);
    }

    #Function to fetch Already Assigned Messages

    public function alreadyAssigned($msgid) {
        $query = "select max(track_id) as track_id from assigned_to where msg_id='$msgid'";
        $res = $this->getDBRecords($query);
        $track_id = $res[0]['track_id'];
        $query = "select assigned_to from assigned_to where track_id='$track_id'";
        $res = $this->getDBRecords($query);
        if ($res[0]['assigned_to'] == 0) {
            return "N"; //not assigned
        } else {
            return "Y";
        }
    }

    #Function to Update Incomming Messages

    public function updateIncomingMsg($msgid, $userid, $curdate) {

        $condition = " msg_id='$msgid'";
        $arrVal = array();
        $arrVal['user_id'] = $userid;
        $this->Update('msg_sent_to', $arrVal, $condition);

        $query = "SELECT max( track_id ) as track_id FROM assigned_to WHERE msg_id ='$msgid'";
        $max_id = $this->getDBRecords($query);
        $max_id = $max_id[0]['track_id'];
        $query = "update assigned_to set assigned_to='$userid' , assigned_dt='$curdate' where track_id='$max_id'";
        $this->Query($query);
    }

    #Function to Compose Message

    public function composeUserMessage($msg_type, $contents, $subject, $userid, $div_name, $priority, $from, $upto, $curdate) {
        $query = "insert into messaging(message,subject,msg_type,sent_by,sent_as,priority,";
        if ($msg_type != "Message") {
            $query.=" " . "valid_from_dt,valid_to_dt,";
        }
        $query.=" " . "msg_sent_dt) values('".addslashes($contents)."','".addslashes($subject)."','$msg_type','$userid','$div_name','$priority',";
        if ($msg_type != "Message") {
            $query.=" " . "'$from','$upto',";
        }
        $query.=" " . "'$curdate')";

        $this->Query($query);

        $query = "select max(msg_id) as msgid from messaging";
        $value = $this->getDBRecords($query);
        $msg_id = $value[0]['msgid'];
        $fwd_msgid = $msg_id;

        $condition = " msg_id='$msg_id'";
        $arrVal = array();
        $arrVal['parent_msgid'] = $msg_id;
        $this->Update('messaging', $arrVal, $condition);

        return $msg_id;
    }

    #Function to Fetch Receipient Lists

    public function getReceipientLists($hidto, $received_as, $msg_id, $subject, $fromEmail, $replyEmail) {
        for ($i = 0; $i < count($hidto); $i++) {
            $to = addslashes($received_as[$i]);

            $query = "insert into msg_sent_to(msg_id,user_id,received_as) values ('$msg_id','$hidto[$i]','$to')";
            $this->Query($query);

            if ($hidto[$i] > 0) {
                # Send notification to composed messages (Based on YES/NO option)
                $this->sendNewMessageNotification($hidto[$i], $to, stripslashes($subject), $fromEmail, $replyEmail);
            }

            if ($hidto[$i] < 0) {//these messages r sent to Atlantic data company Departments
                $cid = 10;
                //insert into assigned_to table to keep track of msg
                $query = "insert into assigned_to (msg_id,sent_to,company_id) values('$msg_id','$hidto[$i]','$cid')";
                $this->Query($query);
            }
        } #end for
    }

    #Function to Check Whethere Receiver is Out Of Station

    public function checkReceiverIsIn($hidto, $received_as, $subject, $curdate, $msg_id, $userid, $div_name) {
        #checking if receiver is out of station or not
        for ($i = 0; $i < count($hidto); $i++) {
            $to = addslashes($received_as[$i]);

            $query = "select forward_to,available,reply_message from add_user_list where user_id='$hidto[$i]'";
            $available = $this->getDBRecords($query);

            if ($available[0]['available'] == "N") {//if not available ...send auto reply to the sender
                $automsg = addslashes($available[0]['reply_message']);
                $sub = addslashes("Re:" . $subject);
                $query = "insert into messaging(message,subject,msg_type,sent_by,sent_as,priority,";
                $query.=" " . "msg_sent_dt) values('$automsg','$sub','Message','$hidto[$i]','$to','normal',";
                $query.=" " . "'$curdate')";
                $this->Query($query);

                $query = "select max(msg_id) as msgid from messaging";
                $value = $this->getDBRecords($query);
                $msg_id = $value[0]['msgid'];

                $query = "update messaging set parent_msgid='$msg_id' where msg_id='$msg_id'";
                $this->Query($query);
                $query = "insert into msg_sent_to(msg_id,user_id,received_as) values ('$msg_id','$userid','$div_name')";
                $this->Query($query);
            }
        }
    }

    #Function to Get All Messages for Applicant Dashboard

    public function getAllMessages($app_user_id) {
        $query = "select m.*,s.msg_read from messaging m,msg_sent_to s where m.msg_id=s.msg_id and s.user_id='$app_user_id' and s.checked = 'N' and s.deleted ='N'";
        $query.=" and if(m.msg_type='Notification',if(now() >= m.valid_from_dt and now() <= m.valid_to_dt,1,0),1) ";
        $query.="  order by m.msg_id desc";
        $value = $this->getDBRecords($query);
        for ($i = 0; $i < count($value); $i++) {
            $results[$i]["msg_id"] = $value[$i]['msg_id'];
            $results[$i]["msg_type"] = $value[$i]['msg_type'];
            $results[$i]["subject"] = stripslashes($value[$i]['subject']);
            $results[$i]["message"] = stripslashes($value[$i]['message']);
            $results[$i]["priority"] = $value[$i]['priority'];
            $results[$i]["sent_date"] = $this->getMessageDate($value[$i]['msg_sent_dt']);
            $results[$i]["sent_by_user_id"] = $value[$i]['sent_by'];
            $results[$i]["sent_by_name"] = $this->correctstring($value[$i]['sent_as']);
            $results[$i]["parent_msgid"] = $value[$i]['parent_msgid'];
            if ($value[$i]['msg_read'] == 'Y')
                $status = "read";
            else
                $status="unread";
            $results[$i]["status"] = $status;
        }
        return $results;
    }

}

?>
