<?php
error_reporting(E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR);
require('./roots.php');
require($root_path.'include/inc_environment_global.php');
/**
* CARE2X Integrated Hospital Information System version deployment 1.1 (mysql) 2004-01-11
* GNU General Public License
* Copyright 2002,2003,2004,2005 Elpidio Latorilla
* , elpidio@care2x.org
*
* See the file "copy_notice.txt" for the licence notice
*/

define('SHOW_DOC_2',1);  # Define to 1 to  show the 2nd doctor-on-duty
define('DOC_CHANGE_TIME','7.30'); # Define the time when the doc-on-duty will change in 24 hours H.M format (eg. 3 PM = 15.00, 12 PM = 0.00)

$lang_tables[]='ambulatory.php';
$lang_tables[]='prompt.php';
$lang_tables[]='departments.php';

define('LANG_FILE','nursing.php');
//define('NO_2LEVEL_CHK',1);


// get current url < dennis . mollel @ yahoo . com
$curpath = 'http';
if ($_SERVER["HTTPS"] == "on") $curpath .= "s";
$curpath .= "://" . $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];


$local_user='ck_pflege_user';
require_once($root_path.'include/inc_front_chain_lang.php');


# create multi functional object
        include_once($root_path.'include/care_api_classes/class_multi.php');
        $multi_obj = New multi;
		$vct = $multi_obj->__genNumbers();


/**
* Set default values if not available from url
*/
//if (!isset($dept_nr)||empty($dept_nr)) { $dept_nr=$_SESSION['sess_dept_nr'];} # Default station must be set here !!
if(!isset($pday)||empty($pday)) $pday=date('d');
if(!isset($pmonth)||empty($pmonth)) $pmonth=date('m');
if(!isset($pyear)||empty($pyear)) $pyear=date('Y');
$s_date=$pyear.'-'.$pmonth.'-'.$pday;

if($s_date==date('Y-m-d')) $is_today=true;
	else $is_today=false;

//$db->debug=1;

$tnow=date('H:i:s');

if(!isset($mode)) $mode='';

$breakfile='amb_clinic_patients_discharge.php'.URL_APPEND; # Set default breakfile
if($backpath) $breakfile=urldecode($backpath).URL_APPEND;
$thisfile=basename($_SERVER['PHP_SELF']);

if(isset($retpath)){
	switch($retpath)
	{
		case 'quick': $breakfile='nursing-schnellsicht.php'.URL_APPEND;
							break;
		case 'ward_mng': $breakfile='nursing-station-info.php'.URL_APPEND.'&ward_nr='.$ward_nr.'&mode=show';
							break;
		case 'billing' : $breakfile='../modules/billing_tz/billing_tz_pending.php'.URL_APPEND;
	}
}
# Mark where we are
$_SESSION['sess_user_origin']='amb';

# Load date formatter
require_once($root_path.'include/inc_date_format_functions.php');

if(($mode=='')||($mode=='fresh')){

	# Create encounter object
	include_once($root_path.'include/care_api_classes/class_encounter.php');
	$enc_obj= new Encounter;

	# Get all outpatients for this dept
	 if($sort=='') $sort='name_last';
	$opat_obj=&$enc_obj->OutPatientsBasic($sort,false);

	//echo $enc_obj->getLastQuery();
	$rows=$enc_obj->LastRecordCount();
	/*
	# If dept name is empty, fetch by location nr
	if(!isset($dept)||empty($dept)){
		# Create department object
		include_once($root_path.'include/care_api_classes/class_department.php');
		$dept_obj= new Department;
		$deptLDvar=$dept_obj->LDvar($dept_nr);
		if(isset($$deptLDvar)&&!empty($$deptLDvar)) $dept=$$deptLDvar;
			else $dept=$dept_obj->FormalName($dept_nr);
	}
	*/
	# set to edit mode
	$edit=true;


		# Create the waiting outpatients??? list

		$dnr=(isset($w_waitlist)&&$w_waitlist) ? 0 : $dept_nr;
		//echo '<p>'.$enc_obj->getLastQuery();
		$waitlist=&$enc_obj->createWaitingOutpatientList($dnr);

		$waitlist_count=$enc_obj->LastRecordCount();
		//echo $waitlist_count.'<p>'.$enc_obj->getLastQuery();

		# Get the doctor???s on duty information
		#### Start of routine to fetch doctors on duty
		$elem='duty_1_pnr';
		if(SHOW_DOC_2) $elem.=',duty_2_pnr';

		# Create personnel object

		include_once($root_path.'include/care_api_classes/class_personell.php');

		$pers_obj=new Personell;

		if($result=$pers_obj->getDOCDutyplan($dept_nr,$pyear,$pmonth,$elem)){
			$duty1=&unserialize($result['duty_1_pnr']);
			if(SHOW_DOC_2) $duty2=&unserialize($result['duty_2_pnr']);
					//echo $sql."<br>";
		}
		//echo $pers_obj->getLastQuery();
		# Adjust the day index. This is necessary since change of duty usually happens early morning  not midnight
		$offset_day=$pday-1;
		# Consider the early morning hours to belong to the past day
		if(date('H.i')<DOC_CHANGE_TIME) $offset_day--;
		if($pnr1=$duty1['ha'.$offset_day]){
			$person1=&$pers_obj->getPersonellInfo($pnr1);
		}
		if(SHOW_DOC_2 && ($pnr2=$duty2['hr'.$offset_day])){
			$person2=&$pers_obj->getPersonellInfo($pnr2);
		}
		#### End of routine to fetch doctors on duty
}

