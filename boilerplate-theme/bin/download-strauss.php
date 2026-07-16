<?php

declare(strict_types=1);

/**
 * Downloads Strauss PHAR into bin/ when missing or obviously corrupt.
 *
 * @return void
 */
function download_strauss_phar(): void
{
	$phar     = __DIR__ . DIRECTORY_SEPARATOR . 'strauss.phar';
	$url      = 'https://github.com/BrianHenryIE/strauss/releases/latest/download/strauss.phar';
	$minBytes = 1_000_000;

	if (is_file($phar) && filesize($phar) >= $minBytes) {
		return;
	}

	if (is_file($phar)) {
		unlink($phar);
	}

	$result = download_url($url);
	if ($result['body'] === null || strlen($result['body']) < $minBytes) {
		$detail = $result['error'] !== '' ? ' (' . $result['error'] . ')' : '';
		fwrite(STDERR, "Failed to download Strauss PHAR from {$url}{$detail}" . PHP_EOL);
		exit(1);
	}

	if (file_put_contents($phar, $result['body']) === false) {
		fwrite(STDERR, "Failed to write Strauss PHAR to {$phar}" . PHP_EOL);
		exit(1);
	}
}

/**
 * Downloads a URL following redirects via cURL, then fopen wrappers.
 *
 * @param string $url Absolute URL to download.
 * @return array{body: ?string, error: string}
 */
function download_url(string $url): array
{
	$errors = [];

	$curl = download_with_curl($url);
	if ($curl['body'] !== null) {
		return $curl;
	}
	if ($curl['error'] !== '') {
		$errors[] = 'curl: ' . $curl['error'];
	}

	$fopen = download_with_fopen($url);
	if ($fopen['body'] !== null) {
		return $fopen;
	}
	if ($fopen['error'] !== '') {
		$errors[] = 'fopen: ' . $fopen['error'];
	}

	return [
		'body'  => null,
		'error' => implode('; ', $errors),
	];
}

/**
 * Downloads via PHP cURL extension when available.
 *
 * @param string $url Absolute URL to download.
 * @return array{body: ?string, error: string}
 */
function download_with_curl(string $url): array
{
	if (! function_exists('curl_init')) {
		return ['body' => null, 'error' => 'extension not loaded'];
	}

	$handle = curl_init($url);
	if ($handle === false) {
		return ['body' => null, 'error' => 'curl_init failed'];
	}

	$options = [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_MAXREDIRS      => 5,
		CURLOPT_TIMEOUT        => 120,
		CURLOPT_USERAGENT      => 'boilerplate-theme-strauss-installer',
		CURLOPT_FAILONERROR    => true,
	];

	$cafile = resolve_ca_bundle();
	if ($cafile !== null) {
		$options[CURLOPT_CAINFO] = $cafile;
	}

	curl_setopt_array($handle, $options);

	$body  = curl_exec($handle);
	$error = curl_error($handle);
	curl_close($handle);

	if (! is_string($body) || $body === '' || $error !== '') {
		return [
			'body'  => null,
			'error' => $error !== '' ? $error : 'empty response',
		];
	}

	return ['body' => $body, 'error' => ''];
}

/**
 * Downloads via PHP stream wrappers (file_get_contents).
 *
 * @param string $url Absolute URL to download.
 * @return array{body: ?string, error: string}
 */
function download_with_fopen(string $url): array
{
	$contextOptions = [
		'http' => [
			'follow_location' => 1,
			'max_redirects'   => 5,
			'timeout'         => 120,
			'header'          => "User-Agent: boilerplate-theme-strauss-installer\r\n",
		],
	];

	$cafile = resolve_ca_bundle();
	if ($cafile !== null) {
		$contextOptions['ssl'] = [
			'cafile' => $cafile,
		];
	}

	$context = stream_context_create($contextOptions);
	$body    = @file_get_contents($url, false, $context);

	if (! is_string($body) || $body === '') {
		$last = error_get_last();
		return [
			'body'  => null,
			'error' => is_array($last) ? (string) $last['message'] : 'empty response',
		];
	}

	return ['body' => $body, 'error' => ''];
}

/**
 * Resolves a CA bundle path from PHP ini or common local installs.
 *
 * @return string|null Absolute ca-bundle path when found.
 */
function resolve_ca_bundle(): ?string
{
	$candidates = [
		(string) ini_get('curl.cainfo'),
		(string) ini_get('openssl.cafile'),
		(string) getenv('SSL_CERT_FILE'),
		getenv('HOME') !== false
			? getenv('HOME') . '/.config/herd/config/php/cacert.pem'
			: '',
		getenv('USERPROFILE') !== false
			? getenv('USERPROFILE') . '\\.config\\herd\\config\\php\\cacert.pem'
			: '',
	];

	foreach ($candidates as $candidate) {
		if ($candidate !== '' && is_file($candidate)) {
			return $candidate;
		}
	}

	return null;
}

download_strauss_phar();
