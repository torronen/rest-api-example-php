<?php

namespace Paybyway;

interface PaybywayConnector
{
	public function request($url, $post_arr);
}