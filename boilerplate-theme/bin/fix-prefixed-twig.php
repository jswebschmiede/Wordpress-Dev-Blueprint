<?php

declare(strict_types=1);

/**
 * Rewrites unprefixed `Twig\` references inside Twig's code-generation strings after Strauss.
 *
 * Strauss prefixes PHP symbols but not the class names Twig writes as strings into compiled
 * templates (e.g. `->write("use Twig\Template;\n")` in ModuleNode). Without this step every
 * compiled template fails with `Class "Twig\Template" not found`.
 *
 * Run from a package directory (theme or plugin) whose composer.json configures `extra.strauss`.
 */

/**
 * Reads target directory and namespace prefix from the Strauss config in composer.json.
 *
 * @param string $composer_json Absolute path to composer.json.
 * @return array{target_directory: string, namespace_prefix: string}
 */
function read_strauss_config(string $composer_json): array
{
	$config = json_decode((string) file_get_contents($composer_json), true);
	$strauss = is_array($config) ? ($config['extra']['strauss'] ?? []) : [];

	return [
		'target_directory' => (string) ($strauss['target_directory'] ?? 'vendor-prefixed'),
		'namespace_prefix' => trim((string) ($strauss['namespace_prefix'] ?? ''), '\\'),
	];
}

/**
 * Prefixes `Twig\` references that are not already namespaced.
 *
 * Handles single (`\Twig\Template`) and escaped (`'\\Twig\\Template'`) separators.
 *
 * @param string $source           PHP source code.
 * @param string $namespace_prefix Namespace prefix without leading/trailing backslash.
 * @return string Updated source code.
 */
function prefix_twig_references(string $source, string $namespace_prefix): string
{
	$segments = explode('\\', $namespace_prefix);

	return (string) preg_replace_callback(
		'/(?<![A-Za-z0-9_\\\\])((?:\\\\){0,2})Twig((?:\\\\){1,2})(?=[A-Z])/',
		static function (array $matches) use ($segments): string {
			$separator = $matches[2];

			return $matches[1] . implode($separator, $segments) . $separator . 'Twig' . $separator;
		},
		$source
	);
}

$package_dir = getcwd();
$composer_json = $package_dir . DIRECTORY_SEPARATOR . 'composer.json';

if (!is_file($composer_json)) {
	fwrite(STDERR, "composer.json not found in {$package_dir}" . PHP_EOL);
	exit(1);
}

$config = read_strauss_config($composer_json);

if ($config['namespace_prefix'] === '') {
	fwrite(STDERR, 'extra.strauss.namespace_prefix is empty, nothing to fix.' . PHP_EOL);
	exit(0);
}

$twig_src = $package_dir . DIRECTORY_SEPARATOR . $config['target_directory'] . '/twig/twig/src';

if (!is_dir($twig_src)) {
	exit(0);
}

$changed = 0;
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($twig_src, FilesystemIterator::SKIP_DOTS));

foreach ($iterator as $file) {
	if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
		continue;
	}

	$path = $file->getPathname();
	$source = (string) file_get_contents($path);
	$updated = prefix_twig_references($source, $config['namespace_prefix']);

	if ($updated !== $source) {
		file_put_contents($path, $updated);
		++$changed;
	}
}

fwrite(STDOUT, "fix-prefixed-twig: updated {$changed} file(s) in {$twig_src}" . PHP_EOL);
