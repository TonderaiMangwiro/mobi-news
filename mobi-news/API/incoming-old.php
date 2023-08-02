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

switch (strtoupper(ltrim(rtrim($sms)))) {					// Now process received SMS message
// -------------------------- YES || status: 0-active, 1-grace, 2-pending, 3-suspended, 4-deactivated, 5-trial --- | ---
	case "YES":
		$status = get_subscription_status($sub_id, $svc_id);
		if ($status==9){
			set_subscription_status($sub_id, 5);
			save_outbox_sms($mobile, "You have successfully subscribed to AMH MobiNews. Your trial period ends on ". add_days_friendly_date(get_subscription_date($sub_id),3) .". To receive today's news, reply with RESEND. Thank You.");
		}
		elseif ($status==0 OR $status==5){
			save_outbox_sms($mobile, $SMS_MSG['act_double']);
		}
		elseif($status==4 ) {
			if(add_days_to_date(get_subscription_date($sub_id),3)<date("Y-m-d")){
				$sdp_status = activate_subscriber_sdp($mobile, $s1_nme, $s1_fee, $x );
				$x 			= set_subscription_status($sub_id, 2);
				save_outbox_sms($mobile, $SMS_MSG['act_attempt1']);
			}
			else{
				set_subscription_status($sub_id, 5);
				save_outbox_sms($mobile, "You have successfully subscribed to AMH MobiNews. Your trial period ends on ". add_days_friendly_date(get_subscription_date($sub_id),3) .". To receive today's news, reply with RESEND. Thank You.");
			}
		}
		elseif ($status==1 OR $status==2 OR $status==3){
			$sdp_status = activate_subscriber_sdp ($mobile, $s1_nme, $s1_fee, $sub_id );
			save_outbox_sms($mobile, $SMS_MSG['act_pending']);
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

// -------------------------- NO || status: 0-active, 1-grace, 2-pending, 3-suspended, 4-deactivated --- | ---
	case "NO":
		$status = get_subscription_status($sub_id, $svc_id);
		$res 	= set_subscription_status($sub_id, 4 );
		if ($status==-1 OR $status==4) {
			save_outbox_sms($mobile, $SMS_MSG['no_account']);
		}
		elseif($status==5){
			$x 				= set_subscription_status	($sub_id, 5);
			save_outbox_sms($mobile, $SMS_MSG['deactivation']);
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
			save_outbox_sms($mobile, $SMS_MSG['dct_attempt']);
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
				save_outbox_sms($mobile, $SMS_MSG['no_account']);
			}
		}
		break;

// --------------------------- RESEND ------------------------------------------------------------------ | ---
	case "RESEND":
		if(count_recent_similar_messages($mobile, 'RESEND', date("Y-m-d")) < 3){
			if (check_subscription($sub_id, $svc_id)) {
				$news 	  = get_todays_news_content($svc_id);
				if (sizeof($news)>1) {
					save_outbox_sms($mobile, $news['content'] . $SMS_MSG['footer']);
				} 
				else {
					save_outbox_sms($mobile, $SMS_MSG['no_news']);
				}
			}
			else{
				save_outbox_sms($mobile, $SMS_MSG['no_account']);
			}
		}
		break;

// --------------------------- DEFAULT ----------------------------------------------------------------- | ---
	default:
		save_outbox_sms($mobile, $SMS_MSG['feedback']);
		break;
}

/*
$smpp = new SMPPClass();
$smpp ->SetSender($smpp_from);
$smpp ->Start($pockets_smsc, $smpp_port, $smpp_userid, $smpp_pwd, $smpp_sys_typ);
$smpp->TestLink();
$smpp->Send($mobile, $sms);
#set_outbox_sms_status($s['id'],1);
$smpp->End();
*/

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