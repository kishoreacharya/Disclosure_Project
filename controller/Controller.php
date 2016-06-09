<?php 
class Controller{
	
    private $actionMode;
    private $smarty;   
    private $validator;
    public $common;
	
    function __construct() {
        global $smarty,$validator,$common;
        $this->smarty = $smarty;        
        $this->validator = $validator;
        $this->common = $common; 
        $this->smartyDecalration();   
    }

    public function invoke(){
        (isset($_REQUEST['accesstype']) && !empty ($_REQUEST['accesstype'])) ? $this->actionMode = $_REQUEST['accesstype'] : $this->actionMode = 'login';
           
		$this->smarty->assign('htmlPage',$this->actionMode.'.html');

		// Check if the file exists - includes the php file
		if(file_exists( MODEL_PATH.$this->actionMode.'.php' ))
        include MODEL_PATH.$this->actionMode.'.php';     
		else
		include MODEL_PATH.'page404.php';
		
    }
   
    private function smartyDecalration(){
        //define user based directories for templates and compiling
        $this->smarty->template_dir = VIEW_PATH;
        $this->smarty->compile_dir  = APP_PATH.'tpl_c/';
        $this->smarty->plugins_dir  = SMARTY_PATH.'plugins/';
        /* Set smarty parameters. */
        $this->smarty->compile_check    = TRUE;
        $this->smarty->force_compile    = FALSE;
        $this->smarty->debugging        = FALSE;
    }   
}

?>