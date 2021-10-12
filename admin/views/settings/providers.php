<div class="col-md-8">
	<div class="settings-header__table">
		<button type="button" class="btn btn-default m-b" data-toggle="modal" data-target="#modalDiv" data-action="new_provider">Add new Provider</button>
	</div>
	<hr>



	<div class="form-group">


		<table class="table providers_list">
			<thead>
				<tr>
					<th class="p-l" width="45%">Provider</th>
					<th>Balance</th>
					<th></th>
				</tr>
			</thead>
			<tbody>

				<?php foreach ($providersList as $provider) : ?>


					<tr id="" class="list_item ">
						<td class="name p-l"><?php echo $provider["api_name"]; ?> </td>
						<td>

							<?php



							$api_url = $provider["api_url"];

							$api_key = $provider["api_key"];



							$veri = json_decode(kontrol($api_url, $api_key));

							echo $veri->balance . " " . $veri->currency;



							?>


						</td>
						<td class="p-r">

							<button type="button" class="btn btn-default btn-xs pull-right" data-toggle="modal" data-target="#modalDiv" data-action="edit_provider" data-id="<?= $provider["id"] ?>">Edit</button>
						</td>


						<input type="hidden" name="privder_changes" value="privder_changes">
					<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>



<?php


function kontrol($api_url, $api_key)
{

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $api_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);


	$_post = 	array(
		'key' => $api_key,
		'action' => 'balance',
	);
	if (is_array($_post)) {
		foreach ($_post as $name => $value) {
			$_post[] = $name . '=' . urlencode($value);
		}
	}

	if (is_array($_post)) {
		curl_setopt($ch, CURLOPT_POSTFIELDS, join('&', $_post));
	}


	$result = curl_exec($ch);
	return $result;
	curl_close($ch);
}



?>