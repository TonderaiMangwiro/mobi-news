<?php

$st_msg          = "";
$st_title        = "";
$st_msgid        = "";
$st_priority     = "0";
$st_destinations = "VOD,ECO";
$st_schedule     = "Mon,Tue,Wed,Thu,Fri,Sat,Sun";
$st_times        = "05:00 AM";
$st_schedule_opt = "SC";
$st_medium       = "SMS";

if (isset($_POST['st_submit'])) {
    $st_title           = addslashes($_POST['st_title']);
    $st_msgid           = addslashes($_POST['st_msgid']);
    $st_priority        = addslashes($_POST['st_priority']);
    $st_schedule_opt    = addslashes($_POST['st_schedule_opt']);
    $st_times           = addslashes($_POST['st_times']);
    $st_medium          = addslashes($_POST['st_medium']);

    $st_destinations    = "";
    if(isset($_POST['VOD'])) $st_destinations .= "VOD,";
    if(isset($_POST['MTN'])) $st_destinations .= "MTN,";
    if(isset($_POST['ECO'])) $st_destinations .= "ECO,";
    if(isset($_POST['NET'])) $st_destinations .= "NET,";
    if(isset($_POST['TEL'])) $st_destinations .= "TEL,";

    $st_schedule        = "";
    if($st_schedule_opt == 'SC'){
        if(isset($_POST['Mon'])) $st_schedule .= "Mon,";
        if(isset($_POST['Tue'])) $st_schedule .= "Tue,";
        if(isset($_POST['Wed'])) $st_schedule .= "Wed,";
        if(isset($_POST['Thu'])) $st_schedule .= "Thu,";
        if(isset($_POST['Fri'])) $st_schedule .= "Fri,";
        if(isset($_POST['Sat'])) $st_schedule .= "Sat,";
        if(isset($_POST['Sun'])) $st_schedule .= "Sun";
    }

    $sql = "INSERT INTO tblnews_svc (id,title,msgid,medium,priority,method,networks,days,schedule,added)
                   VALUES( 0,'$st_title','$st_msgid','$st_medium',$st_priority,'$st_schedule_opt','$st_destinations','$st_schedule','$st_times',CURRENT_TIMESTAMP)";

    $res = mysql_query($sql) or die(mysql_error());

    if ($res) {
        save_activity($oUSR->get_id(), $action_code=3, "Created News Service with title: ". $st_title);
        $st_title        = "";
        $st_msgid        = "";
        $st_priority     = "0";
        $st_destinations = "VOD,ECO";
        $st_schedule     = "Mon,Tue,Wed,Thu,Fri,Sat,Sun";
        $st_times        = "05:00 AM";
        $st_schedule_opt = "SC";
        $st_medium       = "SMS";

        $st_msg          = "<b>Success!</b> The service <b>". $st_title ."</b> was successfuly added.";
    }

}

