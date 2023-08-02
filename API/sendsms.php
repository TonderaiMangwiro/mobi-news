<?php
require_once ('../include/config.php');
require_once ('../include/MysqliDb.php');
require_once ('../include/functions.php');
require_once ('../include/smppclass.sm.php');

//header ("Content-Type: text/plain");
set_time_limit(0);

if (!file_exists('send.lock') AND !file_exists('dispatch.lock')) {
	$ct = 'Running...';
	$fp = fopen ('send.lock','wb');
		  fwrite($fp, $ct);
		  fclose($fp);

	$db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
	$update   = $db->rawQuery("UPDATE tblsubscription SET status=1 WHERE ended < '". date('Y-m-d H:i:s') ."'");	
	$sms  	  = $db->rawQuery("SELECT id,sms,mobile FROM tbloutbox WHERE status=0 ORDER BY id ");

	if(sizeof($sms) > 0){
		$smpp = new SMPPClass();
		$smpp ->SetSender($smpp_from);
		$smpp ->Start($pockets_smsc, $smpp_port, $smpp_userid, $smpp_pwd, $smpp_sys_typ);

		foreach ($sms as $s) {
			$smpp->TestLink();
			$smpp->Send($s['mobile'], $s['sms']); 

			set_outbox_sms_status($s['id'],1);	
		}
		$smpp->End();
	}

	$filename = 'C:/Users/Administrator/Downloads/sendsms';
	for ($i=0; $i < 101; $i++) { 
		$f 	  = $filename;
		if($i > 0) {
			$f= $f .' ('.$i.')';
		}
		 
		if (file_exists($f .'.php')){
			unlink($f .'.php');
		}
	}


$db->close();
	// Remove sending lock
	unlink('send.lock');
}
echo "<script>window.close();</script>";
exit;
?>