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

	echo __DIR__ ; die;
	return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
	// Sample log message
	$this->logger->info("Slim-Skeleton '/' route");

	// Render index view
	return $this->renderer->render($response, 'index.phtml', $args);
});