<?php

  if( $user["access"]["services"] != 1  ):
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

  if( !route(2) ):
    $page   = 1;
  elseif( is_numeric(route(2)) ):
    $page   = route(2);
  elseif( !is_numeric(route(2)) ):
    $action = route(2);
  endif;

  if( empty($action) ):

		$query = $conn->query("SELECT * FROM settings", PDO::FETCH_ASSOC);
		if ( $query->rowCount() ):
			 foreach( $query as $row ):
				  $siraal = $row['servis_siralama'];
			 endforeach;
		endif;
		
		if($_GET["siralama"]!=""):
		
			$updatesiralama = $conn->prepare("UPDATE settings SET servis_siralama=:servis_siralama WHERE id=:id ");
			$updatesiralama->execute(array("servis_siralama"=>$_GET["siralama"],"id"=>1));
         
          header("Location:".site_url("admin/services"));
		
		endif;
		
		
    $services       = $conn->prepare("SELECT * FROM services RIGHT JOIN categories ON categories.category_id = services.category_id LEFT JOIN service_api ON service_api.id = services.service_api ORDER BY categories.category_line,services.service_line ".$siraal);
    $services       -> execute(array());
    $services       = $services->fetchAll(PDO::FETCH_ASSOC);
    $serviceList    = array_group_by($services, 'category_name');
    require admin_view('services');
  elseif( $action == "new-service" ):
      if( $_POST ):
        $language   = $conn->prepare("SELECT * FROM languages WHERE default_language=:default");
        $language->execute(array("default"=>1));
        $language   = $language->fetch(PDO::FETCH_ASSOC);
        foreach ($_POST as $key => $value) {
          $$key = $value;
        }
          $cat = intval(@$_POST["category"]);

        if (!$cat) $cat = $category;
         // $name      = mb_convert_encoding($_POST["name"][$language["language_code"]],"UTF-8","UTF-8");
         $name = $_POST["name"];
        $multiName = json_encode($_POST["name"]);
        if( $package == 2 ): $max = $min; endif;
        if( empty($name) ):
          $error    = 1;
          $errorText= "Ürün adı boş olamaz";
          $icon     = "error";
        elseif( empty($package) ):
          $error    = 1;
          $errorText= "Ürün paketi boş olamaz";
          $icon     = "error";
        elseif( empty($category) ):
          $error    = 1;
          $errorText= "Ürün kategori boş olamaz";
          $icon     = "error";
        elseif( !is_numeric($min) ):
          $error    = 1;
          $errorText= "Minimum sipariş miktarı boş olamaz";
          $icon     = "error";
        elseif( $package != 2 && !is_numeric($max) ):
          $error    = 1;
          $errorText= "Maksimum sipariş miktarı boş olamaz";
          $icon     = "error";
        elseif( $min > $max ):
          $error    = 1;
          $errorText= "Minimum sipariş miktarı maksimum sipariş miktarından fazla olamaz";
          $icon     = "error";
        elseif( $mode != 1 && empty($provider) ):
          $error    = 1;
          $errorText= "Servis sağlayıcı boş olamaz";
          $icon     = "error";
        elseif( $mode != 1 && empty($service) ):
          $error    = 1;
          $errorText= "Servis sağlayıcı servis bilgisi boş olamaz";
          $icon     = "error";
        elseif( empty($secret) ):
          $error    = 1;
          $errorText= "Servis gizliliği boş olamaz";
          $icon     = "error";
        elseif( empty($want_username) ):
          $error    = 1;
          $errorText= "Sipariş bağlantısı boş olamaz";
          $icon     = "error";
        elseif( !is_numeric($price) ):
          $error    = 1;
          $errorText= "Ürün fiyatı rakamlardan oluşmalı";
          $icon     = "error";
        else:
              $api=$conn->prepare("SELECT * FROM service_api WHERE id=:id "); $api->execute(array("id"=>$provider)); $api=$api->fetch(PDO::FETCH_ASSOC);
              if( $mode == 1 ): $provider = 0; $service = 0; endif;
              if( $mode == 2 && $api["api_type"] == 1 ):
                $smmapi   = new SMMApi(); $services = $smmapi->action(array('key' =>$api["api_key"],'action' =>'services'),$api["api_url"]); $balance = $smmapi->action(array('key' =>$api["api_key"],'action' =>'balance'),$api["api_url"]);
                  foreach ($services as $apiService):
                    if( $service == $apiService->service ):
                      $detail["description"]=$apiService->description;
                      $detail["min"]=$apiService->min;
                      $detail["max"]=$apiService->max;
                      $detail["rate"]=$apiService->rate;
                      $detail["currency"]=$balance->currency;
                      $detail=json_encode($detail);
                    endif;
                  endforeach;
              else:
                $detail="";
              endif;
            $row = $conn->query("SELECT * FROM services WHERE category_id='$category' ORDER BY service_line DESC LIMIT 1 ")->fetch(PDO::FETCH_ASSOC);
            $conn->beginTransaction();
            $insert = $conn->prepare("INSERT INTO services SET name_lang=:multiName, service_secret=:secret, service_api=:api, service_dripfeed=:dripfeed, instagram_second=:instagram_second, start_count=:start_count, instagram_private=:instagram_private, api_service=:api_service, api_detail=:detail, category_id=:category, service_line=:line, service_type=:type, service_package=:package, service_name=:name, service_price=:price, service_description=:description, service_min=:min, service_max=:max, want_username=:want_username, service_speed=:speed ");
            $insert = $insert-> execute(array("secret"=>$secret,"multiName"=>$multiName,"instagram_second"=>$instagram_second,"dripfeed"=>$dripfeed,"start_count"=>$start_count,"instagram_private"=>$instagram_private,"api"=>$provider,"api_service"=>$service,"detail"=>$detail,"category"=>$cat,"line"=>$row["service_line"]+1,"type"=>2,"package"=>$package,"name"=>$name,"price"=>$price,"description"=>$description,"min"=>$min,"max"=>$max,"want_username"=>$want_username,"speed"=>$speed ));
            if( $insert ):
              $conn->commit();
              $referrer = site_url("admin/services");
              $error    = 1;
              $errorText= "Success";
              $icon     = "success";
            else:
              $conn->rollBack();
              $error    = 1;
              $errorText= "Failed";
              $icon     = "error";
            endif;
        endif;
        echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon,"r"=>$referrer]);
      endif;
  elseif( $action == "edit-service" ):
    $service_id  = route(3);
    if( !countRow(["table"=>"services","where"=>["service_id"=>$service_id]]) ): header("Location:".site_url("admin/services")); exit(); endif;
      if( $_POST ):
        $language   = $conn->prepare("SELECT * FROM languages WHERE default_language=:default");
        $language->execute(array("default"=>1));
        $language   = $language->fetch(PDO::FETCH_ASSOC);
          foreach ($_POST as $key => $value) {
            $$key = $value;
          }
          $cat = intval(@$_POST["category"]);
          //$name      = mb_convert_encoding($_POST["name"][$language["language_code"]], 'UTF-8', 'UTF-8');
          $name = $_POST["name"];
          $multiName = json_encode($_POST["name"]);

         

          if( $package == 2 ): $max = $min; endif;
          $serviceInfo  = $conn->prepare("SELECT * FROM services INNER JOIN service_api ON service_api.id = services.service_api WHERE service_id=:id ");
          $serviceInfo -> execute(array("id"=>route(3) ));
          $serviceInfo  = $serviceInfo->fetch(PDO::FETCH_ASSOC);
        if( empty($name) ):
          $error    = 1;
          $errorText= "Ürün adı boş olamaz";
          $icon     = "error";
        elseif( empty($package) ):
          $error    = 1;
          $errorText= "Ürün paketi boş olamaz";
          $icon     = "error";
        elseif( empty($category) ):
          $error    = 1;
          $errorText= "Ürün kategori boş olamaz";
          $icon     = "error";
        elseif( !is_numeric($min) ):
          $error    = 1;
          $errorText= "Minimum sipariş miktarı boş olamaz";
          $icon     = "error";
        elseif( $package != 2 && !is_numeric($max) ):
          $error    = 1;
          $errorText= "Maksimum sipariş miktarı boş olamaz";
          $icon     = "error";
        elseif( $min > $max ):
          $error    = 1;
          $errorText= "Minimum sipariş miktarı maksimum sipariş miktarından fazla olamaz";
          $icon     = "error";
        elseif( $mode != 1 && empty($provider) ):
          $error    = 1;
          $errorText= "Servis sağlayıcı boş olamaz";
          $icon     = "error";
        elseif( $mode != 1 && empty($service) ):
          $error    = 1;
          $errorText= "Servis sağlayıcı servis bilgisi boş olamaz";
          $icon     = "error";
        elseif( empty($secret) ):
          $error    = 1;
          $errorText= "Servis gizliliği boş olamaz";
          $icon     = "error";
        elseif( empty($want_username) ):
          $error    = 1;
          $errorText= "Sipariş bağlantısı boş olamaz";
          $icon     = "error";
        elseif( !is_numeric($price) ):
          $error    = 1;
          $errorText= "Ürün fiyatı rakamlardan oluşmalı";
          $icon     = "error";
        else:
            $api=$conn->prepare("SELECT * FROM service_api WHERE id=:id "); $api->execute(array("id"=>$provider)); $api=$api->fetch(PDO::FETCH_ASSOC);
            if( $mode == 1 ): $provider = 0; $service = 0; endif;
            if( $mode == 2 && $api["api_type"] == 1 ):
              $smmapi   = new SMMApi(); $services = $smmapi->action(array('key' =>$api["api_key"],'action' =>'services'),$api["api_url"]); $balance = $smmapi->action(array('key' =>$api["api_key"],'action' =>'balance'),$api["api_url"]);
                foreach ($services as $apiService):
                  if( $service == $apiService->service ):
                    $detail["description"]=$apiService->description;
                    $detail["min"]=$apiService->min;
                    $detail["max"]=$apiService->max;
                    $detail["rate"]=$apiService->rate;
                    $detail["currency"]=$balance->currency;
                    $detail=json_encode($detail);
                  endif;
                endforeach;
            else:
              $detail="";
            endif;
            if( $serviceInfo["category_id"] != $category ): $row = $conn->query("SELECT * FROM services WHERE category_id='$category' ORDER BY service_line DESC LIMIT 1 ")->fetch(PDO::FETCH_ASSOC); $last_category=$serviceInfo["category_id"]; $last_line=$serviceInfo["service_line"]; $line= $row["service_line"] + 1; else: $line= $serviceInfo["service_line"]; endif;
            $conn->beginTransaction();
            $update = $conn->prepare("UPDATE services SET api_detail=:detail, name_lang=:multiName, service_dripfeed=:dripfeed, api_servicetype=:type, instagram_second=:instagram_second, start_count=:start_count, instagram_private=:instagram_private, service_api=:api, api_service=:api_service, category_id=:category, service_package=:package, service_name=:name,service_price=:price, service_description=:description, service_min=:min, service_secret=:secret, service_max=:max, want_username=:want_username, service_speed=:speed WHERE service_id=:id ");
            $update = $update-> execute(array("id"=>route(3),"multiName"=>$multiName,"secret"=>$secret,"type"=>2,"detail"=>$detail,"dripfeed"=>$dripfeed,"instagram_second"=>$instagram_second,"start_count"=>$start_count,"instagram_private"=>$instagram_private,"api"=>$provider,"api_service"=>$service,"category"=>$category,"package"=>$package,"name"=>$name,"price"=>$price,"description"=>$description,"min"=>$min,"max"=>$max,"want_username"=>$want_username,"speed"=>$speed ));
            if( $update ):
              $conn->commit();
              $rows = $conn->prepare("SELECT * FROM services WHERE category_id=:c_id && service_line>=:line ");
              $rows->execute(array("c_id"=>$last_category,"line"=>$last_line ));
              $rows = $rows->fetchAll(PDO::FETCH_ASSOC);
                foreach( $rows as $row ):
                  $update = $conn->prepare("UPDATE services SET service_line=:line WHERE service_id=:id ");
                  $update->execute(array("line"=>$row["service_line"]-1,"id"=>$row["service_id"] ));
                endforeach;
              $error    = 1;
              $errorText= "Success";
              $icon     = "success";
              $referrer = site_url("admin/services");
            else:
              $conn->rollBack();
              $error    = 1;
              $errorText= "Failed";
              $icon     = "error";
            endif;
        endif;
        echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon,"r"=>$referrer]);
      endif;
  elseif( $action == "edit-description" ):
    $service_id  = route(3);
    if( !countRow(["table"=>"services","where"=>["service_id"=>$service_id]]) ): header("Location:".site_url("admin/services")); exit(); endif;
      if( $_POST ):
        $language   = $conn->prepare("SELECT * FROM languages WHERE default_language=:default");
        $language->execute(array("default"=>1));
        $language   = $language->fetch(PDO::FETCH_ASSOC);
          foreach ($_POST as $key => $value) {
            $$key = $value;
          }
          $description  = $_POST["description"][$language["language_code"]];
          $multiDesc    = json_encode($_POST["description"]);

            $conn->beginTransaction();
            $update = $conn->prepare("UPDATE services SET service_description=:description, description_lang=:multi WHERE service_id=:id ");
            $update = $update-> execute(array("id"=>route(3),"multi"=>$multiDesc,"description"=>$description ));
            if( $update ):
              $conn->commit();
              $error    = 1;
              $errorText= "Success";
              $icon     = "success";
            else:
              $conn->rollBack();
              $error    = 1;
              $errorText= "Failed";
              $icon     = "error";
            endif;
        echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon]);
      endif;
  elseif( $action == "new-category" ):
      if( $_POST ):
        $name   = $_POST["name"];
        $secret = $_POST["secret"];
        $icon   = $_POST["icon"];
        $refill = $_POST["is_refill"];

        if( empty($name) ):
          $error    = 1;
          $errorText= "Kategori adı boş olamaz";
          $icon     = "error";
        else:
          $row = $conn->query("SELECT * FROM categories ORDER BY category_line DESC LIMIT 1 ")->fetch(PDO::FETCH_ASSOC);
            $conn->beginTransaction();
           
           $nweIcon = $icon!=""?$icon:" ";
            $insert = $conn->prepare("INSERT INTO categories SET category_name=:name, category_line=:line, category_icon=:icon, category_secret=:secret, is_refill=:is_refill ");
            $insert = $insert-> execute(array("name"=>$name,"secret"=>$secret,"icon"=>$nweIcon,"is_refill"=>$refill,"line"=>($row["category_line"]+1) ));
            if( $insert ):
                
              $conn->commit();
              
              unset($_SESSION["data"]);
              $error    = 1;
              $errorText= "Success";
              $icon     = "success";
              $referrer = site_url("admin/services");
            else:
              $conn->rollBack();
             
              $error    = 1;
              $errorText= "Failed";
              $icon     = "error";
            endif;
        endif;
        echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon,"r"=>$referrer]);
      endif;
  elseif( $action == "edit-category" ):
    $category_id  = route(3);
    if( !countRow(["table"=>"categories","where"=>["category_id"=>$category_id]]) ): header("Location:".site_url("admin/services")); exit(); endif;
    $row  = getRow(["table"=>"categories","where"=>["category_id"=>$category_id]]);
      if( $_POST ):
        $name   = $_POST["name"];
        $secret = $_POST["secret"];
        $icon   = $_POST["icon"];
        $refill = $_POST["is_refill"];

        if( empty($name) ):
          $error    = 1;
          $errorText= "Kategori adı boş olamaz";
          $icon     = "error";
        else:
            $conn->beginTransaction();
           
        
            
            $update = $conn->prepare("UPDATE categories SET category_name=:name, category_icon=:icon, category_secret=:secret, is_refill=:is_refill WHERE category_id=:id  ");
        $icon = $icon!=""?$icon:" ";
            $update = $update->execute(array("name"=>$name,"secret"=>$secret,"icon"=>$icon,"is_refill"=>$refill,"id"=>$category_id ));
            if( $update ):
               
              $conn->commit();
              $referrer = site_url("admin/services");
              $error    = 1;
              $errorText= "Success";
              $icon     = "success";
            else:
               
              $conn->rollBack();
              $error    = 1;
              $errorText= "Failed";
              $icon     = "error";
            endif;
        endif;
        echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon,"r"=>$referrer]);
      endif;
  elseif( $action == "new-subscription" ):
      if( $_POST ):
        $language   = $conn->prepare("SELECT * FROM languages WHERE default_language=:default");
        $language->execute(array("default"=>1));
        $language   = $language->fetch(PDO::FETCH_ASSOC);
        foreach ($_POST as $key => $value) {
          $$key = $value;
        }
          $cat = intval(@$_POST["category"]);
        if (!$cat) $cat = $category;
        $name      = mb_convert_encoding($_POST["name"][$language["language_code"]],"UTF-8","UTF-8");
        $multiName = json_encode($_POST["name"]);

        if( empty($name) ):
          $error    = 1;
          $errorText= "Ürün adı boş olamaz";
          $icon     = "error";
        elseif( empty($package) ):
          $error    = 1;
          $errorText= "Ürün paketi boş olamaz";
          $icon     = "error";
        elseif( empty($category) ):
          $error    = 1;
          $errorText= "Ürün kategori boş olamaz";
          $icon     = "error";
        elseif( empty($provider) ):
          $error    = 1;
          $errorText= "Servis sağlayıcı boş olamaz";
          $icon     = "error";
        elseif( empty($service) ):
          $error    = 1;
          $errorText= "Servis sağlayıcı servis bilgisi boş olamaz";
          $icon     = "error";
        elseif( empty($secret) ):
          $error    = 1;
          $errorText= "Servis gizliliği boş olamaz";
          $icon     = "error";
        elseif(  ( $package == 11 || $package == 12 ) && !is_numeric($price) ):
          $error    = 1;
          $errorText= "Ürün fiyatı rakamlardan oluşmalı";
          $icon     = "error";
        elseif( ( $package == 11 || $package == 12 ) && !is_numeric($min) ):
          $error    = 1;
          $errorText= "Minimum sipariş miktarı boş olamaz";
          $icon     = "error";
        elseif( ( $package == 11 || $package == 12 ) && !is_numeric($max) ):
          $error    = 1;
          $errorText= "Maksimum sipariş miktarı boş olamaz";
          $icon     = "error";
        elseif( ( $package == 11 || $package == 12 ) && $min > $max ):
          $error    = 1;
          $errorText= "Minimum sipariş miktarı maksimum sipariş miktarından fazla olamaz";
          $icon     = "error";
        elseif(  ( $package == 14 || $package == 15 ) && !is_numeric($autopost) ):
          $error    = 1;
          $errorText= "Gönderi miktarı boş olamaz";
          $icon     = "error";
        elseif(  ( $package == 14 || $package == 15 ) && !is_numeric($limited_min) ):
          $error    = 1;
          $errorText= "Sipariş miktarı boş olamaz";
          $icon     = "error";
        elseif(  ( $package == 14 || $package == 15 ) && !is_numeric($autotime) ):
          $error    = 1;
          $errorText= "Paket Süresi boş olamaz";
          $icon     = "error";
        else:
            $api=$conn->prepare("SELECT * FROM service_api WHERE id=:id "); $api->execute(array("id"=>$provider)); $api=$api->fetch(PDO::FETCH_ASSOC);
            if( $mode == 1 ): $provider = 0; $service = 0; endif;
            if( $mode == 2 && $api["api_type"] == 1 ):
              $smmapi   = new SMMApi(); $services = $smmapi->action(array('key' =>$api["api_key"],'action' =>'services'),$api["api_url"]); $balance = $smmapi->action(array('key' =>$api["api_key"],'action' =>'balance'),$api["api_url"]);
                foreach ($services as $apiService):
                  if( $service == $apiService->service ):
                    $detail["min"]=$apiService->min;
                    $detail["max"]=$apiService->max;
                    $detail["rate"]=$apiService->rate;
                    $detail["currency"]=$balance->currency;
                    $detail=json_encode($detail);
                  endif;
                endforeach;
            else:
              $detail="";
            endif;
            if( $package == 14 || $package == 15 ): $min = $limited_min; $max = $min; $price = $limited_price; endif;
            $row = $conn->query("SELECT * FROM services WHERE category_id='$category' ORDER BY service_line DESC LIMIT 1 ")->fetch(PDO::FETCH_ASSOC);
            $conn->beginTransaction();
            $insert = $conn->prepare("INSERT INTO services SET name_lang=:multiName, service_speed=:speed, service_api=:api, api_service=:api_service, api_detail=:detail, category_id=:category, service_line=:line, service_type=:type, service_package=:package, service_name=:name, service_price=:price, service_min=:min, service_max=:max, service_autotime=:autotime, service_autopost=:autopost, service_secret=:secret ");
            $insert = $insert-> execute(array("api"=>$provider,"multiName"=>$multiName,"speed"=>$speed,"detail"=>$detail,"api_service"=>$service,"category"=>$cat,"line"=>$row["service_line"]+1,"type"=>2,"package"=>$package,"name"=>$name,"price"=>$price,"min"=>$min,"max"=>$max,"autotime"=>$autotime,"autopost"=>$autopost,"secret"=>$secret ));
            if( $insert ):
              $conn->commit();
              $error    = 1;
              $errorText= "Success";
              $referrer = site_url("admin/services");
              $icon     = "success";
            else:
              $conn->rollBack();
              $error    = 1;
              $errorText= "Failed";
              $icon     = "error";
            endif;
        endif;
        echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon,"r"=>$referrer]);
      endif;
  elseif( $action == "edit-subscription" ):
      if( $_POST ):
        $language   = $conn->prepare("SELECT * FROM languages WHERE default_language=:default");
        $language->execute(array("default"=>1));
        $language   = $language->fetch(PDO::FETCH_ASSOC);
        foreach ($_POST as $key => $value) {
          $$key = $value;
        }
        // ismi değiştirdiği alan servicslerden
         $cat = intval(@$_POST["category"]);
          $name      = $_POST["name"][$language["language_code"]];
          $multiName = json_encode($_POST["name"]);
        $serviceInfo  = $conn->prepare("SELECT * FROM services INNER JOIN service_api ON service_api.id = services.service_api WHERE service_id=:id ");
        $serviceInfo -> execute(array("id"=>route(3) ));
        $serviceInfo  = $serviceInfo->fetch(PDO::FETCH_ASSOC);
        if( empty($name) ):
          $error    = 1;
          $errorText= "Ürün adı boş olamaz";
          $icon     = "error";
        elseif( empty($category) ):
          $error    = 1;
          $errorText= "Ürün kategori boş olamaz";
          $icon     = "error";
        elseif( empty($provider) ):
          $error    = 1;
          $errorText= "Servis sağlayıcı boş olamaz";
          $icon     = "error";
        elseif( empty($service) ):
          $error    = 1;
          $errorText= "Servis sağlayıcı servis bilgisi boş olamaz";
          $icon     = "error";
        elseif( empty($secret) ):
          $error    = 1;
          $errorText= "Servis gizliliği boş olamaz";
        elseif(  ( $serviceInfo["service_package"] == 11 || $serviceInfo["service_package"] == 12 ) && !is_numeric($price) ):
          $error    = 1;
          $errorText= "Ürün fiyatı rakamlardan oluşmalı";
          $icon     = "error";
        elseif( ( $serviceInfo["service_package"] == 11 || $serviceInfo["service_package"] == 12 ) && !is_numeric($min) ):
          $error    = 1;
          $errorText= "Minimum sipariş miktarı boş olamaz";
          $icon     = "error";
        elseif( ( $serviceInfo["service_package"] == 11 || $serviceInfo["service_package"] == 12 ) && !is_numeric($max) ):
          $error    = 1;
          $errorText= "Maksimum sipariş miktarı boş olamaz";
          $icon     = "error";
        elseif( ( $serviceInfo["service_package"] == 11 || $serviceInfo["service_package"] == 12 ) && $min > $max ):
          $error    = 1;
          $errorText= "Minimum sipariş miktarı maksimum sipariş miktarından fazla olamaz";
          $icon     = "error";
        elseif(  ( $serviceInfo["service_package"] == 14 || $serviceInfo["service_package"] == 15 ) && !is_numeric($autopost) ):
          $error    = 1;
          $errorText= "Gönderi miktarı boş olamaz";
          $icon     = "error";
        elseif(  ( $serviceInfo["service_package"] == 14 || $serviceInfo["service_package"] == 15 ) && !is_numeric($limited_min) ):
          $error    = 1;
          $errorText= "Sipariş miktarı boş olamaz";
          $icon     = "error";
        elseif(  ( $serviceInfo["service_package"] == 14 || $serviceInfo["service_package"] == 15 ) && !is_numeric($autotime) ):
          $error    = 1;
          $errorText= "Paket Süresi boş olamaz";
          $icon     = "error";
        else:
            $api=$conn->prepare("SELECT * FROM service_api WHERE id=:id "); $api->execute(array("id"=>$provider)); $api=$api->fetch(PDO::FETCH_ASSOC);
            if( $mode == 1 ): $provider = 0; $service = 0; endif;
            if( $mode == 2 && $api["api_type"] == 1 ):
              $smmapi   = new SMMApi(); $services = $smmapi->action(array('key' =>$api["api_key"],'action' =>'services'),$api["api_url"]); $balance = $smmapi->action(array('key' =>$api["api_key"],'action' =>'balance'),$api["api_url"]);
                foreach ($services as $apiService):
                  if( $service == $apiService->service ):
                    $detail["min"]=$apiService->min;
                    $detail["max"]=$apiService->max;
                    $detail["rate"]=$apiService->rate;
                    $detail["currency"]=$balance->currency;
                    $detail=json_encode($detail);
                  endif;
                endforeach;
            else:
              $detail="";
            endif;
            if( $serviceInfo["service_package"] == 14 || $serviceInfo["service_package"] == 15 ): $min = $limited_min; $max = $min; $price = $limited_price; endif;
            if( $serviceInfo["category_id"] != $category ): $row = $conn->query("SELECT * FROM services WHERE category_id='$category' ORDER BY service_line DESC LIMIT 1 ")->fetch(PDO::FETCH_ASSOC); $last_category=$serviceInfo["category_id"]; $last_line=$serviceInfo["service_line"]; $line= $row["service_line"] + 1; else: $line= $serviceInfo["service_line"]; endif;
            $conn->beginTransaction();
			// abone update işlem yeri
            $update = $conn->prepare("UPDATE services SET 
			service_speed=:speed, 
			service_api=:api,
			api_servicetype=:type, 
			api_service=:api_service, 
			api_detail=:detail,
			category_id=:category, 
			service_name=:name, 
			service_price=:price, 
			service_min=:min, 
			service_max=:max, 
			service_autotime=:autotime, 
			service_autopost=:autopost,
      name_lang=:name_lang,
			service_secret=:secret 
			WHERE service_id=:id ");
            $update = $update-> execute(array("id"=>route(3),"type"=>2,"speed"=>$speed,"detail"=>$detail,"api"=>$provider,"api_service"=>$service,"category"=>$category,"name"=>$name,"price"=>$price,"min"=>$min,"max"=>$max,"autotime"=>$autotime,"autopost"=>$autopost,"name_lang"=>$multiName,"secret"=>$secret ));
            if( $update ):
              $conn->commit();
              $rows = $conn->prepare("SELECT * FROM services WHERE category_id=:c_id && service_line>=:line ");
              $rows->execute(array("c_id"=>$last_category,"line"=>$last_line ));
              $rows = $rows->fetchAll(PDO::FETCH_ASSOC);
                foreach( $rows as $row ):
                  $update = $conn->prepare("UPDATE services SET service_line=:line WHERE service_id=:id ");
                  $update->execute(array("line"=>$row["service_line"]-1,"id"=>$row["service_id"] ));
                endforeach;
              $error    = 1;
              $errorText= "Success";
              $referrer = site_url("admin/services");
              $icon     = "success";
            else:
              $conn->rollBack();
              $error    = 1;
              $errorText= "Failed";
              $icon     = "error";
            endif;
        endif;
        echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon,"r"=>$referrer]);
      endif;
  elseif( $action == "service-active" ):
    $service_id  = route(3);
    if( countRow(["table"=>"services","where"=>["client_id"=>$service_id,"service_type"=>2]]) ): header("Location:".site_url("admin/services")); exit(); endif;
    $update = $conn->prepare("UPDATE services SET service_type=:type WHERE service_id=:id ");
    $update->execute(array("type"=>2,"id"=>$service_id));
      if( $update ):
        $_SESSION["client"]["data"]["success"]    = 1;
        $_SESSION["client"]["data"]["successText"]= "Success";
      else:
        $_SESSION["client"]["data"]["error"]    = 1;
        $_SESSION["client"]["data"]["errorText"]= "Failed";
      endif;
    header("Location:".site_url("admin/services"));
  elseif( $action == "service-deactive" ):
    $service_id  = route(3);
    if( countRow(["table"=>"services","where"=>["service_id"=>$service_id,"service_type"=>1]]) ): header("Location:".site_url("admin/services")); exit(); endif;
    $update = $conn->prepare("UPDATE services SET service_type=:type WHERE service_id=:id ");
    $update->execute(array("type"=>1,"id"=>$service_id));
      if( $update ):
        $_SESSION["client"]["data"]["success"]    = 1;
        $_SESSION["client"]["data"]["successText"]= "Success";
      else:
        $_SESSION["client"]["data"]["error"]    = 1;
        $_SESSION["client"]["data"]["errorText"]= "Failed";
      endif;
      header("Location:".site_url("admin/services"));
  elseif( $action == "del_price" ):
    $service_id  = route(3);
    if( !countRow(["table"=>"clients_price","where"=>["service_id"=>$service_id]]) ): $_SESSION["client"]["data"]["error"]    = 1; $_SESSION["client"]["data"]["errorText"]= "Servise ait fiyatlandırma bulunamadı."; header("Location:".site_url("admin/services")); exit(); endif;
    $delete = $conn->prepare("DELETE FROM clients_price  WHERE service_id=:id ");
    $delete->execute(array("id"=>$service_id));
      if( $delete ):
        $_SESSION["client"]["data"]["success"]    = 1;
        $_SESSION["client"]["data"]["successText"]= "Success";
      else:
        $_SESSION["client"]["data"]["error"]    = 1;
        $_SESSION["client"]["data"]["errorText"]= "Failed";
      endif;
    header("Location:".site_url("admin/services"));
  elseif( $action == "category-active" ):
    $category_id  = route(3);
    $update = $conn->prepare("UPDATE categories SET category_type=:type WHERE category_id=:id ");
    $update->execute(array("type"=>2,"id"=>$category_id));
      if( $update ):
        $_SESSION["client"]["data"]["success"]    = 1;
        $_SESSION["client"]["data"]["successText"]= "Success";
      else:
        $_SESSION["client"]["data"]["error"]    = 1;
        $_SESSION["client"]["data"]["errorText"]= "Failed";
      endif;
    header("Location:".site_url("admin/services"));
  elseif( $action == "category-deactive" ):
    $category_id  = route(3);
    $update = $conn->prepare("UPDATE categories SET category_type=:type WHERE category_id=:id ");
    $update->execute(array("type"=>1,"id"=>$category_id));
      if( $update ):
        $_SESSION["client"]["data"]["success"]    = 1;
        $_SESSION["client"]["data"]["successText"]= "Success";
      else:
        $_SESSION["client"]["data"]["error"]    = 1;
        $_SESSION["client"]["data"]["errorText"]= "Failed";
      endif;
      header("Location:".site_url("admin/services"));
  elseif( $action == "multi-action" ):
    $services = $_POST["service"];
    $action   = $_POST["bulkStatus"];
      if( $action ==  "active" ):
        foreach ($services as $id => $value):
          $update = $conn->prepare("UPDATE services SET service_type=:type WHERE service_id=:id ");
          $update->execute(array("type"=>2,"id"=>$id));
        endforeach;
      elseif( $action ==  "deactive" ):
        foreach ($services as $id => $value):
          $update = $conn->prepare("UPDATE services SET service_type=:type WHERE service_id=:id ");
          $update->execute(array("type"=>1,"id"=>$id));
        endforeach;
      elseif( $action ==  "secret" ):
        foreach ($services as $id => $value):
          $update = $conn->prepare("UPDATE services SET service_secret=:secret WHERE service_id=:id ");
          $update->execute(array("secret"=>1,"id"=>$id));
        endforeach;
      elseif( $action ==  "desecret" ):
        foreach ($services as $id => $value):
          $update = $conn->prepare("UPDATE services SET service_secret=:secret WHERE service_id=:id ");
          $update->execute(array("secret"=>2,"id"=>$id));
        endforeach;
      elseif( $action ==  "del_price" ):
        foreach ($services as $id => $value):
          $delete = $conn->prepare("DELETE FROM clients_price  WHERE service_id=:id ");
          $delete->execute(array("id"=>$id));
        endforeach;
        elseif( $action == "del_service" ):
               foreach ($services as $id => $value):
      $delete = $conn->prepare("DELETE FROM services WHERE service_id=:id ");
      $delete->execute(array("id"=>$id));
    endforeach;
      endif;
    header("Location:".site_url("admin/services"));
  elseif( $action == "get_services_add" ):
    $services     = $_POST["servicesList"];
    $provider_id  = $_POST["provider"];
    $percentage_increase = $_POST["percentage_increase"];
    $currency     = $conn->prepare("SELECT * FROM settings WHERE id=:id");
    $currency     ->execute(array("id"=>"1"));
    $currency     = $currency->fetch(PDO::FETCH_ASSOC);
    $conv_rate = $currency["dolar_charge"];
    $smmapi       = new SMMApi();
    $provider     = $conn->prepare("SELECT * FROM service_api WHERE id=:id");
    $provider     ->execute(array("id"=>$provider_id));
    $provider     = $provider->fetch(PDO::FETCH_ASSOC);
    $apiServices  = $smmapi->action(array('key'=>$provider["api_key"],'action'=>'services'),$provider["api_url"]);
    $balance      = $smmapi->action(array('key'=>$provider["api_key"],'action'=>'balance'),$provider["api_url"]);
      if( count($services) ):
        foreach ($services as $service => $price):
          foreach ($apiServices as $apiService):
            
            // die();
            if( $service == $apiService->service && $service != 0 ):
                
                $check_category = $conn->prepare("SELECT * FROM categories WHERE category_name=:name");
                $check_category->execute(array("name"=>$apiService->category));
                $check_category = $check_category->fetch(PDO::FETCH_ASSOC);
                if(!empty($check_category)){
                    $cat = $check_category["category_id"];
                }else{
                    $check_category = $conn->prepare("SELECT * FROM categories ORDER BY category_line DESC LIMIT 1");
                    $check_category->execute();
                    $check_category = $check_category->fetch(PDO::FETCH_ASSOC);
                    $insertcat = $conn->prepare("INSERT INTO categories SET category_name=:name, category_line=:line, category_type=:type, category_secret=:secret, category_icon=:icon, is_refill=:refill ");
                    $insertcat = $insertcat->execute(array("name"=>$apiService->category,"line"=>$check_category["category_line"]+1,"type"=>"2","secret"=>"2","icon"=>"","refill"=>"false" ));
                    $cat = $conn->lastInsertId();
                }    
              $detail["min"]=$apiService->min;
              $detail["max"]=$apiService->max;
              $detail["rate"]=$apiService->rate;
              $detail["currency"]=$balance->currency;
              $package= serviceTypeGetList($apiService->type);
              $name2 = $apiService->name;
              
              $numberToAdd = ($price / 100) * $percentage_increase;
              $price = $price+$numberToAdd;

           if( $currency["site_currency"] == "INR" ):    
                
                   
              if($provider["currency"] == "INR"){
                  $price = $price;    
              }else{
                  $price = $price*$conv_rate;
              }
                
                if( $package == 11 ):
                    
                  $insert = $conn->prepare("INSERT INTO services SET service_api=:api, api_service=:api_service, category_id=:category, service_line=:line, service_type=:type, service_package=:package, service_name=:name, service_price=:price, service_min=:min, service_max=:max, service_description=:desc ");
                  $insert = $insert-> execute(array("api"=>$provider_id,"api_service"=>$service,"detail"=>json_encode($detail),"category"=>$cat,"line"=>1,"type"=>2,"package"=>$package,"name"=>$name2,"price"=>$price,"min"=>$apiService->min,"max"=>$apiService->max,"desc"=>$apiService->desc ));
                else:
                    $package = $package==""?1:$package;
                   
                  $insert = $conn->prepare("INSERT INTO services SET service_api=:api, api_service=:api_service, api_detail=:detail, category_id=:category, service_line=:line, service_type=:type, service_package=:package, service_name=:name, service_price=:price, service_min=:min, service_max=:max, service_description=:desc ");
                  $insert = $insert-> execute(array("api"=>$provider_id,"api_service"=>$service,"detail"=>json_encode($detail),"category"=>$cat,"line"=>1,"type"=>2,"package"=>$package,"name"=>$apiService->name,"price"=>$price,"min"=>$apiService->min,"max"=>$apiService->max,"desc"=>$apiService->desc ));
                endif;
                
            else:
                
                if($provider["currency"] == "INR"){
                    $foo = $price/$conv_rate;
                    $formatted_price = number_format((float)$foo, 2, '.', ''); 
                }else{
                    $formatted_price = $price;
                }
                
                
                if( $package == 11 ):
                    
                  $insert = $conn->prepare("INSERT INTO services SET service_api=:api, api_service=:api_service, category_id=:category, service_line=:line, service_type=:type, service_package=:package, service_name=:name, service_price=:price, service_min=:min, service_max=:max, service_description=:desc ");
                  $insert = $insert-> execute(array("api"=>$provider_id,"api_service"=>$service,"detail"=>json_encode($detail),"category"=>$cat,"line"=>1,"type"=>2,"package"=>$package,"name"=>$name2,"price"=>$formatted_price,"min"=>$apiService->min,"max"=>$apiService->max,"desc"=>$apiService->desc ));
                else:
                    $package = $package==""?1:$package;
                   
                  $insert = $conn->prepare("INSERT INTO services SET service_api=:api, api_service=:api_service, api_detail=:detail, category_id=:category, service_line=:line, service_type=:type, service_package=:package, service_name=:name, service_price=:price, service_min=:min, service_max=:max, service_description=:desc ");
                  $insert = $insert-> execute(array("api"=>$provider_id,"api_service"=>$service,"detail"=>json_encode($detail),"category"=>$cat,"line"=>1,"type"=>2,"package"=>$package,"name"=>$apiService->name,"price"=>$formatted_price,"min"=>$apiService->min,"max"=>$apiService->max,"desc"=>$apiService->desc ));
                endif;
            endif;
            endif;
          endforeach;
        endforeach;
        echo json_encode(["t"=>"error","m"=>"Success","s"=>"success","r"=>site_url("admin/services"),"time"=>0]);
      else:
        echo json_encode(["t"=>"error","m"=>"Lütfen eklemek istediğiniz en az 1 servisi seçin","s"=>"error"]);
      endif;
  endif;
  
   if( route(2) == "delete" ):
    $id     = route(3);
    $delete = $conn->prepare("DELETE FROM services WHERE service_id=:id ");
    $delete->execute(array("id"=>$id));
    header("Location:".site_url("admin/services"));
  endif;
  
