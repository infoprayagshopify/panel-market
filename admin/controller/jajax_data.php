<?php

$action = $_POST["action"];
$languages  = $conn->prepare("SELECT * FROM languages WHERE language_type=:type");
$languages->execute(array("type"=>2));
$languages  = $languages->fetchAll(PDO::FETCH_ASSOC);

  if( $action ==  "providers_list" ):
    $smmapi   = new SMMApi();
    $provider = $_POST["provider"];
    $api      = $conn->prepare("SELECT * FROM service_api WHERE id=:id");
    $api     -> execute(array("id"=>$provider ));
    $api      = $api->fetch(PDO::FETCH_ASSOC);
      if( $api["api_type"] == 3 ):
        echo '<div class="service-mode__block">
          <div class="form-group">
            <label>Servis</label>
            <input class="form-control" name="service" placeholder="Servis ID giriniz">
          </div>
        </div>';
      elseif( $api["api_type"] == 1 ):
        $services = $smmapi->action(array('key' =>$api["api_key"],'action' =>'services'),$api["api_url"]);
        echo '<div class="service-mode__block">
          <div class="form-group">
          <label>Servis</label>
            <select class="form-control" name="service">';
                foreach ($services as $service) {
                  echo '<option value="'.$service->service.'"'; if($_SESSION["data"]["service"]==$service->service): echo 'selected';endif; echo '>'.$service->service.' - '.$service->name.' - '.priceFormat($service->rate).'</option>';
                }
                echo '</select>
          </div>
        </div>';
      endif;
    unset($_SESSION["data"]);
  elseif( $action == "paymentmethod-sortable" ):
    $list = $_POST["methods"];
      foreach ($list as $method) {
        $update = $conn->prepare("UPDATE payment_methods SET method_line=:line WHERE id=:id ");
        $update-> execute(array("id"=>$method["id"],"line"=>$method["line"] ));
      }
  elseif( $action == "service-sortable" ):
    $list = $_POST["services"];
      foreach ($list as $service) {
        $id = explode("-",$service["id"]);
        $update = $conn->prepare("UPDATE services SET service_line=:line WHERE service_id=:id ");
        $update-> execute(array("id"=>$id[1],"line"=>$service["line"] ));
      }
  elseif( $action == "category-sortable" ):
    $list = $_POST["categories"];
      foreach ($list as $category) {
        $update = $conn->prepare("UPDATE categories SET category_line=:line WHERE category_id=:id ");
        $update-> execute(array("id"=>$category["id"],"line"=>$category["line"] ));
      }
  elseif( $action ==  "secret_user" ):
    $id       = $_POST["id"];
    $services = $conn->prepare("SELECT * FROM services RIGHT JOIN categories ON categories.category_id=services.category_id WHERE services.service_secret='1' || categories.category_secret='1'  ");
    $services -> execute(array("id"=>$id));
    $services = $services->fetchAll(PDO::FETCH_ASSOC);
    $grouped = array_group_by($services, 'category_id');
    $return = '<form class="form" action="'.site_url("admin/clients/export").'" method="post" data-xhr="true">
        <div class="modal-body">

        <div class="services-import__body">
               <div>
                  <div class="services-import__list-wrap services-import__list-active">
                     <div class="services-import__scroll-wrap">';
                     foreach($grouped as $category):
                       $row = ["table"=>"clients_category","where"=>["client_id"=>$id,"category_id"=>$category[0]["category_id"]]];
                        $return.='<span>
                            <div class="services-import__category">
                               <div class="services-import__category-title">
                                 <label> '; if( $category[0]["category_secret"] == 1 ): $return.='<small><i class="fa fa-lock"></i></small> <input type="checkbox"'; if( countRow($row) ): $return.='checked'; endif; $return.=' class="tiny-toggle" data-tt-palette="blue" data-url="'.site_url("admin/clients/secret_category/".$id).'" data-id="'.$category[0]["category_id"].'"> '; endif; $return.=$category[0]["category_name"].' </label>
                               </div>
                            </div>
                             <div class="services-import__packages">
                                <ul>';
                                  for($i=0;$i<count($category);$i++):
                                    $row = ["table"=>"clients_service","where"=>["client_id"=>$id,"service_id"=>$category[$i]["service_id"]]];
                                    $return.='<li id="service-'.$category[$i]["service_id"].'">
                                     <label>'; if( $category[$i]["service_secret"] == 1 ): $return.='<small><i class="fa fa-lock"></i></small> '; endif;
                                     $return.= $category[$i]["service_id"].' - '.$category[$i]["service_name"].'
                                        <span class="services-import__packages-price-edit" >';
                                        if( $category[$i]["service_secret"] == 1 ): $return.='<input type="checkbox"'; if( countRow($row) ): $return.='checked'; endif; $return.='  class="tiny-toggle" data-tt-palette="blue" data-url="'.site_url("admin/clients/secret_service/".$id).'" data-id="'.$category[$i]["service_id"].'">'; endif;
                                        $return.='</span>
                                     </label>
                                    </li>';
                                  endfor;
                                $return.='</ul>
                             </div>
                          </span>';
                        endforeach;
                      $return.='</div>
                  </div>
               </div>
            </div>
            <script src="'.site_url("public/admin/").'jquery.tinytoggle.min.js"></script>
            <link rel="stylesheet" type="text/css" href="'.site_url("public/admin/").'tinytoggle.min.css" rel="stylesheet">
            <script>
            $(".tiny-toggle").tinyToggle({
              onCheck: function() {
                var id     = $(this).attr("data-id");
                var action = $(this).attr("data-url")+"?type=on&id="+id;
                  $.ajax({
                  url:  action,
                  type: \'GET\',
                  dataType: \'json\',
                  cache: false,
                  contentType: false,
                  processData: false
                  }).done(function(result){
                    if( result == 1 ){
                      $.toast({
                          heading: "Success",
                          text: "Success",
                          icon: "success",
                          loader: true,
                          loaderBg: "#9EC600"
                      });
                    }else{
                      $.toast({
                          heading: "Failed",
                          text: "Failed",
                          icon: "error",
                          loader: true,
                          loaderBg: "#9EC600"
                      });
                    }
                  })
                  .fail(function(){
                    $.toast({
                        heading: "Failed",
                        text: "Failed",
                        icon: "error",
                        loader: true,
                        loaderBg: "#9EC600"
                    });
                  });
              },
              onUncheck: function() {
                var id     = $(this).attr("data-id");
                var action = $(this).attr("data-url")+"?type=off&id="+id;
                  $.ajax({
                  url:  action,
                  type: \'GET\',
                  dataType: \'json\',
                  cache: false,
                  contentType: false,
                  processData: false
                  }).done(function(result){
                    if( result == 1 ){
                      $.toast({
                          heading: "Success",
                          text: "Success",
                          icon: "success",
                          loader: true,
                          loaderBg: "#9EC600"
                      });
                    }else{
                      $.toast({
                          heading: "Failed",
                          text: "Failed",
                          icon: "error",
                          loader: true,
                          loaderBg: "#9EC600"
                      });
                    }
                  })
                  .fail(function(){
                    $.toast({
                        heading: "Failed",
                        text: "Failed",
                        icon: "error",
                        loader: true,
                        loaderBg: "#9EC600"
                    });
                  });
              },
            });

            </script>

        </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
        echo json_encode(["content"=>$return,"title"=>"Kullanıcıya özel servisler"]);
  elseif( $action == "new_user" ):
    $return = '<form class="form" action="'.site_url("admin/clients/new").'" method="post" data-xhr="true">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-group__service-name">Üye adı</label>
            <input type="text" class="form-control" name="name" value="">
          </div>

          <div class="form-group">
            <label>Üye E-mail</label>
            <input type="text" name="email" value="" class="form-control">
          </div>

          <div class="form-group">
            <label>Kullanıcı adı</label>
            <input type="text" name="username" class="form-control" value="">
          </div>

          <div class="form-group">
            <label>Üye Parolası</label>
            <div class="input-group">
              <input type="text" class="form-control" name="password" value="" id="user_password">
              <span class="input-group-btn">
                <button class="btn btn-default" onclick="UserPassword()" type="button">
                <span class="fa fa-random" data-toggle="tooltip" data-placement="bottom" title="" aria-hidden="true" data-original-title="Parola oluştur"></span></button>
              </span>
            </div>
          </div>

          <div class="form-group">
            <label>Üye telefon</label>
            <input type="text" name="telephone" class="form-control" value="">
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Borç durumu</label>
              <select class="form-control" id="debit" name="balance_type">
                    <option value="2">Borç yapamasın</option>
                    <option value="1">Borç yapabilsin</option>
                </select>
            </div>
          </div>

          <div class="form-group" id="debit_limit">
            <label>Ne kadar borç yapabilsin</label>
            <input type="text" name="debit_limit" class="form-control" value="">
          </div>
          
          <div class="service-mode__block" >
            <div class="form-group" style="display: none;">
            <label>SMS Onayı</label>
              <select class="form-control" name="tel_type">
                    <option value="1" selected>Onaysız</option>
                    <option value="2">Onaylı</option>
                </select>
            </div>
          </div>

          <div class="service-mode__block">
            <div class="form-group" style="display": none">
            <label>E-mail Onayı</label>
              <select class="form-control" name="email_type">
                    <option value="1" selected>Onaysız</option>
                    <option value="2">Onaylı</option>
                </select>
            </div>
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Yönetici Hesabı</label>
              <select class="form-control" name="access[admin_access]">
                    <option value="0">Hayır</option>
                    <option value="1">Evet</option>
                </select>
            </div>
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Yönetici Yetkileri</label>
              <div class="form-group col-md-12">
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[users]"  value="0"> Kullanıcılar
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[orders]"  value="0"> Siparişler
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[subscriptions]"  value="0"> Abonelikler
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[dripfeed]"  value="0"> Drip-feed
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[services]"  value="0"> Servisler
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[payments]"  value="0"> Ödemeler
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[tickets]"  value="0"> Destek sistemi
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[reports]"  value="0"> İstatistikler
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[general_settings]"  value="0"> Genel ayarlar
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[pages]"  value="0"> Sayfalar
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[payments_settings]"  value="0"> Ödeme ayarları
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[bank_accounts]"  value="0"> Banka Hesapları
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[payments_bonus]"  value="0"> Ödeme bonusları
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[alert_settings]"  value="0"> Bildirim ayarları
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[providers]"  value="0"> Servis sağlayıcıları
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[themes]"  value="0"> Tema düzenleyicisi
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[language]"  value="0"> Dil düzenleyicisi
                  </label>
                    <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[meta]"  value="0"> Meta (SEO) Ayarları
                  </label>

                        <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[twice]"  value="0"> Hızlı İşlemler</label>
                    <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[proxy]"  value="0"> Proxy</label>
                        <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[kuponlar]"  value="0"> Kuponlar</label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[admins]"  value="0"> Yönetici yetkileri
                  </label>
              </div>
            </div>
          </div>


        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Kullanıcıyı kayıt et</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>
          <script>
            var type = $("#debit").val();
            if( type == 2 ){
              $("#debit_limit").hide();
            } else{
              $("#debit_limit").show();
            }
            $("#debit").change(function(){
              var type = $(this).val();
                if( type == 2 ){
                  $("#debit_limit").hide();
                } else{
                  $("#debit_limit").show();
                }
            });
          </script>';
    echo json_encode(["content"=>$return,"title"=>"Yeni kullanıcı kaydı"]);
  elseif( $action == "edit_user" ):
    $id = $_POST["id"];
    $user   = $conn->prepare("SELECT * FROM clients WHERE client_id=:id ");
    $user ->execute(array("id"=>$id));
    $user   = $user->fetch(PDO::FETCH_ASSOC);
    $access = json_decode($user["access"],true);
    $return = '<form class="form" action="'.site_url("admin/clients/edit/".$user["username"]).'" method="post" data-xhr="true">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-group__service-name">Üye adı</label>
            <input type="text" class="form-control" name="name" value="'.$user["name"].'">
          </div>

          <div class="form-group">
            <label>Üye E-mail</label>
            <input type="text" name="email" value="'.$user["email"].'" class="form-control">
          </div>

          <div class="form-group">
            <label>Kullanıcı adı</label>
            <input type="text" name="username" class="form-control" readonly value="'.$user["username"].'">
          </div>

          <div class="form-group">
            <label>Üye telefon</label>
            <input type="text" name="telephone" class="form-control" value="'.$user["telephone"].'">
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Borç durumu</label>
              <select class="form-control" id="debit" name="balance_type">
                    <option value="2"'; if( $user["balance_type"] == 2 ): $return.='selected'; endif;  $return.='>Borç yapamasın</option>
                    <option value="1"'; if( $user["balance_type"] == 1 ): $return.='selected'; endif;  $return.='>Borç yapabilsin</option>
                </select>
            </div>
          </div>

          <div class="form-group" id="debit_limit">
            <label>Ne kadar borç yapabilsin</label>
            <input type="text" name="debit_limit" class="form-control" value="'.$user["debit_limit"].'">
          </div>

          <div class="service-mode__block">
            <div class="form-group" style="display: none;">
            <label>SMS Onayı</label>
              <select class="form-control" name="tel_type">
                    <option value="1"'; if( $user["tel_type"] == 1 ): $return.='selected'; endif;  $return.='>Onaysız</option>
                    <option value="2"'; if( $user["tel_type"] == 2 ): $return.='selected'; endif;  $return.='>Onaylı</option>
                </select>
            </div>
          </div>

          <div class="service-mode__block">
            <div class="form-group" style="display: none;">
            <label>E-mail Onayı</label>
              <select class="form-control" name="email_type">
                    <option value="1"'; if( $user["email_type"] == 1 ): $return.='selected'; endif;  $return.='>Onaysız</option>
                    <option value="2"'; if( $user["email_type"] == 2 ): $return.='selected'; endif;  $return.='>Onaylı</option>
                </select>
            </div>
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Yönetici Hesabı</label>
              <select class="form-control" name="access[admin_access]">
                    <option value="0"'; if( $access["admin_access"] == 0 ): $return.='selected'; endif;  $return.='>Hayır</option>
                    <option value="1"'; if( $access["admin_access"] == 1 ): $return.='selected'; endif;  $return.='>Evet</option>
                </select>
            </div>
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Yönetici Yetkileri</label>
              <div class="form-group col-md-12">
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[users]"'; if( $access["users"] == 1 ): $return.='checked'; endif;  $return.=' value="1"> Kullanıcılar
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[orders]"'; if( $access["orders"] == 1 ): $return.='checked'; endif;  $return.='  value="1"> Siparişler
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[subscriptions]"'; if( $access["subscriptions"] == 1 ): $return.='checked'; endif;  $return.='  value="1"> Abonelikler
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[dripfeed]"'; if( $access["dripfeed"] == 1 ): $return.='checked'; endif;  $return.='  value="1"> Drip-feed
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[services]"'; if( $access["services"] == 1 ): $return.='checked'; endif;  $return.='  value="1"> Servisler
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[payments]"'; if( $access["payments"] == 1 ): $return.='checked'; endif;  $return.='  value="1"> Ödemeler
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[tickets]"'; if( $access["tickets"] == 1 ): $return.='checked'; endif;  $return.='  value="1"> Destek sistemi
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[reports]"'; if( $access["reports"] == 1 ): $return.='checked'; endif;  $return.='  value="1"> İstatistikler
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[general_settings]"'; if( $access["general_settings"] == 1 ): $return.='checked'; endif;  $return.='  value="1"> Genel ayarlar
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[pages]"'; if( $access["pages"] == 1 ): $return.='checked'; endif;  $return.='  value="1"> Sayfalar
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[payments_settings]"'; if( $access["payments_settings"] == 1 ): $return.='checked'; endif;  $return.='  value="1"> Ödeme ayarları
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[bank_accounts]"'; if( $access["bank_accounts"] == 1 ): $return.='checked'; endif;  $return.='  value="1"> Banka Hesapları
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[payments_bonus]"'; if( $access["payments_bonus"] == 1 ): $return.='checked'; endif;  $return.='  value="1"> Ödeme bonusları
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[alert_settings]"'; if( $access["alert_settings"] == 1 ): $return.='checked'; endif;  $return.='  value="1"> Bildirim ayarları
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[providers]"'; if( $access["providers"] == 1 ): $return.='checked'; endif;  $return.='  value="1"> Servis sağlayıcıları
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[themes]"'; if( $access["themes"] == 1 ): $return.='checked'; endif;  $return.='  value="1"> Tema düzenleyicisi
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[language]"'; if( $access["language"] == 1 ): $return.='checked'; endif;  $return.='  value="1"> Dil düzenleyicisi
                  </label>
                   <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[meta]"'; if( $access["meta"] == 1 ): $return.='checked'; endif;  $return.='  value="1"> Meta (SEO) Ayarları
                  </label>

                         <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[twice]"'; if( $access["twice"] == 1 ): $return.='checked'; endif;  $return.='  value="1"> Hızlı İşlemler
                  </label>
                     <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[proxy]"'; if( $access["proxy"] == 1 ): $return.='checked'; endif;  $return.='  value="1"> Proxy</label>
                         <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[kuponlar]"'; if( $access["kuponlar"] == 1 ): $return.='checked'; endif;  $return.='  value="1"> Kuponlar
                  </label>
                  <label class="checkbox-inline col-md-3">
                    <input type="checkbox" class="access" name="access[admins]"'; if( $access["admins"] == 1 ): $return.='checked'; endif;  $return.='  value="1"> Yönetici yetkileri
                  </label>
              </div>
            </div>
          </div>


        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Kullanıcı bilgilerini güncelle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>
          <script>
            var type = $("#debit").val();
            if( type == 2 ){
              $("#debit_limit").hide();
            } else{
              $("#debit_limit").show();
            }
            $("#debit").change(function(){
              var type = $(this).val();
                if( type == 2 ){
                  $("#debit_limit").hide();
                } else{
                  $("#debit_limit").show();
                }
            });
          </script>
          ';
    echo json_encode(["content"=>$return,"title"=>"Kullanıcıyı düzenle"]);
  elseif( $action == "pass_user" ):
    $id = $_POST["id"];
    $user   = $conn->prepare("SELECT * FROM clients WHERE client_id=:id ");
    $user ->execute(array("id"=>$id));
    $user   = $user->fetch(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="'.site_url("admin/clients/pass/".$user["username"]).'" method="post" data-xhr="true">
        <div class="modal-body">

          <div class="form-group">
            <label>Üye Parolası</label>
            <div class="input-group">
              <input type="text" class="form-control" name="password" value="" id="user_password">
              <span class="input-group-btn">
                <button class="btn btn-default" onclick="UserPassword()" type="button">
                <span class="fa fa-random" data-toggle="tooltip" data-placement="bottom" title="" aria-hidden="true" data-original-title="Parola oluştur"></span></button>
              </span>
            </div>
          </div>

        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Parolayı güncelle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Parola düzenle"]);
  elseif( $action == "alert_user" ):
    $return = '<form class="form" action="'.site_url("admin/clients/alert").'" method="post" data-xhr="true">
        <div class="modal-body">

          <div class="service-mode__block">
            <div class="form-group">
            <label>Bildirim Gönderilecek Üye</label>
              <select class="form-control" id="user_type" name="user_type">
                    <option value="all">Tüm üyeler</option>
                    <option value="secret">Üyeye özel</option>
                </select>
            </div>
          </div>

          <div class="form-group" id="username">
            <label>Kullanıcı adı</label>
            <input type="text" name="username" class="form-control" value="">
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Bildirim Tipi</label>
              <select class="form-control" id="alert_type" name="alert_type">
                    <option value="email">E-mail</option>
                    <option value="sms">SMS</option>
                </select>
            </div>
          </div>

          <div id="email">
            <div class="form-group">
              <label>E-mail Başlığı</label>
              <input type="text" name="subject" class="form-control" value="">
            </div>
          </div>

          <div class="form-group" id="username">
            <label>Bildirim Mesajı</label>
            <textarea type="text" name="message" class="form-control" rows="5"></textarea>
          </div>



        </div>
        <script type="text/javascript">
          $("#username").hide();
          $("#user_type").change(function(){
            var type = $(this).val();
            if( type == "secret" ){
              $("#username").show();
            } else{
              $("#username").hide();
            }
          });
          $("#alert_type").change(function(){
            var type = $(this).val();
            if( type == "email" ){
              $("#email").show();
            } else{
              $("#email").hide();
            }
          });
        </script>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Kullanıcılara bildiri geç</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>

          ';
    echo json_encode(["content"=>$return,"title"=>"Kullanıcılara bildirim"]);
  elseif( $action == "new_service" ):
    $categories = $conn->prepare("SELECT * FROM categories ORDER BY category_line ");
    $categories->execute(array());
    $categories = $categories->fetchAll(PDO::FETCH_ASSOC);
    $providers  = $conn->prepare("SELECT * FROM service_api");
    $providers->execute(array());
    $providers  = $providers->fetchAll(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="'.site_url("admin/services/new-service").'" method="post" data-xhr="true">
        <div class="modal-body">';

        if( count($languages) > 1 ):
          $translationList = '<a class="other_services"> Çeviriler ('.(count($languages)-1).') </a>';
        else:
          $translationList  = '';
        endif;
        foreach ($languages as $language):
          if( $language["default_language"] ):
            $return.='<div class="form-group">
              <label class="form-group__service-name">Servis adı <span class="badge">'.$language["language_name"].'</span> '.$translationList.' </label>
              <input type="text" class="form-control" name="name['.$language["language_code"].']" value="'.$multiName[$language["language_code"]].'">
            </div>';
            if( count($languages) > 1 ):
              $return.='<div class="hidden" id="translationsList">';
            endif;
          else:
            $return.='<div class="form-group">
              <label class="form-group__service-name">Servis adı <span class="badge">'.$language["language_name"].'</span> </label>
              <input type="text" class="form-control" name="name['.$language["language_code"].']" value="'.$multiName[$language["language_code"]].'">
            </div>';
          endif;
        endforeach;
        if( count($languages) > 1 ):
          $return.='</div>';
        endif;

          $return.='<div class="service-mode__block">
            <div class="form-group">
            <label>Servis Kategori</label>
              <select class="form-control" name="category">
                    <option value="0">Lütfen kategori seçin..</option>';
                    foreach ( $categories as $category ):
                      $return.='<option value="'.$category["category_id"].'">'.$category["category_name"].'</option>';
                    endforeach;
                $return.='</select>
            </div>
          </div>

          <div class="service-mode__wrapper">
            <div class="service-mode__block">
              <div class="form-group">
              <label>Servis Tipi</label>
                <select class="form-control" name="package">
                      <option value="1">Servis</option>
                      <option value="2">Paket</option>
                      <option value="3">Özel Yorum</option>
                      <option value="4">Paket Yorum</option>
                  </select>
              </div>
            </div>
            <div class="service-mode__block">
              <div class="form-group">
              <label>Mod</label>
                <select class="form-control" name="mode" id="serviceMode">
                      <option value="1">Manuel</option>
                      <option value="2">Otomatik (API)</option>
                  </select>
              </div>
            </div>

            <div id="autoMode" style="display: none">
              <div class="service-mode__block">
                <div class="form-group">
                <label>Servis Sağlayıcısı</label>
                  <select class="form-control" name="provider" id="provider">
                        <option value="0">Servis sağlayıcı seçiniz...</option>';
                        foreach( $providers as $provider ):
                          $return.='<option value="'.$provider["id"].'">'.$provider["api_name"].'</option>';
                        endforeach;
                      $return.='</select>
                </div>
              </div>
              <div id="provider_service">
              </div>
              <div class="service-mode__block"  style="display: none">
                <div class="form-group">
                <label>Alış Fiyatı Üzerinden Fiyatlandır</label>
                  <select class="form-control" name="saleprice_cal" id="saleprice_cal>
                    <option value="normal">Hayır</option>
                    <option value="percent">Alış fiyatına % ekle </option>
                    <option value="amount">Alış fiyatına tutar ekle </option>
                  </select>
                </div>
              </div>
              <div class="form-group" style="display: none">
                <label class="form-group__service-name">Fiyat</label>
                <input type="text" class="form-control" name="saleprice" value="">
              </div>
              <div class="service-mode__block">
                <div class="form-group">
                <label>Dripfeed</label>
                  <select class="form-control" name="dripfeed">
                    <option value="1">Pasif</option>
                    <option value="2">Aktif</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <div class="service-mode__wrapper">
              <div class="row">
                <div class="col-md-6 service-mode__block ">
                  <div class="form-group">
                  <label>Instagram profil gizliliği kontrol edilsin mi?</label>
                    <select class="form-control" name="instagram_private">
                          <option value="1">Hayır</option>
                          <option value="2">Evet</option>
                      </select>
                  </div>
                </div>
                <div class="col-md-6 service-mode__block ">
                  <div class="form-group">
                  <label>Başlangıç sayısı</label>
                    <select class="form-control" name="start_count">
                          <option value="none">Çekilmesin</option>
                          <option value="instagram_follower">Instagram takipçi sayısı</option>
                          <option value="instagram_photo">Instagram fotoğraf beğeni sayısı</option>
                      </select>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 service-mode__block ">
                  <div class="form-group">
                  <label>Aynı bağlantıya 2.sipariş girilsin mi?</label>
                    <select class="form-control" name="instagram_second">
                          <option value="2">Evet</option>
                          <option value="1">Hayır</option>
                      </select>
                  </div>
                </div>
              </div>
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Servis fiyatı (1000 adet)</label>
            <input type="text" class="form-control" name="price" value="">
          </div>

          <div class="row">
            <div class="col-md-6 form-group">
              <label class="form-group__service-name">Minimum sipariş</label>
              <input type="text" class="form-control" name="min" value="">
            </div>

            <div class="col-md-6 form-group">
              <label class="form-group__service-name">Maksimum sipariş</label>
              <input type="text" class="form-control" name="max" value="">
            </div>
          </div>

          <hr>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Sipariş Bağlantı</label>
              <select class="form-control" name="want_username">
                  <option value="1">Link</option>
                  <option value="2">Kullanıcı adı</option>
              </select>
            </div>
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Kişiye Özel Servis</label>
              <select class="form-control" name="secret">
                  <option value="2">Hayır</option>
                  <option value="1">Evet</option>
              </select>
            </div>
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Servis Hızı</label>
              <select class="form-control" name="speed">
                  <option value="1">Yavaş</option>
                  <option value="2">Bazen Yavaş</option>
                  <option value="3">Normal</option>
                  <option value="4">Hızlı</option>
              </select>
            </div>
          </div>

        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Yeni servisi ekle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>
          <script src="'; $return.=site_url('public/admin/'); $return.='script.js"></script>
          <script>
          $(".other_services").click(function(){
            var control = $("#translationsList");
            if( control.attr("class") == "hidden" ){
              control.removeClass("hidden");
            } else{
              control.addClass("hidden");
            }
          });
          </script>
          ';
    echo json_encode(["content"=>$return,"title"=>"Yeni servis ekle"]);
  elseif( $action == "edit_service" ):
    $id       = $_POST["id"];
    $smmapi   = new SMMApi();
    $categories = $conn->prepare("SELECT * FROM categories ORDER BY category_line ");
    $categories->execute(array());
    $categories = $categories->fetchAll(PDO::FETCH_ASSOC);
    $serviceInfo= $conn->prepare("SELECT * FROM services LEFT JOIN service_api ON service_api.id=services.service_api WHERE services.service_id=:id ");
    $serviceInfo->execute(array("id"=>$id));
    $serviceInfo= $serviceInfo->fetch(PDO::FETCH_ASSOC);
    $providers  = $conn->prepare("SELECT * FROM service_api");
    $providers->execute(array());
    $providers  = $providers->fetchAll(PDO::FETCH_ASSOC);
    $multiName  = json_decode($serviceInfo["name_lang"],true);

      if( in_array($serviceInfo["service_package"],["11","12","13","14","15"]) ):
        $return = '<form class="form" action="'.site_url("admin/services/edit-subscription/".$serviceInfo["service_id"]).'" method="post" data-xhr="true">
            <div class="modal-body">';


   
          if( count($languages) > 1 ):
                $translationList = '<a class="other_services"> Çeviriler ('.(count($languages)-1).') </a>';
              else:
                $translationList  = '';
              endif;
              foreach ($languages as $language):
                if( $language["default_language"] ):
                  $return.='
          <div class="form-group">
                    <label class="form-group__service-name">Servis adı <span class="badge">'.$language["language_name"].'</span> '.$translationList.' </label>
                    <input type="text" class="form-control" name="name['.$language["language_code"].']" value="'.$multiName[$language["language_code"]].'">
                  </div>';
                  if( count($languages) > 1 ):
                    $return.='<div class="hidden" id="translationsList">';
                  endif;
                else:
                  $return.='<div class="form-group">
                    <label class="form-group__service-name">Servis adı <span class="badge">'.$language["language_name"].'</span> </label>
                    <input type="text" class="form-control" name="name['.$language["language_code"].']" value="'.$multiName[$language["language_code"]].'">
                  </div>';
                endif;
              endforeach;
              if( count($languages) > 1 ):
                $return.='</div>';
              endif;

              $return.='<div class="service-mode__block">
                <div class="form-group">
                <label>Servis Kategori</label>
                  <select class="form-control" name="category">
                        <option value="0">Lütfen kategori seçin..</option>';
                        foreach ( $categories as $category ):
                          $return.='<option value="'.$category["category_id"].'"'; if( $serviceInfo["category_id"] == $category["category_id"] ): $return.='selected'; endif; $return.='>'.$category["category_name"].'</option>';
                        endforeach;
                    $return.='</select>
                </div>
              </div>

              <div class="service-mode__block">
                <div class="form-group">
                <label>Abonelik Tipi</label>
                  <select class="form-control" disabled  id="subscription_package">
                        <option value="11"'; if( $serviceInfo["service_package"] == 11 ): $return.='selected'; endif; $return.='>Instagram Otomatik Beğeni - Sınırsız</option>
                        <option value="12"'; if( $serviceInfo["service_package"] == 12 ): $return.='selected'; endif; $return.='>Instagram Otomatik İzlenme - Sınırsız</option>
                        <option value="14"'; if( $serviceInfo["service_package"] == 14 ): $return.='selected'; endif; $return.='>Instagram Otomatik Beğeni - Süreli</option>
                        <option value="15"'; if( $serviceInfo["service_package"] == 15 ): $return.='selected'; endif; $return.='>Instagram Otomatik İzlenme - Süreli</option>
                    </select>
                </div>
              </div>

              

              <div class="service-mode__wrapper">

                <div class="service-mode__block">
                  <div class="form-group">
                  <label>Mod</label>
                    <select class="form-control" name="mode" id="serviceMode">
                          <option value="2"'; if( $serviceInfo["service_api"] != 0 ): $return.='selected'; endif; $return.='>Otomatik (API)</option>
                      </select>
                  </div>
                </div>


                <div id="autoMode" style="display: none">
                  <div class="service-mode__block">
                    <div class="form-group">
                    <label>Servis Sağlayıcısı</label>
                      <select class="form-control" name="provider" id="provider">
                            <option value="0">Servis sağlayıcı seçiniz...</option>';
                            foreach( $providers as $provider ):
                              $return.='<option value="'.$provider["id"].'"'; if( $serviceInfo["service_api"] == $provider["id"] ): $return.='selected'; endif; $return.='>'.$provider["api_name"].'</option>';
                            endforeach;
                          $return.='</select>
                    </div>
                  </div>
                  <div id="provider_service">';
                  $services = $smmapi->action(array('key' =>$serviceInfo["api_key"],'action' =>'services'),$serviceInfo["api_url"]);
                  if( $serviceInfo["api_type"] == 1 ):
                    $return.= '<div class="service-mode__block">
                      <div class="form-group">
                      <label>Servis</label>
                        <select class="form-control" name="service">';
                            foreach ($services as $service):
                              $return.= '<option value="'.$service->service.'"'; if( $serviceInfo["api_service"] == $service->service ): $return.='selected'; endif; $return.= '>'.$service->service.' - '.$service->name.' - '.$service->rate.'</option>';
                            endforeach;
                            $return.= '</select>
                      </div>
                    </div>';
                  elseif( $serviceInfo["api_type"] == 3 ):
                    $return.= '<div class="service-mode__block">
                      <div class="form-group">
                      <label>Servis</label>
                        <input class="form-control" value="'.$serviceInfo['api_service'].'" name="service">
                      </div>
                    </div>';
                  endif;
                  $return.='</div>
                </div>
              </div>

              <div id="unlimited">
                <div class="form-group">
                  <label class="form-group__service-name">Servis fiyatı (1000 adet)</label>
                  <input type="text" class="form-control" name="price" value="'.$serviceInfo["service_price"].'">
                </div>

                <div class="row">
                  <div class="col-md-6 form-group">
                    <label class="form-group__service-name">Minimum sipariş</label>
                    <input type="text" class="form-control" name="min" value="'.$serviceInfo["service_min"].'">
                  </div>

                  <div class="col-md-6 form-group">
                    <label class="form-group__service-name">Maksimum sipariş</label>
                    <input type="text" class="form-control" name="max" value="'.$serviceInfo["service_max"].'">
                  </div>
                </div>
              </div>

              <div id="limited">
                <div class="form-group">
                  <label class="form-group__service-name">Servis fiyatı</label>
                  <input type="text" class="form-control" name="limited_price" value="'.$serviceInfo["service_price"].'">
                </div>



                <div class="row">
                  <div class="col-md-6 form-group">
                    <label class="form-group__service-name">Gönderi miktarı</label>
                    <input type="text" class="form-control" name="autopost" value="'.$serviceInfo["service_autopost"].'">
                  </div>

                  <div class="col-md-6 form-group">
                    <label class="form-group__service-name">Sipariş miktarı</label>
                    <input type="text" class="form-control" name="limited_min" value="'.$serviceInfo["service_min"].'">
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-group__service-name">Paket Süresi <small>(gün)</small></label>
                  <input type="text" class="form-control" name="autotime" value="'.$serviceInfo["service_autotime"].'">
                </div>
              </div>

              <hr>

              <div class="service-mode__block">
                <div class="form-group">
                <label>Kişiye Özel Servis</label>
                  <select class="form-control" name="secret">
                      <option value="2"'; if( $serviceInfo["service_secret"] == 2 ): $return.='selected'; endif; $return.='>Hayır</option>
                      <option value="1"'; if( $serviceInfo["service_secret"] == 1 ): $return.='selected'; endif; $return.='>Evet</option>
                  </select>
                </div>
              </div>

              <div class="service-mode__block">
                <div class="form-group">
                <label>Servis Hızı</label>
                  <select class="form-control" name="speed">
                      <option value="1"'; if( $serviceInfo["service_speed"] == 1 ): $return.='selected'; endif; $return.='>Yavaş</option>
                      <option value="2"'; if( $serviceInfo["service_speed"] == 2 ): $return.='selected'; endif; $return.='>Bazen Yavaş</option>
                      <option value="3"'; if( $serviceInfo["service_speed"] == 3 ): $return.='selected'; endif; $return.='>Normal</option>
                      <option value="4"'; if( $serviceInfo["service_speed"] == 4 ): $return.='selected'; endif; $return.='>Hızlı</option>
                  </select>
                </div>
              </div>

            </div>

              <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Abonelik bilgilerini güncelle</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
              </div>
              </form>
              <script type="text/javascript">
              $(".other_services").click(function(){
                var control = $("#translationsList");
                if( control.attr("class") == "hidden" ){
                  control.removeClass("hidden");
                } else{
                  control.addClass("hidden");
                }
              });
              var site_url  = $("head base").attr("href");
                $("#provider").change(function(){
                  var provider = $(this).val();
                  getProviderServices(provider,site_url);
                });

                getProvider();
                $("#serviceMode").change(function(){
                  getProvider();
                });

                getSalePrice();
                $("#saleprice_cal").change(function(){
                  getSalePrice();
                });

                getSubscription();
                $("#subscription_package").change(function(){
                  getSubscription();
                });
                function getProviderServices(provider,site_url){
                  if( provider == 0 ){
                    $("#provider_service").hide();
                  }else{
                    $.post(site_url+"admin/ajax_data",{action:"providers_list",provider:provider}).done(function( data ) {
                      $("#provider_service").show();
                      $("#provider_service").html(data);
                    }).fail(function(){
                      alert("Hata oluştu!");
                    });
                  }
                }

                function getProvider(){
                  var mode = $("#serviceMode").val();
                    if( mode == 1 ){
                      $("#autoMode").hide();
                    }else{
                      $("#autoMode").show();
                    }
                }

                function getSalePrice(){
                  var type = $("#saleprice_cal").val();
                    if( type == "normal" ){
                      $("#saleprice").hide();
                      $("#servicePrice").show();
                    }else{
                      $("#saleprice").show();
                      $("#servicePrice").hide();
                    }
                }

                function getSubscription(){
                  var type = $("#subscription_package").val();
                    if( type == "11" || type == "12" ){
                      $("#unlimited").show();
                      $("#limited").hide();
                    }else{
                      $("#unlimited").hide();
                      $("#limited").show();
                    }
                }
              </script>
              ';


	 echo json_encode(["content"=>$return,"title"=>"Abonelik düzenle (ID: ".$serviceInfo["service_id"].")"]);


      else:
        $return = '

        <form class="form" action="'.site_url("admin/services/edit-service/".$serviceInfo["service_id"]).'" method="post" data-xhr="true">
            <div class="modal-body">';

              if( count($languages) > 1 ):
                $translationList = '<a class="other_services"> Çeviriler ('.(count($languages)-1).') </a>';
              else:
                $translationList  = '';
              endif;
              foreach ($languages as $language):
                if( $language["default_language"] ):
                  $return.='
				  <div class="form-group">
                    <label class="form-group__service-name">Servis adı <span class="badge">'.$language["language_name"].'</span> '.$translationList.' </label>
                    <input type="text" class="form-control" name="name['.$language["language_code"].']" value="'.$multiName[$language["language_code"]].'">
                  </div>';
                  if( count($languages) > 1 ):
                    $return.='<div class="hidden" id="translationsList">';
                  endif;
                else:
                  $return.='<div class="form-group">
                    <label class="form-group__service-name">Servis adı <span class="badge">'.$language["language_name"].'</span> </label>
                    <input type="text" class="form-control" name="name['.$language["language_code"].']" value="'.$multiName[$language["language_code"]].'">
                  </div>';
                endif;
              endforeach;
              if( count($languages) > 1 ):
                $return.='</div>';
              endif;

              $return.='<div class="service-mode__block">
                <div class="form-group">
                <label>Servis Kategori</label>
                  <select class="form-control" name="category">
                        <option value="0">Lütfen kategori seçin..</option>';
                        foreach ( $categories as $category ):
                          $return.='<option value="'.$category["category_id"].'"'; if( $serviceInfo["category_id"] == $category["category_id"] ): $return.='selected'; endif; $return.='>'.$category["category_name"].'</option>';
                        endforeach;
                    



                    $return.='</select>
                </div>
              </div>

              <div class="service-mode__wrapper">
                <div class="service-mode__block">
                  <div class="form-group">
                  <label>Servis Tipi</label>
                    <select class="form-control" name="package">
                          <option value="1"'; if( $serviceInfo["service_package"] == 1 ): $return.='selected'; endif; $return.='>Servis</option>
                          <option value="2"'; if( $serviceInfo["service_package"] == 2 ): $return.='selected'; endif; $return.='>Paket</option>
                          <option value="3"'; if( $serviceInfo["service_package"] == 3 ): $return.='selected'; endif; $return.='>Özel Yorum</option>
                          <option value="4"'; if( $serviceInfo["service_package"] == 4 ): $return.='selected'; endif; $return.='>Paket Yorum</option>
                      </select>
                  </div>
                </div>
                <div class="service-mode__block">
                  <div class="form-group">
                  <label>Mod</label>
                    <select class="form-control" name="mode" id="serviceMode">
                          <option value="1"'; if( $serviceInfo["service_api"] == 0 ): $return.='selected'; endif; $return.='>Manuel</option>
                          <option value="2"'; if( $serviceInfo["service_api"] != 0 ): $return.='selected'; endif; $return.='>Otomatik (API)</option>
                      </select>
                  </div>
                </div>

                <div id="autoMode" style="display: none">
                  <div class="service-mode__block">
                    <div class="form-group">
                    <label>Servis Sağlayıcısı</label>
                      <select class="form-control" name="provider" id="provider">
                            <option value="0">Servis sağlayıcı seçiniz...</option>';
                            foreach( $providers as $provider ):
                              $return.='<option value="'.$provider["id"].'"'; if( $serviceInfo["service_api"] == $provider["id"] ): $return.='selected'; endif; $return.='>'.$provider["api_name"].'</option>';
                            endforeach;
                          $return.='</select>
                    </div>
                  </div>
                  <div id="provider_service">';
                  $services = $smmapi->action(array('key' =>$serviceInfo["api_key"],'action' =>'services'),$serviceInfo["api_url"]);
                    if( $serviceInfo["api_type"] == 1 ):
                      $return.= '<div class="service-mode__block">
                        <div class="form-group">
                        <label>Servis</label>
                          <select class="form-control" name="service">';
                              foreach ($services as $service):
                                $return.= '<option value="'.$service->service.'"'; if( $serviceInfo["api_service"] == $service->service ): $return.='selected'; endif; $return.= '>'.$service->service.' - '.$service->name.' - '.$service->rate.'</option>';
                              endforeach;
                              $return.= '</select>
                        </div>
                      </div>';
                    elseif( $serviceInfo["api_type"] == 3 ):
                      $return.= '<div class="service-mode__block">
                        <div class="form-group">
                        <label>Servis</label>
                          <input class="form-control" value="'.$serviceInfo['api_service'].'" name="service">
                        </div>
                      </div>';
                    endif;
                  $return.='</div>
                  <div class="service-mode__block"  style="display: none">
                    <div class="form-group">
                    <label>Alış Fiyatı Üzerinden Fiyatlandır</label>
                      <select class="form-control" name="saleprice_cal" id="saleprice_cal>
                        <option value="normal">Hayır</option>
                        <option value="percent">Alış fiyatına % ekle </option>
                        <option value="amount">Alış fiyatına tutar ekle </option>
                      </select>
                    </div>
                  </div>
                  <div class="form-group" style="display: none">
                    <label class="form-group__service-name">Fiyat</label>
                    <input type="text" class="form-control" name="saleprice" value="">
                  </div>
                  <div class="service-mode__block">
                    <div class="form-group">
                    <label>Dripfeed</label>
                      <select class="form-control" name="dripfeed">
                        <option value="1"'; if( $serviceInfo["service_dripfeed"] == 1 ): $return.='selected'; endif; $return.='>Pasif</option>
                        <option value="2"'; if( $serviceInfo["service_dripfeed"] == 2 ): $return.='selected'; endif; $return.='>Aktif</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <div class="service-mode__wrapper">
                  <div class="row">
                    <div class="col-md-6 service-mode__block ">
                      <div class="form-group">
                      <label>Instagram profil gizliliği kontrol edilsin mi?</label>
                        <select class="form-control" name="instagram_private">
                              <option value="1"'; if( $serviceInfo["instagram_private"] == 1 ): $return.='selected'; endif; $return.='>Hayır</option>
                              <option value="2"'; if( $serviceInfo["instagram_private"] == 2 ): $return.='selected'; endif; $return.='>Evet</option>
                          </select>
                      </div>
                    </div>
                    <div class="col-md-6 service-mode__block ">
                      <div class="form-group">
                      <label>Başlangıç sayısı</label>
                        <select class="form-control" name="start_count">
                              <option value="none"'; if( $serviceInfo["start_count"] == "none" ): $return.='selected'; endif; $return.='>Çekilmesin</option>
                              <option value="instagram_follower"'; if( $serviceInfo["start_count"] == "instagram_follower" ): $return.='selected'; endif; $return.='>Instagram takipçi sayısı</option>
                              <option value="instagram_photo"'; if( $serviceInfo["start_count"] == "instagram_photo" ): $return.='selected'; endif; $return.='>Instagram fotoğraf beğeni sayısı</option>
                          </select>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6 service-mode__block ">
                      <div class="form-group">
                      <label>Aynı bağlantıya 2.sipariş girilsin mi?</label>
                        <select class="form-control" name="instagram_second">
                              <option value="2"'; if( $serviceInfo["instagram_second"] == 2 ): $return.='selected'; endif; $return.='>Evet</option>
                              <option value="1"'; if( $serviceInfo["instagram_second"] == 1 ): $return.='selected'; endif; $return.='>Hayır</option>
                          </select>
                      </div>
                    </div>
                  </div>
              </div>

              <div class="form-group">
                <label class="form-group__service-name">Servis fiyatı (1000 adet)</label>
                <input type="text" class="form-control" name="price" value="'.$serviceInfo["service_price"].'">
              </div>

              <div class="row">
                <div class="col-md-6 form-group">
                  <label class="form-group__service-name">Minimum sipariş</label>
                  <input type="text" class="form-control" name="min" value="'.$serviceInfo["service_min"].'">
                </div>

                <div class="col-md-6 form-group">
                  <label class="form-group__service-name">Maksimum sipariş</label>
                  <input type="text" class="form-control" name="max" value="'.$serviceInfo["service_max"].'">
                </div>
              </div>

              <hr>

              <div class="service-mode__block">
                <div class="form-group">
                <label>Sipariş Bağlantı</label>
                  <select class="form-control" name="want_username">
                      <option value="1"'; if( $serviceInfo["want_username"] == 1 ): $return.='selected'; endif; $return.='>Link</option>
                      <option value="2"'; if( $serviceInfo["want_username"] == 2 ): $return.='selected'; endif; $return.='>Kullanıcı adı</option>
                  </select>
                </div>
              </div>

              <div class="service-mode__block">
                <div class="form-group">
                <label>Kişiye Özel Servis</label>
                  <select class="form-control" name="secret">
                      <option value="2"'; if( $serviceInfo["service_secret"] == 2 ): $return.='selected'; endif; $return.='>Hayır</option>
                      <option value="1"'; if( $serviceInfo["service_secret"] == 1 ): $return.='selected'; endif; $return.='>Evet</option>
                  </select>
                </div>
              </div>

              <div class="service-mode__block">
                <div class="form-group">
                <label>Servis Hızı</label>
                  <select class="form-control" name="speed">
                      <option value="1"'; if( $serviceInfo["service_speed"] == 1 ): $return.='selected'; endif; $return.='>Yavaş</option>
                      <option value="2"'; if( $serviceInfo["service_speed"] == 2 ): $return.='selected'; endif; $return.='>Bazen Yavaş</option>
                      <option value="3"'; if( $serviceInfo["service_speed"] == 3 ): $return.='selected'; endif; $return.='>Normal</option>
                      <option value="4"'; if( $serviceInfo["service_speed"] == 4 ): $return.='selected'; endif; $return.='>Hızlı</option>
                  </select>
                </div>
              </div>

            </div>

              <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Servis bilgilerini güncelle</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
              </div>
              </form>
              <script type="text/javascript">

               $(".other_services").click(function(){
                 var control = $("#translationsList");
                 if( control.attr("class") == "hidden" ){
                   control.removeClass("hidden");
                 } else{
                   control.addClass("hidden");
                 }
               });
              var site_url  = $("head base").attr("href");
                $("#provider").change(function(){
                  var provider = $(this).val();
                  getProviderServices(provider,site_url);
                });

                getProvider();
                $("#serviceMode").change(function(){
                  getProvider();
                });

                getSalePrice();
                $("#saleprice_cal").change(function(){
                  getSalePrice();
                });

                getSubscription();
                $("#subscription_package").change(function(){
                  getSubscription();
                });
                function getProviderServices(provider,site_url){
                  if( provider == 0 ){
                    $("#provider_service").hide();
                  }else{
                    $.post(site_url+"admin/ajax_data",{action:"providers_list",provider:provider}).done(function( data ) {
                      $("#provider_service").show();
                      $("#provider_service").html(data);
                    }).fail(function(){
                      alert("Hata oluştu!");
                    });
                  }
                }

                function getProvider(){
                  var mode = $("#serviceMode").val();
                    if( mode == 1 ){
                      $("#autoMode").hide();
                    }else{
                      $("#autoMode").show();
                    }
                }

                function getSalePrice(){
                  var type = $("#saleprice_cal").val();
                    if( type == "normal" ){
                      $("#saleprice").hide();
                      $("#servicePrice").show();
                    }else{
                      $("#saleprice").show();
                      $("#servicePrice").hide();
                    }
                }

                function getSubscription(){
                  var type = $("#subscription_package").val();
                    if( type == "11" || type == "12" ){
                      $("#unlimited").show();
                      $("#limited").hide();
                    }else{
                      $("#unlimited").hide();
                      $("#limited").show();
                    }
                }
              </script>
              ';
        echo json_encode(["content"=>$return,"title"=>"Servis düzenle (ID: ".$serviceInfo["service_id"].")"]);
      endif;
  elseif( $action == "edit_description" ):
    $id       = $_POST["id"];
    $smmapi   = new SMMApi();
    $serviceInfo= $conn->prepare("SELECT * FROM services WHERE service_id=:id ");
    $serviceInfo->execute(array("id"=>$id));
    $serviceInfo= $serviceInfo->fetch(PDO::FETCH_ASSOC);
    $multiDesc  = json_decode($serviceInfo["description_lang"],true);

        $return = '<form class="form" action="'.site_url("admin/services/edit-description/".$serviceInfo["service_id"]).'" method="post" data-xhr="true">
            <div class="modal-body">';

              if( count($languages) > 1 ):
                $translationList = '<a class="other_services"> Çeviriler ('.(count($languages)-1).') </a>';
              else:
                $translationList  = '';
              endif;
              foreach ($languages as $language):
                if( $language["default_language"] ):
                  $return.='<div class="form-group">
                    <label class="form-group__service-name">Açıklama <span class="badge">'.$language["language_name"].'</span> '.$translationList.' </label>
                    <textarea class="form-control" rows="5" name="description['.$language["language_code"].']">'.$multiDesc[$language["language_code"]].'</textarea>
                  </div>';
                  if( count($languages) > 1 ):
                    $return.='<div class="hidden" id="translationsList">';
                  endif;
                else:
                  $return.='<div class="form-group">
                    <label class="form-group__service-name">Açıklama <span class="badge">'.$language["language_name"].'</span> </label>
                    <textarea class="form-control" rows="5"  name="description['.$language["language_code"].']">'.$multiDesc[$language["language_code"]].'</textarea>
                  </div>';
                endif;
              endforeach;
              if( count($languages) > 1 ):
                $return.='</div>';
              endif;

              $return.='

            </div>

              <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Açıklamayı güncelle</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
              </div>
              </form>
              <script type="text/javascript">

              $(".other_services").click(function(){
                var control = $("#translationsList");
                if( control.attr("class") == "hidden" ){
                  control.removeClass("hidden");
                } else{
                  control.addClass("hidden");
                }
              });

              </script>
              ';
        echo json_encode(["content"=>$return,"title"=>"Açıklama düzenle (ID: ".$serviceInfo["service_id"].")"]);
  elseif( $action == "new_subscriptions" ):
    $categories = $conn->prepare("SELECT * FROM categories ORDER BY category_line ");
    $categories->execute(array());
    $categories = $categories->fetchAll(PDO::FETCH_ASSOC);
    $providers  = $conn->prepare("SELECT * FROM service_api");
    $providers->execute(array());
    $providers  = $providers->fetchAll(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="'.site_url("admin/services/new-subscription").'" method="post" data-xhr="true">
        <div class="modal-body">';

        if( count($languages) > 1 ):
          $translationList = '<a class="other_services"> Çeviriler ('.(count($languages)-1).') </a>';
        else:
          $translationList  = '';
        endif;
        foreach ($languages as $language):
          if( $language["default_language"] ):
            $return.='<div class="form-group">
              <label class="form-group__service-name">Servis adı <span class="badge">'.$language["language_name"].'</span> '.$translationList.' </label>
              <input type="text" class="form-control" name="name['.$language["language_code"].']" value="'.$multiName[$language["language_code"]].'">
            </div>';
            if( count($languages) > 1 ):
              $return.='<div class="hidden" id="translationsList">';
            endif;
          else:
            $return.='<div class="form-group">
              <label class="form-group__service-name">Servis adı <span class="badge">'.$language["language_name"].'</span> </label>
              <input type="text" class="form-control" name="name['.$language["language_code"].']" value="'.$multiName[$language["language_code"]].'">
            </div>';
          endif;
        endforeach;
        if( count($languages) > 1 ):
          $return.='</div>';
        endif;

          $return.='<div class="service-mode__block">
            <div class="form-group">
            <label>Servis Kategori</label>
              <select class="form-control" name="category">
                    <option value="0">Lütfen kategori seçin..</option>';
                    foreach ( $categories as $category ):
                      $return.='<option value="'.$category["category_id"].'">'.$category["category_name"].'</option>';
                    endforeach;
                $return.='</select>
            </div>
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Abonelik Tipi</label>
              <select class="form-control" name="package" id="subscription_package">
                    <option value="11">Instagram Otomatik Beğeni - Sınırsız</option>
                    <option value="12">Instagram Otomatik İzlenme - Sınırsız</option>
                    <option value="14">Instagram Otomatik Beğeni - Süreli</option>
                    <option value="15">Instagram Otomatik İzlenme - Süreli</option>
                </select>
            </div>
          </div>

          <div class="service-mode__wrapper">

            <div class="service-mode__block">
              <div class="form-group">
              <label>Mod</label>
                <select class="form-control" name="mode" id="serviceMode">
                      <option value="2">Otomatik (API)</option>
                  </select>
              </div>
            </div>

            <div id="autoMode" style="display: none">
              <div class="service-mode__block">
                <div class="form-group">
                <label>Servis Sağlayıcısı</label>
                  <select class="form-control" name="provider" id="provider">
                        <option value="0">Servis sağlayıcı seçiniz...</option>';
                        foreach( $providers as $provider ):
                          $return.='<option value="'.$provider["id"].'">'.$provider["api_name"].'</option>';
                        endforeach;
                      $return.='</select>
                </div>
              </div>
              <div id="provider_service">
              </div>
            </div>
          </div>

          <div id="unlimited">
            <div class="form-group">
              <label class="form-group__service-name">Servis fiyatı (1000 adet)</label>
              <input type="text" class="form-control" name="price" value="">
            </div>

            <div class="row">
              <div class="col-md-6 form-group">
                <label class="form-group__service-name">Minimum sipariş</label>
                <input type="text" class="form-control" name="min" value="">
              </div>

              <div class="col-md-6 form-group">
                <label class="form-group__service-name">Maksimum sipariş</label>
                <input type="text" class="form-control" name="max" value="">
              </div>
            </div>
          </div>

          <div id="limited">
            <div class="form-group">
              <label class="form-group__service-name">Servis fiyatı</label>
              <input type="text" class="form-control" name="limited_price" value="">
            </div>



            <div class="row">
              <div class="col-md-6 form-group">
                <label class="form-group__service-name">Gönderi miktarı</label>
                <input type="text" class="form-control" name="autopost" value="">
              </div>

              <div class="col-md-6 form-group">
                <label class="form-group__service-name">Sipariş miktarı</label>
                <input type="text" class="form-control" name="limited_min" value="">
              </div>
            </div>
            <div class="form-group">
              <label class="form-group__service-name">Paket Süresi <small>(gün)</small></label>
              <input type="text" class="form-control" name="autotime" value="">
            </div>
          </div>

          <hr>


          <div class="service-mode__block">
            <div class="form-group">
            <label>Kişiye Özel Servis Servis</label>
              <select class="form-control" name="secret">
                  <option value="2">Hayır</option>
                  <option value="1">Evet</option>
              </select>
            </div>
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Servis Hızı</label>
              <select class="form-control" name="speed">
                  <option value="1">Yavaş</option>
                  <option value="2">Bazen Yavaş</option>
                  <option value="3">Normal</option>
                  <option value="4">Hızlı</option>
              </select>
            </div>
          </div>

        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Yeni aboneliği ekle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>
          <script type="text/javascript">

          $(".other_services").click(function(){
            var control = $("#translationsList");
            if( control.attr("class") == "hidden" ){
              control.removeClass("hidden");
            } else{
              control.addClass("hidden");
            }
          });

          var site_url  = $("head base").attr("href");
            $("#provider").change(function(){
              var provider = $(this).val();
              getProviderServices(provider,site_url);
            });

            getProvider();
            $("#serviceMode").change(function(){
              getProvider();
            });

            getSalePrice();
            $("#saleprice_cal").change(function(){
              getSalePrice();
            });

            getSubscription();
            $("#subscription_package").change(function(){
              getSubscription();
            });
            function getProviderServices(provider,site_url){
              if( provider == 0 ){
                $("#provider_service").hide();
              }else{
                $.post(site_url+"admin/ajax_data",{action:"providers_list",provider:provider}).done(function( data ) {
                  $("#provider_service").show();
                  $("#provider_service").html(data);
                }).fail(function(){
                  alert("Hata oluştu!");
                });
              }
            }

            function getProvider(){
              var mode = $("#serviceMode").val();
                if( mode == 1 ){
                  $("#autoMode").hide();
                }else{
                  $("#autoMode").show();
                }
            }

            function getSalePrice(){
              var type = $("#saleprice_cal").val();
                if( type == "normal" ){
                  $("#saleprice").hide();
                  $("#servicePrice").show();
                }else{
                  $("#saleprice").show();
                  $("#servicePrice").hide();
                }
            }

            function getSubscription(){
              var type = $("#subscription_package").val();
                if( type == "11" || type == "12" ){
                  $("#unlimited").show();
                  $("#limited").hide();
                }else{
                  $("#unlimited").hide();
                  $("#limited").show();
                }
            }
          </script>
          ';
    echo json_encode(["content"=>$return,"title"=>"Yeni abonelik ekle"]);
  elseif( $action == "new_category" ):
    $return = '<form class="form" action="'.site_url("admin/services/new-category").'" method="post" data-xhr="true">

        <div class="modal-body">
          <div class="form-group">
            <label class="form-group__service-name">Kategori adı</label>
            <input type="text" class="form-control" name="name" value="">
          </div>
 <div class="form-group">
				<label class="form-group__service-name">İcon</label>
				  <select class="form-control" name="icon">
			
						<option value="">Yok</option>
						<option value="facebook-square">Facebook</option>
						<option value="instagram">Instagram</option>
						<option value="twitter">Twitter</option>
						<option value="twitch">Twitch</option>
						<option value="youtube-play">Youtube</option>
						<option value="snapchat">Snapchat</option>
						<option value="linkedin-square">Linkedin</option>
						<option value="telegram">Telegram</option>
						<option value="spotify">Spotify</option>
						<option value="soundcloud">Soundcloud</option>
						<option value="pinterest">Pinterest</option>
						<option value="google">Google</option>
						<option value="google-plus-official">Google+</option>
						<option value="vk">VK</option>
						<option value="thumbs-up">Like</option>
						<option value="thumbs-down">Dislike</option>
						<option value="user-plus">Follower</option>
						<option value="comment">Comment</option>
						<option value="align-justify">Post</option>
						<option value="eye">View</option>
						<option value="hashtag">Hashtag</option>
						<option value="smile">Smile</option>
						<option value="gift">Gift</option>
						<option value="key">Key</option>
						<option value="globe">Website</option>
						<option value="bolt">Bolt</option>
						<option value="diamond">Diamond</option>
						<option value="bookmark-o">Bookmark O</option>
						<option value="bookmark">Bookmark</option>	
						<option value="heart">Heart</option>
						<option value="star">Star</option>
						<option value="music">Music</option>
						<option value="play">Play</option>
						<option value="venus">Venus</option>
						<option value="mars">Mars</option>
						<option value="cogs">Settings</option>
						<option value="gamepad">Gamepad</option>
						<option value="cubes">Cubes</option>
						<option value="undo">Undo</option>
						<option value="circle-o">Circle</option>
						<option value="info-circle">İnfo Circle</option>
					</select>
              </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Kişiye Özel Kategori</label>
              <select class="form-control" name="secret">
                    <option value="2">Hayır</option>
                    <option value="1">Evet</option>
                </select>
            </div>
          </div>


        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Kategoriyi oluştur</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Yeni kategori oluştur"]);
  elseif( $action == "edit_category" ):
    $id       = $_POST["id"];
    $category = $conn->prepare("SELECT * FROM categories WHERE category_id=:id ");
    $category->execute(array("id"=>$id));
    $category = $category->fetch(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="'.site_url("admin/services/edit-category/".$id).'" method="post" data-xhr="true">

        <div class="modal-body">
          <div class="form-group">
            <label class="form-group__service-name">Kategori adı</label>
            <input type="text" class="form-control" name="name" value="'.$category["category_name"].'">
          </div>
 <div class="form-group">
				<label class="form-group__service-name">İcon <i id="icon" class="fa fa-'.$category["category_icon"].'"></i></label>
				  <select class="form-control" name="icon">
						<option value="'.$category["category_icon"].'">Seçili : '.$category["category_icon"].'</option>
						<option value="">Yok</option>
						<option value="facebook-square">Facebook</option>
						<option value="instagram">Instagram</option>
						<option value="twitter">Twitter</option>
						<option value="twitch">Twitch</option>
						<option value="youtube-play">Youtube</option>
						<option value="snapchat">Snapchat</option>
						<option value="linkedin-square">Linkedin</option>
						<option value="telegram">Telegram</option>
						<option value="spotify">Spotify</option>
						<option value="soundcloud">Soundcloud</option>
						<option value="pinterest">Pinterest</option>
						<option value="google">Google</option>
						<option value="google-plus-official">Google+</option>
						<option value="vk">VK</option>
						<option value="thumbs-up">Like</option>
						<option value="thumbs-down">Dislike</option>
						<option value="user-plus">Follower</option>
						<option value="comment">Comment</option>
						<option value="align-justify">Post</option>
						<option value="eye">View</option>
						<option value="hashtag">Hashtag</option>
						<option value="smile">Smile</option>
						<option value="gift">Gift</option>
						<option value="key">Key</option>
						<option value="globe">Website</option>
						<option value="bolt">Bolt</option>
						<option value="diamond">Diamond</option>
						<option value="bookmark-o">Bookmark O</option>
						<option value="bookmark">Bookmark</option>	
						<option value="heart">Heart</option>
						<option value="star">Star</option>
						<option value="music">Music</option>
						<option value="play">Play</option>
						<option value="venus">Venus</option>
						<option value="mars">Mars</option>
						<option value="cogs">Settings</option>
						<option value="gamepad">Gamepad</option>
						<option value="cubes">Cubes</option>
						<option value="undo">Undo</option>
						<option value="circle-o">Circle</option>
						<option value="info-circle">İnfo Circle</option>
					</select>
              </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Kişiye Özel Kategori</label>
              <select class="form-control" name="secret">
                    <option value="2"'; if( $category["category_secret"] == 2 ): $return.='selected'; endif; $return.='>Hayır</option>
                    <option value="1"'; if( $category["category_secret"] == 1 ): $return.='selected'; endif; $return.='>Evet</option>
                </select>
            </div>
          </div>


        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Kategoriyi güncelle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Kategoriyi düzenle (ID: ".$id.")"]);
  elseif( $action == "import_services" ):
    $providers  = $conn->prepare("SELECT * FROM service_api");
    $providers->execute(array());
    $providers  = $providers->fetchAll(PDO::FETCH_ASSOC);

      $category  = $conn->prepare("SELECT * FROM categories");
      $category->execute(array());
      $category  = $category->fetchAll(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="'.site_url("admin/services/get_services_add/").'" method="post" data-xhr="true">
    
        <div class="modal-body">

          <div id="firstStep"><p style="font-size:12px;margin:0 0 15px 0" id="get_services_warning">
        <b>DİKKAT</b> <br>
        -> Ekleyeceğiniz servisler için dripfeed özelliği ve servis açıklaması çekilmeyecektir. <br>
        -> Otomatik beğeni vb. servisleri buradan çektiğinizde çekmeyecektir Sadece normal servisleri çekebilirsiniz.  <br>
        </p>
        <hr>
            <div class="service-mode__block">
              <div class="form-group">
              <label>Servis Sağlayıcısı</label>
                <select class="form-control" name="provider" id="provider">
                      <option value="0">Servis sağlayıcı seçiniz...</option>';
                      foreach( $providers as $provider ):
                        $return.='<option value="'.$provider["id"].'">'.$provider["api_name"].'</option>';
                      endforeach;
                    $return.='</select>
              </div>
            </div><div class="service-mode__block">
              <div class="form-group">
              <label>Servislerin Ekleneceği  Kategoriyi Seçiniz</label>
                <select class="form-control" name="selector" id="selector">
                      <option value="0">Kategori seçiniz...</option>';
                      foreach( $category as $cat ):
                        $return.='<option value="' . ($cat["category_id"]) . '">'.$cat["category_name"].'</option>';
                      endforeach;
                    $return.= '</select>
              </div>
            </div>
          </div>

          
          <div id="secondStep">
          </div>

          <div id="thirdStep">
          </div>


        </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
            <button type="button" class="btn btn-primary" id="nextStep" data-step="first">Sonra ki adım</button>
            <button type="submit" class="btn btn-primary" id="submitStep">Servisleri ekle</button>
          </div>

        </form>
           <script>
            $("#submitStep").hide();
            $("#nextStep").click(function(){
              var now_step = $(this).attr("data-step");
              var provider = $("#provider").val();
              var category = $("#selector").val();
              $("#secondStep").hide();
                if( now_step == "first" ){
                  if( provider == 0 ){
                    $.toast({
                        heading: "Failed",
                        text: "Lütfen servis sağlayıcısını seçin",
                        icon: "error",
                        loader: true,
                        loaderBg: "#9EC600"
                    });
                  }else{
                    $("#firstStep").hide();
                    $("#secondStep").show();
                    $.post("admin/ajax_data", {provider:provider,category:category,action:"import_services_list" }, function(data){
                      $("#secondStep").html(data);
                    });
                    $("#nextStep").attr("data-step","second");
                  }
                }else if( now_step == "second" ){
                    var array     = [];
                       $(\'[class^="selectServices-"]\').each(function () {
                            var id    = $(this).val();
                            var check = $(this).prop("checked");
                            var provider  =  $(this).attr("data-provider");
                              if( check == true ){
                                var params = {};
                                params["id"]            = id;
                                params["category"]      = $(this).attr("data-category");
                                array.push(params);
                              }
                       });
                       var count = array.length;
                     if( count ){
                       $.post("admin/ajax_data", {provider:provider,action:"import_services_last",services:array }, function(data){
                         $("#thirdStep").html(data);
                       });
                       $("#nextStep").hide();
                       $("#submitStep").show();
                     }else{
                       $("#nextStep").attr("data-step","second");
                       $("#firstStep").hide();
                       $("#secondStep").show();
                       $("#nextStep").show();
                       $("#submitStep").hide();
                       $.toast({
                           heading: "Failed",
                           text: "Lütfen eklemek istediğiniz en az 1 servisi seçin",
                           icon: "error",
                           loader: true,
                           loaderBg: "#9EC600"
                       });
                     }

                }
            });
          </script>
          ';
    echo json_encode(["content"=>$return,"title"=>"Sağlayıcıdaki servisleri çek"]);
  elseif( $action == "import_services_list" ):
    $provider_id  = $_POST["provider"];
    $category_id2  = $_POST["category"];
    $smmapi       = new SMMApi();
    $provider     = $conn->prepare("SELECT * FROM service_api WHERE id=:id");
    $provider     ->execute(array("id"=>$provider_id));
    $provider     = $provider->fetch(PDO::FETCH_ASSOC);
      if( $provider["api_type"] == 1 ):
        $services   = $smmapi->action(array('key'=>$provider["api_key"],'action'=>'services'),$provider["api_url"]);
          if( $services ):
            $grouped = array_group_by($services, 'category');
            echo '<div class="">
            <div class="services-import__body">
                 <div>
                    <div class="services-import__list-wrap">
                       <div class="services-import__scroll-wrap">';
                       foreach($grouped as $category):
                         $category_id++;
                         echo '
                          <span>
                             <div class="services-import__category">
                                <div class="services-import__category-title">
                                  <label><input type="checkbox" data-id="'.$category_id.'" id="checkAll-'.$category_id.'">'.$category[0]->category.'</label>
                                                                <input type="hidden" name="category" value="'.$category_id2.'">
                                </div>
                             </div>
                             <div class="services-import__packages">
                                <ul>';
                                for($i=0;$i<count($category);$i++):
                                  echo '<li><label><input data-service="'.$category[$i]->name.'" data-provider="'.$provider["id"].'"  data-category="'.$category_id.'"  class="selectServices-'.$category_id.'" type="checkbox" value="'.$category[$i]->service.'" name="services[]">'.$category[$i]->service.' - '.$category[$i]->name.'<span class="services-import__packages-price">'.priceFormat($category[$i]->rate).'</span></label></li>';
                                endfor;
                              echo  '</ul>
                             </div>
                          </span>';
                        endforeach;
                        echo '
                       </div>
                    </div>
                 </div>
              </div>
              <script>
              $(\'[id^="checkAll-"]\').click(function () {
                var id = $(this).attr("data-id");
                 if ( $(this).prop("checked") == true ) {
                   $(".selectServices-"+id).not(this).prop("checked", true);
                 }else{
                   $(".selectServices-"+id).not(this).prop("checked", false);
                 }
               });
              </script>
              </div>';
          else:
            echo "Hata oluştu, lütfen daha sonra deneyin.";
          endif;
      endif;
  elseif( $action == "import_services_last" ):
    $provider_id  = $_POST["provider"];
    $services     = json_decode(json_encode($_POST["services"]));
    $smmapi       = new SMMApi();
    $provider     = $conn->prepare("SELECT * FROM service_api WHERE id=:id");
    $provider     ->execute(array("id"=>$provider_id));
    $provider     = $provider->fetch(PDO::FETCH_ASSOC);
    $apiServices  = $smmapi->action(array('key'=>$provider["api_key"],'action'=>'services'),$provider["api_url"]);
    $grouped      = array_group_by($services, 'category');
      echo '
      <div class="services-import__body">
             <div>
                <div class="services-import__fields">
                   <div class="services-import__step3-field">
                      <div class="services-import__placeholder-title">Sabit (1.00)</div>
                      <input type="number" placeholder="0" id="raise-fixed" name="fixed" value="">
                   </div>
                   <div class="services-import__step3-plus">+</div>
                   <div class="services-import__step3-field">
                      <div class="services-import__placeholder-title">Yüzde (%)</div>
                      <input type="number" placeholder="0" id="raise-percent" name="percent" value="">
                   </div>
                   <div class="services-import__step3-actions"><span class="btn btn-default">Hesaplamaları sıfırla</span></div>
                </div>
                <div class="services-import__list-wrap services-import__list-active">
                   <div class="services-import__scroll-wrap">';
                      $category_id  = 0;
                      $c=0;
                      foreach($grouped as $category):
                          foreach ($apiServices as $key => $value):
                            if( $category[$category_id]->id == $value->service ):
                              $categoryName = $value->category;
                            endif;
                          endforeach;
                          $category_id=$category_id++;
                          $c++;
                        echo '<span class="providerCategory" id="providerCategory-'.$c.'">
                           <div class="services-import__category">
                              <div class="services-import__category-title"><label>'.$categoryName.'</label></div>
                           </div>
                           <div class="services-import__packages">
                              <ul>';
                                for($i=0;$i<count($category);$i++):
                                  foreach ($apiServices as $apiService):
                                    if( $apiService->service == $category[$i]->id  ):
                                      echo '<li id="providerService-'.$apiService->service.'">
                                         <label>
                                            '.$apiService->service.' - '.$apiService->name.'
                                            <span class="services-import__packages-price-edit" >
                                               <div class="services-import__packages-price-lock" data-category="'.$c.'"  data-id="servicedelete-'.$apiService->service.'" data-service="'.$apiService->service.'">
                                                 <span class="fa fa-trash"></span>
                                               </div>
                                               <div class="services-import__packages-price-lock"  data-id="servicelock-'.$apiService->service.'" data-service="'.$apiService->service.'">
                                                 <span class="fa fa-unlock"></span>
                                               </div>
                                               <input id="servicePriceCal'.$apiService->service.'" type="text" class="services-import__price" data-rate="'.priceFormat($apiService->rate).'" data-service="'.$apiService->service.'" name="servicesList['.$apiService->service.']" value="'.priceFormat
                                               ($apiService->rate).'">
                                               <span class="services-import__provider-price">'.priceFormat($apiService->rate).'</span>
                                            </span>
                                         </label>
                                      </li>';
                                    endif;
                                  endforeach;
                                endfor;
                            echo  '</ul>
                           </div>
                        </span>';
                      endforeach;
                   echo '</div>
                </div>
             </div>
          </div>
          <script>
          function formatCurrency(total) {
              var neg = false;
              if(total < 0) {
                  neg = true;
                  total = Math.abs(total);
              }
              return parseFloat(total, 10).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, "$1,").toString();
          }
          function sum(input){
           if (toString.call(input) !== "[object Array]")
              return false;

                      var total =  0;
                      for(var i=0;i<input.length;i++)
                        {
                          if(isNaN(input[i])){
                          continue;
                           }
                      total += Number(input[i]);
                   }
             return total;
            }
          function chargeService(){
            var add_fixed       = $("#raise-fixed").val();
            var add_percent     = $("#raise-percent").val();
            $(".services-import__price").each(function(){
              if( $(this).attr("readonly") != "readonly" ){
                var rate        = $(this).attr("data-rate");
                var service     = $(this).attr("data-service");
                var total = sum([rate,(rate*add_percent/100),add_fixed]);
                $("#servicePriceCal"+service).val(total);

              }
            });
          }
            $(\'[data-id^="servicedelete-"]\').click(function(){
              var id        = $(this).attr("data-service");
              var category  = $(this).attr("data-category");
              $("li#providerService-"+id).remove();
                if( $("#providerCategory-"+category+" > .services-import__packages > ul > li").length == 0 ){
                  $("#providerCategory-"+category).remove();
                }
            });
            $(\'[data-id^="servicelock-"]\').click(function(){
              var service_id  = $(this).attr("data-service");
              var lock        = $(this).find("span").attr("class");
              if( lock == "fa fa-unlock" ){
                $(this).find("span").removeClass("fa fa-unlock");
                $(this).find("span").addClass("fa fa-lock");
                $(\'[data-service="\'+service_id+\'"]\').attr("readonly",true);
              } else{
                $(this).find("span").removeClass("fa fa-lock");
                $(this).find("span").addClass("fa fa-unlock");
                $(\'[data-service="\'+service_id+\'"]\').attr("readonly",false);
              }
            });

            $(".services-import__step3-actions").click(function(){
              var add_fixed       = $("#raise-fixed").val("");
              var add_percent     = $("#raise-percent").val("");
              $(".services-import__price").each(function(){
                if( $(this).attr("readonly") != "readonly" ){
                  var rate        = $(this).attr("data-rate");
                  var service     = $(this).attr("data-service");
                    $("#servicePriceCal"+service).val(rate);
                }
              });
            });

            $("#raise-fixed").on("keyup", function(){
              chargeService();
            });

            $("#raise-percent").on("keyup", function(){
              chargeService();
            });

          </script>
          ';
  elseif( $action == "price_providerCal" ):
    $fixed    = $_POST["fixed"];
    $percent  = $_POST["percent"];
    $rate     = $_POST["rate"];
    $total    = $rate;
      if( is_numeric($percent) && $percent > 0  ):
        $total= $total+($rate*$percent/100);
      endif;
      if( is_numeric($fixed) && $fixed > 0 ):
        $total= $total+$fixed;
      endif;
      echo $total;
  elseif( $action == "new_ticket" ):
    $return = '<form class="form" action="'.site_url("admin/tickets/new").'" method="post" data-xhr="true">
        <div class="modal-body">

          <div class="form-group">
            <label class="form-group__service-name">Kullanıcı adı</label>
            <input type="text" class="form-control" name="username" value="">
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Konu</label>
            <input type="text" class="form-control" name="subject" value="">
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Mesajınız</label>
            <textarea class="form-control" name="message" rows="4"></textarea>
          </div>


        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Yeni talebi oluştur</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Yeni destek talebi"]);
	elseif( $action == "yeni_kupon" ):
    $return = '<form class="form" action="'.site_url("admin/kuponlar/new").'" method="post" data-xhr="true">
        <div class="modal-body">

          <div class="form-group">
            <label class="form-group__service-name">Kupon Kodu</label>
            <input type="text" class="form-control" name="kuponadi" value="">
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Adet</label>
            <input type="text" class="form-control" name="adet" value="">
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Tutar</label>
            <input type="text" class="form-control" name="tutar" value="">
          </div>


        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Yeni kupon oluştur</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>
          ';
    echo json_encode(["content"=>$return,"title"=>"Yeni kupon oluştur"]);
	
	
  elseif( $action == "edit_paymentmethod" && $_POST["id"] == "paytr" ):
    $id    = $_POST["id"];
    $method = $conn->prepare("SELECT * FROM payment_methods WHERE method_get=:id ");
    $method->execute(array("id"=>$id));
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extra  = json_decode($method["method_extras"],true);
    $return = '<form class="form" action="'.site_url("admin/settings/payment-methods/edit/".$id).'" method="post" data-xhr="true">

        <div class="modal-body">

          <div class="form-group">
            <label class="form-group__service-name">Method adı</label>
            <input type="text" class="form-control" readonly value="'.$method["method_name"].'">
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Görünürlük</label>
              <select class="form-control" name="method_type">
                    <option value="2"'; if( $method["method_type"] == 2 ): $return.='selected'; endif; $return.='>Aktif</option>
                    <option value="1"'; if( $method["method_type"] == 1 ): $return.='selected'; endif; $return.='>Pasif</option>
                </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Görünür adı</label>
            <input type="text" class="form-control" name="name" value="'.$extra["name"].'">
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Minimum Ödeme</label>
            <input type="text" class="form-control" name="min" value="'.$extra["min"].'">
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Maksimum Ödeme</label>
            <input type="text" class="form-control" name="max" value="'.$extra["max"].'">
          </div>

          <hr>
            <p class="card-description">
              <ul>
                <li>
                  API Geri Dönüş Adresi: <code>'; $return.=site_url("payment/".$method["method_get"]); $return.='</code>
                </li>
              </ul>
            </p>
          <hr>

          <div class="form-group">
            <label class="form-group__service-name">Merchant id</label>
            <input type="text" class="form-control" name="merchant_id" value="'.$extra["merchant_id"].'">
          </div>
          <div class="form-group">
            <label class="form-group__service-name">Merchant key</label>
            <input type="text" class="form-control" name="merchant_key" value="'.$extra["merchant_key"].'">
          </div>
          <div class="form-group">
            <label class="form-group__service-name">Merchant salt</label>
            <input type="text" class="form-control" name="merchant_salt" value="'.$extra["merchant_salt"].'">
          </div>
          <div class="form-group">
            <label class="form-group__service-name">Komisyon, %</label>
            <input type="text" class="form-control" name="fee" value="'.$extra["fee"].'">
          </div>


        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Ayarları güncelle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Ödeme methodu düzenle (Method: ".$method["method_name"].")"]);
    elseif( $action == "edit_paymentmethod" && $_POST["id"] == "paytr_havale" ):
    $id    = $_POST["id"];
    $method = $conn->prepare("SELECT * FROM payment_methods WHERE method_get=:id ");
    $method->execute(array("id"=>$id));
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extra  = json_decode($method["method_extras"],true);
    $return = '<form class="form" action="'.site_url("admin/settings/payment-methods/edit/".$id).'" method="post" data-xhr="true">

        <div class="modal-body">

          <div class="form-group">
            <label class="form-group__service-name">Method adı</label>
            <input type="text" class="form-control" readonly value="'.$method["method_name"].'">
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Görünürlük</label>
              <select class="form-control" name="method_type">
                    <option value="2"'; if( $method["method_type"] == 2 ): $return.='selected'; endif; $return.='>Aktif</option>
                    <option value="1"'; if( $method["method_type"] == 1 ): $return.='selected'; endif; $return.='>Pasif</option>
                </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Görünür adı</label>
            <input type="text" class="form-control" name="name" value="'.$extra["name"].'">
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Minimum Ödeme</label>
            <input type="text" class="form-control" name="min" value="'.$extra["min"].'">
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Maksimum Ödeme</label>
            <input type="text" class="form-control" name="max" value="'.$extra["max"].'">
          </div>

          <hr>
            <p class="card-description">
              <ul>
                <li>
                  API Geri Dönüş Adresi: <code>'; $return.=site_url("payment/paytr"); $return.='</code>
                </li>
              </ul>
            </p>
          <hr>

          <div class="form-group">
            <label class="form-group__service-name">Merchant id</label>
            <input type="text" class="form-control" name="merchant_id" value="'.$extra["merchant_id"].'">
          </div>
          <div class="form-group">
            <label class="form-group__service-name">Merchant key</label>
            <input type="text" class="form-control" name="merchant_key" value="'.$extra["merchant_key"].'">
          </div>
          <div class="form-group">
            <label class="form-group__service-name">Merchant salt</label>
            <input type="text" class="form-control" name="merchant_salt" value="'.$extra["merchant_salt"].'">
          </div>
          <div class="form-group">
            <label class="form-group__service-name">Komisyon, %</label>
            <input type="text" class="form-control" name="fee" value="'.$extra["fee"].'">
          </div>


        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Ayarları güncelle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Ödeme methodu düzenle (Method: ".$method["method_name"].")"]);
  elseif( $action == "edit_paymentmethod" && $_POST["id"] == "paywant" ):
    $id    = $_POST["id"];
    $method = $conn->prepare("SELECT * FROM payment_methods WHERE method_get=:id ");
    $method->execute(array("id"=>$id));
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extra  = json_decode($method["method_extras"],true);
    $return = '<form class="form" action="'.site_url("admin/settings/payment-methods/edit/".$id).'" method="post" data-xhr="true">

        <div class="modal-body">

          <div class="form-group">
            <label class="form-group__service-name">Method adı</label>
            <input type="text" class="form-control" readonly value="'.$method["method_name"].'">
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Görünürlük</label>
              <select class="form-control" name="method_type">
                    <option value="2"'; if( $method["method_type"] == 2 ): $return.='selected'; endif; $return.='>Aktif</option>
                    <option value="1"'; if( $method["method_type"] == 1 ): $return.='selected'; endif; $return.='>Pasif</option>
                </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Görünür adı</label>
            <input type="text" class="form-control" name="name" value="'.$extra["name"].'">
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Minimum Ödeme</label>
            <input type="text" class="form-control" name="min" value="'.$extra["min"].'">
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Maksimum Ödeme</label>
            <input type="text" class="form-control" name="max" value="'.$extra["max"].'">
          </div>

          <hr>
            <p class="card-description">
              <ul>
                <li>
                  API Geri Dönüş Adresi: <code>'; $return.=site_url("payment/".$method["method_get"]); $return.='</code>
                </li>
              </ul>
            </p>
          <hr>

          <div class="form-group">
            <label class="form-group__service-name">apiKey</label>
            <input type="text" class="form-control" name="apiKey" value="'.$extra["apiKey"].'">
          </div>
          <div class="form-group">
            <label class="form-group__service-name">apiSecret</label>
            <input type="text" class="form-control" name="apiSecret" value="'.$extra["apiSecret"].'">
          </div>
          <div class="form-group">
            <label class="form-group__service-name">Komisyon, %</label>
            <input type="text" class="form-control" name="fee" value="'.$extra["fee"].'">
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Paywant Komison</label>
              <select class="form-control" name="commissionType">
                    <option value="2"'; if( $extra["commissionType"] == 2 ): $return.='selected'; endif; $return.='>Kullanıcıya yansıt</option>
                    <option value="1"'; if( $extra["commissionType"] == 1 ): $return.='selected'; endif; $return.='>Kullanıcıya yansıtma</option>
                </select>
            </div>
          </div>

          <div class="form-group">
            <label>Ödeme Yöntemleri</label>
              <div class="form-group col-md-12">
                  <div class="row">
                    <label class="checkbox-inline col-md-3">
                      <input type="checkbox" class="access" name="payment_type[]" value="1"'; if( in_array(1,$extra["payment_type"]) ): $return.=' checked'; endif; $return.='> Mobil Ödeme
                    </label>
                    <label class="checkbox-inline col-md-3">
                      <input type="checkbox" class="access" name="payment_type[]" value="2"'; if( in_array(2,$extra["payment_type"]) ): $return.=' checked'; endif; $return.='> Kredi/Banka Kartı
                    </label>
                    <label class="checkbox-inline col-md-3">
                      <input type="checkbox" class="access" name="payment_type[]" value="3"'; if( in_array(3,$extra["payment_type"]) ): $return.=' checked'; endif; $return.='> Havale/EFT
                    </label>
                  </div>
              </div>
            </div>


        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Ayarları güncelle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Ödeme methodu düzenle (Method: ".$method["method_name"].")"]);
  elseif( $action == "edit_paymentmethod" && $_POST["id"] == "buypayer" ):
    $id    = $_POST["id"];
    $method = $conn->prepare("SELECT * FROM payment_methods WHERE method_get=:id ");
    $method->execute(array("id"=>$id));
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extra  = json_decode($method["method_extras"],true);
    $return = '<form class="form" action="'.site_url("admin/settings/payment-methods/edit/".$id).'" method="post" data-xhr="true">

        <div class="modal-body">

          <div class="form-group">
            <label class="form-group__service-name">Method adı</label>
            <input type="text" class="form-control" readonly value="'.$method["method_name"].'">
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Görünürlük</label>
              <select class="form-control" name="method_type">
                    <option value="2"'; if( $method["method_type"] == 2 ): $return.='selected'; endif; $return.='>Aktif</option>
                    <option value="1"'; if( $method["method_type"] == 1 ): $return.='selected'; endif; $return.='>Pasif</option>
                </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Görünür adı</label>
            <input type="text" class="form-control" name="name" value="'.$extra["name"].'">
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Minimum Ödeme</label>
            <input type="text" class="form-control" name="min" value="'.$extra["min"].'">
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Maksimum Ödeme</label>
            <input type="text" class="form-control" name="max" value="'.$extra["max"].'">
          </div>

          <hr>
            <p class="card-description">
              <ul>
                <li>
                  API Geri Dönüş Adresi: <code>'; $return.=site_url("payment/".$method["method_get"]); $return.='</code>
                </li>
              </ul>
            </p>
          <hr>

          <div class="form-group">
            <label class="form-group__service-name">Mağaza No</label>
            <input type="text" class="form-control" name="magaza_no" value="'.$extra["magaza_no"].'">
          </div>
          <div class="form-group">
            <label class="form-group__service-name">Mağaza Güvenlik Kodu</label>
            <input type="text" class="form-control" name="magaza_secret" value="'.$extra["magaza_secret"].'">
          </div>
          <div class="form-group">
            <label class="form-group__service-name">Mağaza Mail Adresi</label>
            <input type="text" class="form-control" name="magaza_mail" value="'.$extra["magaza_mail"].'">
          </div>
          <div class="form-group">
            <label class="form-group__service-name">Komisyon, %</label>
            <input type="text" class="form-control" name="fee" value="'.$extra["fee"].'">
          </div>


        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Ayarları güncelle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Ödeme methodu düzenle (Method: ".$method["method_name"].")"]);
  elseif( $action == "edit_paymentmethod" && $_POST["id"] == "shopier" ):
    $id    = $_POST["id"];
    $method = $conn->prepare("SELECT * FROM payment_methods WHERE method_get=:id ");
    $method->execute(array("id"=>$id));
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extra  = json_decode($method["method_extras"],true);
    $return = '<form class="form" action="'.site_url("admin/settings/payment-methods/edit/".$id).'" method="post" data-xhr="true">

        <div class="modal-body">

          <div class="form-group">
            <label class="form-group__service-name">Method adı</label>
            <input type="text" class="form-control" readonly value="'.$method["method_name"].'">
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Görünürlük</label>
              <select class="form-control" name="method_type">
                    <option value="2"'; if( $method["method_type"] == 2 ): $return.='selected'; endif; $return.='>Aktif</option>
                    <option value="1"'; if( $method["method_type"] == 1 ): $return.='selected'; endif; $return.='>Pasif</option>
                </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Görünür adı</label>
            <input type="text" class="form-control" name="name" value="'.$extra["name"].'">
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Minimum Ödeme</label>
            <input type="text" class="form-control" name="min" value="'.$extra["min"].'">
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Maksimum Ödeme</label>
            <input type="text" class="form-control" name="max" value="'.$extra["max"].'">
          </div>

          <hr>
            <p class="card-description">
              <ul>
                <li>
                  API Geri Dönüş Adresi: <code>'; $return.=site_url("payment/".$method["method_get"]); $return.='</code>
                </li>
              </ul>
            </p>
          <hr>

          <div class="form-group">
            <label class="form-group__service-name">apiKey</label>
            <input type="text" class="form-control" name="apiKey" value="'.$extra["apiKey"].'">
          </div>
          <div class="form-group">
            <label class="form-group__service-name">apiSecret</label>
            <input type="text" class="form-control" name="apiSecret" value="'.$extra["apiSecret"].'">
          </div>
          <div class="form-group">
          <label>Geri dönüş</label>
            <select class="form-control" name="website_index">
                  <option value="1"'; if( $extra["website_index"] == 1 ): $return.='selected'; endif; $return.='>Geri dönüş URL (1)</option>
                  <option value="2"'; if( $extra["website_index"] == 2 ): $return.='selected'; endif; $return.='>Geri dönüş URL (2)</option>
                  <option value="3"'; if( $extra["website_index"] == 3 ): $return.='selected'; endif; $return.='>Geri dönüş URL (3)</option>
                  <option value="4"'; if( $extra["website_index"] == 4 ): $return.='selected'; endif; $return.='>Geri dönüş URL (4)</option>
                  <option value="5"'; if( $extra["website_index"] == 5 ): $return.='selected'; endif; $return.='>Geri dönüş URL (5)</option>
              </select>
          </div>
          <div class="form-group">
          <label>İşlem ücreti (0,49 TL)</label>
            <select class="form-control" name="processing_fee">
                  <option value="1"'; if( $extra["processing_fee"] == 1 ): $return.='selected'; endif; $return.='>Yansıt</option>
                  <option value="0"'; if( $extra["processing_fee"] == 0 ): $return.='selected'; endif; $return.='>Yansıtma</option>
              </select>
          </div>
          <div class="form-group">
            <label class="form-group__service-name">Komisyon, %</label>
            <input type="text" class="form-control" name="fee" value="'.$extra["fee"].'">
          </div>


        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Ayarları güncelle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Ödeme methodu düzenle (Method: ".$method["method_name"].")"]);
  elseif( $action == "edit_paymentmethod" && $_POST["id"] == "havale-eft" ):
    $id    = $_POST["id"];
    $method = $conn->prepare("SELECT * FROM payment_methods WHERE method_get=:id ");
    $method->execute(array("id"=>$id));
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extra  = json_decode($method["method_extras"],true);
    $return = '<form class="form" action="'.site_url("admin/settings/payment-methods/edit/".$id).'" method="post" data-xhr="true">

        <div class="modal-body">

          <div class="form-group">
            <label class="form-group__service-name">Method adı</label>
            <input type="text" class="form-control" readonly value="'.$method["method_name"].'">
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Görünürlük</label>
              <select class="form-control" name="method_type">
                    <option value="2"'; if( $method["method_type"] == 2 ): $return.='selected'; endif; $return.='>Aktif</option>
                    <option value="1"'; if( $method["method_type"] == 1 ): $return.='selected'; endif; $return.='>Pasif</option>
                </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Görünür adı</label>
            <input type="text" class="form-control" name="name" value="'.$extra["name"].'">
          </div>


        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Ayarları güncelle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Ödeme methodu düzenle (Method: ".$method["method_name"].")"]);
  elseif( $action == "new_bankaccount" ):
    $return = '<form class="form" action="'.site_url("admin/settings/bank-accounts/new").'" method="post" data-xhr="true">
        <div class="modal-body">

          <div class="form-group">
            <label class="form-group">Banka adı</label>
            <input type="text" name="bank_name" class="form-control" value="">
          </div>

          <div class="form-group">
            <label class="form-group">Alıcı adı</label>
            <input type="text" name="bank_alici" class="form-control" value="">
          </div>

          <div class="form-group">
            <label class="form-group">Şube no</label>
            <input type="text" name="bank_sube" class="form-control" value="">
          </div>

          <div class="form-group">
            <label class="form-group">Hesap no</label>
            <input type="text" name="bank_hesap" class="form-control" value="">
          </div>

          <div class="form-group">
            <label class="form-group">IBAN</label>
            <input type="text" name="bank_iban" class="form-control" value="">
          </div>


        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Yeni banka hesabını ekle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Yeni banka hesabı"]);
  elseif( $action == "edit_bankaccount" ):
    $id       = $_POST["id"];
    $bank = $conn->prepare("SELECT * FROM bank_accounts WHERE id=:id ");
    $bank->execute(array("id"=>$id));
    $bank = $bank->fetch(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="'.site_url("admin/settings/bank-accounts/edit/".$id).'" method="post" data-xhr="true">
        <div class="modal-body">

          <div class="form-group">
            <label class="form-group">Banka adı</label>
            <input type="text" name="bank_name" class="form-control" value="'.$bank["bank_name"].'">
          </div>

          <div class="form-group">
            <label class="form-group">Alıcı adı</label>
            <input type="text" name="bank_alici" class="form-control" value="'.$bank["bank_alici"].'">
          </div>

          <div class="form-group">
            <label class="form-group">Şube no</label>
            <input type="text" name="bank_sube" class="form-control" value="'.$bank["bank_sube"].'">
          </div>

          <div class="form-group">
            <label class="form-group">Hesap no</label>
            <input type="text" name="bank_hesap" class="form-control" value="'.$bank["bank_hesap"].'">
          </div>

          <div class="form-group">
            <label class="form-group">IBAN</label>
            <input type="text" name="bank_iban" class="form-control" value="'.$bank["bank_iban"].'">
          </div>


        </div>

        <div class="modal-footer">
          <a id="delete-row" data-url="'.site_url("admin/settings/bank-accounts/delete/".$bank["id"]).'" class="btn btn-danger pull-left">Hesabı kaldır</a>
          <button type="submit" class="btn btn-primary">Banka hesabını güncelle</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
        </div>
        </form>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        <script>
        $("#delete-row").click(function(){
          var action = $(this).attr("data-url");
          swal({
            title: "Silmek istediğinizden emin misiniz?",
            text: "Eğer onaylarsanız bu içerik silinecek, bunu geri getirmek mümkün olmayabilir.",
            icon: "warning",
            buttons: true,
            dangerMode: true,
            buttons: ["Vazgeç", "Evet, eminim!"],
          })
          .then((willDelete) => {
            if (willDelete) {
              $.ajax({
                url:  action,
                type: "GET",
                dataType: "json",
                cache: false,
                contentType: false,
                processData: false
              })
              .done(function(result){
                if( result.s == "error" ){
                  var heading = "Failed";
                }else{
                  var heading = "Success";
                }
                  $.toast({
                      heading: heading,
                      text: result.m,
                      icon: result.s,
                      loader: true,
                      loaderBg: "#9EC600"
                  });
                  if (result.r!=null) {
                    if( result.time ==null ){ result.time = 3; }
                    setTimeout(function(){
                      window.location.href  = result.r;
                    },result.time*1000);
                  }
              })
              .fail(function(){
                $.toast({
                    heading: "Failed",
                    text: "İstek gerçekleştirilemedi",
                    icon: "error",
                    loader: true,
                    loaderBg: "#9EC600"
                });
              });
              /* İçerik silinmesi onaylandı */
            } else {
              $.toast({
                  heading: "Failed",
                  text: "Silinme istediği reddedildi",
                  icon: "error",
                  loader: true,
                  loaderBg: "#9EC600"
              });
            }
          });
        });
        </script>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Banka hesabını güncelle"]);
  elseif( $action == "new_paymentbonus" ):
    $methodList = $conn->prepare("SELECT * FROM payment_methods WHERE id!='4' ");
    $methodList->execute(array());
    $methodList = $methodList->fetchAll(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="'.site_url("admin/settings/payment-bonuses/new").'" method="post" data-xhr="true">
        <div class="modal-body">

          <div class="form-group">
          <label>Method</label>
            <select class="form-control" name="method_type">';
                  foreach ($methodList as $method):
                    $return.='<option value="'.$method["id"].'">'.$method["method_name"].'</option>';
                  endforeach;
              $return.='</select>
          </div>

          <div class="form-group">
            <label class="form-group">Bonus tutarı (%)</label>
            <input type="text" name="amount" class="form-control" value="">
          </div>

          <div class="form-group">
            <label class="form-group">İtibaren (<i class="fa fa-try"></i>)</label>
            <input type="text" name="from" class="form-control" value="">
          </div>

        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Yeni bonusu ekle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Yeni ödeme bonusu"]);
  elseif( $action == "edit_paymentbonus" ):
    $id         = $_POST["id"];
    $bonus      = $conn->prepare("SELECT * FROM payments_bonus WHERE bonus_id=:id ");
    $bonus      ->execute(array("id"=>$id));
    $bonus      = $bonus->fetch(PDO::FETCH_ASSOC);
    $methodList = $conn->prepare("SELECT * FROM payment_methods  WHERE id!='4' ");
    $methodList->execute(array());
    $methodList = $methodList->fetchAll(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="'.site_url("admin/settings/payment-bonuses/edit/".$id).'" method="post" data-xhr="true">
        <div class="modal-body">

          <div class="form-group">
          <label>Method</label>
            <select class="form-control" name="method_type">';
                  foreach ($methodList as $method):
                    $return.='<option value="'.$method["id"].'"'; if( $bonus["bonus_method"] == $method["id"] ): $return.='selected'; endif; $return.='>'.$method["method_name"].'</option>';
                  endforeach;
              $return.='</select>
          </div>

          <div class="form-group">
            <label class="form-group">Bonus tutarı (%)</label>
            <input type="text" name="amount" class="form-control" value="'.$bonus["bonus_amount"].'">
          </div>

          <div class="form-group">
            <label class="form-group">İtibaren (<i class="fa fa-try"></i>)</label>
            <input type="text" name="from" class="form-control" value="'.$bonus["bonus_from"].'">
          </div>

        </div>

          <div class="modal-footer">
            <a id="delete-row" data-url="'.site_url("admin/settings/payment-bonuses/delete/".$bonus["bonus_id"]).'" class="btn btn-danger pull-left">Bonusu kaldır</a>
            <button type="submit" class="btn btn-primary">Bonusu güncelle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>
          <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
          <script>
          $("#delete-row").click(function(){
            var action = $(this).attr("data-url");
            swal({
              title: "Silmek istediğinizden emin misiniz?",
              text: "Eğer onaylarsanız bu içerik silinecek, bunu geri getirmek mümkün olmayabilir.",
              icon: "warning",
              buttons: true,
              dangerMode: true,
              buttons: ["Vazgeç", "Evet, eminim!"],
            })
            .then((willDelete) => {
              if (willDelete) {
                $.ajax({
                  url:  action,
                  type: "GET",
                  dataType: "json",
                  cache: false,
                  contentType: false,
                  processData: false
                })
                .done(function(result){
                  if( result.s == "error" ){
                    var heading = "Failed";
                  }else{
                    var heading = "Success";
                  }
                    $.toast({
                        heading: heading,
                        text: result.m,
                        icon: result.s,
                        loader: true,
                        loaderBg: "#9EC600"
                    });
                    if (result.r!=null) {
                      if( result.time ==null ){ result.time = 3; }
                      setTimeout(function(){
                        window.location.href  = result.r;
                      },result.time*1000);
                    }
                })
                .fail(function(){
                  $.toast({
                      heading: "Failed",
                      text: "İstek gerçekleştirilemedi",
                      icon: "error",
                      loader: true,
                      loaderBg: "#9EC600"
                  });
                });
                /* İçerik silinmesi onaylandı */
              } else {
                $.toast({
                    heading: "Failed",
                    text: "Silinme istediği reddedildi",
                    icon: "error",
                    loader: true,
                    loaderBg: "#9EC600"
                });
              }
            });
          });
          </script>
          ';
    echo json_encode(["content"=>$return,"title"=>"Ödeme bonusu güncelle"]);
  elseif( $action == "new_provider" ):
    $return = '<form class="form" action="'.site_url("admin/settings/providers/new").'" method="post" data-xhr="true">

        <div class="modal-body">

          <div class="form-group">
            <label class="form-group__service-name">Sağlayıcı Adı</label>
            <input type="text" class="form-control" name="name" value="">
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Sağlayıcı API Tipi</label>
              <select class="form-control" name="type">
                    <option value="1">Standart</option>
                    <option value="3">Socials.media</option>
                </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-group__service-name">API URL</label>
            <input type="text" class="form-control" name="url" value="">
          </div>


          <div class="form-group">
            <label class="form-group__service-name">API Key</label>
            <input type="text" class="form-control" name="apikey" value="">
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Bakiye Limit</label>
            <input type="text" class="form-control" name="limit" value="">
          </div>

          <hr>
            <p class="card-description">
              <ul>
                <li>
                  Bakiye Limit: <code>Bakiyeniz bu tutarın altına düşerse bildirim alacaksınız.</code>
                </li>
              </ul>
            </p>
          <hr>
        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Sağlayıcıyı ekle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Yeni sağlayıcı ekle"]);
  elseif( $action == "edit_provider" ):
    $id         = $_POST["id"];
    $provider   = $conn->prepare("SELECT * FROM service_api WHERE id=:id ");
    $provider   ->execute(array("id"=>$id));
    $provider   = $provider->fetch(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="'.site_url("admin/settings/providers/edit/".$id).'" method="post" data-xhr="true">

        <div class="modal-body">

          <div class="form-group">
            <label class="form-group__service-name">Sağlayıcı Adı</label>
            <input type="text" class="form-control" name="name" value="'.$provider["api_name"].'">
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Sağlayıcı API Tipi</label>
              <select class="form-control" name="type">
                    <option value="1"'; if( $provider["api_type"] == 1 ): $return.="selected"; endif; $return.='>Standart</option>
                    <option value="3"'; if( $provider["api_type"] == 3 ): $return.="selected"; endif; $return.='>Socials.media</option>
                </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-group__service-name">API URL</label>
            <input type="text" class="form-control" name="url" value="'.$provider["api_url"].'">
          </div>

          <div class="form-group">
            <label class="form-group__service-name">API Key</label>
            <input type="text" class="form-control" name="apikey" value="'.$provider["api_key"].'">
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Bakiye Limit</label>
            <input type="text" class="form-control" name="limit" value="'.$provider["api_limit"].'">
          </div>

          <hr>
            <p class="card-description">
              <ul>
                <li>
                  Bakiye Limit: <code>Bakiyeniz bu tutarın altına düşerse bildirim alacaksınız.</code>
                </li>
              </ul>
            </p>
          <hr>
        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Sağlayıcıyı düzenle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Sağlayıcı düzenle (".$provider["api_name"].") "]);
   
  elseif( $action == "export_user" ):
    $return = '<form class="form" action="'.site_url("admin/clients/export").'" method="post">
        <div class="modal-body">

          <div class="service-mode__block">
            <div class="form-group">
            <label>Üyelik Statu</label>
              <select class="form-control" name="client_status">
                    <option value="all">Tüm üyeler</option>
                    <option value="1">Pasif</option>
                    <option value="2">Aktif</option>
                </select>
            </div>
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Email Statu</label>
              <select class="form-control" name="email_status">
                    <option value="all">Tüm üyeler</option>
                    <option value="1">Onaysız</option>
                    <option value="2">Onaylı</option>
                </select>
            </div>
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Format</label>
              <select class="form-control" name="format">
                    <option value="json">JSON</option>
                </select>
            </div>
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Üye bilgileri</label>
              <div class="form-group">
                  <label class="checkbox-inline">
                    <input type="checkbox" class="access" name="exportcolumn[client_id]" checked value="1"> ID
                  </label>
                  <label class="checkbox-inline">
                    <input type="checkbox" class="access" name="exportcolumn[email]" checked value="1"> Email
                  </label>
                  <label class="checkbox-inline">
                    <input type="checkbox" class="access" name="exportcolumn[name]" checked value="1"> Ad Soyad
                  </label>
                  <label class="checkbox-inline">
                    <input type="checkbox" class="access" name="exportcolumn[username]" checked value="1"> Kullanıcı adı
                  </label>
                  <label class="checkbox-inline">
                    <input type="checkbox" class="access" name="exportcolumn[telephone]" checked value="1"> Telefon numarası
                  </label>
                  <label class="checkbox-inline">
                    <input type="checkbox" class="access" name="exportcolumn[balance]" checked value="1"> Bakiye
                  </label>
                  <label class="checkbox-inline">
                    <input type="checkbox" class="access" name="exportcolumn[spent]" checked value="1"> Harcama
                  </label>
                  <label class="checkbox-inline">
                    <input type="checkbox" class="access" name="exportcolumn[register_date]" checked value="1"> Kayıt tarihi
                  </label>
                  <label class="checkbox-inline">
                    <input type="checkbox" class="access" name="exportcolumn[login_date]" checked value="1"> Son giriş tarihi
                  </label>
              </div>
            </div>
          </div>


        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Kullanıcıları yedekle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Kullanıcıları yedekle"]);
  elseif( $action == "all_numbers" ):
    $rows   = $conn->prepare("SELECT * FROM clients");
    $rows->execute(array());
    $rows   = $rows->fetchAll(PDO::FETCH_ASSOC);
    $numbers= "";
    $emails = "";
      foreach ($rows as $row):
        if( $row["telephone"] ): $numbers.=$row["telephone"]."\n"; endif;
        $emails.=$row["email"]."\n";
      endforeach;
    $return = '<form>
        <div class="modal-body">

          <div class="service-mode__block">
            <div class="form-group">
            <label>Üye Telefon Numaraları</label>
              <textarea class="form-control" rows="8" readonly>'.$numbers.'</textarea>
            </div>
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Üye E-mail Adresleri</label>
              <textarea class="form-control" rows="8" readonly>'.$emails.'</textarea>
            </div>
          </div>


        </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Kullanıcı Bilgileri"]);

	
	elseif( $action == "details" ):
	
	$toplamkullanici      = $conn->prepare("SELECT * FROM clients");
    $toplamkullanici     -> execute();
    $toplamkullanici      = $toplamkullanici->rowCount();
	
	//Toplam Kullanılabilir Bakiye
	$query = $conn->query("SELECT sum(balance) as toplambakiye FROM clients")->fetch(PDO::FETCH_ASSOC);
	
	//Toplam Harcanan Bakiye
	$query2 = $conn->query("SELECT sum(order_charge) as order_charge FROM orders")->fetch(PDO::FETCH_ASSOC);
	
	//Negatif Bakiyeli Kullanıcılar
	$negatifbakiye      = $conn->prepare("SELECT * FROM clients where balance < 0");
    $negatifbakiye     -> execute();
    $negatifbakiye      = $negatifbakiye->rowCount();
	
	//Bakiyesi Olmayan
	$bakiyesiz      = $conn->prepare("SELECT * FROM clients where balance = 0");
    $bakiyesiz     -> execute();
    $bakiyesiz      = $bakiyesiz->rowCount();
    
   
    $return = '<form>
        <div class="modal-body">
		
          <div class="service-mode__block">
            <div class="form-group">
            <label>Toplam Kullanıcı : '.$toplamkullanici.'</label>
            </div>
          </div>
		  
		  <div class="service-mode__block">
            <div class="form-group">
            <label>Toplam Kullanılabilir Bakiye : '.$query['toplambakiye'].'</label>
            </div>
          </div>
		  
		  <div class="service-mode__block">
            <div class="form-group">
            <label>Toplam Harcanan Bakiye : '.$query2['order_charge'].'</label>
            </div>
          </div>
		  
		  <div class="service-mode__block">
            <div class="form-group">
            <label>Negatif Bakiyeli Kullanıcı : '.$negatifbakiye.'</label>
            </div>
          </div>
		  
		  <div class="service-mode__block">
            <div class="form-group">
            <label>Bakiyesi Sıfır Olan Kullanıcı : '.$bakiyesiz.'</label>
            </div>
          </div>
		  

        </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Detaylar"]);
  elseif( $action ==  "price_user" ):
    $id     = $_POST["id"];
    $price  = $conn->prepare("SELECT *,services.service_id as serviceid,services.service_price as price,clients_price.service_price as clientprice FROM services LEFT JOIN clients_price ON clients_price.service_id=services.service_id && clients_price.client_id=:id ");
    $price -> execute(array("id"=>$id));
    $price  = $price->fetchAll(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="'.site_url("admin/clients/price/".$id).'" method="post" data-xhr="true">
        <div class="modal-body">

        <div class="services-import__body">
               <div>
                  <div class="services-import__list-wrap services-import__list-active">
                     <div class="services-import__scroll-wrap">
                        <span>
                             <div class="services-import__packages">
                                <ul>';
                                  foreach ($price as $row):
                                    $return.='<li id="service-'.$row["serviceid"].'">
                                     <label>
                                        '.$row["serviceid"].' - '.$row["service_name"].'
                                        <span class="services-import__packages-price-edit" >
                                           <div class="services-import__packages-price-lock"  data-id="servicedelete-'.$row["serviceid"].'" data-service="'.$row["serviceid"].'">
                                             <span class="fa fa-trash"></span>
                                           </div>
                                           <input type="text" class="services-import__price" name="price['.$row["serviceid"].']" value="'.$row["clientprice"].'">
                                           <span class="services-import__provider-price">'.$row["price"].'</span>
                                        </span>
                                     </label>
                                    </li>';
                                  endforeach;
                                $return.='</ul>
                             </div>
                          </span></div>
                  </div>
               </div>
            </div>
            <script>

              $(\'[data-id^="servicedelete-"]\').click(function(){
                var id        = $(this).attr("data-service");
                $("[name=\'price["+id+"]\']").val("");
                //$("ul > li#service-"+id).remove();
              });

            </script>

        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Ayarları güncelle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
        echo json_encode(["content"=>$return,"title"=>"Özel Fiyatlandırma"]);
  elseif( $action == "order_errors" ):
    $id     = $_POST["id"];
    $row    = $conn->prepare("SELECT * FROM orders WHERE order_id=:id ");
    $row ->execute(array("id"=>$id));
    $row    = $row->fetch(PDO::FETCH_ASSOC);
    $errors = json_decode($row["order_error"]);
    $return = '<form>
        <div class="modal-body">

          <div class="service-mode__block">
            <div class="form-group">
            <label>Sağlayıcıdan gelen bilgi</label>
              <textarea class="form-control" rows="8" readonly>'; $return.=print_r($errors,true); $return.='</textarea>
            </div>
          </div>


        </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Hata detayları (ID: ".$row["order_id"].") "]);
  elseif( $action == "order_details" ):
    $id     = $_POST["id"];
    $row    = $conn->prepare("SELECT * FROM orders WHERE order_id=:id ");
    $row ->execute(array("id"=>$id));
    $row    = $row->fetch(PDO::FETCH_ASSOC);
    $detail = json_decode($row["order_detail"]);
    $return = '<form>
        <div class="modal-body">

          <div class="service-mode__block">
            <div class="form-group">
            <label>Sağlayıcıdan gelen bilgi</label>
              <textarea class="form-control" rows="8" readonly>'; $return.=print_r($detail,true); $return.='</textarea>
            </div>
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Sipariş ID</label>
              <input class="form-control" value="'.$row["api_orderid"].'" readonly>
            </div>
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Son güncelleme</label>
              <input class="form-control" value="'.$row["last_check"].'" readonly>
            </div>
          </div>


        </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Sipariş detayları (ID: ".$row["order_id"].") "]);
  elseif( $action == "order_orderurl" ):
    $id     = $_POST["id"];
    $row    = $conn->prepare("SELECT * FROM orders WHERE order_id=:id ");
    $row ->execute(array("id"=>$id));
    $row    = $row->fetch(PDO::FETCH_ASSOC);
    $detail = json_decode($row["order_detail"]);
    $return = '<form class="form" action="'.site_url("admin/orders/set_orderurl/".$id).'" method="post">
        <div class="modal-body">

          <div class="service-mode__block">
            <div class="form-group">
            <label>Sipariş Bağlantısı</label>
              <input class="form-control" value="'.$row["order_url"].'" name="url">
            </div>
          </div>


        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Ayarları güncelle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Sipariş detayları (ID: ".$row["order_id"].") "]);
  elseif( $action == "order_startcount" ):
    $id     = $_POST["id"];
    $row    = $conn->prepare("SELECT * FROM orders WHERE order_id=:id ");
    $row ->execute(array("id"=>$id));
    $row    = $row->fetch(PDO::FETCH_ASSOC);
    $detail = json_decode($row["order_detail"]);
    $return = '<form class="form" action="'.site_url("admin/orders/set_startcount/".$id).'" method="post">
        <div class="modal-body">

          <div class="service-mode__block">
            <div class="form-group">
            <label>Başlangıç sayısı</label>
              <input class="form-control" value="'.$row["order_start"].'" name="start">
            </div>
          </div>


        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Ayarları güncelle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Sipariş detayları (ID: ".$row["order_id"].") "]);
  elseif( $action == "order_partial" ):
    $id     = $_POST["id"];
    $row    = $conn->prepare("SELECT * FROM orders WHERE order_id=:id ");
    $row ->execute(array("id"=>$id));
    $row    = $row->fetch(PDO::FETCH_ASSOC);
    $detail = json_decode($row["order_detail"]);
    $return = '<form class="form" action="'.site_url("admin/orders/set_partial/".$id).'" method="post" data-xhr="true">
        <div class="modal-body">

          <div class="service-mode__block">
            <div class="form-group">
            <label>Gitmeyen miktar</label>
              <input class="form-control" name="remains">
            </div>
          </div>

        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Ayarları güncelle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Sipariş detayları (ID: ".$row["order_id"].") "]);
  elseif( $action == "subscriptions_expiry" ):
    $id     = $_POST["id"];
    $row    = $conn->prepare("SELECT * FROM orders WHERE order_id=:id ");
    $row ->execute(array("id"=>$id));
    $row    = $row->fetch(PDO::FETCH_ASSOC);
    $detail = json_decode($row["order_detail"]);
    $return = '<form class="form" action="'.site_url("admin/subscriptions/set_expiry/".$id).'" method="post">
        <div class="modal-body">

          <div class="service-mode__block">
            <div class="form-group">
            <label>Başlangıç sayısı</label>
              <input class="form-control datetime" value="'; if( $row["subscriptions_expiry"] != "1970-01-01" ): $return.=date("d/m/Y", strtotime($row["subscriptions_expiry"])); endif; $return.='" name="expiry">
            </div>
          </div>


        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Ayarları güncelle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>
          <link rel="stylesheet" type="text/css" href="'.site_url("public/").'datepicker/css/bootstrap-datepicker3.min.css">
          <script type="text/javascript" src="'.site_url("public/").'datepicker/js/bootstrap-datepicker.min.js"></script>
          <script type="text/javascript" src="'.site_url("public/").'datepicker/locales/bootstrap-datepicker.tr.min.js"></script>
          ';
    echo json_encode(["content"=>$return,"title"=>"Abonelik bitiş tarihi (ID: ".$row["order_id"].") "]);
  elseif( $action == "payment_bankedit" ):
    $id = $_POST["id"];
    $payment  = $conn->prepare("SELECT * FROM payments INNER JOIN bank_accounts ON bank_accounts.id=payments.payment_bank INNER JOIN clients ON clients.client_id=payments.client_id WHERE payments.payment_id=:id");
    $payment  -> execute(array("id"=>$id));
    $payment  = $payment->fetch(PDO::FETCH_ASSOC);
    $bank     = $conn->prepare("SELECT * FROM bank_accounts ");
    $bank    -> execute();
    $bank     = $bank->fetchAll(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="'.site_url("admin/payments/edit-bank/".$id).'" method="post" data-xhr="true">

        <div class="modal-body">

          <div class="service-mode__block">
            <div class="form-group">
            <label>Ödeme yapılan banka</label>
              <select class="form-control" name="bank">';
                foreach( $bank as $banka ):
                  $return.= '<option value="'.$banka["id"].'"'; if( $payment["payment_bank"] == $banka["id"] ): $return.='selected'; endif; $return.='>'.$banka["bank_name"].'</option>';
                endforeach;
                $return.='</select>
            </div>
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Ödeme durumu</label>
              <select class="form-control" '; if( $payment["payment_status"] == 3 ): $return.='disabled'; endif; $return.=' name="status">
                    <option value="1"'; if( $payment["payment_status"] == 1 ): $return.='selected'; endif; $return.='>Beklemede</option>
                    <option value="2"'; if( $payment["payment_status"] == 2 ): $return.='selected'; endif; $return.='>İptal</option>
                    <option value="3"'; if( $payment["payment_status"] == 3 ): $return.='selected'; endif; $return.='>Onaylandı</option>
                </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-group__service-name">NOT</label>
            <input type="text" class="form-control" name="note" value="'.$payment["payment_note"].'">
          </div>


        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Ayarları güncelle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Banka ödemesi düzenle (ID: ".$id.") "]);
  elseif( $action == "payment_banknew" ):
    $bank     = $conn->prepare("SELECT * FROM bank_accounts ");
    $bank    -> execute();
    $bank     = $bank->fetchAll(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="'.site_url("admin/payments/new-bank/").'" method="post" data-xhr="true">

        <div class="modal-body">


          <div class="form-group">
            <label class="form-group__service-name">Kullanıcı adı</label>
            <input type="text" class="form-control" name="username" value="">
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Tutar</label>
            <input type="text" class="form-control" name="amount" value="">
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Ödeme yapılan banka</label>
              <select class="form-control" name="bank">';
                foreach( $bank as $banka ):
                  $return.= '<option value="'.$banka["id"].'">'.$banka["bank_name"].'</option>';
                endforeach;
                $return.='</select>
            </div>
          </div>


          <div class="form-group">
            <label class="form-group__service-name">NOT</label>
            <input type="text" class="form-control" name="note" value="">
          </div>


        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Ödeme ekle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Banka ödemesi ekle "]);
  elseif( $action == "payment_edit" ):
    $id = $_POST["id"];
    $payment  = $conn->prepare("SELECT * FROM payments INNER JOIN clients ON clients.client_id=payments.client_id WHERE payments.payment_id=:id");
    $payment  -> execute(array("id"=>$id));
    $payment  = $payment->fetch(PDO::FETCH_ASSOC);
    $methods  = $conn->prepare("SELECT * FROM payment_methods WHERE id!='4' ");
    $methods  -> execute();
    $methods  = $methods->fetchAll(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="'.site_url("admin/payments/edit-online/".$id).'" method="post" data-xhr="true">

        <div class="modal-body">

          <div class="service-mode__block">
            <div class="form-group">
            <label>Ödeme yöntemi</label>
              <select class="form-control" name="method">';
                foreach( $methods as $method ):
                  $return.= '<option value="'.$method["id"].'"'; if( $payment["payment_method"] == $method["id"] ): $return.='selected'; endif; $return.='>'.$method["method_name"].'</option>';
                endforeach;
                $return.='</select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-group__service-name">NOT</label>
            <input type="text" class="form-control" name="note" value="'.$payment["payment_note"].'">
          </div>


        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Ayarları güncelle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Online ödeme düzenle (ID: ".$id.") "]);
  elseif( $action == "payment_new" ):
    $methods  = $conn->prepare("SELECT * FROM payment_methods WHERE id!='4' ");
    $methods  -> execute();
    $methods  = $methods->fetchAll(PDO::FETCH_ASSOC);
    $return = '<form class="form" action="'.site_url("admin/payments/new-online").'" method="post" data-xhr="true">

        <div class="modal-body">

          <div class="form-group">
            <label class="form-group__service-name">Kullanıcı adı</label>
            <input type="text" class="form-control" name="username" value="">
          </div>

          <div class="form-group">
            <label class="form-group__service-name">Tutar</label>
            <input type="text" class="form-control" name="amount" value="">
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Ekle/Çıkar</label>
              <select class="form-control" name="add-remove">
                <option value="add">Ekle</option>
                <option value="remove">Çıkar</option>
            </select>
            </div>
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Ödeme yöntemi</label>
              <select class="form-control" name="method">';
                foreach( $methods as $method ):
                  $return.= '<option value="'.$method["id"].'">'.$method["method_name"].'</option>';
                endforeach;
                $return.='</select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-group__service-name">NOT</label>
            <input type="text" class="form-control" name="note" value="">
          </div>


        </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Ödeme ekle</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Online ödeme ekle"]);
  elseif( $action == "payment_detail" ):
    $id     = $_POST["id"];
    $row    = $conn->prepare("SELECT * FROM payments WHERE payment_id=:id ");
    $row ->execute(array("id"=>$id));
    $row    = $row->fetch(PDO::FETCH_ASSOC);
    $detail = json_decode($row["payment_extra"]);
    $return = '<form>
        <div class="modal-body">

          <div class="service-mode__block">
            <div class="form-group">
            <label>Ödeme bilgisi</label>
              <textarea class="form-control" rows="8" readonly>'; $return.=print_r($detail,true); $return.='</textarea>
            </div>
          </div>

          <div class="service-mode__block">
            <div class="form-group">
            <label>Son güncelleme</label>
              <input class="form-control" value="'.$row["payment_update_date"].'" readonly>
            </div>
          </div>


        </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          </div>
          </form>';
    echo json_encode(["content"=>$return,"title"=>"Ödeme detayları (ID: ".$row["payment_id"].") "]);

  endif;
  
if (!$licence_control['licence']) {
	echo "$hata";
	exit();
}
