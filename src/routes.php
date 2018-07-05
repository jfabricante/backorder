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
	// Sample log message
	$this->logger->info("Slim-Skeleton '/' route");

	// Render index view
	return $this->renderer->render($response, 'backorder/index.phtml', $args);
});

$app->post('/store_resource/', function(Request $request, Response $response, array $args) {
	ini_set('memory_limit', '-1');
	ini_set('max_execution_time', 0);

	$data = json_decode(file_get_contents("php://input"), true);

	$not_updated = array();

	$limit = 1;

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

	echo $not_updated ? json_encode($not_updated) : '';  
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