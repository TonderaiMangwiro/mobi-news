<?PHP
session_start();

date_default_timezone_set('Africa/Harare');

/*
********************************************
 	Website URL Settings
********************************************
*/
$ROOT      		= "/amh.ncp";
$pg_adm 		= array('home','stats','accounts','pwd','tools');
$pg_mgr 		= array('home', 'manage', 'sms', 'mms', 'wap', 'subscriptions','trace','inbound','stats','services','svc','users','usr','tools');
$pg_usr 		= array('home', 'manage', 'sms', 'mms', 'wap', 'subscriptions','trace','inbound','stats','services','svc','users','usr','tools');


/*
********************************************
 	SMS MESSAGE Settings
********************************************
*/
$ALLOW_SENDING_ON_ACTIVATION = TRUE;
$SMS_MSG		= array(
	'footer'		=> "\n---\nMobiNews costs $4.12 per day. If you have not received the day's news, reply with RESEND and for additional content reply with MORE.",
	'act_trial'		=> "You have successfully subscribed to AMH MobiNews. Your trial period ends on ". date("Y-m-d", strtotime("+ 3 day")) .". To receive today's news, reply with RESEND. Thank You.",
	'act_attempt1'	=> "Your subscription request for AMH MobiNews at $4.12 per day has been submitted. Thank You.",
	'act_attempt7'	=> "Your subscription request for AMH MobiNews at $26.75 per week has been submitted. Thank You.",
	'act_attemptM'	=> "Your subscription request for AMH MobiNews at $98.78 per month has been submitted. Thank You.",
	'act_attemptBRKD' => "Your subscription request for AMH Breaking News at $2.06 per Day has been submitted. Thank you.",
	'act_attemptBRKW' => "Your subscription request for AMH Breaking News at $10.29 per Week has been submitted. Thank you.",
	'act_attemptBRKM' => "Your subscription request for AMH Breaking News at $51.45 per Month has been submitted. Thank you.",
	'dct_attempt'	=> "Your unsubscription request for AMH MobiNews has been submitted. Thank You.",
	'act_double'	=> "You are already subscribed for AMH MobiNews. To receive today's news, reply with RESEND. Thank You.",
	'act_doubleBRK'	=> "You are already subscribed for AMH Breaking News. To receive today's news, reply with RESEND. Thank You.",
	'act_pending'	=> "Your subscription request for AMH MobiNews has already been submitted and is under review. Thank You.",
	'act_pendingBRK' => "Your subscription request for AMH Breaking News has already been submitted and is under review. Thank You.",
	'activation'	=> "You have successfully subscribed to AMH MobiNews Service. To receive today's news, reply with RESEND. Thank You.",
	'deactivation'	=> "You have successfully unsubscribed from AMH MobiNews Service. Thank You.",
	'feedback'		=> "Your message has been saved for quality control purposes. To subscribe, reply with YES and to unsubscribe reply with NO. If you have not received today's news, reply with RESEND. MobiNews costs $4.12 per day. Thank you.",
	'no_account'	=> "You do not have an active AMH MobiNews Service subscription. To subscribe, please reply to this message with YES. Thank you.",
	'no_news'		=> "Sorry, no news content is available for publication at present. Thank You.",
	'additional'	=> "Sorry, no additional news content is available at present. If you have not received today's news, reply with RESEND. Thank You."
	);


/*
********************************************
 	ECONET SDP API ACCESS
********************************************
*/
$act_url 		= 'http://172.27.100.11:9080/BL/services/SDP';
$dct_url 		= 'http://172.27.100.11:9080/BL/services/SDP';
$econet_oa		= '35569';
$econet_us		= 'amhmns';
$econet_pw		= 'amhmns';

/*
********************************************
 	NEWS SERVICE DETAILS
********************************************
*/
$sbrk_nme = "B_NEWS";
$s1_nme = "MOBI_NEWS1";										 
$s1_fee = 4.12;
$s7_nme = "MOBI_NEWS";
$s7_fee = 26.75;
$sM_nme = "MOBI_NEWS30";
$sM_fee = 98.78;
$brkd_nme = "NEWSDAYBRK_DAILY";
$brkd_fee = 2.06;
$brkw_nme = "NEWSDAYBRK_WEEKLY";
$brkw_fee = 10.29;
$brkm_nme = "NEWSDAYBRK_MONTHLY";
$brkm_fee = 51.45;


/*
********************************************
 	ECONET SMSC ACCESS
********************************************
*/
$smpp_host 	  	= "172.19.207.105";
$pockets_smsc  	= "172.22.198.105"; //  $pockets_smsc  	= "172.24.110.102";
$willowv_smsc  	= "172.19.207.105"; //$willowv_smsc  	= "172.27.130.3";
$smpp_port 	  	= 5016;
$smpp_userid  	= "AMH";
$smpp_pwd 	  	= "#amh123";
$smpp_sys_typ 	= "SMPP";
$smpp_from 	  	= "26335569";

/*
********************************************
 	NOWSMS SMSC ACCESS
********************************************
*/
$nows_host 	  	= "192.168.10.11";
$nows_port 	  	= 5016;
$nows_userid  	= "admin";
$nows_pwd 	  	= "admin";

/*
********************************************
 	Database Connection Settings
********************************************
*/
#LOCAL SETTINGS
$ncp_host		= 'localhost';
$ncp_usr		= 'root';
$ncp_pwd 		= '';
$ncp_db 		= 'amhsms';

/*
********************************************
 	Connect To Database Server
********************************************
*/
$conn 			= mysql_connect   ($ncp_host, $ncp_usr, $ncp_pwd) or die("Failed connecting to database server."); 	// DEBUG: . mysql_error());
				  mysql_select_db ($ncp_db) 				  	  or die("Failed selecting database."); 	 		// DEBUG: . mysql_error());

?>
