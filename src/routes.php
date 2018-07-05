<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
	// Sample log message
	$this->logger->info("Slim-Skeleton '/' route");

	// Render index view
	return $this->renderer->render($response, 'index.phtml', $args);
});


$app->get('/test/[{name}]', function (Request $request, Response $response, array $args) {
	// Sample log message
	$this->logger->info("Slim-Skeleton '/' route '/' test");

	$args['items'] = $this->db->table('t_pbo_order_item')->limit(10)->get();

	echo $request->getUri()->getBasePath(); die;
	return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/authenticate/', function (Request $request, Response $response, array $args) {

	$hasCredential = $this->ipc_central->table('user_access_tab')->where('system_id', '=', 41)->where('employee_id', '=', $_SESSION['user_data']['employee_id'])->count();

	if ($hasCredential)
	{
		$this->logger->info($_SESSION['user_data']['full_name']);
		return $response->withRedirect('/ipc_central/parts/backorder/public/index/');
	}
	else
	{
		$this->logger->info('Unauthorized Access');
		return $response->withRedirect('/ipc_central/main_home.php');
	}
});

$app->get('/index/', function (Request $request, Response $response, array $args) {

	// Render index view
	return $this->renderer->render($response, 'backorder/index.phtml', $args);
});

$app->post('/store_resource/', function(Request $request, Response $response, array $args) {
	ini_set('memory_limit', '-1');
	ini_set('max_execution_time', 0);

	// Log Message
	$this->logger->info("Processing Update");

	$data = json_decode(file_get_contents("php://input"), true);

	$not_updated = array();

	if (count($data))
	{
		$this->db->table('t_pbo_order_item')->where('eta', '!=', '')->orWhereNull('eta')->update(array('eta' => ''));

		foreach($data as $item)
		{
			if (isset($item['Po No']) && isset($item['Part Number']) && isset($item['SO No']) && isset($item['New ETA']))
			{

				$flag = $this->db->table('t_pbo_order_item')->where('order_no', '=', $item['Po No'])->where('part_no', '=', $item['Part Number'])->count();

				if ($flag)
				{
					 $this->db->table('t_pbo_order_item')->where('order_no', '=', $item['Po No'])->where('part_no', '=', $item['Part Number'])->update(array('eta' => $item['New ETA']));
				}
				else
				{
					$not_updated[] = array(
							'Po No'       => $item['Po No'],
							'Part Number' => $item['Part Number'],
							'SO No'       => $item['SO No'],
							'New ETA'     => $item['New ETA']
						);
				}
			}
		}
	}

	echo $not_updated ? json_encode($not_updated) : '';  
});

$app->get('/minimum_order/', function(Request $request, Response $response, array $args) {
	$this->logger->info('Minimum order form');

	return $this->renderer->render($response, 'backorder/minimum_order.phtml', $args);
});

$app->post('/store_min_order/', function(Request $request, Response $response, array $args) {
	$this->logger->info('Process minimum order');

	ini_set('memory_limit', '-1');
	ini_set('max_execution_time', 0);

	$data = json_decode(file_get_contents("php://input"), true);

	$not_exist = array();

	foreach ($data as $item)
	{
		$parts = $this->db->table('t_pbo_parts')->where('part_no', '=', $item['Part No.'])->first();

		// Verify if the item exist in the parts table
		if (count($parts))
		{

			$hasDiscount = $this->db->table('t_pbo_parts_discount')->where('part_no', '=', $item['Part No.'])->count();

			if ($hasDiscount)
			{

				$this->db->table('t_pbo_parts_discount')->where('id', '=', $parts->id)->update(array('min_order_qty' => $item['Standard Pack Qty.']));
			}
			else
			{
				// Prepare data
				$config = array(
						'id'            => $parts->id,
						'part_no'       => $item['Part No.'],
						'min_order_qty' => $item['Standard Pack Qty.']
					);

				$this->db->table('t_pbo_parts_discount')->insert($config);
			}
		}
		else
		{
			array_push($not_exist, $item);
		}
	}

	echo $not_exist ? json_encode($not_exist) : '';
});

$app->get('/logout/', function (Request $request, Response $response, array $args) {
	session_destroy();
	
	return $response->withRedirect('/ipc_central/main_home.php');
});


$app->get('/fetch/', function (Request $request, Response $response, array $args) {
	// Sample log message
	$this->logger->info("Slim-Skeleton '/' route");

	echo '<pre>';
	print_r($this->db->table('t_pbo_order_item')->take(100)->get());
	echo '</pre>';

	// Render index view
	// return $this->renderer->render($response, 'backorder/index.phtml', $args);
});