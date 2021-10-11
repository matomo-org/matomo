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
  },
};
