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

  echo '
    <div class="close-button" onclick="close_button()">x</div>
    <h1>WAREHOUSE ' . $warehouse_id . '</h1>
    <p>STATUS:<span class="right-text">OWNED</span></p>
    <p>WAREHOUSE SLOTS:<span class="right-text">' . number_format($warehouse['slots']) . '</span></p>
  ';
?>
