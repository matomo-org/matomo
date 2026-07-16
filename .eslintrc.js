module.exports = {
  root: true,
  env: {
    node: true,
  },
  extends: [
    'plugin:vue/vue3-essential',
    '@vue/airbnb',
    '@vue/typescript/recommended',
  ],
  parserOptions: {
    ecmaVersion: 2020,
  },
  rules: {
    'no-console': 'off',
    'no-debugger': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
    'import/prefer-default-export': 'off',
    'no-useless-constructor': 'off',
    '@typescript-eslint/no-useless-constructor': ['error'],
    'class-methods-use-this': 'off',
    "@typescript-eslint/no-this-alias": [
      "error",
      {
        "allowDestructuring": true,
        "allowedNames": ["self"],
      }
    ],
    'no-param-reassign': ["error", { "props": false }],
    'camelcase': 'off',
    '@typescript-eslint/no-non-null-assertion': 'off',

    // typescript will provide similar error messages, potentially conflicting ones, for
    // the following rules, so we disable them
    'no-undef': 'off',
    'no-undef-init': 'off',
    'import/extensions': 'off',

    // error on imports using ../vue/src paths - use e.g. `from 'CoreHome'` instead
    'no-restricted-imports': ['error', {
      patterns: ['**/vue/src'],
    }],
    'vue/component-tags-order': ['warn', {
      order: ['template', 'script', 'style'],
    }],

    // Plugin Vue libraries are referenced by their plain name (e.g. `from 'CoreHome'`) and resolved
    // at runtime against UMD globals; the eslint import resolver cannot follow that scheme, and
    // TypeScript/the build already validate these imports.
    'import/no-unresolved': 'off',
    // Matomo intentionally uses single-word component names (e.g. Alert, Field) for its libraries.
    'vue/multi-word-component-names': 'off',
  },
  overrides: [
    {
      // Vue/Vitest specs intentionally place component imports after vi.mock() calls and import
      // test utilities that live in devDependencies.
      files: ['**/*.spec.ts', '**/*.spec.js'],
      rules: {
        'import/first': 'off',
        'import/no-extraneous-dependencies': 'off',
      },
    },
  ],
};
