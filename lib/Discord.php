<?php

namespace LMMS\Discord;


function sendMessage($message) {
	$client = new \GuzzleHttp\Client();
	$url = "https://discordapp.com/api/channels/" . DISCORD_CHANNEL . "/messages";

	$res = $client->post($url, [
		'headers' => [
			'Authorization' => "Bot " . DISCORD_TOKEN,
			'User-Agent' => 'lmms.io GitHub/Discord Integration Bot (https://lmms.io/, 0.0.1)',
		],
		'json' => [
			'content' => $message,
		],
	]);

	if ($res->getStatusCode() !== 200) {
		error_log("Failed to send message to discord: HTTP Status {$res->getStatusCode()}: {$res->getBody()}");
	}
}
