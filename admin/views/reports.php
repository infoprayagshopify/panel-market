<?php include 'header.php'; ?>
<div class="container-fluid">
  <div class="report-navs__left">
     <ul class="nav nav-tabs p-b">
        <li class="<?php if( $action == "profit" ): echo "active"; endif; ?>"><a href="<?php echo site_url("admin/reports") ?>">Profit from Orders</a></li>
        <li class="<?php if( $action == "payments" ): echo "active"; endif; ?>"><a href="<?php echo site_url("admin/reports/payments") ?>">Earning from Payments</a></li>
        <li class="<?php if( $action == "orders" ): echo "active"; endif; ?>"><a href="<?php echo site_url("admin/reports/orders") ?>">Number of Orders</a></li>
     </ul>
   </div>
   <div class="report-navs__right">
     <form class="" action="<?php echo site_url("admin/reports/".$action."?year=".$year) ?>" method="post">

       <div class="report-navs__block">
          <div class="btn-group" role="group">
            <?php foreach($yearList as $yearl): ?>
              <a href="<?php echo site_url("admin/reports/".$action."?year=".$yearl) ?>" class="btn btn-default <?php if( $yearl == $year ): echo "active"; endif; ?> ">
                  <?php echo $yearl; ?>
              </a>
            <?php endforeach; ?>
          </div>
       </div>

       <?php if( $action == "payments" ): ?>
         <div class="report-navs__block selectpicker_methods">
           <select class="selectpicker" data-actions-box="true" data-live-search="true" name="methods[]" multiple="" data-max-options="100" data-size="10" title="Ödeme Yöntemleri" tabindex="-98">
             <?php foreach($methods as $method ): ?>
                <option value="<?php echo $method["id"]; ?>" <?php if( $_POST ): if( in_array($method["id"],$_POST["methods"]) ): echo 'selected'; endif; else: echo 'selected'; endif; ?>> <?php echo $method["method_name"] ?> </option>
              <?php endforeach; ?>
          </select>
         </div>
       <?php endif; ?>
       <?php if( $action == "profit" || $action == "orders" ): ?>
         <div class="report-navs__block selectpicker_service">
           <select class="selectpicker" data-actions-box="true" data-live-search="true" name="services[]" multiple="" data-max-options="100" data-size="10" title="Services" tabindex="-98">
             <?php $c=0;foreach($serviceList as $category => $services ): $c++; ?>
                <optgroup label="<?=$category?>">
                  <?php if( !empty($services[0]["service_id"]) ): ?>
                    <?php for($i=0;$i<count($services);$i++): ?>
                      <option value="<?php echo $services[$i]["service_id"]; ?>" <?php if( $_POST ): if( in_array($services[$i]["service_id"],$_POST["services"]) ): echo 'selected'; endif; else: echo 'selected'; endif; ?>> <?php echo $services[$i]["service_id"]." - ".$services[$i]["service_name"] ?> </option>
                    <?php endfor; ?>
                  <?php endif; ?>
                </optgroup>
              <?php endforeach; ?>
          </select>
         </div>
         <div class="report-navs__block selectpicker_status">
           <select class="selectpicker" name="statuses[]" multiple="" data-max-options="100" data-size="10" title="Order Status" tabindex="-98">
             <option value="cron" <?php if( $_POST ): if( in_array("cron",$_POST["statuses"]) ): echo 'selected'; endif; else: echo 'selected'; endif; ?>>Waiting for CRON</option>
             <option value="fail"  <?php if( $_POST ): if( in_array("fail",$_POST["statuses"]) ): echo 'selected'; endif; else: echo 'selected'; endif; ?>>Failed</option>
             <option value="pending"  <?php if( $_POST ): if( in_array("pending",$_POST["statuses"]) ): echo 'selected'; endif; else: echo 'selected'; endif; ?>>Pending</option>
             <option value="inprogress"  <?php if( $_POST ): if( in_array("inprogress",$_POST["statuses"]) ): echo 'selected'; endif; else: echo 'selected'; endif; ?>>In Progress</option>
             <option value="completed"  <?php if( $_POST ): if( in_array("completed",$_POST["statuses"]) ): echo 'selected'; endif; else: echo 'selected'; endif; ?>>Completed</option>
             <option value="partial"  <?php if( $_POST ): if( in_array("partial",$_POST["statuses"]) ): echo 'selected'; endif; else: echo 'selected'; endif; ?>>Partial</option>
             <option value="canceled"  <?php if( $_POST ): if( in_array("canceled",$_POST["statuses"]) ): echo 'selected'; endif; else: echo 'selected'; endif; ?>>Canceled</option>
             <option value="processing"  <?php if( $_POST ): if( in_array("processing",$_POST["statuses"]) ): echo 'selected'; endif; else: echo 'selected'; endif; ?>>Processing</option>
           </select>
         </div>

       <?php endif; ?>

       <div class="report-navs__block">
            <button type="submit" class="btn btn-primary">
              Update
            </button>
        </div>
     </form>

   </div>
   <div class="row">
      <div class="col-md-12">
         <table class="table report-table" style="border:1px solid #ddd">
            <thead>
               <tr>
                  <th>
                  </th>
                  <th align="right" style="text-align:center;">January</th>
                  <th align="right" style="text-align:center;">February</th>
                  <th align="right" style="text-align:center;">March</th>
                  <th align="right" style="text-align:center;">April</th>
                  <th align="right" style="text-align:center;">May</th>
                  <th align="right" style="text-align:center;">June</th>
                  <th align="right" style="text-align:center;">July</th>
                  <th align="right" style="text-align:center;">August</th>
                  <th align="right" style="text-align:center;">September</th>
                  <th align="right" style="text-align:center;">October</th>
                  <th align="right" style="text-align:center;">November</th>
                  <th align="right" style="text-align:center;">December</th>
               </tr>
            </thead>
            <tbody>
              <?php if( $action == "profit" ): ?>
                <?php for ($day=1; $day <=31; $day++): ?>
                 <tr>
                    <td align="center"><?=$day?></td>
                    <?php for( $month=1; $month<=12; $month++ ): ?>
                      <td align="center">
                         <?php echo dayCharge($day,$month,$year,["services"=>$_POST["services"],"status"=>$_POST["statuses"]]); ?>
                      </td>
                    <?php endfor; ?>
                 </tr>
               <?php endfor; ?>
               <tr>
                 <td align="center"><b>Gross Profit: </b></td>
                 <?php for( $month=1; $month<=12; $month++ ): ?>
                   <td align="center">
                     <b>  <?php echo monthCharge($month,$year,["services"=>$_POST["services"],"status"=>$_POST["statuses"]]); ?> </b>
                   </td>
                 <?php endfor; ?>
               </tr>
               <tr>
                 <td align="center"><b>Net Profit: </b></td>
                 <?php for( $month=1; $month<=12; $month++ ): ?>
                   <td align="center">
                     <b>  <?php echo monthChargeNet($month,$year,["services"=>$_POST["services"],"status"=>$_POST["statuses"]]); ?> </b>
                   </td>
                 <?php endfor; ?>
               </tr>
              <?php elseif( $action == "payments" ): ?>
               <?php for ($day=1; $day <=31; $day++): ?>
                <tr>
                   <td align="center"><?=$day?></td>
                   <?php for( $month=1; $month<=12; $month++ ): ?>
                     <td align="center">
                        <?php echo dayPayments($day,$month,$year,["methods"=>$_POST["methods"]]); ?>
                     </td>
                   <?php endfor; ?>
                </tr>
                <?php endfor; ?>
                <tr>
                  <td align="center"><b>Total: </b></td>
                  <?php for( $month=1; $month<=12; $month++ ): ?>
                    <td align="center">
                      <b>  <?php echo monthPayments($month,$year,["methods"=>$_POST["methods"]]); ?> </b>
                    </td>
                  <?php endfor; ?>
                </tr>
              <?php elseif( $action == "orders" ): ?>
               <?php for ($day=1; $day <=31; $day++): ?>
                <tr>
                   <td align="center"><?=$day?></td>
                   <?php for( $month=1; $month<=12; $month++ ): ?>
                     <td align="center">
                        <?php echo dayOrders($day,$month,$year,["services"=>$_POST["services"],"status"=>$_POST["statuses"]]); ?>
                     </td>
                   <?php endfor; ?>
                </tr>
                <?php endfor; ?>
                <tr>
                  <td align="center"><b>Total: </b></td>
                  <?php for( $month=1; $month<=12; $month++ ): ?>
                    <td align="center">
                      <b>  <?php echo monthOrders($month,$year,["services"=>$_POST["services"],"status"=>$_POST["statuses"]]); ?> </b>
                    </td>
                  <?php endfor; ?>
                </tr>
              <?php endif; ?>
            </tbody>
         </table>
      </div>
   </div>
</div>
<?php include 'footer.php'; ?>
