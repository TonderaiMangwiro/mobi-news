<?php

function get_connection(){
	global $ncp_host, $ncp_usr, $ncp_pwd, $ncp_db;
		
	try {
		$dbh = new PDO("mysql:host=$ncp_host;dbname=$ncp_db", $ncp_usr, $ncp_pwd);
	} catch (PDOException $e) {
		exit("Connection to database failed");
	}

	return $dbh;
}

	function send_breaking_news_now(){
		//ENSURING THIS FUNCTION WONT BE STOPED
		set_time_limit(0);
		
		//GETTING THE BREAKING NEWS
		$statement = get_connection()->prepare("SELECT `id`, `data`, `created` FROM tblcontent WHERE `status` = 0 AND `svc_id` = 4 LIMIT 1");
		$statement->execute();

		if(!isset($statement->errorInfo()[2]) && $statement->rowCount() == 1){
			$temp = $statement->fetch();
			$message_id = $temp['id'];
			$message = $temp['data'];
			$created = $temp['created'];
			
			if(!empty($message_id) && !empty($message)){
				//GETTING SUSBSCRIBERS FOR BREAKING NEWS
				$statement = get_connection()->prepare("SELECT DISTINCT tblsubscriber.mobile FROM tblsubscriber INNER JOIN tblsubscription ON tblsubscriber.id = tblsubscription.subscriber_id AND tblsubscription.service_id = 4 AND (tblsubscriber.status = 0 OR tblsubscriber.status=1 OR tblsubscriber.status=5 OR tblsubscriber.id <14) AND 0 OR (tblsubscriber.id = 4 OR tblsubscriber.id = 1)"); 
				$statement->execute();
				if(!isset($statement->errorInfo()[2]) && $statement->rowCount() == 2){
					$subscribers = $statement->fetchAll(PDO::FETCH_ASSOC);
					if(!empty($subscribers)){
						//OPENDING THE CONNECTION TO SEND MESSAGES
						global $smpp_from, $pockets_smsc, $smpp_port, $smpp_userid, $smpp_pwd, $smpp_sys_typ;
						$smpp = new SMPPClass();
						$smpp ->SetSender($smpp_from);
						$smpp ->Start($pockets_smsc, $smpp_port, $smpp_userid, $smpp_pwd, $smpp_sys_typ);

						foreach ($subscribers as $s) {
							$smpp->TestLink();
							$smpp->Send($s['mobile'], $message);
						}
						$smpp->End();
						
						//NOW UPDATING TO SHOW THAT THE BREAKING NEWS HAS BEEN SENT
						$statement = get_connection()->prepare("UPDATE tblcontent SET status = 2, num_sent = ?, to_send = ? WHERE id = ?");
						$statement->execute([count($subscribers), $message_id, $created]);
					}
				}
			}
		}
	}

function count_recent_similar_messages($mobile, $sms, $date){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;
  
  $db     = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  $params = Array($mobile,strtolower($sms),$date);
  $sbs    = $db->rawQuery("SELECT id,data FROM tblinbox WHERE oa=? AND LOWER(data)=? AND DATE(added)=? ", $params);
  
  return sizeof($sbs);
}

function get_act_message($mobile){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;

  $return = 7;
  
  $db     = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  $params = Array($mobile);
  $sbs    = $db->rawQuery("SELECT id,data FROM tblinbox WHERE oa=? AND LOWER(data) LIKE '%yes%' ORDER BY id DESC LIMIT 0,1", $params);
  
  if(!empty($sbs)){
    $r        = $sbs[0];
    if (strtoupper($r['data']) == 'YES' OR strtoupper($r['data']) == 'YES1') {
      $return = 1;
    }
    elseif (strtoupper($r['data']) == 'YES30') {
      $return = 30;
    }
  }

  return $return;  
}

function save_sms_message($mobile, $sms, $sms_date_time){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;
  
  $db   = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  $data = Array ( "id"     => 0,
                  "oa"     => $mobile,
                  "data"   => $sms,
                  "added"  => $sms_date_time
                );
  $id   = $db->insert ('tblinbox', $data);

  return $id;
}

function save_outbox_sms($mobile, $sms){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;
  
  $db   = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  $data = Array ( "id"     => 0,
                  "mobile" => $mobile,
                  "sms"    => $sms,
                  "status" => 0,
                  "added"  => date('Y-m-d H:i:s')
                );
  $id   = $db->insert ('tbloutbox', $data);

  return $id;
}

function set_outbox_sms_status($sms_id, $new_status){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;

  $db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  $params   = Array($new_status,$sms_id);
  $result   = $db->rawQuery("UPDATE tbloutbox SET status=? WHERE id=?", $params);

  return $result;
}

function get_subscriber_id($msisdn){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;
  
  $db          = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  $params      = Array($msisdn);
  $subscribers = $db->rawQuery("SELECT id FROM tblsubscriber WHERE mobile = ?", $params);

  if(sizeof($subscribers) == 0){
    $data      = Array ( "name"   => "n/a",
                         "status" => 9,
                         "mobile" => $msisdn,
                         "added"  => Date('Y-m-d H:i:s'));
    $id        = $db->insert ('tblsubscriber', $data);
    if($id)
      return $id;
  }
  else{
    $sub       = $subscribers[0];
    return       $sub['id'];
  }
}

// status: 0-active, 1-grace, 2-pending, 3-suspended, 4-deactivated, 5-trial
function save_subscription($subscr_id, $service_id, $processed_dt, $renew_dt, $fee){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;

  //$_id     = get_subscription_id($subscr_id, $service_id);
  if ($subscr_id > 0) {
    set_subscription_status($subscr_id, 2);
    return $subscr_id;
  } 
  else {
    $db     = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
    $data   = Array ( "subscriber_id" => $subscr_id,
                      "service_id"    => $service_id,
                      "status"        => 2,
                      "fee"           => $fee,
                      "started"       => $processed_dt,
                      "ended"         => $renew_dt);
    $_id    = $db->insert ('tblsubscription', $data);
    return $_id;
  }
  
}

function save_trial($subscr_id, $service_id, $processed_dt, $renew_dt, $fee){
  set_subscription_status($subscr_id, 5);
  return $subscr_id;
}

function get_subscription_id($subscr_id, $service_id){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;

  $db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  $db       ->where ('subscriber_id', $subscr_id);
  $db       ->where ('service_id',    $service_id);
  $result   = $db->get('tblsubscription', 1, 'id');
  
  if(sizeof($result) > 0){
    $s      = $result[0];
    return    $s['id'];
  }
  else{
    return 0;
  }
}

// status: 0-active, 1-grace, 2-pending, 3-suspended, 4-deactivated
function set_subscription_status($subscr_id, $new_status){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;

  $db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  $params   = Array($new_status,$subscr_id);
  //$db       ->where ('id', );
  $result   = $db->rawQuery("UPDATE tblsubscriber SET status=? WHERE id=?", $params);

  return $result;
}

function get_subscription_status($subscr_id, $service_id){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;

  $db     = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  //$db   ->where ('ended',         Array ('>' => Date('Y-m-d H:i:s')));
  //$db     ->where ('subscriber_id', $subscr_id);
  //$db     ->where ('service_id',    $service_id);

  //$result = $db->get('tblsubscription', 1, 'status');

  $params   = Array($subscr_id);
  $result = $db->rawQuery("SELECT status FROM tblsubscriber WHERE id=?", $params);

  if(sizeof($result) > 0){
    $s    = $result[0];
    return $s['status'];
  }
  else{
    return -1;
  }
}

