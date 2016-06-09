<?
class Paging
{
 var $Total_Records_Per_Page=25;
 var $Total_Recs;
 var $parameter_keys=array();
 var $parameter_value=array();
 var $Total_Pages=0;
 var $Page_String="";
 var $CurrentItem;
 var $Script_Filename="";
 var $Has_Previous_Next=true;
 var $Has_First_Last=true;
 var $Start_Variable="start";
 
	function Create_paging()
	{
		global $vars;
		$this->Total_Pages=ceil($this->Total_Recs/$this->Total_Records_Per_Page);
				
		
		
		for($i=1;$i<=$this->Total_Pages;$i++)
		{		  			
		  if($this->CurrentItem==$i)
		  {
		  	$Page_String.="<font face=arial size=2>".$i."</font> | ";
		  }
		  else
		  {
		  	
$Page_String.="<a href=".$this->Script_Filename."?&".$this->Start_Variable."=".$this->get_Page_Start_Item($i).$ParamString."&".$vars.">".$i."</a> | ";
		  }

		}
		
		if($this->Has_Previous_Next==true)
		  {
		  	
		  	if($this->has_Previous())
		  	{
		  		$Previous_Item=$this->get_Page_Start_Item($this->CurrentItem);
		  		$Previous_Item=$Previous_Item - $this->Total_Records_Per_Page;		  		
		  		$Page_String="<a href='".$this->Script_Filename."?&".$this->Start_Variable."=".$Previous_Item.$ParamString."&".$vars."'>Prev</a> | ".$Page_String;
		  	}
		  	
		  	
		  	$Next_Item=$this->get_Page_Start_Item($this->CurrentItem);
		  	$Next_Item=$Next_Item + $this->Total_Records_Per_Page;		  	
		  	if($this->has_Next())
		  	{
		  		$Page_String.="  <a href='".$this->Script_Filename."?&".$this->Start_Variable."=".$Next_Item.$ParamString."&".$vars."'>Next</a> | ";
		  	}
		  }
		  
		  
		  if($this->Has_First_Last=="true")
		  {
		  	$First_Item=$this->get_Page_Start_Item(1);
		  	$Page_String="  <a href='".$this->Script_Filename."?&".$this->Start_Variable."=".$First_Item.$ParamString."&".$vars."'>First</a> |   ".$Page_String;
		  	$Last_Item=$this->get_Page_Start_Item($this->Total_Pages );
		  	$Page_String.="  <a href='".$this->Script_Filename."?&".$this->Start_Variable."=".$Last_Item.$ParamString."&".$vars."'>Last</a>";
		  }
		  
		
	      return $Page_String;
		
	}
	
	function has_Next()
	{
		$Next_Item=$this->get_Page_Start_Item($this->CurrentItem);
		$Next_Item=$Next_Item + $this->Total_Records_Per_Page;		  	
		if(($Next_Item / $this->Total_Records_Per_Page)<=($this->Total_Recs / $this->Total_Records_Per_Page))
		 return true;
		else
		 return false;		 
	}
	
	function has_Previous()
	{
		$Prev_Item=$this->get_Page_Start_Item($this->CurrentItem);
		$Prev_Item=$Prev_Item - $this->Total_Records_Per_Page;		  	
		if($Prev_Item >= $this->Total_Records_Per_Page)
		{
			 return true;
		}
		else
		{
			return false;
		}
	 
	}
	
	function set_Page_String($StringValue)
	{
		$this->Page_String=$StringValue;
	}
	
	function set_Start_Item($phpNewValue)
	{
		$phpNewValue=$phpNewValue/$this->Total_Records_Per_Page;	
		$this->CurrentItem=$phpNewValue;
	}
	
	
	function get_Page_String()
	{
		return $this->Page_String;
	}
	
	
	function set_Current_Item($ItemValue)
	{
		$this->CurrentItem=$ItemValue;
	}
	
	function get_Current_Item()
	{
		return $this->CurrentItem;
	}
	
	function get_Page_Start_Item($page_Number)
	{
		return $page_Number * $this->Total_Records_Per_Page;
	}
	
	function prepare_ParameterString($KeysandValues)
	{
		$ParamString="";
		if(!empty($KeysandValues))
		{
			if(is_array($KeysandValues))
			{
				foreach($KeysandValues as $ky=>$val)
				{
					$ParamString.="&".$ky."=".$val;
				}
			}
			else
			{
				echo "<font face=arial size=2 color=red><b>Please Send Parameters in Array</b></font>";
				die();
			}
		    return $ParamString;
		 }
		return;
	}

}

?>