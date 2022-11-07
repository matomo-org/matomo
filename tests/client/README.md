## Installation:

`[sudo] npm install .`

#### Requirements
* node.js
* npm

## Used libraries
* [Jest](https://jestjs.io/)

## File structure

We do not have a general `tests` folder containing all test files. Instead we create a file having the same name appended by `.spec.ts` in the same directory.

For instance you want to test a file named `startfrom.ts` then we create a file named `startfrom.spec.js`:
`plugins/CoreHome/vue/src/startfrom.ts` =>
`plugins/CoreHome/vue/src/startfrom.spec.ts`

## Execution

Run `npm test` in the root Matomo directory. `npm test` will invoke Vue CLI, which in turn handles invoking the TypeScript compiler and Jest.

## Testing existing jQuery code

Just in case you want to write a test for your jQuery code you can do this the same way. You might be interested in the [Chai jQuery](http://chaijs.com/plugins/chai-jquery) plugin.
