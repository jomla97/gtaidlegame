<?php
  require "../../includes/model.php";
  session_start();
  ob_start();

  $pdo = pdo();
  $statement = $pdo->prepare("SELECT * FROM shipments WHERE type LIKE 'purchase' AND status LIKE 'delivered'");
  $statement->execute();
  $collections_completed = $statement->rowCount();

  $statement = $pdo->prepare("SELECT * FROM shipments WHERE type LIKE 'sale' AND status LIKE 'delivered'");
  $statement->execute();
  $sales_completed = $statement->rowCount();

  $statement = $pdo->prepare("SELECT * FROM transport_upgrades WHERE vehicle LIKE 'planes'");
  $statement->execute();
  $plane_upgrades = $statement->rowCount();

  $statement = $pdo->prepare("SELECT * FROM transport_upgrades WHERE vehicle LIKE 'trucks'");
  $statement->execute();
  $truck_upgrades = $statement->rowCount();

  $statement = $pdo->prepare("SELECT SUM(value) FROM shipments WHERE type LIKE 'sale' AND status LIKE 'delivered'");
  $statement->execute();
  $money_earned = $statement->fetch();

  $statement = $pdo->prepare("SELECT SUM(quantity) FROM shipments WHERE type LIKE 'purchase'");
  $statement->execute();
  $crates_bought = $statement->fetch();

  $statement = $pdo->prepare("SELECT SUM(quantity) FROM shipments WHERE type LIKE 'sale' AND status LIKE 'delivered'");
  $statement->execute();
  $crates_sold = $statement->fetch();

  $statement = $pdo->prepare("SELECT * FROM team_upgrades");
  $statement->bindParam(1, $_SESSION['username']);
  $statement->execute();
  $previous_team_upgrades = $statement->rowCount();

  echo '
    <div class="summary-panel">
      <div id="summary-toggle">
        <div class="button toggle">PERSONAL STATS</div>
        <div class="button toggle-selected">GLOBAL STATS</div>
      </div>

      <h1 class="primary-title">GLOBAL STATISTICS</h1>
      <ul>
        <li>COLLECTIONS COMPLETED <span class="right-text">' . number_format($collections_completed) . '</span></li>
        <li>SALES COMPLETED <span class="right-text">' . number_format($sales_completed) . '</span></li>
        <li>TRUCK UPGRADES <span class="right-text">' . number_format($truck_upgrades) . '</span></li>
        <li>PLANE UPGRADES <span class="right-text">' . number_format($plane_upgrades) . '</span></li>
        <li>TEAM & ORGANIZATION UPGRADES <span class="right-text">' . number_format($previous_team_upgrades) . '</span></li>
        <li>MONEY SPENT <span class="right-text">-' . /*number_format($money_spent)*/  '</span></li>
        <li>MONEY EARNED <span class="right-text">$' . number_format($money_earned['SUM(value)']) . '</span></li>
        <li>CRATES BOUGHT <span class="right-text">' . number_format($crates_bought['SUM(quantity)']) . '</span></li>
        <li>CRATES SOLD <span class="right-text">' . number_format($crates_sold['SUM(quantity)']) . '</span></li>
      </ul>
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
  ';
?>
