<?php
if ($user["access"]["tickets"] != 1):
    header("Location:" . site_url("admin"));
    exit();
endif;
if ($_SESSION["client"]["data"]):
    $data = $_SESSION["client"]["data"];
    foreach ($data as $key => $value) {
        $$key = $value;
    }
    unset($_SESSION["client"]);
endif;
if (!route(2)):
    $page = 1;
elseif (is_numeric(route(2))):
    $page = route(2);
elseif (!is_numeric(route(2))):
    $action = route(2);
endif;
if (empty($action)):
    if ($_GET["search"] == "unread" && $_GET["search"]):
        $search = " client_new='2' ";
        $count = $conn->prepare("SELECT * FROM tickets INNER JOIN clients ON clients.client_id = tickets.client_id WHERE {$search}");
        $count->execute(array());
        $count = $count->rowCount();
        $search = "WHERE {$search}";
        $search_link = "?search=unread";
    elseif ($_GET["search_type"] == "client" && $_GET["search_type"]):
        $search_where = $_GET["search_type"];
        $search_word = $_GET["search"];
        $clients = $conn->prepare("SELECT client_id FROM clients WHERE username LIKE '%" . $search_word . "%' ");
        $clients->execute(array());
        $clients = $clients->fetchAll(PDO::FETCH_ASSOC);
        $id = "(";
        foreach ($clients as $client) {
            $id.= $client["client_id"] . ",";
        }
        if (substr($id, -1) == ","):
            $id = substr($id, 0, -1);
        endif;
        $id.= ")";
        $search = " tickets.client_id IN " . $id;
        $count = $conn->prepare("SELECT * FROM tickets INNER JOIN clients ON clients.client_id = tickets.client_id WHERE {$search}");
        $count->execute(array());
        $count = $count->rowCount();
        $search = "WHERE {$search}";
        $search_link = "?search=" . $search_word . "&search_type=" . $search_where;
    elseif ($_GET["status"]):
        $search = " status='" . $_GET["status"] . "' ";
        $count = $conn->prepare("SELECT * FROM tickets INNER JOIN clients ON clients.client_id = tickets.client_id WHERE {$search}");
        $count->execute(array());
        $count = $count->rowCount();
        $search = "WHERE {$search}";
        $search_link = "?status=" . $_GET["status"];
    elseif ($_GET["search"]):
        $search_where = $_GET["search_type"];
        $search_word = $_GET["search"];
        $search = $search_where . " LIKE '%" . $search_word . "%'";
        $count = $conn->prepare("SELECT * FROM tickets INNER JOIN clients ON clients.client_id = tickets.client_id WHERE {$search}");
        $count->execute(array());
        $count = $count->rowCount();
        $search = "WHERE {$search}";
        $search_link = "?search=" . $search_word . "&search_type=" . $search_where;
    else:
        $count = $conn->prepare("SELECT * FROM tickets INNER JOIN clients ON clients.client_id = tickets.client_id");
        $count->execute(array());
        $count = $count->rowCount();
    endif;
    $to = 50;
    $pageCount = ceil($count / $to);
    if ($page > $pageCount):
        $page = 1;
    endif;
    $where = ($page * $to) - $to;
    $paginationArr = ["count" => $pageCount, "current" => $page, "next" => $page + 1, "previous" => $page - 1];
    $tickets = $conn->prepare("SELECT * FROM tickets INNER JOIN clients ON clients.client_id = tickets.client_id $search ORDER BY FIELD(status, 'pending', 'answered', 'closed'),lastupdate_time DESC LIMIT $where,$to ");
    $tickets->execute(array());
    $tickets = $tickets->fetchAll(PDO::FETCH_ASSOC);
    require admin_view('tickets');