function get_subscription_date($subscr_id){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;

  $ret_val = 0;

  $db      = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);

  $params  = Array($subscr_id);
  $result  = $db->rawQuery("SELECT DATE(added) AS _added FROM tblsubscriber WHERE id=?", $params);
  $s       = $result[0];

  $ret_val = $s['_added'];
  return $ret_val;
}

function add_days_to_date($date,$days){
  $date = strtotime("+".$days." days", strtotime($date));
  return  date("Y-m-d", $date);
}

function add_days_friendly_date($date,$days){
  $date = strtotime("+".$days." days", strtotime($date));
  return  date("d M Y", $date);
}

// status: 0-active, 1-grace, 2-pending, 3-suspended, 4-deactivated, 5-trial
function check_subscription($subscr_id,$service_id){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;

  $db     = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  /*$db     ->where ('status',        0 );
  $db     ->where ('service_id',    $service_id );
  $db     ->where ('subscriber_id', $subscr_id  );

  $sbs    = $db->get('tblsubscription', 1, 'id');*/
  $params = Array($subscr_id);
  $sbs    = $db->rawQuery("SELECT id FROM tblsubscriber WHERE (status=0 OR status=1 OR status=5) AND id=? ",$params);

  //$params   = Array($subscr_id,$service_id);
  //$sbs      = $db->rawQuery("SELECT id FROM tblsubscription WHERE (status=0 OR status=2) AND subscriber_id=? AND service_id=?",$params); 

  if(sizeof($sbs) > 0){
    return TRUE;
  }
  else{
    return FALSE;
  }  
}

function update_summary($date, $param, $fee, $count){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;

  $db          = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  $params      = Array($count, $fee, $date);
  $sql         = "UPDATE tblsummary SET ";
  
  if (strtolower($param)=='act')      $sql .= "act=act+?, fee=fee+? WHERE added=?";
  elseif (strtolower($param)=='ren')  $sql .= "ren=ren+?, fee=fee+? WHERE added=?";
  elseif (strtolower($param)=='dct')  $sql .= "dct=dct+?, fee=fee+? WHERE added=?";
  
  $result      = $db->rawQuery($sql, $params);

  if($db->count>0){
    return $result;
  } 
  else {
    $data      = Array ( $param   => $count,
                         "fee"    => $fee,
                         "added"  => DATE($date) );
    $id        = $db->insert ('tblsummary', $data);
    if($id)
      return $id;
  }
}

function get_service_id($svc_code){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;

  $db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  //$db       ->where ('svc_code', $svc_code);
  $params   = Array($svc_code);
  $services = $db->rawQuery("SELECT id FROM tblnews_svc WHERE svc_code=?",$params);

  if(sizeof($services) > 0){
    $svc    = $services[0];
    return    $svc['id'];
  }
  else{
    return 0;
  }
}

function save_deduction($api_log_id, $mobile, $fee, $datetime){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;
  
  $db   = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  $data = Array ( "id"         => 0,
                  "apilog_id"  => $api_log_id,
                  "subscriber" => $mobile,
                  "fee"        => $fee,
                  "added"      => $datetime
                );
  $id   = $db->insert ('tbldeduct', $data);

  return $id;
}

function reverse_deduction($api_log_id, $mobile, $fee, $datetime){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;
  
  $db   = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  $data = Array ( "id"         => 0,
                  "apilog_id"  => $api_log_id,
                  "subscriber" => $mobile,
                  "fee"        => $fee,
                  "added"      => $datetime
                );
  $id   = $db->insert ('tbldeduct', $data);

  return $id;
}

function activate_subscriber_sdp($mobile,$product,$amount,$t_id){
  global $act_url;
  global $econet_oa;
  global $econet_us;
  global $econet_pw;

  $xml = '<?xml version="1.0" encoding="UTF-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://ws.apache.org/axis2/com/sixdee/imp/axis2/dto/Request/xsd/" xmlns:xsd1="http://dto.axis2.imp.sixdee.com/xsd"><soap:Body><xsd:ServiceExecutor><xsd:request><xsd1:billingText>msisdn='.$mobile.'|productCode='.$product.'|channelID=1|chargeAmount='.$amount.'|clientTransId='.$t_id.'|cpID=303|username='.$econet_us.'|password='.$econet_pw.'|language=0|shortCode='.$econet_oa.'</xsd1:billingText><xsd1:operationCode>ACTIVATE</xsd1:operationCode></xsd:request></xsd:ServiceExecutor></soap:Body></soap:Envelope>';

  $headers = array(
    "Content-type: text/xml",
    "Content-length: ". strlen($xml),
    "Connection: Keep-Alive",
    "User-Agent: Java/1.6.0_21",
    "Accept: */*",
    "POST /BL/services/SDP?wsdl HTTP/1.1",
    "Host: 192.168.5.82:80"
  );

  $ch   = curl_init(); 

  curl_setopt($ch, CURLOPT_URL,             $act_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER,  1);
  curl_setopt($ch, CURLOPT_TIMEOUT,         300);
  curl_setopt($ch, CURLOPT_POST,            true);
  curl_setopt($ch, CURLOPT_POSTFIELDS,      $xml);
  curl_setopt($ch, CURLOPT_HTTPHEADER,      $headers);

  $data   = curl_exec($ch);
  $d_doc  = new DOMDocument();
  $d_doc  ->loadXML($data);

  return  $d_doc ->getElementsByTagName('statusCode')->item(0)->nodeValue;
}

function deactivate_subscriber_sdp($mobile,$product,$t_id){
  global $dct_url;
  global $econet_oa;
  global $econet_us;
  global $econet_pw;

  $xml = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://ws.apache.org/axis2/com/sixdee/imp/axis2/dto/Request/xsd/" xmlns:xsd1="http://dto.axis2.imp.sixdee.com/xsd"><soap:Body><xsd:ServiceExecutor><xsd:request><xsd1:billingText>msisdn='.$mobile.'|productCode='.$product.'|channelID=1|chargeAmount=0|clientTransId='.$t_id.'|cpID=303|username='.$econet_us.'|password='.$econet_pw.'|language=0|shortCode='.$econet_oa.'</xsd1:billingText><xsd1:operationCode>DEACTIVATE</xsd1:operationCode></xsd:request></xsd:ServiceExecutor></soap:Body></soap:Envelope>';

  $headers = array(
    "Content-type: text/xml",
    "Content-length: ". strlen($xml),
    "Connection: Keep-Alive",
    "User-Agent: Java/1.6.0_21",
    "Accept: */*",
    "POST /BL/services/SDP?wsdl HTTP/1.1",
    "Host: 192.168.5.82:80"
  );

  $ch   = curl_init(); 

  curl_setopt($ch, CURLOPT_URL,             $dct_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER,  1);
  curl_setopt($ch, CURLOPT_TIMEOUT,         300);
  curl_setopt($ch, CURLOPT_POST,            true);
  curl_setopt($ch, CURLOPT_POSTFIELDS,      $xml);
  curl_setopt($ch, CURLOPT_HTTPHEADER,      $headers);

  $data   = curl_exec($ch);
  $d_doc  = new DOMDocument();
  $d_doc  ->loadXML($data);

  return  $d_doc ->getElementsByTagName('statusCode')->item(0)->nodeValue;
}

