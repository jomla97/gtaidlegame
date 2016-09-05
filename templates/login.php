<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>SecuroServ - Login</title>
    <link rel="shortcut icon" href="favicon.png?v=1"/>
    <link rel="stylesheet" href="css/login.css">
  </head>
  <body>
    <form id="login" action="index.php?page=login" method="post">
      <?php
        if(isset($login_error)){
          echo '
            <div id="error">
              <p>' . $login_error . '</p>
            </div>
          ';
        }
       ?>
      <img id="securoserv-logo" src="res/securoserv-logo.png" alt="SecuroServ logo">
      <p>PLEASE LOG IN</p>
      <div class="inner-wrapper">
        <span>Username</span><input class="textfield" type="text" name="username" maxlength="64" required>
      </div>
      <br><br>
      <div class="inner-wrapper">
        <span>Password</span><input class="textfield" type="password" name="password" maxlength="64" required>
      </div>
      <a href="index.php?page=register"><p id="register-link">Don't have an account?</p></a>
      <input id="login-button" class="button" type="submit" value="Log in">
      <p id="version-number">VERSION 1.0</p>
    </form>
  </body>
</html>
