<div class="col-md-8">
  <div class="panel panel-default">
    <div class="panel-body">
      <form action="" method="post" enctype="multipart/form-data">

        <div class="form-group">
          <div class="row">
            <div class="col-md-10">
              <label for="preferenceLogo" class="control-label">Site Logo</label>
              <input type="file" name="logo" id="preferenceLogo">
            </div>
            <div class="col-md-2">
              <?php if( $settings["site_logo"] ):  ?>
                <div class="setting-block__image">
                      <img class="img-thumbnail" src="<?=$settings["site_logo"]?>">
                    <div class="setting-block__image-remove">
                      <a href="" data-toggle="modal" data-target="#confirmChange" data-href="<?=site_url("admin/settings/general/delete-logo")?>"><span class="fa fa-remove"></span></a>
                    </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="form-group">
          <div class="row">
            <div class="col-md-11">
              <label for="preferenceFavicon" class="control-label">Site Favicon</label>
              <input type="file" name="favicon" id="preferenceFavicon">
            </div>
            <div class="col-md-1">
              <?php if( $settings["favicon"] ):  ?>
                <div class="setting-block__image">
                    <img class="img-thumbnail" src="<?=$settings["favicon"]?>">
                    <div class="setting-block__image-remove">
                      <a href="" data-toggle="modal" data-target="#confirmChange" data-href="<?=site_url("admin/settings/general/delete-favicon")?>"><span class="fa fa-remove"></span></a>
                    </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
          <hr>
      
        <div class="form-group">
          <label class="control-label">Bakım Modu</label>
          <select class="form-control" name="site_maintenance">
            <option value="2" <?= $settings["site_maintenance"] == 2 ? "selected" : null; ?> >Pasif</option>
            <option value="1" <?= $settings["site_maintenance"] == 1 ? "selected" : null; ?>>Aktif</option>
          </select>
          <hr>
        </div>  <div class="form-group">
          <label class="control-label">Panel adı</label>
          <input type="text" class="form-control" name="name" value="<?=$settings["site_name"]?>">
        </div>
                      <div class="form-group">
          <label class="control-label">Site Para Birimi</label>
          <select class="form-control" name="site_currency" selected>
              <option value="<?=$settings["site_currency"]?>">Aktif Para Birimi :  <?=$settings["site_currency"]?></option>
            <option value="TRY">Türk Lirası (TRY)</option>
            <option value="USD">Dolar (USD)</option>
            <option value="EUR">Euro (EUR)</option>
          </select>
        </div>
           <div class="form-group">
          <label class="control-label">Alt Tema</label>
        
          <select class="form-control" name="site_theme_alt">
           	<option value="<?=$settings["site_theme_alt"]?>" selected>Aktif Alt Tema : <?=$settings["site_theme_alt"]?></option>
																		<option value="Bootstrap" >Bootstrap</option>
																		<option value="Cerulean" >Cerulean</option>
																		<option value="Cosmo" >Cosmo</option>
																		<option value="Flatly" >Flatly</option>
																		<option value="Journal" >Journal</option>
																		<option value="Lumen" >Lumen</option>
																		<option value="Paper" >Paper</option>
																		<option value="Readable" >Readable</option>
																		<option value="Sandstone" >Sandstone</option>
																		<option value="Slate">Slate</option>
																		<option value="Solar" >Solar</option>
																		<option value="Spacelab" >Spacelab</option>
																		<option value="Spin" >Spin</option>
																		<option value="Superhero" >Superhero</option>
																		<option value="United" >United</option>
																		<option value="Yeti" >Yeti</option>
																		<option value="Lightblue" >Light Blue</option>
																		
          </select>
        </div>  <div class="row">
           <p class="col-md-12 help-block">
                <small>Tema düzenleyici kısmından eğer <code>Classic</code> temasını aktif ettiyseniz üst taraftaki <b>Alt</b> temalardan birini seçerek <code>Classic</code> temasının diğer hallerini görebilirsiniz.</small>
          </p>
          </div>
       
        <hr>

        <div class="form-group">
          <label class="control-label">Robot kontrol <small>(Recaptcha)</small> </label>
          <select class="form-control" name="recaptcha">
            <option value="2" <?= $settings["recaptcha"] == 2 ? "selected" : null; ?> >Aktif</option>
            <option value="1" <?= $settings["recaptcha"] == 1 ? "selected" : null; ?>>Pasif</option>
          </select>
        </div>
        <div class="form-group">
          <label for="" class="control-label">Recaptcha site key</label>
          <input type="text" class="form-control" name="recaptcha_key" value="<?=$settings["recaptcha_key"]?>">
        </div>
        <div class="form-group">
          <label for="" class="control-label">Recaptcha secret key</label>
          <input type="text" class="form-control" name="recaptcha_secret" value="<?=$settings["recaptcha_secret"]?>">
        </div>
        <hr>
        <div class="row">
              <div class="col-md-12 form-group">
            <div class="alert alert-info"><strong>DİKKAT! </strong>Kur Değerleri Otomatik Bir Şekilde Güncellenmektedir, Lütfen Müdahale Etmeyiniz.</div>
            </div>
            <?php
 
$connect_web = simplexml_load_file('http://www.tcmb.gov.tr/kurlar/today.xml');
   
