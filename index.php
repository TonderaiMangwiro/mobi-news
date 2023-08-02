<?php
require_once ('include/config.php');
require_once ('include/MysqliDb.php');
require_once ('include/functions.php');
require_once ('include/user.php');

if (isset($_SESSION['ncp-id-el']) && isset($_SESSION['ncp-usr-el'])) {
  header('Location: cpanel/');
  exit;
}

/*
------------------------------------------
PROCESS LOGIN
------------------------------------------
*/
$st_msg   = "";

if (isset($_POST['st_submit'])) {
  $db       = new MysqliDb ($ncp_host, $ncp_usr, $ncp_pwd, $ncp_db);
  $params   = Array(addslashes($_POST['st_user']), md5(addslashes($_POST['st_pwd'])));
  $users    = $db->rawQuery("SELECT id, u2, type FROM tbluser WHERE u2 = ? AND x3 = ?", $params);

  if(sizeof($users) > 0){
    $logon  = $users[0];
    $oUSR   = new User($logon['id']);

    $_SESSION['ncp-id-el']   = $oUSR->get_id();
    save_activity($oUSR->get_id(), $action_code=0, "Logged into Control Panel");
    header('Location: cpanel/');
    exit;
  }
  else{
    $st_msg = 'Bad username or password, please retry';
  }
}

if (isset($_GET['p'])){
  if (addslashes($_GET['p']) == 'login') {
    $st_msg = 'Please enter your credentials to proceed to cPanel';
  }
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="images/favicon.png">

    <title>NCP : Signin</title>

    <!-- Bootstrap core CSS -->
    <link href="style/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="style/signin.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="style/ie-emulation-modes-warning.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

    <div class="container" style="height:500px;width:500px;">
      <?php // echo md5('admin'); ?>
      <div class="panel panel-danger">
        <div class="panel-heading">
          <center>
            <img src="images/logo.png"  width="300"><br/>
            <h4 style="color:#000;">News Content Platfrom</h4>
          </center>
        </div>
        <div class="panel-body">
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
          <form class="form-signin" action="" method="POST">

            <h3 class="form-signin-heading">Please sign in</h3>

            <label for="st_user" class="sr-only">Username or Email address:</label>
            <input type="text" name="st_user" class="form-control" placeholder="User ID" required autofocus 
                    value="<?php if(isset($_POST['st_user'])) echo addslashes($_POST['st_user']); ?>"><br/>

            <label for="st_pwd" class="sr-only">Password:</label>
            <input type="password" name="st_pwd" class="form-control" placeholder="Password" required>
<!--
            <div class="form-group">
              <label for="st_account">Choose Account:</label>
              <select class="form-control" name="st_account" required>
                <option value="amh">AlphaMedia ZW</option>
                <option value="mgz">M&G South Africa</option>
                <option value="mga">M&G Africa</option>
              </select>
            </div>
-->
            <div class="checkbox">
              <label>
                <input type="checkbox" name="st_rem" value="remember-me"> Remember me
              </label>
            </div>

            <button type="submit" name="st_submit" class="btn btn-lg btn-danger btn-block">Sign in</button>
          </form>

          <br/><br/><br/>

          <center>
            <img src="images/mg.jpg" width="100">
            <img src="images/nd.jpg" width="100" style="margin-left:10px;"><br/><br/>
            <img src="images/vd.jpg"  width="80">
            <img src="images/eco.png" width="80" style="margin-left:5px;">
            <!-- <img src="images/tel.jpg" width="80" style="margin-left:5px;">
            <img src="images/net.jpg" width="80" style="margin-left:5px;">-->
            <img src="images/mtn.png" height="21" style="margin-left:5px;">
          </center>

        </div>
      </div>



    </div> <!-- /container -->


    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="style/ie10-viewport-bug-workaround.js"></script>
  </body>
</html>