elseif (route(2) == "read"):
    if (!countRow(["table" => "tickets", "where" => ["ticket_id" => route(3) ]])):
        header("Location:" . site_url("admin/tickets"));
        exit();
    endif;
    if ($_POST):
        
        $message = htmlspecialchars($_POST["message"]);
       
        if (strlen($message) < 3):
            $error = 1;
            $errorText = "Message should be at least 3 chracters.";
        else:
            $conn->beginTransaction();
            $update = $conn->prepare("UPDATE tickets SET canmessage=:canmessage, status=:status, lastupdate_time=:time, support_new=:new WHERE ticket_id=:t_id ");
            $update = $update->execute(array("t_id" => route(3), "time" => date("Y-m-d H:i:s"), "status" => "answered", "canmessage" => 2, "new" => 2));
             $tr_arr =array(
                "t_id" => route(3), 
                "time" => date("Y-m-d H:i:s"),
                "support" => '2',
                "message" => $message,
                "client_id"=>'0'
                );
           // print_r($tr_arr); die;
                   $insert = $conn->prepare("INSERT INTO ticket_reply SET ticket_id=:t_id, time=:time, support=:support, message=:message, client_id=:client_id");
            $insert = $insert->execute($tr_arr);
           // echo $update->debugDumpParams(); die;
            if ($insert && $update):
               //  echo "updated"; die;
                $conn->commit();
                header("Location:" . site_url("admin/tickets/read/" . route(3)));
                $_SESSION["client"]["data"]["success"] = 1;
                $_SESSION["client"]["data"]["successText"] = "Successful";
            else:
                //echo "not updated"; die;
                $conn->rollBack();
                header("Location:" . site_url("admin/tickets/read/" . route(3)));
                $_SESSION["client"]["data"]["error"] = 1;
                $_SESSION["client"]["data"]["errorText"] = "Error";
            endif;
        endif;
    endif;
    $update = $conn->prepare("UPDATE tickets SET client_new=:new WHERE ticket_id=:t_id ");
    $update->execute(array("t_id" => route(3), "new" => 1));
    $ticketMessage = $conn->prepare("SELECT ticket_reply.*,tickets.subject,tickets.client_new,tickets.status,tickets.canmessage,tickets.client_id,clients.username  FROM ticket_reply INNER JOIN tickets ON ticket_reply.ticket_id = tickets.ticket_id INNER JOIN clients ON clients.client_id = tickets.client_id WHERE ticket_reply.ticket_id=:t_id ORDER BY ticket_reply.ticket_id DESC ");
    $ticketMessage->execute(array("t_id" => route(3)));
    $ticketMessage = $ticketMessage->fetchAll(PDO::FETCH_ASSOC);
    require admin_view('tickets_read');
elseif (route(2) == "unread"):
    if (!countRow(["table" => "tickets", "where" => ["ticket_id" => route(3) ]])):
        header("Location:" . site_url("admin/tickets"));
        exit();
    endif;
    $update = $conn->prepare("UPDATE tickets SET client_new=:new WHERE ticket_id=:t_id ");
    $update->execute(array("t_id" => route(3), "new" => 2));
    if ($update):
        header("Location:" . site_url("admin/tickets"));
        $_SESSION["client"]["data"]["success"] = 1;
        $_SESSION["client"]["data"]["successText"] = "Successful";
    else:
        header("Location:" . site_url("admin/tickets"));
        $_SESSION["client"]["data"]["error"] = 1;
        $_SESSION["client"]["data"]["errorText"] = "Error";
    endif;
elseif (route(2) == "lock"):
    if (!countRow(["table" => "tickets", "where" => ["ticket_id" => route(3) ]])):
        header("Location:" . site_url("admin/tickets"));
        exit();
    endif;
    $update = $conn->prepare("UPDATE tickets SET canmessage=:can WHERE ticket_id=:t_id ");
    $update->execute(array("t_id" => route(3), "can" => 1));
    if ($update):
        header("Location:" . site_url("admin/tickets"));
        $_SESSION["client"]["data"]["success"] = 1;
        $_SESSION["client"]["data"]["successText"] = "Successful";
    else:
        header("Location:" . site_url("admin/tickets"));
        $_SESSION["client"]["data"]["error"] = 1;
        $_SESSION["client"]["data"]["errorText"] = "Error";
    endif;
elseif (route(2) == "unlock"):
    if (!countRow(["table" => "tickets", "where" => ["ticket_id" => route(3) ]])):
        header("Location:" . site_url("admin/tickets"));
        exit();
    endif;
    $update = $conn->prepare("UPDATE tickets SET canmessage=:can WHERE ticket_id=:t_id ");
    $update->execute(array("t_id" => route(3), "can" => 2));
    if ($update):
        header("Location:" . site_url("admin/tickets"));
        $_SESSION["client"]["data"]["success"] = 1;
        $_SESSION["client"]["data"]["successText"] = "Successful";
    else:
        header("Location:" . site_url("admin/tickets"));
        $_SESSION["client"]["data"]["error"] = 1;
        $_SESSION["client"]["data"]["errorText"] = "Error";
    endif;
