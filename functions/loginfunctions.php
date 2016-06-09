<?php
function logUserLogin($orgId,$username) {
    global $_SERVER;
    $hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $timeNow = time();
    $query = "INSERT INTO user_logs (org_id, username, login_time, host,comments) VALUES ('$orgId', '$username', '$timeNow', '$hostname','Main Login')";
    $res = updateDBRecord($query);
}

function logLoginFailure($username,$password) {
    global $_SERVER;
    $orgId = "0";
    $hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $timeNow = time();
    $query = "INSERT INTO user_logs (org_id, username,password,login_time, host,login_failed,comments) VALUES ('$orgId', '$username','$password', '$timeNow', '$hostname','Y','Main Login')";
    $res = updateDBRecord($query);
}

function isNewMessage($userid) {
    $query="select count(msg_id) as msgcount from msg_sent_to where user_id='$userid' and msg_read='N' and checked='N'";
    $res=getDBRecords($query);
    if($res[0]['msgcount'] > 0)
        return true;
    else
        return false;
}

function redirectLogin($confId) {
    $location="";	

	#Check if the logged in person have access to this page
	$query="select o.access_type from officer_types o,users u where o.type_id=u.officer_type and u.user_id='$confId' limit 1";
	$restrict_access=getDBRecords($query);
	$pid=$restrict_access[0]['access_type'];

    #Get all file names which I have access to
    $query="select file_id,file_name from system_files where file_id in ($pid) and file_sub_id='0' and active_yn ='Y'";
    $restrict_accesspage=getDBRecords($query);

    if(count($restrict_accesspage) > 0) {
        $location=$restrict_accesspage[0]['file_name'];
    }
    return $location;
}

function getRestrictedFileNames($restrict_accesspage) {
    $accessArray=array();
    for($m=0;$m<count($restrict_accesspage);$m++) {
        $accessArray[]=$restrict_accesspage[$m]['file_name'];

        if(!empty($restrict_accesspage[$m]['file_linked'])) {
            $linkedFileNameArray = explode(",",$restrict_accesspage[$m]['file_linked']);

            for($acc=0;$acc<count($linkedFileNameArray);$acc++)
                $accessArray[]=trim($linkedFileNameArray[$acc]);
        }
    }

    $accessArray = array_unique($accessArray);
    return $accessArray;
}

function getRestrictedPageLinks($file_ids) {
    $restrict_links=explode(',',trim($file_ids));
    $arr_restrict_links = array_flip($restrict_links);
    foreach( $arr_restrict_links as $key=>$value ) {
        $arr_restrict_links[$key] = 1;
    }
    return $arr_restrict_links;
}

function getResetPwdAlert($user_id){
    $query = 'SELECT last_updated, initiate_activate FROM users WHERE user_id ='.$user_id;
    $res = getDBRecords($query);
    $current_time = time();
    $date_diff = 0;
    if(!empty($res[0]['last_updated'])){
      $date_diff = round((($current_time-$res[0]['last_updated'])/60/60/24));
    }
    if( ($res[0]['last_updated'] > 0 && $date_diff > 90) || ($res[0]['last_updated'] == 0 && $res[0]['initiate_activate'] =='Y') ){
        return $resetpwd = '0';
    }else{
        return $resetpwd = '1';
    }
}
?>