<?php
$st_msg         = "";
if (isset($_GET['rmdir'])) {
    $st_id      = addslashes($_GET['rmdir']);
    if (is_numeric($st_id)) {
        $title  = get_news_service_title($st_id);
        
        mysql_query("DELETE FROM tblnews_svc WHERE id=$st_id") or die("Error deleting service."); # DEBUG: mysql_error());
        $st_msg = "The service <b>$title</b> was successfuly removed.";

        save_activity($oUSR->get_id(), $action_code=4, "Removed News Service with title: ". $title);
    }
}
if (isset($_GET['pssvc'])) {
    $st_id      = addslashes($_GET['pssvc']);
    if (is_numeric($st_id)) {
        $title  = get_news_service_title($st_id);

        mysql_query("UPDATE tblnews_svc SET status=1 WHERE id=$st_id") or die("Error suspending service."); # DEBUG: mysql_error());
        $st_msg = "The service <b>$title</b> was successfuly suspended. No news content can be added or transmitted from the service.";

        save_activity($oUSR->get_id(), $action_code=5, "Suspended News Service with title: ". $title);
    }
}
if (isset($_GET['stitm'])) {
    $st_id      = addslashes($_GET['stitm']);
    if (is_numeric($st_id)) {
        $title  = get_news_service_title($st_id);

        mysql_query("UPDATE tblnews_svc SET status=0 WHERE id=$st_id") or die("Error deleting service."); # DEBUG: mysql_error());
        $st_msg = "The service <b>$title</b> has been resumed.";

        save_activity($oUSR->get_id(), $action_code=6, "Resumed News Service with title: ". $title);
    }
}
?>
<div id="page-wrapper" >
    <div id="page-inner">
        <div class="row">
            <div class="col-md-12">
                <h2><i class="fa fa-signal fa-fw"></i> Manage News Services</h2>
                <!-- <h5>Welcome Jhon Deo , Love to see you back. </h5> -->
            </div>
        </div><!-- /. ROW  -->

        <hr />

        <div class="row">

            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading"><i class="fa fa-list fa-fw"></i> News Services List</div>
                    <div class="panel-body">
                        <?php
                            if ($st_msg != "") {
                                ?>
                                <div class="form-group">
                                    <div class="alert alert-info" role="alert">
                                        <?php echo $st_msg; ?>
                                    </div>
                                </div>
                                <?php
                            }
                        ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Service Name</th>
                                        <th>Code</th>
                                        <th>Medium</th>
                                        <th>Priority</th>
                                        <th>Delivery Method</th>
                                        <th>Networks</th>
                                        <th>Added</th>
                                        <th>&nbsp;</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $sql        = "SELECT * FROM tblnews_svc ORDER BY status, title ASC, priority DESC";
                                        $result     = mysql_query($sql) or die('Error fetching services.'); // DEBUG: . mysql_error());

                                        if (mysql_num_rows($result) > 0) {
                                            $c      = 1;
                                            while($row  = mysql_fetch_assoc($result)){ //id,title,priority,method,networks,days,schedule,added
                                                ?>
                                                <tr <?php if($row['status'] == 1) echo 'class="warning"'; ?>>
                                                    <td><?php echo $c; ?></td>
                                                    <td>
                                                      <?php
                                                        echo $row['title'];
                                                        if ($row['status'] == 1) {
                                                            echo "<font color='brown'> [SUSPENDED]</font>";
                                                        }
                                                      ?>
                                                    </td>
                                                    <td><?php echo $row['svc_code']; ?></td>
                                                    <td><?php echo $row['medium']; ?></td>
                                                    <td><?php echo $row['priority']; ?></td>
                                                    <td>
                                                      <?php
                                                        if(strtoupper($row['method']) == 'SC'){
                                                            echo "At <b>". $row['schedule'] ."</b> on [<b>". $row['days'] ."</b>]";
                                                        }
                                                        elseif(strtoupper($row['method']) == 'UR'){
                                                            echo "ON USER REQUEST";
                                                        }
                                                        elseif(strtoupper($row['method']) == 'IM'){
                                                            echo "IMMEDIATELY ON UPLOAD";
                                                        }
                                                      ?>
                                                    </td>
                                                    <td>
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
                                                    </td>
                                                    <td><?php echo $row['added']; ?></td>
                                                    <td style="text-align:right;">
                                                        <a href="#" title="View Reports"><span class="glyphicon glyphicon-file"></span></a>
                                                        <a href="#" title="Update Service"><span class="glyphicon glyphicon-edit"></span></a>
                                                        <?php
                                                        if ($row['status'] == 1) {
                                                            ?>
                                                            <a href="index.php?p=services&stitm=<?php echo $row['id']; ?>" title="Continue Service"><span class="glyphicon glyphicon-play"></span></a>
                                                            <?php
                                                        }
                                                        else{
                                                            ?>
                                                            <a href="index.php?p=services&pssvc=<?php echo $row['id']; ?>" title="Suspend Service"><span class="glyphicon glyphicon-pause"></span></a>
                                                            <?php
                                                        }
                                                        ?>
                                                        <a href="index.php?p=services&rmdir=<?php echo $row['id']; ?>" title="Delete Service"><span class="glyphicon glyphicon-remove-circle"></span></a>
                                                    </td>
                                                </tr>
                                                <?php
                                                $c++;
                                            }
                                        }
                                        else{
                                            ?>
                                            <tr><td colspan="8"><p align="center"><em>-- No services defined. Click <a href="index.php?p=svc">here</a> to add a news service. --</em></p></td>
                                            <?php
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /. ROW  -->
    </div>
    <!-- /. PAGE INNER  -->
</div>
<!-- /. PAGE WRAPPER  -->
