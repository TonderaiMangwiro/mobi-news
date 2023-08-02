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
                <h2><i class="fa fa-list fa-fw"></i> NCP Log</h2>   
                <!-- <h5>Welcome Jhon Deo , Love to see you back. </h5> -->
            </div>
        </div><!-- /. ROW  -->
        
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="panel panel-default" >
                    <div class="panel-heading">
                        <?php 
                        if ($type == 'api') {
                            ?><i class="fa fa-link fa-fw"></i> NCP API Log<?php
                        }
                        else{
                            ?><i class="fa fa-users fa-fw"></i> NCP Users Log<?php
                        }
                        ?>
                        <div class="btn-group pull-right">
                            <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-chevron-down"></i>
                            </button>
                            <ul class="dropdown-menu slidedown">
                                <li>
                                    <a href="index.php?p=log&t=usr">
                                        <i class="fa fa-users fa-fw"></i>User Log
                                    </a>
                                </li>
                                <li>
                                    <a href="index.php?p=log&t=api">
                                        <i class="fa fa-link fa-fw"></i>API Log
                                    </a>
                                </li>
                            </ul>
                        </div>

                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                <thead>
                                    <?php
                                    if ($type == 'api') {
                                    ?>
                                        <tr>
                                            <th>Date</th>
                                            <th>Dir</th>
                                            <th>Mobile</th>
                                            <th>IP Address</th>
                                            <th width="60%">URL</th>
                                            <th>SDP Response</th>
                                        </tr>
                                        <?php
                                    }
                                    else{
                                        ?>
                                        <tr>
                                            <th>Date</th>
                                            <th>User</th>
                                            <th>IP Address</th>
                                            <th>Description</th>
                                        </tr>
                                        <?php                                    
                                    }
                                    ?>
                                </thead>
                                <tbody>
                                <?php
                                    $db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
                                    $sql      = "SELECT * FROM tblapilog WHERE dir='IN' ORDER BY id DESC LIMIT 0,100";
                                    if (isset($_GET['t'])) {
                                        if(addslashes($_GET['t']) == 'usr')
                                            $sql = "SELECT * FROM tbl_log WHERE uid>0 ORDER BY id DESC LIMIT 0,50";
                                    }
                                    
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
                                                <td class="center"><?php echo $lg['dir']; ?></td>
                                                <td><?php echo $lg['mobile']; ?></td>
                                                <td><?php echo $lg['ip']; ?></td>
                                                <td style="width:60%;">
                                                    <textarea class="form-control" rows="3" disabled style="width:100%;text-align:left;"><?php echo trim($lg['url']); ?></textarea>
                                                </td>
                                                <td><?php echo $lg['response']; ?></td></tr>
                                            <?php
                                            }
                                            else{
                                                $u  = new User($lg['uid']);
                                                ?>
                                                <td><?php echo $lg['added']; ?></td>
                                                <td><?php echo $u->get_name(); ?></td>
                                                <td><?php echo $lg['ip']; ?></td>
                                                <td><?php echo $lg['action']; ?></td></tr>
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