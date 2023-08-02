<?php
$st_msg = "";

if(isset($_GET['ssp'])){
    if (is_numeric($_GET['ssp'])) {
        $sql    = "SELECT status FROM tblcontent WHERE id=". $_GET['ssp'];
        $result = mysql_query($sql) or die('Error fetching content detail.'); // DEBUG: . mysql_error());

        if (mysql_num_rows($result) > 0) { 
            $row  = mysql_fetch_assoc($result);
            if ($row['status'] == 0) {
                $sql = "UPDATE tblcontent SET status=1 WHERE id=". $_GET['ssp'];
            }
            else{
                $sql = "UPDATE tblcontent SET status=0 WHERE id=". $_GET['ssp'];
            }
            mysql_query($sql) or die('Error updating content.'); // DEBUG: . mysql_error());
            $st_msg = "Content status was successfuly updated.";
        }
    }
}
elseif(isset($_GET['rmcnt'])){
    if (is_numeric($_GET['rmcnt'])) {

        $db     = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
        
        ### PARAMETERS
        $db     ->where('id', addslashes($_GET['rmcnt']));
        $db     ->where('status', 0);

        if($db->delete('tblcontent')) 
            $st_msg = "Content was successfuly removed.";
        else
            $st_msg = "Unspecified error.";
    }
}
?>
<div id="page-wrapper" >
    <div id="page-inner">
        <div class="row">
            <div class="col-md-12">
                <h2><i class="fa fa-envelope-o fa-fw"></i> Manage News Content</h2>
                <?php 
                    if ($st_msg != "") {
                        ?>
                        <div class="alert alert-info" role="alert">
                            <?php echo $st_msg; ?>
                        </div>
                        <?php
                    }
                ?>
            </div>
        </div>              
        <!-- /. ROW  -->
        <hr />
        <div class="row">
            <div class="col-md-8">
                <div class="chat-panel panel panel-default chat-boder chat-panel-head" >
                    <div class="panel-heading">
                        <i class="fa fa-comments fa-fw"></i> Latest News Added
                        <div class="btn-group pull-right">
                            <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-chevron-down"></i>
                            </button>
                            <ul class="dropdown-menu slidedown">
                                <li><a href="#"><i class="fa fa-refresh fa-fw"></i> Refresh</a></li>
                                <li><a href="#"><i class="fa fa-list fa-fw"></i> Refine</a></li>
                                <li><a href="#"><i class="fa fa-search fa-fw"></i> Search</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="panel-body">
                        <table class="table table-striped table-bordered table-hover" style="cursor: pointer;">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Uploaded</th>
                                    <th>Transmission</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                <?php
                                    $db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
                                    $content  = $db->rawQuery("SELECT c.*, s.title FROM tblcontent c, tblnews_svc s WHERE c.svc_id=s.id ORDER BY created DESC");

                                    if(sizeof($content) > 0){
                                        foreach ($content as $row) {
                                            $tag = '<i class="fa fa-check fa-fw"></i> SENT';
                                            if($row['status'] == 0){
                                                $tag = '<i class="fa fa-clock-o fa-fw"></i> PENDING';
                                            }
                                            elseif ($row['status'] == 1) {
                                                $tag = '<i class="fa fa-warning fa-fw"></i> PAUSED';
                                            }

                                            echo '<tr onclick="javascript:showContentDetail('. $row['id'] .');"><td>'. $row['title'] .'</td>';
                                            echo '<td>'. $row['created'] .'</td>';
                                            echo '<td>'. $row['to_send'] .'</td>';
                                            echo '<td>'. $tag .'</td></tr>';
                                        }
                                    }
                                    else{
                                        ?><td colspan="4"><em><center>-- No news content found. Click <a href="index.php?p=sms">here</a> to add news content --</center></em></td><?php
                                    }
                                ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel chat-panel panel-default" >
                    <div class="panel-heading">
                        <i class="fa fa-envelope fa-fw"></i> Selected News Content
                        <div class="btn-group pull-right">
                            <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-chevron-down"></i>
                            </button>
                            <ul class="dropdown-menu slidedown">
                                <li><a href="#"><i class="fa fa-refresh fa-fw"></i> Refresh</a></li>
                                <li><a href="#"><i class="fa fa-list fa-fw"></i> Refine</a></li>
                                <li><a href="#"><i class="fa fa-search fa-fw"></i> Search</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div id="st_detail">
                            <center><i class="fa fa-hand-o-left fa-fw"></i>Click on an entry in the left panel to view it here.</center>
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