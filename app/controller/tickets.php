<?php

$title .= $languageArray["tickets.title"];

if( $_SESSION["msmbilisim_userlogin"] != 1  || $user["client_type"] == 1  ){
  Header("Location:".site_url('logout'));
}

if( !route(1) ){

  $tickets = $conn->prepare("SELECT * FROM tickets WHERE client_id=:c_id ORDER BY lastupdate_time DESC ");
  $tickets-> execute(array("c_id"=>$user["client_id"]));
  $tickets = $tickets->fetchAll(PDO::FETCH_ASSOC);
  $ticketList = [];
    foreach ($tickets as $ticket) {
      foreach ($ticket as $key => $value) {
        if( $key == "status" ){
          $t[$key] = $languageArray["tickets.status.".$value];
        }else{
          $t[$key] = $value;
        }
      }
      array_push($ticketList,$t);
    }

  if( $_POST ){
    foreach ($_POST as $key => $value) {
      $_SESSION["data"][$key]  = $value;
    }
    $subject  = $_POST["subject"];
    $message  = htmlspecialchars($_POST["message"]);
      if( empty($subject) ){
        $error    = 1;
        $errorText= $languageArray["error.tickets.new.subject"];
      }elseif( strlen(str_replace(' ','',$message)) < 10 ){
        $error    = 1;
        $errorText= str_replace("{length}","10",$languageArray["error.tickets.new.message.length"]);
      }elseif( open_ticket($user["client_id"]) >= 2 ){
        $error    = 1;
        $errorText= str_replace("{limit}","2",$languageArray["error.tickets.new.limit"]);
      }else{
        $conn->beginTransaction();
        $insert = $conn->prepare("INSERT INTO tickets SET client_id=:c_id, subject=:subject, time=:time, lastupdate_time=:last_time ");
        $insert = $insert->execute(array("c_id"=>$user["client_id"],"subject"=>$subject,"time"=>date("Y.m.d H:i:s"),"last_time"=>date("Y.m.d H:i:s") ));
          if( $insert ){ $ticket_id = $conn->lastInsertId(); }
        $insert2= $conn->prepare("INSERT INTO ticket_reply SET ticket_id=:t_id, message=:message, time=:time ");
        $insert2= $insert2->execute(array("t_id"=>$ticket_id,"message"=>$message,"time"=>date("Y.m.d H:i:s")));
        $insert3= $conn->prepare("INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ");
        $insert3= $insert3->execute(array("c_id"=>$user["client_id"],"action"=>"Yeni destek talebi oluşturuldu #".$ticket_id,"ip"=>GetIP(),"date"=>date("Y-m-d H:i:s") ));
        if( $insert && $insert2 && $insert3 ):
          unset($_SESSION["data"]);
          header('Location:'.site_url('tickets/').$ticket_id);
          $conn->commit();
          if( $settings["alert_newticket"] == 2 ):
            if( $settings["alert_type"] == 3 ):   $sendmail = 1; $sendsms  = 1; elseif( $settings["alert_type"] == 2 ): $sendmail = 1; $sendsms=0; elseif( $settings["alert_type"] == 1 ): $sendmail=0; $sendsms  = 1; endif;
            if( $sendsms ):
              SMSUser($settings["admin_telephone"],"Websitenizde #".$ticket_id." idli yeni bir destek talebi mevcut.");
            endif;
            if( $sendmail ):
              sendMail(["subject"=>"Yeni destek talebi mevcut.","body"=>"Websitenizde #".$ticket_id." idli yeni bir destek talebi mevcut.","mail"=>$settings["admin_mail"]]);
            endif;
          endif;
        else:
          $error    = 1;
          $errorText= $languageArray["error.tickets.new.fail"];
          $conn->rollBack();
        endif;
      }
  }

}elseif( route(1) && preg_replace('/[^0-9]/', '', route(1)) && !preg_replace('/[^a-zA-Z]/', '', route(1))  ){
  $templateDir  = "open_ticket";
  $ticketUpdate = $conn->prepare("UPDATE tickets SET support_new=:new  WHERE client_id=:c_id && ticket_id=:t_id ");
  $ticketUpdate-> execute(array("c_id"=>$user["client_id"], "new"=>1, "t_id"=>route(1) ));
  $ticketUpdate = $ticketUpdate->fetch(PDO::FETCH_ASSOC);
  $messageList  = $conn->prepare("SELECT * FROM ticket_reply WHERE ticket_id=:t_id ");
  $messageList  -> execute(array("t_id"=>route(1)));
  $messageList  = $messageList->fetchAll(PDO::FETCH_ASSOC);
  $ticketList = $conn->prepare("SELECT * FROM tickets WHERE client_id=:c_id && ticket_id=:t_id ");
  $ticketList-> execute(array("c_id"=>$user["client_id"], "t_id"=>route(1) ));
  $ticketList = $ticketList->fetch(PDO::FETCH_ASSOC);
  $messageList["ticket"]  = $ticketList;

  if( $_POST ){
    foreach ($_POST as $key => $value) {
      $_SESSION["data"][$key]  = $value;
    }
   $message  = htmlspecialchars($_POST["message"]);
      if( strlen(str_replace(' ','',$message)) < 5 ){
        $error    = 1;
        $errorText= str_replace("{length}","5",$languageArray["error.tickets.read.message.length"]);
      }elseif( $ticketList["canmessage"] == 1 ){
        $error    = 1;
        $errorText= $languageArray["error.tickets.read.message.cant"];
      }else{
        $conn->beginTransaction();
        $update = $conn->prepare("UPDATE tickets SET lastupdate_time=:last_time, status=:status, client_new=:new WHERE ticket_id=:t_id ");
        $update = $update->execute(array("last_time"=>date("Y.m.d H:i:s"),"t_id"=>route(1),"new"=>2,"status"=>"pending" ));
        $insert = $conn->prepare("INSERT INTO ticket_reply SET ticket_id=:t_id, message=:message, time=:time ");
        $insert = $insert->execute(array("t_id"=>route(1),"message"=>$message,"time"=>date("Y.m.d H:i:s")));
        $insert3= $conn->prepare("INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ");
        $insert3= $insert3->execute(array("c_id"=>$user["client_id"],"action"=>"Destek talebine yanıt verildi #".route(1),"ip"=>GetIP(),"date"=>date("Y-m-d H:i:s") ));
        if( $update && $insert && $insert3 ):
          unset($_SESSION["data"]);
          $conn->commit();
          header("Location:".site_url('tickets/').route(1));
        else:
          $error    = 1;
          $errorText= $languageArray["error.tickets.read.fail"];
          $conn->rollBack();
        endif;
      }
  }

}elseif( route(1) && preg_replace('/[^a-zA-Z]/', '', route(1))  ){

  header('Location:'.site_url('404'));

}
