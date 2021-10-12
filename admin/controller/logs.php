<?php

if( route(2) && is_numeric(route(2)) ):
  $page = route(2);
else:
  $page = 1;
endif;

if( $_GET["search_type"] == "username" && $_GET["search"] ):
  $search_where = $_GET["search_type"];
  $search_word  = urldecode($_GET["search"]);
  $clients      = $conn->prepare("SELECT client_id FROM clients WHERE username LIKE '%".$search_word."%' ");
  $clients     -> execute(array());
  $clients      = $clients->fetchAll(PDO::FETCH_ASSOC);
  $id=  "("; foreach ($clients as $client) { $id.=$client["client_id"].","; } if( substr($id,-1) == "," ):  $id = substr($id,0,-1); endif; $id.=")";
  $search       = " client_report.client_id IN ".$id;
  $count        = $conn->prepare("SELECT * FROM client_report INNER JOIN clients ON clients.client_id=client_report.client_id WHERE {$search} ");
  $count        -> execute(array());
  $count        = $count->rowCount();
  $search       = "WHERE {$search} ";
  $search_link  = "?search=".$search_word."&search_type=".$search_where;
elseif( $_GET["search_type"] == "action" && $_GET["search"] ):
  $search_where = $_GET["search_type"];
  
  $search_word  = urldecode($_GET["search"]);
  $count        = $conn->prepare("SELECT * FROM client_report INNER JOIN clients ON clients.client_id=client_report.client_id WHERE client_report.action LIKE '%".$search_word."%' ");
  $count        -> execute(array());
  $count        = $count->rowCount();
  $search       = "WHERE client_report.action LIKE '%".$search_word."%' ";
  $search_link  = "?search=".$search_word."&search_type=".$search_where;
else:
  $count          = $conn->prepare("SELECT * FROM client_report INNER JOIN clients ON clients.client_id=client_report.client_id ");
  $count        ->execute(array());
  $count          = $count->rowCount();
  $search         = "";
endif;

  $to             = 50;
  $pageCount      = ceil($count/$to); if( $page > $pageCount ): $page = 1; endif;
  $where          = ($page*$to)-$to;
  $paginationArr  = ["count"=>$pageCount,"current"=>$page,"next"=>$page+1,"previous"=>$page-1];
  $logs = $conn->prepare("SELECT * FROM client_report INNER JOIN clients ON clients.client_id=client_report.client_id $search ORDER BY client_report.id DESC LIMIT $where,$to ");
  $logs->execute(array());
  $logs = $logs->fetchAll(PDO::FETCH_ASSOC);

  if( route(2) == "delete" ):
    $id     = route(3);
    $delete = $conn->prepare("DELETE FROM client_report WHERE id=:id ");
    $delete->execute(array("id"=>$id));
    header("Location:".site_url("admin/logs"));
  elseif( route(2) == "multi-action" ):
    $logs     = $_POST["log"];
    $action   = $_POST["bulkStatus"];
    foreach ($logs as $id => $value):
      $delete = $conn->prepare("DELETE FROM client_report WHERE id=:id ");
      $delete->execute(array("id"=>$id));
    endforeach;
    header("Location:".site_url("admin/logs"));
  endif;

require admin_view('logs');
