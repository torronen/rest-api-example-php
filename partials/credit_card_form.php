<div class="row">
	<div class="col-md-12">
		<form id="credit-card-form" action="#" role="form" autocomplete="off">
			<div class="row">
				<div class="col-xs-12">
					<h2>Credit card payment</h2>
					<div class="form-group">
						<label for="cardNumber">Card number</label>
						<input type="number" id="cardNumber" lenght="30" placeholder="Enter the card number" class="form-control"/>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-6">
					<div class="form-group">
						<label for="expMonth">Month</label>
						<select id="expMonth" class="form-control card-exp-month">
							<option>01</option>
							<option>02</option>
							<option>03</option>
							<option>04</option>
							<option>05</option>
							<option>06</option>
							<option>07</option>
							<option>08</option>
							<option>09</option>
							<option>10</option>
							<option>11</option>
							<option>12</option>
						</select>
					</div>
				</div>
				<div class="col-xs-6">
					<div class="form-group">
						<label for="expYear">Year</label>
						<select id="expYear" class="form-control card-exp-year">
							<option>2016</option>
							<option>2017</option>
							<option>2018</option>
							<option>2019</option>
							<option>2020</option>
							<option>2021</option>
						</select>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-6">
					<div class="form-group">
						<label for="cvv">CVV</label>
						<input type="number" id="cvv" maxlength="4" class="form-control" lenght="4" placeholder="Enter the CVV"/>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12">
					<div class="form-group">
						<input type=hidden value="<?=$amount?>">
						<input type="submit" id="cc-form-button" class="btn" class="form-control" value="Pay"/>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>