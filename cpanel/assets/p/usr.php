<?php

// Default password: <b>NCP123news</b> f9d1b6374066bf0ee87a894fa39df8d3

$st_msg          = "";
$st_id           = "";
$st_user         = "";
$st_account      = 1;
$st_access       = 1;

if (isset($_POST['st_submit'])) {
    $st_id       = addslashes($_POST['st_id']);
    $st_user     = addslashes($_POST['st_user']);
    $st_account  = addslashes($_POST['st_account']);
    $st_access   = addslashes($_POST['st_access']);
    
    if ($st_id == "") {
        # insert code...
    } 
    else {
        # update code...
    }
    
    $sql = "INSERT INTO tbluser (id,acc_id,u2,x3,type,status,added) 
                   VALUES( 0,$st_account,'$st_user','". md5('Amhncp2017') ."',$st_access,0,CURRENT_TIMESTAMP)";
    
    $res = mysql_query($sql) or die(mysql_error());

    // save_activity($_SESSION['ncp-id-el'],'Saved user: $st_user');

    if ($res) {
        $st_msg          = "<b>Success!</b> The user <b>". $st_user ."</b> was successfuly saved.";
        $st_id           = "";
        $st_user         = "";
        $st_account      = 1;
        $st_access       = 1;
    }

}

?>
<div id="page-wrapper" >
    <div id="page-inner">
        <div class="row">
            <div class="col-md-12">
                <h2><i class="fa fa-user fa-fw"></i> Add/Edit NCP User</h2>   
                <!-- <h5>Welcome Jhon Deo , Love to see you back. </h5> -->
            </div>
        </div><!-- /. ROW  -->
        
        <hr />

        <div class="row">
            <div class="col-md-6 col-sm-12 col-xs-12">
                <div class="panel panel-default" >
                    <div class="panel-heading">
                        <i class="fa fa-edit fa-fw"></i> NCP User's Details
                    </div>
                    <div class="panel-body">
                        <form action="" method="POST">
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
                            <div class="form-group">
                                <label class="control-label">Username:</label><!-- Networks -->
                                <input name="st_user" type="text" class="form-control" value="<?php echo $st_user; ?>" />
                                <input name="st_id" type="hidden" class="form-control" value="<?php echo $st_id; ?>" />
                            </div>

                            <div class="form-group">
                                <label class="control-label">Account:</label><!-- Networks -->
                                <br/>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <select name="st_account" class="form-control">
                                                <option value="1" <?php if($st_account=="1") { echo "selected"; } ?> >AMH</option>
                                                <option value="2" <?php if($st_account=="2") { echo "selected"; } ?> >M&G</option>
                                                <option value="3" <?php if($st_account=="3") { echo "selected"; } ?> >ECONET</option>
                                            </select>
                                        </div>
                                    </div><!-- /.col-lg-6 -->
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label">Access Level:</label><!-- Networks -->
                                <br/>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <select name="st_access" class="form-control">
                                                <option value="1" <?php if($st_access==1) { echo "selected"; } ?> >Administrator</option>
                                                <option value="2" <?php if($st_access==2) { echo "selected"; } ?> >Editorial</option>
                                                <option value="3" <?php if($st_access==3) { echo "selected"; } ?> >Help Desk</option>
                                            </select>
                                        </div>
                                    </div><!-- /.col-lg-6 -->
                                </div>
                            </div>

                            <div class="form-group pull-right">
                                <button type="submit" class="btn btn-danger" name="st_submit">
                                    <span class="glyphicon glyphicon-floppy-disk"></span> Save User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-12 col-xs-12">
                <div id="smsOpt">&nbsp;</div>
            </div>
        </div>
        <!-- /. ROW  -->
    </div>
    <!-- /. PAGE INNER  -->
</div>
<!-- /. PAGE WRAPPER  -->