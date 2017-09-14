<?php

namespace Lib\Controllers\AddClient;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ClientsCtrl extends AddClientController {
	public function getClients (Request $request, Response $response) {
		$params = $request->getQueryParams();

		$q = isset($params['q']) ? filter_var($params['q'], FILTER_SANITIZE_STRING) : '';

		return $response->withJson($this->generateClients(50, $q));
	}
	public function getClient (Request $request, Response $response, $args) {
		$params = $request->getQueryParams();

		$id = filter_var($args['id'], FILTER_SANITIZE_NUMBER_INT);
		if (!(int)$id) {
			return $response->withStatus(400);
		}

		return $response->withJson($this->generateClient('', $id, true));
	}

	protected function generateClients(int $limit, string $q = '') {
		$clients = [];

		// Reducing $limit as length of $q rises
		switch (mb_strlen($q)) {
			case 0: $limit = mt_rand(0, $limit);	break;
			case 3: $limit = (time()%10 > 4) ? round(mt_rand(0, $limit)) : 0; break;
			case 4: $limit = (time()%10 > 5) ? round(mt_rand(0, $limit)) : 0; break;
			case 5: $limit = (time()%10 > 6) ? round(mt_rand(0, $limit)) : 0; break;
			default: $limit = 0;
		}

		for ($i=0; $i < $limit; $i++) {
			$clients []= $this->generateClient($q);
		}

		return $clients;
	}
	protected function generateClient(string $q = '', int $id = 0, bool $is_full = false) {
		return [
			'id' => rand(0, 30000),
			'name' => $this->generatePhrase($q, 1, 2),
		];
	}

	public function addClient (Request $request, Response $response):Response {
		$files = $request->getUploadedFiles();

		$body = $request->getParsedBody();
		$body = is_array($body) ? $body : [];

		$is_body_correct = $this->checkCorrectness($body);
		if ($is_body_correct['is_correct']) {
			return $response->withStatus(201);
		} else {
			$body = $response->getBody();
			$body->write($is_body_correct['msg']);
			return $response->withStatus(400);
		}
	}
	private function checkCorrectness (array $body): array {
		$correct_body = ['name', 'photo', 'phone', 'email', 'address', 'birthdate', 'filling_up', 'sex', 'approved_marketing', 'signature', 'depts', 'notes', 'social', 'source', 'recommended_by'];

		$is_correct = true;
		$msg = '';

		$diff_keys = array_diff(array_keys($body), $correct_body); # nonexpected fields exist
		$msg .= json_encode($diff_keys);
		if (!empty($diff_keys)) {
			$is_correct = false;
			$msg .= implode(', ', array_keys($diff_keys)) . ' arguments should not exist' . "<br>";
		}

		if (isset($body['phone']) && !preg_match('/^[0-9-+*#]+$/', $body['phone'])) { $is_correct = false; $msg .= 'phone number does\'nt match the pattern - /^[0-9-+*#]+$/' . "<br>"; }
		if (isset($body['email']) && !preg_match('/^.*@.*\..{2,}$/', $body['email'])) { $is_correct = false; $msg .= 'email does\'nt match the pattern - /^.*@.*\..{2,}$/' . "<br>"; }
		if (isset($body['address']) && mb_strlen($body['address']) < 3) { $is_correct = false; $msg .= 'address too short' . "<br>"; }

		if (isset($body['birthdate']) && !$this->checkDate($body['birthdate'])) { $is_correct = false; $msg .= 'date and start has to be Y-m-d H:i format, like 1970-01-01 00:00' . "<br>"; }

		if (isset($body['filling_up']) && !in_array($body['filling_up'], ['true', 'false'])) { $is_correct = false; $msg .= 'filling_up can be true or false' . "<br>"; }
		if (isset($body['sex']) && !in_array($body['sex'], ['male', 'female'])) { $is_correct = false; $msg .= 'sex can be male or female' . "<br>"; }

		if (isset($body['approved_marketing']) && $body['approved_marketing'] !== 'true') { $is_correct = false; $msg .= 'approved_marketing can be true or not to exist' . "<br>"; }
		if (isset($body['approved_marketing']) && $body['approved_marketing'] === 'true' && !isset($body['signature'])) { $is_correct = false; $msg .= 'if th user permits getting ads, he has to sign' . "<br>"; }

		if (isset($body['depts'])) {
			$depts = json_decode($body['depts']);
			if (!is_array($depts) || count(array_filter($depts, function ($dept) {
				return isset($dept->sum) && is_int($dept->sum) && isset($dept->desc) && is_string($dept->desc);
			})) !== count($depts)) { $is_correct = false; $msg .= 'depts are malformed' . "<br>"; }
		}
		if (isset($body['notes'])) {
			$notes = json_decode($body['notes']);
			if (!$notes || count(array_filter($notes, function ($note) {
				if (!(isset($note->text) && is_string($note->text))) {
					return false;
				}
				if (isset($note->reminder)) {
					if ($note->reminder === false) { return true; }
					else { return $note->reminder === true && isset($note->date) && $this->checkDate($note->date); }
				} else {
					return false;
				}
				return true;
			})) !== count($notes)) { $is_correct = false; $msg .= 'notes are malformed' . "<br>"; }
		}

		if (isset($body['social'])) {
			$social_links = json_decode($body['social']);
			$types = ['facebook','instagram','linkedin','twitter','pinterest','google+','vk','website'];
			if (!$social_links || count(array_filter($social_links, function ($link) use ($types) {
				return isset($link->type) && in_array($link->type, $types) && isset($link->url) && $this->isValidUrl($link->url);
			})) !== count($social_links)) { $is_correct = false; $msg .= 'social_links are malformed' . "<br>"; }
		}

		if (isset($body['source'])) {
			$source_options = ["ads", "fb_page", "family", "friends", "recommendation"];
			if (!in_array($body['source'], $source_options)) { $is_correct = false; $msg .= 'unknown source_option' . "<br>"; }
			if ($body['source'] === 'recommendation') {
				if (!isset($body['recommended_by'])) { $is_correct = false; $msg .= 'recommended_by doesnt exist' . "<br>"; }
				else if (!preg_match('/^\d+$/', $body['recommended_by'])) { $is_correct = false; $msg .= 'recommended_by client_id has to be integer' . "<br>"; }
			}
		}

		return ["is_correct" => $is_correct, "msg" => $msg];
	}

	private function checkDate (string $date):bool { return (bool)\DateTime::createFromFormat('Y-m-d H:i', $date); }
	private function isValidUrl (string $url):bool { return preg_match('/^\w.*\..{2,}$/', $url); }
}