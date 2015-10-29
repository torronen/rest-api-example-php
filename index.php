<?php
/*
	Allow the requests to be sent outside of the current origin. This is needed because we want to send the credit card details straight to Paybyway API.
*/
Header('Access-Control-Allow-Origin', '*');
Header('Access-Control-Allow-Methods', 'POST');
Header("Access-Control-Allow-Headers", "X-Requested-With");

date_default_timezone_set('Europe/Helsinki');

/*
	Include the PHP library
*/
include_once('rest-php-lib/lib/paybyway_loader.php');

/*
	Autoloader setup for Composer
	include_once('rest-php-lib/vendor/autoload.php');
*/

/*
	Create instance of the lib and define api_key and private_key
	Paybyway('api_key', 'private_key')
*/
$payment = new Paybyway\Paybyway('api_key', 'private_key');

//includes the logic for creating payments
include("pay_logic.php");

//includes the logic for return handling
include("return_logic.php");

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Maksukaista - Web payment demo</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		<link href="bootstrap/css/bootstrap.css" rel="stylesheet">
		<link href="css/default.css" rel="stylesheet">
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
		<style type="text/css">body{padding-top:10px;}</style>
	</head>
	<body>
  		<div class="container">
		<div class="row">
		<div class="col-xs-12">
			<?php include("partials/return.php"); ?>
			<img src="images/maksukaista_pbw_white_250.png" />
			<?php include("partials/credit_card_form.php"); ?>
			<?php include("partials/embedded_banks_form.php"); ?>
			<?php include("partials/pay_page_form.php"); ?>
		<br/>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function(){
		var embedded_buttons = [];
		embedded_buttons.push($('.bank-button'));
		embedded_buttons.push($('#pay-page-button'));
		embedded_buttons.push($("#cc-form-button"));

		function hide_buttons()
		{
			$.each(embedded_buttons, function(i,v)
			{
				v.addClass('disabled');
			});
			$('#return-box').removeClass("alert-success").removeClass("alert-danger").hide();
		}
		function show_buttons()
		{
			$.each(embedded_buttons, function(i,v)
			{
				v.removeClass('disabled');
			});
		}
		$("#credit-card-form").submit(function(e) {
			var form = this;
			hide_buttons();
			e.preventDefault();
			var chargeRequest = $.get("?action=credit_card_payment");
			chargeRequest.done(function(data)
			{
				var response;

				try
				{
					response = $.parseJSON(data);
					var token = response.token;
					var amount = response.amount;
					var card = $('#cardNumber').val();
					var expMonth = $('#expMonth').val();
					var expYear = $('#expYear').val();
					var code = $('#cvv').val();

					var request = $.post(response.payment_url, {"token": token, "card": card, "amount": amount, "currency": response.currency, "exp_month": expMonth, "exp_year": expYear, "security_code": code});
					request.done(function(result){
						//the result of charge cannot be trusted (since its done from client browser) -> do check from backend
						var check_request = $.post("?action=check", {"token": token});
						check_request.done(function(result)
						{
							if($.parseJSON(result).result == 0)
							{
								$('#return-box').addClass("alert-success").text("Payment was successful.").show();
								show_buttons();
							}
							else
								$('#return-box').addClass("alert-danger").text("Error while processing payment, check your card details.").show();
								show_buttons();
						});
					});
					request.error(function(d)
					{
						$('#return-box').addClass("alert-danger").text("Error while processing payment, please refresh the page and try again.").show();
						show_buttons();
					});

				}
				catch(err)
				{
					$('#return-box').addClass("alert-danger").text("Error while processing payment, please refresh the page and try again.").show();
					show_buttons();
					return;
				}
			});
			chargeRequest.error(function(d)
			{
				$('#return-box').addClass("alert-danger").text("Error while processing payment, please refresh the page and try again.").show();
				show_buttons();
				return
			});
		});

		//handle embedded bank button clicks
		$("#bank-buttons > div").click(function(){

			if($(this).hasClass('disabled')) return;
			hide_buttons();

			$("#bank-buttons > div").addClass('disabled');
			$(this).addClass('selected');

			var data = 'selected=' + $(this).data('selected');

			//make request to backend to create new charge
			var request = $.post('?action=embedded_bank_payment', data);

			request.done(function(result){
				var response;
				
				try
				{
					response = $.parseJSON(result);
				}
				catch(err)
				{
					$('#return-box').addClass("alert-danger").text("Error while processing payment, please refresh the page and try again.").show();
					show_buttons();
					return;
				}
				//create a form to move user to the pay page -> bank page
				var form = $('<form></form>').attr('action', response.payment_url).attr('method', 'GET');

				$('body').append(form);
				form.submit();
			});
			request.error(function(result){
				$('#return-box').addClass("alert-danger").text("Error while processing payment, please refresh the page and try again.").show();
				show_buttons();
			});
		});

		$("#pay-page-button").click(function(e) {
			e.preventDefault();
			hide_buttons();
			var request = $.post('?action=pay_page_payment');

			request.done(function(result){
				var response;
				
				try
				{
					response = $.parseJSON(result);
				}
				catch(err)
				{
					$('#return-box').addClass("alert-danger").text("Error while processing payment, please refresh the page and try again.").show();
					show_buttons();
					return;
				}

				var form = $('<form></form>').attr('action', response.payment_url).attr('method', 'GET');

				$('body').append(form);
				form.submit();
			});
			request.error(function(result){
				$('#return-box').addClass("alert-danger").text("Error while processing payment, please refresh the page and try again.").show();
				show_buttons();
			});
		});
	});

</script>
</body>
</html>
