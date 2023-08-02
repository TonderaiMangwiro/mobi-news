<div id="page-wrapper" >
    <div id="page-inner">
        <div class="row">
            <div class="col-md-12">
                <h2>AMH News Content Platform (NCP)</h2>
                <h5>
                    Welcome back <b><?php echo $oUSR->get_name(); ?></b>! Happy to see you again. <br/>
                    <h6><em>Your most recent login was: <?php echo get_last_logon($_SESSION['ncp-id-el']); ?></em></h6>
                </h5>
            </div>
        </div>
        <!-- /. ROW  -->
        <hr />

        <div class="row">
            <div class="col-md-3 col-sm-12 col-xs-12">
              <div class="panel panel-default">
                  <div class="panel-heading">
                      This Week
                  </div>
                  <div class="panel-body">
                      <div id="morris-donut-chart"></div>
                  </div>
              </div>
            </div>
            <div class="col-md-6 col-sm-12 col-xs-12">
              <div class="panel panel-default">
                  <div class="panel-heading">
                      Activations &amp; Deactivations (Last 7 days)
                  </div>
                  <div class="panel-body">
                      <div id="morris-bar-chart"></div>
                  </div>
              </div>
            </div>
            
            <div class="col-md-3 col-sm-12 col-xs-12">
              <div class="panel panel-default">
                  <div class="panel-heading">
                      News Dispatched Today
                  </div>
                  <div class="panel-body">
                      <div id="morris-dispatch-chart"></div>
                  </div>
              </div>
            </div>
        </div>
         <!-- /. ROW  -->
        <div class="row">
          <div class="col-md-6 col-sm-12 col-xs-12">
              <div class="panel panel-default">
                  <div class="panel-heading">
                    <?php
                    $db     = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
                    $res    = $db->rawQuery("SELECT SUM(fee) AS t FROM tblsummary WHERE MONTH(added)= MONTH(CURRENT_DATE()) AND YEAR(added)= YEAR(CURRENT_DATE())");
                    $tot    = $res[0]['t'];
                    ?>
                    Last 7 days Deduct Report (<b>Total this month: $<?php echo number_format($tot, 2, '.', ','); //money_format('%i',$tot); //echo date(//get_monthly_total(date()); ?></b>)
                  </div>
                  <div class="panel-body">
                    <div id="morris-area-chart"></div>
                  </div>
              </div>
          </div>
          <div class="col-md-6 col-sm-12 col-xs-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        SDP Transmission (Last 7 days)
                    </div>
                    <div class="panel-body">
                        <div id="morris-line-chart"></div>
                    </div>
                </div>
            </div>

        </div>
        <!-- /. ROW  -->

    <!--
        <div class="panel-body">
            <button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal">
              Click  Launch Demo Modal
            </button>
            <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title" id="myModalLabel">Modal title Here</h4>
                        </div>
                        <div class="modal-body">
                            Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary">Save changes</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    -->
    </div><!-- /. PAGE INNER  -->
</div>
 <!-- /. PAGE WRAPPER  -->