?>
<div id="page-wrapper" >
    <div id="page-inner">
        <div class="row">
            <div class="col-md-12">
                <h2><i class="fa fa-signal fa-fw"></i> Add/Edit News Service</h2>
                <!-- <h5>Welcome Jhon Deo , Love to see you back. </h5> -->
            </div>
        </div><!-- /. ROW  -->

        <hr />

        <div class="row">
            <div class="col-md-6 col-sm-12 col-xs-12">
                <div class="panel panel-default" >
                    <div class="panel-heading">
                        <i class="fa fa-edit fa-fw"></i> News Service Detail
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
                                <label class="control-label">Service Title:</label><!-- Networks -->
                                <input name="st_title" type="text" class="form-control" value="<?php echo $st_title; ?>" />
                            </div>

                            <div class="form-group">
                                <label class="control-label">Service Medium:</label><!-- Networks -->
                                <br/>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <select name="st_medium" class="form-control">
                                                <option value="SMS">SMS</option>
                                                <option value="MMS">MMS</option>
                                                <option value="WAP">WAP</option>
                                            </select>
                                        </div>
                                    </div><!-- /.col-lg-6 -->
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label">Message ID:</label><!-- Networks -->
                                <br/>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <input name="st_msgid" type="text" class="form-control" value="<?php echo $st_msgid; ?>" />
                                        </div>
                                    </div><!-- /.col-lg-6 -->
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label">Service Priority:</label><!-- Networks -->
                                <br/>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <span class="input-group-btn">
                                                <button class="btn btn-default" type="button" onclick="javascript:updatePriority(-1);"><span class="glyphicon glyphicon-chevron-down"></span></button>
                                            </span>
                                            <input type="text" name="st_priority" id="st_priority" class="form-control" value="0" style="text-align:center;">
                                            <span class="input-group-btn">
                                                <button class="btn btn-default" type="button" onclick="javascript:updatePriority(1);"><span class="glyphicon glyphicon-chevron-up"></span></button>
                                            </span>
                                        </div><!-- /input-group -->
                                    </div><!-- /.col-lg-6 -->
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Network Destinations:</label><br/>
                                <label class="checkbox-inline"><input name="VOD" type="checkbox" <?php if(stristr($st_destinations, 'VOD')) echo 'checked'; ?> /> VODACOM</label>
                                <label class="checkbox-inline"><input name="MTN" type="checkbox" <?php if(stristr($st_destinations, 'MTN')) echo 'checked'; ?> /> MTN</label>
                                <label class="checkbox-inline"><input name="ECO" type="checkbox" <?php if(stristr($st_destinations, 'ECO')) echo 'checked'; ?> /> ECONET</label>
                                <label class="checkbox-inline"><input name="NET" type="checkbox" <?php if(stristr($st_destinations, 'NET')) echo 'checked'; ?> /> NET-ONE</label>
                                <label class="checkbox-inline"><input name="TEL" type="checkbox" <?php if(stristr($st_destinations, 'TEL')) echo 'checked'; ?> /> TELECEL</label>
                            </div>

                            <div class="form-group">
                                <label>Delivery Schedule:</label><br/>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <select name="st_schedule_opt" id="st_schedule_opt" class="form-control" onchange="showSvcsOptions();">
                                                <option value="SC">ON SCHEDULE:</option>
                                                <option value="UR">USER REQUEST</option>
                                                <option value="IM">IMMEDIATELY (Breaking)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" id="st_schedule_div">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label class="checkbox-inline">
                                                <input name="Mon" type="checkbox" <?php if(stristr($st_schedule, 'Mon')) echo 'checked'; ?> /> Mo</label>
                                            <label class="checkbox-inline">
                                                <input name="Tue" type="checkbox" <?php if(stristr($st_schedule, 'Tue')) echo 'checked'; ?> /> Tu</label>
                                            <label class="checkbox-inline">
                                                <input name="Wed" type="checkbox" <?php if(stristr($st_schedule, 'Wed')) echo 'checked'; ?> /> We</label>
                                            <label class="checkbox-inline">
                                                <input name="Thu" type="checkbox" <?php if(stristr($st_schedule, 'Thu')) echo 'checked'; ?> /> Th</label>
                                            <label class="checkbox-inline">
                                                <input name="Fri" type="checkbox" <?php if(stristr($st_schedule, 'Fri')) echo 'checked'; ?> /> Fr</label>
                                            <label class="checkbox-inline">
                                                <input name="Sat" type="checkbox" <?php if(stristr($st_schedule, 'Sat')) echo 'checked'; ?> /> Sa</label>
                                            <label class="checkbox-inline">
                                                <input name="Sun" type="checkbox" <?php if(stristr($st_schedule, 'Sun')) echo 'checked'; ?> /> Su</label>
                                        </div>
                                        <div class="form-group">
                                            <input name="st_times" type="text" class="form-control" value="<?php echo $st_times; ?>" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group pull-right">
                                <button type="submit" class="btn btn-danger" name="st_submit">
                                    <span class="glyphicon glyphicon-floppy-disk"></span> Save Service
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
