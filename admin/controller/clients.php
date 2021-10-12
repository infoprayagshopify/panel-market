<?php

  if( $user["access"]["users"] != 1  ):
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
      if( $_GET["search"] ):
        $search_where = $_GET["search_type"];
        $search_word  = urldecode($_GET["search"]);
        $search       = $search_where." LIKE '%".$search_word."%'";
        $count        = $conn->prepare("SELECT * FROM clients WHERE {$search}");
        $count        -> execute(array());
        $count        = $count->rowCount();
        $search       = "WHERE {$search}";
        $search_link  = "?search=".$search_word."&search_type=".$search_where;
      else:
        $count          = $conn->prepare("SELECT * FROM clients");
        $count        ->execute(array());
        $count          = $count->rowCount();
      endif;
    $to             = 100;
    $pageCount      = ceil($count/$to); if( $page > $pageCount ): $page = 1; endif;
    $where          = ($page*$to)-$to;
    $paginationArr  = ["count"=>$pageCount,"current"=>$page,"next"=>$page+1,"previous"=>$page-1];
    $clients        = $conn->prepare("SELECT * FROM clients $search ORDER BY client_id DESC LIMIT $where,$to ");
    $clients        -> execute(array());
    $clients        = $clients->fetchAll(PDO::FETCH_ASSOC);
    require admin_view('clients');
  elseif( $action == "new" ):
      if( $_POST ):
        $name       = $_POST["name"];
        $email      = $_POST["email"];
        $username   = $_POST["username"];
        $pass       = $_POST["password"];
        $tel        = $_POST["telephone"];
        $tel_type   = $_POST["tel_type"];
        $email_type = $_POST["email_type"];
        $access     = $_POST["access"];
        $admin      = $_POST["access"]["admin_access"];
        $debit      = $_POST["balance_type"];
        $debit_limit= $_POST["debit_limit"];

        if( empty($name) || strlen($name) < 5 ){
          $error      = 1;
          $errorText  = "Member name must be at least 5 characters";
          $icon     = "error";
        }elseif( !email_check($email) ){
          $error      = 1;
          $errorText  = "Please enter a valid email format.";
          $icon     = "error";
        }elseif( userdata_check("email",$email) ){
          $error      = 1;
          $errorText  = "The email address you entered is used.";
          $icon     = "error";
        }elseif( !username_check($username) ){
          $error      = 1;
          $errorText  = "The username must contain a minimum of 4 and a maximum of 32 characters, including letters and numbers.";
          $icon     = "error";
        }elseif( userdata_check("username",$username) ){
          $error      = 1;
          $errorText  = "The username you specified is used.";
          $icon     = "error";
        }elseif( !empty($phone) && userdata_check("telephone",$phone) ){
          $error      = 1;
          $errorText  = "The phone number you specified is used.";
          $icon     = "error";
        }elseif( strlen($pass) < 8 ){
          $error      = 1;
          $errorText  = "Password must be at least 8 characters.";
          $icon     = "error";
        }else{
          $apikey = CreateApiKey($_POST);
          $conn->beginTransaction();
          $insert = $conn->prepare("INSERT INTO clients SET name=:name, username=:username, email=:email, password=:pass, lang=:lang, telephone=:phone, register_date=:date, apikey=:key, access=:access, tel_type=:tel_type, email_type=:email_type ");
          $insert = $insert-> execute(array("lang"=>"en","name"=>$name,"username"=>$username,"email"=>$email,"pass"=>md5(sha1(md5($pass))),"phone"=>$tel,"date"=>date("Y.m.d H:i:s"),'key'=>$apikey,'access'=>json_encode($access),'tel_type'=>$tel_type,'email_type'=>$email_type ));
          if( $insert ):
            $conn->commit();
            $referrer = site_url("admin/clients");
            $error    = 1;
            $errorText= "Success";
            $icon     = "success";
          else:
            $conn->rollBack();
            $error    = 1;
            $errorText= "Failed";
            $icon     = "error";
          endif;
        }
        echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon,"r"=>$referrer]);
      endif;
  elseif( $action == "edit" ):
    $username  = route(3);
    if( !countRow(["table"=>"clients","where"=>["username"=>$username]]) ): header("Location:".site_url("admin/clients")); exit(); endif;
    $client_detail  = getRow(["table"=>"clients","where"=>["username"=>$username]]);
    $client_access  = json_decode($client_detail["access"],true);
        if( $_POST ):
          $name       = $_POST["name"];
          $email      = $_POST["email"];
          $tel        = $_POST["telephone"];
          $tel_type   = $_POST["tel_type"];
          $email_type = $_POST["email_type"];
          $access     = $_POST["access"];
          $admin      = $_POST["access"]["admin_access"];
          $debit      = $_POST["balance_type"];

          if( empty($name) || strlen($name) < 5 ){
            $error      = 1;
            $errorText  = "Member name must be at least 5 characters";
            $icon     = "error";
          }elseif( !email_check($email) ){
            $error      = 1;
            $errorText  = "Please enter a valid email format.";
            $icon     = "error";
          }elseif( $conn->query("SELECT * FROM clients WHERE username!='$username' && email='$email' ")->rowCount() ){
            $error      = 1;
            $errorText  = "The email address you entered is used.";
            $icon     = "error";
          }elseif( !username_check($username) ){
            $error      = 1;
            $errorText  = "The username must contain a minimum of 4 and a maximum of 32 characters, including letters and numbers.";
            $icon     = "error";
          }elseif( !empty($phone) && $conn->query("SELECT * FROM clients WHERE username!='$username' && telephone='$telephone' ")->rowCount() ){
            $error      = 1;
            $errorText  = "The phone number you specified is used.";
            $icon     = "error";
          }else{
            $apikey = CreateApiKey($_POST);
            $conn->beginTransaction();
            
            $insert = $conn->prepare("UPDATE clients SET name=:name, balance_type=:balance_type, debit_limit=:debit_limit, email=:email, telephone=:phone, register_date=:date, tel_type=:tel_type, email_type=:email_type, access=:access WHERE username=:id ");
            $insert = $insert-> execute(array("id"=>route(3),"balance_type"=>$debit,"debit_limit"=>$debit_limit,"name"=>$name,"email"=>$email,"phone"=>$tel,"date"=>date("Y.m.d H:i:s"),'tel_type'=>$tel_type,'email_type'=>$email_type,'access'=>json_encode($access) ));
            if( $insert ):
              $conn->commit();
              $referrer = site_url("admin/clients");
              $error    = 1;
              $errorText= "Success";
              $icon     = "success";
            else:
              $conn->rollBack();
              $error    = 1;
              $errorText= "Failed";
              $icon     = "error";
            endif;
          }
          echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon,"r"=>$referrer]);
        endif;
  elseif( $action == "pass" ):
    $username  = route(3);
    // if( !countRow(["table"=>"clients","where"=>["username"=>$username]]) ): header("Location:".site_url("admin/clients")); exit(); endif;
    $client_detail  = getRow(["table"=>"clients","where"=>["username"=>$username]]);
    $client_access  = json_decode($client_detail["access"],true);
        if( $_POST ):
          $password = $_POST["password"];

          if( strlen($password) < 8 ){
            $error      = 1;
            $errorText  = "Password must be at least 8 characters.";
            $icon       = "error";
          }else{
            $conn->beginTransaction();
            $insert = $conn->prepare("UPDATE clients SET password=:pass WHERE username=:username ");
            $insert = $insert-> execute(array("username"=>$username,"pass"=>md5(sha1(md5($password))) ));
            // $conn->commit();
            if( $insert ):
              $conn->commit();
              $referrer = site_url("admin/clients");
              $error    = 1;
              $errorText= "Success";
              $icon     = "success";
            else:
              $conn->rollBack();
              $error    = 1;
              $errorText= "Failed";
              $icon     = "error";
            endif;
          }
          echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon,"r"=>$referrer]);
        endif;
  elseif( $action == "export" ):
      if( $_POST ):
        $format           = $_POST["format"]; // XML,CSV,JSON
        $export_status    = $_POST["client_status"]; // Tümü (-1), Aktif (1), Pasif (0)
        $colums           = $_POST["exportcolumn"]; //Üye bilgileri
        $email_status     = $_POST["email_status"]; //Üye bilgileri
        $export           = array();
        $colum            = "";
        foreach ($colums as $key => $value) {
           $colum.=$key.",";
        }
        $colum  = rtrim($colum,",");

        $where  = "WHERE ";
        if( $export_status != "all" ): $where.="client_type=".$export_status." &&"; endif;
        if( $email_status != "all" ): $where.="email_type=".$email_status; else: if( substr($where,-2) == "&&" ): $where = substr($where,0,-2); endif; endif;
        if( $where == "WHERE " ): $where = ""; endif;

          $row  = $conn->prepare("SELECT $colum FROM clients $where ORDER BY client_id DESC ");
          $row-> execute(array());
          $row  = $row->fetchAll(PDO::FETCH_OBJ);
          $rows  = json_encode($row);


        if( $format == "json" ):
          $fp = fopen('users.json', 'w');
          fwrite($fp, json_encode($row, JSON_PRETTY_PRINT));
          fclose($fp);
          force_download('users.json');
          unlink('users.json');
        endif;

      endif;
  elseif( $action == "price" ):
    if( $_POST ):
      $client = route(3);
      foreach( $_POST["price"] as $id => $price ):
        if( $price == null ):
          $delete = $conn->prepare("DELETE FROM clients_price WHERE client_id=:client && service_id=:service ");
          $delete->execute(array("service"=>$id,"client"=>$client));
        elseif( getRow(["table"=>"clients_price","where"=>["client_id"=>$client,"service_id"=>$id] ]) ):
          $update = $conn->prepare("UPDATE clients_price SET client_id=:client, service_price=:price WHERE service_id=:service && client_id=:clientt ");
          $update->execute(array("service"=>$id,"client"=>$client,"clientt"=>$client,"price"=>$price));
        else:
          $insert = $conn->prepare("INSERT INTO clients_price SET client_id=:client, service_price=:price, service_id=:service ");
          $insert->execute(array("service"=>$id,"client"=>$client,"price"=>$price));
        endif;
      endforeach;
      $error    = 1;
      $errorText= "Success";
      $icon     = "success";
      echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon,"r"=>$referrer]);
      exit();
    endif;
    $username  = route(3);
    if( !countRow(["table"=>"clients","where"=>["username"=>$username]]) ): header("Location:".site_url("admin/clients")); exit(); endif;
    $client_detail  = getRow(["table"=>"clients","where"=>["username"=>$username]]);
    $client_access  = json_decode($client_detail["access"],true);
    $services       = $conn->prepare("SELECT * FROM services ORDER BY service_id ASC ");
    $services->execute(array());
    $services       = $services->fetchAll(PDO::FETCH_ASSOC);
    $serviceList    = [];
      foreach ($services as $service) {
        $price  = getRow(["table"=>"clients_price","where"=>["service_id"=>$service["service_id"],"client_id"=>$client_detail["client_id"]]]);
        $service["client_price"]  = $price["service_price"];
        array_push($serviceList,$service);
      }
  elseif( $action == "active" ):
    $client_id  = route(3);
    if( countRow(["table"=>"clients","where"=>["client_id"=>$client_id,"client_type"=>2]]) ): header("Location:".site_url("admin/clients")); exit(); endif;
    $update = $conn->prepare("UPDATE clients SET client_type=:type WHERE client_id=:id ");
    $update->execute(array("type"=>2,"id"=>$client_id));
      if( $update ):
        $_SESSION["client"]["data"]["success"]    = 1;
        $_SESSION["client"]["data"]["successText"]= "Success";
      else:
        $_SESSION["client"]["data"]["error"]    = 1;
        $_SESSION["client"]["data"]["errorText"]= "Failed";
      endif;
    header("Location:".site_url("admin/clients"));
  elseif( $action == "deactive" ):
    $client_id  = route(3);
    if( countRow(["table"=>"clients","where"=>["client_id"=>$client_id,"client_type"=>1]]) ): header("Location:".site_url("admin/clients")); exit(); endif;
    $update = $conn->prepare("UPDATE clients SET client_type=:type WHERE client_id=:id ");
    $update->execute(array("type"=>1,"id"=>$client_id));
      if( $update ):
        $_SESSION["client"]["data"]["success"]    = 1;
        $_SESSION["client"]["data"]["successText"]= "Success";
      else:
        $_SESSION["client"]["data"]["error"]    = 1;
        $_SESSION["client"]["data"]["errorText"]= "Failed";
      endif;
      header("Location:".site_url("admin/clients"));
  elseif( $action == "del_price" ):
    $client_id  = route(3);
    if( !countRow(["table"=>"clients_price","where"=>["client_id"=>$client_id]]) ): $_SESSION["client"]["data"]["error"]    = 1; $_SESSION["client"]["data"]["errorText"]= "Pricing for the member was not found."; header("Location:".site_url("admin/clients")); exit(); endif;
    $delete = $conn->prepare("DELETE FROM clients_price  WHERE client_id=:id ");
    $delete->execute(array("id"=>$client_id));
      if( $delete ):
        $_SESSION["client"]["data"]["success"]    = 1;
        $_SESSION["client"]["data"]["successText"]= "Success";
      else:
        $_SESSION["client"]["data"]["error"]    = 1;
        $_SESSION["client"]["data"]["errorText"]= "Failed";
      endif;
    header("Location:".site_url("admin/clients"));
  elseif( $action == "change_apikey" ):
    $client_id  = route(3);
    $client_detail  = getRow(["table"=>"clients","where"=>["client_id"=>$client_id]]);
    $apikey     = CreateApiKey(["email"=>$client_detail["email"],"username"=>$client_detail["username"]]);
    if( countRow(["table"=>"clients","where"=>["client_id"=>$client_id,"client_type"=>1]]) ): header("Location:".site_url("admin/clients")); exit(); endif;
    $update = $conn->prepare("UPDATE clients SET apikey=:key WHERE client_id=:id ");
    $update->execute(array("key"=>$apikey,"id"=>$client_id));
      if( $update ):
        $_SESSION["client"]["data"]["success"]    = 1;
        $_SESSION["client"]["data"]["successText"]= "Success";
      else:
        $_SESSION["client"]["data"]["error"]    = 1;
        $_SESSION["client"]["data"]["errorText"]= "Failed";
      endif;
      header("Location:".site_url("admin/clients"));
  elseif( $action == "secret_category" ):
    $client = route(3);
    $type   = $_GET["type"];
    $id     = $_GET["id"];
      if( $type == "on" ):
        $search   = $conn->query("SELECT * FROM clients_category WHERE client_id='$client' && category_id='$id' ");
        if( !$search->rowCount() ):
          $insert = $conn->prepare("INSERT INTO clients_category SET client_id=:client, category_id=:c_id  ");
          $insert->execute(array("client"=>$client,"c_id"=>$id));
            if( $insert ):
              echo "1";
            else:
              echo "0";
            endif;
        else:
          echo "0";
        endif;
      elseif( $type == "off" ):
        $search   = $conn->query("SELECT * FROM clients_category WHERE client_id='$client' && category_id='$id' ");
        if( $search->rowCount() ):
          $delete = $conn->prepare("DELETE FROM clients_category WHERE client_id=:client && category_id=:c_id  ");
          $delete->execute(array("client"=>$client,"c_id"=>$id));
            if( $delete ):
              echo "1";
            else:
              echo "0";
            endif;
          else:
            echo "0";
          endif;
      endif;
  elseif( $action == "secret_service" ):
    $client = route(3);
    $type   = $_GET["type"];
    $id     = $_GET["id"];
      if( $type == "on" ):
        $search   = $conn->query("SELECT * FROM clients_service WHERE client_id='$client' && service_id='$id' ");
        if( !$search->rowCount() ):
          $insert = $conn->prepare("INSERT INTO clients_service SET client_id=:client, service_id=:c_id   ");
          $insert->execute(array("client"=>$client,"c_id"=>$id));
            if( $insert ):
              echo "1";
            else:
              echo "0";
            endif;
          else:
            echo "0";
        endif;
      elseif( $type == "off" ):
        $search   = $conn->query("SELECT * FROM clients_service WHERE client_id='$client' && service_id='$id' ");
        if( $search->rowCount() ):
          $delete = $conn->prepare("DELETE FROM clients_service WHERE client_id=:client && service_id=:c_id  ");
          $delete->execute(array("client"=>$client,"c_id"=>$id));
            if( $delete ):
              echo "1";
            else:
              echo "0";
            endif;
        else:
          echo "0";
        endif;
      endif;
  elseif( $action == "alert" ):
    $subject  = $_POST["subject"];
    $type     = $_POST["alert_type"];
    $message  = $_POST["message"];
    $user     = $_POST["user_type"];
    $username = $_POST["username"];
      if( $user == "secret" && !getRow(["table"=>"clients","where"=>["username"=>$username]]) ):
        $error    = 1;
        $errorText= "User not found";
        $icon     = "error";
      elseif( empty($message) ):
        $error    = 1;
        $errorText= "Notification Message cannot be empty";
        $icon     = "error";
      elseif( $type == "email" && $user == "all" ):
        $users  = $conn->prepare("SELECT * FROM clients ");
        $users->execute(array());
        $users  = $users->fetchAll(PDO::FETCH_ASSOC);
        $email= array();
        foreach ($users as $user):
          $email[]  = $user["email"];
        endforeach;
        sendMail(["subject"=>$subject,"body"=>$message,"mail"=>$email]);
        $error    = 1;
        $errorText= "Success";
        $icon     = "success";
      elseif( $type == "email" && $user == "secret" ):
        $user= getRow(["table"=>"clients","where"=>["username"=>$username]]);
        if( sendMail(["subject"=>$subject,"body"=>$message,"mail"=>$user["email"]]) ):
          $error    = 1;
          $errorText= "Success";
          $icon     = "success";
        else:
          $error    = 1;
          $errorText= "Failed";
          $icon     = "error";
        endif;
      elseif( $type == "sms" && $user == "secret" ):
          $user= getRow(["table"=>"clients","where"=>["username"=>$username]]);
          $sms = SMSUser($user["telephone"],$message);
            if( $sms ):
              $error    = 1;
              $errorText= "Success";
              $icon     = "success";
            else:
              $error    = 1;
              $errorText= "Failed";
              $icon     = "error";
            endif;
      elseif( $type == "sms" && $user == "all" ):
        $users  = $conn->prepare("SELECT * FROM clients ");
        $users->execute(array());
        $users  = $users->fetchAll(PDO::FETCH_ASSOC);
        $tel    = "";
        foreach ($users as $user):
          $tel .= "<no>".$user["telephone"]."</no>";
        endforeach;
        $sms = SMSToplu($tel,$message);
          if( $sms ):
            $error    = 1;
            $errorText= "Success";
            $icon     = "success";
          else:
            $error    = 1;
            $errorText= "Failed";
            $icon     = "error";
          endif;
      endif;
      echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon,"r"=>$referrer]);
  endif;
