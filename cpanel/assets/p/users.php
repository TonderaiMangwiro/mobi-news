<?php
$st_msg         = "";
if (isset($_GET['rmdir'])) {
    $st_id      = addslashes($_GET['rmdir']);
    if (is_numeric($st_id)) {
        mysql_query("DELETE FROM tblnews_svc WHERE id=$st_id") or die("Error deleting service."); # DEBUG: mysql_error()); 
        $st_msg = "The service was successfuly removed.";
    }
}
if (isset($_GET['pssvc'])) {
    $st_id      = addslashes($_GET['pssvc']);
    if (is_numeric($st_id)) {
        mysql_query("UPDATE tblnews_svc SET status=1 WHERE id=$st_id") or die("Error suspending service."); # DEBUG: mysql_error());
        $st_msg = "The service was successfuly suspended. No news can be added or sent from the service.";
    }
}
if (isset($_GET['stitm'])) {
    $st_id      = addslashes($_GET['stitm']);
    if (is_numeric($st_id)) {
        mysql_query("UPDATE tblnews_svc SET status=0 WHERE id=$st_id") or die("Error deleting service."); # DEBUG: mysql_error());
        $st_msg = "The service has been resumed.";
    }
}
?>
<div id="page-wrapper" >
    <div id="page-inner">
        <div class="row">
            <div class="col-md-12">
                <h2><i class="fa fa-group fa-fw"></i> Manage NCP Users</h2>   
                <!-- <h5>Welcome Jhon Deo , Love to see you back. </h5> -->
            </div>
        </div><!-- /. ROW  -->
        
        <hr />

        <div class="row">

            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading"><i class="fa fa-user fa-fw"></i> NCP Users</div>
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
                                        <th>NCP User</th>
                                        <th>Account</th>
                                        <th>Level</th>
                                        <th>Status</th>
                                        <th>Last Active</th>
                                        <th>Added</th>
                                        <th>&nbsp;</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $sql        = "SELECT * FROM tbluser ORDER BY status ASC, u2";
                                        $result     = mysql_query($sql) or die('Error fetching users.'); // DEBUG: . mysql_error());

                                        if (mysql_num_rows($result) > 0) { 
                                            $c      = 1;
                                            while($row  = mysql_fetch_assoc($result)){ //id,title,priority,method,networks,days,schedule,added
                                                $status  = "Active";
                                                $type    = "Administrator";
                                                $account = "AMH";

                                                if($row['status'] == 1)  {   $status    = "<font color='red'>SUSPENDED</font>"; }
                                                
                                                if($row['type'] == 2)    {   $type      = "Editorial";  }
                                                elseif($row['type'] == 3){   $type      = "Helpdesk";   }

                                                if($row['acc_id'] == 2) {    $account   = "M&G";        }
                                                elseif($row['acc_id'] == 3){ $account   = "ECONET";     }
                                                ?>
                                                <tr <?php if($row['status'] == 2) echo 'class="warning"'; ?>>
                                                    <td><?php echo $c; ?></td>
                                                    <td><?php echo $row['u2']; ?></td>
                                                    <td><?php echo $account;  ?></td>
                                                    <td><?php echo $type;  ?></td>
                                                    <td><?php echo $status;  ?></td>
                                                    <td><?php echo Date('d M Y, h:iA', strtotime($row['last_active'])); ?></td>
                                                    <td><?php echo Date('d M Y, h:iA', strtotime($row['added'])); ?></td>
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
                                            <tr><td colspan="7"><p align="center"><em>-- No users defined. Click <a href="index.php?p=usr">here</a> to add a user. --</em></p></td>
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
