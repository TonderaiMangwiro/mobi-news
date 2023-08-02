
<div id="page-wrapper" >
    <div id="page-inner">
        <div class="row">
            <div class="col-md-12">
                <h2><i class="fa fa-group"></i> Manage Subscriptions</h2>   
                <!-- <h5>Welcome Jhon Deo , Love to see you back. </h5> -->
            </div>
        </div><!-- /. ROW  -->
        
        <hr />

        <div class="row">

            <div class="col-md-9">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-user"></i> Active Subscriptions List
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
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Mobile</th>
                                        <th>Service</th>
                                        <th>Status</th>
                                        <th>Added</th>
                                        <th>Expiry</th>
                                        <th>&nbsp;</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
                                        //$sql      = "SELECT s.id, s.status, mobile, title, ended FROM tblsubscription s, tblsubscriber m, tblnews_svc v WHERE subscriber_id=m.id AND service_id=v.id ORDER BY id DESC";

                                        $sql      = "SELECT DISTINCT s.id, s.status sub_status, u.status usr_status, title, mobile, started, ended  
                                                        FROM tblsubscriber u, tblsubscription s, tblnews_svc v  
                                                        WHERE service_id=v.id AND subscriber_id=u.id  
                                                        ORDER BY usr_status, sub_status, title, id DESC";
                                        
                                        $subs     = $db->rawQuery($sql);
                                        if(sizeof($subs) > 0){
                                            $i = 1;
                                            foreach ($subs as $sub) {
                                                $status = 'Active';
                                                if ($sub['usr_status'] == 1) {
                                                    $status = 'User Suspended';
                                                }
                                                elseif ($sub['sub_status'] == 1) {
                                                    $status = 'Expired';
                                                }

                                                if ($i%2 == 0){ 
                                                    ?><tr class="even gradeC"><?php
                                                }else{
                                                    ?><tr class="odd gradeX"><?php
                                                }
                                                ?>
                                                    <td><?php echo $i; ?></td>
                                                    <td><?php echo $sub['mobile']; ?></td>
                                                    <td><?php echo $sub['title']; ?></td>
                                                    <td><?php echo $status; ?></td>
                                                    <td><?php echo $sub['started']; ?></td>
                                                    <td><?php echo $sub['ended']; ?></td>
                                                    <td style="text-align:right;">
                                                        <a href="#" title="Subscriber Details"><span class="glyphicon glyphicon-list"></span></a>
                                                        <a href="#" title="Subscriber Trace"><span class="glyphicon glyphicon-random"></span></a>
                                                        <a href="#" title="Activate Subscription"><span class="glyphicon glyphicon-refresh"></span></a>
                                                        <a href="#" title="Suspend Subscription"><span class="glyphicon glyphicon-remove-circle"></span></a>
                                                    </td></tr>
                                                <?php
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