# Start Smarty templating here
 /**
 * LOAD Smarty
 */

 # Note: it is advisable to load this after the inc_front_chain_lang.php so
 # that the smarty script can use the user configured template theme

 require_once($root_path.'gui/smarty_template/smarty_care.class.php');
 $smarty = new smarty_care('nursing');

# Title in toolbar
 $smarty->assign('sToolbarTitle', $LDDischargePatients." (".formatDate2Local($s_date,$date_format,'','',$null='').")");

  # hide back button
 $smarty->assign('pbBack',FALSE);

 # href for help button
 $smarty->assign('pbHelp',"javascript:gethelp('discharge_patient.php','Discharge Patient')");

 # href for close button
 $smarty->assign('breakfile',$breakfile);

 # Window bar title
 $smarty->assign('sWindowTitle',$dept." :: $LDOutpatientClinic (".formatDate2Local($s_date,$date_format,'','',$null='').")");


# Reload the page after 5 minutes
	$smarty->assign('sReloadPage',TRUE);
	$smarty->assign('sReloadTarget',$REQUEST_URI.'&refreshed=1');
	$smarty->assign('sReloadDelay',300);


 # Collect extra javascript code

 ob_start();
?>

<script language="javascript">
<!--
  var urlholder;

  function CheckAll()
{
count = document.discharge_form.elements.length;
    for (i=0; i < count; i++)
	{
    if(document.discharge_form.elements[i].checked == 1)
    	{document.discharge_form.elements[i].checked = 1; }
    else {document.discharge_form.elements[i].checked = 1;}
	}
}

function UncheckAll(){
count = document.discharge_form.elements.length;
    for (i=0; i < count; i++)
	{
    if(document.discharge_form.elements[i].checked == 1)
    	{document.discharge_form.elements[i].checked = 0; }
    else {document.discharge_form.elements[i].checked = 0; }
	}
}


 function checkUncheckAll(ref) {

  var chkAll = document.getElementById('checkUncheckAll');
  var checks = document.getElementsByName('checkbox[]');
  var boxLength = checks.length;
  var allChecked = false;
  var totalChecked = 0;

  if ( ref == 1 ) {
    if ( chkAll.checked == false ) {

      for ( i=0; i < boxLength; i++ ) {
        checks[i].checked = true;
      }
    }
    else {

      for ( i=0; i < boxLength; i++ ) {
        checks[i].checked = false;
      }
    }
  }
  else {

    for ( i=0; i < boxLength; i++ ) {
      if ( checks[i].checked == true ) {
        allChecked = false;
		checks[i].checked=false;
        continue;
      }
      else {
        allChecked = false;
        break;
      }
    }
    if ( allChecked == true ) {
      chkAll.checked = true;
    }
    else {
      chkAll.checked = false;
    }
  }
  for ( j=0; j < boxLength; j++ ) {
    if ( checks[j].checked == true ) {
      totalChecked++;
	}
  }

}


function confSubmit(form) {

	if (confirm("Discharge Selected Patients?")) {
		form.submit();
		return true;
	}

else {
		alert("Selected Patients Not Discharged!");
		//document.discharge_form.reset();
		return false;
	}
}

function discharge(x)
{
	discharge=x;

	if(x==1)
	{
		what = "Inpatient"
	}
	else if(x==2)
	{
		what = "Outpatient"
	}

	if(confirm("Do you realy want to discharge all " + what)){

		window.location.href="amb_clinic_patients_discharge.php<?php echo URL_REDIRECT_APPEND ?>&discharge="+what+"";

	}
	else{

		window.location.href="amb_clinic_patients_discharge.php";
	}
}



function getinfo(pn){
<?php /* if($edit) {*/

	{ echo '
	urlholder="../nursing/nursing-station-patientdaten.php'.URL_REDIRECT_APPEND;
	echo '&pn=" + pn + "';
	echo "&pday=$pday&pmonth=$pmonth&pyear=$pyear&edit=$edit&station=".$ward_info['name'];
	echo '";';
	echo '
	patientwin=window.open(urlholder,pn,"width=700,height=600,menubar=no,resizable=yes,scrollbars=yes");
	';
	}

	/*}else echo '
	window.location.href=\'nursing-station-pass.php'.URL_APPEND.'&rt=pflege&edit=1&station='.$station.'\'';*/
?>
	}

function getrem(pn){
	urlholder="<?php echo $root_path ?>modules/nursing/nursing-station-remarks.php<?php echo URL_REDIRECT_APPEND; ?>&pn="+pn+"<?php echo "&dept_nr=$dept_nr&pday=$pday&pmonth=$pmonth&pyear=$pyear"; ?>";
	patientwin=window.open(urlholder,pn,"width=700,height=500,menubar=no,resizable=yes,scrollbars=yes");
	}
