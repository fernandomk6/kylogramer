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

    $sql = "UPDATE `sale` SET `deleted` = 1 WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $_POST['id']);
    $stmt->execute();
    exit();

  }
}

if (isset($_GET) && !empty($_GET)) {

  if ($_GET['type'] == "selectAll") {

    $sql = "SELECT sale.id, sale.date, sale.client_id, client.name, client.phone, sale.date, sale.total
            FROM sale, client
            WHERE sale.client_id = client.id 
            AND sale.deleted = 0";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT 
              product.name,
              sale_product.sale_id,
              sale_product.product_id,
              sale_product.kilogram,
              sale_product.unitary
            FROM 
              sale, sale_product, product
            WHERE 
              sale.id = sale_product.sale_id
            AND 
              sale_product.product_id = product.id
            AND
              sale.deleted = 0";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT 
              payment.name,
              sale_payment.sale_id,
              sale_payment.payment_id, 
              sale_payment.total
            FROM 
              sale, sale_payment, payment
            WHERE 
              sale.id = sale_payment.sale_id 
            AND 
              sale_payment.payment_id = payment.id
            AND 
              sale.deleted = 0";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $allSales= []; 

    foreach ($sales as $sale) {

      $actualSale = [
        "id" => $sale['id'],
        "date" => $sale['date'],
        "client_id" => $sale['client_id'],
        "client_name" => $sale['name'],
        "client_phone" => $sale['phone'],
        "total" => $sale['total'],
        "products" => [],
        "payments" => [],
      ];

      foreach ($products as $product) {
        if ($product['sale_id'] == $sale['id']) {
          array_push($actualSale["products"], $product); 
        }
      }

      foreach ($payments as $payment) {
        if ($payment['payment_id'] == $sale['id']) {
          array_push($actualSale["payments"], $payment);
        }
      }

      $allSales[] = $actualSale;
    }

    echo json_encode($allSales);
    exit();
  }

  if ($_GET['type'] == "selectById") {

    $sql = "SELECT sale.id, sale.date, sale.client_id, client.name, client.phone, sale.date, sale.total
            FROM sale, client
            WHERE sale.client_id = client.id
            AND sale.id = :id 
            AND sale.deleted = 0";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT 
              product.name,
              sale_product.sale_id,
              sale_product.product_id,
              sale_product.kilogram,
              sale_product.unitary
            FROM 
              sale, sale_product, product
            WHERE 
              sale.id = sale_product.sale_id
            AND 
              sale_product.product_id = product.id
            AND
              sale.id = :id
            AND
              sale.deleted = 0";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT 
              payment.name,
              sale_payment.sale_id,
              sale_payment.payment_id, 
              sale_payment.total
            FROM 
              sale, sale_payment, payment
            WHERE 
              sale.id = sale_payment.sale_id 
            AND 
              sale_payment.payment_id = payment.id
            AND 
              sale.id = :id
            AND
              sale.deleted = 0";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $allSales= []; 

    foreach ($sales as $sale) {

      $actualSale = [
        "id" => $sale['id'],
        "date" => $sale['date'],
        "client_id" => $sale['client_id'],
        "client_name" => $sale['name'],
        "client_phone" => $sale['phone'],
        "total" => $sale['total'],
        "products" => [],
        "payments" => [],
      ];

      foreach ($products as $product) {
        if ($product['sale_id'] == $sale['id']) {
          array_push($actualSale["products"], $product); 
        }
      }

      foreach ($payments as $payment) {
        if ($payment['payment_id'] == $sale['id']) {
          array_push($actualSale["payments"], $payment);
        }
      }

      $allSales[] = $actualSale;
    }

    echo json_encode($allSales);
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

  if ($_GET['type'] == "insert") {

    $data = [
      "sale_id" => "",
      "clients" => [],
      "products" => [],
      "payments" => []
    ];

    // inserindo venda
    $date = date("Y/m/d");
    $sql = "INSERT INTO `sale`(`date`) VALUES (:date);";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':date', $date);
    $stmt->execute();

    // pegando o id da ultima venda inserida
    $sql = "SELECT id FROM sale ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $data["sale_id"] = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]["id"];


    // pegando todos os clientes
    $sql = "SELECT * FROM client";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $data["clients"] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // pegando todos os produtos
    $sql = "SELECT * FROM product";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $data["products"] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // pegando todas as formas de pagamento
    $sql = "SELECT * FROM payment";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $data["payments"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($data);
    exit();

  }
}