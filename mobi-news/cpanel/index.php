<?php
require_once ('../include/config.php');
require_once ('../include/MysqliDb.php');
require_once ('../include/functions.php');
require_once ('../include/user.php');

require_once ('../include/smppclass.sm.php');

set_time_limit(0);

if (!isset($_SESSION['ncp-id-el'])) {
    header('Location: ../index.php');
    exit;
}

$oUSR   = new User($_SESSION['ncp-id-el']);

// >>> PAGINATION <<<
$page   = (isset($_GET['p']) && addslashes($_GET['p']) != '') ? addslashes($_GET['p']) : 'home';

if($page=='logout'){
    save_activity($oUSR->get_id(), $action_code=1, "Logged out of Control Panel");
    unset($_SESSION['ncp-id-el']);
    header('Location: ../');
    exit;
}
/*
else{
    switch ($oUSR->get_type()) { // >>> 0-admin, 1-acc_mgr, 2-gen_user
        case 0:
            if(!in_array($page, $pg_adm)) { $page   = '404';   }
            break;
        case 1:
            if(!in_array($page, $pg_mgr)) { $page   = '404';   }
            break;
        case 2:
            if(!in_array($page, $pg_usr)) { $page   = '404';   }
            break;
    }

}

*/

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <link rel="icon" href="../images/favicon.png">

        <title>NCP SMS Platform</title>

        <!-- BOOTSTRAP STYLES-->
        <link href="assets/css/bootstrap.css" rel="stylesheet" />
        <!-- FONTAWESOME STYLES-->
        <link href="assets/css/font-awesome.css" rel="stylesheet" />
        <!-- MORRIS CHART STYLES-->
        <link href="assets/js/morris/morris-0.4.3.min.css" rel="stylesheet" />
        <!-- CUSTOM STYLES-->
        <link href="assets/css/custom.css" rel="stylesheet" />
        <!-- GOOGLE FONTS-->
       <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css' />
    </head>
    <body>
        <div id="wrapper">

            <?php include 'top-bar.php'; ?>

            <?php include 'side-menu.php'; ?>

            <?php include 'assets/p/'. $page .'.php'; ?>

        </div>
        <!-- /. WRAPPER  -->

        <!-- SCRIPTS -AT THE BOTOM TO REDUCE THE LOAD TIME -->
        <!-- JQUERY SCRIPTS -->
        <script src="assets/js/jquery-1.10.2.js"></script>
        <!-- BOOTSTRAP SCRIPTS -->
        <script src="assets/js/bootstrap.min.js"></script>
        <!-- METISMENU SCRIPTS -->
        <script src="assets/js/jquery.metisMenu.js"></script>
        <!-- MORRIS CHART SCRIPTS -->
        <script src="assets/js/morris/raphael-2.1.0.min.js"></script>
        <script src="assets/js/morris/morris.js"></script>
        

        <!-- DATA TABLE SCRIPTS -->
        <script src="assets/js/dataTables/jquery.dataTables.js"></script>
        <script src="assets/js/dataTables/dataTables.bootstrap.js"></script>
            <script>
                $(document).ready(function () {
                    $('#dataTables-example').dataTable();
                });
        </script>
        
        <!-- CUSTOM SCRIPTS -->
        <script src="assets/js/custom.js"></script> 
        <script language="javascript" type="text/javascript">
            $(document).ready(function(){
                $.ajax({
                    url: 'sections/script.php?sec=subscriptions', 
                    dataType: 'JSON',
                    type: 'POST',
                    data: {get_values: true},
                    success: function(response) {
                        Morris.Donut({
                            element: 'morris-donut-chart',
                            data: response,
                            resize: true
                        });
                    }
                });
                $.ajax({
                    url: 'sections/script.php?sec=activations', 
                    dataType: 'JSON',
                    type: 'POST',
                    data: {get_values: true},
                    success: function(response) {
                        Morris.Bar({
                            element: 'morris-bar-chart',
                            data: response,
                            xkey: 'dte',
                            ykeys: ['act', 'dct'],
                            labels: ['Activations', 'Deactivations'],
                            hideHover: 'auto',
                            resize: true
                        });
                    }
                });
                $.ajax({
                    url: 'sections/script.php?sec=dispatch', 
                    dataType: 'JSON',
                    type: 'POST',
                    data: {get_values: true},
                    success: function(response) {
                        Morris.Donut({
                            element: 'morris-dispatch-chart',
                            data: response,
                            resize: true
                        });
                    }
                });
                $.ajax({
                    url: 'sections/script.php?sec=deductions', 
                    dataType: 'JSON',
                    type: 'POST',
                    data: {get_values: true},
                    success: function(response) {
                        Morris.Area({
                            element: 'morris-area-chart',
                            data: response,
                            xkey: 'dte',
                            ykeys: ['fee'],
                            labels: ['Deductions'],
                            pointSize: 2,
                            hideHover: 'auto',
                            resize: true
                        });
                    }
                });
                $.ajax({
                    url: 'sections/script.php?sec=sdp', 
                    dataType: 'JSON',
                    type: 'POST',
                    data: {get_values: true},
                    success: function(response) {
                        Morris.Line({
                            element: 'morris-line-chart',
                            data: response,
                            xkey: 'dt',
                            ykeys: ['in', 'ou'],
                            labels: ['Inbound Notification','Outbound SMS'],
                            hideHover: 'auto',
                            resize: true
                        });
                    }
                });
            });
        </script>

        <script type="text/javascript" src="assets/js/biomp.js"></script>

        <script language="javascript" type="text/javascript">
            function displayServices(){
                var prov = document.getElementById("selProvider").value;
                document.getElementById("selService").options.length = 0;

                switch(prov) {
                    case "1":
                        document.getElementById("selService").options[0] = new Option("LOCAL NEWS", "1", true, false);
                        document.getElementById("selService").options[1] = new Option("REGIONAL NEWS", "2", false, false);
                        break;
                    case "2":
                        document.getElementById("selService").options[0] = new Option("REGIONAL NEWS", "2", true, false);
                        document.getElementById("selService").options[1] = new Option("PAN-AFRICAN NEWS", "3", false, false);
                        break;
                    case "3":
                        document.getElementById("selService").options[0] = new Option("LOCAL NEWS", "1", true, false);
                        document.getElementById("selService").options[1] = new Option("REGIONAL NEWS", "2", false, false);
                        break;
                }
                getClearNetworkOpt();
            }
            function countChars(elem, max_legth, output){
                var characters = "";
                var msg_num = "";
                var rem_chr = "";
                var reNewLines=/[\n\r]/g;

                characters  = document.getElementById("st_sms_text_" + elem).value;
                characters  = characters.replace(reNewLines,"11");
                msg_num     = Math.floor(characters.length / max_legth);

                msg_num++;
                if( (max_legth * (msg_num-1)) - characters.length == 0){
                    msg_num--;
                }
                rem_chr     = (max_legth * msg_num) - characters.length;

                if (msg_num == 0) {
                    document.getElementById("st_count_" + output).innerHTML =  "<b>Message 1</b>, "+ max_legth +" characters remaining.";
                }
                else{
                    document.getElementById("st_count_" + output).innerHTML =  "<b>Message " + msg_num + "</b>, " + rem_chr + " characters remaining.";
                }

                // var text = document.getElementById(elem).value;
                // text = text.replace(/(\r\n|\n|\r)/gm,"<br/>");
                // document.getElementById("st_phone_text").value = text;

            }
            function updatePriority(val){
                var priority = val + (document.getElementById("st_priority").value * 1);

                if(priority < 1){   priority = 0;   }
                if(priority > 5){   priority = 5;   }

                document.getElementById("st_priority").value = priority; // (document.getElementById("st_priority").value * 1) + val);
            }
            function showContentDetail(msg_id){
                document.getElementById("st_detail").innerHTML =  "<center><img src='assets/img/wait.gif'/></center>";
                setTimeout(
                    function() {  getMSGData(msg_id); },
                    1000
                    );
            }
            function getMSGData(st_id){
                var u = "sections/smsOpt.php?st_id="+ st_id;
                $('div#st_detail').load(u);
            }


            function runChecks(){
                document.getElementById("st_console").innerHTML =  "<center><img src='assets/img/wait.gif'/></center>";
                setTimeout(do_checks, 1000);
            }
            function do_checks(){
                var u = "sections/ping.php";
                $('div#st_connectivity').load(u);
            }


            function showSvcsOptions(){
                if(document.getElementById("st_schedule_opt").value == 'SC'){
                    document.getElementById('st_schedule_div').style.display = 'block';
                }
                else{
                    document.getElementById('st_schedule_div').style.display = 'none';
                }
            }
            function togSMSStatus(sm_id,status){
                if (status == 0) {
                    if (confirm('Are you sure you want to suspend sending this content?')) {
                        window.location.href = 'index.php?p=news-content&ssp=' + sm_id;
                    }
                }
                else{
                    window.location.href = 'index.php?p=news-content&ssp=' + sm_id;
                };

            }
            function deleteSMS(sm_id){
                if (confirm('This content will be permanently removed. Do you want to proceed?')) {
                    window.location.href = 'index.php?p=news-content&rmcnt=' + sm_id;
                }
            }
            function getSvcOpt(){
                document.getElementById("smsOpt").innerHTML =  "<center><img src='assets/img/wait.gif'/></center>";
                setTimeout(getOptions, 1000);
            }
            function getOptions(){
                var u = "sections/smsOpt.php?svc="+ document.getElementById("st_svc").value;
                $('div#smsOpt').load(u);
            }
            function getClearNetworkOpt(){
                document.getElementById("smsOpt").innerHTML =  "&nbsp;";
            }
            $(document).ready(function() {
                setTimeout(function() { $("#txtMessage").hide()}, 5000);
            });
            //  End -->
        </script>
    </body>
</html>