function reverse_bill_subscriber_sdp($mobile,$product,$t_id){
  global $dct_url;
  global $econet_oa;
  global $econet_us;
  global $econet_pw;

  $xml = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://ws.apache.org/axis2/com/sixdee/imp/axis2/dto/Request/xsd/" xmlns:xsd1="http://dto.axis2.imp.sixdee.com/xsd"><soap:Body><xsd:ServiceExecutor><xsd:request><xsd1:billingText>msisdn='.$mobile.'|productCode='.$product.'|channelID=1|chargeAmount=0|clientTransId='.$t_id.'|cpID=303|username='.$econet_us.'|password='.$econet_pw.'|language=0|shortCode='.$econet_oa.'</xsd1:billingText><xsd1:operationCode>DEACTIVATE</xsd1:operationCode></xsd:request></xsd:ServiceExecutor></soap:Body></soap:Envelope>';

  $headers = array(
    "Content-type: text/xml",
    "Content-length: ". strlen($xml),
    "Connection: Keep-Alive",
    "User-Agent: Java/1.6.0_21",
    "Accept: */*",
    "POST /BL/services/SDP?wsdl HTTP/1.1",
    "Host: 192.168.5.82:80"
  );

  $ch   = curl_init(); 

  curl_setopt($ch, CURLOPT_URL,             $dct_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER,  1);
  curl_setopt($ch, CURLOPT_TIMEOUT,         300);
  curl_setopt($ch, CURLOPT_POST,            true);
  curl_setopt($ch, CURLOPT_POSTFIELDS,      $xml);
  curl_setopt($ch, CURLOPT_HTTPHEADER,      $headers);

  $data   = curl_exec($ch);
  $d_doc  = new DOMDocument();
  $d_doc  ->loadXML($data);

  return  $d_doc ->getElementsByTagName('statusCode')->item(0)->nodeValue;
}

function get_todays_news_content($svc_id){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;

  $db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  $params   = Array($svc_id);
  $content  = $db->rawQuery("SELECT id, data FROM tblcontent WHERE svc_id=? AND DATE(to_send)=CURDATE() ORDER BY id DESC LIMIT 0,1", $params);

  if(sizeof($content) > 0){
    $news   = $content[0];
    return array('id'=>$news['id'], 'content'=>$news['data']);
  }
  else{
    return array('content'=>'NO-NEWS');
  }
}

function get_todays_more_news($svc_id){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;

  $db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  $params   = Array($svc_id);
  $content  = $db->rawQuery("SELECT id, data_2 FROM tblcontent WHERE svc_id=? AND DATE(to_send)=CURDATE() ORDER BY id DESC LIMIT 0,1", $params);

  if(sizeof($content) > 0){
    $news   = $content[0];
    return array('id'=>$news['id'], 'content'=>$news['data_2']);
  }
  else{
    return array('content'=>'NO-NEWS');
  }
}

function send_SMS_nowsms ($host, $port, $username, $password, $phoneNoRecip, $msgText) {
 
/* Parameters:
   $host - IP address or host name of the NowSMS server
   $port - "Port number for the web interface" of the NowSMS Server
   $username - "SMS Users" account on the NowSMS server
   $password - Password defined for the "SMS Users" account on the NowSMS Server
   $phoneNoRecip - One or more phone numbers (comma delimited) to receive the text message
   $msgText - Text of the message
*/
 
   $fp = fsockopen($host, $port, $errno, $errstr);
   if (!$fp) {
      echo "errno: $errno \n";
      echo "errstr: $errstr\n";
      return $result;
   }
   fwrite($fp, "GET /?Phone=" . rawurlencode($phoneNoRecip) . "&Text=" .
    rawurlencode($msgText) . " HTTP/1.0\n");
   if ($username != "") {
      $auth = $username . ":" . $password;
      $auth = base64_encode($auth);
      fwrite($fp, "Authorization: Basic " . $auth . "\n");
   }
 
   fwrite($fp, "\n");
   $res = "";
   while(!feof($fp)) {
      $res .= fread($fp,1);
   }
   fclose($fp);
 
   return $res;
 
}















function ping($host,$port=80,$timeout=6){
  error_reporting(0);

  if (parse_url($host, PHP_URL_PORT) != '') {
    $port = parse_url($host, PHP_URL_PORT);
  }
  $host = parse_url($host, PHP_URL_HOST);

  $fsock = fsockopen($host, $port, $errno, $errstr, $timeout);
  if($fsock ){
    return "UP";
  }
  else{
    return "DOWN";
  }
}

function get_monthly_total($date){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;
  
  $db   = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  $data = Array ( "id"       => 0,
                  "uid"      => $usr_id,
                  "ip"       => addslashes($_SERVER['REMOTE_ADDR']),
                  "acn_code" => $action_code,
                  "action"   => $action,
                  "added"    => Date('Y-m-d H:i:s')
                );
  /*

  SELECT COUNT(id) AS units, SUM(fee) AS earnings FROM `tbldeduct` WHERE  MONTH(added) = 1 AND YEAR(added) = 2017
  
SELECT SUM(fee) FROM Member
WHERE DATEPART(m, date_created) = DATEPART(m, DATEADD(m, -1, getdate())) AND DATEPART(yyyy, date_created) = DATEPART(yyyy, DATEADD(m, -1, getdate()))
    */
  $params = Array($i);
  $res    = $db->rawQuery("SELECT id, title, IFNULL((SELECT SUM(fee) FROM tblsubscription WHERE service_id=n.id AND DATE(started)=date(date_sub(now(),interval ? day))),0) AS total FROM tblnews_svc n",$params);
  $id   = $db->insert ('tbl_log', $data);
}





function save_activity($usr_id, $action_code, $action){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;
  
  $db   = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  $data = Array ( "id"       => 0,
                  "uid"      => $usr_id,
                  "ip"       => addslashes($_SERVER['REMOTE_ADDR']),
                  "acn_code" => $action_code,
                  "action"   => $action,
                  "added"    => Date('Y-m-d H:i:s')
                );
  $id   = $db->insert ('tbl_log', $data);
}

function save_api_log($ip, $mobile, $direction, $url, $response){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;
  
  $db   = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  $data = Array ( "id"        => 0,
                  "ip"        => $ip,
                  "mobile"    => $mobile,
                  "dir" => $direction,
                  "url"       => $url,
                  "response"  => $response,
                  "added"     => Date('Y-m-d H:i:s')
                );
  $id   = $db->insert ('tblapilog', $data);
  return $id;
}













function save_deduction_2($subscr_id, $apilog_id, $ProcessedTime,$fee){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;

  $db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  $data     = Array ( "apilog_id"     => $apilog_id,
                      "subscriber_id" => $subscr_id,
                      "fee"           => $fee,
                      "added"         => $ProcessedTime);
  $id       = $db->insert ('tbldeduct', $data);
  if($id)
    return $id;
}

function deactivate_subscription($subscr_id, $service_id, $ProcessedTime){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;

  $db          = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  $params      = Array($subscr_id, $service_id);
  $db->rawQuery("UPDATE tblsubscription SET status=1 WHERE subscriber_id=? AND service_id=?", $params);
}

function update_subscription($subscr_id, $service_id, $ProcessedTime, $nextRenewDate, $fee){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;

  $db          = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  $params      = Array($fee,$ProcessedTime,$nextRenewDate,$subscr_id, $service_id);
  $db->rawQuery("UPDATE tblsubscription SET status=0, fee=?, started=?, ended=? WHERE subscriber_id=? AND service_id=?", $params);
}



