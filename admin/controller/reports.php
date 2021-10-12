<?php

  if( $user["access"]["reports"] != 1  ):
    header("Location:".site_url("admin"));
    exit();
  endif;

  if( $_SESSION["client"]["data"] ):
    $data = $_SESSION["client"]["data"];
    foreach ($data as $key => $value) {
      $$key = $value;
    }
    unset($_SESSION["client"]);
  endif;
   
  $services       = $conn->prepare("SELECT * FROM services RIGHT JOIN categories ON categories.category_id = services.category_id LEFT JOIN service_api ON service_api.id = services.service_api ORDER BY categories.category_line,services.service_line ASC ");
  $services       -> execute(array());
  $services       = $services->fetchAll(PDO::FETCH_ASSOC);
  $serviceList    = array_group_by($services, 'category_name');
//  echo "1" ; die;
  if( !route(2) ):
    $action = "profit";
   
    //New Query 
    
    $years    = $conn->query("SELECT DISTINCT(YEAR(order_create)) AS order_year FROM orders order by order_year asc")->fetchAll(PDO::FETCH_ASSOC);
    //  echo "1" ; die;
    
    $yearList = []; 
    
    //print_r($years[0]); die;
    foreach ($years as $key) {
       
      $yearList[] =$key['order_year'];
      
    } 
    
    //print_r( $yearList); die;
    
    //  echo "1" ; die;
  else:
    $action = route(2);
      if( $action == "orders" || $action == "profit" ):
        $years    = $conn->query("SELECT DISTINCT(YEAR(order_create)) AS order_year FROM orders order by order_year asc")->fetchAll(PDO::FETCH_ASSOC);
        $yearList = []; 
        foreach ($years as $key) {
          $yearList[] = $key["order_year"];
         
        }
      elseif( $action == "payments" ):
          
        $methods  = $conn->prepare("SELECT * FROM payment_methods");
        $methods->execute(array());
        $methods  = $methods->fetchAll(PDO::FETCH_ASSOC);
        $years    = $conn->query("SELECT DISTINCT(YEAR(order_create)) AS order_year FROM orders order by order_year asc")->fetchAll(PDO::FETCH_ASSOC);
        //echo "here"; die;
        $yearList = [];
        foreach ($years as $key) {
          $yearList[] = $key["order_create"];
          
        }
      endif;
  endif;

  if( count($yearList) == 0 ): $yearList[0] = date("Y"); endif;

  if( $_GET["year"] ):
    $year = $_GET["year"];
  else:
    $year = date("Y");
  endif;


  require admin_view('reports');
