<?php
  require "../../includes/model.php";

  $pdo = pdo();
  $crate_type_name = strtolower($_GET['crate_type_name']);
  $quantity = $_GET['quantity'];
  $statement = $pdo->prepare("SELECT risk, profit, price FROM crate_types WHERE name LIKE ?");
  $statement->bindParam(1, $crate_type_name);
  $statement->execute();
  $crate_type = $statement->fetch();

  $data = array(
    "risk"=>$crate_type['risk'],
    "profit"=>$crate_type['profit'],
    "price"=>number_format($crate_type['price']*$quantity),

  );
  echo json_encode($data);
?>
