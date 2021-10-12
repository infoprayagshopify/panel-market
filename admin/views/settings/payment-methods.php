<div class="col-md-9">
    <table class="table">
        <thead>
            <tr>
                <th>
                    <span class="table__drag-header">Method</span>
                </th>
                <th>Visible Name</th>
                <th>Min Payment</th>
                <th>Max Payment</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody class="methods-sortable">
            <?php foreach($methodList as $method): $extra = json_decode($method["method_extras"],true); ?>
                <tr class="<?php if( $method["method_type"] == 1 ): echo "grey "; endif; ?>" data-toggle="<?php echo $method["id"]; ?>" data-id="<?php echo $method["id"]; ?>">
                    <td>
                        <div class="table__drag handle">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <title>Drag-Handle</title>
                                <path d="M7 2c-1.104 0-2 .896-2 2s.896 2 2 2 2-.896 2-2-.896-2-2-2zm0 6c-1.104 0-2 .896-2 2s.896 2 2 2 2-.896 2-2-.896-2-2-2zm0 6c-1.104 0-2 .896-2 2s.896 2 2 2 2-.896 2-2-.896-2-2-2zm6-8c1.104 0 2-.896 2-2s-.896-2-2-2-2 .896-2 2 .896 2 2 2zm0 2c-1.104 0-2 .896-2 2s.896 2 2 2 2-.896 2-2-.896-2-2-2zm0 6c-1.104 0-2 .896-2 2s.896 2 2 2 2-.896 2-2-.896-2-2-2z"></path>
                            </svg>
                        </div>
                        <?php echo $method["method_name"]; ?>
                    </td>
                    <td>
                        <?php echo $extra["name"]; ?>
                    </td>
                    <td>
                        <?php echo $method["method_min"]; ?>
                    </td>
                    <td>
                        <?php echo $method["method_max"]; ?>
                    </td>
                    <td>
                        <input type="checkbox" class="tiny-toggle" data-tt-palette="purple" data-url="<?=site_url("admin/settings/payment-methods/type")?>" data-id="<?php echo $method["id"]; ?>" <?php if( $method["method_type"]==2 ): echo "checked"; endif; ?>> </td>
                    <td class="p-r">
                        <button type="button" class="btn btn-primary btn-xs pull-right edit-payment-method" data-toggle="modal" data-target="#modalDiv" data-action="edit_paymentmethod" data-id="<?php echo $method["method_get"]; ?>">Edit</button>
                    </td>
                </tr>
                <?php endforeach; ?>
        </tbody>
    </table>
</div>