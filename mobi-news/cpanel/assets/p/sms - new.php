<?php
$st_msg      = "";
$st_svc      = "";
$st_msgid    = "";
$st_text     = "";
$st_text2    = "";
$st_dispatch = new DateTime('tomorrow');

if (isset($_POST['st_submit'])) {
    $st_dispatch = DateTime::createFromFormat('Y-m-d H:i:s', addslashes($_POST['st_dispatch']) .' 05:00:00');
    $st_svc      = addslashes($_POST['st_svc']);
    // $st_msgid    = addslashes($_POST['st_msgid']);
    $st_text     = $_POST['st_sms_text'];
    $st_text2    = $_POST['st_sms_text_2'];
    
    // Clean Up The Text:
    $st_text    = str_replace("’", "'", $st_text);
    $st_text    = str_replace('“', '"', $st_text);
    $st_text    = str_replace('”', '"', $st_text);
    $st_text    = utf8_to_gsm0338($st_text);
    $st_text    = addslashes($st_text);
    $st_text2   = utf8_to_gsm0338($st_text2);
    $st_text2   = addslashes($st_text2);

    $sql = "INSERT INTO tblcontent (id,svc_id,data,data_2,status,created,to_send)
                   VALUES( 0,$st_svc,'$st_text','$st_text2',0,CURRENT_TIMESTAMP,'". $st_dispatch->format('Y-m-d H:i:s') ."')";

    $res = mysql_query($sql) or die(mysql_error());
      
      if($st_svc == 4) send_breaking_news_now();
    

    if ($res) {
        $title       = get_news_service_title($st_svc);
        save_activity($oUSR->get_id(), $action_code=7, "Uploaded news content to the service: ". $title);

        $st_dispatch = new DateTime('tomorrow');
        $st_svc      = "";
        $st_msgid    = "";
        $st_text     = "";
        $st_text2    = "";

        $st_msg      = "<b>Success!</b> The news content was successfuly added.";

        //position of the if statement that send breaking news
    }
}
?>
<div id="page-wrapper" >
    <div id="page-inner">
        <div class="row">
            <div class="col-md-12">
                <h2><i class="fa fa-envelope-o fa-fw"></i> Add SMS News</h2>
                <!-- <h5>Welcome Jhon Deo , Love to see you back. </h5> -->
            </div>
        </div><!-- /. ROW  -->

        <hr />

        <div class="row">
            <div class="col-md-6 col-sm-12 col-xs-12">
                <div class="panel panel-default" >
                    <div class="panel-heading">
                        <i class="fa fa-comments fa-fw"></i> SMS News Service
                    </div>
                    <div class="panel-body" style="height: 500px;overflow: auto;">
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
                        <form action="" method="POST" >
                            <div class="form-group">
                                <label class="control-label">Dispatch Date:</label>
                                <input class="form-control" type="text" name="st_dispatch" placeholder="YYYY-MM-DD" value="<?php echo $st_dispatch->format('Y-m-d'); ?>">
                            </div>
                            <div class="form-group">
                                <label class="control-label">Choose News Service:</label><!-- Networks -->
                                <select name="st_svc" id="st_svc" class="form-control" onchange="javascript:getSvcOpt();">
                                    <option value="" <?php if($st_svc == '') echo 'checked'; ?> >-- Choose Service --</option>
                                    <?php
                                        $sql        = "SELECT * FROM tblnews_svc WHERE status=0 AND medium='SMS' ORDER BY title";
                                        $result     = mysql_query($sql) or die('Error fetching services.'); // DEBUG: . mysql_error());

                                        if (mysql_num_rows($result) > 0) {
                                            while($row  = mysql_fetch_assoc($result)){
                                                ?>
                                                <option value="<?php echo $row['id']; ?>" <?php if($row['id'] == $st_svc) echo 'selected'; ?>>
                                                    <?php echo strtoupper($row['title']); ?>
                                                </option>
                                                <?php
                                            }
                                        }
                                    ?>
                                </select>
                            </div>

                            <div id="smsOpt">&nbsp;</div>

                            <div class="form-group has-warning">
                                <label class="control-label">News Content:</label>
                                <textarea name="st_sms_text" id="st_sms_text_1" class="form-control" rows="6" onkeyup="countChars(1,1500,1);"></textarea>
                                <div style="margin-top:10px;" id="st_count_1"></div>
                            </div>

                            <div class="form-group has-warning">
                                <label class="control-label">Additional Content:</label>
                                <textarea name="st_sms_text_2" id="st_sms_text_2" class="form-control" rows="6" onkeyup="countChars(2,1500,2);"></textarea>
                                <div style="margin-top:10px;" id="st_count_2"></div>
                            </div>

                            <hr/>

                            <div class="form-group pull-right">
                                <button type="reset" class="btn btn-primary" onclick="javascript:getClearNetworkOpt();">
                                    <span class="glyphicon glyphicon-refresh"></span> Clear Fields
                                </button>
                                <button type="submit" class="btn btn-danger" name="st_submit">
                                    <span class="glyphicon glyphicon-floppy-disk"></span> Add News
                                </button>

                                <!--
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#preview">
                                    <span class="glyphicon glyphicon-zoom-in"></span> Preview News
                                </button>
                                <div class="modal fade" id="preview" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                                <h4 class="modal-title" id="myModalLabel">News Content Preview</h4>
                                            </div>
                                            <div class="modal-body" style="background:#eee;" id="sm_preview">
                                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut
                                                labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco
                                                laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in
                                                voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat
                                                non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close Preview</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                -->
                            </div>

                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-12 col-xs-12">
                <div class="panel panel-default" >
                    <div class="panel-heading">
                        <span class="glyphicon glyphicon-zoom-in"></span> Preview News Content
                    </div>
                    <div class="panel-body" style="height: 500px;overflow: auto;">
                        <center>
                            <div class="form-group" style="margin-top:10px;margin-bottom:10px;">
                                <textarea style="width:350px;height:623px;background:#eee;font-size: 16px;" id="st_phone_text" class="form-control" rows="20" style="cursor:auto;" readonly><?php echo $row['data']; ?></textarea>
                            </div>
                        </center>
                    </div>
                </div>
            </div>
        </div>
        <!-- /. ROW  -->
    </div>
    <!-- /. PAGE INNER  -->
</div>
<!-- /. PAGE WRAPPER  -->