function release(nr)
{
	urlholder="amb_clinic_discharge.php<?php echo URL_REDIRECT_APPEND; ?>&pn="+nr+"<?php echo "&pyear=".$pyear."&pmonth=".$pmonth."&pday=".$pday."&tb=".str_replace("#","",$cfg['top_bgcolor'])."&tt=".str_replace("#","",$cfg['top_txtcolor'])."&bb=".str_replace("#","",$cfg['body_bgcolor'])."&d=".$cfg['dhtml']; ?>&station=<?php echo $station; ?>&dept_nr=<?php echo $dept_nr; ?>&backpath=<?php echo $breakfile; ?>";
	indatawin=window.open(urlholder,"bedroom","width=700,height=730,menubar=no,resizable=yes,scrollbars=yes");
	//window.location.href=urlholder;
}

function release_info(nr)
{
	urlholder="amb_clinic_discharge_info.php<?php echo URL_REDIRECT_APPEND; ?>&pn="+nr+"<?php echo "&pyear=".$pyear."&pmonth=".$pmonth."&pday=".$pday."&tb=".str_replace("#","",$cfg['top_bgcolor'])."&tt=".str_replace("#","",$cfg['top_txtcolor'])."&bb=".str_replace("#","",$cfg['body_bgcolor'])."&d=".$cfg['dhtml']; ?>&station=<?php echo $station; ?>&dept_nr=<?php echo $dept_nr; ?>&backpath=<?php echo $breakfile; ?>";
	indatawin=window.open(urlholder,"bedroom","width=700,height=730,menubar=no,resizable=yes,scrollbars=yes");
	//window.location.href=urlholder;
}


function popinfo(l,d)
{
	urlholder="<?php echo $root_path ?>modules/doctors/doctors-dienstplan-popinfo.php<?php echo URL_REDIRECT_APPEND ?>&nr="+l+"&dept_nr="+d+"&user=<?php echo $aufnahme_user.'"' ?>;

	infowin=window.open(urlholder,"dienstinfo","width=400,height=450,menubar=no,resizable=yes,scrollbars=yes");

}
function assignWaiting(pn,pw)
{
	urlholder="amb_clinic_assignwaiting.php<?php echo URL_REDIRECT_APPEND ?>&pn="+pn+"&pdept="+pw+"&dept_nr=<?php echo $dept_nr ?>&station=<?php echo $station ?>";
	asswin<?php echo $sid ?>=window.open(urlholder,"asswind<?php echo $sid ?>","width=650,height=500,menubar=no,resizable=yes,scrollbars=yes");

}
function Transfer(pn,pw)
{
	if(confirm("<?php echo $LDSureTransferPatient ?>")){
		urlholder="amb_clinic_transfer_select.php<?php echo URL_REDIRECT_APPEND ?>&pn="+pn+"&pat_station="+pw+"&dept_nr=<?php echo $dept_nr ?>&station=<?php echo $station ?>";
		transwin<?php echo $sid ?>=window.open(urlholder,"transwin<?php echo $sid ?>","width=550,height=620,menubar=no,resizable=yes,scrollbars=yes");
	}


}

<?php
require($root_path.'include/inc_checkdate_lang.php');
?>

// -->
</script>

<script language="javascript" src="<?php echo $root_path; ?>js/setdatetime.js"></script>

<script language="javascript" src="<?php echo $root_path; ?>js/checkdate.js"></script>

<?php
$sTemp = ob_get_contents();

ob_end_clean();

$smarty->append('JavaScript',$sTemp);



# If ward exists, show the occupancy list

