<?php

if( route(1) == "v2" ):

  function servicePackage($type){
    switch ($type) {
      case 1:
        $service_type = "Default";
      break;
      case 2:
        $service_type = "Package";
      break;
      case 3:
        $service_type = "Custom Comments";
      break;
      case 4:
        $service_type = "Custom Comments Package";
      break;
      default:
        $service_type = "Subscriptions";
      break;
    }
    return $service_type;
  }


  $smmapi           = new SMMApi();
  $action           = $_POST["action"];
  $key              = $_POST["key"];
  $orderid          = $_POST["order"];
  $serviceid        = $_POST["service"];
  $quantity         = $_POST["quantity"];
  $link             = $_POST["link"];
  $username         = $_POST["username"];
  $posts            = $_POST["posts"];
  $delay            = $_POST["delay"];
  $otoMin           = $_POST["min"];
  $otoMax           = $_POST["max"];
  $comments         = $_POST["comments"];
  $runs             = $_POST["runs"];
  $interval         = $_POST["interval"];
  $expiry           = date("Y.m.d", strtotime($_POST["expiry"]));
  $subscriptions    = 0;


  $client = $conn->prepare("SELECT * FROM clients WHERE apikey=:key ");
  $client->execute(array("key"=>$key));
  $clientDetail = $client->fetch(PDO::FETCH_ASSOC);

  if ( empty( $action ) || empty( $key ) ):
    $output    = array('error'=>'Eksik veri','status'=>"101");
  elseif ( !$client->rowCount() ):
    $output    = array('error'=>'API key hatalı','status'=>"102");
  elseif ( $clientDetail["client_type"] == 1 ):
    $output    = array('error'=>'Hesabınız pasif','status'=>"103");
  else:
    ## actionlar başla ##
      if( $action == "balance" ):
        $output    = array('balance'=>$clientDetail["balance"],'currency'=>getCurrencyUnit());
      elseif( $action == "status" ):
        $order        = $conn->prepare("SELECT * FROM orders WHERE order_id=:id && client_id=:client ");
        $order        -> execute(array("client"=>$clientDetail["client_id"],"id"=>$orderid ));
        $orderDetail  = $order->fetch(PDO::FETCH_ASSOC);
        if( $order->rowCount() ):
          if( $orderDetail["subscriptions_type"] == 2 ):
            $output    = array('status'=>ucwords($orderDetail["subscriptions_status"]),"posts"=>$orderDetail["subscriptions_posts"]);
          elseif( $orderDetail["dripfeed"] != 1 ):
            $output    = array('status'=>ucwords($orderDetail["subscriptions_status"]),"runs"=>$orderDetail["dripfeed_runs"]);
          else:
            $output    = array('charge'=>$orderDetail["order_charge"],"start_count"=>$orderDetail["order_start"],'status'=>ucfirst($orderDetail["order_status"]),"remains"=>$orderDetail["order_remains"],"currency"=>getCurrencyUnit());

          endif;
        else:
          $output    = array('error'=>'Sipariş bulunamadı.','status'=>"104");
        endif;
      elseif( $action == "services" ):
        $servicesRows = $conn->prepare("SELECT * FROM services INNER JOIN categories ON categories.category_id=services.category_id WHERE categories.category_type=:type2 && services.service_type=:type  ORDER BY categories.category_line,services.service_line ASC ");
        $servicesRows->execute(array("type"=>2,"type2"=>2));
        $servicesRows = $servicesRows->fetchAll(PDO::FETCH_ASSOC);

        $services = [];
          foreach ( $servicesRows as $serviceRow ) {
            $search = $conn->prepare("SELECT * FROM clients_service WHERE service_id=:service && client_id=:c_id ");
            $search->execute(array("service"=>$serviceRow["service_id"],"c_id"=>$clientDetail["client_id"]));
            $search2 = $conn->prepare("SELECT * FROM clients_category WHERE category_id=:category && client_id=:c_id ");
            $search2->execute(array("category"=>$serviceRow["category_id"],"c_id"=>$clientDetail["client_id"]));
            if( ( $serviceRow["service_secret"] == 2 || $search->rowCount() ) && ( $serviceRow["category_secret"] == 2 || $search2->rowCount() ) ):
              $s["rate"]    = client_price($serviceRow["service_id"],$clientDetail["client_id"]);
              $s['service'] = $serviceRow["service_id"];
              $s['category']= $serviceRow["category_name"];
              $s['name']    = $serviceRow["service_name"];
              $s['type']    = servicePackage($serviceRow["service_package"]);
              $s['min']     = $serviceRow["service_min"];
              $s['max']     = $serviceRow["service_max"];
                array_push($services,$s);
            endif;
          }
          $output  = $services;
      elseif( $action == "add" ):
        $clientBalance = $clientDetail["balance"];
        $serviceDetail = $conn->prepare("SELECT * FROM services INNER JOIN categories ON categories.category_id=services.category_id LEFT JOIN service_api ON service_api.id=services.service_api WHERE services.service_id=:id ");
        $serviceDetail->execute(array("id"=>$serviceid));
        $serviceDetail = $serviceDetail->fetch(PDO::FETCH_ASSOC);

        $search = $conn->prepare("SELECT * FROM clients_service WHERE service_id=:service && client_id=:c_id ");
        $search->execute(array("service"=>$serviceid,"c_id"=>$clientDetail["client_id"]));
        $search2 = $conn->prepare("SELECT * FROM clients_category WHERE category_id=:category && client_id=:c_id ");
        $search2->execute(array("category"=>$serviceDetail["category_id"],"c_id"=>$clientDetail["client_id"]));

        if( $serviceDetail["want_username"] == 2 ):
          $private_type = "username";
          $countRow     = $conn->prepare("SELECT * FROM orders WHERE order_url=:url && ( order_status=:statu || order_status=:statu2 || order_status=:statu3 ) && dripfeed=:dripfeed && subscriptions_type=:subscriptions_type ");
          $countRow    -> execute(array("url"=>$link,"statu"=>"pending","statu2"=>"inprogress","statu3"=>"processing","dripfeed"=>1,"subscriptions_type"=>1 ));
          $countRow     = $countRow->rowCount();
        else:
          $private_type = "url";
          if( substr($link,0,7) == "http://" ): $link = substr($link,7); endif; if( substr($link,0,8) == "https://" ): $link = substr($link,8); endif; if( substr($link,0,4) == "www." ): $link = substr($link,4); endif;
          $countRow     = $conn->prepare("SELECT * FROM orders WHERE order_url LIKE :url && ( order_status=:statu || order_status=:statu2 || order_status=:statu3 ) && dripfeed=:dripfeed && subscriptions_type=:subscriptions_type ");
          $countRow    -> execute(array("url"=>'%'.$link.'%',"statu"=>"pending","statu2"=>"inprogress","statu3"=>"processing","dripfeed"=>1,"subscriptions_type"=>1 ));
          $countRow     = $countRow->rowCount();
        endif;
            $link = $_POST["link"];
        if( ( $serviceDetail["service_secret"] == 2 || $search->rowCount() ) && $serviceDetail["category_type"] == 2 && $serviceDetail["service_type"] == 2 && ( $serviceDetail["category_secret"] == 2 || $search2->rowCount() ) ):
          ## sipariş geç ##
          $price  = client_price($serviceDetail["service_id"],$clientDetail["client_id"])/1000*$quantity;
          if( $runs && $interval  ):
            $dripfeed  = 2; $totalcharges  = $price*$runs; $totalquantity = $quantity*$runs; $price = $price*$runs;
          else:
            $dripfeed  = 1; $totalcharges  = ""; $totalquantity = "";
          endif;

          if( ( $runs && empty( $interval ) ) || ( $interval && empty( $runs ) ) ):
            $output        = array('error'=>"Gerekli alanları doldurmalısınız.",'status'=>107);
          elseif( $serviceDetail["service_package"] == 1 && ( empty($link) || empty($quantity) ) ):
            $output        = array('error'=>"Gerekli alanları doldurmalısınız.",'status'=>107);
          elseif( $serviceDetail["service_package"] == 2 && empty($link) ):
            $output        = array('error'=>"Gerekli alanları doldurmalısınız.",'status'=>107);
          elseif( ($serviceDetail["service_package"] == 14 || $serviceDetail["service_package"] == 15 ) && empty($link) ):
            $output        = array('error'=>"Gerekli alanları doldurmalısınız.",'status'=>107);
          elseif( $serviceDetail["service_package"] == 3 && ( empty($link) || empty($comments) ) ):
            $output        = array('error'=>"Gerekli alanları doldurmalısınız.",'status'=>107);
          elseif( $serviceDetail["service_package"] == 4 && ( empty($link) || empty($comments) ) ):
            $output        = array('error'=>"Gerekli alanları doldurmalısınız.",'status'=>107);
          elseif( ( $serviceDetail["service_package"] != 11 && $serviceDetail["service_package"] != 12 && $serviceDetail["service_package"] != 13  ) && ( ( $dripfeed == 2 && $totalquantity < $serviceDetail["service_min"] ) || ( $dripfeed == 1 && $quantity < $serviceDetail["service_min"]  ) ) ):
            $output        = array('error'=>"Minimum sayıyı karşılayamadınız.",'status'=>108);
          elseif( ( $serviceDetail["service_package"] != 11 && $serviceDetail["service_package"] != 12 && $serviceDetail["service_package"] != 13  ) && ( ( $dripfeed == 2 && $totalquantity > $serviceDetail["service_max"] ) || ( $dripfeed == 1 && $quantity > $serviceDetail["service_max"]  ) ) ):
            $output        = array('error'=>"Maksimum sayı aşıldı.",'status'=>109);
          elseif( ( $serviceDetail["service_package"] == 11 || $serviceDetail["service_package"] == 12 || $serviceDetail["service_package"] == 13  ) && empty($username) ):
            $output        = array('error'=>"Gerekli alanları doldurmalısınız.",'status'=>107);
          elseif( ( $serviceDetail["service_package"] == 11 || $serviceDetail["service_package"] == 12 || $serviceDetail["service_package"] == 13  ) && empty($otoMin) ):
            $output        = array('error'=>"Gerekli alanları doldurmalısınız.",'status'=>107);
          elseif( ( $serviceDetail["service_package"] == 11 || $serviceDetail["service_package"] == 12 || $serviceDetail["service_package"] == 13  ) && empty($otoMax) ):
            $output        = array('error'=>"Gerekli alanları doldurmalısınız.",'status'=>107);
          elseif( ( $serviceDetail["service_package"] == 11 || $serviceDetail["service_package"] == 12 || $serviceDetail["service_package"] == 13  ) && empty($posts) ):
            $output        = array('error'=>"Gerekli alanları doldurmalısınız.",'status'=>107);
          elseif( ( $serviceDetail["service_package"] == 11 || $serviceDetail["service_package"] == 12 || $serviceDetail["service_package"] == 13  ) && $otoMax < $otoMin ):
            $output        = array('error'=>"Minimum sayı Maksimum sayıdan büyük olamaz.",'status'=>110);
          elseif( ( $serviceDetail["service_package"] == 11 || $serviceDetail["service_package"] == 12 || $serviceDetail["service_package"] == 13  ) && $otoMin < $serviceDetail["service_min"] ):
            $output        = array('error'=>"Minimum sayıyı karşılayamadınız.",'status'=>111);
          elseif( ( $serviceDetail["service_package"] == 11 || $serviceDetail["service_package"] == 12 || $serviceDetail["service_package"] == 13  ) && $otoMax > $serviceDetail["service_max"] ):
            $output        = array('error'=>"Maksimum sayı aşıldı",'status'=>112);
          elseif( $serviceDetail["instagram_second"] == 1 && $countRow && ( $serviceDetail["service_package"] != 11 && $serviceDetail["service_package"] != 12 && $serviceDetail["service_package"] != 13 && $serviceDetail["service_package"] != 14 && $serviceDetail["service_package"] != 15 ) ):
            $output        = array('error'=>"Gönderim olan bağlantıya yeni bir sipariş giremezsiniz.",'status'=>113);
          elseif( instagramProfilecheck(["type"=>$private_type,"url"=>$link,"return"=>"private"]) && $serviceDetail["instagram_private"] == 2 ):
            $output        = array('error'=>"Girilen profil gizli",'status'=>114);
          elseif( ( $price > $clientDetail["balance"] ) && $clientDetail["balance_type"] == 2 ):
            $output        = array('error'=>"Bakiyeniz yetersiz",'status'=>113);
          elseif( ( $clientDetail["balance"] - $price < "-".$clientDetail["debit_limit"] ) && $clientDetail["balance_type"] == 1 ):
            $output        = array('error'=>"Bakiyeniz yetersiz",'status'=>113);
          else:
              if( !$runs ):  $runs = 1; endif;

            if( $serviceDetail["service_package"] == 3 || $serviceDetail["service_package"] == 4 ):
              $quantity = count(explode("\n",$comments));// count custom comments
              $extras   = json_encode(["comments"=>$comments]);
              $subscriptions_status = "active";
              $subscriptions = 1;
            elseif( $serviceDetail["service_package"] == 11 ||  $serviceDetail["service_package"] == 12 ||  $serviceDetail["service_package"] == 13 ):
              $quantity         = $otoMin."-".$otoMax; // Sipariş miktarı
              $price            = 0;
              $extras = json_encode([]);
              $subscriptions = 1;
            elseif( $serviceDetail["service_package"] == 14 ||  $serviceDetail["service_package"] == 15 ):
              $quantity         = $serviceDetail["service_min"];
              $price            = service_price($service["service_id"]);
              $posts            = $serviceDetail["service_autopost"];
              $delay            = 0;
              $time             = '+'.$serviceDetail["service_autotime"].' days';
              $expiry           = date('Y-m-d H:i:s', strtotime($time));
              $otoMin           = $serviceDetail["service_min"];
              $otoMax           = $serviceDetail["service_min"];
              $extras = json_encode([]);
            else:
              $posts            = 0;
              $delay            = 0;
              $expiry           = "1970-01-01";
              $extras = json_encode([]);
              $subscriptions_status = "active";
              $subscriptions = 1;
            endif;

            if( $serviceDetail["start_count"] == "none"  ): $start_count = "0"; else: $start_count = instagramCount(["type"=>$private_type,"url"=>$link,"search"=>$serviceDetail["start_count"]]); endif;

              if( $serviceDetail["service_api"] == 0 ):
                /* manuel sipariş - başla */
                //$conn->beginTransaction();
                $insert = $conn->prepare("INSERT INTO orders SET order_where=:order_where, order_start=:count, order_profit=:profit, order_error=:error, client_id=:c_id, service_id=:s_id, order_quantity=:quantity, order_charge=:price, order_url=:url, order_create=:create, last_check=:last ");
                $insert = $insert-> execute(array("order_where"=>"api","count"=>$start_count,"c_id"=>$clientDetail["client_id"],"error"=>"-","s_id"=>$serviceDetail["service_id"],"quantity"=>$quantity,"price"=>$price,"profit"=>$price,"url"=>$link,"create"=>date("Y.m.d H:i:s"),"last"=>date("Y.m.d H:i:s")));
                  if( $insert ): $last_id = $conn->lastInsertId(); endif;
                $update = $conn->prepare("UPDATE clients SET balance=:balance, spent=:spent WHERE client_id=:id");
                $update = $update-> execute(array("balance"=>$clientDetail["balance"]-$price,"spent"=>$clientDetail["spent"]+$price,"id"=>$clientDetail["client_id"]));
                $insert2= $conn->prepare("INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ");
                $insert2= $insert2->execute(array("c_id"=>$clientDetail["client_id"],"action"=>"API aracılığıyla ".$price. " " . getCurrencyUnit() . "  tutarında yeni sipariş geçildi.","ip"=>GetIP(),"date"=>date("Y-m-d H:i:s") ));
                  if ( $insert && $update && $insert2 ):
                    //$conn->commit();
                    $output        = array('status'=>100,'order'=>$last_id );
                    if( $settings["alert_newmanuelservice"] == 2 ):
                      if( $settings["alert_type"] == 3 ):   $sendmail = 1; $sendsms  = 1; elseif( $settings["alert_type"] == 2 ): $sendmail = 1; $sendsms=0; elseif( $settings["alert_type"] == 1 ): $sendmail=0; $sendsms  = 1; endif;
                      if( $sendsms ):
                        SMSUser($settings["admin_telephone"],"Websiteniz #".$last_id." idli yeni bir sipariş mevcut.");
                      endif;
                      if( $sendmail ):
                        sendMail(["subject"=>"Yeni sipariş mevcut.","body"=>"Websiteniz #".$last_id." idli yeni bir sipariş mevcut.","mail"=>$settings["admin_mail"]]);
                      endif;
                    endif;
                  else:
                    //$conn->rollBack();
                    $output        = array('error'=>"Siparişiniz verilirken hata oluştu",'status'=>114);
                  endif;
                /* manuel sipariş - bitir */
              else:
                /* api ile sipariş - başla */
                //$conn->beginTransaction();

                  $insert = $conn->prepare("INSERT INTO orders SET order_where=:order_where, order_error=:error, order_detail=:detail, client_id=:c_id,
                    service_id=:s_id, order_quantity=:quantity, order_charge=:price, order_url=:url, order_create=:create, order_extras=:extra, last_check=:last_check,
                    order_api=:api, api_serviceid=:api_serviceid, subscriptions_status=:s_status,
                    subscriptions_type=:subscriptions, subscriptions_username=:username, subscriptions_posts=:posts, subscriptions_delay=:delay, subscriptions_min=:min,
                    subscriptions_max=:max, subscriptions_expiry=:expiry
                    ");
                  $insert = $insert-> execute(array("order_where"=>"api","c_id"=>$clientDetail["client_id"],"detail"=>"cronpending","error"=>"-",
                    "s_id"=>$serviceDetail["service_id"],"quantity"=>$quantity,"price"=>$price / $runs,"url"=>$link,
                    "create"=>date("Y.m.d H:i:s"),"extra"=>$extras,"last_check"=>date("Y.m.d H:i:s"),"api"=>$serviceDetail["id"],
                    "api_serviceid"=>$serviceDetail["api_service"],"s_status"=>$subscriptions_status,"subscriptions"=>$subscriptions,"username"=>$username,
                    'posts'=>$posts,
                    "delay"=>$delay,"min"=>$otoMin,"max"=>$otoMax,"expiry"=>$expiry
                  ));
                    if( $insert ): $last_id = $conn->lastInsertId(); endif;

                    if ( $insert ):
                      //$conn->commit();
                      $output        = array('status'=>100,'order'=>$last_id );
                    else:
                     // $conn->rollBack();
                      $output        = array('error'=>"Siparişiniz verilirken hata oluştu",'status'=>114);
                    endif;
                /* api ile sipariş - bitir */
              endif;
          endif;
          ## sipariş geç  bitti ##
        else:
          $output    = array('error'=>'Servis pasif ya da bulunamadı','status'=>"105");
        endif;
      endif;
    ## actionlar bitti ##
  endif;
   print_r(json_encode($output));
  exit();
elseif( !route(1) ):

  $title .= $languageArray["api.title"];

else:
  header("Location:".site_url());
endif;
