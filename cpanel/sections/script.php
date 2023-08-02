<?php
require_once ('../../include/config.php');
require_once ('../../include/MysqliDb.php');
require_once ('../../include/functions.php');

/* set out document type to text/javascript instead of text/html */
header("Content-type: text/javascript");

$db     = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
$result = array();
if (isset($_GET['sec'])) {

    // SUBSCRIPTIONS
    if (addslashes($_GET['sec']) == 'subscriptions') {
        $new    = 0;
        $total  = 0;
        $out    = 0;
        
        // New Subs
        $res    = $db->rawQuery("SELECT SUM(act) AS n FROM tblsummary WHERE added>DATE(DATE_SUB(NOW(), INTERVAL 7 DAY))");
        $new    = $res[0]['n'];

        // Total Active
        $res    = $db->rawQuery("SELECT COUNT(id) AS n FROM tblsubscriber WHERE status=0");
        $total  = $res[0]['n'];

        // Deactivations
        $res    = $db->rawQuery("SELECT SUM(dct) AS n FROM tblsummary WHERE added>DATE(DATE_SUB(NOW(), INTERVAL 7 DAY))");
        $out    = $res[0]['n'];

        $result[]  = array('label'=>"NEW SUBS",     'value'=>$new   );
        $result[]  = array('label'=>"TOTAL ACTIVE", 'value'=>$total );
        $result[]  = array('label'=>"DEACTIVATIONS",'value'=>$out   );
    }

    // ACTIVATIONS & DEACTIVATIONS
    elseif (addslashes($_GET['sec']) == 'activations') {
        $res    = $db->rawQuery("SELECT act,dct,added FROM tblsummary WHERE added>DATE(DATE_SUB(NOW(), INTERVAL 7 DAY)) ");
        if(sizeof($res) > 0){
            foreach ($res as $r) {
                $result[] = array(
                    'dte' => $r['added'],
                    'act' => $r['act'],
                    'dct' => $r['dct']
                    );
            }
        }
    }

    // NEWS DISPATCHED
    elseif (addslashes($_GET['sec']) == 'dispatch') {
        
        //$res    = $db->rawQuery("SELECT title, IFNULL((SELECT SUM(num_sent) FROM tblcontent WHERE DATE(to_send)=DATE(NOW()) AND svc_id=n.id),0) AS sent FROM tblnews_svc n");
        $res    = $db->rawQuery("SELECT num_sent FROM tblcontent WHERE DATE(to_send)=DATE(NOW())");
        
        if(sizeof($res) > 0){
            foreach ($res as $r) {
                $result[] = array(
                    'label' => strtoupper("MAIN NEWS"),
                    'value' => $r['num_sent']
                    );
            }
        }
    }

    /*
SELECT * 
FROM Member
WHERE DATEPART(m, date_created) = DATEPART(m, DATEADD(m, -1, getdate()))
AND DATEPART(yyyy, date_created) = DATEPART(yyyy, DATEADD(m, -1, getdate()))
    */

    // MONTHLY DEDUCT REPORT
    elseif (addslashes($_GET['sec']) == 'deductions') {
        $res    = $db->rawQuery("SELECT fee,added FROM tblsummary WHERE added>DATE(DATE_SUB(NOW(), INTERVAL 7 DAY)) ");
        if(sizeof($res) > 0){
            foreach ($res as $r) {
                $result[] = array(
                    'dte' => $r['added'],
                    'fee' => $r['fee']
                    );
            }
        }
    }
    
    // SDP TRANSMISSION
    elseif (addslashes($_GET['sec']) == 'sdp') {
        
        for ($i=7; $i >= 0; $i--) { 
            $params = Array($i,$i);
            $res    = $db->rawQuery("SELECT COUNT(id) AS outbound, (SELECT COUNT(id) FROM tblapilog WHERE DATE(added)=DATE(DATE_SUB(now(),interval ? day)) AND dir='IN') AS inbound FROM tblapilog WHERE DATE(added)=DATE(DATE_SUB(now(),interval ? day))",$params);
            
            if(sizeof($res) > 0){
                foreach ($res as $r) {
                    $result[] = array(
                        'dt' => date('Y-m-d', strtotime("-$i days")),
                        'in' => $r['inbound'],
                        'ou' => $r['outbound']
                        );
                }
            }
        }
    }
}

echo json_encode($result);


?>