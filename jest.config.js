module.exports={
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
      tsconfig: 'tsconfig.spec.json',
    },
  },
  setupFiles: ['./tests/client/bootstrap.jest.js'],
};
