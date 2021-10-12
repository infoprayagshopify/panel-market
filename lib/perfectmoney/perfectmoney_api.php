<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 
 */

	function check_v2_hash($perfectmoney_alternate_passphrase = ""){
		$alternate_passphrase = strtoupper(md5($perfectmoney_alternate_passphrase));;
		$string= $_POST['PAYMENT_ID'].':'.$_POST['PAYEE_ACCOUNT'].':'. $_POST['PAYMENT_AMOUNT'].':'.$_POST['PAYMENT_UNITS'].':'. $_POST['PAYMENT_BATCH_NUM'].':'. $_POST['PAYER_ACCOUNT'].':'.$alternate_passphrase.':'. $_POST['TIMESTAMPGMT'];
		$hash = strtoupper(md5($string));
		if ($hash == $_POST['V2_HASH']) {
			return true;
		}else{
			return false;
		}

	}

	function verify_transaction_using_api($perfectmoney_member_id = "", $perfectmoney_password = ""){
		$f = fopen('https://perfectmoney.com/acct/historycsv.asp?AccountID='.$perfectmoney_member_id.'&PassPhrase='.$perfectmoney_password.'&startmonth='.date("m", $_POST['TIMESTAMPGMT']).'&startday='.date("d", $_POST['TIMESTAMPGMT']).'&startyear='.date("Y", $_POST['TIMESTAMPGMT']).'&endmonth='.date("m", $_POST['TIMESTAMPGMT']).'&endday='.date("d", $_POST['TIMESTAMPGMT']).'&endyear='.date("Y", $_POST['TIMESTAMPGMT']).'&paymentsreceived=1&batchfilter='.$_POST['PAYMENT_BATCH_NUM'], 'rb');
		if($f === false) return false;

		$lines = array();
		while(!feof($f)) array_push($lines, trim(fgets($f)));
		fclose($f);
		if($lines[0] != 'Time,Type,Batch,Currency,Amount,Fee,Payer Account,Payee Account,Payment ID,Memo'){
			return false;
		}else{
		 	$ar = array();
		 	$n = count($lines);
		 	if($n != 2) return false;
		 	$item = explode(",", $lines[1], 10);
		 	if(count($item) != 10) return 'invalid API output';
		 	$item_named['Time']				=	$item[0];
		 	$item_named['Type']				=	$item[1];
		 	$item_named['Batch']			=	$item[2];
		 	$item_named['Currency']			=	$item[3];
		 	$item_named['Amount']			=	$item[4];
		 	$item_named['Fee']				=   $item[5];
		 	$item_named['Payer Account']	=	$item[6];
		 	$item_named['Payee Account']	=	$item[7];
		 	$item_named['Payment ID']		=	$item[8];
		 	$item_named['Memo']				=	$item[9];

		 	if($item_named['Batch'] == $_POST['PAYMENT_BATCH_NUM'] && $_POST['PAYMENT_ID'] == $item_named['Payment ID'] && $item_named['Type'] == 'Income' && $_POST['PAYEE_ACCOUNT'] == $item_named['Payee Account'] && $_POST['PAYMENT_AMOUNT'] == $item_named['Amount'] && $_POST['PAYMENT_UNITS'] == $item_named['Currency'] && $_POST['PAYER_ACCOUNT'] == $item_named['Payer Account']){
		 		return true;
		 	}else{
				return false;
		 	}
		}

	}

