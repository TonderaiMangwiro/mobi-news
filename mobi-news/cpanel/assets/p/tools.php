
<div id="page-wrapper" >
    <div id="page-inner">
        <div class="row">
            <div class="col-md-12">
                <h2><i class="fa fa-gear"></i> NCP Platform Tools</h2>   
                <!-- <h5>Welcome Jhon Deo , Love to see you back. </h5> -->
            </div>
        </div><!-- /. ROW  -->
        
        <hr />

        <div class="row">

            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <span class="glyphicon glyphicon-transfer"></span> Connectivity with SDP
                    </div>
                    <div class="panel-body">
                        
                        <div class="row">

                            <div id="st_connectivity">
                                <div class="col-md-3 col-sm-12 col-xs-12">                       
                                    <div class="panel panel-primary text-center no-boder">
                                        <div class="panel-body">
                                            <i class="fa fa-link fa-4x"></i>
                                            <h4>SDP LINK</h4>
                                        </div>
                                        <div id="sm_sdp">
                                            <div class="panel-footer back-footer-gray" style="background:green;color:white;">
                                               <font size="1">ONLINE</font>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-12 col-xs-12">                       
                                    <div class="panel panel-primary text-center no-boder">
                                        <div class="panel-body">
                                            <i class="fa fa-envelope-o fa-4x"></i>
                                            <h4>SMSC</h4>
                                        </div>
                                        <div id="sm_smsc">
                                            <div class="panel-footer back-footer-gray"  style="background:#3276B1;color:white;">
                                               <font size="1">SECONDARY LINK</font>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-12 col-xs-12">                       
                                    <div class="panel panel-primary text-center no-boder">
                                        <div class="panel-body">
                                            <i class="fa fa-usd fa-4x"></i>
                                            <h4>BILLING</h4>
                                        </div>
                                        <div id="sm_bill">
                                            <div class="panel-footer back-footer-gray" style="background:#D2322D;color:white;">
                                               <font size="1">OFFLINE</font>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-12 col-xs-12">                       
                                    <div class="panel panel-primary text-center no-boder">
                                        <div class="panel-body">
                                            <i class="fa fa-bell-o fa-4x"></i>
                                            <h4>NOTIFICATIONS</h4>
                                        </div>
                                        <div class="panel-footer back-footer-gray">
                                           <font size="1">Pending...</font>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <hr style="padding:0;margin:0;padding-bottom:15px;"/>
                                <button class="btn btn-danger pull-right" onclick="javascript:runChecks();">
                                    <span class="glyphicon glyphicon-refresh"></span> Run SDP Tests
                                </button>
                            </div>

                        </div>
                        
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-user"></i> Sections
                    </div>
                    <div class="panel-body">
                        Connectivity tests, change pwd, reports [period deductions, period content] per service, subscribers [manage,trace]
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <span class="glyphicon glyphicon-phone"></span> Whitelist Numbers
                    </div>
                    <div class="panel-body">
                        <?php
                            if (isset($_GET['smw'])) {
                                $sm_w   = addslashes($_GET['smw']);
                                $sql    = "DELETE FROM tblwhitelist WHERE id=$sm_w";
                                $res    = mysql_query($sql) or die('Failed removing whitelist number.'); //.mysql_error());
                            }

                            if (isset($_POST['sm_whitelist'])) {
                                $sm_w   = addslashes($_POST['sm_whitelist']);
                                $sql    = "INSERT INTO tblwhitelist (id,acc_id,num,type) VALUES( 0,0,'$sm_w',1)";
                                $res    = mysql_query($sql) or die('Failed adding new whitelist number.'); //.mysql_error());
                            }

                            $sql    = "SELECT * FROM tblwhitelist";
                            $res    = mysql_query($sql) or die('Error fetching whitelist numbers.'); //mysql_error());
                            if (mysql_num_rows($res) > 0) { 
                                while($row  = mysql_fetch_assoc($res)){
                                    ?>
                                    <div class="btn-group" role="group" aria-label="...">
                                      <a class="btn btn-primary btn-sm"><?php echo $row['num']; ?></a>
                                      <a href="index.php?p=tools&smw=<?php echo $row['id']; ?>" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-remove-circle"></span></a>
                                    </div>
                                    <?php
                                }
                            }
                        ?>
                        <hr/>
                        <h5>Add Number:</h5>
                        <form class="navbar-form navbar-left" role="form" action="" method="POST">
                            <div class="form-group"><input type="text" name="sm_whitelist" class="form-control" placeholder="Add whitelist number"></div>
                            <button type="submit" class="btn btn-danger"><span class="glyphicon glyphicon-floppy-disk"></span> Add Number</button>
                        </form>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-desktop fa-fw"></i> Console
                    </div>
                    <div class="panel-body" style="background:#ccc;color:#202020;">
                        <div id="st_console" style="width:100%;">>_</div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /. ROW  -->
    </div>
    <!-- /. PAGE INNER  -->
</div>
<!-- /. PAGE WRAPPER  -->
