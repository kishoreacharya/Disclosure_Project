<?php

include_once MODEL_PATH . 'Authenticate.php';
//  Languages For status
require LANGUAGE_PATH . 'lang.common.php';

require CLASS_PATH . 'class.editRegEmail.php';
$objeditRegEmail = new editRegEmail();


foreach ($_REQUEST as $kPost => $vVal) {
    $$kPost = $vVal;
}

if (!empty($regEmail) && !empty($userId)) {
    $modified_by = $this->common->getUserId($username);
    $regEmail = trim($regEmail);
    $objeditRegEmail->updateEditRegEmail($regEmail, $userId,$modified_by,$old_email,$userType);
?>
    

<script type="text/javascript">
/*parent.parent.document.searchForm.firstname.value = 's';*/
//var viewall=window.parent.document.getElementById('firstname').value;
//alert(viewall);
        //
        //parent.parent.viewAllRegisteredApplicant();
        //window.top.location.reload(true);
        if(window.parent.document.getElementById('application_search'))
            {
                window.parent.appSearch();
            }
            else if(window.parent.document.getElementById('searchonholdapp'))
            {
                if(window.parent.document.getElementById('firstname').value== "" && window.parent.document.getElementById('lastname').value== "" && window.parent.document.getElementById('emailaddress').value == "" && window.parent.document.getElementById('telephoneNo').value == "" && window.parent.document.getElementById('organisation').value == "")

                            {

                                window.parent.viewAllOnHoldApplicant();

                            }
                            else 
                            {

                                window.parent.searchOnHoldApplicant();
                            }
            }
            else
            
                {
                      if(window.parent.document.getElementById('firstname').value== "" && window.parent.document.getElementById('lastname').value== "" && window.parent.document.getElementById('emailaddress').value == "" && window.parent.document.getElementById('telephoneNo').value == "" && window.parent.document.getElementById('organisation').value == "")

                            {

                                window.parent.viewAllRegisteredApplicant();

                            }
                            else 
                            {

                                window.parent.searchRegisteredApplicant();
                            }
                }
      
self.parent.tb_remove();
    </script>
<?php

}
?>

