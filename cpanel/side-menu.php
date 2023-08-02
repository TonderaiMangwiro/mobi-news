<?php
$ar_cont = array('services','svc','news-content','sms','mms','wap');
$ar_subs = array('subscriptions','trace','inbound');
$ar_stat = array('stats');
$ar_sett = array('users','usr','profile','log','inbox');

?>
<nav class="navbar-default navbar-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav" id="main-menu">
			<li class="text-center">
                <img src="assets/img/side-img.png" class="user-image img-responsive"/>
			</li>
            <li>
                <a <?php if($page == 'home') echo 'class="active-menu"'; ?> href="index.php">
                    <i class="fa fa-home fa-3x"></i> Overview 
                </a> <!-- all -->
            </li>
                <li>
                    <a <?php if(in_array($page, $ar_cont)) echo 'class="active-menu"'; ?> href="#">
                        <i class="fa fa-envelope-o fa-3x"></i> News Content<span class="fa arrow"></span>
                    </a>
                    <ul class="nav nav-second-level">
                        <li><a href="index.php?p=news-content">Manage News Content</a></li> <!-- all -->
                        
                        <?php if($oUSR->get_type() <= 2) { ?><!-- editors + admin -->
                        <li><a href="index.php?p=sms">Add News Content</a></li> 
                        <?php } ?>
                        
                        <?php if($oUSR->get_type() <= 2) { ?><!-- editors + admin -->
                        <li>
                            <a href="#">News Services<span class="fa arrow"></span></a>
                            <ul class="nav nav-third-level">
                                <li><a href="index.php?p=services">Manage News Services</a></li> <!-- all -->
                                <?php if($oUSR->get_type() == 1) { ?><!-- admin -->
                                <li><a href="index.php?p=svc">Create News Service</a></li>
                                <?php } ?>
                            </ul>
                        </li>
                        <?php } ?>
                    </ul>
                </li>
                <li>
                    <a <?php if(in_array($page, $ar_subs)) echo 'class="active-menu"'; ?> href="#">
                        <i class="fa fa-group fa-3x"></i> Subscriptions<span class="fa arrow"></span>
                    </a>
                    <ul class="nav nav-second-level">
                        <li><a href="index.php?p=subscriptions">Manage Subscriptions</a></li> <!-- all -->
                        <?php if($oUSR->get_type() == 1) { ?><!-- admin -->
                        <li><a href="index.php?p=trace">Subscriber Trace</a></li> 
                        <li><a href="#">Billing</a></li>
                        <?php } ?> 
                    </ul>
                </li>

                <?php if($oUSR->get_type() == 1) { ?><!-- admin -->
                <li>
                    <a <?php if(in_array($page, $ar_stat)) echo 'class="active-menu"'; ?> href="index.php?p=stats">
                        <i class="fa fa-dashboard fa-3x"></i> Statistics 
                    </a> <!-- admin -->
                </li>
                <?php } ?>
            <li>
                <a <?php if(in_array($page, $ar_sett)) echo 'class="active-menu"'; ?> href="#">
                    <i class="fa fa-gear fa-3x"></i> Tools<span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level">
                    
                    <li><a href="index.php?p=profile">Change Logons</a></li> <!-- all -->
                    <li><a href="index.php?p=inbox">Customer Feedback</a></li>  <!-- all -->
                    <li><a href="index.php?p=log&t=api">SDP API Log</a></li> <!-- all -->

                    <?php if($oUSR->get_type() == 1) { ?><!-- admin -->
                    <li>
                        <a href="#">NCP Users<span class="fa arrow"></span></a> <!-- admin -->
                        <ul class="nav nav-third-level">
                            <li><a href="index.php?p=users">Manage NCP Users</a></li><!-- admin -->
                            <li><a href="index.php?p=usr">Create New User</a></li><!-- admin -->
                            <li><a href="index.php?p=log&t=usr">NCP Users Log</a></li>
                        </ul>
                    </li>
                    <?php } ?>
                </ul>
            </li>
        </ul>
    </div>
</nav><!-- /. NAV SIDE  -->
