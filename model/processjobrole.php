<?php

include_once MODEL_PATH . 'Authenticate.php';
//  Languages For status
require LANGUAGE_PATH . 'lang.common.php';

require CLASS_PATH . 'class.jobroles.php';
$objJobroles = new Jobroles();


foreach ($_REQUEST as $kPost => $vVal) {
    $$kPost = $vVal;
}


if ($mode == "add") {
    if (!empty($jobrole)) {
        $jobrole = trim($jobrole);
        if (empty($jobroleno))
            $jobroleno = "NULL";

        $arrAddJobrole = array();
        $arrAddJobrole['job'] = $jobrole;
        $arrAddJobrole['org_provide_id'] = $service_name;
        $arrAddJobrole['disclosure'] = $crb_level;
        $arrAddJobrole['category_code'] = $cat_code;
        $arrAddJobrole['jobwork'] = $works_with;
        $arrAddJobrole['volunteer'] = $volunteer;
        $arrAddJobrole['job_no'] = $jobroleno;
        $arrAddJobrole['homebasedquestion'] = $workathome;

        #Check whether same job exist or not!
        $getJobRoleDescrition = $objJobroles->checkJobRoleAvailabilty($jobrole,$crb_level,$cat_code,$works_with,$jobroleno,$volunteer,$service_name);

        if(empty($getJobRoleDescrition)){
            $jobId = $objJobroles->addJobRole($arrAddJobrole);
        }else{
            $jobId = "";
        }
    }
    //header("location:assignBands.php?jobId=$jobId"); 

    $accesstype = "jobroles";
    $postParams = array("accesstype" => $accesstype, "jobId" => $jobId);
    require_once 'formSubmission.php';
    die();
}else {

    if (!empty($jobrole) && !empty($jobId)) {
        $jobrole = trim($jobrole);
        if (empty($jobroleno))
            $jobroleno = "NULL";
        $query = "update jobs set job='$jobrole',org_provide_id='$service_name',disclosure='$crb_level',category_code='$cat_code', jobwork='$works_with', volunteer='$volunteer',job_no='$jobroleno',homebasedquestion='$workathome' where sno='$jobId'";
        $this->common->Query($query);
    }
?>
    <script type="text/javascript">
        <!--
        self.parent.tb_remove();
        //window.top.location.reload(true);
        parent.parent.reloadAftercomplete('<?php echo $_POST['jobrole']; ?>');
        

        //-->
    </script>
<?php

}
?>

