<div class="col-md-8">
  <div class="panel panel-default">
    <div class="panel-body">
        
      <form action="" method="post" enctype="multipart/form-data">

        <div class="row">
          <div class="col-md-6 form-group">
            <label class="control-label">API balance notification</label>
            <select class="form-control" name="alert_apibalance">
              <option value="2" <?= $settings["alert_apibalance"] == 2 ? "selected" : null; ?> >Active</option>
              <option value="1" <?= $settings["alert_apibalance"] == 1 ? "selected" : null; ?>>Passive</option>
            </select>
          </div>

          <div class="col-md-6 form-group">
            <label class="control-label">New support ticket notification</label>
            <select class="form-control" name="alert_newticket">
              <option value="2" <?= $settings["alert_newticket"] == 2 ? "selected" : null; ?> >Active</option>
              <option value="1" <?= $settings["alert_newticket"] == 1 ? "selected" : null; ?>>Passive</option>
            </select>
          </div>

          <div class="col-md-6 form-group">
            <label class="control-label">New manual service order notification</label>
            <select class="form-control" name="alert_newmanuelservice">
              <option value="2" <?= $settings["alert_newmanuelservice"] == 2 ? "selected" : null; ?> >Active</option>
              <option value="1" <?= $settings["alert_newmanuelservice"] == 1 ? "selected" : null; ?>>Passive</option>
            </select>
          </div>
          <div class="col-md-6 form-group">
            <label class="control-label">New bank payment notification</label>
            <select class="form-control" name="alert_newbankpayment">
              <option value="2" <?= $settings["alert_newbankpayment"] == 2 ? "selected" : null; ?> >Active</option>
              <option value="1" <?= $settings["alert_newbankpayment"] == 1 ? "selected" : null; ?>>Passive</option>
            </select>
          </div>
          <div class="col-md-12 form-group">
            <label class="control-label">Service provider changed information</label>
            <select class="form-control" name="serviceapialert">
              <option value="2" <?= $settings["alert_serviceapialert"] == 2 ? "selected" : null; ?> >Active</option>
              <option value="1" <?= $settings["alert_serviceapialert"] == 1 ? "selected" : null; ?>>Passive</option>
            </select>
          </div>
        </div>
        <hr>
        <div class="row">
          <div class="col-md-4 form-group">
            <label class="control-label">Notification Form</label>
            <select class="form-control" name="alert_type">
              <option value="3" <?= $settings["alert_type"] == 3 ? "selected" : null; ?> >Mail + SMS</option>
              <option value="2" <?= $settings["alert_type"] == 2 ? "selected" : null; ?>>Mail</option>
              <option value="1" <?= $settings["alert_type"] == 1 ? "selected" : null; ?>>SMS</option>
            </select>
          </div>
          <div class="form-group col-md-4">
            <label class="control-label">Admin e-mail</label>
            <input type="text" class="form-control" name="admin_mail" value="<?=$settings["admin_mail"]?>">
          </div>
          <div class="form-group col-md-4">
            <label class="control-label">Admin phone number</label>
            <input type="text" class="form-control" name="admin_telephone" value="<?=$settings["admin_telephone"]?>">
          </div>
        </div>

         
        <hr>
        <div class="row">
          <div class="col-md-3 form-group">
            <label class="control-label">SMS Provider</label>
            <select class="form-control" name="sms_provider">
              <option value="bizimsms" <?= $settings["sms_provider"] == "bizimsms" ? "selected" : null; ?> >Bizim SMS</option>
              <option value="netgsm" <?= $settings["sms_provider"] == "netgsm" ? "selected" : null; ?> >NetGSM</option>
            </select>
          </div>
          <div class="form-group col-md-3">
            <label class="control-label">SMS Title</label>
            <input type="text" class="form-control" name="sms_title" value="<?=$settings["sms_title"]?>">
          </div>
          <div class="form-group col-md-3">
            <label class="control-label">User name</label>
            <input type="text" class="form-control" name="sms_user" value="<?=$settings["sms_user"]?>">
          </div>
          <div class="form-group col-md-3">
            <label class="control-label">User password</label>
            <input type="text" class="form-control" name="sms_pass" value="<?=$settings["sms_pass"]?>"> 
          </div>
          <div class="col-md-12 help-block">
         <small><i class="fa fa-warning"></i> <code>Bizim SMS</code>  Type your API Key in the password section.</small>
         </div>
        </div>
        <hr>
        <div class="row">
          <div class="form-group col-md-6">
            <label class="control-label">Email</label>
            <input type="text" class="form-control" name="smtp_user" value="<?=$settings["smtp_user"]?>">
          </div>
          <div class="form-group col-md-6">
            <label class="control-label">Email password</label>
            <input type="text" class="form-control" name="smtp_pass" value="<?=$settings["smtp_pass"]?>">
          </div>
          <div class="form-group col-md-6">
            <label class="control-label">SMTP Server</label>
            <input type="text" class="form-control" name="smtp_server" value="<?=$settings["smtp_server"]?>">
          </div>
          <div class="form-group col-md-3">
            <label class="control-label">SMTP Port</label>
            <input type="text" class="form-control" name="smtp_port" value="<?=$settings["smtp_port"]?>">
          </div>
          <div class="col-md-3 form-group">
            <label class="control-label">SMTP Protocol</label>
            <select class="form-control" name="smtp_protocol">
              <option value="0" <?= $settings["smtp_protocol"] == 0 ? "selected" : null; ?> >None</option>
              <option value="tls" <?= $settings["smtp_protocol"] == "tls" ? "selected" : null; ?>>TLS</option>
              <option value="ssl" <?= $settings["smtp_protocol"] == "ssl" ? "selected" : null; ?>>SSL</option>
            </select>
          </div>
        </div>

        <button type="submit" class="btn btn-primary">Update Settings</button>
      </form>
    </div>
  </div>
</div>
