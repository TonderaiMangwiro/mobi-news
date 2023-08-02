<?php

$db    = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);

$start = date('Y-m-01');
$end   = date('Y-m-d');
if(isset($_POST['start'])){
    $start  = $_POST['start'];
}
if(isset($_POST['end'])){
    $end    = $_POST['end'];
}

?>

<div id="page-wrapper" >
    <div id="page-inner">
        <div class="row">
            <div class="col-md-12">
                <h2>Deduct Statistics</h2>   
                <h5>&nbsp;</h5>
            </div>
        </div>
        <!-- /. ROW  -->
        <hr />
     
        
        
        <div class="row">
            <div class="col-md-12">
                <!-- Advanced Tables -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Monthly Report
                    </div>
                    <div class="panel-body">
                        <div style="padding: 0 0 10px 0; text-align: right;">
                            <form class="form-inline" method="POST" action="">
                              <div class="form-group">
                                <label for="start">Start:</label>
                                <input type="date" class="form-control" name="start" value="<?php echo $start; ?>">
                              </div>
                              <div class="form-group">
                                <label for="end">End:</label>
                                <input type="date" class="form-control" name="end" value="<?php echo $end; ?>">
                              </div>
                              <button type="submit" class="btn btn-default">Display</button>
                            </form>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Activations</th>
                                        <th>Renewals</th>
                                        <th>Deactivations</th>
                                        <th>Deduction</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 

                                $res    = $db->rawQuery("SELECT act,dct,ren,fee,added FROM tblsummary WHERE added>='$start' AND added<='$end'");
                                if(sizeof($res) > 0){
                                    $i  = 0;
                                    $fee= 0;
                                    foreach ($res as $r) {
                                        if($i%2 == 0)
                                            echo '<tr class="even gradeX">';
                                        else
                                            echo '<tr class="odd gradeX">';
                                        ?>
                                        <td><?php echo $r['added']; ?></td>
                                        <td><?php echo number_format($r['act'],0); ?></td>
                                        <td><?php echo number_format($r['ren'],0); ?></td>
                                        <td><?php echo number_format($r['dct'],0); ?></td>
                                        <td>$ <?php echo number_format($r['fee'],2); ?></td></tr>
                                        <?php
                                        $fee = $fee + $r['fee'];
                                        $i++;
                                    }
                                    ?>
                                    <tr class="gradeA">
                                        <td colspan="4">Total:</td>
                                        <td>$ <?php echo number_format($fee,2); ?></td>
                                    </tr>
                                    <?php
                                }

                                ?>
                                </tbody>
                            </table>
                        </div>
                        
                    </div>
                </div>
                <!--End Advanced Tables -->
            </div>
        </div>



    </div>
    <!-- /. PAGE INNER  -->
</div>
<!-- /. PAGE WRAPPER  -->