if($rows){

	$smarty->assign('bShowPatientsList',TRUE);

	if($s_date<date('Y-m-d')){
	 	$smarty->assign('sWarningPrompt','<img '.createComIcon($root_path,'warn.gif','0','absmiddle',TRUE).'> <font color="#ff0000"><b>'.$LDAttention.'</font> '.$LDOldList.'</b>');

		# Prevent adding new patients to the list  if list is old
		$edit=FALSE;
	}

	# Start here, create the occupancy list
	# Assign the column  names
	$smarty->assign('sFormFile',$thisfile);

	// manual reload button
	$smarty->assign('sReloadBtn',$curpath);

	$smarty->assign('LDTime',$LDTime);
	$smarty->assign('LDRoom',$LDRoom);
	$smarty->assign('LDBed',$LDPatListElements[1]);
	$smarty->assign('LDFamilyName','<a href="amb_clinic_patients_discharge.php?&sort=name_last">'.$LDLastName.'</a>');
	$smarty->assign('LDName','<a href="amb_clinic_patients_discharge.php?&sort=name_first">'.$LDName.'</a>');
	$smarty->assign('LDBirthDate','<a href="amb_clinic_patients_discharge.php?&sort=date_birth">'.$LDBirthDate.'</a>');
	$smarty->assign('LDPatNr','<a href="amb_clinic_patients_discharge.php?&sort=encounter_nr">'.$LDPatListElements[4].'</a>');
	$smarty->assign('LDAdmissionDate','<a href="amb_clinic_patients_discharge.php?&sort=encounter_date">'.$LDAdmissionDate.'</a>');
	#$smarty->assign('LDSelian_pid',$LDFile_Nr);
	$smarty->assign('LDInsuranceType',$LDPatListElements[6]);
	$smarty->assign('LDOptions',$LDPatListElements[7]);

	# Initialize help flags
	$toggle=1;
	$room_info=array();

	$males=0;
	$females=0;

	# Initialize list rows container string
$sListRows='';
	# Loop trough patients


	while ($patient=$opat_obj->FetchRow()){


			if($patient['encounter_nr']==$sEncNrBuffer) continue;
				else $sEncNrBuffer=$patient['encounter_nr'];

			$id = $patient['encounter_nr'];

			# Reset elements

			$smarty->assign('sMiniColorBars','');
			$smarty->assign('sRoom','');
			$smarty->assign('sBed','');
			$smarty->assign('sBedIcon','');
			$smarty->assign('cComma','');
			$smarty->assign('sFamilyName','');
			$smarty->assign('sName','');
			$smarty->assign('sTitle','');
			$smarty->assign('sBirthDate','');
			$smarty->assign('sPatNr','');
			$smarty->assign('sAdmissionDate','');
			$smarty->assign('sAdmitDataIcon','');
			$smarty->assign('sChartFolderIcon','');
			$smarty->assign('sNotesIcon','');
			$smarty->assign('sTransferIcon','');
			$smarty->assign('sDischargeIcon','');
			$smarty->assign('sSelianPid','');
			$smarty->assign('sClass','');
			$smarty->assign('sCheckBox','');

			$smarty->assign('sFlagDiag','');
			$smarty->assign('sFlagDiag2',' ');
			$smarty->assign('sNoDiag','  ');

			$sAstart='';
			$sAend='';
			$sFamNameBuffer='';
			$sNameBuffer='';
			$sPidBuffer='';

			# set row color/class

			if ($toggle){
				$smarty->assign('bToggleRowClass',TRUE);
			}else{
				$smarty->assign('bToggleRowClass',FALSE);
			}

			$toggle=!$toggle;


			# If appt time is past the now time, color font with red
			/*if($patient['time']<$tnow) $smarty->assign('sTimeFontClass'.'class="cave_data"');
				elseif(($patient['time']>=$tnow)&&($patient['time']<=$tnow)) $smarty->assign('sTimeFontClass'.'class="go_data"');

			if($patient['time']) $smarty->assign('sTime',convertTimeToLocal($patient['time'],$date_format));
*/

			switch($patient['encounter_class_nr']){
				case 1:
						$smarty->assign('sClass','<Font color="blue" size="1">Inpatient</font>');
						break;
				case 2:
						$smarty->assign('sClass','<font color="red" size="1">Outpatient</font>');
						break;
				default:
						$smarty->assign('sClass','');
			}

			# If patient and edit show small color bars

			if($edit){
			 	$smarty->assign('sMiniColorBars','<a href="javascript:getinfo(\''.$patient['encounter_nr'].'\')">
			 		<img src="'.$root_path.'main/imgcreator/imgcreate_colorbar_small.php'.URL_APPEND.'&pn='.$patient['encounter_nr'].'" alt="'.$LDSetColorRider.'" align="absmiddle" border=0 width=80 height=18>
			 		</a>');

			}


			# Show images by sex

			$sBuffer = '<a href="javascript:popPic(\''.$patient['pid'].'\')" title="'.$LDShowPhoto.'">';
			switch(strtolower($patient['sex'])){
				case 'f':
						$smarty->assign('sBedIcon',$sBuffer.'<img '.createComIcon($root_path,'spf.gif','0','',TRUE).'></a>');
						$females++;
						break;
				case 'm':
						$smarty->assign('sBedIcon',$sBuffer.'<img '.createComIcon($root_path,'spm.gif','0','',TRUE).'></a>');
						$males++;
						break;
				default:
						$smarty->assign('sBedIcon',$sBuffer.'<img '.createComIcon($root_path,'bn.gif','0','',TRUE).'></a>');
			}

			# Show the patients name with link to open charts
			if($edit){

				$sAstart = '<a href="'.$root_path.'modules/registration_admission/aufnahme_pass.php'.URL_APPEND.'&target=search&fwd_nr='.$patient['encounter_nr'].'" title="'.$LDClk2Show.'">';

			}

			$smarty->assign('sTitle',ucfirst($patient['title']));

			if(isset($sln)&&$sln) $sFamNameBuffer = eregi_replace($sln,'<span style="background:yellow">'.ucfirst($sln).'</span>',ucfirst($patient['name_last']));
				else $sFamNameBuffer = ucfirst($patient['name_last']);

			if($bed['name_last']) $smarty->assign('cComma',',');
				else $smarty->assign('cComma','');

			if(isset($sfn)&&$sfn) $sNameBuffer = eregi_replace($sfn,'<span style="background:yellow">'.ucfirst($sln).'</span>',ucfirst($patient['name_first']));
				else $sNameBuffer = ucfirst($patient['name_first']);



			if($edit) $sAend ='</a>';
				else $sAend='';

			# Assign the family and first names together with the <a href></a> tags
			# WITHOUT LINKS
			$smarty->assign('sFamilyName',$sFamNameBuffer);
			$smarty->assign('sName',$sNameBuffer);

			/* old code
			$smarty->assign('sFamilyName',$sAstart.$sFamNameBuffer.$sAend);
			$smarty->assign('sName',$sAstart.$sNameBuffer.$sAend);
			*/

			# discharge without diagnosis
			$nodischarge = 0;

			$kct = $multi_obj->CheckDiagnosis($patient['encounter_nr']);
			if (($vct[9]==1)&&($patient['encounter_nr']!='')){
				if ($kct<1){
					$smarty->assign('sFlagDiag',' style="background-color:yellow !important; color:#000000;" ');
					$smarty->assign('sFlagDiag2',' style="background-color:gold !important; color:#000000;" ');
					$smarty->assign('sNoDiag',' [ No Dx ] ');
					$nodischarge = 1;
				}
			}

			if($patient['date_birth']){

				if(isset($sg)&&$sg) $smarty->assign('sBirthDate',eregi_replace($sg,"<font color=#ff0000><b>".ucfirst($sg)."</b></font>",formatDate2Local($patient['date_birth'],$date_format)));
					else $smarty->assign('sBirthDate',formatDate2Local($patient['date_birth'],$date_format));
			}

			$sBuffer = '';
			 if(isset($$patient['selian_pid'])&&!empty($$patient['selian_pid']))  $sBuffer = $sBuffer.$$patient['selian_pid'];
                                else  $sBuffer = $sBuffer.$patient['selian_pid'];

			$smarty->assign('sPatNr',$sBuffer);


			if ($patient['encounter_nr'])
			{

				$smarty->assign('sAdmissionDate',formatDate2Local($patient['encounter_date'],$date_format,'','',$null=''));

			}

			$sBuffer = '';
			#if($patient['insurance_class_nr']!=2) $sBuffer = $sBuffer.'<font color="#ff0000">';

			$smarty->assign('sInsuranceType',$sBuffer);

			if($edit){

				/* MEROTECH:
				Commented out for selian town clinic by Alexander Irro
				$smarty->assign('sAdmitDataIcon','<a href="'.$root_path.'modules/registration_admission/aufnahme_pass.php'.URL_APPEND.'&target=search&fwd_nr='.$patient['encounter_nr'].'" title="'.$LDAdmissionData.' : '.$LDClk2Show.'"><img '.createComIcon($root_path,'pdata.gif','0','',TRUE).' alt="'.$LDAdmissionData.' : '.$LDClk2Show.'"></a>');

				$smarty->assign('sChartFolderIcon','<a href="javascript:getinfo(\''.$patient['encounter_nr'].'\')"><img '.createComIcon($root_path,'open.gif','0','',TRUE).' alt="'.$LDShowPatData.'"></a>');

				$sBuffer = '<a href="javascript:getrem(\''.$patient['encounter_nr'].'\')"><img ';
				if($patient['notes']) $sBuffer = $sBuffer.createComIcon($root_path,'bubble3.gif','0','',TRUE);
					else $sBuffer = $sBuffer.createComIcon($root_path,'bubble2.gif','0','',TRUE);
				$sBuffer = $sBuffer.' alt="'.$LDNoticeRW.'"></a>';

				$smarty->assign('sNotesIcon',$sBuffer);

				$smarty->assign('sTransferIcon','<a href="javascript:Transfer(\''.$patient['encounter_nr'].'\')"><img '.createComIcon($root_path,'xchange.gif','0','',TRUE).' alt="'.$LDTransferPatient.'"></a>');
				*/
				$smarty->assign('sDischargeInfoIcon','<a href="javascript:void(0)" onclick="release_info(\''.$patient['encounter_nr'].'\')" title="show info"><img '.createComIcon($root_path,'button_info.gif','0','',TRUE).' alt="show info"></a>');


				$smarty->assign('sDischargeIcon','<a href="javascript:void(0)" onclick="'.((($vct[11]==1)&&($kct==0))?'alert(\'This Patient has no Diagnosis \n You can not Discharge.\')':'release(\''.$patient['encounter_nr'].'\')').'" title="'.$LDReleasePatient.'"><img '.createComIcon($root_path,'bestell.gif','0','',TRUE).' alt="'.$LDReleasePatient.'"></a>');

				if($patient['encounter_class_nr'] == 2)
				{
					$smarty->assign('sCheckBox','<input name="checkbox[]" '.((($vct[11]==1)&&($kct==0))?' disabled="disabled"':'').' type="checkbox" id="checkbox[]" value="'.$id.'">' );
				}

		}

			# Create the rows using ward_occupancy_list_row.tpl template
			ob_start();
				$smarty->display('ambulatory/outpatients_list_row.tpl');
				$sListRows = $sListRows.ob_get_contents();
			ob_end_clean();

		# Append the new row to the previous row in string

		$smarty->assign('sOccListRows',$sListRows);

		//$countCheck= count($_POST['checkbox']);

		//$smarty->assign('scountCheck','<input type= "hidden" name ="countCheck" id = "countCheck" value="'.$countCheck.'">');


	} // end of patients loop
}else{

	# Prompt no outpatients available
	$smarty->assign('sWarningPrompt','
			<div class="warnprompt"><p><ul><img '.createMascot($root_path,'mascot1_r.gif','0','absmiddle').'>
			'.str_replace("~station~",strtoupper($station),"There are no patients yet.").'</b></font><br><img '.createComIcon($root_path,'bul_arrowgrnlrg.gif','0','',TRUE).'>
			<a href="'.$root_path.'modules/appointment_scheduler/appt_main_pass.php'.URL_APPEND.'&dept_nr='.$dept_nr.'&dept_name='.$dept.'">'.$LDGoToAppointments.'</a><p>
			</ul></div>');
}

//discharge all outpatients at time
if($discharge=='Outpatient'){
		//echo '<script type="javascript"> confim("Do you realy want to discharge all Outpatients at time")</script>';
				$enc_obj->DischargeAllPatient(2);
	//echo 'prakash';
	$discharge='';
	if (!isset($_GET['reload'])) {
		echo '<meta http-equiv=Refresh content="0;url=amb_clinic_patients_discharge.php?reload=1">';
	}
}

//discharge all inpatients at time
if($discharge=='Inpatient'){
		//echo '<script type="javascript"> confim("Do you realy want to discharge all Outpatients at time")</script>';

			$enc_obj->DischargeAllPatient(1);

	//echo 'prakash';
	$discharge='';
	if (!isset($_GET['reload'])) {
		echo '<meta http-equiv=Refresh content="0;url=amb_clinic_patients_discharge.php?reload=1">';
	}
}


//discharge all selected at time

			 if($_POST['discharge_checked']){


				 $date=(empty($x_date))?date('Y-m-d'):formatDate2STD($x_date,$date_format);
				 $time=(empty($x_time))?date('H:i:s'):convertTimeToStandard($x_time);
				 $d_type_nr='4';

				 $checkbox = $_POST['checkbox']; //from name="checkbox[]"
				 $countCheck = count($_POST['checkbox']);

				 for($i=0;$i<$countCheck;$i++)
		 			{
						$disch_id  = $checkbox[$i];

						$enc_obj->Discharge($disch_id,$d_type_nr,$date,$time);

					}




	//echo 'prakash';
	$discharge='';

	if (!isset($_POST[reload])) {
		echo '<meta http-equiv=Refresh content="0;url=amb_clinic_patients_discharge.php?reload=1">';

	}

}


//$test='sdadf';

$smarty->assign('LDSelectOutpatients','</a><a href="javascript:checkUncheckAll(1)">
<input type="image"  id = "checkUncheckAll" value="Select All Outpatients"></a>');

$smarty->assign('LDUnSelectOutpatients','</a><a href="javascript:checkUncheckAll(0)">
<input type="image"  id = "checkUncheckAll" src="'.$rootpath.'./../../gui/img/control/blue_aqua/en/en_close2.gif"></a>');


# Assign href  for  bottom close button
$smarty->assign('pbClose','<a href="'.$breakfile.'">
<img border="0" src="'.$rootpath.'./../../gui/img/control/blue_aqua/en/en_close2.gif"></a>');


$smarty->assign('LDSelectOutpatients','<input name ="select_all" type ="button"
value = "Select All Outpatients" onClick="CheckAll()">');

$smarty->assign('LDUnSelectOutpatients','<input name ="select_all" type ="button"
value = "UnSelect All Outpatients" onClick="UncheckAll()">');

$smarty->assign('sDischargeSelected','<input name ="discharge_checked" type = "submit" id = "discharge_checked" value ="Discharge Checked Patients">');

//$smarty->assign('sMiniColorBars','<a href="javascript:getinfo(\''.$patient['encounter_nr'].'\')">
	//		 		<img src="'.$root_path.'main/imgcreator/imgcreate_colorbar_small.php'.URL_APPEND.'&pn='.$patient['encounter_nr'].'" alt="'.$LDSetColorRider.'" align="absmiddle" border=0 width=80 height=18>
		//	 		</a>');
//$smarty->assign('pbClose','<a href="'.$breakfile.'"><img '.createLDImgSrc($root_path,'close2.gif','0','absmiddle').'></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="amb_clinic_patients_discharge.php?&discharge=discharge all">Please Click Here To Discharge All Patients</a>');
/*
if($rows){

	if($s_date<date('Y-m-d')){
	 	echo '
		<font face="verdana,arial" size="2"><img '.createComIcon($root_path,'warn.gif','0','absmiddle').'> <font color="#ff0000"><b>'.$LDAttention.'</font> '.$LDOldList.'</b></font> ';
		$edit=0;
	}

# Start here, create the occupancy list

$occ_list='<table  cellpadding="0" cellspacing=0 border="0" >';

$occ_list.='<tr bgcolor="#0000dd" align=center>';
# Add the description row

$occ_list.='
	<td><font face="verdana,arial" size="2" color="#ffffff"><b>'.$LDTime.'&nbsp;&nbsp;</b></td>
	<td><font face="verdana,arial" size="2" color="#ffffff"><b>&nbsp;</b></td>
	<td><font face="verdana,arial" size="2" color="#ffffff"><b>&nbsp;</b></td>
	<td><font face="verdana,arial" size="2" color="#ffffff"><b>'.$LDLastName.'&nbsp;&nbsp;</b></td>
	<td><font face="verdana,arial" size="2" color="#ffffff"><b>'.$LDName.'&nbsp;&nbsp;</b></td>
	<td><font face="verdana,arial" size="2" color="#ffffff"><b>'.$LDBirthDate.' &nbsp;&nbsp;</b></td>
	<td><font face="verdana,arial" size="2" color="#ffffff"><b>'.$LDFinanceType.'&nbsp;&nbsp;</b></td>
	<td><font face="verdana,arial" size="2" color="#ffffff"><b>'.$LDAdm_Nr.'&nbsp;&nbsp;</b></td>
	<td><font face="verdana,arial" size="2" color="#ffffff"><b>'.$LDOptions.'&nbsp;&nbsp;</b></td>';


$occ_list.= '</tr>';

$opat_obj->MoveFirst();

$toggle=1;
$room_info=array();
$males=0;
$females=0;
while ($patient=$opat_obj->FetchRow()){

	# set row color
	$occ_list.='
			<tr bgcolor=';
	if ($toggle) $occ_list.='"#fefefe">'; else $occ_list.='"#dfdfdf">';

	$toggle=!$toggle;

	$occ_list.='
			<td><font face="verdana,arial" size="2"';
	# If appt time is past the now time, color font with red
	if($patient['time']<$tnow) $occ_list.=' color="red"';
		elseif(($patient['time']>=$tnow)&&($patient['time']<=$tnow)) $occ_list.=' color="green"';
	$occ_list.='>';
	if($patient['time']) $occ_list.=convertTimeToLocal($patient['time'],$date_format);
	$occ_list.='&nbsp;</td><td>';
	# If edit show small color bars
	if($edit)
	{
		 $occ_list.='<a href="javascript:getinfo(\''.$patient['encounter_nr'].'\')" title="'.$LDSetColorRider.'">
		 <img src="'.$root_path.'main/imgcreator/imgcreate_colorbar_small.php'.URL_APPEND.'&pn='.$patient['encounter_nr'].'" alt="'.$LDSetColorRider.'" align="absmiddle" border=0 width=80 height=18>
		 </a>';
    }
	$occ_list.='&nbsp;
			</td>
			<td align=center><font face="verdana,arial" size="2" >';
	# If patient, show images by sex
	$occ_list.='<a href="javascript:popPic(\''.$patient['pid'].'\')" title="'.$LDShowPhoto.'">';
		switch(strtolower($patient['sex']))
		{
			case 'f': $occ_list.='<img '.createComIcon($root_path,'spf.gif','0'); $females++; break;
			case 'm': $occ_list.='<img '.createComIcon($root_path,'spm.gif','0'); $males++; break;
			default: $occ_list.='<img '.createComIcon($root_path,'bn.gif','0');break;
		}

	 $occ_list.=' alt="'.$LDShowPhoto.'"></a>';

	$occ_list.='&nbsp;
	</td>';
	$occ_list.='
			<td><font face="verdana,arial" size="2" >';
	# Show the patients name with link to open charts
	if($edit)
	{
	  //$occ_list.='<a href="javascript:';
	   //$occ_list.='getinfo(\''.$patient['encounter_nr'].'\')" title="'.$LDShowPatData.'">';
	  $occ_list.='<a href="'.$root_path.'modules/registration_admission/aufnahme_pass.php'.URL_APPEND.'&target=search&fwd_nr='.$patient['encounter_nr'].'" title="'.$LDAdmissionData.' : '.$LDClk2Show.'">';
	}

	$occ_list.=ucfirst($patient['title']).' ';

	 $occ_list.=ucfirst($patient['name_last']);

	if($edit) $occ_list.='</a>';


	$occ_list.='&nbsp;
	</td><td><font face="verdana,arial" size="2">'.ucfirst($patient['name_first']);


	$occ_list.='&nbsp;
			</td><td align=right><font face="verdana,arial" size="2">&nbsp;';

    if($patient['date_birth'])
	{
	   $occ_list.=formatDate2Local($patient['date_birth'],$date_format);
    }
	$occ_list.='&nbsp;
			</td><td ><font face="verdana,arial" size="2" >&nbsp;';
	if($patient['insurance_class_nr']!=2) $occ_list.='<font color="#ff0000">';
	if(isset($$patient['LD_var'])&&!empty($$patient['LD_var'])) $occ_list.=$$patient['LD_var'];
		else $occ_list.=$patient['insurance_name'];
	$occ_list.='&nbsp;
	</td>
	<td><font face="verdana,arial" size="2">&nbsp;'.$patient['encounter_nr'].'&nbsp;
	</td>';

	if($edit)
	{
		$occ_list.='
			<td><nobr>&nbsp;';

		$occ_list.='<a href="'.$root_path.'modules/registration_admission/aufnahme_pass.php'.URL_APPEND.'&target=search&fwd_nr='.$patient['encounter_nr'].'" title="'.$LDAdmissionData.' : '.$LDClk2Show.'">';
		$occ_list.='<img '.createComIcon($root_path,'pdata.gif','0').' alt="'.$LDAdmissionData.' : '.$LDClk2Show.'"></a>';
		$occ_list.='
		<a href="javascript:getinfo(\''.$patient['encounter_nr'].'\')" title="'.$LDShowPatData.'"><img '.createComIcon($root_path,'open.gif','0').' alt="'.$LDShowPatData.'"></a>
	 	<a href="javascript:getrem(\''.$patient['encounter_nr'].'\')" title="'.$LDNoticeRW.'"><img ';
		if($patient['notes']) $occ_list.=createComIcon($root_path,'bubble3.gif','0'); else $occ_list.=createComIcon($root_path,'bubble2.gif','0');
		$occ_list.=' alt="'.$LDNoticeRW.'"></a>';
		$occ_list.='&nbsp;<a href="javascript:Transfer(\''.$patient['encounter_nr'].'\')" title="'.$LDTransferPatient.'"><img '.createComIcon($root_path,'xchange.gif','0').' alt="'.$LDTransferPatient.'"></a>
		 <a href="javascript:release(\''.$patient['encounter_nr'].'\')" title="'.$LDReleasePatient.'"><img '.createComIcon($root_path,'bestell.gif','0').' alt="'.$LDReleasePatient.'"></a>';
		 //<a href="javascript:deletePatient(\''.$helper[r].'\',\''.$helper[b].'\',\''.$helper[t].'\',\''.$helper[ln].'\')"><img src="../img/delete.gif" border=0 width=19 height=19 alt="L???schen (Passwort erforderlich)"></a>';

		 $occ_list.='</nobr>
	 	</td>
		</tr>
		 <tr><td bgcolor="#0000ee" colspan="9"><img '.createComIcon($root_path,'pixel.gif').'></td></tr>
	 	';
	}
}
# Final occupancy list line
$occ_list.='</table>';
}

echo $occ_list;
*/
/*
# Declare template items
$TP_DOC1_BLOCK='';
$TP_DOC2_BLOCK='';
$TP_ICON1='';
$TP_ICON2='';
$TP_Legend1_BLOCK='';

//$buf1='<img '.createComIcon($root_path,'powdot.gif','0','absmiddle').'>';
# Create waiting list block
if($waitlist_count){
	while($waitpatient=$waitlist->FetchRow()){
		$buf2='';
		if($waitpatient['current_dept_nr']!=$dept_nr) $buf2=createComIcon($root_path,'red_dot.gif','0','',TRUE);
			else  $buf2=createComIcon($root_path,'green_dot.gif','0','',TRUE);
		$TP_WLIST_BLOCK.='<nobr><img '.$buf2.'>&nbsp;<a href="javascript:assignWaiting(\''.$waitpatient['encounter_nr'].'\',\''.$waitpatient['dept_LDvar'].'\')">'.$waitpatient['name_last'].', '.$waitpatient['name_first'].' '.formatDate2Local($waitpatient['date_birth'],$date_format).'</nobr></a><br>';
	}
}
$wlist_url=$thisfile.URL_APPEND.'&dept_nr='.$dept_nr.'&edit='.$edit.'&station='.$station;
if($w_waitlist){
	$TP_WLIST_OPT =	'[<a href="'.$wlist_url.'&w_waitlist=0">'.$LDShowClinicOnly.'</a>]';
}else{
	$TP_WLIST_OPT=	'[<a href="'.$wlist_url.'&w_waitlist=1">'.$LDShowAll.'</a>]';
}

# Create doctors-on-duty block
if(isset($person1)){
	$TP_DOC1_BLOCK='<a href="javascript:popinfo(\''.$pnr1.'\',\''.$dept_nr.'\')" title="'.$LDClk4Phone.'">'.$person1['name_last'].', '.$person1['name_first'].'</a>';
	$TP_ICON1='<img '.createComIcon($root_path,'violet_phone.gif','0','absmiddle',TRUE).'>';
}
if(isset($person2)){
	$TP_DOC2_BLOCK='<a href="javascript:popinfo(\''.$pnr2.'\',\''.$dept_nr.'\')" title="'.$LDClk4Phone.'">'.$person2['name_last'].', '.$person2['name_first'].'</a>';
	$TP_ICON2=$TP_ICON1;
}

$TP_Legend1_BLOCK.='
<nobr>&nbsp;<img '.createComIcon($root_path,'green_dot.gif','0','absmiddle',TRUE).'>&nbsp;<b>'.$LDOwnPatient.'</b></nobr><br>
<nobr>&nbsp;<img '.createComIcon($root_path,'red_dot.gif','0','absmiddle',TRUE).'> <b>'.$LDNonOwnPatient.'</b></nobr><br>';
# Create the data block
if($edit&&$rows){
$TP_Legend1_BLOCK.='<nobr>&nbsp;<img '.createComIcon($root_path,'pdata.gif','0','absmiddle',TRUE).'> <b>'.$LDAdmissionData.'</b></nobr><br>
<nobr>&nbsp;<img '.createComIcon($root_path,'open.gif','0','absmiddle',TRUE).'> <b>'.$LDOpenFile.'</b></nobr><br>
<nobr>&nbsp;<img '.createComIcon($root_path,'bubble2.gif','0','absmiddle',TRUE).'> <b>'.$LDNotesEmpty.'</b></nobr><br>
<nobr>&nbsp;<img '.createComIcon($root_path,'bubble3.gif','0','absmiddle',TRUE).'> <b>'.$LDNotes.'</b></nobr><br>
<nobr>&nbsp;<nobr><img '.createComIcon($root_path,'xchange.gif','0','absmiddle',TRUE).'> <b>'.$LDTransferPatient.'</b></nobr></nobr><br>
<nobr>&nbsp;<img '.createComIcon($root_path,'bestell.gif','0','absmiddle',TRUE).'> <b>'.$LDRelease.'</b></nobr><br>';

$TP_Legend2_BLOCK= '
&nbsp;<img '.createComIcon($root_path,'spf.gif','0','absmiddle',TRUE).'> <b>'.$LDFemale.'</b><br>
&nbsp;<img '.createComIcon($root_path,'spm.gif','0','absmiddle',TRUE).'> <b>'.$LDMale.'</b><br>';
}
# Load the quick info block template
$tp=$TP_obj->load('ambulatory/tp_clinic_quickinfo.htm');

	# Buffer orig template output
	ob_start();
		eval("echo $tp;");
		$sTemp = ob_get_contents();
	ob_end_clean();
	 # Assign to page template object
	$smarty->assign('sSubMenuBlock',$sTemp);

# Assign the submenu to the mainframe center block
*/

 $smarty->assign('sMainBlockIncludeFile','ambulatory/outpatients.tpl');

 /**
 * show Template
 */
 $smarty->display('common/mainframe.tpl');
 ?>

