<?php

  session_start();
  ob_start();

  require "includes/model.php";

  date_default_timezone_set("UTC");


  if(!isset($_GET['page']) || $_GET['page'] == ""){
    if(logged_in()){
      check_active_shipment();

      if(company_name_is_set() == false){
        if(isset($_POST['company_name'])){
          change_company_name($_POST['company_name'], true);
        }
        require "templates/set_company_name.php";
      }
      else if(isset($_GET['action'])){
        if($_GET['action'] == "buy_crates" && isset($_POST['type']) && isset($_POST['quantity'])){
          buy_crates($_POST['type'], $_POST['quantity']);
        }
        else if($_GET['action'] == "sell_crates"){
          sell_crates();
        }
        else if($_GET['action'] == "buy_warehouse" && isset($_GET['id'])){
          buy_warehouse($_GET['id']);
        }
        else if($_GET['action'] == "upgrade" && isset($_GET['vehicle']) && isset($_GET['type'])){
          upgrade($_GET['vehicle'], $_GET['type']);
        }
        else if($_GET['action'] == "upgrade" && isset($_GET['subject']) && isset($_GET['type'])){
          upgrade_team($_GET['type']);
        }
      }
      else{
        require "game.php";
      }
    }
    else{
      header("location:index.php?page=login");
    }
  }

  else if(isset($_GET['page']) && $_GET['page'] == "summary"){
    if(logged_in()){
      require "summary.php";
    }
    else{
      header("location:index.php");
    }
  }


  else if(isset($_GET['page']) && $_GET['page'] == "login"){
    if(logged_in()){
      header("location:index.php");
    }
    else if(isset($_POST['username']) && isset($_POST['password'])){
      $login_error = login($_POST['username'], $_POST['password']);
    }
    require "templates/login.php";
  }


  else if(isset($_GET['page']) && $_GET['page'] == "register" && !logged_in()){
    if(logged_in()){
      header("location:index.php");
    }
    else if(isset($_POST['username']) && isset($_POST['password'])){
      $register_error = register($_POST['username'], $_POST['password']);
    }
    require "templates/register.php";
  }


  else if(isset($_GET['page']) && $_GET['page'] == "logout"){
    if(logged_in()){
      logout();
    }
    header("location:index.php");
  }

  else if(isset($_GET['page']) && $_GET['page'] == "cheater"){
    cheater();
    require "templates/cheater.php";
  }

?>
