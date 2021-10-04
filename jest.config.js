function getTsConfigJson() {
  const config = require('./tsconfig.json').compilerOptions;
  config.types.push("jest");
  return config;
}

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
      tsconfig: getTsConfigJson(),
    },
  },
  setupFilesAfterEnv: ['./tests/angularjs/bootstrap.jest.js'],
};
