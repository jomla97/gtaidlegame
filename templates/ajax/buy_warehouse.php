<script type="text/javascript">
  function close_button(){
    $(".dialog").css("display", "none");
    $(".map-marker").removeClass("selected");
  }
</script>

<?php
  require "../../includes/model.php";
  $pdo = pdo();
  $warehouse_id = $_GET['warehouse_id'];
  $statement = $pdo->prepare("SELECT * FROM warehouses WHERE id LIKE ?");
  $statement->bindParam(1, $warehouse_id);
  $statement->execute();
  $warehouse = $statement->fetch();

  $cash = $_GET['cash'];

  if($cash >= $warehouse['price']){
    echo '
      <div class="close-button" onclick="close_button()">x</div>
      <h1>WAREHOUSE ' . $warehouse_id . '</h1>
      <p>STATUS:<span class="right-text">FOR SALE</span></p>
      <p>WAREHOUSE SLOTS:<span class="right-text">' . number_format($warehouse['slots']) . '</span></p>
      <p>COST:<span class="right-text">$' . number_format($warehouse['price']) . '</span></p>

      <a href="index.php?action=buy_warehouse&id=' . $warehouse_id . '"><div class="submit-button">BUY</div></a>
    ';
  }
  else{
    echo '
      <div class="close-button" onclick="close_button()">x</div>
      <h1>WAREHOUSE ' . $warehouse_id . '</h1>
      <p>STATUS:<span class="right-text">FOR SALE</span></p>
      <p>WAREHOUSE SLOTS:<span class="right-text">' . number_format($warehouse['slots']) . '</span></p>
      <p>COST:<span class="right-text red-text">$' . number_format($warehouse['price']) . '</span></p>

      <div class="submit-button-disabled">BUY</div>
    ';
  }
?>
