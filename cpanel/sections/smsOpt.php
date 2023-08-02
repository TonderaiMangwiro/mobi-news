<?php
require_once '../../include/config.php';

if(isset($_GET['svc'])){
    if (is_numeric($_GET['svc'])) {
        $svc_id = addslashes($_GET['svc']);

        $sql    = "SELECT * FROM tblnews_svc WHERE status=0 AND id=$svc_id";
        
        $result = mysql_query($sql) or die('Error fetching service.'); //DEBUG: . mysql_error());

        if (mysql_num_rows($result) > 0) { 
            $row  = mysql_fetch_assoc($result)
            ?>  
                <div class="form-group">
                    <label class="control-label">Message ID:</label><!-- Networks -->
                    <br/>
                    <div class="row">
                        <div class="col-lg-4">
                            <input name="st_msgid" type="text" class="form-control" value="<?php echo $row['msgid']; ?>" />
                        </div><!-- /.col-lg-6 -->
                    </div>                                
                </div>

                <div class="form-group">
                    <label class="control-label">Destination Networks:</label><br/>
                    <?php
                        $st_destinations = $row['networks'];

                        if(stristr($st_destinations, 'VOD')) 
                            echo '<a href="#" class="btn btn-default btn-xs">VODACOM</a> ';
                        if(stristr($st_destinations, 'MTN')) 
                            echo '<a href="#" class="btn btn-warning btn-xs">MTN</a> ';
                        if(stristr($st_destinations, 'ECO')) 
                            echo '<a href="#" class="btn btn-primary btn-xs">ECONET</a> ';
                        if(stristr($st_destinations, 'NET')) 
                            echo '<a href="#" class="btn btn-default btn-xs">NET-ONE</a> ';
                        if(stristr($st_destinations, 'TEL')) 
                            echo '<a href="#" class="btn btn-danger btn-xs">TELECEL</a> ';
                    ?>
                </div>

                <div class="form-group">
                    <label class="control-label">News Schedule:</label>
                    <div class="alert alert-danger" role="alert">
                        <?php
                        if(strtoupper($row['method']) == 'SC'){ 
                            echo "News will be sent at <b>". $row['schedule'] ."</b> on [<b>". $row['days'] ."</b>]";
                        }
                        elseif(strtoupper($row['method']) == 'UR'){
                            echo "News will be sent on user request.";
                        }
                        elseif(strtoupper($row['method']) == 'IM'){
                            echo "News will be sent immediately after uploading.";
                        }
                        ?>
                    </div>
                </div>
            <?php
        }
    }
    else{
        echo "Bad Request!";
        exit;
    }
}
elseif(isset($_GET['st_id'])) {
    if (is_numeric($_GET['st_id'])) {
        $st_id = addslashes($_GET['st_id']);
        $sql    = "SELECT c.*, s.title, s.networks, s.method, s.schedule, s.days 
                    FROM tblcontent c, tblnews_svc s 
                    WHERE c.svc_id=s.id AND c.id=$st_id 
                    ORDER BY created DESC";
        $result = mysql_query($sql) or die('Error fetching news content.'); // DEBUG: . mysql_error());
        
        if (mysql_num_rows($result) > 0) {
            $row  = mysql_fetch_assoc($result);

            $sent = "NOT SENT";
            $stat = "NEWS NOT YET SENT";
            $s_bt = "Suspend Sending";
            
            if ($row['status'] == 1) {
                $stat = "<font color='red'><b>SENDING PAUSED</b></font>";
                $s_bt = "Resume Sending";
            }
            elseif ($row['status'] == 2) {
                $stat = "<font color='green'><b>NEWS CONTENT WAS SENT TO ". $row['num_sent'] ." RECIPIENTS.</b></font>";
                $sent = $row['to_send'];
            }
            ?>
            <div class="form-group">
                <label class="control-label">News Content:</label><br/>
                <textarea class="form-control" rows="8" style="cursor:auto;" readonly><?php echo $row['data']; ?></textarea>
            </div>
            <div class="form-group">
                <label class="control-label">News Service:</label> <?php echo $row['title'] ." - (".$row['days']." @".$row['schedule'].")"; ?>
            </div>
            <div class="form-group">
                <label class="control-label">Current Status:</label> <?php echo $stat; ?>
            </div>
            <div class="form-group">
                <label class="control-label">Uploaded:</label> <?php echo $row['created']; ?>
            </div>
            <div class="form-group">
                <label class="control-label">Sent:</label> <?php echo $sent; ?>
            </div>
            <div class="form-group">
            <?php if ($row['status'] < 2) { ?>
                <button onclick="javascript:togSMSStatus(<?php echo $row['id']; ?>,0);" type="button" class="btn btn-default"><?php echo $s_bt; ?></button>
                <button onclick="javascript:deleteSMS(<?php echo $row['id']; ?>);" type="button" class="btn btn-danger">Remove This Item</button>
            <?php } ?>
            </div>
            <?php
        }
        else{
            echo "News Content Not Found!";
        }
    }
    else{
        echo "Bad Request!";
    }
    exit;
}

?>