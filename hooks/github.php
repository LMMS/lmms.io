<?php

require_once('../lib/Discord.php');

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use LMMS\Discord;


function checkGithubSignature($content, $signature) {
	$parts = explode('=', $signature, 2);
	$hashAlgo = $parts[0];
	$hash = $parts[1];
	if (!in_array($hashAlgo, hash_algos())) {
		error_log("Unknown hash algorithm: {$hashAlgo}");
		return false;
	}
	$computedHash = hash_hmac($hashAlgo, $content, GITHUB_HOOK_SECRET);
	if ($computedHash !== $hash) {
		error_log("Signature mismatch: Expected {$computedHash}, got {$hash}");
		return false;
	}
	return true;
}


function githubHook(Request $request) {

	// Make sure this actually comes from GitHub
	$content = $request->getContent();
	$signature = $request->headers->get('x-hub-signature');
	if (!checkGithubSignature($content, $signature)) {
		return new Response("Bad signature", Response::HTTP_UNPROCESSABLE_ENTITY);
	}

	$hookData = json_decode($content);
	$repo = $hookData->repository->name;
	$prefix = "[**{$repo}**]";
	$importantActions = ['opened', 'closed', 'reopened'];
	$action = $hookData->action;

	if (property_exists($hookData, 'issue')) {
		$issue = $hookData->issue;
		$url = $issue->html_url;
		$user = $issue->user->login;

		if (in_array($action, $importantActions)) {
			Discord\sendMessage("{$prefix} {$user} {$action} issue: {$url}");
		}
	}
	else if (property_exists($hookData, 'pull_request')) {
		$pr = $hookData->pull_request;
		$url = $pr->html_url;
		$user = $pr->user->login;
		
		if (in_array($action, $importantActions)) {
			if ($action === 'closed' && $pr->merged) {
				$user = $pr->merged_by->login;
				$action = 'merged';
			}
		
			Discord\sendMessage("{$prefix} {$user} {$action} PR: {$url}");
		}
	}

	return new Response("", Response::HTTP_OK);
}
