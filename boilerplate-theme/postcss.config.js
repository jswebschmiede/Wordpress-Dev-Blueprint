export default () => {
    /*
     * Elements Tailwind Typography leaves alone. Theme block wrappers are excluded themselves
     * (not their descendants), so the `.prose > :first-child` / `:last-child` margin reset
     * does not override the block margins from tailwind/custom/components.
     */
    const whereNot = ["[class~='not-prose']", "[class~='not-prose'] *", "[class*='wp-block-boilerplate-']"];

    if ('editor' === process.env._TW_TARGET) {
        whereNot.push("[class~='acf-block-body']:not([class~='acf-block-preview']) *");
    }

    const plugins = {
        'postcss-import-ext-glob': {},
        '@tailwindcss/postcss': {
            optimize: false,
            transformAssetUrls: false,
        },
        'postcss-advanced-variables': {
            variables: {
                target: process.env._TW_TARGET || 'frontend',
                'where-not': whereNot.join(', '),
            },
        },
        'postcss-nesting': {},
    };

    if ('production' === process.env._TW_ENV) {
        plugins.cssnano = {};
    }

    if ('intellisense' === process.env._TW_TARGET) {
        delete plugins['@tailwindcss/postcss'];

        plugins.cssnano = {};
        plugins['postcss-advanced-variables'] = {
            importFilter: () => {
                return false;
            },
            unresolved: 'ignore',
            variables: {
                target: 'editor',
            },
        };
        plugins['postcss-header'] = {
            header: '/*!\n * IMPORTANT: This is a generated file. EDITS WILL BE OVERWRITTEN.\n * This file enables full Tailwind CSS Language Server support for all imports.\n */',
        };
    }

    return { plugins };
};
