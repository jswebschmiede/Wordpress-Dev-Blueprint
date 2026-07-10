import globals from 'globals';
import js from '@eslint/js';
import prettier from 'eslint-config-prettier';

export default [
    {
        ignores: ['**/*.min.js', '**/vendor/'],
    },
    {
        files: ['**/*.js'],
        languageOptions: {
            ecmaVersion: 'latest',
            parserOptions: {
                ecmaFeatures: {
                    jsx: true,
                },
            },
        },
        rules: {
            ...js.configs.recommended.rules,
            ...prettier.rules,
        },
    },
    {
        files: ['plugins/**/*.js'],
        languageOptions: {
            globals: {
                ...globals.browser,
                wp: 'readonly',
                jQuery: 'readonly',
            },
        },
    },
    {
        files: ['blocks/**/*.js'],
        languageOptions: {
            globals: {
                ...globals.browser,
                wp: 'readonly',
            },
        },
        rules: {
            'no-unused-vars': [
                'error',
                {
                    varsIgnorePattern: '^[A-Z]',
                    argsIgnorePattern: '^[A-Z]',
                },
            ],
        },
    },
    {
        files: ['javascript/**/*.js'],
        languageOptions: {
            globals: {
                ...globals.browser,
                wp: 'readonly',
            },
        },
        rules: {
            'no-unused-vars': [
                'error',
                {
                    varsIgnorePattern: '^[A-Z]',
                    argsIgnorePattern: '^[A-Z]',
                },
            ],
        },
    },
    {
        files: ['node_scripts/*.js', 'tailwind/*.js', 'postcss.config.js'],
        languageOptions: {
            globals: {
                ...globals.node,
            },
        },
    },
];
