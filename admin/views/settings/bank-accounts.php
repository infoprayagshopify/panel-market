<div class="col-md-8">
  <div class="settings-header__table">
    <button type="button"  class="btn btn-default m-b" data-toggle="modal" data-target="#modalDiv" data-action="new_bankaccount" >New Bank Account</button>
  </div>
   <table class="table">
      <thead>
         <tr>
            <th>
              Bank Name
            </th>
            <th>
               Recipient Name	
            </th>
            <th>
              IBAN
            </th>
            <th></th>
         </tr>
      </thead>
      <tbody class="methods-sortable">
         <?php foreach($bankList as $bank): ?>
           <tr>
            <td>
               <?php echo $bank["bank_name"]; ?>
            </td>
            <td><?php echo $bank["bank_alici"]; ?></td>
            <td><?php echo $bank["bank_iban"]; ?></td>
            <td class="p-r">
               <button type="button" class="btn btn-default btn-xs pull-right edit-payment-method" data-toggle="modal" data-target="#modalDiv" data-action="edit_bankaccount" data-id="<?php echo $bank["id"]; ?>">Edit</button>
            </td>
         </tr>
         <?php endforeach; ?>
      </tbody>
   </table>
</div>
