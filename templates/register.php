<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>SecuroServ - Register</title>
    <link rel="shortcut icon" href="favicon.png?v=1"/>
    <link rel="stylesheet" href="css/login.css">
  </head>
  <body>
    <form id="login" action="index.php?page=register" method="post">
      <?php
        if(isset($register_error)){
          echo '
            <div id="error">
              <p>' . $register_error . '</p>
            </div>
          ';
        }
       ?>
      <img id="securoserv-logo" src="res/securoserv-logo.png" alt="SecuroServ logo">
      <p>PLEASE REGISTER</p>
      <div class="inner-wrapper">
        <span>Username</span><input class="textfield" type="text" name="username" maxlength="64" required>
      </div>
      <br><br>
      <div class="inner-wrapper">
        <span>Password</span><input class="textfield" type="password" name="password" maxlength="64" required>
      </div>
      <a href="index.php?page=login"><p id="register-link">Already have an account?</p></a>
      <input id="login-button" class="button" type="submit" value="Register">
      <p id="version-number">VERSION 1.0</p>
    </form>
  </body>
</html>
