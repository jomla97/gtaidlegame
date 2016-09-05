<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>CEO Panel</title>
    <link rel="shortcut icon" href="favicon.png?v=1"/>
    <link rel="stylesheet" href="css/main.css">

    <!-- JQUERY -->
    <script src="js/libs/jquery-3.1.0.min.js"></script>
    <script src="js/libs/jquery-ui-1.12.0/jquery-ui.min.js"></script>

    <!-- LIBS AND PLUGINS -->
    <script src="js/libs/jquery-mousewheel-3.1.13/jquery.mousewheel.min.js"></script>
    <script src="js/libs/mapbox.js/jquery.mapbox.js"></script>
    <script src="js/libs/countdown/jquery.countdown.min.js"></script>
    <script src="js/libs/jquery.ui.touch-punch.min.js"></script>

    <!-- GAME JS -->
    <script src="js/game_logic/main.js"></script>
    <script src="js/game_logic/device-ui.js"></script>
    <script src="js/game_logic/buttons.js"></script>
  </head>
  <body>
    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

      ga('create', 'UA-74850113-3', 'auto');
      ga('send', 'pageview');

    </script>
    <div id="loading">
      <p>Loading, please wait...</p>
    </div>
    <main>
      <div id="map-wrapper">
        <img id="map" src="res/los_santos_map.png" alt="Los Santos Map">
        <div id="map-content">
          <!--<div class="map-marker owned" id="warehouse1"></div>
          <div class="map-marker for-sale" id="warehouse2"></div>-->

          <?php
            $pdo = pdo();
            $username = $_SESSION['username'];

            foreach($pdo->query("SELECT * FROM warehouses") as $row){
              $warehouse_id = $row['id'];
              $statement = $pdo->prepare("SELECT * FROM warehouse_contracts WHERE warehouse_id LIKE '$warehouse_id' AND username LIKE '$username'");
              $statement->execute();
              $rowCount = $statement->rowCount();

              //find out the size of the warehouse
              if($row['slots'] < 64){
                $size = "small";
              }
              else if($row['slots'] >= 64 && $row['slots'] < 256){
                $size = "medium";
              }
              else{
                $size = "large";
              }

              //find out if it's owned by the user
              if($rowCount>0){
                echo '<div class="map-marker owned ' . $size . '" id="warehouse' . $row['id'] . '" style="top: ' . $row['y'] . 'px; left: ' . $row['x'] . 'px;"></div>';
              }
              else{
                echo '<div class="map-marker for-sale ' . $size . '" id="warehouse' . $row['id'] . '" style="top: ' . $row['y'] . 'px; left: ' . $row['x'] . 'px;"></div>';
              }
            }
          ?>
        </div>
      </div>
      <div style="background-image: url('res/radar-overlay.png'); width: 100%; height: 100%; position: fixed; background-size: cover; opacity: 0.2; pointer-events: none;"></div>

      <div id="HUD">
        <div id="hamburger-menu-toggle">
          <div></div>
          <div></div>
          <div></div>
        </div>
        <div id="hamburger-menu">
          <ul>
            <li class="buy-stock-button">BUY</li>
            <li class="sell-stock-button">SELL</li>
            <li class="upgrades-button">UPGRADES</li>
            <li class="summary-button">SUMMARY</li>
            <a href="index.php?page=logout"><li>LOGOUT</li></a>
          </ul>
        </div>

        <a href="index.php?page=logout"><div id="logout" class="button">LOGOUT</div></a>

        <?php
          if(isset($_SESSION['admin']) && $_SESSION['admin'] === "TRUE"){
            echo '
              <div id="admin-stats">
                <h1>map.x: <span id="map_x"></span><br>map.y: <span id="map_y"></span></h1>
                <h1>map.height: <span id="map_height"></span><br>map.width: <span id="map_width"></span><br>mouse.x: <span id="mouse_x"></span><br>mouse.y: <span id="mouse_y"></span></h1>
              </div>
            ';
          }
        ?>

        <h1 id="cash">
          <?php
            $pdo = pdo();
            $username = $_SESSION['username'];
            $statement = $pdo->prepare("SELECT cash FROM users WHERE username LIKE '$username'");
            $statement->execute();
            $results = $statement->fetch();

            echo '$' . number_format($results['cash']);
          ?>
        </h1>

        <div id="warehouse-toggle">
          <div class="button toggle-selected">ALL</div>
          <div class="button toggle">OWNED</div>
          <div class="button toggle">SMALL</div>
          <div class="button toggle">MEDIUM</div>
          <div class="button toggle">LARGE</div>
        </div>

        <div id="stock-actions">
          <?php
            $pdo = pdo();
            $username = $_SESSION['username'];
            $statement = $pdo->prepare("SELECT * FROM shipments WHERE status LIKE 'active' AND username LIKE ?");
            $statement->bindParam(1, $username);
            $statement->execute();
            $row_count = $statement->rowCount();

            if($row_count == 0){
              echo '
                <div class="button buy-stock-button">BUY</div>
              ';

              $statement = $pdo->prepare("SELECT * FROM storage WHERE username LIKE ?");
              $statement->bindParam(1, $username);
              $statement->execute();
              $row_count = $statement->rowCount();
              if($row_count > 0){
                echo '
                  <div class="button sell-stock-button">SELL</div>
                ';
              }
              else{
                echo '
                  <div class="button sell-stock-button disabled">SELL</div>
                ';
              }
            }
            else{
              echo '
                <div class="button buy-stock-button disabled">BUY</div>
                <div class="button sell-stock-button disabled">SELL</div>
              ';
            }
          ?>
          <div class="button upgrades-button">UPGRADES</div>
        </div>

        <div id="page-toggle">
          <div class="button toggle">SUMMARY PAGE</div>
          <div class="button toggle-selected">WAREHOUSE MAP</div>
        </div>

        <div class="dialog" id="owned-warehouse-dialog">
          <div class="close-button">x</div>
        </div>

        <div class="dialog" id="buy-warehouse-dialog">
          <div class="close-button">x</div>

          <?php
            /*
            $pdo = pdo();
            $warehouse_id = $_GET['warehouse_id'];
            $statement = $pdo->prepare("SELECT * FROM warehouses WHERE id LIKE ?");
            $statement->bindParam(1, $warehouse_id);
            $statement->execute();
            $warehouse = $statement->fetch();

            echo '
              <h1>WAREHOUSE ' . $warehouse_id . '</h1>
              <p>STATUS:<span class="right-text">FOR SALE</span></p>
              <p>WAREHOUSE SLOTS:<span class="right-text">' . $warehouse['slots'] . '</span></p>
              <p>COST:<span class="right-text">' . $warehouse['price'] . '</span></p>

              <div class="submit-button">BUY</div>
            ';
            */
          ?>
        </div>

        <div class="dialog" id="buy-warehouse-dialog"></div>

        <div class="dialog" id="buy-stock-dialog">
          <div class="close-button">x</div>

          <form action="index.php?action=buy_crates" method="post">
            <h1>BUY CARGO CRATES</h1>
            <p>TYPE</p>
            <div class="selection-list">
              <?php
                $pdo = pdo();
                foreach($pdo->query("SELECT * FROM crate_types") as $row){
                  echo '<div class="button toggle">' . strtoupper($row['name']) . '</div>';
                }
              ?>
              <input class="hidden" name="type" type="text" required>
            </div>
            <p>QUANTITY</p>
            <div class="selection-list">

              <?php
                $pdo = pdo();
                $username = $_SESSION['username'];

                $statement = $pdo->prepare("SELECT SUM(slots) FROM warehouses INNER JOIN warehouse_contracts ON warehouses.id=warehouse_contracts.warehouse_id WHERE username LIKE '$username'");
                $statement->execute();
                $slots = $statement->fetch();

                $statement = $pdo->prepare("SELECT SUM(quantity) FROM storage WHERE username LIKE ?");
                $statement->bindParam(1, $username);
                $statement->execute();
                $crates = $statement->fetch();

                $statement = $pdo->prepare("SELECT * FROM transport_upgrades WHERE username LIKE ? AND type LIKE 'storage'");
                $statement->bindParam(1, $username);
                $statement->execute();
                $storage_upgrades = $statement->rowCount() + 1;

                $available_slots = $slots['SUM(slots)'] - $crates['SUM(quantity)'];

                if($available_slots >= 1 * pow(2, $storage_upgrades)){
                  echo '<div class="button toggle">' . 1 * pow(2, $storage_upgrades) . '</div>';
                }
                else{
                  echo '<div class="disabled">' . 1 * pow(2, $storage_upgrades) . '</div>';
                }

                if($available_slots >= 2 * pow(2, $storage_upgrades)){
                  echo '<div class="button toggle">' . 2 * pow(2, $storage_upgrades) . '</div>';
                }
                else{
                  echo '<div class="disabled">' . 2 * pow(2, $storage_upgrades) . '</div>';
                }

                if($available_slots >= 4 * pow(2, $storage_upgrades)){
                  echo '<div class="button toggle">' . 4 * pow(2, $storage_upgrades) . '</div>';
                }
                else{
                  echo '<div class="disabled">' . 4 * pow(2, $storage_upgrades) . '</div>';
                }

                if($available_slots >= 8 * pow(2, $storage_upgrades)){
                  echo '<div class="button toggle">' . 8 * pow(2, $storage_upgrades) . '</div>';
                }
                else{
                  echo '<div class="disabled">' . 8 * pow(2, $storage_upgrades) . '</div>';
                }

                if($available_slots >= 16 * pow(2, $storage_upgrades)){
                  echo '<div class="button toggle">' . 16 * pow(2, $storage_upgrades) . '</div>';
                }
                else{
                  echo '<div class="disabled">' . 16 * pow(2, $storage_upgrades) . '</div>';
                }

                if($available_slots >= 32 * pow(2, $storage_upgrades)){
                  echo '<div class="button toggle">' . 32 * pow(2, $storage_upgrades) . '</div>';
                }
                else{
                  echo '<div class="disabled">' . 32 * pow(2, $storage_upgrades) . '</div>';
                }

                if($available_slots < 1 * pow(2, $storage_upgrades)){
                  echo '
                    <input class="hidden" name="quantity" type="text" required>
                  </div>
                  <p>RISK:<span class="right-text" id="delivery-risk">0%</span></p>
                  <p>PROFIT MARGIN:<span class="right-text" id="delivery-profit">0%</span></p>
                  <p>PRICE:<span class="right-text" id="delivery-price">$0</span></p>
                  <input class="submit-button disabled" type="submit" value="BUY">
                ';
                }
                else{
                  echo '
                    <input class="hidden" name="quantity" type="text" required>
                  </div>
                  <p>RISK:<span class="right-text" id="delivery-risk">0%</span></p>
                  <p>PROFIT MARGIN:<span class="right-text" id="delivery-profit">0%</span></p>
                  <p>PRICE:<span class="right-text" id="delivery-price">$0</span></p>
                  <input class="submit-button" type="submit" value="BUY">
                ';
                }
              ?>


          </form>
        </div>

        <div class="dialog" id="sell-stock-dialog">
          <div class="close-button">x</div>
          <?php
            $pdo = pdo();
            $profit_margin_from_upgrades = calculate_upgraded_profit_margin();

            $statement = $pdo->prepare("SELECT SUM(price), SUM(price*quantity), SUM(quantity), AVG(profit) FROM storage INNER JOIN crate_types ON storage.crate_type_id=crate_types.id WHERE username LIKE ?");
            $statement->bindParam(1, $username);
            $statement->execute();
            $rows = $statement->fetch();
            $quantity = $rows['SUM(quantity)'];
            $price = $rows['SUM(price)'];
            //$value = round($rows['SUM((price*((profit/100)+1))*quantity)'], 0);
            $original_cost = round($rows['SUM(price*quantity)'], 0);
            $profit_margin = round($rows['AVG(profit)'] + $profit_margin_from_upgrades, 0);
            $value = round(($price*(($profit_margin/100)+1))*$quantity);

            echo '
                <h1>SELL CARGO CRATES</h1>
                <p>CRATES: <span class="right-text">' . $quantity . '</span></p>
                <p>RISK: <span class="right-text"></span></p>
                <p>PROFIT MARGIN: <span class="right-text">' . $profit_margin . '% (' . round($rows['AVG(profit)'], 0) . '+' . $profit_margin_from_upgrades . ')</span></p>
                <p>ORIGINAL COST: <span class="right-text">$' . number_format($original_cost) .'</span></p>
                <p>PROFIT: <span class="right-text">$' . number_format($value - $original_cost) . '</span></p>
                <p>TOTAL VALUE: <span class="right-text">$' . number_format($value) .'</span></p>
                <a href="index.php?action=sell_crates"><div class="submit-button">SELL</div></a>
            ';
          ?>
        </div>

        <?php
          $pdo = pdo();
          $username = $_SESSION['username'];
          $statement = $pdo->prepare("SELECT * FROM shipments WHERE username LIKE '$username' AND status LIKE 'active' LIMIT 1");
          $statement->execute();
          $row_count = $statement->rowCount();
          $row1 = $statement->fetch();

          $statement = $pdo->prepare("SELECT * FROM shipments INNER JOIN crate_types ON shipments.crate_type_id=crate_types.id WHERE username LIKE '$username' AND status LIKE 'active' LIMIT 1");
          $statement->execute();
          $row2 = $statement->fetch();

          if($row_count > 0){
            echo '<div id="active-shipment">';
            if($row1['type'] === "purchase"){
              echo '
                <h1 id="countdown"></h1>
                <p>Incoming shipment of ' . number_format($row2['quantity']) . ' crates of ' . $row2['name'] . '</p>
              ';
            }
            else if($row1['type'] === "sale"){
              echo '
                <h1 id="countdown"></h1>
                <p>Outgoing shipment of ' . number_format($row1['quantity']) . ' crates with a value of $' . number_format($row1['value']) . '</p>
              ';
            }
            echo '
              </div>
              <script type="text/javascript">
                  $("#countdown").countdown("' . $row1['arrival'] . '", function(event) {
                    var arrival = {
                      days: event.strftime("%D"),
                      hours: event.strftime("%H"),
                      minutes: event.strftime("%M"),
                      seconds: event.strftime("%Ss")
                    };

                    if(arrival.days <= 0){
                      arrival.days = "";
                    }
                    else{
                      arrival.days += "d";
                    }

                    if(arrival.hours <= 0){
                      arrival.hours = "";
                    }
                    else{
                      arrival.hours += "h";
                    }

                    if(arrival.minutes <= 0){
                      arrival.minutes = "";
                    }
                    else{
                      arrival.minutes += "m";
                    }

              		  $(this).text(arrival.days + " " + arrival.hours + " " + arrival.minutes + " " + arrival.seconds);

                    if(new Date("' . $row1['arrival'] . '") <= new Date()){
                   	  window.location.replace("index.php");
                    }
              		});

                  $("title").countdown("' . $row1['arrival'] . '", function(event){
                    var arrival = {
                      days: event.strftime("%D"),
                      hours: event.strftime("%H"),
                      minutes: event.strftime("%M"),
                      seconds: event.strftime("%Ss")
                    };

                    if(arrival.days <= 0){
                      arrival.days = "";
                    }
                    else{
                      arrival.days += "d";
                    }

                    if(arrival.hours <= 0){
                      arrival.hours = "";
                    }
                    else{
                      arrival.hours += "h";
                    }

                    if(arrival.minutes <= 0){
                      arrival.minutes = "";
                    }
                    else{
                      arrival.minutes += "m";
                    }

                    $(this).text(arrival.days + " " + arrival.hours + " " + arrival.minutes + " " + arrival.seconds + " - CEO Panel");
              		});
              </script>
            ';
          }
        ?>

        <div class="dialog" id="upgrades-dialog">
          <div class="close-button">x</div>
          <h1>UPGRADES</h1>

          <p>TRANSPORT TRUCKS</p>
          <p style="font-size: 16px;">These trucks are used for <strong>purchases</strong>.</p>
          <?php
            $upgrade_cost = calculate_upgrade_cost("trucks", "speed");

            $pdo = pdo();
            $statement = $pdo->prepare("SELECT cash FROM users WHERE username LIKE ?");
            $statement->bindParam(1, $_SESSION['username']);
            $statement->execute();
            $user = $statement->fetch();

            $statement = $pdo->prepare("SELECT * FROM transport_upgrades WHERE vehicle LIKE 'trucks' AND username LIKE ? AND type LIKE 'speed'");
            $statement->bindParam(1, $username);
            $statement->execute();
            $previous_upgrades = $statement->rowCount();

            $crate_delivery_time = explode('.', 1 * (3 * (1 - ($previous_upgrades * 0.03))));

            if(strpos((string)$crate_delivery_time[0], "-") !== false){
              $crate_delivery_time[0] = 0;
              $crate_delivery_time[1] = 0;
            }

            if(($crate_delivery_time[0] <= 0 && $crate_delivery_time[1] <= 0) || $crate_delivery_time[0] < 0){
              echo '
                <div class="upgrade">
                  <p>SPEED</p><div class="upgrade-button-disabled">UPGRADE</div>
                  <p class="upgrade-description">This will improve the delivery times for purchases.</p>
                  <div class="upgrade-cost">Limit for upgrades reached.</div>
                </div>
              ';
            }
            else if($user['cash'] < $upgrade_cost){
              echo '
                <div class="upgrade">
                  <p>SPEED</p><div class="upgrade-button-disabled">UPGRADE</div>
                  <p class="upgrade-description">This will improve the delivery times for purchases.</p>
                  <div class="upgrade-cost">COST <span class="right-text red-text">$' . number_format($upgrade_cost) . '</span></div>
                </div>
              ';
            }
            else if($user['cash'] >= $upgrade_cost){
              echo '
                <div class="upgrade">
                  <p>SPEED</p><a href="index.php?action=upgrade&vehicle=trucks&type=speed"><div class="upgrade-button">UPGRADE</div></a>
                  <p class="upgrade-description">This will improve the delivery times for purchases.</p>
                  <div class="upgrade-cost">COST <span class="right-text">$' . number_format($upgrade_cost) . '</span></div>
                </div>
              ';
            }

            $upgrade_cost = calculate_upgrade_cost("trucks", "storage");

            $statement = $pdo->prepare("SELECT cash FROM users WHERE username LIKE ?");
            $statement->bindParam(1, $_SESSION['username']);
            $statement->execute();
            $user = $statement->fetch();

            if($user['cash'] >= $upgrade_cost){
              echo '
                <div class="upgrade">
                  <p>STORAGE</p><a href="index.php?action=upgrade&vehicle=trucks&type=storage"><div class="upgrade-button">UPGRADE</div></a>
                  <p class="upgrade-description">This will allow for shipments of larger quantities.</p>
                  <div class="upgrade-cost">COST <span class="right-text">$' . number_format($upgrade_cost) . '</span></div>
                </div>
              ';
            }
            else{
              echo '
                <div class="upgrade">
                  <p>STORAGE</p><div class="upgrade-button-disabled">UPGRADE</div>
                  <p class="upgrade-description">This will allow for shipments of larger quantities.</p>
                  <div class="upgrade-cost">COST <span class="right-text red-text">$' . number_format($upgrade_cost) . '</span></div>
                </div>
              ';
            }
          ?>
          <!--
          <div class="upgrade">
            <p>STORAGE</p><a href="index.php?action=upgrade&vehicle=trucks&type=storage"><div class="upgrade-button">UPGRADE</div></a>
          </div>
          -->

          <br>
          <p>TRANSPORT PLANES</p>
          <p style="font-size: 16px;">These planes are used for <strong>sales</strong>.</p>
          <?php
            $upgrade_cost = calculate_upgrade_cost("planes", "speed");

            $pdo = pdo();
            $statement = $pdo->prepare("SELECT cash FROM users WHERE username LIKE ?");
            $statement->bindParam(1, $_SESSION['username']);
            $statement->execute();
            $user = $statement->fetch();

            $statement = $pdo->prepare("SELECT * FROM transport_upgrades WHERE vehicle LIKE 'planes' AND username LIKE ? AND type LIKE 'speed'");
            $statement->bindParam(1, $username);
            $statement->execute();
            $previous_upgrades = $statement->rowCount();

            $crate_delivery_time = explode('.', 1 * (3 * (1 - ($previous_upgrades * 0.03))));

            if(strpos((string)$crate_delivery_time[0], "-") !== false){
              $crate_delivery_time[0] = 0;
              $crate_delivery_time[1] = 0;
            }

            if(($crate_delivery_time[0] <= 0 && $crate_delivery_time[1] <= 0) || $crate_delivery_time[0] < 0){
              echo '
                <div class="upgrade">
                  <p>SPEED</p><div class="upgrade-button-disabled">UPGRADE</div>
                  <p class="upgrade-description">This will improve the delivery times for sales.</p>
                  <div class="upgrade-cost">Limit for upgrades reached.</div>
                </div>
              ';
            }
            else if($user['cash'] < $upgrade_cost){
              echo '
                <div class="upgrade">
                  <p>SPEED</p><div class="upgrade-button-disabled">UPGRADE</div>
                  <p class="upgrade-description">This will improve the delivery times for sales.</p>
                  <div class="upgrade-cost">COST <span class="right-text red-text">$' . number_format($upgrade_cost) . '</span></div>
                </div>
              ';
            }
            else if($user['cash'] >= $upgrade_cost){
              echo '
                <div class="upgrade">
                  <p>SPEED</p><a href="index.php?action=upgrade&vehicle=planes&type=speed"><div class="upgrade-button">UPGRADE</div></a>
                  <p class="upgrade-description">This will improve the delivery times for sales.</p>
                  <div class="upgrade-cost">COST <span class="right-text">$' . number_format($upgrade_cost) . '</span></div>
                </div>
              ';
            }
          ?>

          <br>
          <p>TEAM & ORGANIZATION</p>
          <p style="font-size: 16px;">These upgrades will improve your organization's different skills.</p>
          <?php
            $upgrade_cost = calculate_team_upgrade_cost("negotiations");

            $pdo = pdo();
            $statement = $pdo->prepare("SELECT cash FROM users WHERE username LIKE ?");
            $statement->bindParam(1, $_SESSION['username']);
            $statement->execute();
            $user = $statement->fetch();

            $statement = $pdo->prepare("SELECT * FROM team_upgrades WHERE username LIKE ? AND type LIKE 'negotiations'");
            $statement->bindParam(1, $username);
            $statement->execute();
            $previous_upgrades = $statement->rowCount();

            if($user['cash'] < $upgrade_cost){
              echo '
                <div class="upgrade">
                  <p>NEGOTIATIONS</p><div class="upgrade-button-disabled">UPGRADE</div>
                  <p class="upgrade-description">This will improve the profit-margin of all sales by 20%.</p>
                  <div class="upgrade-cost">COST <span class="right-text red-text">$' . number_format($upgrade_cost) . '</span></div>
                </div>
              ';
            }
            else if($user['cash'] >= $upgrade_cost){
              echo '
                <div class="upgrade">
                  <p>NEGOTIATIONS</p><a href="index.php?action=upgrade&subject=team&type=negotiations"><div class="upgrade-button">UPGRADE</div></a>
                  <p class="upgrade-description">This will improve the profit-margin of all sales by 20%.</p>
                  <div class="upgrade-cost">COST <span class="right-text">$' . number_format($upgrade_cost) . '</span></div>
                </div>
              ';
            }
          ?>
        </div>

        <div id="warehouse-space">
          <div id="used-space"></div>
          <p>Used warehouse space:
            <?php
              $pdo = pdo();
              $username = $_SESSION['username'];
              $statement = $pdo->prepare("SELECT SUM(slots) FROM warehouses INNER JOIN warehouse_contracts ON warehouses.id=warehouse_contracts.warehouse_id WHERE username LIKE '$username'");
              $statement->execute();
              $row = $statement->fetch();
              $slots = $row['SUM(slots)'];

              $statement = $pdo->prepare("SELECT SUM(quantity) FROM storage WHERE username LIKE '$username'");
              $statement->execute();
              $row = $statement->fetch();
              $crates = $row['SUM(quantity)'];

              if($crates === 0 || !isset($crates)){
                $crates = 0;
              }

              $percentage_used = round(($crates/$slots)*100, 1);

              echo number_format($crates) . '/' . number_format($slots) . ' (' . $percentage_used . '%)';
            ?>
          </p>
            <?php
              echo '
                <script>
                  $("#used-space").css("width", ' . $percentage_used . ' + "%");
                </script>
              ';
            ?>
        </div>
      </div>

      <div id="summary"></div>

      <?php
        if(isset($_SESSION['random_event']) && $_SESSION['random_event']['recent'] === true){
          $robbers = array(
            "Hell's Angels",
            "The Lost",
            "some asian streetpunks",
            "a pair of irish virgins",
            "the russian mob",
            "some methheads",
            "a korean crew"
          );

          if($_SESSION['random_event']['shipment_type'] === "purchase"){
            echo '
              <div class="alert-background"></div>
              <div class="alert">
                <img class="companion-speech" src="res/companion-speeches/random-event.png">
                <h1>Oh no!</h1>
                <p>Boss, I\'ve got some bad news. Your recent shipment of <strong>' . number_format($_SESSION['random_event']['ordered_quantity']) . '</strong> crates was hijacked during transport by ' . $robbers[rand(0, count($robbers) - 1)] . '. They managed to get away with <strong>' . number_format($_SESSION['random_event']['random_event_penalty']) . '</strong> crates. Though, your crew did kill <strong>' . rand(1, 20) . '</strong> of them before they took off with the product.</p>
                <div class="ok-button">OK</div>
              </div>
            ';
          }
          unset($_SESSION['random_event']);
        }
      ?>
    </main>
  </body>
</html>
