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

$app->get('/dashboard/', function (Request $request, Response $response, array $args) {
	// Sample log message
	$this->logger->info("Slim-Skeleton '/' route");

	// Render index view
	return $this->renderer->render($response, 'backorder/index.phtml', $args);
});

$app->post('/store_resource/', function(Request $request, Response $response, array $args) {
	ini_set('memory_limit', '-1');
	ini_set('max_execution_time', 0);

	$data = json_decode(file_get_contents("php://input"), true);

	$total_count = count($data);

	$limit = 0;

	$table = $this->db->table('t_pbo_order_item');

	$not_updated = array();

	foreach($data as $item)
	{
		if (isset($item['Po No']) && isset($item['Part Number']) && isset($item['SO No']) && isset($item['New ETA']))
		{
			if (!($table->where('order_no', $item['Po No'])->where('part_no', $item['Part Number'])->update(array('eta' => $item['New ETA'])) ))
			{
				$not_updated[] = array(
						'Po No'       => $item['Po No'],
						'Part Number' => $item['Part Number'],
						'SO No'       => $item['SO No'],
						'New ETA'     => $item['New ETA']
					); 
			}
			$limit++;
		}
	}

	echo '<pre>';
	print_r($not_updated);
	echo '</pre>';
});