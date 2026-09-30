/**
 * Builds CSS entries for one plugin under plugins/ with the theme PostCSS stack.
 *
 * Discovers plugins/<slug>/assets/{frontend,admin}/css/*.css and writes
 * plugins/<slug>/build/<basename>.css (entry basename = output basename).
 * Reuses the package-root postcss.config.js (@tailwindcss/postcss).
 *
 * Usage:
 *   node node_scripts/build-plugin-css.js <plugin-name> [--watch] [--minify]
 */

import { spawn, spawnSync } from 'child_process';
import { existsSync, mkdirSync, readdirSync } from 'fs';
import { basename, dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const rootDir = join(__dirname, '..');
const args = process.argv.slice(2);
const pluginName = args.find((arg) => !arg.startsWith('--'));
const isProduction = args.includes('--minify');
const isWatch = args.includes('--watch');
const postcssBin = join(rootDir, 'node_modules', '.bin', 'postcss');

if (!pluginName) {
	console.error(
		'Usage: node node_scripts/build-plugin-css.js <plugin-name> [--watch] [--minify]',
	);
	process.exit(1);
}

const pluginDir = join(rootDir, 'plugins', pluginName);

if (!existsSync(pluginDir)) {
	console.error(`Plugin directory does not exist: ${pluginDir}`);
	process.exit(1);
}

if (!existsSync(postcssBin)) {
	console.error(`postcss CLI not found at ${postcssBin}. Run pnpm install in the package root.`);
	process.exit(1);
}

/**
 * @typedef {{ input: string, output: string, fileName: string }} CssEntry
 */

/**
 * Discovers CSS entries under assets/{frontend,admin}/css/.
 *
 * @param {string} directory - Absolute plugin directory.
 * @returns {CssEntry[]} Entries to build. Missing CSS dirs are skipped.
 */
function discoverCssEntries(directory) {
	/** @type {CssEntry[]} */
	const entries = [];
	/** @type {Map<string, string>} */
	const basenames = new Map();

	for (const context of ['frontend', 'admin']) {
		const cssDir = join(directory, 'assets', context, 'css');

		if (!existsSync(cssDir)) {
			continue;
		}

		for (const fileName of readdirSync(cssDir).sort()) {
			if (!fileName.endsWith('.css')) {
				continue;
			}

			const previousContext = basenames.get(fileName);

			if (previousContext) {
				throw new Error(
					`Duplicate plugin CSS basename "${fileName}" in "${pluginName}" ` +
						`(assets/${previousContext}/css and assets/${context}/css). ` +
						`Use unique filenames so build/${fileName} is unambiguous.`,
				);
			}

			basenames.set(fileName, context);
			entries.push({
				input: join(cssDir, fileName),
				output: join(directory, 'build', fileName),
				fileName,
			});
		}
	}

	return entries;
}

/**
 * Environment for PostCSS, including production cssnano via _TW_ENV.
 *
 * @returns {NodeJS.ProcessEnv}
 */
function postcssEnv() {
	const env = { ...process.env };

	if (isProduction) {
		env._TW_ENV = 'production';
	}

	return env;
}

/**
 * Builds one CSS entry once with the PostCSS CLI.
 *
 * @param {CssEntry} entry - Input/output pair.
 * @returns {void}
 */
function buildEntry(entry) {
	mkdirSync(dirname(entry.output), { recursive: true });

	const result = spawnSync(postcssBin, [entry.input, '-o', entry.output], {
		cwd: rootDir,
		env: postcssEnv(),
		stdio: 'inherit',
	});

	if (result.error) {
		throw result.error;
	}

	if ((result.status ?? 1) !== 0) {
		process.exit(result.status ?? 1);
	}
}

/**
 * Watches one CSS entry with the PostCSS CLI.
 *
 * @param {CssEntry} entry - Input/output pair.
 * @returns {import('child_process').ChildProcess}
 */
function watchEntry(entry) {
	mkdirSync(dirname(entry.output), { recursive: true });

	return spawn(postcssBin, [entry.input, '-o', entry.output, '--watch'], {
		cwd: rootDir,
		env: postcssEnv(),
		stdio: 'inherit',
	});
}

/**
 * Builds or watches discovered CSS entries for this plugin.
 *
 * @returns {void}
 */
function main() {
	try {
		const entries = discoverCssEntries(pluginDir);

		if (entries.length === 0) {
			console.log(`No plugin CSS entries found for "${pluginName}", skipping CSS build.`);
			process.exit(0);
		}

		if (isWatch) {
			const children = entries.map((entry) => watchEntry(entry));
			let running = children.length;
			let failed = false;

			console.log(
				`Watching plugin "${pluginName}" CSS (${entries.map((entry) => basename(entry.input)).join(', ')})...`,
			);

			/**
			 * Stops sibling watchers after one PostCSS process fails.
			 *
			 * @param {import('child_process').ChildProcess} failedChild - Process that exited with an error.
			 * @returns {void}
			 */
			function stopSiblings(failedChild) {
				for (const child of children) {
					if (
						child === failedChild ||
						child.exitCode !== null ||
						child.signalCode !== null
					) {
						continue;
					}

					child.kill('SIGTERM');
				}
			}

			/**
			 * Records one child as finished. `error` and `exit` can both fire.
			 *
			 * @param {import('child_process').ChildProcess & { settled?: boolean }} child - Watched process.
			 * @param {number | null} code - Exit code. Null counts as a failure.
			 * @returns {void}
			 */
			function settle(child, code) {
				if (child.settled) {
					return;
				}

				child.settled = true;

				if ((code ?? 1) !== 0) {
					failed = true;
					stopSiblings(child);
				}

				running -= 1;

				if (running === 0) {
					process.exit(failed ? 1 : 0);
				}
			}

			for (const child of children) {
				child.on('error', (error) => {
					console.error(error instanceof Error ? error.message : String(error));
					settle(child, 1);
				});

				child.on('exit', (code) => {
					settle(child, code);
				});
			}

			return;
		}

		for (const entry of entries) {
			buildEntry(entry);
		}

		console.log(
			`Plugin "${pluginName}" CSS built${isProduction ? ' for production' : ''} ` +
				`(${entries.map((entry) => entry.fileName).join(', ')}).`,
		);
		process.exit(0);
	} catch (error) {
		const message = error instanceof Error ? error.message : String(error);
		console.error(`Plugin "${pluginName}" CSS build failed:`, message);
		process.exit(1);
	}
}

main();
