<div class="col-md-8">
  <div class="panel panel-default">
    <div class="panel-body">
      <form action="" method="post" enctype="multipart/form-data">
        <div class="form-group">
          <label for="" class="control-label">Nameserver 1</label>
          <input type="text" class="form-control" name="ns1" value="<?=$settings["ns1"]?>">
        </div>
        <div class="form-group">  
          <label for="" class="control-label">Nameserver 2</label>
          <input type="text" class="form-control" name="ns2" value="<?=$settings["ns2"]?>">
        </div>
        <div class="form-group">
          <label for="" class="control-label">Price</label>
          <input type="text" class="form-control" name="price" value="<?=$settings["childpanel_price"]?>">
        </div>
        <button type="submit" class="btn btn-primary">Update Settings</button>
      </form>
    </div>
  </div>
</div>