elseif (route(2) == "close"):
    if (!countRow(["table" => "tickets", "where" => ["ticket_id" => route(3) ]])):
        header("Location:" . site_url("admin/tickets"));
        exit();
    endif;
    $update = $conn->prepare("UPDATE tickets SET status=:status WHERE ticket_id=:t_id ");
    $update->execute(array("t_id" => route(3), "status" => "closed"));
    if ($update):
        header("Location:" . site_url("admin/tickets"));
        $_SESSION["client"]["data"]["success"] = 1;
        $_SESSION["client"]["data"]["successText"] = "Successful";
    else:
        header("Location:" . site_url("admin/tickets"));
        $_SESSION["client"]["data"]["error"] = 1;
        $_SESSION["client"]["data"]["errorText"] = "Error";
    endif;
elseif ($action == "multi-action"):
    $tickets = htmlspecialchars($_POST["ticket"]);
    $action = htmlspecialchars($_POST["bulkStatus"]);
    if ($action == "unread"):
        foreach ($tickets as $id => $value):
            $update = $conn->prepare("UPDATE tickets SET client_new=:new WHERE ticket_id=:id ");
            $update->execute(array("new" => 2, "id" => $id));
        endforeach;
    elseif ($action == "lock"):
        foreach ($tickets as $id => $value):
            $update = $conn->prepare("UPDATE tickets SET canmessage=:can WHERE ticket_id=:id ");
            $update->execute(array("can" => 1, "id" => $id));
        endforeach;
    elseif ($action == "unlock"):
        foreach ($tickets as $id => $value):
            $update = $conn->prepare("UPDATE tickets SET canmessage=:can WHERE ticket_id=:id ");
            $update->execute(array("can" => 2, "id" => $id));
        endforeach;
    elseif ($action == "close"):
        foreach ($tickets as $id => $value):
            $update = $conn->prepare("UPDATE tickets SET status=:status, canmessage=:can WHERE ticket_id=:id ");
            $update->execute(array("status" => "closed", "id" => $id, "can" => 2));
        endforeach;
    elseif ($action == "pending"):
        foreach ($tickets as $id => $value):
            $update = $conn->prepare("UPDATE tickets SET status=:status, canmessage=:can WHERE ticket_id=:id ");
            $update->execute(array("status" => "pending", "id" => $id, "can" => 2));
        endforeach;
    elseif ($action == "answered"):
        foreach ($tickets as $id => $value):
            $update = $conn->prepare("UPDATE tickets SET status=:status, canmessage=:can WHERE ticket_id=:id ");
            $update->execute(array("status" => "answered", "id" => $id, "can" => 2));
        endforeach;
    endif;
    header("Location:" . site_url("admin/tickets"));
elseif ($action == "new"):
    if ($_POST):
        foreach ($_POST as $key => $value) {
            $$key = $value;
        }
        $userRow = $conn->prepare("SELECT * FROM clients WHERE username=:username ");
        $userRow->execute(array("username" => $username));
        $userDetail = $userRow->fetch(PDO::FETCH_ASSOC);
        if (!$userRow->rowCount()):
            $error = 1;
            $errorText = "User not found";
            $icon = "error";
        elseif (empty($subject)):
            $error = 1;
            $errorText = "Subject can not be empty";
            $icon = "error";
        elseif (empty($message)):
            $error = 1;
            $errorText = "Message can not be empty";
            $icon = "error";
        else:
            $conn->beginTransaction();
            $insert = $conn->prepare("INSERT INTO tickets SET client_id=:c_id, subject=:subject, support_new=:support_new, client_new=:client_new, time=:time, lastupdate_time=:last_time ");
            $insert = $insert->execute(array("c_id" => $userDetail["client_id"], "subject" => htmlspecialchars($subject), "support_new" => 2, "client_new" => 1, "time" => date("Y.m.d H:i:s"), "last_time" => date("Y.m.d H:i:s")));
            if ($insert) {
                $ticket_id = $conn->lastInsertId();
            }
            $insert2 = $conn->prepare("INSERT INTO ticket_reply SET ticket_id=:t_id, client_id=:c_id, support=:support, message=:message, time=:time ");
            $insert2 = $insert2->execute(array("t_id" => $ticket_id, "c_id" => $user["client_id"], "support" => 2, "message" => htmlspecialchars($message), "time" => date("Y.m.d H:i:s")));
            if ($insert && $insert2):
                $conn->commit();
                $referrer = site_url("admin/tickets");
                $error = 1;
                $errorText = "Successful";
                $icon = "success";
            else:
                $conn->rollBack();
                $error = 1;
                $errorText = "Error";
                $icon = "error";
            endif;
        endif;
        echo json_encode(["t" => "error", "m" => $errorText, "s" => $icon, "r" => $referrer]);
    endif;
endif;