function get_last_logon($usr_id){
  $result = mysql_query("SELECT added FROM tbl_log WHERE uid=$usr_id AND acn_code=0 ORDER BY id DESC LIMIT 0,1")
                 or die('Error fetching activity.'); // DEBUG: . mysql_error());
  $row    = mysql_fetch_assoc($result);

  return $row['added'];
}

function get_news_service_title($_id){
  $result = mysql_query("SELECT title FROM tblnews_svc WHERE id=$_id")
                 or die('Error fetching news service title.'); // DEBUG: . mysql_error());
  $row    = mysql_fetch_assoc($result);
  return $row['title'];
}

function save_feedback($oa,$content){
  global $ncp_host;
  global $ncp_usr;
  global $ncp_pwd;
  global $ncp_db;

  $db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  $data     = Array ( "oa"    => $oa,
                      "data"  => $content,
                      "added" => Date('Y-m-d H:i:s'));
  $id       = $db->insert ('tblsmsin', $data);
  if($id)
    return $id;
}

function send_SMS_econet($sms_id,$sms,$da){
  global $mt_primary;
  global $mt_secondary;
  global $econet_smsc1;
  global $econet_smsc2;
  global $econet_oa;
  global $econet_us;
  global $econet_pw;

  if (startsWith($da,"07")) {    $da    = "263". substr ($da, 1); }
  elseif (startsWith($da,"7")) { $da    = "263". $da;             }

  $xml = '<Request><requestId>'.$sms_id.'</requestId><senderSystem>SM</senderSystem><transDateTime>'. Date("ddmYHis",time()) .'</transDateTime><transactionId>'.$sms_id . time().'</transactionId><dcs>1</dcs><smscId>'.$econet_smsc1.'</smscId><tagList><tagData><name>OA</name><value>'.$econet_oa.'</value></tagData><tagData><name>DA</name><value>'.$da.'</value></tagData><tagData><name>MESSAGE</name><value>'.$sms.'</value></tagData><tagData><name>MESSAGE_TYPE</name><value>1</value></tagData><tagData><name>MESSAGE_ID</name><value>'.time() . $sms_id.'</value></tagData><tagData><name>USERNAME</name><value>'.$econet_us.'</value></tagData><tagData><name>PASSWORD</name><value>'.$econet_pw.'</value></tagData></tagList></Request>';

  //setting the curl parameters.
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $mt_primary);

  // Following line is compulsary to add as it is:
  curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
  $data = curl_exec($ch);
  curl_close($ch);

  $response = json_encode(simplexml_load_string($data));
  $json_rsp = json_decode($response,true);

  if (isset($json_rsp['statusDesc'])) {
    return $json_rsp['statusDesc'];
  }
  elseif ($data != "") {
    return $data;
  }
  else{
    return "Result Unknown";
  }
}

function utf8_to_gsm0338($string){
  $dict = array(
    '@' => "\x00", '£' => "\x01", '$' => "\x02", '¥' => "\x03", 'è' => "\x04", 'é' => "\x05", 'ù' => "\x06", 'ì' => "\x07", 'ò' => "\x08", 'Ç' => "\x09", 'Ø' => "\x0B", 'ø' => "\x0C", 'Å' => "\x0E", 'å' => "\x0F",
    'Δ' => "\x10", '_' => "\x11", 'Φ' => "\x12", 'Γ' => "\x13", 'Λ' => "\x14", 'Ω' => "\x15", 'Π' => "\x16", 'Ψ' => "\x17", 'Σ' => "\x18", 'Θ' => "\x19", 'Ξ' => "\x1A", 'Æ' => "\x1C", 'æ' => "\x1D", 'ß' => "\x1E", 'É' => "\x1F",
    // all \x2? removed
    // all \x3? removed
    // all \x4? removed
    'Ä' => "\x5B", 'Ö' => "\x5C", 'Ñ' => "\x5D", 'Ü' => "\x5E", '§' => "\x5F",
    '¿' => "\x60",
    'ä' => "\x7B", 'ö' => "\x7C", 'ñ' => "\x7D", 'ü' => "\x7E", 'à' => "\x7F",
    '^' => "\x1B\x14", '{' => "\x1B\x28", '}' => "\x1B\x29", '\\' => "\x1B\x2F", '[' => "\x1B\x3C", '~' => "\x1B\x3D", ']' => "\x1B\x3E", '|' => "\x1B\x40", '€' => "\x1B\x65"
  );
  $converted = strtr($string, $dict);
  
  // Replace unconverted UTF-8 chars from codepages U+0080-U+07FF, U+0080-U+FFFF and U+010000-U+10FFFF with a single ?
  return preg_replace('/([\\xC0-\\xDF].)|([\\xE0-\\xEF]..)|([\\xF0-\\xFF]...)/m',' ',$converted);
}

function display_top_menu_nodes($mnu_id, $num_nodes=4){
  global $SLASHES;

  $sql  = "SELECT p.id, p.title, MAX(i.views) AS h, MAX(i.downloads) AS d
            FROM st_item p LEFT JOIN st_issue i ON i.itm_id=p.id
            WHERE cat_id=". $mnu_id. "
            GROUP BY id
            ORDER BY d, h, title
            LIMIT 0,". $num_nodes;

  $result = mysql_query($sql) or die('Error listing popular newspapers.'); // DEBUG: .. mysql_error());

  if (mysql_num_rows($result) != 0) {
      echo "<ul>";
      while ($row = mysql_fetch_assoc($result)) {
        $p = new Product($row['id']);
        $p ->get_latest_issue();

        echo "<li><a href='". $SLASHES ."product/". $p->get_id() ."'>". stripslashes($p->get_name()) ."</a></li>";
      }
      echo "</ul>";
  }
  else{
    echo "<ul><li style='border:0;'>&nbsp;</li></ul>";
  }
}

function display_msg_queue($msg_array){
  if($msg_array['msg'] != 'Message text here'){
    ?>
      <div class="alert alert-<?php echo $msg_array['type']; ?> alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <strong>Attention!</strong> <?php echo $msg_array['msg']; ?>
      </div>
    <?php
  }
}

function get_category_image($categ_id){
  if(is_numeric($categ_id)){
    $rs     = mysql_query("SELECT image FROM st_category WHERE id = {$categ_id}") or die('Error fetching category image.'); // DEBUG: . mysql_error());
    $rw     = mysql_fetch_assoc($rs);

    return $rw['image'];
  }
  else
    return "noimage.jpg";
}

function get_category_title($categ_id){
  if(is_numeric($categ_id)){
    $rs     = mysql_query("SELECT title FROM st_category WHERE id = {$categ_id}") or die('Error fetching category title.'); // DEBUG: . mysql_error());
    $rw     = mysql_fetch_assoc($rs);

    return $rw['title'];
  }
  else
    return "No category found!";
}

function get_publisher_title($pub_id){
  if(is_numeric($pub_id)){
    $rs     = mysql_query("SELECT name FROM st_publisher WHERE id = {$pub_id}") or die('Error fetching publisher title.'); // DEBUG: . mysql_error());
    $rw     = mysql_fetch_assoc($rs);

    return $rw['name'];
  }
  else
    return "No publisher found!";
}

