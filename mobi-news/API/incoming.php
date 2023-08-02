<?php
require_once 	('../include/config.php');
require_once 	('../include/MysqliDb.php');
require_once 	('../include/functions.php');
require_once 	('../include/smppclass.sm.php');

header 			("Content-Type: text/plain");
set_time_limit 	(0);
//error_reporting (0);

$sub_id = 0;
$sms 	= addslashes(ltrim(rtrim($_GET['s'])));				// @@FULLSMS@@ 
$mobile = addslashes($_GET['m']);							// @@SENDER@@ 
//$sm_dte = addslashes($_GET['d']);							// @@MSGDATE@@ YYYYMMDD format
//$sm_tme = addslashes($_GET['t']);							// @@MSGTIME@@ HHMMSS format

$sms_dt = date('Y-m-d H:i:s');								// Format SMS datetime
$s_dte  = date('Y-m-d');
$ren_dt	= date("Y-m-d H:i:s", strtotime("+ 7 day"));

															// Format incoming phone number
if (startsWith($mobile,"+263"))   $mobile = substr($mobile, 1);  
elseif (startsWith($mobile,"07")) $mobile = "263". substr($mobile, 1);  
elseif (startsWith($mobile,"7"))  $mobile = "263". $mobile;

if ($mobile!='' AND $sms!='') {
	$sms_id = save_sms_message($mobile, $sms, $sms_dt);  	// SAVE SMS in DB   
	$sub_id = get_subscriber_id($mobile);  					// SAVE Mobile in DB  
}
else {
	exit;
}
$svc_id = get_service_id($s7_nme);
$svc_id1 = get_service_id($sbrk_nme);

/* PREPARING TO SEND MESSAGE */
$smpp = new SMPPClass();
$smpp ->SetSender($smpp_from);
$smpp ->Start($pockets_smsc, $smpp_port, $smpp_userid, $smpp_pwd, $smpp_sys_typ);
$smpp->TestLink();
/***********************/

