<?php
$type = 'api';
if (isset($_GET['t'])) {
    if(addslashes($_GET['t']) == 'usr'){
        $type = 'usr';
    }
}
?>
<div id="page-wrapper" >
    <div id="page-inner">
        <div class="row">
            <div class="col-md-12">
                <h2><i class="fa fa-comments-o fa-fw"></i> MobiNews Feedback</h2>   
                <!-- <h5>Welcome Jhon Deo , Love to see you back. </h5> -->
            </div>
        </div><!-- /. ROW  -->
        
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="panel panel-default" >
                    <div class="panel-heading">
                        <i class="fa fa-envelope fa-fw"></i> Inbox
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                <thead>
                                    <tr>
                                        <th width="20%">Date</th>
                                        <th>Mobile</th>
                                        <th>Feedback</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                    $db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
                                    $sql      = "SELECT * FROM tblinbox ORDER BY id DESC LIMIT 0,100";
                                    
                                    $logs     = $db->rawQuery($sql);
                                    $i        = 0;
                                    if(sizeof($logs) > 0){
                                        foreach ($logs as $lg) {
                                            if ($i%2 == 0){ 
                                                ?><tr class="even gradeC"><?php
                                            }else{
                                                ?><tr class="odd gradeX"><?php
                                            }

                                            if ($type == 'api') {
                                            ?>
                                                <td><?php echo $lg['added']; ?></td>
                                                <td><?php echo $lg['oa']; ?></td>
                                                <td><?php echo $lg['data']; ?></td></tr>
                                            <?php
                                            }  
                                            $i++;
                                        }
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