function get_author_title($auth_id){
  if(is_numeric($auth_id)){
    $rs     = mysql_query("SELECT name FROM st_author WHERE id = {$auth_id}") or die('Error fetching author title.'); // DEBUG: . mysql_error());
    $rw     = mysql_fetch_assoc($rs);

    return $rw['name'];
  }
  else
    return "No author found!";
}

function get_category_description($categ_id){
  if(is_numeric($categ_id)){
    $rs     = mysql_query("SELECT description FROM st_category WHERE id = {$categ_id}") or die('Error fetching category description.'); // DEBUG: . mysql_error());
    $rw     = mysql_fetch_assoc($rs);

    if ($rw['description'] != '') {
      return $rw['description'];
    } else {
      return "No category description available.";
    }
  }
  else
    return "No category found!";
}

function get_publisher_description($pub_id){
  if(is_numeric($pub_id)){
    $rs     = mysql_query("SELECT brief FROM st_publisher WHERE id = {$pub_id}") or die('Error fetching publisher description.'); // DEBUG: . mysql_error());
    $rw     = mysql_fetch_assoc($rs);

    if ($rw['brief'] != '') {
      return $rw['brief'];
    } else {
      return "No publisher description available.";
    }
  }
  else
    return "No publisher found!";
}

function get_author_description($auth_id){
  if(is_numeric($auth_id)){
    $rs     = mysql_query("SELECT brief FROM st_author WHERE id = {$auth_id}") or die('Error fetching author description.'); // DEBUG: . mysql_error());
    $rw     = mysql_fetch_assoc($rs);

    if ($rw['brief'] != '') {
      return $rw['brief'];
    } else {
      return "No author description available.";
    }
  }
  else
    return "No author found!";
}

function get_category_parent_id($categ_id){
  if(is_numeric($categ_id)){
    $rs     = mysql_query("SELECT parent_id FROM st_category WHERE id = {$categ_id}") or die('Error fetching category parent.'); // DEBUG: . mysql_error());
    $rw     = mysql_fetch_assoc($rs);

    return $rw['parent_id'];
  }
  else
    return "No category found!";
}

function get_category_breadcrumb($categ_id){
  if(is_numeric($categ_id)){
    global $SLASHES;
    $cat_id     = $categ_id;
    $str_return = '<ul class="breadcrumb"><li><a href="'. $SLASHES .'">Home</a></li>';
    $slug       = '';

    while ($cat_id != 0) {
      if ($cat_id  == $categ_id) {
        $slug   = '<li>'. get_category_title($cat_id) .'</li>'. $slug;
      }
      else{
        $slug   = '<li><a href="'. $SLASHES .'store/'. $cat_id .'">'. get_category_title($cat_id) .'</a></li>'. $slug;
      }
      $cat_id   = get_category_parent_id($cat_id);
    }
    return $str_return . $slug .'</ul>';
  }
  else
    return "No category found!";
}

function get_publisher_breadcrumb($pub_id){
  if(is_numeric($pub_id)){
    global        $SLASHES;

    $str_return  = '<ul class="breadcrumb"><li><a href="'. $SLASHES .'">Home</a></li>';
    $str_return .= '<li><a href="#">Publishers</a></li>';
    $str_return .= '<li>'. get_publisher_title($pub_id) .'</li>';

    return $str_return .'</ul>';
  }
  else
    return "No category found!";
}

function get_author_breadcrumb($auth_id){
  if(is_numeric($auth_id)){
    global        $SLASHES;

    $str_return  = '<ul class="breadcrumb"><li><a href="'. $SLASHES .'">Home</a></li>';
    $str_return .= '<li><a href="#">Authors</a></li>';
    $str_return .= '<li>'. get_author_title($auth_id) .'</li>';

    return $str_return .'</ul>';
  }
  else
    return "No author found!";
}

function get_product_breadcrumb($prod_id){
  if(is_numeric($prod_id)){
    global $SLASHES;
    $p          = new Product($prod_id);
    $p          ->get_latest_issue();
    $cat_id     = $p->get_category_id();
    $str_return = '<ul class="breadcrumb"><li><a href="'. $SLASHES .'">Home</a></li>';
    $slug       = '';

    while ($cat_id != 0) {
      $slug     = '<li><a href="'. $SLASHES .'store/'. $cat_id .'">'. get_category_title($cat_id) .'</a></li>'. $slug;
      $cat_id   = get_category_parent_id($cat_id);
    }

    $slug       = $slug. '<li>'. $p->get_name() .'</li>';

    return $str_return . $slug .'</ul>';
  }
  else
    return "No product found!";
}

function get_user_id_by_email($email){
  if(isValidEmail($email)){
    $rs     = mysql_query("SELECT id FROM st_user WHERE email = '{$email}'") or die('Error fetching user id.'); // DEBUG: . mysql_error());
    if(mysql_num_rows($rs) > 0){
      $rw     = mysql_fetch_assoc($rs);
      return $rw['id'];
    }
    else
      return 0;
  }
  else
    return 0;
}

function save_user($user_data){
  $uid  = get_user_id_by_email($user_data['email']);
  if ($uid > 0) {
    return $uid;
  }
  else{
    $sql  = "INSERT INTO st_user (id,fullname,email,mobile,subm)
             VALUES(0,'". $user_data['name'] ."','". $user_data['email'] ."','". $user_data['mobile'] ."',". time() .")";

    mysql_query($sql) or die('Error saving user data.'); // DEBUG:  . mysql_error());

    return mysql_insert_id();
  }
}

function create_transaction($t_data){
  $trans_id = 0;
  $sql  = "INSERT INTO st_transaction (id,uid,ref,transactiondate,amount,status,description,datesub)
           VALUES(0,".$t_data['uid'].",'".$t_data['ref']."','".$t_data['tdate']."',".$t_data['amount'].",'created','".$t_data['descr']."',".time().")";

  mysql_query($sql) or die('Error saving transaction.'); // DEBUG:  . mysql_error());

  $trans_id = mysql_insert_id();

  // >>> Now save the transaction data
  if ($trans_id > 0) {
    foreach ($_SESSION['eshopcart'] as $prodid => $value) {
      foreach ($_SESSION['eshopcart'][$prodid] as $issueid => $value) {
        $oProd    = new Product($prodid);
        $oProd    ->get_issue($issueid);

        $sql      = "INSERT INTO st_trans_data (id,tid,pid,iid,amount,downloads)
                     VALUES(0,".$trans_id.",".$prodid.",".$issueid.",".$oProd->get_price().",0)";

        mysql_query($sql) or die('Error transaction data.'); // DEBUG: . mysql_error());
      }
    }
  }
  return $trans_id;
}

/*
function update_transaction_status($transaction_id, $status){
  $sql  = "UPDATE st_transaction SET status='". $status ."' WHERE id=". $transaction_id;

  mysql_query($sql) or die('Error updating transaction.'); // DEBUG:  . mysql_error());
}
*/

