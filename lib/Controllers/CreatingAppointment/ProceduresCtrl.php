<?php

namespace Lib\Controllers\CreatingAppointment;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ProceduresCtrl extends CreatingAppointmentController {
	public function getAllProcedures (Request $request, Response $response) {
		$DEFAULT_LIMIT = 20;

		$params = $request->getQueryParams();
		if (!isset($params['limit'])) {
			$params['limit'] = $DEFAULT_LIMIT;
		}
		if (!isset($params['offset'])) {
			$params['offset'] = 0;
		}
		$q = isset($params['q']) ? filter_var($params['q'], FILTER_SANITIZE_STRING) : '';

		$procedures = [];
		for ($i=0; $i < $params['limit']; $i++) {
			$procedures []= $this->generateProcedure($q);
		}

		$response = $response->withHeader('Access-Control-Allow-Origin', '*')->withHeader('X-Robots-Tag', 'noindex, nofollow');
		return $response->withJson($procedures);
	}

	public function getBIProcedures (Request $request, Response $response):Response {
		$DEFAULT_LIMIT = 6;

		$procedures = [];
		for ($i=0; $i < $DEFAULT_LIMIT; $i++) {
			$procedures []= $this->generateProcedure();
		}

		return $response->withJson($procedures);
	}

	protected function generateProcedure($q = '') {
		return [
			"id" => mt_rand(0, 30000),  // AvodaID
			"name" => $this->generatePhrase($q, 1, 6), // Name
			"duration" => 15 * $this->rand_with_average(1, 40, 4, 0.1), // minutes < 10*60, TimeTipul
			"price" => 50 * $this->rand_with_average(2, 100, 10, 0.1), // float, PriceTipul
			"color" => '#' . dechex(mt_rand(0x000000, 0xFFFFFF)), // int, Color
			"category" => [
				"id" => mt_rand(0, 30000), // smallint, SpecializationID
				"name" => $this->generatePhrase('', 1, 2) // Name
			],
			// "shortName" => $this->generatePhrase($q, 1, 3, 2, 7), // ShortName
		];
	}

	public function add (Request $request, Response $response):Response {
		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$is_body_correct = $this->checkBodyCorrectness($body);

		if ($is_body_correct['is_correct']) {
			return $response->withStatus(201);
		} else {
			$body = $response->getBody();
			$body->write("<br>" . $is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}

	private function checkBodyCorrectness($body) {
		$correct_body = ['name', 'duration', 'price', 'color', 'category_id'];

		$is_correct = true;
		$msg = '';

		if (!empty(array_diff($correct_body, array_keys($body)))) {
			$is_correct = false;
			$msg = implode(', ', array_diff($correct_body, array_keys($body))) . ' argument should exist';
		}

		if (empty($body['name'])) { $is_correct = false; $msg .= 'name cannot be empty' . "<br>"; }
		if (isset($body['duration']) && !ctype_digit($body['duration'])) { $is_correct = false; $msg .= 'duration ahve to be an integer' . "<br>"; }
		if (isset($body['price']) && !is_numeric($body['price'])) { $is_correct = false; $msg .= 'price have to be a number' . "<br>"; }
		if (isset($body['color']) && !($body['color'][0] === '#' && $this->checkColorValue(substr($body['color'], 1)))) { $is_correct = false; $msg .= $body['color'] . ' color has to be a valid hex value' . "<br>"; }
		if (isset($body['category_id']) && !ctype_digit($body['category_id'])) { $is_correct = false; $msg .= 'category_id have to be an integer' . "<br>"; }

		return ["is_correct" => $is_correct, "msg" => $msg];
	}

	private function checkColorValue($color) {
		$hex_color_parts = 3;
		return $hex_color_parts === count(array_filter(str_split($color, mb_strlen($color)/3), 'ctype_xdigit'));
	}
}