<?php

require_once ('../include/config.php');
require_once ('../include/MysqliDb.php');
require_once ('../include/functions.php');
require_once ('../include/smppclass.sm.php');

header ("Content-Type: text/plain");
set_time_limit(0);
error_reporting(0);

$request  = addslashes($_SERVER['REQUEST_SCHEME'] .'://'. $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
$response = "UNDEFINED";

if (isset($_GET['chargingType'])) {
	
	$productCode	= "";
	$msisdn			= "";
	$chargingType	= "";
	$nextRenewDate	= "";
	$fee 			= "";
	$lifeCycle		= "";
	$reason			= "";
	$ProcessedTime 	= "";
	$channelId 		= "";

	if (isset($_GET['productCode'])) {		$productCode 	= urldecode($_GET['productCode']); }
	if (isset($_GET['msisdn'])) {			$msisdn 		= urldecode($_GET['msisdn']); 		}
	if (isset($_GET['chargingType'])) {		$chargingType 	= urldecode($_GET['chargingType']); }
	if (isset($_GET['nextRenewDate'])) {	$nextRenewDate 	= urldecode($_GET['nextRenewDate']); }
	if (isset($_GET['fee'])) {				$fee 			= urldecode($_GET['fee']); 		}
	if (isset($_GET['lifeCycle'])) {		$lifeCycle 		= urldecode($_GET['lifeCycle']); 	}
	if (isset($_GET['reason'])) {			$reason 		= urldecode($_GET['reason']); 		}
	if (isset($_GET['ProcessedTime'])) {	$ProcessedTime 	= urldecode($_GET['ProcessedTime']); }
	else{									$ProcessedTime 	= date('Y-m-d H:i:s');				 }
	if (isset($_GET['channelId'])) {		$channelId 		= urldecode($_GET['channelId']); 	}

	if (startsWith($msisdn,"07")) {    		$msisdn    		= "263". substr ($msisdn, 1);  }
	elseif (startsWith($msisdn,"7")) { 		$msisdn    		= "263". $msisdn;			  	 }

	$subscr_id 		= get_subscriber_id	($msisdn);
	$service_id 	= get_service_id 	($productCode);
	$apilog_id 		= save_api_log 		($_SERVER['REMOTE_ADDR'], $msisdn, 'IN', $request, $response);
	$str_code 		= strtoupper(ltrim(rtrim($chargingType)));

	$notif_dte 	= date('Y-m-d');
	//update_summary($notif_dte, 'act', $fee, 1);
	//exit;
	
	// Prepare SMPP Connection settings
	$smpp 			= new SMPPClass();
	$smpp 			->SetSender($smpp_from);

	/* bind to smpp server */
	$smpp 			->Start($smpp_host, $smpp_port, $smpp_userid, $smpp_pwd, $smpp_sys_typ);

	//echo $apilog_id . $msisdn . $fee . $ProcessedTime;
	
	// PROCESS NOTIFICATION HERE:
	switch ($str_code) {
		
		// status: 0-active, 1-grace, 2-pending, 3-suspended, 4-deactivated
		case "ACT":
			$dct_id = save_deduction 		  ($apilog_id, $msisdn, $fee, $ProcessedTime);
			//$sbs_id = get_subscription_id 	  ($subscr_id, $service_id);
			$conf 	= set_subscription_status ($subscr_id, 0 );

			/* send enquire link PDU to smpp server */
			$smpp 	->TestLink();
			/* send single message; large messages are automatically split */
			$smpp 	->Send($msisdn, $SMS_MSG['activation']);

			$smry 	= update_summary($notif_dte, 'act', $fee, 1);

			//echo $SMS_MSG['activation'];
			
	        // SEND NEWS
	        if ($ALLOW_SENDING_ON_ACTIVATION) {
	        	$smpp 	  ->TestLink();
	        	$n 	 	  = get_todays_news_content ($service_id);
	        	if (sizeof($n)>1) {
	        		$smpp ->Send($msisdn, $n['content'].$SMS_MSG['footer']);
				}
	        }
	        break;

	    case "DCT":
	    	$smpp 	->TestLink 		();
	        $smpp  	->Send 			($msisdn, $SMS_MSG['deactivation']);
	    	$smry 	= update_summary($notif_dte, 'dct', $fee, 1);

	    	// echo $SMS_MSG['deactivation'];
	    	break;

	    case "REN":
	    	$dct_id = save_deduction 		  ($apilog_id, $msisdn, $fee, $ProcessedTime);
	    	//$sbs_id = get_subscription_id 	  ($subscr_id, $service_id);
	    	$conf 	= set_subscription_status ($subscr_id, 0 );
	    	$smry 	= update_summary 		  ($notif_dte, 'ren', $fee, 1);
	        break;
	}
}
exit;
?>
