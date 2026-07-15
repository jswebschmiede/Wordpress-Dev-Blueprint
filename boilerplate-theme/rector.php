<?php
/**
 * Rector rules to automatically refactor code to modern syntax.
 *
 * @package boilerplate-theme
 */

declare( strict_types=1 );

use Rector\Config\RectorConfig;
use Rector\Php54\Rector\Array_\LongArrayToShortArrayRector;
use Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;
use Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector;

return RectorConfig::configure()
	->withPaths(
		array(
			__DIR__ . '/theme',
			__DIR__ . '/plugins',
		)
	)
	->withSkip(
		array(
			__DIR__ . '/vendor',
			__DIR__ . '/theme/vendor',
			__DIR__ . '/plugins/boilerplate-plugin/vendor',
			__DIR__ . '/node_modules',
			__DIR__ . '/tests',
			LongArrayToShortArrayRector::class,
			ChangeSwitchToMatchRector::class,
			ArrayToFirstClassCallableRector::class,
		)
	)
	->withPhpSets( php83: true )
	->withPreparedSets(
		deadCode: false,
		codeQuality: false,
		codingStyle: false,
		typeDeclarations: false,
		privatization: false,
		naming: false,
		instanceOf: false,
		earlyReturn: false,
		strictBooleans: false,
	);
