<?php

$action = $_POST['action'];

if ($action == 'providers_list') {
    $smmapi = new SMMApi();
    $provider = $_POST['provider'];
    $api = $conn->prepare('SELECT * FROM service_api WHERE id=:id');
    $api->execute(['id' => $provider]);
    $api = $api->fetch(PDO::FETCH_ASSOC);

    if ($api['api_type'] == 2) {
        echo '<label class="col-sm-3 col-form-label">Service</label>' . "\r\n" . '<div class="col-sm-9">' . "\r\n" . ' <select class="form-control" name="service">' . "\r\n" . '<option value="follow"';

        if ($_SESSION['data']['service'] == 'follow') {
            echo 'selected';
        }

        echo '>Follower</option>' . "\r\n" . '<option value="like"';

        if ($_SESSION['data']['service'] == 'like') {
            echo 'selected';
        }

        echo '>Like</option>' . "\r\n" . ' </select>' . "\r\n" . '</div>';
    } elseif ($api['api_type'] == 1) {
        $services = $smmapi->action(['key' => $api['api_key'], 'action' => 'services'], $api['api_url']);
        echo '<div class="service-mode__block">' . "\r\n" . ' <div class="form-group">' . "\r\n" . ' <label>Service</label>' . "\r\n" . '  <select class="form-control" name="service">';

        foreach ($services as $service) {
            echo '<option value="' . $service->service . '"';

            if ($_SESSION['data']['service'] == $service->service) {
                echo 'selected';
            }

            echo '>' . $service->name . ' - ' . priceFormat($service->rate) . '</option>';
        }

        echo '</select>' . "\r\n" . ' </div>' . "\r\n" . '</div>';
    }

    unset($_SESSION['data']);
} elseif ($action == 'paymentmethod-sortable') {
    $list = $_POST['methods'];

    foreach ($list as $method) {
        $update = $conn->prepare('UPDATE payment_methods SET method_line=:line WHERE id=:id ');
        $update->execute(['id' => $method['id'], 'line' => $method['line']]);
    }
} elseif ($action == 'service-sortable') {
    $list = $_POST['services'];

    foreach ($list as $service) {
        $id = explode('-', $service['id']);
        $update = $conn->prepare('UPDATE services SET service_line=:line WHERE service_id=:id ');
        $update->execute(['id' => $id[1], 'line' => $service['line']]);
    }
} elseif ($action == 'category-sortable') {
    $list = $_POST['categories'];

    foreach ($list as $category) {
        $update = $conn->prepare('UPDATE categories SET category_line=:line WHERE category_id=:id ');
        $update->execute(['id' => $category['id'], 'line' => $category['line']]);
    }
} elseif ($action == 'secret_user') {
    $id = $_POST['id'];
    $services = $conn->prepare('SELECT * FROM services RIGHT JOIN categories ON categories.category_id=services.category_id WHERE services.service_secret=\'1\' || categories.category_secret=\'1\' ');
    $services->execute(['id' => $id]);
    $services = $services->fetchAll(PDO::FETCH_ASSOC);
    $grouped = array_group_by($services, 'category_id');
    $return = '<form class="form" action="' . site_url('admin/clients/export') . '" method="post" data-xhr="true">' . "\r\n" . '<div class="modal-body">' . "\r\n\r\n" . '<div class="services-import__body">' . "\r\n" . ' <div>' . "\r\n" . ' <div class="services-import__list-wrap services-import__list-active">' . "\r\n" . '   <div class="services-import__scroll-wrap">';

    foreach ($grouped as $category) {
        $row = [
            'table' => 'clients_category',
            'where' => ['client_id' => $id, 'category_id' => $category[0]['category_id']]
        ];
        $return .= '<span>' . "\r\n" . '  <div class="services-import__category">' . "\r\n" . ' <div class="services-import__category-title">' . "\r\n" . ' <label> ';

        if ($category[0]['category_secret'] == 1) {
            $return .= '<small><i class="fa fa-lock"></i></small> <input type="checkbox"';

            if (countRow($row)) {
                $return .= 'checked';
            }

            $return .= ' class="tiny-toggle" data-tt-palette="blue" data-url="' . site_url('admin/clients/secret_category/' . $id) . '" data-id="' . $category[0]['category_id'] . '"> ';
        }

        $return .= $category[0]['category_name'] . ' </label>' . "\r\n" . ' </div>' . "\r\n" . '  </div>' . "\r\n" . '   <div class="services-import__packages">' . "\r\n" . '<ul>';

        for ($i = 0; $i < count($category); $i++) {
            $row = [
                'table' => 'clients_service',
                'where' => ['client_id' => $id, 'service_id' => $category[$i]['service_id']]
            ];
            $return .= '<li id="service-' . $category[$i]['service_id'] . '">' . "\r\n" . '   <label>';

            if ($category[$i]['service_secret'] == 1) {
                $return .= '<small><i class="fa fa-lock"></i></small> ';
            }

            $return .= $category[$i]['service_id'] . ' - ' . $category[$i]['service_name'] . "\r\n" . '<span class="services-import__packages-price-edit" >';

            if ($category[$i]['service_secret'] == 1) {
                $return .= '<input type="checkbox"';

                if (countRow($row)) {
                    $return .= 'checked';
                }

                $return .= ' class="tiny-toggle" data-tt-palette="blue" data-url="' . site_url('admin/clients/secret_service/' . $id) . '" data-id="' . $category[$i]['service_id'] . '">';
            }

            $return .= '</span>' . "\r\n" . '   </label>' . "\r\n" . '  </li>';
        }

        $return .= '</ul>' . "\r\n" . '   </div>' . "\r\n" . ' </span>';
    }

    $return .= '</div>' . "\r\n" . ' </div>' . "\r\n" . ' </div>' . "\r\n" . '  </div>' . "\r\n" . '  <script src="' . site_url('theme/admin/admin/') . 'jquery.tinytoggle.min.js"></script>' . "\r\n" . '  <link rel="stylesheet" type="text/css" href="' . site_url('theme/admin/admin/') . 'tinytoggle.min.css" rel="stylesheet">' . "\r\n" . '  <script>' . "\r\n" . '  $(".tiny-toggle").tinyToggle({' . "\r\n" . 'onCheck: function() {' . "\r\n" . 'var id   = $(this).attr("data-id");' . "\r\n" . 'var action = $(this).attr("data-url")+"?type=on&id="+id;' . "\r\n" . ' $.ajax({' . "\r\n" . ' url: action,' . "\r\n" . ' type: \'GET\',' . "\r\n" . ' dataType: \'json\',' . "\r\n" . ' cache: false,' . "\r\n" . ' contentType: false,' . "\r\n" . ' processData: false' . "\r\n" . ' }).done(function(result){' . "\r\n" . '  if( result == 1 ){' . "\r\n" . '$.toast({' . "\r\n" . ' heading: "Successful",' . "\r\n" . ' text: "Action successful",' . "\r\n" . ' icon: "success",' . "\r\n" . ' loader: true,' . "\r\n" . ' loaderBg: "#9EC600"' . "\r\n" . '});' . "\r\n" . '  }else{' . "\r\n" . '$.toast({' . "\r\n" . ' heading: "Fail",' . "\r\n" . ' text: "Action Fail",' . "\r\n" . ' icon: "error",' . "\r\n" . ' loader: true,' . "\r\n" . ' loaderBg: "#9EC600"' . "\r\n" . '});' . "\r\n" . '  }' . "\r\n" . ' })' . "\r\n" . ' .fail(function(){' . "\r\n" . '  $.toast({' . "\r\n" . 'heading: "Fail",' . "\r\n" . 'text: "Action Fail",' . "\r\n" . 'icon: "error",' . "\r\n" . 'loader: true,' . "\r\n" . 'loaderBg: "#9EC600"' . "\r\n" . '  });' . "\r\n" . ' });' . "\r\n" . '},' . "\r\n" . 'onUncheck: function() {' . "\r\n" . 'var id   = $(this).attr("data-id");' . "\r\n" . 'var action = $(this).attr("data-url")+"?type=off&id="+id;' . "\r\n" . ' $.ajax({' . "\r\n" . ' url: action,' . "\r\n" . ' type: \'GET\',' . "\r\n" . ' dataType: \'json\',' . "\r\n" . ' cache: false,' . "\r\n" . ' contentType: false,' . "\r\n" . ' processData: false' . "\r\n" . ' }).done(function(result){' . "\r\n" . '  if( result == 1 ){' . "\r\n" . '$.toast({' . "\r\n" . ' heading: "Successful",' . "\r\n" . ' text: "Action successful",' . "\r\n" . ' icon: "success",' . "\r\n" . ' loader: true,' . "\r\n" . ' loaderBg: "#9EC600"' . "\r\n" . '});' . "\r\n" . '  }else{' . "\r\n" . '$.toast({' . "\r\n" . ' heading: "Fail",' . "\r\n" . ' text: "Action Fail",' . "\r\n" . ' icon: "error",' . "\r\n" . ' loader: true,' . "\r\n" . ' loaderBg: "#9EC600"' . "\r\n" . '});' . "\r\n" . '  }' . "\r\n" . ' })' . "\r\n" . ' .fail(function(){' . "\r\n" . '  $.toast({' . "\r\n" . 'heading: "Fail",' . "\r\n" . 'text: "Action Fail",' . "\r\n" . 'icon: "error",' . "\r\n" . 'loader: true,' . "\r\n" . 'loaderBg: "#9EC600"' . "\r\n" . '  });' . "\r\n" . ' });' . "\r\n" . '},' . "\r\n" . '  });' . "\r\n\r\n" . '  </script>' . "\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'new_user') {
    $return = '<form class="form" action="' . site_url('admin/clients/new') . '" method="post" data-xhr="true">' . "\r\n" . '<div class="modal-body">' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label>E-mail</label>' . "\r\n" . '  <input type="text" name="email" value="" class="form-control">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label>Username</label>' . "\r\n" . '  <input type="text" name="username" class="form-control" value="">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label>Password</label>' . "\r\n" . '  <div class="input-group">' . "\r\n" . '<input type="text" class="form-control" name="password" value="" id="user_password">' . "\r\n" . '<span class="input-group-btn">' . "\r\n" . '<button class="btn btn-default" onclick="UserPassword()" type="button">' . "\r\n" . '<span class="fa fa-random" data-toggle="tooltip" data-placement="bottom" title="" aria-hidden="true" data-original-title="Create password"></span></button>' . "\r\n" . '</span>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label>Phone</label>' . "\r\n" . '  <input type="text" name="telephone" class="form-control" value="">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Debt status</label>' . "\r\n" . '<select class="form-control" id="debit" name="balance_type">' . "\r\n" . '  <option value="2">Can not make a debt</option>' . "\r\n" . '  <option value="1">Can make a debt</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group" id="debit_limit">' . "\r\n" . '  <label>How much can borrow?</label>' . "\r\n" . '  <input type="text" name="debit_limit" class="form-control" value="">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>SMS Verification</label>' . "\r\n" . '<select class="form-control" name="tel_type">' . "\r\n" . '  <option value="1">Unverified</option>' . "\r\n" . '  <option value="2">Verified</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>E-mail Verification</label>' . "\r\n" . '<select class="form-control" name="email_type">' . "\r\n" . '  <option value="1">Unverified</option>' . "\r\n" . '  <option value="2">Verified</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Is this an admin?</label>' . "\r\n" . '<select class="form-control" name="access[admin_access]">' . "\r\n" . '  <option value="0">No</option>' . "\r\n" . '  <option value="1">Yes</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Admin permissions</label>' . "\r\n" . '<div class="form-group col-md-12">' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[users]" checked value="1"> Clients' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[orders]" checked value="1"> Orders' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[subscriptions]" checked value="1"> Subscriptions' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[dripfeed]" checked value="1"> Drip-feed' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[services]" checked value="1"> Services' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[payments]" checked value="1"> Payments' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[tickets]" checked value="1"> Tickets' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[reports]" checked value="1"> Reports' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[general_settings]" checked value="1"> General Settings' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[pages]" checked value="1"> Pages' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[payments_settings]" checked value="1"> Payment Settings' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[bank_accounts]" checked value="1"> Bank Accounts' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[payments_bonus]" checked value="1"> Payment Bonus' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[alert_settings]" checked value="1"> Alert Settings' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[providers]" checked value="1"> Service Providers' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[themes]" checked value="1"> Themes' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[admins]" checked value="1"> Admins' . "\r\n" . ' </label>'. '  <input type="checkbox" class="access" name="access[language]" checked value="1"> Languages'. '  <input type="checkbox" class="access" name="access[meta]" checked value="1"> Meta' . "\r\n" . ' </label>' . "\r\n" . ' </label>' . "\r\n" . '</div>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Create</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>' . "\r\n" . ' <script>' . "\r\n" . '  var type = $("#debit").val();' . "\r\n" . '  if( type == 2 ){' . "\r\n" . '$("#debit_limit").hide();' . "\r\n" . '  } else{' . "\r\n" . '$("#debit_limit").show();' . "\r\n" . '  }' . "\r\n" . '  $("#debit").change(function(){' . "\r\n" . 'var type = $(this).val();' . "\r\n" . 'if( type == 2 ){' . "\r\n" . ' $("#debit_limit").hide();' . "\r\n" . '} else{' . "\r\n" . ' $("#debit_limit").show();' . "\r\n" . '}' . "\r\n" . '  });' . "\r\n" . ' </script>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'edit_user') {
    $id = $_POST['id'];
    $user = $conn->prepare('SELECT * FROM clients WHERE client_id=:id ');
    $user->execute(['id' => $id]);
    $user = $user->fetch(PDO::FETCH_ASSOC);
    $access = json_decode($user['access'], true);
    $return = '<form class="form" action="' . site_url('admin/clients/edit/' . $user['username']) . '" method="post" data-xhr="true">' . "\r\n" . '<div class="modal-body">' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="' . $user['name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label>E-mail</label>' . "\r\n" . '  <input type="text" name="email" value="' . $user['email'] . '" class="form-control">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label>Username</label>' . "\r\n" . '  <input type="text" name="username" class="form-control" readonly value="' . $user['username'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label>Phone</label>' . "\r\n" . '  <input type="text" name="telephone" class="form-control" value="' . $user['telephone'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Debt status</label>' . "\r\n" . '<select class="form-control" id="debit" name="balance_type">' . "\r\n" . '  <option value="2"';

    if ($user['balance_type'] == 2) {
        $return .= 'selected';
    }

    $return .= '>Can not make a debt</option>' . "\r\n" . '  <option value="1"';

    if ($user['balance_type'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Can make a debt</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group" id="debit_limit">' . "\r\n" . '  <label>How much can borrow?</label>' . "\r\n" . '  <input type="text" name="debit_limit" class="form-control" value="' . $user['debit_limit'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>SMS Verification</label>' . "\r\n" . '<select class="form-control" name="tel_type">' . "\r\n" . '  <option value="1"';

    if ($user['tel_type'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Unverified</option>' . "\r\n" . '  <option value="2"';

    if ($user['tel_type'] == 2) {
        $return .= 'selected';
    }

    $return .= '>Verified</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>E-mail Verification</label>' . "\r\n" . '<select class="form-control" name="email_type">' . "\r\n" . '  <option value="1"';

    if ($user['email_type'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Unverified</option>' . "\r\n" . '  <option value="2"';

    if ($user['email_type'] == 2) {
        $return .= 'selected';
    }

    $return .= '>Verified</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Is this an admin?</label>' . "\r\n" . '<select class="form-control" name="access[admin_access]">' . "\r\n" . '  <option value="0"';

    if ($access['admin_access'] == 0) {
        $return .= 'selected';
    }

    $return .= '>No</option>' . "\r\n" . '  <option value="1"';

    if ($access['admin_access'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Yes</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group row">' . "\r\n" . '  <label>Admin permissions</label>' . "\r\n" . '<div class="form-group col-md-12">' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[users]"';

    if ($access['users'] == 1) {
        $return .= 'checked';
    }

    $return .= ' value="1"> Clients' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[orders]"';

    if ($access['orders'] == 1) {
        $return .= 'checked';
    }

    $return .= ' value="1"> Orders' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[subscriptions]"';

    if ($access['subscriptions'] == 1) {
        $return .= 'checked';
    }

    $return .= ' value="1"> Subscriptions' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[dripfeed]"';

    if ($access['dripfeed'] == 1) {
        $return .= 'checked';
    }

    $return .= ' value="1"> Drip-feed' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[services]"';

    if ($access['services'] == 1) {
        $return .= 'checked';
    }

    $return .= ' value="1"> Services' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[payments]"';

    if ($access['payments'] == 1) {
        $return .= 'checked';
    }

    $return .= ' value="1"> Payments' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[tickets]"';

    if ($access['tickets'] == 1) {
        $return .= 'checked';
    }

    $return .= ' value="1"> Tickets' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[reports]"';

    if ($access['reports'] == 1) {
        $return .= 'checked';
    }

    $return .= ' value="1"> Reports' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[general_settings]"';

    if ($access['general_settings'] == 1) {
        $return .= 'checked';
    }

    $return .= ' value="1"> General Settings' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[pages]"';

    if ($access['pages'] == 1) {
        $return .= 'checked';
    }

    $return .= ' value="1"> Pages' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[payments_settings]"';

    if ($access['payments_settings'] == 1) {
        $return .= 'checked';
    }

    $return .= ' value="1"> Payment Settings' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[bank_accounts]"';

    if ($access['bank_accounts'] == 1) {
        $return .= 'checked';
    }

    $return .= ' value="1"> Bank Accounts' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[payments_bonus]"';

    if ($access['payments_bonus'] == 1) {
        $return .= 'checked';
    }

    $return .= ' value="1"> Payment Bonus' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[alert_settings]"';

    if ($access['alert_settings'] == 1) {
        $return .= 'checked';
    }

    $return .= ' value="1"> Alert Settings' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[providers]"';

    if ($access['providers'] == 1) {
        $return .= 'checked';
    }

    $return .= ' value="1"> Service Providers' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">' . "\r\n" . '  <input type="checkbox" class="access" name="access[themes]"';

    if ($access['themes'] == 1) {
        $return .= 'checked ';
    }

    $return .= ' value="1"> Themes' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline col-md-3">'  . '  <input type="checkbox" class="access" name="access[admins]"';

    if ($access['admins'] == 1) {
        $return .= 'checked ';
    }

    $return .= ' value="1"> Admins'  . ' </label>' . "\r\n".'<label class="checkbox-inline col-md-3">  <input type="checkbox" class="access" name="access[language]"';
    
    if($access['language']==1)
    {
        $return .= ' checked ';
    }
      //checked 
      
      $return .='value="1"> Languages </label>'."\r\n". ' <label class="checkbox-inline col-md-3"> <input type="checkbox" class="access" name="access[meta]"';
      
    if($access['meta']==1)
    {
        $return .= ' checked ';
    }
      
     $return .=  'value="1">  Meta  </label>'."\r\n". ' <label class="checkbox-inline col-md-3">  <input type="checkbox" class="access" name="access[proxy]"';
    
    if($access['proxy']==1)
    {
        $return .= ' checked ';
    }
      
     $return .=  'value="1">  Proxy </label> <label  class="checkbox-inline col-md-3"> <input type="checkbox" class="access" name="access[kuponlar]"';
     
    if($access['kuponlar']==1)
    {
        $return .= ' checked ';
    }
    
    $return .=  ' value="1">  Kuponlar </label>';
    
   
    $return.='</div>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>' . "\r\n" . ' <script>' . "\r\n" . '  var type = $("#debit").val();' . "\r\n" . '  if( type == 2 ){' . "\r\n" . '$("#debit_limit").hide();' . "\r\n" . '  } else{' . "\r\n" . '$("#debit_limit").show();' . "\r\n" . '  }' . "\r\n" . '  $("#debit").change(function(){' . "\r\n" . 'var type = $(this).val();' . "\r\n" . 'if( type == 2 ){' . "\r\n" . ' $("#debit_limit").hide();' . "\r\n" . '} else{' . "\r\n" . ' $("#debit_limit").show();' . "\r\n" . '}' . "\r\n" . '  });' . "\r\n" . ' </script>' . "\r\n" . ' ';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'pass_user') {
    $id = $_POST['id'];
    $user = $conn->prepare('SELECT * FROM clients WHERE client_id=:id ');
    $user->execute(['id' => $id]);
    $user = $user->fetch(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="' . site_url('admin/clients/pass/' . $user['username']) . '" method="post" data-xhr="true">' . "\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label>Password</label>' . "\r\n" . '  <div class="input-group">' . "\r\n" . '<input type="text" class="form-control" name="password" value="" id="user_password">' . "\r\n" . '<span class="input-group-btn">' . "\r\n" . '<button class="btn btn-primary" onclick="UserPassword()" type="button">' . "\r\n" . '<span class="fa fa-random" data-toggle="tooltip" data-placement="bottom" title="" aria-hidden="true" data-original-title="Create password"></span></button>' . "\r\n" . '</span>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'alert_user') {
    $return = '<form class="form" action="' . site_url('admin/clients/alert') . '" method="post" data-xhr="true">' . "\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Client to be notified</label>' . "\r\n" . '<select class="form-control" id="user_type" name="user_type">' . "\r\n" . '  <option value="all">All Clients</option>' . "\r\n" . '  <option value="secret">Specified Client</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group" id="username">' . "\r\n" . '  <label>Username</label>' . "\r\n" . '  <input type="text" name="username" class="form-control" value="">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Alert Type</label>' . "\r\n" . '<select class="form-control" id="alert_type" name="alert_type">' . "\r\n" . '  <option value="email">E-mail</option>' . "\r\n" . '  <option value="sms">SMS</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div id="email">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '<label>E-mail Title</label>' . "\r\n" . '<input type="text" name="subject" class="form-control" value="">' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group" id="username">' . "\r\n" . '  <label>Message</label>' . "\r\n" . '  <textarea type="text" name="message" class="form-control" rows="5"></textarea>' . "\r\n" . ' </div>' . "\r\n\r\n\r\n\r\n" . '</div>' . "\r\n" . '<script type="text/javascript">' . "\r\n" . ' $("#username").hide();' . "\r\n" . ' $("#user_type").change(function(){' . "\r\n" . '  var type = $(this).val();' . "\r\n" . '  if( type == "secret" ){' . "\r\n" . '$("#username").show();' . "\r\n" . '  } else{' . "\r\n" . '$("#username").hide();' . "\r\n" . '  }' . "\r\n" . ' });' . "\r\n" . ' $("#alert_type").change(function(){' . "\r\n" . '  var type = $(this).val();' . "\r\n" . '  if( type == "email" ){' . "\r\n" . '$("#email").show();' . "\r\n" . '  } else{' . "\r\n" . '$("#email").hide();' . "\r\n" . '  }' . "\r\n" . ' });' . "\r\n" . '</script>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Send Alerts</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>' . "\r\n\r\n" . ' ';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'new_service') {
    $categories = $conn->prepare('SELECT * FROM categories ORDER BY category_line ');
    $categories->execute([]);
    $categories = $categories->fetchAll(PDO::FETCH_ASSOC);
    $providers = $conn->prepare('SELECT * FROM service_api');
    $providers->execute([]);
    $providers = $providers->fetchAll(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="' . site_url('admin/services/new-service') . '" method="post" data-xhr="true">' . "\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Service name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Service category</label>' . "\r\n" . '<select class="form-control" name="category">' . "\r\n" . '  <option value="0">Please select a category..</option>';

    foreach ($categories as $category) {
        $return .= '<option value="' . $category['category_id'] . '">' . $category['category_name'] . '</option>';
    }

    $return .= '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__wrapper">' . "\r\n" . '  <div class="service-mode__block">' . "\r\n" . '<div class="form-group">' . "\r\n" . '<label>Service type</label>' . "\r\n" . '<select class="form-control" name="package">' . "\r\n" . '<option value="1">Service</option>' . "\r\n" . '<option value="2">Package</option>' . "\r\n" . '<option value="3">Special Comment</option>' . "\r\n" . '<option value="4">Package Comment</option>' . "\r\n" . ' </select>' . "\r\n" . '</div>' . "\r\n" . '  </div>' . "\r\n" . '  <div class="service-mode__block">' . "\r\n" . '<div class="form-group">' . "\r\n" . '<label>Mode</label>' . "\r\n" . '<select class="form-control" name="mode" id="serviceMode">' . "\r\n" . '<option value="1">Manual</option>' . "\r\n" . '<option value="2">Automatic (API)</option>' . "\r\n" . ' </select>' . "\r\n" . '</div>' . "\r\n" . '  </div>' . "\r\n\r\n" . '  <div id="autoMode" style="display:none">' . "\r\n" . '<div class="service-mode__block">' . "\r\n" . '<div class="form-group">' . "\r\n" . '<label>Service Provider</label>' . "\r\n" . ' <select class="form-control" name="provider" id="provider">' . "\r\n" . '<option value="0">Please select a service provider..</option>';

    foreach ($providers as $provider) {
        $return .= '<option value="' . $provider['id'] . '">' . $provider['api_name'] . '</option>';
    }

    $return .= '</select>' . "\r\n" . '</div>' . "\r\n" . '</div>' . "\r\n" . '<div id="provider_service">' . "\r\n" . '</div>' . "\r\n" . '<div class="service-mode__block" style="display:none">' . "\r\n" . '<div class="form-group">' . "\r\n" . '<label>Pricing over the purchase price</label>' . "\r\n" . ' <select class="form-control" name="saleprice_cal" id="saleprice_cal>' . "\r\n" . '  <option value="normal">No</option>' . "\r\n" . '  <option value="percent">Add % to the purchase price </option>' . "\r\n" . '  <option value="amount">Add amount to the purchase price </option>' . "\r\n" . ' </select>' . "\r\n" . '</div>' . "\r\n" . '</div>' . "\r\n" . '<div class="form-group" style="display:none">' . "\r\n" . '<label class="form-group__service-name">Price</label>' . "\r\n" . '<input type="text" class="form-control" name="saleprice" value="">' . "\r\n" . '</div>' . "\r\n" . '<div class="service-mode__block">' . "\r\n" . '<div class="form-group">' . "\r\n" . '<label>Dripfeed</label>' . "\r\n" . ' <select class="form-control" name="dripfeed">' . "\r\n" . '  <option value="1">Inactive</option>' . "\r\n" . '  <option value="2">Active</option>' . "\r\n" . ' </select>' . "\r\n" . '</div>' . "\r\n" . '</div>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__wrapper">' . "\r\n" . '<div class="row">' . "\r\n" . '<div class="col-md-6 service-mode__block ">' . "\r\n" . ' <div class="form-group">' . "\r\n" . ' <label>Check Instagram profile privacy?</label>' . "\r\n" . '  <select class="form-control" name="instagram_private">' . "\r\n" . ' <option value="1">No</option>' . "\r\n" . ' <option value="2">Yes</option>' . "\r\n" . '</select>' . "\r\n" . ' </div>' . "\r\n" . '</div>' . "\r\n" . '<div class="col-md-6 service-mode__block ">' . "\r\n" . ' <div class="form-group">' . "\r\n" . ' <label>Start count</label>' . "\r\n" . '  <select class="form-control" name="start_count">' . "\r\n" . ' <option value="none">No</option>' . "\r\n" . ' <option value="instagram_follower">Instagram follower count</option>' . "\r\n" . ' <option value="instagram_photo">Instagram photo like count</option>' . "\r\n" . '</select>' . "\r\n" . ' </div>' . "\r\n" . '</div>' . "\r\n" . '</div>' . "\r\n" . '<div class="row">' . "\r\n" . '<div class="col-md-6 service-mode__block ">' . "\r\n" . ' <div class="form-group">' . "\r\n" . ' <label>Enter multiple order to the same link?</label>' . "\r\n" . '  <select class="form-control" name="instagram_second">' . "\r\n" . ' <option value="2">Yes</option>' . "\r\n" . ' <option value="1">No</option>' . "\r\n" . '</select>' . "\r\n" . ' </div>' . "\r\n" . '</div>' . "\r\n" . '</div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Service price (Rate per 1000)</label>' . "\r\n" . '  <input type="text" class="form-control" name="price" value="">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Service Description</label>' . "\r\n" . '  <textarea class="form-control" name="description" rows="4"></textarea>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="row">' . "\r\n" . '  <div class="col-md-6 form-group">' . "\r\n" . '<label class="form-group__service-name">Minimum order</label>' . "\r\n" . '<input type="text" class="form-control" name="min" value="">' . "\r\n" . '  </div>' . "\r\n\r\n" . '  <div class="col-md-6 form-group">' . "\r\n" . '<label class="form-group__service-name">Maximum order</label>' . "\r\n" . '<input type="text" class="form-control" name="max" value="">' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <hr>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>How should the order be? With:</label>' . "\r\n" . '<select class="form-control" name="want_username">' . "\r\n" . ' <option value="1">Link</option>' . "\r\n" . ' <option value="2">Username</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Hidden Service</label>' . "\r\n" . '<select class="form-control" name="secret">' . "\r\n" . ' <option value="2">No</option>' . "\r\n" . ' <option value="1">Yes</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Service Speed</label>' . "\r\n" . '<select class="form-control" name="speed">' . "\r\n" . ' <option value="1">Slow</option>' . "\r\n" . ' <option value="2">Sometimes Slow</option>' . "\r\n" . ' <option value="3">Normal</option>' . "\r\n" . ' <option value="4">Fast</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Add new service</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>' . "\r\n" . ' <script src="';
    $return .= site_url('theme/admin/admin/');
    $return .= 'script.js"></script>' . "\r\n" . ' ';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'edit_service') {
    $id = $_POST['id'];
    $smmapi = new SMMApi();
    $categories = $conn->prepare('SELECT * FROM categories ORDER BY category_line ');
    $categories->execute([]);
    $categories = $categories->fetchAll(PDO::FETCH_ASSOC);
    $serviceInfo = $conn->prepare('SELECT * FROM services LEFT JOIN service_api ON service_api.id=services.service_api WHERE services.service_id=:id ');
    $serviceInfo->execute(['id' => $id]);
    $serviceInfo = $serviceInfo->fetch(PDO::FETCH_ASSOC);
    $providers = $conn->prepare('SELECT * FROM service_api');
    $providers->execute([]);
    $providers = $providers->fetchAll(PDO::FETCH_ASSOC);

    if (in_array($serviceInfo['service_package'], ['11', '12', '13', '14', '15'])) {
        $return = '<form class="form" action="' . site_url('admin/services/edit-subscription/' . $serviceInfo['service_id']) . '" method="post" data-xhr="true">' . "\r\n" . '  <div class="modal-body">' . "\r\n\r\n" . '<div class="form-group">' . "\r\n" . '<label class="form-group__service-name">Service name</label>' . "\r\n" . '<input type="text" class="form-control" name="name" value="' . $serviceInfo['service_name'] . '">' . "\r\n" . '</div>' . "\r\n\r\n" . '<div class="service-mode__block">' . "\r\n" . '<div class="form-group">' . "\r\n" . '<label>Service Category</label>' . "\r\n" . ' <select class="form-control" name="category">' . "\r\n" . '<option value="0">Please select a category..</option>';

        foreach ($categories as $category) {
            $return .= '<option value="' . $category['category_id'] . '"';

            if ($serviceInfo['category_id'] == $category['category_id']) {
                $return .= 'selected';
            }

            $return .= '>' . $category['category_name'] . '</option>';
        }

        $return .= '</select>' . "\r\n" . '</div>' . "\r\n" . '</div>' . "\r\n\r\n" . '<div class="service-mode__block">' . "\r\n" . '<div class="form-group">' . "\r\n" . '<label>Subscription type</label>' . "\r\n" . ' <select class="form-control" disabled id="subscription_package">' . "\r\n" . '<option value="11"';

        if ($serviceInfo['service_package'] == 11) {
            $return .= 'selected';
        }

        $return .= '>Instagram Automatic Like - Unlimited</option>' . "\r\n" . '<option value="12"';

        if ($serviceInfo['service_package'] == 12) {
            $return .= 'selected';
        }

        $return .= '>Instagram Automatic View - Unlimited</option>' . "\r\n" . '<option value="14"';

        if ($serviceInfo['service_package'] == 14) {
            $return .= 'selected';
        }

        $return .= '>Instagram Automatic Like - Timed</option>' . "\r\n" . '<option value="15"';

        if ($serviceInfo['service_package'] == 15) {
            $return .= 'selected';
        }

        $return .= '>Instagram Automatic View - Timed</option>' . "\r\n" . '  </select>' . "\r\n" . '</div>' . "\r\n" . '</div>' . "\r\n\r\n" . '<div class="service-mode__wrapper">' . "\r\n\r\n" . '<div class="service-mode__block">' . "\r\n" . ' <div class="form-group">' . "\r\n" . ' <label>Mode</label>' . "\r\n" . '  <select class="form-control" name="mode" id="serviceMode">' . "\r\n" . ' <option value="2"';

        if ($serviceInfo['service_api'] != 0) {
            $return .= 'selected';
        }

        $return .= '>Automatic (API)</option>' . "\r\n" . '</select>' . "\r\n" . ' </div>' . "\r\n" . '</div>' . "\r\n\r\n\r\n" . '<div id="autoMode" style="display: none">' . "\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Service Provider</label>' . "\r\n" . '<select class="form-control" name="provider" id="provider">' . "\r\n" . '  <option value="0">Please select a service provider..</option>';

        foreach ($providers as $provider) {
            $return .= '<option value="' . $provider['id'] . '"';

            if ($serviceInfo['service_api'] == $provider['id']) {
                $return .= 'selected';
            }

            $return .= '>' . $provider['api_name'] . '</option>';
        }

        $return .= '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n" . ' <div id="provider_service">';
        $services = $smmapi->action(['key' => $serviceInfo['api_key'], 'action' => 'services'], $serviceInfo['api_url']);
        $return .= '<div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Service</label>' . "\r\n" . '<select class="form-control" name="service">';

        foreach ($services as $service) {
            $return .= '<option value="' . $service->service . '"';

            if ($serviceInfo['api_service'] == $service->service) {
                $return .= 'selected';
            }

            $return .= '>' . $service->name . ' - ' . $service->rate . '</option>';
        }

        $return .= '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>';
        $return .= '</div>' . "\r\n" . '</div>' . "\r\n" . '</div>' . "\r\n\r\n" . '<div id="unlimited">' . "\r\n" . '<div class="form-group">' . "\r\n" . ' <label class="form-group__service-name">Service price (Rate per 1000)</label>' . "\r\n" . ' <input type="text" class="form-control" name="price" value="' . $serviceInfo['service_price'] . '">' . "\r\n" . '</div>' . "\r\n\r\n" . '<div class="row">' . "\r\n" . ' <div class="col-md-6 form-group">' . "\r\n" . '  <label class="form-group__service-name">Minimum order</label>' . "\r\n" . '  <input type="text" class="form-control" name="min" value="' . $serviceInfo['service_min'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="col-md-6 form-group">' . "\r\n" . '  <label class="form-group__service-name">Maximum order</label>' . "\r\n" . '  <input type="text" class="form-control" name="max" value="' . $serviceInfo['service_max'] . '">' . "\r\n" . ' </div>' . "\r\n" . '</div>' . "\r\n" . '</div>' . "\r\n\r\n" . '<div id="limited">' . "\r\n" . '<div class="form-group">' . "\r\n" . ' <label class="form-group__service-name">Service price</label>' . "\r\n" . ' <input type="text" class="form-control" name="limited_price" value="' . $serviceInfo['service_price'] . '">' . "\r\n" . '</div>' . "\r\n\r\n\r\n\r\n" . '<div class="row">' . "\r\n" . ' <div class="col-md-6 form-group">' . "\r\n" . '  <label class="form-group__service-name">Post limit</label>' . "\r\n" . '  <input type="text" class="form-control" name="autopost" value="' . $serviceInfo['service_autopost'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="col-md-6 form-group">' . "\r\n" . '  <label class="form-group__service-name">Order Quantity</label>' . "\r\n" . '  <input type="text" class="form-control" name="limited_min" value="' . $serviceInfo['service_min'] . '">' . "\r\n" . ' </div>' . "\r\n" . '</div>' . "\r\n" . '<div class="form-group">' . "\r\n" . ' <label class="form-group__service-name">Package Time <small>(day)</small></label>' . "\r\n" . ' <input type="text" class="form-control" name="autotime" value="' . $serviceInfo['service_autotime'] . '">' . "\r\n" . '</div>' . "\r\n" . '</div>' . "\r\n\r\n" . '<hr>' . "\r\n\r\n" . '<div class="form-group">' . "\r\n" . '<label class="form-group__service-name">Service Description</label>' . "\r\n" . '<textarea class="form-control" name="description" rows="4">' . $serviceInfo['service_description'] . '</textarea>' . "\r\n" . '</div>' . "\r\n\r\n\r\n" . '<div class="service-mode__block">' . "\r\n" . '<div class="form-group">' . "\r\n" . '<label>Hidden Service</label>' . "\r\n" . ' <select class="form-control" name="secret">' . "\r\n" . '<option value="2"';

        if ($serviceInfo['service_secret'] == 2) {
            $return .= 'selected';
        }

        $return .= '>No</option>' . "\r\n" . '<option value="1"';

        if ($serviceInfo['service_secret'] == 1) {
            $return .= 'selected';
        }

        $return .= '>Yes</option>' . "\r\n" . ' </select>' . "\r\n" . '</div>' . "\r\n" . '</div>' . "\r\n\r\n" . '<div class="service-mode__block">' . "\r\n" . '<div class="form-group">' . "\r\n" . '<label>Service Speed</label>' . "\r\n" . ' <select class="form-control" name="speed">' . "\r\n" . '<option value="1"';

        if ($serviceInfo['service_speed'] == 1) {
            $return .= 'selected';
        }

        $return .= '>Slow</option>' . "\r\n" . '<option value="2"';

        if ($serviceInfo['service_speed'] == 2) {
            $return .= 'selected';
        }

        $return .= '>Sometimes Slow</option>' . "\r\n" . '<option value="3"';

        if ($serviceInfo['service_speed'] == 3) {
            $return .= 'selected';
        }

        $return .= '>Normal</option>' . "\r\n" . '<option value="4"';

        if ($serviceInfo['service_speed'] == 4) {
            $return .= 'selected';
        }

        $return .= '>Fast</option>' . "\r\n" . ' </select>' . "\r\n" . '</div>' . "\r\n" . '</div>' . "\r\n\r\n" . '  </div>' . "\r\n\r\n" . '<div class="modal-footer">' . "\r\n" . '<button type="submit" class="btn btn-primary">Update Subscription</button>' . "\r\n" . '<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . '</div>' . "\r\n" . '</form>' . "\r\n" . '<script type="text/javascript">' . "\r\n" . 'var site_url = $("head base").attr("href");' . "\r\n" . '$("#provider").change(function(){' . "\r\n" . ' var provider = $(this).val();' . "\r\n" . ' getProviderServices(provider,site_url);' . "\r\n" . '});' . "\r\n\r\n" . 'getProvider();' . "\r\n" . '$("#serviceMode").change(function(){' . "\r\n" . ' getProvider();' . "\r\n" . '});' . "\r\n\r\n" . 'getSalePrice();' . "\r\n" . '$("#saleprice_cal").change(function(){' . "\r\n" . ' getSalePrice();' . "\r\n" . '});' . "\r\n\r\n" . 'getSubscription();' . "\r\n" . '$("#subscription_package").change(function(){' . "\r\n" . ' getSubscription();' . "\r\n" . '});' . "\r\n" . 'function getProviderServices(provider,site_url){' . "\r\n" . ' if( provider == 0 ){' . "\r\n" . '  $("#provider_service").hide();' . "\r\n" . ' }else{' . "\r\n" . '  $.post(site_url+"admin/ajax_data",{action:"providers_list",provider:provider}).done(function( data ) {' . "\r\n" . '$("#provider_service").show();' . "\r\n" . '$("#provider_service").html(data);' . "\r\n" . '  }).fail(function(){' . "\r\n" . 'alert("Somethings went wrong!");' . "\r\n" . '  });' . "\r\n" . ' }' . "\r\n" . '}' . "\r\n\r\n" . 'function getProvider(){' . "\r\n" . ' var mode = $("#serviceMode").val();' . "\r\n" . '  if( mode == 1 ){' . "\r\n" . '$("#autoMode").hide();' . "\r\n" . '  }else{' . "\r\n" . '$("#autoMode").show();' . "\r\n" . '  }' . "\r\n" . '}' . "\r\n\r\n" . 'function getSalePrice(){' . "\r\n" . ' var type = $("#saleprice_cal").val();' . "\r\n" . '  if( type == "normal" ){' . "\r\n" . '$("#saleprice").hide();' . "\r\n" . '$("#servicePrice").show();' . "\r\n" . '  }else{' . "\r\n" . '$("#saleprice").show();' . "\r\n" . '$("#servicePrice").hide();' . "\r\n" . '  }' . "\r\n" . '}' . "\r\n\r\n" . 'function getSubscription(){' . "\r\n" . ' var type = $("#subscription_package").val();' . "\r\n" . '  if( type == "11" || type == "12" ){' . "\r\n" . '$("#unlimited").show();' . "\r\n" . '$("#limited").hide();' . "\r\n" . '  }else{' . "\r\n" . '$("#unlimited").hide();' . "\r\n" . '$("#limited").show();' . "\r\n" . '  }' . "\r\n" . '}' . "\r\n" . '</script>' . "\r\n" . '';
        echo json_encode(['content' => $return, 'title' => '']);
    } else {
        $return = '<form class="form" action="' . site_url('admin/services/edit-service/' . $serviceInfo['service_id']) . '" method="post" data-xhr="true">' . "\r\n" . '  <div class="modal-body">' . "\r\n\r\n" . '<div class="form-group">' . "\r\n" . '<label class="form-group__service-name">Service name</label>' . "\r\n" . '<input type="text" class="form-control" name="name" value="' . $serviceInfo['service_name'] . '">' . "\r\n" . '</div>' . "\r\n\r\n" . '<div class="service-mode__block">' . "\r\n" . '<div class="form-group">' . "\r\n" . '<label>Service Category</label>' . "\r\n" . ' <select class="form-control" name="category">' . "\r\n" . '<option value="0">Please select a category..</option>';

        foreach ($categories as $category) {
            $return .= '<option value="' . $category['category_id'] . '"';

            if ($serviceInfo['category_id'] == $category['category_id']) {
                $return .= 'selected';
            }

            $return .= '>' . $category['category_name'] . '</option>';
        }

        $return .= '</select>' . "\r\n" . '</div>' . "\r\n" . '</div>' . "\r\n\r\n" . '<div class="service-mode__wrapper">' . "\r\n" . '<div class="service-mode__block">' . "\r\n" . ' <div class="form-group">' . "\r\n" . ' <label>Service type</label>' . "\r\n" . '  <select class="form-control" name="package">' . "\r\n" . ' <option value="1"';

        if ($serviceInfo['service_package'] == 1) {
            $return .= 'selected';
        }

        $return .= '>Service</option>' . "\r\n" . ' <option value="2"';

        if ($serviceInfo['service_package'] == 2) {
            $return .= 'selected';
        }

        $return .= '>Package</option>' . "\r\n" . ' <option value="3"';

        if ($serviceInfo['service_package'] == 3) {
            $return .= 'selected';
        }

        $return .= '>Special Comment</option>' . "\r\n" . ' <option value="4"';

        if ($serviceInfo['service_package'] == 4) {
            $return .= 'selected';
        }

        $return .= '>Package Comment</option>' . "\r\n" . '</select>' . "\r\n" . ' </div>' . "\r\n" . '</div>' . "\r\n" . '<div class="service-mode__block">' . "\r\n" . ' <div class="form-group">' . "\r\n" . ' <label>Mode</label>' . "\r\n" . '  <select class="form-control" name="mode" id="serviceMode">' . "\r\n" . ' <option value="1"';

        if ($serviceInfo['service_api'] == 0) {
            $return .= 'selected';
        }

        $return .= '>Manual</option>' . "\r\n" . ' <option value="2"';

        if ($serviceInfo['service_api'] != 0) {
            $return .= 'selected';
        }

        $return .= '>Automatic (API)</option>' . "\r\n" . '</select>' . "\r\n" . ' </div>' . "\r\n" . '</div>' . "\r\n\r\n" . '<div id="autoMode" style="display: none">' . "\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Service Provider</label>' . "\r\n" . '<select class="form-control" name="provider" id="provider">' . "\r\n" . '  <option value="0">Please select a service provider..</option>';

        foreach ($providers as $provider) {
            $return .= '<option value="' . $provider['id'] . '"';

            if ($serviceInfo['service_api'] == $provider['id']) {
                $return .= 'selected';
            }

            $return .= '>' . $provider['api_name'] . '</option>';
        }

        $return .= '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n" . ' <div id="provider_service">';
        $services = $smmapi->action(['key' => $serviceInfo['api_key'], 'action' => 'services'], $serviceInfo['api_url']);
        $return .= '<div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Service</label>' . "\r\n" . '<select class="form-control" name="service">';

        foreach ($services as $service) {
            $return .= '<option value="' . $service->service . '"';

            if ($serviceInfo['api_service'] == $service->service) {
                $return .= 'selected';
            }

            $return .= '>' . $service->name . ' - ' . $service->rate . '</option>';
        }

        $return .= '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>';
        $return .= '</div>' . "\r\n" . ' <div class="service-mode__block" style="display: none">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Pricing over the purchase price</label>' . "\r\n" . '<select class="form-control" name="saleprice_cal" id="saleprice_cal>' . "\r\n" . '<option value="normal">No</option>' . "\r\n" . '<option value="percent">Add % to the purchase price </option>' . "\r\n" . '<option value="amount">Add amount to the purchase price </option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group" style="display: none">' . "\r\n" . '  <label class="form-group__service-name">Price</label>' . "\r\n" . '  <input type="text" class="form-control" name="saleprice" value="">' . "\r\n" . ' </div>' . "\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Dripfeed</label>' . "\r\n" . '<select class="form-control" name="dripfeed">' . "\r\n" . '<option value="1"';

        if ($serviceInfo['service_dripfeed'] == 1) {
            $return .= 'selected';
        }

        $return .= '>Inactive</option>' . "\r\n" . '<option value="2"';

        if ($serviceInfo['service_dripfeed'] == 2) {
            $return .= 'selected';
        }

        $return .= '>Active</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n" . '</div>' . "\r\n" . '</div>' . "\r\n\r\n" . '<div class="service-mode__wrapper">' . "\r\n" . ' <div class="row">' . "\r\n" . '  <div class="col-md-6 service-mode__block ">' . "\r\n" . '<div class="form-group">' . "\r\n" . '<label>Check Instagram profile privacy?</label>' . "\r\n" . '<select class="form-control" name="instagram_private">' . "\r\n" . '<option value="1"';

        if ($serviceInfo['instagram_private'] == 1) {
            $return .= 'selected';
        }

        $return .= '>No</option>' . "\r\n" . '<option value="2"';

        if ($serviceInfo['instagram_private'] == 2) {
            $return .= 'selected';
        }

        $return .= '>Yes</option>' . "\r\n" . ' </select>' . "\r\n" . '</div>' . "\r\n" . '  </div>' . "\r\n" . '  <div class="col-md-6 service-mode__block ">' . "\r\n" . '<div class="form-group">' . "\r\n" . '<label>Start count</label>' . "\r\n" . '<select class="form-control" name="start_count">' . "\r\n" . '<option value="none"';

        if ($serviceInfo['start_count'] == 'none') {
            $return .= 'selected';
        }

        $return .= '>No</option>' . "\r\n" . '<option value="instagram_follower"';

        if ($serviceInfo['start_count'] == 'instagram_follower') {
            $return .= 'selected';
        }

        $return .= '>Instagram follower count</option>' . "\r\n" . '<option value="instagram_photo"';

        if ($serviceInfo['start_count'] == 'instagram_photo') {
            $return .= 'selected';
        }

        $return .= '>Instagram photo like count</option>' . "\r\n" . ' </select>' . "\r\n" . '</div>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n" . ' <div class="row">' . "\r\n" . '  <div class="col-md-6 service-mode__block ">' . "\r\n" . '<div class="form-group">' . "\r\n" . '<label>Enter multiple order to the same link?</label>' . "\r\n" . '<select class="form-control" name="instagram_second">' . "\r\n" . '<option value="2"';

        if ($serviceInfo['instagram_second'] == 2) {
            $return .= 'selected';
        }

        $return .= '>Yes</option>' . "\r\n" . '<option value="1"';

        if ($serviceInfo['instagram_second'] == 1) {
            $return .= 'selected';
        }

        $return .= '>No</option>' . "\r\n" . ' </select>' . "\r\n" . '</div>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n" . '</div>' . "\r\n\r\n" . '<div class="form-group">' . "\r\n" . '<label class="form-group__service-name">Service price (Rate per 1000)</label>' . "\r\n" . '<input type="text" class="form-control" name="price" value="' . $serviceInfo['service_price'] . '">' . "\r\n" . '</div>' . "\r\n\r\n" . '<div class="form-group">' . "\r\n" . '<label class="form-group__service-name">Service Description</label>' . "\r\n" . '<textarea class="form-control" name="description" rows="4">' . $serviceInfo['service_description'] . '</textarea>' . "\r\n" . '</div>' . "\r\n\r\n" . '<div class="row">' . "\r\n" . '<div class="col-md-6 form-group">' . "\r\n" . ' <label class="form-group__service-name">Minimum order</label>' . "\r\n" . ' <input type="text" class="form-control" name="min" value="' . $serviceInfo['service_min'] . '">' . "\r\n" . '</div>' . "\r\n\r\n" . '<div class="col-md-6 form-group">' . "\r\n" . ' <label class="form-group__service-name">Maximum order</label>' . "\r\n" . ' <input type="text" class="form-control" name="max" value="' . $serviceInfo['service_max'] . '">' . "\r\n" . '</div>' . "\r\n" . '</div>' . "\r\n\r\n" . '<hr>' . "\r\n\r\n" . '<div class="service-mode__block">' . "\r\n" . '<div class="form-group">' . "\r\n" . '<label>How should the order be? With:</label>' . "\r\n" . ' <select class="form-control" name="want_username">' . "\r\n" . '<option value="1"';

        if ($serviceInfo['want_username'] == 1) {
            $return .= 'selected';
        }

        $return .= '>Link</option>' . "\r\n" . '<option value="2"';

        if ($serviceInfo['want_username'] == 2) {
            $return .= 'selected';
        }

        $return .= '>Username</option>' . "\r\n" . ' </select>' . "\r\n" . '</div>' . "\r\n" . '</div>' . "\r\n\r\n" . '<div class="service-mode__block">' . "\r\n" . '<div class="form-group">' . "\r\n" . '<label>Hidden Service</label>' . "\r\n" . ' <select class="form-control" name="secret">' . "\r\n" . '<option value="2"';

        if ($serviceInfo['service_secret'] == 2) {
            $return .= 'selected';
        }

        $return .= '>No</option>' . "\r\n" . '<option value="1"';

        if ($serviceInfo['service_secret'] == 1) {
            $return .= 'selected';
        }

        $return .= '>Yes</option>' . "\r\n" . ' </select>' . "\r\n" . '</div>' . "\r\n" . '</div>' . "\r\n\r\n" . '<div class="service-mode__block">' . "\r\n" . '<div class="form-group">' . "\r\n" . '<label>Service Speed</label>' . "\r\n" . ' <select class="form-control" name="speed">' . "\r\n" . '<option value="1"';

        if ($serviceInfo['service_speed'] == 1) {
            $return .= 'selected';
        }

        $return .= '>Slow</option>' . "\r\n" . '<option value="2"';

        if ($serviceInfo['service_speed'] == 2) {
            $return .= 'selected';
        }

        $return .= '>Sometimes Slow</option>' . "\r\n" . '<option value="3"';

        if ($serviceInfo['service_speed'] == 3) {
            $return .= 'selected';
        }

        $return .= '>Normal</option>' . "\r\n" . '<option value="4"';

        if ($serviceInfo['service_speed'] == 4) {
            $return .= 'selected';
        }

        $return .= '>Fast</option>' . "\r\n" . ' </select>' . "\r\n" . '</div>' . "\r\n" . '</div>' . "\r\n\r\n" . '  </div>' . "\r\n\r\n" . '<div class="modal-footer">' . "\r\n" . '<button type="submit" class="btn btn-primary">Service Update</button>' . "\r\n" . '<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . '</div>' . "\r\n" . '</form>' . "\r\n" . '<script type="text/javascript">' . "\r\n" . 'var site_url = $("head base").attr("href");' . "\r\n" . '$("#provider").change(function(){' . "\r\n" . ' var provider = $(this).val();' . "\r\n" . ' getProviderServices(provider,site_url);' . "\r\n" . '});' . "\r\n\r\n" . 'getProvider();' . "\r\n" . '$("#serviceMode").change(function(){' . "\r\n" . ' getProvider();' . "\r\n" . '});' . "\r\n\r\n" . 'getSalePrice();' . "\r\n" . '$("#saleprice_cal").change(function(){' . "\r\n" . ' getSalePrice();' . "\r\n" . '});' . "\r\n\r\n" . 'getSubscription();' . "\r\n" . '$("#subscription_package").change(function(){' . "\r\n" . ' getSubscription();' . "\r\n" . '});' . "\r\n" . 'function getProviderServices(provider,site_url){' . "\r\n" . ' if( provider == 0 ){' . "\r\n" . '  $("#provider_service").hide();' . "\r\n" . ' }else{' . "\r\n" . '  $.post(site_url+"admin/ajax_data",{action:"providers_list",provider:provider}).done(function( data ) {' . "\r\n" . '$("#provider_service").show();' . "\r\n" . '$("#provider_service").html(data);' . "\r\n" . '  }).fail(function(){' . "\r\n" . 'alert("Somethings went wrong!");' . "\r\n" . '  });' . "\r\n" . ' }' . "\r\n" . '}' . "\r\n\r\n" . 'function getProvider(){' . "\r\n" . ' var mode = $("#serviceMode").val();' . "\r\n" . '  if( mode == 1 ){' . "\r\n" . '$("#autoMode").hide();' . "\r\n" . '  }else{' . "\r\n" . '$("#autoMode").show();' . "\r\n" . '  }' . "\r\n" . '}' . "\r\n\r\n" . 'function getSalePrice(){' . "\r\n" . ' var type = $("#saleprice_cal").val();' . "\r\n" . '  if( type == "normal" ){' . "\r\n" . '$("#saleprice").hide();' . "\r\n" . '$("#servicePrice").show();' . "\r\n" . '  }else{' . "\r\n" . '$("#saleprice").show();' . "\r\n" . '$("#servicePrice").hide();' . "\r\n" . '  }' . "\r\n" . '}' . "\r\n\r\n" . 'function getSubscription(){' . "\r\n" . ' var type = $("#subscription_package").val();' . "\r\n" . '  if( type == "11" || type == "12" ){' . "\r\n" . '$("#unlimited").show();' . "\r\n" . '$("#limited").hide();' . "\r\n" . '  }else{' . "\r\n" . '$("#unlimited").hide();' . "\r\n" . '$("#limited").show();' . "\r\n" . '  }' . "\r\n" . '}' . "\r\n" . '</script>' . "\r\n" . '';
        echo json_encode(['content' => $return, 'title' => '']);
    }
} elseif ($action == 'new_subscriptions') {
    $categories = $conn->prepare('SELECT * FROM categories ORDER BY category_line ');
    $categories->execute([]);
    $categories = $categories->fetchAll(PDO::FETCH_ASSOC);
    $providers = $conn->prepare('SELECT * FROM service_api');
    $providers->execute([]);
    $providers = $providers->fetchAll(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="' . site_url('admin/services/new-subscription') . '" method="post" data-xhr="true">' . "\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Service name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Service Category</label>' . "\r\n" . '<select class="form-control" name="category">' . "\r\n" . '  <option value="0">Please select a category..</option>';

    foreach ($categories as $category) {
        $return .= '<option value="' . $category['category_id'] . '">' . $category['category_name'] . '</option>';
    }

    $return .= '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Subscription type</label>' . "\r\n" . '<select class="form-control" name="package" id="subscription_package">' . "\r\n" . '  <option value="11">Instagram Automatic Like - Unlimited</option>' . "\r\n" . '  <option value="12">Instagram Automatic View - Unlimited</option>' . "\r\n" . '  <option value="14">Instagram Automatic Like - Timed</option>' . "\r\n" . '  <option value="15">Instagram Automatic View - Timed</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__wrapper">' . "\r\n\r\n" . '  <div class="service-mode__block">' . "\r\n" . '<div class="form-group">' . "\r\n" . '<label>Mode</label>' . "\r\n" . '<select class="form-control" name="mode" id="serviceMode">' . "\r\n" . '<option value="2">Automatic (API)</option>' . "\r\n" . ' </select>' . "\r\n" . '</div>' . "\r\n" . '  </div>' . "\r\n\r\n" . '  <div id="autoMode" style="display: none">' . "\r\n" . '<div class="service-mode__block">' . "\r\n" . '<div class="form-group">' . "\r\n" . '<label>Service Provider</label>' . "\r\n" . ' <select class="form-control" name="provider" id="provider">' . "\r\n" . '<option value="0">Please select a service provider..</option>';

    foreach ($providers as $provider) {
        $return .= '<option value="' . $provider['id'] . '">' . $provider['api_name'] . '</option>';
    }

    $return .= '</select>' . "\r\n" . '</div>' . "\r\n" . '</div>' . "\r\n" . '<div id="provider_service">' . "\r\n" . '</div>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div id="unlimited">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '<label class="form-group__service-name">Service price (Rate per 1000)</label>' . "\r\n" . '<input type="text" class="form-control" name="price" value="">' . "\r\n" . '  </div>' . "\r\n\r\n" . '  <div class="row">' . "\r\n" . '<div class="col-md-6 form-group">' . "\r\n" . '<label class="form-group__service-name">Minimum order</label>' . "\r\n" . '<input type="text" class="form-control" name="min" value="">' . "\r\n" . '</div>' . "\r\n\r\n" . '<div class="col-md-6 form-group">' . "\r\n" . '<label class="form-group__service-name">Maximum order</label>' . "\r\n" . '<input type="text" class="form-control" name="max" value="">' . "\r\n" . '</div>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div id="limited">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '<label class="form-group__service-name">Service price</label>' . "\r\n" . '<input type="text" class="form-control" name="limited_price" value="">' . "\r\n" . '  </div>' . "\r\n\r\n\r\n\r\n" . '  <div class="row">' . "\r\n" . '<div class="col-md-6 form-group">' . "\r\n" . '<label class="form-group__service-name">Post limit</label>' . "\r\n" . '<input type="text" class="form-control" name="autopost" value="">' . "\r\n" . '</div>' . "\r\n\r\n" . '<div class="col-md-6 form-group">' . "\r\n" . '<label class="form-group__service-name">Order Quantity</label>' . "\r\n" . '<input type="text" class="form-control" name="limited_min" value="">' . "\r\n" . '</div>' . "\r\n" . '  </div>' . "\r\n" . '  <div class="form-group">' . "\r\n" . '<label class="form-group__service-name">Package Time <small>(day)</small></label>' . "\r\n" . '<input type="text" class="form-control" name="autotime" value="">' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <hr>' . "\r\n\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Hidden Service</label>' . "\r\n" . '<select class="form-control" name="secret">' . "\r\n" . ' <option value="2">No</option>' . "\r\n" . ' <option value="1">Yes</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Service Speed</label>' . "\r\n" . '<select class="form-control" name="speed">' . "\r\n" . ' <option value="1">Slow</option>' . "\r\n" . ' <option value="2">Sometimes Slow</option>' . "\r\n" . ' <option value="3">Normal</option>' . "\r\n" . ' <option value="4">Fast</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Add new subscription</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>' . "\r\n" . ' <script type="text/javascript">' . "\r\n" . ' var site_url = $("head base").attr("href");' . "\r\n" . '  $("#provider").change(function(){' . "\r\n" . 'var provider = $(this).val();' . "\r\n" . 'getProviderServices(provider,site_url);' . "\r\n" . '  });' . "\r\n\r\n" . '  getProvider();' . "\r\n" . '  $("#serviceMode").change(function(){' . "\r\n" . 'getProvider();' . "\r\n" . '  });' . "\r\n\r\n" . '  getSalePrice();' . "\r\n" . '  $("#saleprice_cal").change(function(){' . "\r\n" . 'getSalePrice();' . "\r\n" . '  });' . "\r\n\r\n" . '  getSubscription();' . "\r\n" . '  $("#subscription_package").change(function(){' . "\r\n" . 'getSubscription();' . "\r\n" . '  });' . "\r\n" . '  function getProviderServices(provider,site_url){' . "\r\n" . 'if( provider == 0 ){' . "\r\n" . '$("#provider_service").hide();' . "\r\n" . '}else{' . "\r\n" . '$.post(site_url+"admin/ajax_data",{action:"providers_list",provider:provider}).done(function( data ) {' . "\r\n" . ' $("#provider_service").show();' . "\r\n" . ' $("#provider_service").html(data);' . "\r\n" . '}).fail(function(){' . "\r\n" . ' alert("Somethings went wrong!");' . "\r\n" . '});' . "\r\n" . '}' . "\r\n" . '  }' . "\r\n\r\n" . '  function getProvider(){' . "\r\n" . 'var mode = $("#serviceMode").val();' . "\r\n" . 'if( mode == 1 ){' . "\r\n" . ' $("#autoMode").hide();' . "\r\n" . '}else{' . "\r\n" . ' $("#autoMode").show();' . "\r\n" . '}' . "\r\n" . '  }' . "\r\n\r\n" . '  function getSalePrice(){' . "\r\n" . 'var type = $("#saleprice_cal").val();' . "\r\n" . 'if( type == "normal" ){' . "\r\n" . ' $("#saleprice").hide();' . "\r\n" . ' $("#servicePrice").show();' . "\r\n" . '}else{' . "\r\n" . ' $("#saleprice").show();' . "\r\n" . ' $("#servicePrice").hide();' . "\r\n" . '}' . "\r\n" . '  }' . "\r\n\r\n" . '  function getSubscription(){' . "\r\n" . 'var type = $("#subscription_package").val();' . "\r\n" . 'if( type == "11" || type == "12" ){' . "\r\n" . ' $("#unlimited").show();' . "\r\n" . ' $("#limited").hide();' . "\r\n" . '}else{' . "\r\n" . ' $("#unlimited").hide();' . "\r\n" . ' $("#limited").show();' . "\r\n" . '}' . "\r\n" . '  }' . "\r\n" . ' </script>' . "\r\n" . ' ';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'new_category') {
    $return = '<form class="form" action="' . site_url('admin/services/new-category') . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Category name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Category Icon Name</label>' . "\r\n" . '  <input type="text" class="form-control" name="icon" value="">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-refill">Refillable?</label>' . "\r\n" . ' <select name="is_refill" class="form-control"><option value="true">True</option><option value="false">False</option></select> ' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Hidden Category</label>' . "\r\n" . '<select class="form-control" name="secret">' . "\r\n" . '  <option value="2">No</option>' . "\r\n" . '  <option value="1">Yes</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Create category</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'edit_category') {
    $id = $_POST['id'];
    $category = $conn->prepare('SELECT * FROM categories WHERE category_id=:id ');
    $category->execute(['id' => $id]);
    $category = $category->fetch(PDO::FETCH_ASSOC);
    
    if($category['is_refill'] == "true"){
        $true = "selected";
        $false = "";
    }else{
        $false = "selected";
        $true = "";
    }
    $return = '<form class="form" action="' . site_url('admin/services/edit-category/' . $id) . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Category name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="' . $category['category_name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Category Icon Name</label>' . "\r\n" . '  <input type="text" class="form-control" name="icon" value="' . $category['category_icon'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-refill">Refillable?</label>' . "\r\n" . ' <select name="is_refill" class="form-control"><option '.$true.' value="true">True</option><option '.$false.' value="false">False</option></select> ' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Hidden Category</label>' . "\r\n" . '<select class="form-control" name="secret">' . "\r\n" . '  <option value="2"';

    if ($category['category_secret'] == 2) {
        $return .= 'selected';
    }

    $return .= '>No</option>' . "\r\n" . '  <option value="1"';

    if ($category['category_secret'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Yes</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update category</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'import_services') {
    
    $categories = $conn->prepare('SELECT * FROM categories ORDER BY category_line ');
    $categories->execute([]);
    $categories = $categories->fetchAll(PDO::FETCH_ASSOC);
    
    $providers = $conn->prepare('SELECT * FROM service_api');
    $providers->execute([]);
    $providers = $providers->fetchAll(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="' . site_url('admin/services/get_services_add/') . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div id="firstStep">' . "\r\n" . '  <div class="service-mode__block">' . "\r\n" . '<div class="form-group">' . "\r\n" . '<label>Service Provider</label>' . "\r\n" . '<select class="form-control" name="provider" id="provider">' . "\r\n" . '<option value="0">Please select a service provider..</option>';

    foreach ($providers as $provider) {
        $return .= '<option value="' . $provider['id'] . '">' . $provider['api_name'] . '</option>';
    }

    $return .= '</select>' . "\r\n" . '</div>';
    
    $return .="\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div id="secondStep">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div id="thirdStep">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . '  <button type="button" class="btn btn-primary" id="nextStep" data-step="first">Next step</button>' . "\r\n" . '  <button type="submit" class="btn btn-primary" id="submitStep">Add Services</button>' . "\r\n" . ' </div>' . "\r\n\r\n" . '</form>' . "\r\n" . ' <script>' . "\r\n" . '  $("#submitStep").hide();' . "\r\n" . '  $("#nextStep").on("click", function() {' . "\r\n" . 'var now_step = $(this).attr("data-step");' . "\r\n" . 'var provider = $("#provider").val();' . "\r\n" . '$("#secondStep").hide();' . "\r\n" . 'if( now_step == "first" ){' . "\r\n" . ' if( provider == 0 ){' . "\r\n" . '  $.toast({' . "\r\n" . 'heading: "Fail",' . "\r\n" . 'text: "Please select service provider..",' . "\r\n" . 'icon: "error",' . "\r\n" . 'loader: true,' . "\r\n" . 'loaderBg: "#9EC600"' . "\r\n" . '  });' . "\r\n" . ' }else{' . "\r\n" . '  $("#firstStep").hide();' . "\r\n" . '  $("#secondStep").show();' . "\r\n" . '  $.post("admin/ajax_data", {provider:provider,action:"import_services_list" }, function(data){' . "\r\n" . '$("#secondStep").html(data);' . "\r\n" . '  });' . "\r\n" . '  $("#nextStep").attr("data-step","second");' . "\r\n" . ' }' . "\r\n" . '}else if( now_step == "second" ){' . "\r\n" . '  var array   = [];' . "\r\n" . ' $(\'[class^="selectServices-"]\').each(function () {' . "\r\n" . '  var id  = $(this).val();' . "\r\n" . '  var check = $(this).prop("checked");' . "\r\n" . '  var provider = $(this).attr("data-provider");' . "\r\n" . 'if( check == true ){' . "\r\n" . 'var params = {};' . "\r\n" . 'params["id"]  = id;' . "\r\n" . 'params["category"]= $(this).attr("data-category");' . "\r\n" . 'array.push(params);' . "\r\n" . '}' . "\r\n" . ' });' . "\r\n" . ' var count = array.length;' . "\r\n" . '   if( count ){' . "\r\n" . ' $.post("admin/ajax_data", {provider:provider,action:"import_services_last",services:array }, function(data){' . "\r\n" . ' $("#thirdStep").html(data);' . "\r\n" . ' });' . "\r\n" . ' $("#nextStep").hide();' . "\r\n" . ' $("#submitStep").show();' . "\r\n" . '   }else{' . "\r\n" . ' $("#nextStep").attr("data-step","second");' . "\r\n" . ' $("#firstStep").hide();' . "\r\n" . ' $("#secondStep").show();' . "\r\n" . ' $("#nextStep").show();' . "\r\n" . ' $("#submitStep").hide();' . "\r\n" . ' $.toast({' . "\r\n" . '  heading: "Fail",' . "\r\n" . '  text: "Please select at least 1 service you want to add.",' . "\r\n" . '  icon: "error",' . "\r\n" . '  loader: true,' . "\r\n" . '  loaderBg: "#9EC600"' . "\r\n" . ' });' . "\r\n" . '   }' . "\r\n\r\n" . '}' . "\r\n" . '  });' . "\r\n" . ' </script>' . "\r\n" . ' ';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'import_services_list') {
    $provider_id = $_POST['provider'];
    $smmapi = new SMMApi();
    $provider = $conn->prepare('SELECT * FROM service_api WHERE id=:id');
    $provider->execute(['id' => $provider_id]);
    $provider = $provider->fetch(PDO::FETCH_ASSOC);

    if ($provider['api_type'] == 1) {
        $services = $smmapi->action(['key' => $provider['api_key'], 'action' => 'services'], $provider['api_url']);

        if ($services) {
            $grouped = array_group_by($services, 'category');
            $category_id = 0;
            echo '<div class="">' . "\r\n" . '  <div class="services-import__body">' . "\r\n" . ' <div>' . "\r\n" . '  <div class="services-import__list-wrap">' . "\r\n" . ' <div class="services-import__scroll-wrap"><label class="btn btn-primary"> <input id="checkk" type="checkbox"> Select All</label>';

            foreach ($grouped as $category) {
                $category_id++;
                echo "\r\n" . ' <span>' . "\r\n" . '   <div class="services-import__category">' . "\r\n" . '<div class="services-import__category-title">' . "\r\n" . '<label><input class="check_cate" type="checkbox" data-id="' . $category_id . '" id="checkAll-' . $category_id . '">' . $category[0]->category . '</label>' . "\r\n" . '</div>' . "\r\n" . '   </div>' . "\r\n" . '   <div class="services-import__packages">' . "\r\n" . '<ul>';

                for ($i = 0; $i < count($category); $i++) {
                    echo '<li><label><input data-service="' . $category[$i]->name . '" data-provider="' . $provider['id'] . '" data-category="' . $category_id . '" class="selectServices-' . $category_id . '" type="checkbox" value="' . $category[$i]->service . '" name="services[]">' . $category[$i]->service . ' - ' . $category[$i]->name . '<span class="services-import__packages-price">' . priceFormat($category[$i]->rate) . '</span></label></li>';
                }

                echo '</ul>' . "\r\n" . '   </div>' . "\r\n" . ' </span>';
            }

            echo "\r\n" . ' </div>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n" . '</div>' . "\r\n" . '<script> $("#checkk").click(function () {$("#secondStep :checkbox").not(this).prop("checked", this.checked);});</script><script>' . "\r\n" . '$(\'[id^="checkAll-"]\').on("click", function() {' . "\r\n" . 'var id = $(this).attr("data-id");' . "\r\n" . ' if ( $(this).prop("checked") == true ) {' . "\r\n" . '  $(".selectServices-"+id).not(this).prop("checked", true);' . "\r\n" . ' }else{' . "\r\n" . '  $(".selectServices-"+id).not(this).prop("checked", false);' . "\r\n" . ' }' . "\r\n" . ' });' . "\r\n" . '</script>' . "\r\n" . '</div>';
        } else {
            echo 'Somethings went wrong, please try again later.';
        }
    }
} elseif ($action == 'import_services_last') {
    //print_r($_POST); die;
    $provider_id = $_POST['provider'];
    $services = json_decode(json_encode($_POST['services']));
    $smmapi = new SMMApi();
    $provider = $conn->prepare('SELECT * FROM service_api WHERE id=:id');
    $provider->execute(['id' => $provider_id]);
    $provider = $provider->fetch(PDO::FETCH_ASSOC);
    $apiServices = $smmapi->action(['key' => $provider['api_key'], 'action' => 'services'], $provider['api_url']);
    $grouped = array_group_by($services, 'category');
    echo "\r\n" . '<div class="services-import__body">' . "\r\n" . '   <div>' . "\r\n" . '<div class="services-import__fields">' . "\r\n" . '  <div class="services-import__step3-field">' . "\r\n" . '<div style="display: none;" class="services-import__placeholder-title">Fixed (1.00)</div>' . "\r\n" . '<input style="display: none;" type="number" placeholder="0" id="raise-fixed" value="">' . "\r\n" . '  </div>' . "\r\n" . '  <div class="services-import__step3-plus">+</div>' . "\r\n" . '  <div class="services-import__step3-field">' . "\r\n" . '<div style="display: none;" class="services-import__placeholder-title">Percent (%)</div>' . "\r\n" . '<input style="display: none;" type="number" placeholder="0" id="raise-percent" value="">' . "\r\n" . '  <div class="services-import__step3-field">' . "\r\n" . '<div class="services-import__placeholder-title">Profit Percent (%)</div>' . "\r\n" . '<input name="percentage_increase" type="number" placeholder="0" value="">' . "\r\n" . '  </div>'    . "\r\n" . '  <div style="display: none;" class="services-import__step3-actions"><span class="btn btn-default">Reset calculations</span></div>' . "\r\n" . '</div>' . "\r\n" . '<div class="services-import__list-wrap services-import__list-active">' . "\r\n" . '  <div class="services-import__scroll-wrap">';
    $category_id = $_POST['service']['category'];
    $c = 0;

    foreach ($grouped as $category) {
        foreach ($apiServices as $key => $value) {
            if ($category[$category_id]->id == $value->service) {
                $categoryName = $value->category;
            }
        }

        $category_id = $category_id++;
        $c++;
        echo '<span class="providerCategory" id="providerCategory-' . $c . '">' . "\r\n" . '  <div class="services-import__category">' . "\r\n" . '<div class="services-import__category-title"><label>' . $categoryName . '</label></div>' . "\r\n" . '  </div>' . "\r\n" . '  <div class="services-import__packages">' . "\r\n" . '<ul>';

        for ($i = 0; $i < count($category); $i++) {
            foreach ($apiServices as $apiService) {
                if ($apiService->service == $category[$i]->id) {
                    echo '<li id="providerService-' . $apiService->service . '">' . "\r\n" . ' <label>' . "\r\n" . '  ' . $apiService->service . ' - ' . $apiService->name . "\r\n" . '  <span class="services-import__packages-price-edit" >' . "\r\n" . ' <div class="services-import__packages-price-lock" data-category="' . $c . '" data-id="servicedelete-' . $apiService->service . '" data-service="' . $apiService->service . '">' . "\r\n" . ' <span class="fa fa-trash"></span>' . "\r\n" . ' </div>' . "\r\n" . ' <div class="services-import__packages-price-lock" data-id="servicelock-' . $apiService->service . '" data-service="' . $apiService->service . '">' . "\r\n" . ' <span class="fa fa-unlock"></span>' . "\r\n" . ' </div>' . "\r\n" . ' <input id="servicePriceCal' . $apiService->service . '" type="text" class="services-import__price" data-rate="' . priceFormat($apiService->rate) . '" data-service="' . $apiService->service . '" name="servicesList[' . $apiService->service . ']" value="' . priceFormat($apiService->rate) . '">' . "\r\n" . ' <span class="services-import__provider-price">' . priceFormat($apiService->rate) . '</span>' . "\r\n" . '  </span>' . "\r\n" . ' </label>' . "\r\n" . '</li>';
                }
            }
        }

        echo '</ul>' . "\r\n" . '  </div>' . "\r\n" . '</span>';
    }

    echo '</div>' . "\r\n" . '</div>' . "\r\n" . '   </div>' . "\r\n" . ' </div>' . "\r\n" . ' <script>' . "\r\n" . ' function formatCurrency(total) {' . "\r\n" . 'var neg = false;' . "\r\n" . 'if(total < 0) {' . "\r\n" . ' neg = true;' . "\r\n" . ' total = Math.abs(total);' . "\r\n" . '}' . "\r\n" . 'return parseFloat(total, 10).toFixed(2).replace(/(\\d)(?=(\\d{3})+\\.)/g, "$1,").toString();' . "\r\n" . ' }' . "\r\n" . ' function chargeService(){' . "\r\n" . '  var add_fixed = $("#raise-fixed").val();' . "\r\n" . '  var add_percent   = $("#raise-percent").val();' . "\r\n" . '  $(".services-import__price").each(function(){' . "\r\n" . 'if( $(this).attr("disabled") != "disabled" ){' . "\r\n" . 'var rate= $(this).attr("data-rate");' . "\r\n" . 'var service   = $(this).attr("data-service");' . "\r\n" . '$.post("admin/ajax_data",{action:"price_providerCal",fixed:add_fixed,percent:add_percent,rate:rate}, function(data){' . "\r\n" . ' $("#servicePriceCal"+service).val(data);' . "\r\n" . '});' . "\r\n" . '}' . "\r\n" . '  });' . "\r\n" . ' }' . "\r\n" . '  $(\'[data-id^="servicedelete-"]\').on("click", function() {' . "\r\n" . 'var id= $(this).attr("data-service");' . "\r\n" . 'var category = $(this).attr("data-category");' . "\r\n" . '$("li#providerService-"+id).remove();' . "\r\n" . 'if( $("#providerCategory-"+category+" > .services-import__packages > ul > li").length == 0 ){' . "\r\n" . ' $("#providerCategory-"+category).remove();' . "\r\n" . '}' . "\r\n" . '  });' . "\r\n" . '  $(\'[data-id^="servicelock-"]\').on("click", function() {' . "\r\n" . 'var service_id = $(this).attr("data-service");' . "\r\n" . 'var lock= $(this).find("span").attr("class");' . "\r\n" . 'if( lock == "fa fa-unlock" ){' . "\r\n" . '$(this).find("span").removeClass("fa fa-unlock");' . "\r\n" . '$(this).find("span").addClass("fa fa-lock");' . "\r\n" . '$(\'[data-service="\'+service_id+\'"]\').attr("disabled",true);' . "\r\n" . '} else{' . "\r\n" . '$(this).find("span").removeClass("fa fa-lock");' . "\r\n" . '$(this).find("span").addClass("fa fa-unlock");' . "\r\n" . '$(\'[data-service="\'+service_id+\'"]\').attr("disabled",false);' . "\r\n" . '}' . "\r\n" . '  });' . "\r\n\r\n" . '  $(".services-import__step3-actions").on("click", function() {' . "\r\n" . 'var add_fixed = $("#raise-fixed").val("");' . "\r\n" . 'var add_percent   = $("#raise-percent").val("");' . "\r\n" . '$(".services-import__price").each(function(){' . "\r\n" . 'if( $(this).attr("disabled") != "disabled" ){' . "\r\n" . ' var rate= $(this).attr("data-rate");' . "\r\n" . ' var service   = $(this).attr("data-service");' . "\r\n" . ' $.post("admin/ajax_data",{action:"price_providerCal",fixed:add_fixed,percent:add_percent,rate:rate}).done(function(data){' . "\r\n" . '  $("#servicePriceCal"+service).val(data);' . "\r\n" . ' });' . "\r\n" . '}' . "\r\n" . '});' . "\r\n" . '  });' . "\r\n\r\n" . '  $("#raise-fixed").on("keyup", function(){' . "\r\n" . 'chargeService();' . "\r\n" . '  });' . "\r\n\r\n" . '  $("#raise-percent").on("keyup", function(){' . "\r\n" . 'chargeService();' . "\r\n" . '  });' . "\r\n\r\n" . ' </script>' . "\r\n" . ' ';
} elseif ($action == 'price_providerCal') {
    $fixed = $_POST['fixed'];
    $percent = $_POST['percent'];
    $rate = $_POST['rate'];
    $total = $rate;
    if (is_numeric($percent) && (0 < $percent)) {
        $total = $total + (($rate * $percent) / 100);
    }
    if (is_numeric($fixed) && (0 < $fixed)) {
        $total = $total + $fixed;
    }

    echo $total;
} elseif ($action == 'new_ticket') {
    $return = '<form class="form" action="' . site_url('admin/tickets/new') . '" method="post" data-xhr="true">' . "\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Username</label>' . "\r\n" . '  <input type="text" class="form-control" name="username" value="">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Subject</label>' . "\r\n" . '  <input type="text" class="form-control" name="subject" value="">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Message</label>' . "\r\n" . '  <textarea class="form-control" name="message" rows="4"></textarea>' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Create</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif (($action == 'edit_paymentmethod') && ($_POST['id'] == 'paytr')) {
    $id = $_POST['id'];
    $method = $conn->prepare('SELECT * FROM payment_methods WHERE method_get=:id ');
    $method->execute(['id' => $id]);
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extra = json_decode($method['method_extras'], true);
    $return = '<form class="form" action="' . site_url('admin/settings/payment-methods/edit/' . $id) . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Method name</label>' . "\r\n" . '  <input type="text" class="form-control" readonly value="' . $method['method_name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Visibility</label>' . "\r\n" . '<select class="form-control" name="method_type">' . "\r\n" . '  <option value="2"';

    if ($method['method_type'] == 2) {
        $return .= 'selected';
    }

    $return .= '>Active</option>' . "\r\n" . '  <option value="1"';

    if ($method['method_type'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Inactive</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Visible name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="' . $extra['name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Minimum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="min" value="' . $extra['min'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Maximum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="max" value="' . $extra['max'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <hr>' . "\r\n" . '  <p class="card-description">' . "\r\n" . '<ul>' . "\r\n" . '<li>' . "\r\n" . ' API Callback address: <code>';
    $return .= site_url('payment/' . $method['method_get']);
    $return .= '</code>' . "\r\n" . '</li>' . "\r\n" . '</ul>' . "\r\n" . '  </p>' . "\r\n" . ' <hr>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Merchant id</label>' . "\r\n" . '  <input type="text" class="form-control" name="merchant_id" value="' . $extra['merchant_id'] . '">' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Merchant key</label>' . "\r\n" . '  <input type="text" class="form-control" name="merchant_key" value="' . $extra['merchant_key'] . '">' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Merchant salt</label>' . "\r\n" . '  <input type="text" class="form-control" name="merchant_salt" value="' . $extra['merchant_salt'] . '">' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Commission, %</label>' . "\r\n" . '  <input type="text" class="form-control" name="fee" value="' . $extra['fee'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif (($action == 'edit_paymentmethod') && ($_POST['id'] == 'paytr_havale')) {
    $id = $_POST['id'];
    $method = $conn->prepare('SELECT * FROM payment_methods WHERE method_get=:id ');
    $method->execute(['id' => $id]);
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extra = json_decode($method['method_extras'], true);
    $return = '<form class="form" action="' . site_url('admin/settings/payment-methods/edit/' . $id) . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Method name</label>' . "\r\n" . '  <input type="text" class="form-control" readonly value="' . $method['method_name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Visibility</label>' . "\r\n" . '<select class="form-control" name="method_type">' . "\r\n" . '  <option value="2"';

    if ($method['method_type'] == 2) {
        $return .= 'selected';
    }

    $return .= '>Active</option>' . "\r\n" . '  <option value="1"';

    if ($method['method_type'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Inactive</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Visible name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="' . $extra['name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Minimum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="min" value="' . $extra['min'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Maximum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="max" value="' . $extra['max'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <hr>' . "\r\n" . '  <p class="card-description">' . "\r\n" . '<ul>' . "\r\n" . '<li>' . "\r\n" . ' API callback address: <code>';
    $return .= site_url('payment/paytr');
    $return .= '</code>' . "\r\n" . '</li>' . "\r\n" . '</ul>' . "\r\n" . '  </p>' . "\r\n" . ' <hr>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Merchant id</label>' . "\r\n" . '  <input type="text" class="form-control" name="merchant_id" value="' . $extra['merchant_id'] . '">' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Merchant key</label>' . "\r\n" . '  <input type="text" class="form-control" name="merchant_key" value="' . $extra['merchant_key'] . '">' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Merchant salt</label>' . "\r\n" . '  <input type="text" class="form-control" name="merchant_salt" value="' . $extra['merchant_salt'] . '">' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Commission, %</label>' . "\r\n" . '  <input type="text" class="form-control" name="fee" value="' . $extra['fee'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif (($action == 'edit_paymentmethod') && ($_POST['id'] == 'paywant')) {
    $id = $_POST['id'];
    $method = $conn->prepare('SELECT * FROM payment_methods WHERE method_get=:id ');
    $method->execute(['id' => $id]);
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extra = json_decode($method['method_extras'], true);
    $return = '<form class="form" action="' . site_url('admin/settings/payment-methods/edit/' . $id) . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Method name</label>' . "\r\n" . '  <input type="text" class="form-control" readonly value="' . $method['method_name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Visibility</label>' . "\r\n" . '<select class="form-control" name="method_type">' . "\r\n" . '  <option value="2"';

    if ($method['method_type'] == 2) {
        $return .= 'selected';
    }

    $return .= '>Active</option>' . "\r\n" . '  <option value="1"';

    if ($method['method_type'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Inactive</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Visible name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="' . $extra['name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Minimum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="min" value="' . $extra['min'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Maximum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="max" value="' . $extra['max'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <hr>' . "\r\n" . '  <p class="card-description">' . "\r\n" . '<ul>' . "\r\n" . '<li>' . "\r\n" . ' API callback address: <code>';
    $return .= site_url('payment/' . $method['method_get']);
    $return .= '</code>' . "\r\n" . '</li>' . "\r\n" . '</ul>' . "\r\n" . '  </p>' . "\r\n" . ' <hr>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">apiKey</label>' . "\r\n" . '  <input type="text" class="form-control" name="apiKey" value="' . $extra['apiKey'] . '">' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">apiSecret</label>' . "\r\n" . '  <input type="text" class="form-control" name="apiSecret" value="' . $extra['apiSecret'] . '">' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Commission, %</label>' . "\r\n" . '  <input type="text" class="form-control" name="fee" value="' . $extra['fee'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Paywant Commission</label>' . "\r\n" . '<select class="form-control" name="commissionType">' . "\r\n" . '  <option value="2"';

    if ($extra['commissionType'] == 2) {
        $return .= 'selected';
    }

    $return .= '>User should pay this commission</option>' . "\r\n" . '  <option value="1"';

    if ($extra['commissionType'] == 1) {
        $return .= 'selected';
    }

    $return .= '>User should not pay this commission</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label>Payment Methods</label>' . "\r\n" . '<div class="form-group col-md-12">' . "\r\n" . ' <div class="row">' . "\r\n" . '  <label class="checkbox-inline col-md-3">' . "\r\n" . '<input type="checkbox" class="access" name="payment_type[]" value="1"';

    if (in_array(1, $extra['payment_type'])) {
        $return .= ' checked';
    }

    $return .= '> Mobile Payment' . "\r\n" . '  </label>' . "\r\n" . '  <label class="checkbox-inline col-md-3">' . "\r\n" . '<input type="checkbox" class="access" name="payment_type[]" value="2"';

    if (in_array(2, $extra['payment_type'])) {
        $return .= ' checked';
    }

    $return .= '> Credit/Bank Card' . "\r\n" . '  </label>' . "\r\n" . '  <label class="checkbox-inline col-md-3">' . "\r\n" . '<input type="checkbox" class="access" name="payment_type[]" value="3"';

    if (in_array(3, $extra['payment_type'])) {
        $return .= ' checked';
    }

    $return .= '> Money Order / EFT' . "\r\n" . '  </label>' . "\r\n" . ' </div>' . "\r\n" . '</div>' . "\r\n" . '  </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif (($action == 'edit_paymentmethod') && ($_POST['id'] == 'paypal')) {
    $id = $_POST['id'];
    $method = $conn->prepare('SELECT * FROM payment_methods WHERE method_get=:id ');
    $method->execute(['id' => $id]);
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extra = json_decode($method['method_extras'], true);
    $return = '<form class="form" action="' . site_url('admin/settings/payment-methods/edit/' . $id) . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Method name</label>' . "\r\n" . '  <input type="text" class="form-control" readonly value="' . $method['method_name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Visibility</label>' . "\r\n" . '<select class="form-control" name="method_type">' . "\r\n" . '  <option value="2"';

    if ($method['method_type'] == 2) {
        $return .= 'selected';
    }

    $return .= '>Active</option>' . "\r\n" . '  <option value="1"';

    if ($method['method_type'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Inactive</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Visible name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="' . $extra['name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Minimum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="min" value="' . $extra['min'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Maximum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="max" value="' . $extra['max'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <hr>' . "\r\n" . '  <p class="card-description">' . "\r\n" . '<ul>' . "\r\n" . '<li>' . "\r\n" . ' API callback address: <code>';
    $return .= site_url('payment/' . $method['method_get']);
    $return .= '</code>' . "\r\n" . '</li>' . "\r\n" . '</ul>' . "\r\n" . '  </p>' . "\r\n" . ' <hr>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Client ID</label>' . "\r\n" . '  <input type="text" class="form-control" name="client_id" value="' . $extra['client_id'] . '">' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Client Secret</label>' . "\r\n" . '  <input type="text" class="form-control" name="client_secret" value="' . $extra['client_secret'] . '">' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Commission, %</label>' . "\r\n" . '  <input type="text" class="form-control" name="fee" value="' . $extra['fee'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif (($action == 'edit_paymentmethod') && ($_POST['id'] == 'stripe')) {
    $id = $_POST['id'];
    $method = $conn->prepare('SELECT * FROM payment_methods WHERE method_get=:id ');
    $method->execute(['id' => $id]);
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extra = json_decode($method['method_extras'], true);
    $return = '<form class="form" action="' . site_url('admin/settings/payment-methods/edit/' . $id) . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Method name</label>' . "\r\n" . '  <input type="text" class="form-control" readonly value="' . $method['method_name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Visibility</label>' . "\r\n" . '<select class="form-control" name="method_type">' . "\r\n" . '  <option value="2"';

    if ($method['method_type'] == 2) {
        $return .= 'selected';
    }

    $return .= '>Active</option>' . "\r\n" . '  <option value="1"';

    if ($method['method_type'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Inactive</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Visible name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="' . $extra['name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Minimum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="min" value="' . $extra['min'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Maximum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="max" value="' . $extra['max'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <hr>' . "\r\n" . '  <p class="card-description">' . "\r\n" . '<ul>' . "\r\n" . '<li>' . "\r\n" . ' API callback address: <code>';
    $return .= site_url('payment/' . $method['method_get']);
    $return .= '</code>' . "\r\n" . '</li>' . "\r\n" . '</ul>' . "\r\n" . '  </p>' . "\r\n" . ' <hr>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Stripe Publishable Key</label>' . "\r\n" . '  <input type="text" class="form-control" name="stripe_publishable_key" value="' . $extra['stripe_publishable_key'] . '">' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Stripe Secret Key</label>' . "\r\n" . '  <input type="text" class="form-control" name="stripe_secret_key" value="' . $extra['stripe_secret_key'] . '">' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Stripe Webhooks Secret</label>' . "\r\n" . '  <input type="text" class="form-control" name="stripe_webhooks_secret" value="' . $extra['stripe_webhooks_secret'] . '">' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Commission, %</label>' . "\r\n" . '  <input type="text" class="form-control" name="fee" value="' . $extra['fee'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif (($action == 'edit_paymentmethod') && ($_POST['id'] == 'coinpayments')) {
    $id = $_POST['id'];
    $method = $conn->prepare('SELECT * FROM payment_methods WHERE method_get=:id ');
    $method->execute(['id' => $id]);
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extra = json_decode($method['method_extras'], true);
    $return = '<form class="form" action="' . site_url('admin/settings/payment-methods/edit/' . $id) . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Method name</label>' . "\r\n" . '  <input type="text" class="form-control" readonly value="' . $method['method_name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Visibility</label>' . "\r\n" . '<select class="form-control" name="method_type">' . "\r\n" . '  <option value="2"';

    if ($method['method_type'] == 2) {
        $return .= 'selected';
    }

    $return .= '>Active</option>' . "\r\n" . '  <option value="1"';

    if ($method['method_type'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Inactive</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Visible name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="' . $extra['name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Minimum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="min" value="' . $extra['min'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Maximum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="max" value="' . $extra['max'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <hr>' . "\r\n" . '  <p class="card-description">' . "\r\n" . '<ul>' . "\r\n" . '<li>' . "\r\n" . ' API callback address: <code>';
    $return .= site_url('payment/' . $method['method_get']);
    $return .= '</code>' . "\r\n" . '</li>' . "\r\n" . '</ul>' . "\r\n" . '  </p>' . "\r\n" . ' <hr>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Coinpayments Public Key</label>' . "\r\n" . '  <input type="text" class="form-control" name="coinpayments_public_key" value="' . $extra['coinpayments_public_key'] . '">' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Coinpayments Private Key</label>' . "\r\n" . '  <input type="text" class="form-control" name="coinpayments_private_key" value="' . $extra['coinpayments_private_key'] . '">' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Coinpayments Crypto Currency</label>' . "\r\n" . '  <input type="text" class="form-control" name="coinpayments_currency" value="' . $extra['coinpayments_currency'] . '">' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Merchant ID</label>' . "\r\n" . '  <input type="text" class="form-control" name="merchant_id" value="' . $extra['merchant_id'] . '">' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">IPN Secret</label>' . "\r\n" . '  <input type="text" class="form-control" name="ipn_secret" value="' . $extra['ipn_secret'] . '">' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Commission, %</label>' . "\r\n" . '  <input type="text" class="form-control" name="fee" value="' . $extra['fee'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif (($action == 'edit_paymentmethod') && ($_POST['id'] == '2checkout')) {
    $id = $_POST['id'];
    $method = $conn->prepare('SELECT * FROM payment_methods WHERE method_get=:id ');
    $method->execute(['id' => $id]);
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extra = json_decode($method['method_extras'], true);
    $return = '<form class="form" action="' . site_url('admin/settings/payment-methods/edit/' . $id) . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Method name</label>' . "\r\n" . '  <input type="text" class="form-control" readonly value="' . $method['method_name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Visibility</label>' . "\r\n" . '<select class="form-control" name="method_type">' . "\r\n" . '  <option value="2"';

    if ($method['method_type'] == 2) {
        $return .= 'selected';
    }

    $return .= '>Active</option>' . "\r\n" . '  <option value="1"';

    if ($method['method_type'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Inactive</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Visible name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="' . $extra['name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Minimum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="min" value="' . $extra['min'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Maximum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="max" value="' . $extra['max'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <hr>' . "\r\n" . '  <p class="card-description">' . "\r\n" . '<ul>' . "\r\n" . '<li>' . "\r\n" . ' API callback address: <code>';
    $return .= site_url('payment/' . $method['method_get']);
    $return .= '</code>' . "\r\n" . '</li>' . "\r\n" . '</ul>' . "\r\n" . '  </p>' . "\r\n" . ' <hr>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Seller ID</label>' . "\r\n" . '  <input type="text" class="form-control" name="seller_id" value="' . $extra['seller_id'] . '">' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Private Key</label>' . "\r\n" . '  <input type="text" class="form-control" name="private_key" value="' . $extra['private_key'] . '">' . "\r\n" . ' </div>' . "\r\n" . '<div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Commission, %</label>' . "\r\n" . '  <input type="text" class="form-control" name="fee" value="' . $extra['fee'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif (($action == 'edit_paymentmethod') && ($_POST['id'] == 'payoneer')) {
    $id = $_POST['id'];
    $method = $conn->prepare('SELECT * FROM payment_methods WHERE method_get=:id ');
    $method->execute(['id' => $id]);
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extra = json_decode($method['method_extras'], true);
    $return = '<form class="form" action="' . site_url('admin/settings/payment-methods/edit/' . $id) . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Method name</label>' . "\r\n" . '  <input type="text" class="form-control" readonly value="' . $method['method_name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Visibility</label>' . "\r\n" . '<select class="form-control" name="method_type">' . "\r\n" . '  <option value="2"';

    if ($method['method_type'] == 2) {
        $return .= 'selected';
    }

    $return .= '>Active</option>' . "\r\n" . '  <option value="1"';

    if ($method['method_type'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Inactive</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Visible name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="' . $extra['name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . '<div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Email</label>' . "\r\n" . '  <input type="text" class="form-control" name="email" value="' . $extra['email'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif (($action == 'edit_paymentmethod') && ($_POST['id'] == 'mollie')) {
    $id = $_POST['id'];
    $method = $conn->prepare('SELECT * FROM payment_methods WHERE method_get=:id ');
    $method->execute(['id' => $id]);
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extra = json_decode($method['method_extras'], true);
    $return = '<form class="form" action="' . site_url('admin/settings/payment-methods/edit/' . $id) . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Method name</label>' . "\r\n" . '  <input type="text" class="form-control" readonly value="' . $method['method_name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Visibility</label>' . "\r\n" . '<select class="form-control" name="method_type">' . "\r\n" . '  <option value="2"';

    if ($method['method_type'] == 2) {
        $return .= 'selected';
    }

    $return .= '>Active</option>' . "\r\n" . '  <option value="1"';

    if ($method['method_type'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Inactive</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Visible name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="' . $extra['name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Minimum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="min" value="' . $extra['min'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Maximum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="max" value="' . $extra['max'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <hr>' . "\r\n" . '  <p class="card-description">' . "\r\n" . '<ul>' . "\r\n" . '<li>' . "\r\n" . ' API callback address: <code>';
    $return .= site_url('payment/' . $method['method_get']);
    $return .= '</code>' . "\r\n" . '</li>' . "\r\n" . '</ul>' . "\r\n" . '  </p>' . "\r\n" . ' <hr>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Live API key</label>' . "\r\n" . '  <input type="text" class="form-control" name="live_api_key" value="' . $extra['live_api_key'] . '">' . "\r\n" . ' </div>' . "\r\n" . '<div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Commission, %</label>' . "\r\n" . '  <input type="text" class="form-control" name="fee" value="' . $extra['fee'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif (($action == 'edit_paymentmethod') && ($_POST['id'] == 'paytm')) {
    $id = $_POST['id'];
    $method = $conn->prepare('SELECT * FROM payment_methods WHERE method_get=:id ');
    $method->execute(['id' => $id]);
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extra = json_decode($method['method_extras'], true);
    $return = '<form class="form" action="' . site_url('admin/settings/payment-methods/edit/' . $id) . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Method name</label>' . "\r\n" . '  <input type="text" class="form-control" readonly value="' . $method['method_name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Visibility</label>' . "\r\n" . '<select class="form-control" name="method_type">' . "\r\n" . '  <option value="2"';

    if ($method['method_type'] == 2) {
        $return .= 'selected';
    }

    $return .= '>Active</option>' . "\r\n" . '  <option value="1"';

    if ($method['method_type'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Inactive</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Visible name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="' . $extra['name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Minimum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="min" value="' . $extra['min'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Maximum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="max" value="' . $extra['max'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <hr>' . "\r\n" . '  <p class="card-description">' . "\r\n" . '<ul>' . "\r\n" . '<li>' . "\r\n" . ' API callback address: <code>';
    $return .= site_url('payment/' . $method['method_get']);
    $return .= '</code>' . "\r\n" . '</li>' . "\r\n" . '</ul>' . "\r\n" . '  </p>' . "\r\n" . ' <hr>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Merchant Key</label>' . "\r\n" . '  <input type="text" class="form-control" name="merchant_key" value="' . $extra['merchant_key'] . '">' . "\r\n" . ' </div>' . "\r\n" . '<div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Merchant MID</label>' . "\r\n" . '  <input type="text" class="form-control" name="merchant_mid" value="' . $extra['merchant_mid'] . '">' . "\r\n" . ' </div>' . "\r\n" . '<div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Merchant Website</label>' . "\r\n" . '  <input type="text" class="form-control" name="merchant_website" value="' . $extra['merchant_website'] . '">' . "\r\n" . ' </div>' . "\r\n" . '<div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Commission, %</label>' . "\r\n" . '  <input type="text" class="form-control" name="fee" value="' . $extra['fee'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif (($action == 'edit_paymentmethod') && ($_POST['id'] == 'paytmqr')) {
    $id = $_POST['id'];
    $method = $conn->prepare('SELECT * FROM payment_methods WHERE method_get=:id ');
    $method->execute(['id' => $id]);
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extra = json_decode($method['method_extras'], true);
    $return = '<form class="form" action="' . site_url('admin/settings/payment-methods/edit/' . $id) . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Method name</label>' . "\r\n" . '  <input type="text" class="form-control" readonly value="' . $method['method_name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Visibility</label>' . "\r\n" . '<select class="form-control" name="method_type">' . "\r\n" . '  <option value="2"';

    if ($method['method_type'] == 2) {
        $return .= 'selected';
    }

    $return .= '>Active</option>' . "\r\n" . '  <option value="1"';

    if ($method['method_type'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Inactive</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Visible name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="' . $extra['name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Minimum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="min" value="' . $extra['min'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Maximum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="max" value="' . $extra['max'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <hr>' . "\r\n" . '  <p class="card-description">' . "\r\n" . '<ul>' . "\r\n" . '<li>' . "\r\n" . ' API callback address: <code>';
    $return .= site_url('payment/' . $method['method_get']);
    $return .= '</code>' . "\r\n" . '</li>' . "\r\n" . '</ul>' . "\r\n" . '  </p>' . "\r\n" . ' <hr>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Paytm QR Image Link</label>' . "\r\n" . '  <input type="text" class="form-control" name="merchant_key" value="' . $extra['merchant_key'] . '">' . "\r\n" . ' </div>' . "\r\n" . '<div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Merchant MID</label>' . "\r\n" . '  <input type="text" class="form-control" name="merchant_mid" value="' . $extra['merchant_mid'] . '">' . "\r\n" . ' </div>' . "\r\n" . '<div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Merchant Website</label>' . "\r\n" . '  <input type="text" class="form-control" name="merchant_website" value="' . $extra['merchant_website'] . '">' . "\r\n" . ' </div>' . "\r\n" . '<div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Commission, %</label>' . "\r\n" . '  <input type="text" class="form-control" name="fee" value="' . $extra['fee'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);

} elseif (($action == 'edit_paymentmethod') && ($_POST['id'] == 'cashmaal')) {
    $id = $_POST['id'];
    $method = $conn->prepare('SELECT * FROM payment_methods WHERE method_get=:id ');
    $method->execute(['id' => $id]);
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extra = json_decode($method['method_extras'], true);
    $return = '<form class="form" action="' . site_url('admin/settings/payment-methods/edit/' . $id) . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Method name</label>' . "\r\n" . '  <input type="text" class="form-control" readonly value="' . $method['method_name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Visibility</label>' . "\r\n" . '<select class="form-control" name="method_type">' . "\r\n" . '  <option value="2"';

    if ($method['method_type'] == 2) {
        $return .= 'selected';
    }

    $return .= '>Active</option>' . "\r\n" . '  <option value="1"';

    if ($method['method_type'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Inactive</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Visible name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="' . $extra['name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Minimum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="min" value="' . $extra['min'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Maximum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="max" value="' . $extra['max'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <hr>' . "\r\n" . '  <p class="card-description">' . "\r\n" . '<ul>' . "\r\n" . '<li>' . "\r\n" . ' API callback address: <code>';
    $return .= site_url('payment/' . $method['method_get']);
    $return .= '</code>' . "\r\n" . '</li>' . "\r\n" . '</ul>' . "\r\n" . '  </p>' . "\r\n" . ' <hr>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Merchant Key</label>' . "\r\n" . '  <input type="text" class="form-control" name="merchant_key" value="' . $extra['web_id'] . '">' . "\r\n" . ' </div>' . "\r\n" . '<div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Commission, %</label>' . "\r\n" . '  <input type="text" class="form-control" name="fee" value="' . $extra['fee'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);

} elseif (($action == 'edit_paymentmethod') && ($_POST['id'] == 'perfectmoney')) {
    $id = $_POST['id'];
    $method = $conn->prepare('SELECT * FROM payment_methods WHERE method_get=:id ');
    $method->execute(['id' => $id]);
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extra = json_decode($method['method_extras'], true);
    $return = '<form class="form" action="' . site_url('admin/settings/payment-methods/edit/' . $id) . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Method name</label>' . "\r\n" . '  <input type="text" class="form-control" readonly value="' . $method['method_name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Visibility</label>' . "\r\n" . '<select class="form-control" name="method_type">' . "\r\n" . '  <option value="2"';

    if ($method['method_type'] == 2) {
        $return .= 'selected';
    }

    $return .= '>Active</option>' . "\r\n" . '  <option value="1"';

    if ($method['method_type'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Inactive</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Visible name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="' . $extra['name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Minimum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="min" value="' . $extra['min'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Maximum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="max" value="' . $extra['max'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <hr>' . "\r\n" . '  <p class="card-description">' . "\r\n" . '<ul>' . "\r\n" . '<li>' . "\r\n" . ' API callback address: <code>';
    $return .= site_url('payment/' . $method['method_get']);
    $return .= '</code>' . "\r\n" . '</li>' . "\r\n" . '</ul>' . "\r\n" . '  </p>' . "\r\n" . ' <hr>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Alternate Passphrase</label>' . "\r\n" . '  <input type="text" class="form-control" name="passphrase" value="' . $extra['passphrase'] . '">' . "\r\n" . ' </div>' . "\r\n" . '<div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">USD ID</label>' . "\r\n" . '  <input type="text" class="form-control" name="usd" value="' . $extra['usd'] . '">' . "\r\n" . ' </div>' . "\r\n" . '<div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Merchant Website Name</label>' . "\r\n" . '  <input type="text" class="form-control" name="merchant_website" value="' . $extra['merchant_website'] . '">' . "\r\n" . ' </div>' . "\r\n" . '<div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Commission, %</label>' . "\r\n" . '  <input type="text" class="form-control" name="fee" value="' . $extra['fee'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} 

elseif (($action == 'edit_paymentmethod') && ($_POST['id'] == 'razorpay')) {
    $id = $_POST['id'];
    $method = $conn->prepare('SELECT * FROM payment_methods WHERE method_get=:id ');
    $method->execute(['id' => $id]);
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extra = json_decode($method['method_extras'], true);
    $return = '<form class="form" action="' . site_url('admin/settings/payment-methods/edit/' . $id) . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Method name</label>' . "\r\n" . '  <input type="text" class="form-control" readonly value="' . $method['method_name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Visibility</label>' . "\r\n" . '<select class="form-control" name="method_type">' . "\r\n" . '  <option value="2"';

    if ($method['method_type'] == 2) {
        $return .= 'selected';
    }

    $return .= '>Active</option>' . "\r\n" . '  <option value="1"';

    if ($method['method_type'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Inactive</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Visible name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="' . $extra['name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Minimum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="min" value="' . $extra['min'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Maximum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="max" value="' . $extra['max'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <hr>' . "\r\n" . '  <p class="card-description">' . "\r\n" . '<ul>' . "\r\n" . '<li>' . "\r\n" . ' API callback address: <code>';
    $return .= site_url('payment/' . $method['method_get']);
    $return .= '</code>' . "\r\n" . '</li>' . "\r\n" . '</ul>' . "\r\n" . '  </p>' . "\r\n" . ' <hr>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Public Key</label>' . "\r\n" . '  <input type="text" class="form-control" name="public_key" value="' . $extra['public_key'] . '">' . "\r\n" . ' </div>' . "\r\n" . '<div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Key Secret</label>' . "\r\n" . '  <input type="text" class="form-control" name="key_secret" value="' . $extra['key_secret'] . '">' . "\r\n" . ' </div>' . "\r\n" . '<div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Merchant Website</label>' . "\r\n" . '  <input type="text" class="form-control" name="merchant_website" value="' . $extra['merchant_website'] . '">' . "\r\n" . ' </div>' . "\r\n" . '<div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Commission, %</label>' . "\r\n" . '  <input type="text" class="form-control" name="fee" value="' . $extra['fee'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} 
elseif (($action == 'edit_paymentmethod') && ($_POST['id'] == 'shopier')) {
    $id = $_POST['id'];
    $method = $conn->prepare('SELECT * FROM payment_methods WHERE method_get=:id ');
    $method->execute(['id' => $id]);
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extra = json_decode($method['method_extras'], true);
    $return = '<form class="form" action="' . site_url('admin/settings/payment-methods/edit/' . $id) . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Method name</label>' . "\r\n" . '  <input type="text" class="form-control" readonly value="' . $method['method_name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Visibility</label>' . "\r\n" . '<select class="form-control" name="method_type">' . "\r\n" . '  <option value="2"';

    if ($method['method_type'] == 2) {
        $return .= 'selected';
    }

    $return .= '>Active</option>' . "\r\n" . '  <option value="1"';

    if ($method['method_type'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Inactive</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Visible name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="' . $extra['name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Minimum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="min" value="' . $extra['min'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Maximum Payment</label>' . "\r\n" . '  <input type="text" class="form-control" name="max" value="' . $extra['max'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <hr>' . "\r\n" . '  <p class="card-description">' . "\r\n" . '<ul>' . "\r\n" . '<li>' . "\r\n" . ' API callback address: <code>';
    $return .= site_url('payment/' . $method['method_get']);
    $return .= '</code>' . "\r\n" . '</li>' . "\r\n" . '</ul>' . "\r\n" . '  </p>' . "\r\n" . ' <hr>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">apiKey</label>' . "\r\n" . '  <input type="text" class="form-control" name="apiKey" value="' . $extra['apiKey'] . '">' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">apiSecret</label>' . "\r\n" . '  <input type="text" class="form-control" name="apiSecret" value="' . $extra['apiSecret'] . '">' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group">' . "\r\n" . ' <label>Callbacks</label>' . "\r\n" . '  <select class="form-control" name="website_index">' . "\r\n" . ' <option value="1"';

    if ($extra['website_index'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Callback URL (1)</option>' . "\r\n" . ' <option value="2"';

    if ($extra['website_index'] == 2) {
        $return .= 'selected';
    }

    $return .= '>Callback URL (2)</option>' . "\r\n" . ' <option value="3"';

    if ($extra['website_index'] == 3) {
        $return .= 'selected';
    }

    $return .= '>Callback URL (3)</option>' . "\r\n" . ' <option value="4"';

    if ($extra['website_index'] == 4) {
        $return .= 'selected';
    }

    $return .= '>Callback URL (4)</option>' . "\r\n" . ' <option value="5"';

    if ($extra['website_index'] == 5) {
        $return .= 'selected';
    }

    $return .= '>Callback URL (5)</option>' . "\r\n" . '</select>' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group">' . "\r\n" . ' <label>Processing fee (0,49 TL)</label>' . "\r\n" . '  <select class="form-control" name="processing_fee">' . "\r\n" . ' <option value="1"';

    if ($extra['processing_fee'] == 1) {
        $return .= 'selected';
    }

    $return .= '>User should pay this commission</option>' . "\r\n" . ' <option value="0"';

    if ($extra['processing_fee'] == 0) {
        $return .= 'selected';
    }

    $return .= '>User should not pay this commission</option>' . "\r\n" . '</select>' . "\r\n" . ' </div>' . "\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Commission, %</label>' . "\r\n" . '  <input type="text" class="form-control" name="fee" value="' . $extra['fee'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif (($action == 'edit_paymentmethod') && ($_POST['id'] == 'havale-eft')) {
    $id = $_POST['id'];
    $method = $conn->prepare('SELECT * FROM payment_methods WHERE method_get=:id ');
    $method->execute(['id' => $id]);
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extra = json_decode($method['method_extras'], true);
    $return = '<form class="form" action="' . site_url('admin/settings/payment-methods/edit/' . $id) . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Method name</label>' . "\r\n" . '  <input type="text" class="form-control" readonly value="' . $method['method_name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Visibility</label>' . "\r\n" . '<select class="form-control" name="method_type">' . "\r\n" . '  <option value="2"';

    if ($method['method_type'] == 2) {
        $return .= 'selected';
    }

    $return .= '>Active</option>' . "\r\n" . '  <option value="1"';

    if ($method['method_type'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Inactive</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Visible name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="' . $extra['name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'new_bankaccount') {
    $return = '<form class="form" action="' . site_url('admin/settings/bank-accounts/new') . '" method="post" data-xhr="true">' . "\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group">Bank name</label>' . "\r\n" . '  <input type="text" name="bank_name" class="form-control" value="">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group">Holder name</label>' . "\r\n" . '  <input type="text" name="bank_alici" class="form-control" value="">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group">Branch number</label>' . "\r\n" . '  <input type="text" name="bank_sube" class="form-control" value="">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group">Account no</label>' . "\r\n" . '  <input type="text" name="bank_hesap" class="form-control" value="">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group">IBAN</label>' . "\r\n" . '  <input type="text" name="bank_iban" class="form-control" value="">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Add new bank account</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'edit_bankaccount') {
    $id = $_POST['id'];
    $bank = $conn->prepare('SELECT * FROM bank_accounts WHERE id=:id ');
    $bank->execute(['id' => $id]);
    $bank = $bank->fetch(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="' . site_url('admin/settings/bank-accounts/edit/' . $id) . '" method="post" data-xhr="true">' . "\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group">Bank name</label>' . "\r\n" . '  <input type="text" name="bank_name" class="form-control" value="' . $bank['bank_name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group">Holder name</label>' . "\r\n" . '  <input type="text" name="bank_alici" class="form-control" value="' . $bank['bank_alici'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group">Branch number</label>' . "\r\n" . '  <input type="text" name="bank_sube" class="form-control" value="' . $bank['bank_sube'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group">Account no</label>' . "\r\n" . '  <input type="text" name="bank_hesap" class="form-control" value="' . $bank['bank_hesap'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group">IBAN</label>' . "\r\n" . '  <input type="text" name="bank_iban" class="form-control" value="' . $bank['bank_iban'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . '<div class="modal-footer">' . "\r\n" . ' <a id="delete-row" data-url="' . site_url('admin/settings/bank-accounts/delete/' . $bank['id']) . '" class="btn btn-danger pull-left">Delete</a>' . "\r\n" . ' <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . ' <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . '</div>' . "\r\n" . '</form>' . "\r\n" . '<script src="theme/admin/admin/sweetalert.min.js"></script>' . "\r\n" . '<script>' . "\r\n" . '$("#delete-row").on("click", function() {' . "\r\n" . ' var action = $(this).attr("data-url");' . "\r\n" . ' swal({' . "\r\n" . '  title: "Are you sure to delete?",' . "\r\n" . '  text: "If you confirm this content will be deleted, it may not be possible to bring it back.",' . "\r\n" . '  icon: "warning",' . "\r\n" . '  buttons: true,' . "\r\n" . '  dangerMode: true,' . "\r\n" . '  buttons: ["Cancel", "Yes, I\'m sure!"],' . "\r\n" . ' })' . "\r\n" . ' .then((willDelete) => {' . "\r\n" . '  if (willDelete) {' . "\r\n" . '$.ajax({' . "\r\n" . 'url: action,' . "\r\n" . 'type: "GET",' . "\r\n" . 'dataType: "json",' . "\r\n" . 'cache: false,' . "\r\n" . 'contentType: false,' . "\r\n" . 'processData: false' . "\r\n" . '})' . "\r\n" . '.done(function(result){' . "\r\n" . 'if( result.s == "error" ){' . "\r\n" . ' var heading = "Fail";' . "\r\n" . '}else{' . "\r\n" . ' var heading = "Successful";' . "\r\n" . '}' . "\r\n" . ' $.toast({' . "\r\n" . 'heading: heading,' . "\r\n" . 'text: result.m,' . "\r\n" . 'icon: result.s,' . "\r\n" . 'loader: true,' . "\r\n" . 'loaderBg: "#9EC600"' . "\r\n" . ' });' . "\r\n" . ' if (result.r!=null) {' . "\r\n" . '  if( result.time ==null ){ result.time = 3; }' . "\r\n" . '  setTimeout(function(){' . "\r\n" . 'window.location.href = result.r;' . "\r\n" . '  },result.time*1000);' . "\r\n" . ' }' . "\r\n" . '})' . "\r\n" . '.fail(function(){' . "\r\n" . '$.toast({' . "\r\n" . '  heading: "Fail",' . "\r\n" . '  text: "Request failed",' . "\r\n" . '  icon: "error",' . "\r\n" . '  loader: true,' . "\r\n" . '  loaderBg: "#9EC600"' . "\r\n" . '});' . "\r\n" . '});' . "\r\n" . '/* Content deletion approved */' . "\r\n" . '  } else {' . "\r\n" . '$.toast({' . "\r\n" . ' heading: "Fail",' . "\r\n" . ' text: "Request for deletion denied",' . "\r\n" . ' icon: "error",' . "\r\n" . ' loader: true,' . "\r\n" . ' loaderBg: "#9EC600"' . "\r\n" . '});' . "\r\n" . '  }' . "\r\n" . ' });' . "\r\n" . '});' . "\r\n" . '</script>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'new_paymentbonus') {
    $methodList = $conn->prepare('SELECT * FROM payment_methods WHERE id!=\'6\' ');
    $methodList->execute([]);
    $methodList = $methodList->fetchAll(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="' . site_url('admin/settings/payment-bonuses/new') . '" method="post" data-xhr="true">' . "\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . ' <label>Method</label>' . "\r\n" . '  <select class="form-control" name="method_type">';

    foreach ($methodList as $method) {
        $return .= '<option value="' . $method['id'] . '">' . $method['method_name'] . '</option>';
    }

    $return .= '</select>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group">Bonus amount (%)</label>' . "\r\n" . '  <input type="text" name="amount" class="form-control" value="">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group">Over ('.$settings["currency"].')</label>' . "\r\n" . '  <input type="text" name="from" class="form-control" value="">' . "\r\n" . ' </div>' . "\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Add new bonus</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'edit_paymentbonus') {
    $id = $_POST['id'];
    $bonus = $conn->prepare('SELECT * FROM payments_bonus WHERE bonus_id=:id ');
    $bonus->execute(['id' => $id]);
    $bonus = $bonus->fetch(PDO::FETCH_ASSOC);
    $methodList = $conn->prepare('SELECT * FROM payment_methods WHERE id!=\'6\' ');
    $methodList->execute([]);
    $methodList = $methodList->fetchAll(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="' . site_url('admin/settings/payment-bonuses/edit/' . $id) . '" method="post" data-xhr="true">' . "\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . ' <label>Method</label>' . "\r\n" . '  <select class="form-control" name="method_type">';

    foreach ($methodList as $method) {
        $return .= '<option value="' . $method['id'] . '"';

        if ($bonus['bonus_method'] == $method['id']) {
            $return .= 'selected';
        }

        $return .= '>' . $method['method_name'] . '</option>';
    }

    $return .= '</select>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group">Bonus amount (%)</label>' . "\r\n" . '  <input type="text" name="amount" class="form-control" value="' . $bonus['bonus_amount'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group">Over ('.$settings["currency"].')</label>' . "\r\n" . '  <input type="text" name="from" class="form-control" value="' . $bonus['bonus_from'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <a id="delete-row" data-url="' . site_url('admin/settings/payment-bonuses/delete/' . $bonus['bonus_id']) . '" class="btn btn-danger pull-left">Delete Bonus</a>' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update Bonus</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>' . "\r\n" . ' <script src="theme/admin/admin/sweetalert.min.js"></script>' . "\r\n" . ' <script>' . "\r\n" . ' $("#delete-row").on("click", function() {' . "\r\n" . '  var action = $(this).attr("data-url");' . "\r\n" . '  swal({' . "\r\n" . 'title: "Are you sure to delete?",' . "\r\n" . 'text: "If you confirm this content will be deleted, it may not be possible to bring it back.",' . "\r\n" . 'icon: "warning",' . "\r\n" . 'buttons: true,' . "\r\n" . 'dangerMode: true,' . "\r\n" . 'buttons: ["Cancel", "Yes, I\'m Sure!"],' . "\r\n" . '  })' . "\r\n" . '  .then((willDelete) => {' . "\r\n" . 'if (willDelete) {' . "\r\n" . '$.ajax({' . "\r\n" . ' url: action,' . "\r\n" . ' type: "GET",' . "\r\n" . ' dataType: "json",' . "\r\n" . ' cache: false,' . "\r\n" . ' contentType: false,' . "\r\n" . ' processData: false' . "\r\n" . '})' . "\r\n" . '.done(function(result){' . "\r\n" . ' if( result.s == "error" ){' . "\r\n" . '  var heading = "Fail";' . "\r\n" . ' }else{' . "\r\n" . '  var heading = "Successful";' . "\r\n" . ' }' . "\r\n" . '  $.toast({' . "\r\n" . 'heading: heading,' . "\r\n" . 'text: result.m,' . "\r\n" . 'icon: result.s,' . "\r\n" . 'loader: true,' . "\r\n" . 'loaderBg: "#9EC600"' . "\r\n" . '  });' . "\r\n" . '  if (result.r!=null) {' . "\r\n" . 'if( result.time ==null ){ result.time = 3; }' . "\r\n" . 'setTimeout(function(){' . "\r\n" . 'window.location.href = result.r;' . "\r\n" . '},result.time*1000);' . "\r\n" . '  }' . "\r\n" . '})' . "\r\n" . '.fail(function(){' . "\r\n" . ' $.toast({' . "\r\n" . 'heading: "Fail",' . "\r\n" . 'text: "Request failed",' . "\r\n" . 'icon: "error",' . "\r\n" . 'loader: true,' . "\r\n" . 'loaderBg: "#9EC600"' . "\r\n" . ' });' . "\r\n" . '});' . "\r\n" . '/* Content deletion approved */' . "\r\n" . '} else {' . "\r\n" . '$.toast({' . "\r\n" . '  heading: "Fail",' . "\r\n" . '  text: "Request for deletion denied",' . "\r\n" . '  icon: "error",' . "\r\n" . '  loader: true,' . "\r\n" . '  loaderBg: "#9EC600"' . "\r\n" . '});' . "\r\n" . '}' . "\r\n" . '  });' . "\r\n" . ' });' . "\r\n" . ' </script>' . "\r\n" . ' ';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'new_provider') {
    $return = '<form class="form" action="' . site_url('admin/settings/providers/new') . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Provider Name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Provider API type</label>' . "\r\n" . '<select class="form-control" name="type">' . "\r\n" . '  <option value="1">Standard</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">API URL</label>' . "\r\n" . '  <input type="text" class="form-control" name="url" value="">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">API Key</label>' . "\r\n" . '  <input type="text" class="form-control" name="apikey" value="">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Balance Limit</label>' . "\r\n" . '  <input type="text" class="form-control" name="limit" value="">' . "\r\n" . ' </div>' . "\r\n\r\n" . '<div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Provider Currency</label>' . "\r\n" . '  <select type="text" class="form-control" name="currency">' . "\r\n" . ' <option value="INR">INR</option> ' . "\r\n" . ' <option value="USD">USD</option> ' . "\r\n" . ' </select> ' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <hr>' . "\r\n" . '  <p class="card-description">' . "\r\n" . '<ul>' . "\r\n" . '<li>' . "\r\n" . ' Balance Limit: <code>You will receive a notification if your balance falls below this amount..</code>' . "\r\n" . '</li>' . "\r\n" . '</ul>' . "\r\n" . '  </p>' . "\r\n" . ' <hr>' . "\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Add new provider</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'edit_provider') {
    $id = $_POST['id'];
    $provider = $conn->prepare('SELECT * FROM service_api WHERE id=:id ');
    $provider->execute(['id' => $id]);
    $provider = $provider->fetch(PDO::FETCH_ASSOC);
    if ($provider['currency'] == "USD") {
        $currency_usd = 'selected';
    }else{
        $currency_inr = 'selected';
    }
    $return = '<form class="form" action="' . site_url('admin/settings/providers/edit/' . $id) . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Provider Name</label>' . "\r\n" . '  <input type="text" class="form-control" name="name" value="' . $provider['api_name'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Provider API type</label>' . "\r\n" . '<select class="form-control" name="type">' . "\r\n" . '  <option value="1"';


    if ($provider['api_type'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Standart</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">API URL</label>' . "\r\n" . '  <input type="text" class="form-control" name="url" value="' . $provider['api_url'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">API Key</label>' . "\r\n" . '  <input type="text" class="form-control" name="apikey" value="' . $provider['api_key'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Balance Limit</label>' . "\r\n" . '  <input type="text" class="form-control" name="limit" value="' . $provider['api_limit'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n" . '<div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Provider Currency</label>' . "\r\n" . '  <select type="text" class="form-control" name="currency">' . "\r\n" . ' <option "'. $currency_inr .'" value="INR">INR</option> ' . "\r\n" . ' <option "'. $currency_usd .'" value="USD">USD</option> ' . "\r\n" . ' </select> ' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <hr>' . "\r\n" . '  <p class="card-description">' . "\r\n" . '<ul>' . "\r\n" . '<li>' . "\r\n" . ' Balance Limit: <code>You will receive a notification if your balance falls below this amount..</code>' . "\r\n" . '</li>' . "\r\n" . '</ul>' . "\r\n" . '  </p>' . "\r\n" . ' <hr>' . "\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Edit Provider</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'export_user') {
    $return = '<form class="form" action="' . site_url('admin/clients/export') . '" method="post">' . "\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Client Status</label>' . "\r\n" . '<select class="form-control" name="client_status">' . "\r\n" . '  <option value="all">All Clients</option>' . "\r\n" . '  <option value="1">Inactive</option>' . "\r\n" . '  <option value="2">Active</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Email Status</label>' . "\r\n" . '<select class="form-control" name="email_status">' . "\r\n" . '  <option value="all">All Clients</option>' . "\r\n" . '  <option value="1">Unverified</option>' . "\r\n" . '  <option value="2">Verified</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Type</label>' . "\r\n" . '<select class="form-control" name="format">' . "\r\n" . '  <option value="json">JSON</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Client Details</label>' . "\r\n" . '<div class="form-group">' . "\r\n" . ' <label class="checkbox-inline">' . "\r\n" . '  <input type="checkbox" class="access" name="exportcolumn[client_id]" checked value="1"> ID' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline">' . "\r\n" . '  <input type="checkbox" class="access" name="exportcolumn[email]" checked value="1"> Email' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline">' . "\r\n" . '  <input type="checkbox" class="access" name="exportcolumn[name]" checked value="1"> Name surname' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline">' . "\r\n" . '  <input type="checkbox" class="access" name="exportcolumn[username]" checked value="1"> Username' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline">' . "\r\n" . '  <input type="checkbox" class="access" name="exportcolumn[telephone]" checked value="1"> Phone' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline">' . "\r\n" . '  <input type="checkbox" class="access" name="exportcolumn[balance]" checked value="1"> Balance' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline">' . "\r\n" . '  <input type="checkbox" class="access" name="exportcolumn[spent]" checked value="1"> Spent' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline">' . "\r\n" . '  <input type="checkbox" class="access" name="exportcolumn[register_date]" checked value="1"> Register date' . "\r\n" . ' </label>' . "\r\n" . ' <label class="checkbox-inline">' . "\r\n" . '  <input type="checkbox" class="access" name="exportcolumn[login_date]" checked value="1"> Last login date' . "\r\n" . ' </label>' . "\r\n" . '</div>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Download</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'all_numbers') {
    $rows = $conn->prepare('SELECT * FROM clients');
    $rows->execute([]);
    $rows = $rows->fetchAll(PDO::FETCH_ASSOC);
    $numbers = '';
    $emails = '';

    foreach ($rows as $row) {
        if ($row['telephone']) {
            $numbers .= $row['telephone'] . "\n";
        }

        $emails .= $row['email'] . "\n";
    }

    $return = '<form>' . "\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Client phone numbers</label>' . "\r\n" . '<textarea class="form-control" rows="8" readonly>' . $numbers . '</textarea>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Client E-mail addresses</label>' . "\r\n" . '<textarea class="form-control" rows="8" readonly>' . $emails . '</textarea>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'price_user') {
    $id = $_POST['id'];
    $price = $conn->prepare('SELECT *,services.service_id as serviceid,services.service_price as price,clients_price.service_price as clientprice FROM services LEFT JOIN clients_price ON clients_price.service_id=services.service_id && clients_price.client_id=:id ');
    $price->execute(['id' => $id]);
    $price = $price->fetchAll(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="' . site_url('admin/clients/price/' . $id) . '" method="post" data-xhr="true">' . "\r\n" . '<div class="modal-body">' . "\r\n\r\n" . '<div class="services-import__body">' . "\r\n" . ' <div>' . "\r\n" . ' <div class="services-import__list-wrap services-import__list-active">' . "\r\n" . '   <div class="services-import__scroll-wrap">' . "\r\n" . '<span>' . "\r\n" . '   <div class="services-import__packages">' . "\r\n" . '<ul>';

    foreach ($price as $row) {
        $return .= '<li id="service-' . $row['serviceid'] . '">' . "\r\n" . '   <label>' . "\r\n" . '' . $row['serviceid'] . ' - ' . $row['service_name'] . "\r\n" . '<span class="services-import__packages-price-edit" >' . "\r\n" . '  <div class="services-import__packages-price-lock" data-id="servicedelete-' . $row['serviceid'] . '" data-service="' . $row['serviceid'] . '">' . "\r\n" . '   <span class="fa fa-trash"></span>' . "\r\n" . '  </div>' . "\r\n" . '  <input type="text" class="services-import__price" name="price[' . $row['serviceid'] . ']" value="' . $row['clientprice'] . '">' . "\r\n" . '  <span class="services-import__provider-price">' . $row['price'] . '</span>' . "\r\n" . '</span>' . "\r\n" . '   </label>' . "\r\n" . '  </li>';
    }

    $return .= '</ul>' . "\r\n" . '   </div>' . "\r\n" . ' </span></div>' . "\r\n" . ' </div>' . "\r\n" . ' </div>' . "\r\n" . '  </div>' . "\r\n" . '  <script>' . "\r\n\r\n" . '$(\'[data-id^="servicedelete-"]\').on("click", function() {' . "\r\n" . 'var id= $(this).attr("data-service");' . "\r\n" . '$("[name=\'price["+id+"]\']").val("");' . "\r\n" . '//$("ul > li#service-"+id).remove();' . "\r\n" . '});' . "\r\n\r\n" . '  </script>' . "\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'order_errors') {
    $id = $_POST['id'];
    $row = $conn->prepare('SELECT * FROM orders WHERE order_id=:id ');
    $row->execute(['id' => $id]);
    $row = $row->fetch(PDO::FETCH_ASSOC);
    $errors = json_decode($row['order_error']);
    $return = '<form>' . "\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Information from the provider</label>' . "\r\n" . '<textarea class="form-control" rows="8" readonly>';
    $return .= print_r($errors, true);
    $return .= '</textarea>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'order_details') {
    $id = $_POST['id'];
    $row = $conn->prepare('SELECT * FROM orders WHERE order_id=:id ');
    $row->execute(['id' => $id]);
    $row = $row->fetch(PDO::FETCH_ASSOC);
    $detail = json_decode($row['order_detail']);
    $return = '<form>' . "\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Information from the provider</label>' . "\r\n" . '<textarea class="form-control" rows="8" readonly>';
    $return .= print_r($detail, true);
    $return .= '</textarea>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Order ID</label>' . "\r\n" . '<input class="form-control" value="' . $row['api_orderid'] . '" readonly>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Last update</label>' . "\r\n" . '<input class="form-control" value="' . $row['last_check'] . '" readonly>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'order_orderurl') {
    $id = $_POST['id'];
    $row = $conn->prepare('SELECT * FROM orders WHERE order_id=:id ');
    $row->execute(['id' => $id]);
    $row = $row->fetch(PDO::FETCH_ASSOC);
    $detail = json_decode($row['order_detail']);
    $return = '<form class="form" action="' . site_url('admin/orders/set_orderurl/' . $id) . '" method="post">' . "\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Order URL</label>' . "\r\n" . '<input class="form-control" value="' . $row['order_url'] . '" name="url">' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'order_startcount') {
    $id = $_POST['id'];
    $row = $conn->prepare('SELECT * FROM orders WHERE order_id=:id ');
    $row->execute(['id' => $id]);
    $row = $row->fetch(PDO::FETCH_ASSOC);
    $detail = json_decode($row['order_detail']);
    $return = '<form class="form" action="' . site_url('admin/orders/set_startcount/' . $id) . '" method="post">' . "\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Start count</label>' . "\r\n" . '<input class="form-control" value="' . $row['order_start'] . '" name="start">' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'order_partial') {
    $id = $_POST['id'];
    $row = $conn->prepare('SELECT * FROM orders WHERE order_id=:id ');
    $row->execute(['id' => $id]);
    $row = $row->fetch(PDO::FETCH_ASSOC);
    $detail = json_decode($row['order_detail']);
    $return = '<form class="form" action="' . site_url('admin/orders/set_partial/' . $id) . '" method="post" data-xhr="true">' . "\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Remaining amount</label>' . "\r\n" . '<input class="form-control" name="remains">' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'subscriptions_expiry') {
    $id = $_POST['id'];
    $row = $conn->prepare('SELECT * FROM orders WHERE order_id=:id ');
    $row->execute(['id' => $id]);
    $row = $row->fetch(PDO::FETCH_ASSOC);
    $detail = json_decode($row['order_detail']);
    $return = '<form class="form" action="' . site_url('admin/subscriptions/set_expiry/' . $id) . '" method="post">' . "\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Start count</label>' . "\r\n" . '<input class="form-control datetime" value="';

    if ($row['subscriptions_expiry'] != '1970-01-01') {
        $return .= date('d/m/Y', strtotime($row['subscriptions_expiry']));
    }

    $return .= '" name="expiry">' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>' . "\r\n" . ' <link rel="stylesheet" type="text/css" href="' . site_url('theme/admin/') . 'datepicker/css/bootstrap-datepicker3.min.css">' . "\r\n" . ' <script type="text/javascript" src="' . site_url('theme/admin/') . 'datepicker/js/bootstrap-datepicker.min.js"></script>' . "\r\n" . ' <script type="text/javascript" src="' . site_url('theme/admin/') . 'datepicker/locales/bootstrap-datepicker.tr.min.js"></script>' . "\r\n" . ' ';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'payment_bankedit') {
    $id = $_POST['id'];
    $payment = $conn->prepare('SELECT * FROM payments INNER JOIN bank_accounts ON bank_accounts.id=payments.payment_bank INNER JOIN clients ON clients.client_id=payments.client_id WHERE payments.payment_id=:id');
    $payment->execute(['id' => $id]);
    $payment = $payment->fetch(PDO::FETCH_ASSOC);
    $bank = $conn->prepare('SELECT * FROM bank_accounts ');
    $bank->execute();
    $bank = $bank->fetchAll(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="' . site_url('admin/payments/edit-bank/' . $id) . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Payment bank</label>' . "\r\n" . '<select class="form-control" name="bank">';

    foreach ($bank as $banka) {
        $return .= '<option value="' . $banka['id'] . '"';

        if ($payment['payment_bank'] == $banka['id']) {
            $return .= 'selected';
        }

        $return .= '>' . $banka['bank_name'] . '</option>';
    }

    $return .= '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Payment status</label>' . "\r\n" . '<select class="form-control" ';

    if ($payment['payment_status'] == 3) {
        $return .= 'disabled';
    }

    $return .= ' name="status">' . "\r\n" . '  <option value="1"';

    if ($payment['payment_status'] == 1) {
        $return .= 'selected';
    }

    $return .= '>Pending</option>' . "\r\n" . '  <option value="2"';

    if ($payment['payment_status'] == 2) {
        $return .= 'selected';
    }

    $return .= '>Cancel</option>' . "\r\n" . '  <option value="3"';

    if ($payment['payment_status'] == 3) {
        $return .= 'selected';
    }

    $return .= '>Approved</option>' . "\r\n" . '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">NOTE</label>' . "\r\n" . '  <input type="text" class="form-control" name="note" value="' . $payment['payment_note'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'payment_banknew') {
    $bank = $conn->prepare('SELECT * FROM bank_accounts ');
    $bank->execute();
    $bank = $bank->fetchAll(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="' . site_url('admin/payments/new-bank/') . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Username</label>' . "\r\n" . '  <input type="text" class="form-control" name="username" value="">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Amount</label>' . "\r\n" . '  <input type="text" class="form-control" name="amount" value="">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Payment bank</label>' . "\r\n" . '<select class="form-control" name="bank">';

    foreach ($bank as $banka) {
        $return .= '<option value="' . $banka['id'] . '">' . $banka['bank_name'] . '</option>';
    }

    $return .= '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">NOTE</label>' . "\r\n" . '  <input type="text" class="form-control" name="note" value="">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Add Payment</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'payment_edit') {
    $id = $_POST['id'];
    $payment = $conn->prepare('SELECT * FROM payments INNER JOIN clients ON clients.client_id=payments.client_id WHERE payments.payment_id=:id');
    $payment->execute(['id' => $id]);
    $payment = $payment->fetch(PDO::FETCH_ASSOC);
    $methods = $conn->prepare('SELECT * FROM payment_methods WHERE id!=\'6\' ');
    $methods->execute();
    $methods = $methods->fetchAll(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="' . site_url('admin/payments/edit-online/' . $id) . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Payment Method</label>' . "\r\n" . '<select class="form-control" name="method">';

    foreach ($methods as $method) {
        $return .= '<option value="' . $method['id'] . '"';

        if ($payment['payment_method'] == $method['id']) {
            $return .= 'selected';
        }

        $return .= '>' . $method['method_name'] . '</option>';
    }

    $return .= '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">NOTE</label>' . "\r\n" . '  <input type="text" class="form-control" name="note" value="' . $payment['payment_note'] . '">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Update</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'payment_new') {
    $methods = $conn->prepare('SELECT * FROM payment_methods WHERE id!=\'6\' ');
    $methods->execute();
    $methods = $methods->fetchAll(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="' . site_url('admin/payments/new-online') . '" method="post" data-xhr="true">' . "\r\n\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Username</label>' . "\r\n" . '  <input type="text" class="form-control" name="username" value="">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">Amount</label>' . "\r\n" . '  <input type="text" class="form-control" name="amount" value="">' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Payment Method</label>' . "\r\n" . '<select class="form-control" name="method">';

    foreach ($methods as $method) {
        $return .= '<option value="' . $method['id'] . '">' . $method['method_name'] . '</option>';
    }

    $return .= '</select>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="form-group">' . "\r\n" . '  <label class="form-group__service-name">NOTE</label>' . "\r\n" . '  <input type="text" class="form-control" name="note" value="">' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="submit" class="btn btn-primary">Add Payment</button>' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
} elseif ($action == 'payment_detail') {
    $id = $_POST['id'];
    $row = $conn->prepare('SELECT * FROM payments WHERE payment_id=:id ');
    $row->execute(['id' => $id]);
    $row = $row->fetch(PDO::FETCH_ASSOC);
    $detail = json_decode($row['payment_extra']);
    $return = '<form>' . "\r\n" . '<div class="modal-body">' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Payment Info</label>' . "\r\n" . '<textarea class="form-control" rows="8" readonly>';
    $return .= print_r($detail, true);
    $return .= '</textarea>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n" . ' <div class="service-mode__block">' . "\r\n" . '  <div class="form-group">' . "\r\n" . '  <label>Last update</label>' . "\r\n" . '<input class="form-control" value="' . $row['payment_update_date'] . '" readonly>' . "\r\n" . '  </div>' . "\r\n" . ' </div>' . "\r\n\r\n\r\n" . '</div>' . "\r\n\r\n" . ' <div class="modal-footer">' . "\r\n" . '  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => '']);
}
elseif($action=='yeni_kupon')
{
    $return = '<form class="form" action="' . site_url('admin/kuponlar/new') . '" method="post" data-xhr="true">' . "\r\n" . '<div class="modal-body"> <div class="form-group"><label>Name	</label><input name="kuponadi" type="text" class="form-control" required/></div><div class="form-group"><label>Peice (How Many times Coupon Will Be Used)</label><input type="number" class="form-control" name="adet" required/></div><label>Amount</label><input type="number" required class="form-control" name="tutar"/></div></div> ' . "\r\n" . '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button><button type="submit"  class="btn btn-primary">submit</button>' . "\r\n" . ' </div>' . "\r\n" . ' </form>';
    echo json_encode(['content' => $return, 'title' => 'Create Coupon Code']);   
}