switch (strtoupper(ltrim(rtrim($sms)))) {					// Now process received SMS message
// -------------------------- YES || status: 0-active, 1-grace, 2-pending, 3-suspended, 4-deactivated, 5-trial --- | ---
	case "YES":
		$status = get_subscription_status($sub_id, $svc_id);
		if ($status==9){
			set_subscription_status($sub_id, 5);
			$message_id =  save_outbox_sms($mobile, "You have successfully subscribed to AMH MobiNews. Your trial period ends on ". add_days_friendly_date(get_subscription_date($sub_id),3) .". To receive today's news, reply with RESEND. Thank You.");

			//SENDING MESSAGE TO USER
			$smpp->Send($mobile, "You have successfully subscribed to AMH MobiNews. Your trial period ends on ". add_days_friendly_date(get_subscription_date($sub_id),3) .". To receive today's news, reply with RESEND. Thank You.");
			//UPDATING MESSAGE STATUS TO SENT
			set_outbox_sms_status($message_id,1);
		}
		elseif ($status==0 OR $status==5){
			$message_id = save_outbox_sms($mobile, $SMS_MSG['act_double']);

			//SENDING MESSAGE TO USER
			$smpp->Send($mobile, $SMS_MSG['act_double']);
			//UPDATING MESSAGE STATUS TO SENT
			set_outbox_sms_status($message_id,1);
		}
		elseif($status==4 ) {
			if(add_days_to_date(get_subscription_date($sub_id),3)<date("Y-m-d")){
				$sdp_status = activate_subscriber_sdp($mobile, $s1_nme, $s1_fee, $x );
				$x 			= set_subscription_status($sub_id, 2);
				$message_id = save_outbox_sms($mobile, $SMS_MSG['act_attempt1']);

				//SENDING MESSAGE TO USER
				$smpp->Send($mobile, $SMS_MSG['act_attempt1']);
				//UPDATING MESSAGE STATUS TO SENT
				set_outbox_sms_status($message_id,1);
			}
			else{
				set_subscription_status($sub_id, 5);
				$message_id = save_outbox_sms($mobile, "You have successfully subscribed to AMH MobiNews. Your trial period ends on ". add_days_friendly_date(get_subscription_date($sub_id),3) .". To receive today's news, reply with RESEND. Thank You.");


				//SENDING MESSAGE TO USER
				$smpp->Send($mobile, "You have successfully subscribed to AMH MobiNews. Your trial period ends on ". add_days_friendly_date(get_subscription_date($sub_id),3) .". To receive today's news, reply with RESEND. Thank You.");
				//UPDATING MESSAGE STATUS TO SENT
				set_outbox_sms_status($message_id,1);
			}
		}
		elseif ($status==1 OR $status==2 OR $status==3){
			$sdp_status = activate_subscriber_sdp ($mobile, $s1_nme, $s1_fee, $sub_id );
			$message_id = save_outbox_sms($mobile, $SMS_MSG['act_pending']);

			//SENDING MESSAGE TO USER
			$smpp->Send($mobile, $SMS_MSG['act_pending']);
			//UPDATING MESSAGE STATUS TO SENT
			set_outbox_sms_status($message_id,1);
		}
		break;

	case "YES1":
		$status = get_subscription_status($sub_id, $svc_id);
		if ($status==-1 OR $status==4 OR $status==5) {
			$sdp_status = activate_subscriber_sdp ($mobile, $s1_nme, $s1_fee, $sbs_id );
			$sbs_id 	= set_subscription_status ($sub_id, 2);
			save_outbox_sms($mobile, $SMS_MSG['act_attempt1']);
		}
		elseif ($status==0){
			save_outbox_sms($mobile, $SMS_MSG['act_double']);
		}
		elseif ($status==1 OR $status==2 OR $status==3){
			$sdp_status = activate_subscriber_sdp ($mobile, $s1_nme, $s1_fee, $sub_id );
			save_outbox_sms($mobile, $SMS_MSG['act_pending']);
		}
		break;

	case "YES7":
		$status = get_subscription_status($sub_id, $svc_id);
		if ($status==-1 OR $status==4 OR $status==5) {
			$sdp_status = activate_subscriber_sdp ($mobile, $s7_nme, $s7_fee, $sbs_id );
			$sbs_id 	= set_subscription_status ($sub_id, 2);
			save_outbox_sms($mobile, $SMS_MSG['act_attempt']);
		}
		elseif ($status==0){
			save_outbox_sms($mobile, $SMS_MSG['act_double']);
		}
		elseif ($status==1 OR $status==2 OR $status==3){
			$sdp_status = activate_subscriber_sdp ($mobile, $s7_nme, $s7_fee, $sub_id );
			save_outbox_sms($mobile, $SMS_MSG['act_pending']);
		}
		break;

	case "YES30":
		$status = get_subscription_status($sub_id, $svc_id);
		if ($status==-1 OR $status==4 OR $status==5) {
			$sbs_id 	= set_subscription_status ($sub_id, 2);
			$sdp_status = activate_subscriber_sdp ($mobile, $sM_nme, $sM_fee, $sbs_id );
			save_outbox_sms($mobile, $SMS_MSG['act_attemptM']);
		}
		elseif ($status==0){
			save_outbox_sms($mobile, $SMS_MSG['act_double']);
		}
		elseif ($status==1 OR $status==2 OR $status==3){
			$sdp_status = activate_subscriber_sdp ($mobile, $sM_nme, $sM_fee, $sub_id );
			save_outbox_sms($mobile, $SMS_MSG['act_pending']);
		}
		break;

//-----------------------------------------NEWSDAYBRK_DAILY---------------------------------------
	case "NEWSDAYBRK_DAILY":
			$status = get_subscription_status($sub_id, $svc_id1);
			if ($status==1 OR $status==4 OR $status==5) {
				$sbs_id 	= set_subscription_status ($sub_id, 2);
				$sdp_status = activate_subscriber_sdp ($mobile, $brkd_nme, $brkd_fee, $sbs_id );
				save_outbox_sms($mobile, $SMS_MSG['act_attemptBRKD']);
			}
			elseif ($status==0){
				save_outbox_sms($mobile, $SMS_MSG['act_doubleBRK']);
			}
			elseif ($status==1 OR $status==2 OR $status==3){
				$sdp_status = activate_subscriber_sdp ($mobile, $brkd_nme, $brkd_fee, $sub_id );
				save_outbox_sms($mobile, $SMS_MSG['act_pendingBRK']);
			}
			break;
		
//-----------------------------------------NEWSDAYBRK_WEEKLY---------------------------------------		
		case "NEWSDAYBRK_WEEKLY":
			$status = get_subscription_status($sub_id, $svc_id1);
			if ($status==1 OR $status==4 OR $status==5) {
				$sbs_id 	= set_subscription_status ($sub_id, 2);
				$sdp_status = activate_subscriber_sdp ($mobile, $brkw_nme, $brkw_fee, $sbs_id );
				save_outbox_sms($mobile, $SMS_MSG['act_attemptBRKW']);
			}
			elseif ($status==0){
				save_outbox_sms($mobile, $SMS_MSG['act_doubleBRK']);
			}
			elseif ($status==1 OR $status==2 OR $status==3){
				$sdp_status = activate_subscriber_sdp ($mobile, $brkw_nme, $brkw_fee, $sub_id );
				save_outbox_sms($mobile, $SMS_MSG['act_pendingBRK']);
			}
			break;
//-----------------------------------------NEWSDAYBRK_MONTHLY---------------------------------------			
	case "NEWSDAYBRK_MONTHLY":
			$status = get_subscription_status($sub_id, $svc_id1);
			if ($status==1 OR $status==4 OR $status==5) {
				$sbs_id 	= set_subscription_status ($sub_id, 2);
				$sdp_status = activate_subscriber_sdp ($mobile, $brkm_nme, $brkm_fee, $sbs_id );
				save_outbox_sms($mobile, $SMS_MSG['act_attemptBRKM']);
			}
			elseif ($status==0){
				save_outbox_sms($mobile, $SMS_MSG['act_doubleBRK']);
			}
			elseif ($status==1 OR $status==2 OR $status==3){
				$sdp_status = activate_subscriber_sdp ($mobile, $brkm_nme, $brkm_fee, $sub_id );
				save_outbox_sms($mobile, $SMS_MSG['act_pendingBRK']);
			}
			break;




// -------------------------- NO || status: 0-active, 1-grace, 2-pending, 3-suspended, 4-deactivated --- | ---
	case "NO":
		$status = get_subscription_status($sub_id, $svc_id);
		$res 	= set_subscription_status($sub_id, 4 );
		if ($status==-1 OR $status==4) {
			$message_id = save_outbox_sms($mobile, $SMS_MSG['no_account']);

			//SENDING MESSAGE TO USER
			$smpp->Send($mobile, $SMS_MSG['no_account']);
			//UPDATING MESSAGE STATUS TO SENT
			set_outbox_sms_status($message_id,1);
		}
		elseif($status==5){
			$x 				= set_subscription_status	($sub_id, 5);
			$message_id = save_outbox_sms($mobile, $SMS_MSG['deactivation']);

			//SENDING MESSAGE TO USER
			$smpp->Send($mobile, $SMS_MSG['deactivation']);
			//UPDATING MESSAGE STATUS TO SENT
			set_outbox_sms_status($message_id,1);
		}
		else{
			$act_msg 		= get_act_message 			($mobile);
			$x 				= set_subscription_status	($sub_id, 4);
			
			if ($act_msg 	== 1) {
				$sdp_status = deactivate_subscriber_sdp ($mobile, $s1_nme, $sub_id );
				$sdp_status = deactivate_subscriber_sdp ($mobile, $s7_nme, $sub_id ); // Old subscribers
			} 
			elseif ($act_msg == 7) {
				$sdp_status  = deactivate_subscriber_sdp ($mobile, $s7_nme, $sub_id );
			}
			elseif ($act_msg == 30) {
				$sdp_status  = deactivate_subscriber_sdp ($mobile, $sM_nme, $sub_id );
			}
			$message_id = save_outbox_sms($mobile, $SMS_MSG['dct_attempt']);

			//SENDING MESSAGE TO USER
			$smpp->Send($mobile, $SMS_MSG['dct_attempt']);
			//UPDATING MESSAGE STATUS TO SENT
			set_outbox_sms_status($message_id,1);
		}

		break;
// --------------------------- MORE -------------------------------------------------------------------- | ---
	case "MORE":
		if(count_recent_similar_messages($mobile, 'MORE', date("Y-m-d")) < 3){
			if (check_subscription($sub_id, $svc_id)) {
				/*$news 		= get_todays_more_news($svc_id);
				if (sizeof($news)>1) {
					save_outbox_sms($mobile, $news['content'] . $SMS_MSG['footer']);
				} 
				else {
				*/
					save_outbox_sms($mobile, $SMS_MSG['additional']);
				//}
			}
			else{
				$message_id = save_outbox_sms($mobile, $SMS_MSG['no_account']);

				//SENDING MESSAGE TO USER
				$smpp->Send($mobile, $SMS_MSG['no_account']);
				//UPDATING MESSAGE STATUS TO SENT
				set_outbox_sms_status($message_id,1);
			}
		}
		break;

// --------------------------- RESEND ------------------------------------------------------------------ | ---
	case "RESEND":
		if(count_recent_similar_messages($mobile, 'RESEND', date("Y-m-d")) < 3){
			if (check_subscription($sub_id, $svc_id)) {
				$news 	  = get_todays_news_content($svc_id);
				if (sizeof($news)>1) {
					$message_id = save_outbox_sms($mobile, $news['content'] . $SMS_MSG['footer']);

					//SENDING MESSAGE TO USER
					$smpp->Send($mobile, $news['content'] . $SMS_MSG['footer']);
					//UPDATING MESSAGE STATUS TO SENT
					set_outbox_sms_status($message_id,1);
				} 
				else {
					$message_id = save_outbox_sms($mobile, $SMS_MSG['no_news']);

					//SENDING MESSAGE TO USER
					$smpp->Send($mobile, $SMS_MSG['no_news']);
					//UPDATING MESSAGE STATUS TO SENT
					set_outbox_sms_status($message_id,1);
				}
			}
			else{
				$message_id = save_outbox_sms($mobile, $SMS_MSG['no_account']);

				//SENDING MESSAGE TO USER
				$smpp->Send($mobile, $SMS_MSG['no_account']);
				//UPDATING MESSAGE STATUS TO SENT
				set_outbox_sms_status($message_id,1);
			}
		}
		break;

// --------------------------- DEFAULT ----------------------------------------------------------------- | ---
	default:
		$message_id = save_outbox_sms($mobile, $SMS_MSG['feedback']);

		//SENDING MESSAGE TO USER
		$smpp->Send($mobile, $SMS_MSG['feedback']);
		//UPDATING MESSAGE STATUS TO SENT
		set_outbox_sms_status($message_id,1);
		break;
}



/*  CLOSING THE MESSAGE SEDING OBJECT */
$smpp->End();
/*****************************/

// A BIT OF HOUSE WORK: CLEAN-UP EXPIRED SUBSCRIPTIONS
// status: 0-active, 1-grace, 2-pending, 3-suspended, 4-deactivated, 5-trial
$db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
$update   = $db->rawQuery("SELECT id,mobile FROM tblsubscriber WHERE status=5 AND DATE_ADD(added,INTERVAL +3 DAY)<NOW() LIMIT 0,200");
if(sizeof($update) > 0){
	foreach ($update as $s) {
		$x 		 = set_subscription_status($s['id'], 2);
		$_status = activate_subscriber_sdp($s['mobile'], $s1_nme, $s1_fee, $s['id'] );
	}
}
exit;

?>