$usd_selling = $connect_web->Currency[0]->BanknoteSelling;
$euro_selling = $connect_web->Currency[3]->BanknoteSelling;
  
   ?>
          <div class="col-md-6 form-group">
            <label for="" class="control-label">Dolar Kur</label>
            <input type="text" class="form-control" name="dolar" value="<?php echo ''.$usd_selling.''; ?>">
          </div>
          <div class="col-md-6 form-group">
            <label for="" class="control-label">Euro Kur</label>
            <input type="text" class="form-control" name="euro" value="<?php echo ''.$euro_selling.''; ?>">
          </div>
          <p class="col-md-12 help-block">
                <small>Siparişlerden kazanç hesaplaması yapılırken kullanılacak döviz kurları.</small>
          </p>
        </div>
        <hr>
        		
		<div class="form-group">
          <label for="" class="control-label">Bronz Üye</label>
          <input type="text" class="form-control" name="bronz_statu" value="<?=$settings["bronz_statu"]?>">
        </div>
		
		<div class="form-group">
          <label for="" class="control-label">Silver Üye</label>
          <input type="text" class="form-control" name="silver_statu" value="<?=$settings["silver_statu"]?>">
        </div>
		
		<div class="form-group">
          <label for="" class="control-label">Gold Üye</label>
          <input type="text" class="form-control" name="gold_statu" value="<?=$settings["gold_statu"]?>">
        </div>
		
		<div class="form-group">
          <label for="" class="control-label">Bayi</label>
          <input type="text" class="form-control" name="bayi_statu" value="<?=$settings["bayi_statu"]?>">
        </div>
		 <p class="help block">
                <small>	Rank için üyenin toplam harcaması gereken tutarı belirlemek için sadece sayı giriniz. Örnek : 350</small>
          </p>
	
	<hr />
        <div class="row">	
          <div class="form-group col-md-4">
            <label class="control-label">Parolamı unuttum</label>
            <select class="form-control" name="resetpass">
              <option value="2" <?= $settings["resetpass_page"] == 2 ? "selected" : null; ?> >Aktif</option>
              <option value="1" <?= $settings["resetpass_page"] == 1 ? "selected" : null; ?>>Pasif</option>
            </select>
          </div>

          <div class="form-group col-md-4">
            <label class="control-label">Parolamı Telefonuma Gönder</label>
            <select class="form-control" name="resetsms">
              <option value="2" <?= $settings["resetpass_sms"] == 2 ? "selected" : null; ?> >Aktif</option>
              <option value="1" <?= $settings["resetpass_sms"] == 1 ? "selected" : null; ?>>Pasif</option>
            </select>
          </div>
          <div class="form-group col-md-4">
            <label class="control-label">Parolamı Mail Adresime Gönder</label>
            <select class="form-control" name="resetmail">
              <option value="2" <?= $settings["resetpass_email"] == 2 ? "selected" : null; ?> >Aktif</option>
              <option value="1" <?= $settings["resetpass_email"] == 1 ? "selected" : null; ?>>Pasif</option>
            </select>
          </div>
        </div>
        <hr>
        <div class="form-group">
          <label class="control-label">Destek sistemi</label>
          <select class="form-control" name="ticket_system">
            <option value="2" <?= $settings["ticket_system"] == 2 ? "selected" : null; ?> >Aktif</option>
            <option value="1" <?= $settings["ticket_system"] == 1 ? "selected" : null; ?>>Pasif</option>
          </select>
        </div>
        <div class="form-group">
          <label class="control-label">Kayıt sayfası</label>
          <select class="form-control" name="registration_page">
            <option value="2" <?= $settings["registration_page"] == 2 ? "selected" : null; ?>>Aktif</option>
            <option value="1" <?= $settings["registration_page"] == 1 ? "selected" : null; ?>>Pasif</option>
          </select>
        </div>
        <div class="form-group">
          <label class="control-label">Servis Listesi</label>
          <select class="form-control" name="service_list">
            <option value="2" <?= $settings["service_list"] == 2 ? "selected" : null; ?>>Herkese aktif</option>
            <option value="1" <?= $settings["service_list"] == 1 ? "selected" : null; ?>>Sadece üyeler</option>
          </select>
        </div>
        <div class="form-group">
          <label class="control-label">Servis hız göstergesi</label>
          <select class="form-control" name="service_speed">
            <option value="2" <?= $settings["service_speed"] == 2 ? "selected" : null; ?>>Aktif</option>
            <option value="1" <?= $settings["service_speed"] == 1 ? "selected" : null; ?>>Pasif</option>
          </select>
        </div>
        <hr />
        <div class="form-group">
          <label class="control-label">Header kodları</label>
          <textarea class="form-control" rows="7" name="custom_header" placeholder='<style type="text/css">...</style>'><?=$settings["custom_header"]?></textarea>
        </div>
        <div class="form-group">
          <label>Footer kodları</label>
          <textarea class="form-control" rows="7" name="custom_footer" placeholder='<script>...</script>'><?=$settings["custom_footer"]?></textarea>
        </div>
		<hr>
        <button type="submit" class="btn btn-primary">Ayarları Güncelle</button>
      </form>
    </div>
  </div>
</div>

<div class="modal modal-center fade" id="confirmChange" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static">
 <div class="modal-dialog modal-dialog-center" role="document">
   <div class="modal-content">
     <div class="modal-body text-center">
       <h4>Emin misiniz?</h4>
       <div align="center">
         <a class="btn btn-primary" href="" id="confirmYes">Evet</a>
         <button type="button" class="btn btn-default" data-dismiss="modal">Hayır</button>
       </div>
     </div>
   </div>
 </div>
</div>