/// >>> -----------------------------------------------------------------------------
/// >>> --------------- PAYNOW FUNCTIONS --------------------------------------------
/// >>> -----------------------------------------------------------------------------
function process_paynow_purchase($post_data){

  // local and global variables
  global $site_url;
  global $merchant_id;
  global $merchant_key;
  global $paynow_url;
  global $oMsg;

  $return_val = false;

  // >>>> send to paynow
  $values       = array(
                        'resulturl' => $site_url ."downloads?paynowaction=result&tid=". $post_data['transaction_id'] .'&ref='. $post_data['reference'],  // used for silent polls as well
                        'returnurl' => $site_url ."downloads?paynowaction=return&tid=". $post_data['transaction_id'] .'&ref='. $post_data['reference'],
                        'reference' => $post_data['reference'],
                        'amount'    => $post_data['amount'],
                        'id'        => $merchant_id,
                        'status'    => 'Message',
                        'additionalinfo'  => $post_data['description'],
                        'authemail'       => $post_data['user_email'],
                        );

  $fields_string  = CreateMsg($values, $merchant_key);

  //open connection
  $ch             = curl_init();
  $url            = $paynow_url;

  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

  //execute post
  $result         = curl_exec($ch);

  if($result) {
    $msg          = ParseMsg($result);

    //first check status, take appropriate action
    if ($msg["status"]      == ps_error){
      $oMsg->add_message('danger','Failed with error: '. $msg['Error']);
    }
    else if ($msg["status"] == ps_ok){

      //second, check hash
      $validateHash         = CreateHash($msg, $merchant_key);
      if($validateHash     != $msg["hash"]){
        $oMsg->add_message('danger','Mismatched payment security tokens');
        //DEBUG: 'Paynow reply hashes do not match: '. $validateHash . ' - '. $msg["hash"] .' <br>'. $result;
      }
      else{
        $paynow_process_url = $msg["browserurl"];

        //update transaction
        $sql  = "UPDATE st_transaction SET status='waiting confirmation' WHERE id=". $post_data['transaction_id'];
        mysql_query($sql) or die('Error updating transaction.'); // DEBUG:  . mysql_error());
        //update_transaction_status($post_data['transaction_id'], "waiting confirmation")

        //close connection
        curl_close($ch);

        header("Location: $paynow_process_url");
        $return_val = true;
        exit;
      }
    }
    else {
      $oMsg->add_message('danger','Invalid status in from payment gateway, cannot continue.'); // DEBUG: . $msg["status"];
    }
  }
  else{
     $error = curl_error($ch);
     $oMsg->add_message('danger','Could not access the paynow servers. Payment aborted.');
  }

  //close connection
  curl_close($ch);
  return $return_val;

}

function ParseMsg($msg) {
  $parts = explode("&",$msg);
  $result = array();
  foreach($parts as $i => $value) {
    $bits = explode("=", $value, 2);
    $result[$bits[0]] = urldecode($bits[1]);
  }
  return $result;
}

function CreateHash($values, $MerchantKey){
  $string = "";
  foreach($values as $key=>$value) {
    if( strtoupper($key) != "HASH" ){
      $string .= $value;
    }
  }
  $string .= $MerchantKey;
  $hash = hash("sha512", $string);
  return strtoupper($hash);
}

function CreateMsg($values, $MerchantKey){
  $fields = array();
  foreach($values as $key=>$value) {
     $fields[$key] = urlencode($value);
  }

  $fields["hash"] = urlencode(CreateHash($values, $MerchantKey));

  $fields_string = UrlIfy($fields);
  return $fields_string;
}

function UrlIfy($fields) {
  //url-ify the data for the POST
  $delim = "";
  $fields_string = "";
  foreach($fields as $key=>$value) {
    $fields_string .= $delim . $key . '=' . $value;
    $delim = "&";
  }

  return $fields_string;
}

/*
  --------------------------
   GENERAL URL FUNCTIONS
  --------------------------
*/
///////// Returns Current Page to be displayed ////////////
function get_url_page($directory){
  global    $allowed_pages;

  $clsURL   = new URL($directory);
  $depth    = $clsURL->getDepth();
  $pg       = "";

  if( !$clsURL->segment(1))
    $pg     = 'store';
  else
    $pg     = $clsURL->segment(1);

  if($pg == 'index.php' || startsWith($pg, "index.php")){
    $pg = 'store';
  }
  elseif(startsWith($pg, "downloads")){
    $pg = 'downloads';
  }
  elseif ($pg == 'logout') {
    unset($_SESSION['eshopuser']);
    $pg = 'store';
  }

  if (!in_array($pg, $allowed_pages))
    $pg     = '404';

  return $pg;
}
//////// Returns Slashes to Escape to HOME ///////////////
function get_url_slashes($directory){
  $clsURL   = new URL($directory);
  $depth    = $clsURL->getDepth();
  $sl       = "";

  for ($i   = 0; $i < $depth; $i++){
    $sl    .= '../';
  }

  return $sl;
}
//////// Returns page title to use
function get_page_title(){
  global $DIR;

  $clsURL     = new URL($DIR);
  $url_block  = $clsURL->segment(2);
  $itm_array  = explode("?", $url_block);
  $itmid      = $itm_array[0];
  $pgtitle    = "E-Shop Zimbabwe";

  if($clsURL->segment(1) == "product" && is_numeric($itmid)){
    $rs     = mysql_query("SELECT title FROM st_item WHERE id = {$itmid}") or die('Error fetching slug-title.'); // DEBUG: . mysql_error());
    if(mysql_num_rows($rs) > 0){
      $rw       = mysql_fetch_assoc($rs);
      $pgtitle  = ltrim(rtrim($rw['title'])) ." | E-Shop Zimbabwe";
    }
  }
  elseif($clsURL->segment(1) == "store" && is_numeric($itmid)){
    $rs     = mysql_query("SELECT title FROM st_category WHERE id = {$itmid}") or die('Error fetching slug-title.'); // DEBUG: . mysql_error());
    if(mysql_num_rows($rs) > 0){
      $rw       = mysql_fetch_assoc($rs);
      $pgtitle  = ltrim(rtrim($rw['title'])) ." | E-Shop Zimbabwe";
    }
  }
  elseif($clsURL->segment(1) == "cart"){
    $pgtitle  = "Shopping Cart | E-Shop Zimbabwe";
  }
  elseif($clsURL->segment(1) == "checkout"){
    $pgtitle  = "Checkout | E-Shop Zimbabwe";
  }
  return $pgtitle;
}

/////// PROCESSING CHECKOUT AND PAYNOW REDIRECT:


/*
  --------------------------
   GENERAL SESSION FUNCTIONS
  --------------------------
*/
////// SHOPPING CART & FAVORITES LIST PROCESSING //////////
/* If you're used to WooCommerce, i'm very sorry my friend... */
function process_shopping_cart(){
  global $oMsg;
  global $SLASHES;

  // >>> Clear old cart items
  if(isset($_SESSION['eshopcart'])){
    foreach ($_SESSION['eshopcart'] as $prodid => $value) {
      foreach ($_SESSION['eshopcart'][$prodid] as $issueid => $value) {
        if($_SESSION['eshopcart'][$prodid][$issueid]['expiry'] < time())
          unset($_SESSION['eshopcart'][$prodid][$issueid]);
        if(sizeof($_SESSION['eshopcart'][$prodid]) < 1)
          unset($_SESSION['eshopcart'][$prodid]);
      }
    }
  }

  // >>> Add to Cart
  if(isset($_GET['action'])){
    if($_GET['action'] == 'add'){
      $prod_id      = intval($_GET['id']);
      $issue_id     = 0;
      if(isset($_GET['iid']))
        $issue_id   = intval($_GET['iid']);

      if(!isset($_SESSION['eshopcart'][$prod_id][$issue_id])){
        $_SESSION['eshopcart'][$prod_id][$issue_id]  = array('expiry' => time()+(2*24*3600), 'qty' => 1);
      }
      else{
        $_SESSION['eshopcart'][$prod_id][$issue_id]['expiry'] = time()+(2*24*3600);
      }
      $oMsg->add_message('success','Product successfully added to your shopping cart. Click <b><a href="'.$SLASHES.'cart/">here</a></b> to go to your shopping cart.');
    }

  // >>> Remove From Cart
    if($_GET['action'] == 'rmd'){
      $prod_id      = intval($_GET['id']);
      $issue_id     = 0;
      if(isset($_GET['iid']))
        $issue_id   = intval($_GET['iid']);

      if(!isset($_SESSION['eshopcart'][$prod_id][$issue_id])){
        unset($_SESSION['eshopcart'][$prod_id][$issue_id]);
      }
      $oMsg         ->add_message('info','Product removed from your shopping cart.');
    }

  // >>> Hack to clear cart
    if($_GET['action'] == 'clearcarteliazer'){
      unset($_SESSION['eshopcart']);
    }
  }

  // >>> Clear old/expired cart items
}

