<?php
require_once ('../include/config.php');
require_once ('../include/MysqliDb.php');
require_once ('../include/functions.php');
require_once ('../include/smppclass.php');

set_time_limit 	(0);

$s1_nme = "MOBI_NEWS1";										// NEWS SERVICE DETAILS
$s1_fee = 0.12;
$sdp_status = activate_subscriber_sdp ('263779678354', $s1_nme, $s1_fee, 701 );
echo $sdp_status;

//echo date("Y-m-d");
exit;

echo count_recent_similar_messages('263773988955', 'Yes', date("Y-m-d"));
exit;

$sdp_status = deactivate_subscriber_sdp  ('263773988955', "MOBI_NEWS1", 101 );
echo $sdp_status;

exit;

/*
$str = 'http://192.168.10.11/API/?productCode=MOBI_NEWS&msisdn=773988955&chargingType=ACT&nextRenewDate=2016-12-06+15%3A41%3A04&fee=0.25&lifeCycle=A&reason=NA&%20ProcessedTime%20=2016-11-29+15%3A41%3A04&channelId=GUI';
$str = str_replace('%20', '', $str);
parse_str($str, $output);

$d = date_create_from_format('Y-m-d H:i:s',$output['ProcessedTime']);

echo "<br/>C: ". $output['chargingType'];
echo "<br/>F: ". $output['fee'];
echo "<br/>T: ". date_format($d, 'Y-m-d');

exit;
*/
$i=0;
$db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
$logs     = $db->rawQuery("SELECT mobile,url FROM tblapilog WHERE dir='IN' AND (url LIKE '%ACT%' OR url LIKE '%DCT%' OR url LIKE '%REN%') AND DATE(added)<'2017-05-17'"); 
//$logs     = $db->rawQuery("SELECT id,oa,data FROM `tblinbox` WHERE LOWER(data)='no' OR LOWER(data)='yes' ORDER BY id"); 
//$logs     = $db->rawQuery("SELECT id, url, mobile FROM tblapilog WHERE dir='IN' AND (url LIKE '%ACT%' OR url LIKE '%DCT%')"); 
//DATE(added)>='2017-05-09' AND DATE(added)<'2017-05-19'"); //>=DATE(DATE_SUB(NOW(),interval 28 day))");
if (sizeof($logs) > 0) {
	foreach ($logs as $l) {
		$str  = str_replace('%20', '', $l['url']);
		parse_str($str, $output);

		if (strtolower($output['chargingType'])=='ren' OR strtolower($output['chargingType'])=='ACT') {
			$db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
			$params   = Array($l['mobile']);
			$result   = $db->rawQuery("UPDATE tblsubscriber SET status=0 WHERE mobile=?", $params);
		}
		elseif (strtolower($output['chargingType'])=='dct') {
			$db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
			$params   = Array($l['mobile']);
			$result   = $db->rawQuery("UPDATE tblsubscriber SET status=4 WHERE mobile=?", $params);
		}
		/*
		$m 	  = $l['oa'];
		$d 	  = strtolower($l['data']);
		//$str  = str_replace('%20', '', $l['url']);
		//parse_str($str, $output);
		
		if (isset($output['ProcessedTime'])) {
			$t = date_create_from_format('Y-m-d H:i:s',$output['ProcessedTime']);
			$d = date_format($t, 'Y-m-d');
		}
		echo $d ."\n";
		if ($d == 'yes') {
			//update_summary($d, 'act', $output['fee'], 1);

			$db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
			$params   = Array($m);
			//$db       ->where ('mobile', $m);
			$result   = $db->rawQuery("UPDATE tblsubscriber SET status=0 WHERE mobile=?", $params);
		} 
		elseif ($d == 'no') {
			//update_summary($d, 'dct', $output['fee'], 1);

			$db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
			$params   = Array($m);
			//$db       ->where ('mobile', $m);
			$result   = $db->rawQuery("UPDATE tblsubscriber SET status=4 WHERE mobile=?", $params);
		}
		//elseif (strtolower($output['chargingType'])=='ren') {
		//	update_summary($d, 'ren', $output['fee'], 1);
		//}
		*/
		$i++;
	}
}
echo "Processed: ". $i;
exit;

echo $output['first'];  // value
echo $output['arr'][0]; // foo bar

//header ("Content-Type: text/plain");
//set_time_limit(0);
$i 		  = 1;
$db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
/*
WHERE DATE(signup_date) = CURDATE()

$logs     = $db->rawQuery("SELECT l.added, l.id AS lid, s.id AS sid, l.mobile, s.data, url FROM tblapilog l, tblinbox s  
							WHERE url LIKE '%DCT%' AND mobile=oa AND LOWER(s.data) = 'no' 
							ORDER BY l.id DESC, s.id DESC LIMIT 0,100"); 
*/
//WHERE url LIKE '%Type=REN%' ORDER BY id DESC LIMIT 0,1000");

$l     	  = $logs[0];

echo 	  $l['logs'];
exit;

if (sizeof($logs) > 0) {
	//echo "<table border='1'><tr><th>#</th><th>L-DTE</th><th>L-ID</th><th>S-ID</th><th>MOBILE</th><th>SMS</th><th>SDP</th></tr>";
	echo "<table border='1'><tr><th>#</th><th>L-DTE</th><th>L-ID</th><th>MOBILE</th><th>SDP</th></tr>";
	foreach ($logs as $l) {
		$_tmp 	= date_create_from_format('Y-m-d H:i:s', $l['added']);

		echo "<tr>";
		echo "<td>". $i ."</td>";
		echo "<td>". date_format($_tmp, 'm-d H:i') ."</td>";
		echo "<td>". $l['id'] ."</td>";
		//echo "<td>". $l['sid'] ."</td>";
		echo "<td>". $l['mobile'] ."</td>";
		//echo "<td>". $l['data'] ."</td>";
		echo "<td><small>". $l['url'] ."</small></td>";
		echo "</tr>";
		$i++;
	}
	echo "</table>";
}

exit;

$db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);

// Clear old/expired subscriptions
$update   = $db->rawQuery("UPDATE tblsubscription SET status=1 WHERE ended < '". date('Y-m-d H:i:s') ."'");	

// Get latest news for Main News
$svc_id   = get_service_id('MOBI_NEWS');
$params   = Array($svc_id);
$users    = $db->rawQuery("SELECT DISTINCT u.id, mobile  
							FROM tblsubscriber u, tblsubscription s  
							WHERE s.status=0 AND u.status=0 AND service_id=? AND subscriber_id=u.id AND u.id>13   
							ORDER BY id", $params);
$billed   = 0;
// Begin billing 
if (sizeof($users) > 0) {
	foreach ($users as $usr) {
		activate_subscriber_sdp($usr['mobile'],'MOBI_NEWS',0.8,$usr['id']);
		$billed++;
	}
}

echo "Billed: ". $billed ." users"; 

?>