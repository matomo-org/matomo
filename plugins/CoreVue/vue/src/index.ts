function doSomething():Promise<void> {
  console.log('starting');
  return (new Promise((resolve) => setTimeout(resolve, 2000))).then(() => {
    console.log('did something');
  });
}

function doAnotherSomething(a: Record<string, unknown>):void {
  const b = { a: 'b', c: 'd' };
  console.log({ ...a, ...b });
}

const exampleLoaded = 22;

export {
  doSomething,
  doAnotherSomething,
  exampleLoaded,
};
