<?php
if(isset($payment_return))
{
	if($payment_return == 0)
	{
		$text = "Payment was successful!";
		$display = "block";
		$class = "alert-success";
	}
	else if($payment_return == 1)
	{
		$text = "Payment failed!";
		$display = "block";
		$class = "alert-danger";
	}
	else
	{
		$text = "An error occurred while validating the payment return!";
		$display = "block";
		$class = "alert-danger";
	}
}
else
{
	$text = "";
	$display = "none";
	$class = "";
}
?>
<div class="row">
	<div class="col-md-12">
		<div id="return-box" class="alert <?=$class?>" style="display: <?=$display?>"><?=$text?></div> 
	</div>
</div>
