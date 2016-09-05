<?php
  require "../../includes/model.php";
  session_start();
  ob_start();

  $pdo = pdo();
  $statement = $pdo->prepare("SELECT * FROM users WHERE username LIKE ?");
  $statement->bindParam(1, $_SESSION['username']);
  $statement->execute();
  $user = $statement->fetch();

  $statement = $pdo->prepare("SELECT * FROM warehouse_contracts WHERE username LIKE ?");
  $statement->bindParam(1, $_SESSION['username']);
  $statement->execute();
  $warehouse_contracts = $statement->fetch();
  $warehouses_owned = $statement->rowCount();

  $statement = $pdo->prepare("SELECT SUM(value) FROM shipments WHERE username LIKE ? AND status LIKE 'delivered'");
  $statement->bindParam(1, $_SESSION['username']);
  $statement->execute();
  $user_sales = $statement->fetch();

  $statement = $pdo->prepare("SELECT * FROM shipments WHERE username LIKE ? AND status LIKE 'delivered' AND type LIKE 'purchase'");
  $statement->bindParam(1, $_SESSION['username']);
  $statement->execute();
  $collections_completed = $statement->rowCount();

  $statement = $pdo->prepare("SELECT * FROM shipments WHERE username LIKE ? AND status LIKE 'delivered' AND type LIKE 'sale'");
  $statement->bindParam(1, $_SESSION['username']);
  $statement->execute();
  $sales_completed = $statement->rowCount();

  $statement = $pdo->prepare("SELECT SUM(quantity) FROM storage WHERE username LIKE ?");
  $statement->bindParam(1, $_SESSION['username']);
  $statement->execute();
  $user_storage = $statement->fetch();

  $statement = $pdo->prepare("SELECT SUM(slots) FROM warehouses INNER JOIN warehouse_contracts ON warehouses.id=warehouse_contracts.warehouse_id WHERE username LIKE ?");
  $statement->bindParam(1, $_SESSION['username']);
  $statement->execute();
  $user_total_warehouse_slots = $statement->fetch();

  $statement = $pdo->prepare("SELECT SUM(slots) FROM warehouses INNER JOIN warehouse_contracts ON warehouses.id=warehouse_contracts.warehouse_id WHERE username LIKE ?");
  $statement->bindParam(1, $_SESSION['username']);
  $statement->execute();
  $row = $statement->fetch();
  $slots = $row['SUM(slots)'];

  $statement = $pdo->prepare("SELECT SUM(quantity) FROM storage WHERE username LIKE ?");
  $statement->bindParam(1, $_SESSION['username']);
  $statement->execute();
  $row = $statement->fetch();
  $crates = $row['SUM(quantity)'];

  $statement = $pdo->prepare("SELECT * FROM transport_upgrades WHERE username LIKE ? AND vehicle LIKE 'planes'");
  $statement->bindParam(1, $_SESSION['username']);
  $statement->execute();
  $previous_plane_upgrades = $statement->rowCount();

  $statement = $pdo->prepare("SELECT * FROM transport_upgrades WHERE username LIKE ? AND vehicle LIKE 'trucks'");
  $statement->bindParam(1, $_SESSION['username']);
  $statement->execute();
  $previous_truck_upgrades = $statement->rowCount();

  $statement = $pdo->prepare("SELECT * FROM team_upgrades WHERE username LIKE ?");
  $statement->bindParam(1, $_SESSION['username']);
  $statement->execute();
  $previous_team_upgrades = $statement->rowCount();

  if($crates === 0 || !isset($crates)){
    $crates = 0;
  }

  $percentage_used = round(($crates/$slots)*100, 1);

  echo '
    <div class="summary-panel">
      <div id="summary-toggle">
        <div class="button toggle-selected">PERSONAL STATS</div>
        <div class="button toggle">GLOBAL STATS</div>
      </div>

      <h1 class="primary-title">' . $user['company_name'] . '</h1>
      <ul>
        <li>COLLECTIONS COMPLETED <span class="right-text">' . number_format($collections_completed) . '</span></li>
        <li>SALES COMPLETED <span class="right-text">' . number_format($sales_completed) . '</span></li>
        <li>TRUCK UPGRADES <span class="right-text">' . number_format($previous_truck_upgrades) . '</span></li>
        <li>PLANE UPGRADES <span class="right-text">' . number_format($previous_plane_upgrades) . '</span></li>
        <li>TEAM & ORGANIZATION UPGRADES <span class="right-text">' . number_format($previous_team_upgrades) . '</span></li>
      </ul>
    </div>

    <div class="summary-panel">
      <h1>WAREHOUSES OWNED: <span class="red-text">' . number_format($warehouses_owned) . '</span></h1>
      <h1>TOTAL EARNINGS: <span class="red-text">$' . number_format($user_sales['SUM(value)']) . '</span></h1>
    </div>

    <div class="summary-panel">
      <h1 class="primary-title">TOTAL STOCK</h1>
      <h2>FREE SPACE<span class="red-text right-text">HELD</span></h2>
      <h2>' . (100-$percentage_used) . '%<span class="red-text right-text">' . $percentage_used . '%</span></h2>
      <h1 style="text-align: center; margin-top: 10px; font-size: 40px;">' . number_format($crates) . ' / ' . number_format($slots) . ' Crates</h1>
    </div>

    <script>
      $("#summary-toggle").children(".toggle").click(function(){
        $("#summary-toggle").children().removeClass("toggle-selected").addClass("toggle");
        $(this).removeClass("toggle").addClass("toggle-selected");

        if($(this).text() == "PERSONAL STATS"){
          $.ajax({
              type: "POST",
              url: "templates/ajax/summary_page.php",
              dataType: "html",
              success: function(response){
                  $("#summary").html(response);
              }

          });
        }
        else{
          $.ajax({
              type: "POST",
              url: "templates/ajax/global_summary_page.php",
              dataType: "html",
              success: function(response){
                  $("#summary").html(response);
              }

          });
        }
      });
    </script>

    <!--<div class="summary-panel">
      <h1 class="primary-title">MOST SUCCESSFUL ORGANIZATIONS</h1>
    </div>-->
  ';
?>
