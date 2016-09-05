<?php

  function pdo(){
		//Establish a connection to the database using PDO
		$host = "localhost";
		$dbname = "gtaidlegame";
		$user = "gtaidlegame";
		$dbpassword = "Br9GydUzuws6y7Cr";
		$attr = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
		$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
		$pdo = new PDO($dsn, $user, $dbpassword, $attr);
		return $pdo;
	}

  function login($username, $password){
		$pdo = pdo();

		$statement = $pdo->prepare("SELECT * FROM users WHERE username LIKE '$username'");
		$statement->execute();
		$rowCount = $statement->rowCount();

		if($rowCount >= 1){
			//user_id exists!

			$statement = $pdo->prepare("SELECT * FROM users WHERE username LIKE '$username'");
			$statement->execute();
			$row = $statement->fetch();

			if(password_verify($password, $row['password'])){
				//user_id and password is a match!
				$_SESSION['username'] = $username;
        $_SESSION['admin'] = $row['admin'];
				header("location:index.php");
			}
			else if($password == $row['password']){
				//user_id and password is a match, but password is not encrypted!
				$_SESSION['username'] = $username;
        $_SESSION['admin'] = $row['admin'];
				header("location:index.php");
			}
			else{
				//user_id and password does not match!
				return "Username or password is incorrect.";
			}
		}
		else{
			//user_id does not exist!
      return "Username or password is incorrect.";
		}
	}

	//Check if user is logged in
	function logged_in(){
		$pdo = pdo();

		if(isset($_SESSION['username'])){
			return true;
		}
		else{
			if(isset($_SESSION)){
				session_unset();
			}
			return false;
		}
	}

  function register($username, $password){
    $pdo = pdo();

    $statement = $pdo->prepare("SELECT * FROM users WHERE username LIKE '$username'");
    $statement->execute();
    $rowcount = $statement->rowCount();

    if($rowcount>0){
      //username is taken
      return "Username is already in use.";
    }
    else{
      $hash = password_hash($password, PASSWORD_DEFAULT);

      $statement = $pdo->prepare("SELECT price*6 FROM crate_types ORDER BY price ASC LIMIT 1");
      $statement->execute();
      $row = $statement->fetch();
      $cash = $row['price*6'];

      $statement = $pdo->prepare("INSERT INTO users (username, password, cash, admin) VALUES (?, ?, ?, 'FALSE')");
      $statement->bindParam(1, $username);
      $statement->bindParam(2, $hash);
      $statement->bindParam(3, $cash);
      if($statement->execute()){
        login($username, $password);
        starter_pack($username);
        header("location:index.php");
      }
      else{
        print_r($statement->errorInfo());
      }
    }
  }

	//Log out
	function logout(){
		session_unset();
		session_destroy();
	}

  function starter_pack($username){
    $pdo = pdo();
    $statement = $pdo->prepare("SELECT * FROM warehouses ORDER BY slots ASC LIMIT 1");
    $statement->execute();
    $row_count = $statement->rowCount();

    if($row_count > 0){
      $row = $statement->fetch();
      $warehouse_id = $row['id'];
    }
    else{
      $statement = $pdo->prepare("INSERT INTO warehouses (x, y, price, slots) VALUES ('1200', '2155', '30000', '8')");
      $statement->execute();

      $statement = $pdo->prepare("SELECT * FROM warehouses WHERE slots LIKE '8' LIMIT 1");
      $statement->execute();
      $row = $statement->fetch();
      $warehouse_id = $row['id'];
    }

    $statement = $pdo->prepare("INSERT INTO warehouse_contracts (username, warehouse_id) VALUES ('$username', '$warehouse_id')");
    $statement->execute();
  }

  function buy_crates($type, $quantity){
    $pdo = pdo();
    $username = $_SESSION['username'];

    $statement = $pdo->prepare("SELECT SUM(quantity) FROM storage WHERE username LIKE '$username'");
    $statement->execute();
    $rows = $statement->fetch();
    $crates = $rows['SUM(quantity)'];

    $statement = $pdo->prepare("SELECT SUM(slots) FROM warehouses INNER JOIN warehouse_contracts ON warehouses.id=warehouse_contracts.warehouse_id WHERE username LIKE '$username'");
    $statement->execute();
    $rows = $statement->fetch();
    $space = $rows['SUM(slots)'];

    $available_slots = $space - $crates;

    if($available_slots < $quantity){
      header("location:index.php?page=cheater");
    }
    else{
      $statement = $pdo->prepare("SELECT id, price FROM crate_types WHERE name LIKE '$type'");
      $statement->execute();
      $row = $statement->fetch();
      $crate_type_id = $row['id'];
      $cost = $row['price'] * $quantity;

      $statement = $pdo->prepare("SELECT cash FROM users WHERE username LIKE ?");
      $statement->bindParam(1, $username);
      $statement->execute();
      $row = $statement->fetch();

      $statement = $pdo->prepare("SELECT * FROM shipments WHERE status LIKE 'active' AND username LIKE ?");
      $statement->bindParam(1, $username);
      $statement->execute();
      $row_count = $statement->rowCount();

      if($row['cash'] >= $cost && $row_count == 0){
        $statement = $pdo->prepare("SELECT * FROM transport_upgrades WHERE vehicle LIKE 'trucks' AND username LIKE ? AND type LIKE 'speed'");
        $statement->bindParam(1, $username);
        $statement->execute();
        $previous_upgrades = $statement->rowCount();

        $crate_delivery_time = $quantity * (3 * (1 - ($previous_upgrades * 0.03)));

        $statement = $pdo->prepare("SELECT * FROM transport_upgrades WHERE vehicle LIKE 'trucks' AND username LIKE ? AND type LIKE 'speed'");
        $statement->bindParam(1, $username);
        $statement->execute();
        $previous_upgrades = $statement->rowCount();

        $crate_delivery_time = explode('.', $quantity * (3 * (1 - ($previous_upgrades * 0.03))));
        //print_r($crate_delivery_time);

        if(!isset($crate_delivery_time[1]) || $crate_delivery_time[1] === 0){
          $crate_delivery_time[1] = 0;
        }
        else if($crate_delivery_time[1] >= 10){
          $crate_delivery_time[1] = ($crate_delivery_time[1]/100) * 60;
        }
        else{
          $crate_delivery_time[1] = ($crate_delivery_time[1]/10) * 60;
        }

        if(($crate_delivery_time[0] <= 0 && $crate_delivery_time[1] <= 0) || $crate_delivery_time[0] < 0){
          $statement = $pdo->prepare("INSERT INTO shipments (crate_type_id, username, arrival, quantity, type, status) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND), ?, 'purchase', 'active')");
          $statement->bindParam(1, $crate_type_id);
          $statement->bindParam(2, $username);
          $statement->bindParam(3, $quantity);
          $statement->bindParam(4, $quantity);
          $statement->execute();
        }
        else{
          $statement = $pdo->prepare("INSERT INTO shipments (crate_type_id, username, arrival, quantity, type, status) VALUES (?, ?, DATE_ADD(DATE_ADD(NOW(), INTERVAL ? MINUTE), INTERVAL ? SECOND), ?, 'purchase', 'active')");
          $statement->bindParam(1, $crate_type_id);
          $statement->bindParam(2, $username);
          $statement->bindParam(3, $crate_delivery_time[0]);
          $statement->bindParam(4, $crate_delivery_time[1]);
          $statement->bindParam(5, $quantity);
          $statement->execute();
        }

        $cash = $row['cash'] - $cost;

        $statement = $pdo->prepare("UPDATE users SET cash=? WHERE username LIKE ?");
        $statement->bindParam(1, $cash);
        $statement->bindParam(2, $username);
        $statement->execute();

        header("location:index.php");
      }
      else{
        header("location:index.php");
      }
    }
  }

  function sell_crates(){
    $pdo = pdo();
    $username = $_SESSION['username'];

    $statement = $pdo->prepare("SELECT * FROM shipments WHERE status LIKE 'active' AND username LIKE ?");
    $statement->bindParam(1, $username);
    $statement->execute();
    $row_count = $statement->rowCount();

    if($row_count == 0){
      $statement = $pdo->prepare("SELECT * FROM storage WHERE username LIKE ?");
      $statement->bindParam(1, $username);
      $statement->execute();
      $row_count = $statement->rowCount();

      if($row_count > 0){
        $statement = $pdo->prepare("SELECT SUM((price*((profit/100)+1))*quantity), SUM(quantity), AVG(profit), SUM(price) FROM storage INNER JOIN crate_types ON storage.crate_type_id=crate_types.id WHERE username LIKE ?");
        $statement->bindParam(1, $username);
        $statement->execute();
        $rows = $statement->fetch();

        $profit_margin_from_upgrades = calculate_upgraded_profit_margin();

        $quantity = $rows['SUM(quantity)'];
        $price = $rows['SUM(price)'];
        $profit_margin = round($rows['AVG(profit)'] + $profit_margin_from_upgrades, 0);

        $value = round(($price*(($profit_margin/100)+1))*$quantity);
        $quantity = $rows['SUM(quantity)'];

        $statement = $pdo->prepare("SELECT * FROM transport_upgrades WHERE vehicle LIKE 'planes' AND username LIKE ? AND type LIKE 'speed'");
        $statement->bindParam(1, $username);
        $statement->execute();
        $previous_upgrades = $statement->rowCount();

        $crate_delivery_time = explode('.', $quantity * (3 * (1 - ($previous_upgrades * 0.03))));
        //print_r($crate_delivery_time);

        if(!isset($crate_delivery_time[1]) || $crate_delivery_time[1] === 0){
          $crate_delivery_time[1] = 0;
        }
        else if($crate_delivery_time[1] >= 10){
          $crate_delivery_time[1] = ($crate_delivery_time[1]/100) * 60;
        }
        else{
          $crate_delivery_time[1] = ($crate_delivery_time[1]/10) * 60;
        }

        if(($crate_delivery_time[0] <= 0 && $crate_delivery_time[1] <= 0) || $crate_delivery_time[0] < 0){
          $statement = $pdo->prepare("INSERT INTO shipments (username, arrival, quantity, type, status, value) VALUES (?, DATE_ADD(NOW(), INTERVAL ? SECOND), ?, 'sale', 'active', ?)");
          $statement->bindParam(1, $username);
          $statement->bindParam(2, $quantity);
          $statement->bindParam(3, $quantity);
          $statement->bindParam(4, $value);
        }
        else{
          $statement = $pdo->prepare("INSERT INTO shipments (username, arrival, quantity, type, status, value) VALUES (?, DATE_ADD(DATE_ADD(NOW(), INTERVAL ? MINUTE), INTERVAL ? SECOND), ?, 'sale', 'active', ?)");
          $statement->bindParam(1, $username);
          $statement->bindParam(2, $crate_delivery_time[0]);
          $statement->bindParam(3, $crate_delivery_time[1]);
          $statement->bindParam(4, $quantity);
          $statement->bindParam(5, $value);
        }

        if($statement->execute()){
          $statement = $pdo->prepare("DELETE FROM storage WHERE username LIKE ?");
          $statement->bindParam(1, $username);
          $statement->execute();

          header("location:index.php");
        }
        else{
          print_r($statement->errorInfo());
        }
      }
      else{
        header("location:index.php");
      }
    }
    else{
      header("location:index.php");
    }
  }

  function cheater(){
    $pdo = pdo();
    $username = $_SESSION['username'];

    $statement = $pdo->prepare("SELECT cash FROM users WHERE username LIKE ?");
    $statement->bindParam(1, $username);
    $statement->execute();
    $cash = $statement->fetch();
    $cash = $cash['cash'] - 1000;

    $statement = $pdo->prepare("UPDATE users WHERE username LIKE ? SET cash=?");
    $statement->bindParam(1, $username);
    $statement->bindParam(2, $cash);
    $statement->execute();
  }

  function check_active_shipment(){
    $pdo = pdo();
    $username = $_SESSION['username'];
    $statement = $pdo->prepare("SELECT * FROM shipments WHERE username LIKE '$username' AND status LIKE 'active' LIMIT 1");
    $statement->execute();
    $rows = $statement->fetch();
    $id = $rows['id'];

    $statement = $pdo->prepare("SELECT NOW()");
    $statement->execute();
    $server_time = $statement->fetch();

    if($rows['type'] == "purchase"){
      if(strtotime($server_time['NOW()']) >= strtotime($rows['arrival'])){
        $statement = $pdo->prepare("SELECT * FROM crate_types WHERE id LIKE ?");
        $statement->bindParam(1, $rows['crate_type_id']);
        $statement->execute();
        $crate_type = $statement->fetch();

        if(rand(1, 100) <= $crate_type['risk']){
          $random_event_penalty = rand(1, $rows['quantity'] - 1);

          $_SESSION['random_event']['recent'] = true;
          $_SESSION['random_event']['shipment_type'] = "purchase";
          $_SESSION['random_event']['ordered_quantity'] = $rows['quantity'];
          $_SESSION['random_event']['random_event_penalty'] = $random_event_penalty;

          echo '
            <script>
              var options = {
                icon: "favicon.png"
              };
              new Notification("Your order of ' . $rows['quantity'] . ' crates of ' . $crate_type['name'] . ' has been delivered, but was robbed!", options);
            </script>
          ';
        }
        else{
          $random_event_penalty = 0;

          echo '
            <script>
              var options = {
                icon: "favicon.png"
              };
              new Notification("Your order of ' . $rows['quantity'] . ' crates of ' . $crate_type['name'] . ' has been delivered!", options);
            </script>
          ';
        }

        $quantity = $rows['quantity'] - $random_event_penalty;

        $statement = $pdo->prepare("UPDATE shipments SET status='delivered' WHERE id LIKE '$id'");
        $statement->execute();

        $statement = $pdo->prepare("INSERT INTO storage (username, crate_type_id, quantity) VALUES (?, ?, ?)");
        $statement->bindParam(1, $username);
        $statement->bindParam(2, $rows['crate_type_id']);
        $statement->bindParam(3, $quantity);
        $statement->execute();
        //header("location:index.php");
      }
    }
    else if($rows['type'] == "sale"){
      if(strtotime($server_time['NOW()']) >= strtotime($rows['arrival'])){
        $statement = $pdo->prepare("UPDATE shipments SET status='delivered' WHERE id LIKE ?");
        $statement->bindParam(1, $id);
        $statement->execute();

        $statement = $pdo->prepare("SELECT cash FROM users WHERE username LIKE ?");
        $statement->bindParam(1, $username);
        $statement->execute();
        $user = $statement->fetch();
        $cash = $user['cash'] + $rows['value'];

        $statement = $pdo->prepare("UPDATE users SET cash=? WHERE username LIKE ?");
        $statement->bindParam(1, $cash);
        $statement->bindParam(2, $username);
        $statement->execute();

        $statement = $pdo->prepare("SELECT name FROM crate_types WHERE id LIKE ?");
        $statement->bindParam(1, $rows['crate_type_id']);
        $statement->execute();
        $crate_type = $statement->fetch();

        echo '
          <script>
            var options = {
              icon: "favicon.png"
            };
            new Notification("Your shipment of ' . $rows['quantity'] . ' crates has been delivered to the buyer.", options);
          </script>
        ';
        //header("location:index.php");
      }
    }
  }

  function buy_warehouse($id){
    $pdo = pdo();
    $username = $_SESSION['username'];

    $statement = $pdo->prepare("SELECT cash FROM users WHERE username LIKE ?");
    $statement->bindParam(1, $username);
    $statement->execute();
    $user = $statement->fetch();

    $statement = $pdo->prepare("SELECT price FROM warehouses WHERE id LIKE ?");
    $statement->bindParam(1, $id);
    $statement->execute();
    $warehouse = $statement->fetch();

    if($user['cash'] >= $warehouse['price']){
      $statement = $pdo->prepare("SELECT * FROM warehouse_contracts WHERE warehouse_id LIKE ? AND username LIKE ?");
      $statement->bindParam(1, $id);
      $statement->bindParam(2, $username);
      $statement->execute();
      $row_count = $statement->rowCount();

      if($row_count <= 0){
        $cash = $user['cash']-$warehouse['price'];
        $statement = $pdo->prepare("UPDATE users SET cash=? WHERE username LIKE ?");
        $statement->bindParam(1, $cash);
        $statement->bindParam(2, $username);
        $statement->execute();

        $statement = $pdo->prepare("INSERT INTO warehouse_contracts (username, warehouse_id) VALUES (?, ?)");
        $statement->bindParam(1, $username);
        $statement->bindParam(2, $id);
        $statement->execute();
        header("location:index.php");
      }
      else{
        header("location:index.php?page=cheater");
      }
    }
    else{
      header("location:index.php?page=cheater");
    }
  }

  function company_name_is_set(){
    $pdo = pdo();
    $statement = $pdo->prepare("SELECT company_name FROM users WHERE username LIKE ?");
    $statement->bindParam(1, $_SESSION['username']);
    $statement->execute();
    $rows = $statement->fetch();

    if(!isset($rows['company_name']) || $rows['company_name'] == ""){
      return false;
    }
    else{
      return true;
    }
  }

  function change_company_name($company_name, $is_first_setup){
    $pdo = pdo();
    $statement = $pdo->prepare("UPDATE users SET company_name=? WHERE username LIKE ?");
    $statement->bindParam(1, $company_name);
    $statement->bindParam(2, $_SESSION['username']);
    $statement->execute();

    if($is_first_setup == false){
      $statement = $pdo->prepare("SELECT cash FROM users WHERE username LIKE ?");
      $statement->bindParam(1, $_SESSION['username']);
      $statement->execute();
      $user = $statement->fetch();

      $statement = $pdo->prepare("UPDATE users SET cash=? WHERE username LIKE ?");
      $statement->bindParam(1, $user['cash']);
      $statement->bindParam(2, $_SESSION['username']);
      $statement->execute();
    }

    header("location:index.php");
  }

  function calculate_team_upgrade_cost($type){
    $team_initial_upgrade_cost = 100000000; // 100m
    $cost_multiplier = 2;

    $pdo = pdo();
    $statement = $pdo->prepare("SELECT cash FROM users WHERE username LIKE ?");
    $statement->bindParam(1, $_SESSION['username']);
    $statement->execute();
    $user = $statement->fetch();

    $statement = $pdo->prepare("SELECT * FROM team_upgrades WHERE username LIKE ? AND type LIKE ?");
    $statement->bindParam(1, $_SESSION['username']);
    $statement->bindParam(2, $type);
    $statement->execute();
    $previous_upgrades = $statement->rowCount();

    if($previous_upgrades == 0){
      $cost = $team_initial_upgrade_cost;
    }
    else{
      $cost = $team_initial_upgrade_cost * pow($cost_multiplier, $previous_upgrades);
    }

    return $cost;
  }

  function calculate_upgrade_cost($vehicle, $type){
    $truck_initial_upgrade_cost = 20000; // 20k
    $plane_initial_upgrade_cost = 300000; // 300k
    $cost_multiplier = 1.4;

    $pdo = pdo();
    $statement = $pdo->prepare("SELECT cash FROM users WHERE username LIKE ?");
    $statement->bindParam(1, $_SESSION['username']);
    $statement->execute();
    $user = $statement->fetch();

    $statement = $pdo->prepare("SELECT * FROM transport_upgrades WHERE username LIKE ? AND vehicle LIKE ? AND type LIKE ?");
    $statement->bindParam(1, $_SESSION['username']);
    $statement->bindParam(2, $vehicle);
    $statement->bindParam(3, $type);
    $statement->execute();
    $previous_upgrades = $statement->rowCount();

    if($previous_upgrades == 0){
      if($vehicle == "trucks"){
        $cost = $truck_initial_upgrade_cost;
      }
      else if($vehicle == "planes"){
        $cost = $plane_initial_upgrade_cost;
      }
      else if($vehicle == "team"){
        $cost = $team_initial_upgrade_cost;
      }
    }
    else{
      if($vehicle == "trucks" && $type == "speed"){
        $cost = $truck_initial_upgrade_cost * pow($cost_multiplier, $previous_upgrades);
      }
      else if($vehicle == "trucks" && $type == "storage"){
        $cost_multiplier += 0.6;
        $cost = ($truck_initial_upgrade_cost * pow($cost_multiplier, $previous_upgrades));
      }
      else if($vehicle == "planes" && $type == "speed"){
        $cost = $plane_initial_upgrade_cost * pow($cost_multiplier, $previous_upgrades);
      }
      else if($vehicle == "team" && $type == "negotiations"){
        $cost = $team_initial_upgrade_cost * pow($cost_multiplier, $previous_upgrades);
        $cost_multiplier += 1;
      }
    }

    if($vehicle == "trucks" && $type == "storage"){
      $cost = $cost * 25000;
    }

    return $cost;
  }

  function upgrade_team($type){
    $cost = calculate_team_upgrade_cost($type);

    $pdo = pdo();
    $statement = $pdo->prepare("SELECT cash FROM users WHERE username LIKE ?");
    $statement->bindParam(1, $_SESSION['username']);
    $statement->execute();
    $user = $statement->fetch();

    if($user['cash'] >= $cost){
      $new_user_cash = $user['cash'] - $cost;

      $statement = $pdo->prepare("UPDATE users SET cash=? WHERE username LIKE ?");
      $statement->bindParam(1, $new_user_cash);
      $statement->bindParam(2, $_SESSION['username']);
      $statement->execute();

      $statement = $pdo->prepare("INSERT INTO team_upgrades (username, type) VALUES (?, ?)");
      $statement->bindParam(1, $_SESSION['username']);
      $statement->bindParam(2, $type);
      $statement->execute();

      header("location:index.php");
    }
    else{
      header("location:index.php");
    }
  }

  function upgrade($vehicle, $type){
    $cost = calculate_upgrade_cost($vehicle, $type);

    $pdo = pdo();
    $statement = $pdo->prepare("SELECT cash FROM users WHERE username LIKE ?");
    $statement->bindParam(1, $_SESSION['username']);
    $statement->execute();
    $user = $statement->fetch();

    $statement = $pdo->prepare("SELECT * FROM transport_upgrades WHERE vehicle LIKE ? AND username LIKE ? AND type LIKE 'speed'");
    $statement->bindParam(1, $vehicle);
    $statement->bindParam(2, $username);
    $statement->execute();
    $previous_upgrades = $statement->rowCount();

    $crate_delivery_time = explode('.', 1 * (3 * (1 - ($previous_upgrades * 0.03))));

    if(strpos((string)$crate_delivery_time[0], "-") !== false){
      $crate_delivery_time[0] = 0;
      $crate_delivery_time[1] = 0;
    }

    if(($crate_delivery_time[0] <= 0 && $crate_delivery_time[1] <= 0) || $crate_delivery_time[0] < 0){
      header("location:index.php?page=cheater");
    }
    else if($user['cash'] >= $cost){
      $new_user_cash = $user['cash'] - $cost;

      $pdo = pdo();
      $statement = $pdo->prepare("UPDATE users SET cash=? WHERE username LIKE ?");
      $statement->bindParam(1, $new_user_cash);
      $statement->bindParam(2, $_SESSION['username']);
      $statement->execute();

      $statement = $pdo->prepare("INSERT INTO transport_upgrades (username, vehicle, type) VALUES (?, ?, ?)");
      $statement->bindParam(1, $_SESSION['username']);
      $statement->bindParam(2, $vehicle);
      $statement->bindParam(3, $type);
      $statement->execute();

      header("location:index.php");
    }
  }

  function calculate_upgraded_profit_margin(){
    $pdo = pdo();

    $statement = $pdo->prepare("SELECT * FROM team_upgrades WHERE username LIKE ? AND type LIKE 'negotiations'");
    $statement->bindParam(1, $_SESSION['username']);
    $statement->execute();
    $previous_upgrades = $statement->rowCount();

    return $previous_upgrades * 20; // +15% per upgrade
  }

 ?>
