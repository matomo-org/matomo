module.exports = {
  preset: '@vue/cli-plugin-unit-jest/presets/typescript-and-babel',
  transform: {
    '^.+\\.vue$': 'vue-jest',
    '^.+\\.ts$': 'ts-jest',
  },
  testMatch: [
    '**/plugins/*/vue/**/*.spec.[tj]s',
  ],
  globals: {
    'ts-jest': {
      tsconfig: './tsconfig.spec.json',
    },
  },
  setupFilesAfterEnv: ['./tests/angularjs/bootstrap.jest.js'],
};