function process_favorites(){
  global $oMsg;

  // >>> Clear old favorites items
  if(isset($_SESSION['eshopwatch'])){
    foreach ($_SESSION['eshopwatch'] as $prodid => $value) {
      foreach ($_SESSION['eshopwatch'][$prodid] as $issueid => $value) {
        if($_SESSION['eshopwatch'][$prodid][$issueid]['expiry'] < time())
          unset($_SESSION['eshopwatch'][$prodid][$issueid]);
        if(sizeof($_SESSION['eshopwatch'][$prodid]) < 1)
          unset($_SESSION['eshopwatch'][$prodid]);
      }
    }
  }

  // >>> Add to Favorites
  if(isset($_GET['action'])){
    if($_GET['action'] == 'addwtc'){
      $prod_id    = intval($_GET['id']);
      $issue_id   = 0;
      if(isset($_GET['iid']))
        $issue_id = intval($_GET['iid']);

      if(!isset($_SESSION['eshopwatch'][$prod_id][$issue_id])){
        $_SESSION['eshopwatch'][$prod_id][$issue_id] = array('expiry' => time()+(7*24*3600), 'qty' => 1);
      }
      else{
        $_SESSION['eshopwatch'][$prod_id][$issue_id]['expiry'] = time()+(7*24*3600);
      }
      $oMsg       ->add_message('success','Product successfully added to your wishlist. Click <b><a href="#">here</a></b> to go to wishlist.');
    }

  // >>> Remove from Favorites
    if($_GET['action'] == 'rmdwtc'){
      $prod_id    = intval($_GET['id']);
      $issue_id   = 0;
      if(isset($_GET['iid']))
        $issue_id = intval($_GET['iid']);

      if(!isset($_SESSION['eshopwatch'][$prod_id][$issue_id])){
        unset($_SESSION['eshopwatch'][$prod_id][$issue_id]);
      }
      $oMsg       ->add_message('info','Product removed from your wishlist.');
    }

  // >>> Hacks to clear faves
    if($_GET['action'] == 'clearfaveseliazer'){
      unset($_SESSION['eshopwatch']);
    }
  }
  // >>> Clear old/expired fave items
}

// >> Get number of cart items
function get_cart_items_count(){
  if(isset($_SESSION['eshopcart'])){
    $cart_items = 0;
    foreach ($_SESSION['eshopcart'] as $prodid => $value) {
      foreach ($_SESSION['eshopcart'][$prodid] as $issueid => $value) {
        $cart_items++;
      }
    }
    return $cart_items;
  }
  else{
    return 0;
  }
}

// >> Get number of favorite items
function get_favorites_items_count(){
  if(isset($_SESSION['eshopwatch'])){
    $fave_items = 0;
    foreach ($_SESSION['eshopwatch'] as $prodid => $value) {
      foreach ($_SESSION['eshopwatch'][$prodid] as $issueid => $value) {
        $fave_items++;
      }
    }
    return $fave_items;
  }
  else{
    return 0;
  }
}

/*
	--------------------------
	 GENERAL STRING FUNCTIONS
	--------------------------
*/
function get_string_between($string, $start, $end){
    $string = " ".$string;
    $ini = strpos($string,$start);
    if ($ini == 0) return "";
    $ini += strlen($start);
    $len = strpos($string,$end,$ini) - $ini;
    return substr($string,$ini,$len);
}

function startsWith($haystack, $needle){
    return $needle === "" || strpos($haystack, $needle) === 0;
}

