<?php

require_once("./conn.php");

if (isset($_POST) && !empty($_POST)) {
  if ($_POST['type'] == "insert") {

    if ($_POST['id'] == "0") {

      $sql = "INSERT INTO `client`(`name`, `phone`) VALUES (:name, :phone)";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(':name', $_POST['name']);
      $stmt->bindParam(':phone', $_POST['phone']);
      $stmt->execute();
      exit();

    } else {

      $sql = "UPDATE `client` SET `name` = :name, `phone` = :phone WHERE id = :id";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(':name', $_POST['name']);
      $stmt->bindParam(':phone', $_POST['phone']);
      $stmt->bindParam(':id', $_POST['id']);
      $stmt->execute();
      exit();

    }
  }

  if ($_POST['type'] == "delete") {

    $sql = "UPDATE `client` SET `deleted` = 1 WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $_POST['id']);
    $stmt->execute();
    exit();

  }
}

if (isset($_GET) && !empty($_GET)) {

  if ($_GET['type'] == "selectAll") {

    $sql = "SELECT * FROM client WHERE deleted = 0 ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit();

  }

  if ($_GET['type'] == "selectById") {

    $sql = "SELECT * FROM client WHERE deleted = 0 AND id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    exit();
    
  }

  if ($_GET['type'] == "search") {
    
    $name = "%".$_GET['name']."%";
    $phone = "%".$_GET['phone']."%";

    if (!empty($_GET['id'])) {

      $sql = "SELECT *
              FROM `client`
              WHERE deleted = 0
              AND name LIKE :name
              AND phone LIKE :phone
              AND id = :id
              ORDER BY id DESC";

      $stmt = $conn->prepare($sql);
      $stmt->bindParam(':id', $_GET['id']);
      $stmt->bindParam(':name', $name);
      $stmt->bindParam(':phone', $phone);
      $stmt->execute();
      echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
      exit();

    } else {

      $sql = "SELECT *
              FROM `client`
              WHERE deleted = 0
              AND name LIKE :name
              AND phone LIKE :phone
              ORDER BY id DESC";

      $stmt = $conn->prepare($sql);
      $stmt->bindParam(':name', $name);
      $stmt->bindParam(':phone', $phone);
      $stmt->execute();
      echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
      exit();
    }

  }
}