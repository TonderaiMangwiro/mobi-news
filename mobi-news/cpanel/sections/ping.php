<?php

require_once '../include/config.php';

error_reporting(0);

function ping($host,$port=80,$timeout=6){
    $fsock = fsockopen($host, $port, $errno, $errstr, $timeout);
    if ( ! $fsock ){
        return FALSE;
    }
    else{
        return TRUE;
    }
}

$response = "";

if (ping($sdp_test_b)) {
    $response = '<div class="col-md-3 col-sm-12 col-xs-12">                       
                    <div class="panel panel-primary text-center no-boder bg-color-green">
                        <div class="panel-body">
                            <i class="fa fa-usd fa-4x"></i>
                            <h4>BILLING</h4>
                        </div>
                        <div class="panel-footer back-footer-gray">
                           Available.
                        </div>
                    </div>
                  </div>';
}
else {
    $response = '<div class="col-md-3 col-sm-12 col-xs-12">                       
                    <div class="panel panel-primary text-center no-boder bg-color-red">
                        <div class="panel-body">
                            <i class="fa fa-usd fa-4x"></i>
                            <h4>BILLING</h4>
                        </div>
                        <div class="panel-footer back-footer-gray" style="color:#000;">
                           Offline.
                        </div>
                    </div>
                  </div>';
}

exit;
?>