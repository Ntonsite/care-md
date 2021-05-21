<?php
require('./roots.php');
require($root_path.'include/inc_environment_global.php');
$lang_tables[]='date_time.php';
$lang_tables[]='reporting.php';
require($root_path.'include/inc_front_chain_lang.php');
require($root_path.'language/en/lang_en_reporting.php');
require($root_path.'language/en/lang_en_date_time.php');
require($root_path.'include/inc_date_format_functions.php');
require_once($root_path.'include/care_api_classes/class_tz_insurance.php');


$insurance_obj = new Insurance_tz;

#Load and create paginator object
require_once($root_path.'include/care_api_classes/class_tz_reporting.php');
/**
 * getting summary of OPD...
 */
$rep_obj = new selianreport();


//require_once('include/inc_timeframe.php');
/**
 * Getting the timeframe...
 */
 $debug=FALSE;
$PRINTOUT=FALSE;
$EXPORT=FALSE;

if (empty($_GET['printout']) && empty($_GET['export'])) { 
	
					

			
			if(isset($_POST['date_from'])&&!empty($_POST['date_from'])&&isset($_POST['date_to'])&&!empty($_POST['date_to'])) 
			{
				                               
				$selected_date_from=@formatDate2STD($_POST['date_from'],$date_format);
				$selected_date_to=@formatDate2STD($_POST['date_to'],$date_format);					
						
								
			} 
			
//capture insurance company				
       if($_POST['insurance']==-1){
           $company="AND billelem.insurance_id=0";
       } elseif($_POST['insurance']==-2){
       $company="";	
       }else{
       $company="AND billelem.insurance_id=".$_POST['insurance'];
       }
      
        			
    } // end of if (empty($_GET['printout']))

   if($_GET['printout'] || isset($_GET['printout'])){
	   $PRINTOUT=TRUE;
	   }
	   
   if($_GET['export'] || isset($_GET['export'])){
	   $EXPORT=TRUE;
	   }	   



require_once('gui/gui_DetailedRevenue.php');
?>
