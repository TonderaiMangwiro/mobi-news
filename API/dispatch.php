<?php
require_once ('../include/config.php');
require_once ('../include/MysqliDb.php');
require_once ('../include/functions.php');
require_once ('../include/smppclass.sm.php');
echo 'hello';
//header ("Content-Type: text/plain");
set_time_limit(0);

if (!file_exists('dispatch.lock')) {
	$ct = 'Running...';
	$fp = fopen ('dispatch.lock','wb');
		  fwrite($fp, $ct);
		  fclose($fp);
}

$db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
$svc_id   = get_service_id('MOBI_NEWS');
$params   = Array($svc_id);

 $tt = date('Y-m-d H:i:s',strtotime('-20 hours'));
	//	 echo $tt;
		

//$content  = $db->rawQuery("SELECT * FROM tblcontent WHERE svc_id=? AND status=0 ORDER BY id DESC LIMIT 0, 1", $params); //AND status=0

$content  = $db->rawQuery("SELECT * FROM tblcontent WHERE svc_id=? AND status=0 AND created >'$tt' ORDER BY id DESC LIMIT 0, 1", $params); //AND status=0
$users    = $db->rawQuery("SELECT DISTINCT mobile FROM tblsubscriber WHERE status=0 OR status=1 OR status=5 OR id<14");

// Begin sending 
if(sizeof($content) > 0){
	$smpp = new SMPPClass();
	$smpp ->SetSender($smpp_from);
	$smpp ->Start($willowv_smsc, $smpp_port, $smpp_userid, $smpp_pwd, $smpp_sys_typ);
	$sent = 0;

	foreach ($content as $sms) {
		
		$smpp->TestLink();
		$smpp->Send("263772222977", $sms['data'] . $SMS_MSG['footer']);		// Econet VAS
		$smpp->TestLink();
		$smpp->Send("263773277599", $sms['data'] . $SMS_MSG['footer']);		// Wilson Masawa
		$smpp->TestLink();		
		$smpp->Send("263773292376", $sms['data'] . $SMS_MSG['footer']);		// Silence
		$smpp->TestLink();		
		$smpp->Send("263773034446", $sms['data'] . $SMS_MSG['footer']);		// Tonderai Mangwiro
		$smpp->TestLink();
		$smpp->Send("263775031338", $sms['data'] . $SMS_MSG['footer']);		// Tinashe
		$smpp->TestLink();
		$smpp->Send("263774797966", $sms['data'] . $SMS_MSG['footer']);		// Elias
		$smpp->TestLink();
		$smpp->Send("263785101773", $sms['data'] . $SMS_MSG['footer']);		// Joseph
		$smpp->TestLink();
		$smpp->Send("263774300340", $sms['data'] . $SMS_MSG['footer']);		// Mutambara
		$smpp->TestLink();
		$smpp->Send("263774344505", $sms['data'] . $SMS_MSG['footer']);		// Siza
		$smpp->TestLink();
		
		if (sizeof($users) > 0) {
			foreach ($users as $usr) {
				$smpp->TestLink();
				$smpp->Send($usr['mobile'], $sms['data'] . $SMS_MSG['footer']);
				$sent++;
			}
		}
		$db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
		$params   = Array($sent,$sms['id']);
		$update   = $db->rawQuery("UPDATE tblcontent SET status=2, num_sent=?, to_send='". date('Y-m-d H:i:s') ."' WHERE id=?", $params);			
	}
	$smpp->End();
}

// A BIT OF HOUSE WORK: CLEAN-UP EXPIRED SUBSCRIPTIONS
// status: 0-active, 1-grace, 2-pending, 3-suspended, 4-deactivated, 5-trial
$db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
$update   = $db->rawQuery("SELECT id,mobile FROM tblsubscriber WHERE status=5 AND DATE_ADD(added,INTERVAL +3 DAY)<NOW()");
if(sizeof($update) > 0){
	foreach ($update as $s) {
		$x 		 = set_subscription_status($s['id'], 2);
		$_status = activate_subscriber_sdp($s['mobile'], $s1_nme, $s1_fee, $s['id'] );
	}
}

$filename = 'C:/Users/Administrator/Downloads/dispatch';
for ($i=0; $i < 101; $i++) { 
	$f 	  = $filename;
	if($i > 0) {
		$f= $f .' ('.$i.')';
	}
	 
	if (file_exists($f .'.php')){
		unlink($f .'.php');
	}
}

/*mysqli_close(conn);*/
mysqli_close(conn);
//$db->close();
unlink('dispatch.lock');
echo "<script>window.close();</script>";
exit;
?>