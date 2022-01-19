module.exports = {
  plugins: ['import'],
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
    'import/no-unresolved': 'error',
    'no-console': 'off',
    'no-debugger': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
    'import/prefer-default-export': 'off',
    'no-useless-constructor': 'off',
    '@typescript-eslint/no-useless-constructor': ['error'],
    'class-methods-use-this': 'off',
    '@typescript-eslint/no-this-alias': [
      'error',
      {
        'allowDestructuring': true,
        'allowedNames': ['self'],
      },
    ],
    'no-param-reassign': ['error', { 'props': false }],
    'camelcase': 'off',
    '@typescript-eslint/no-non-null-assertion': 'off',

    // typescript will provide similar error messages, potentially conflicting ones, for
    // the following rules, so we disable them
    'no-undef': 'off',
    'no-undef-init': 'off',
    'import/extensions': 'off',
  },
  settings:{
    "import/parsers": {
      "@typescript-eslint/parser": [".ts", ".tsx"]
    },
    "import/resolver": {
      "typescript": {
        "alwaysTryTypes": true, // always try to resolve types under `<root>@types` directory even it doesn't contain any source code, like `@types/unist`
        // Choose from one of the "project" configs below or omit to use <root>/tsconfig.json by default
        // use <root>/path/to/folder/tsconfig.json
        "project": "tsconfig.json",

      }
    }
  }
};