function endsWith($haystack, $needle){
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

//* --- Format Currency
function formatMoney($number, $fractional=true) {
    if ($fractional) {
        $number = sprintf('%.2f', $number);
    }
    while (true) {
        $replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1,$2', $number);
        if ($replaced != $number) {
            $number = $replaced;
        } else {
            break;
        }
    }
    return $number;
}

//* --- Check if submitted email is valid
function isValidEmail($email){
	return eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$",$email);
}

//* --- Generate a random character string
function randomString($length){

	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';

    // Length of character list
    $chars_length = (strlen($chars) - 1);

    // Start our string
    $string = $chars{rand(0, $chars_length)};

    // Generate random string
    for ($i = 1; $i < $length; $i = strlen($string)){
        // Grab a random character from our list
        $r = $chars{rand(0, $chars_length)};

        // Make sure the same two characters don't appear next to each other
        if ($r != $string{$i - 1}) $string .=  $r;
    }
    // Return the string
    return $string;
}


/*
	--------------------------
	 	UPLOADING FUNCTIONS
	--------------------------
***
	Upload an image and create the thumbnail. The thumbnail is stored
	under the /thumbnail/ sub-directory of $uploadDir.
	---
	Return the uploaded image and thumbnail names.
*/
function uploadImage($inputName, $uploadDir) {
	$image     = $_FILES[$inputName];
	$imagePath = '';
	$thumbnailPath = '';

	// if a file is given
	if (trim($image['tmp_name']) != '') {
		$ext = substr(strrchr($image['name'], "."), 1);

		// generate a random new file name to avoid name conflict
		// then save the image under the new file name
		$imagePath = md5(rand() * time()) . ".$ext";
		$result    = move_uploaded_file($image['tmp_name'], $uploadDir . $imagePath);

		if ($result) {
			// create thumbnail
			$thumbnailPath =  md5(rand() * time()) . ".$ext";
			$result = createThumbnail($uploadDir . $imagePath, $uploadDir . 'thumbnails/' . $thumbnailPath, 200);

			// create thumbnail failed, delete the image
			if (!$result) {
				unlink($uploadDir . $imagePath);
				$imagePath = $thumbnailPath = '';
			} else {
				$thumbnailPath = $result;
			}
		} else {
			// the image cannot be uploaded
			$imagePath = $thumbnailPath = '';
		}
	}
	return array('image' => $imagePath, 'thumbnail' => $thumbnailPath);
}

/*
	Create a thumbnail of $srcFile and save it to $destFile.
	The thumbnail will be $width pixels.
*/
function createThumbnail($srcFile, $destFile, $width, $quality = 75){
	$thumbnail = '';

	if (file_exists($srcFile)  && isset($destFile))
	{
		$size        = getimagesize($srcFile);
		$w           = number_format($width, 0, ',', '');
		$h           = number_format(($size[1] / $size[0]) * $width, 0, ',', '');

		$thumbnail =  copyImage($srcFile, $destFile, $w, $h, $quality);
	}

	// return the thumbnail file name on sucess or blank on fail
	return basename($thumbnail);
}

/*
	Copy an image to a destination file. The destination
	image size will be $w X $h pixels
*/
function copyImage($srcFile, $destFile, $w, $h, $quality = 75) {
    $tmpSrc     = pathinfo(strtolower($srcFile));
    $tmpDest    = pathinfo(strtolower($destFile));
    $size       = getimagesize($srcFile);

    if ($tmpDest['extension'] == "gif" || $tmpDest['extension'] == "jpg")
    {
       $destFile  = substr_replace($destFile, 'jpg', -3);
       $dest      = imagecreatetruecolor($w, $h);
       //imageantialias($dest, TRUE);
    } elseif ($tmpDest['extension'] == "png") {
       $dest = imagecreatetruecolor($w, $h);
       //imageantialias($dest, TRUE);
    } else {
      return false;
    }

    switch($size[2])
    {
       case 1:       //GIF
           $src = imagecreatefromgif($srcFile);
           break;
       case 2:       //JPEG
           $src = imagecreatefromjpeg($srcFile);
           break;
       case 3:       //PNG
           $src = imagecreatefrompng($srcFile);
           break;
       default:
           return false;
           break;
    }

    imagecopyresampled($dest, $src, 0, 0, 0, 0, $w, $h, $size[0], $size[1]);

    switch($size[2]){
       case 1:
       case 2:
           imagejpeg($dest,$destFile, $quality);
           break;
       case 3:
           imagepng($dest,$destFile);
    }
    return $destFile;

}

/*
	Upload an image and create the preview image (700x500). The preview image file
	is stored under the $uploadDir sub-directory.

	Return the uploaded image name and the thumbnail also.
*/
function uploadPreview($inputName, $uploadDir, $img_width=800){
	$image     = $_FILES[$inputName];
	$imagePath = '';

	// if a file is given
	if (trim($image['tmp_name']) != '') {
		$ext = substr(strrchr($image['name'], "."), 1);

		// generate a random new file name to avoid name conflict
		// then save the image under the new file name
		$imagePath 	= md5(rand() * time()) . ".$ext";
		$result    	= move_uploaded_file($image['tmp_name'], $uploadDir . $imagePath);

		if (file_exists($uploadDir . $imagePath)){
			$srcImg	= $uploadDir . $imagePath;

			$size	= getimagesize($srcImg);
			$w		= number_format($img_width, 0, ',', '');
			$h    = number_format(($size[1] / $size[0]) * $img_width, 0, ',', '');

			$img	= resizeImage($srcImg, $w, $h);
		}
	}
	return $imagePath;
}

/*
	Copy an image to a destination file. The destination
	image size will be $w X $h pixels
*/
function resizeImage($srcFile, $w, $h){
    $destFile	= $srcFile;
	$tmpSrc     = pathinfo(strtolower($srcFile));
    $tmpDest    = pathinfo(strtolower($destFile));
    $size       = getimagesize($srcFile);


    if ($tmpDest['extension'] == "gif" || $tmpDest['extension'] == "jpg"){
       $destFile  = substr_replace($destFile, 'jpg', -3);
       $dest      = imagecreatetruecolor($w, $h);
       //imageantialias($dest, TRUE);
    }
	elseif ($tmpDest['extension'] == "png") {
       $dest = imagecreatetruecolor($w, $h);
       //imageantialias($dest, TRUE);
    }
	else {
      return false;
    }

    switch($size[2]){
       case 1:       //GIF
           $src = imagecreatefromgif($srcFile);
           break;
       case 2:       //JPEG
           $src = imagecreatefromjpeg($srcFile);
           break;
       case 3:       //PNG
           $src = imagecreatefrompng($srcFile);
           break;
       default:
           return false;
           break;
    }

    imagecopyresampled($dest, $src, 0, 0, 0, 0, $w, $h, $size[0], $size[1]);

    switch($size[2]){
       case 1:
       case 2:
           imagejpeg($dest,$destFile, 75);
           break;
       case 3:
           imagepng($dest,$destFile);
    }
    return $destFile;
}

/*
	Upload a file and store it under the $uploadDir directory.

	Return the uploaded file name.
*/
function uploadFile($inputName, $uploadDir){
	$file     = $_FILES[$inputName];
	$filePath = '';

	// if a file is given
	if (trim($file['tmp_name']) != '') {
		$ext = substr(strrchr($file['name'], "."), 1);

		// generate a random new file name to avoid name conflict
		// then save the file under the new name
		$filePath 	= md5(rand() * time()) . ".$ext";
		$result    	= move_uploaded_file($file['tmp_name'], $uploadDir . $filePath);
	}
	return $filePath;
}


/*
	--------------------------
	 	LOGON FUNCTIONS
	--------------------------
***
	Check if the user is logged in or not
*/
function isUserLoggedin(){
	if (isset($_SESSION['amh.earl']) && isset($_SESSION['amh.earl.id'])) {
		return true;
	}
	else{
		return false;
	}
}
/*
	Check if the administrator is logged in or not
*/
function isAdminLoggedin(){
	if (!isset($_SESSION['amh.earl.ad']) || $_SESSION['amh.earl.ad'] == false) {
		return false;
	}
	else{
		return true;
	}
}



/*
	--------------------------
	 	EMAIL FUNCTIONS
	--------------------------
*/
//* --- Send registration success email
function sendActivationEmail($email,$activation){
	// Sending params.
	$year  	 = Date('Y',time());
	$today   = Date('Y/m/d, H:i',time());

	$subject = "EShop Zim Subscriptions";
	$header  = "From: account-reg@eshopzim.com \r\n" .
             "Reply-To: info@eshopzim.com \r\n" .
             "Content-type: text/html; charset=UTF-8 \r\n";

	//begin of HTML message
	$em_body = <<<EOF
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
            <!--
            © $year E-Shop Zimbabwe
            http://eshopzim.com/

            Name       : E-Shop Zimbabwe
            Description: Zimbabwe's one-stop online digital shop for multimedia content.
            Generated  : $today

            -->
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
              <title>E-Shop Zimbabwe</title>
            </head>
            <body style="font-family: Arial, Helvetica, sans-serif; color: #515151; font-size: 14px;text-align:center;">
              <table style="border:1px solid #ccc; margin:0px auto;" cellpadding="5" cellspacing="0" width="700">
              <tr>
              <td align="center" style="font-size: 22px; background: #ccc; color:#e22b30;"><b>E-Shop Zimbabwe</b></td>
              </tr>
              <tr>
              <td>
                <p>E-Shop Zimbabwe Account Activation</p>
                <p>
                Thank you for choosing E-Shop Zimbabwe. To begin using your account please go to
                <a href="http://eshopzim.com/login/">http://www.eshopzim.com/</a> and login with
                the following details:<br/><br/>
                <b>Username:</b> $email<br/>
                <b>Password:</b> $activate<br/><br/>

                <b>Zimbabwe's one-stop online digital shop for multimedia content....</b>
                 </p>
              </td>
              </tr>
              <tr>
              <td align="center" style="font-size: 11px; background:#e22b30; color: #000;">
                &copy; $year, <a style="color: #fff; text-decoration: underline;" href="http://eshopzim.com/">E-Shop Zimbabwe Platform</a>.<br/>
                All Rights Reserved.
              </td>
              </tr>
              </table>
            </body>
            </html>
EOF;

    return  ($send  = mail($email, $subject, $em_body, $header));
}

?>
