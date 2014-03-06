describe('should', function() {
  var should = chai.Should();

  it('assertion', function(){
    'test'.should.be.a('string');
    should.equal('foo', 'foo');
    should.not.equal('foo', 'bar');
  });

  it('root exist', function () {
    var foo = 'foo'
      , bar = undefined;
    should.exist(foo);
    should.not.exist(bar);

    err(function () {
      should.exist(bar, 'blah');
    }, "blah: expected undefined to exist");

    err(function () {
      should.not.exist(foo, 'blah');
    }, "blah: expected 'foo' to not exist")
  });

  it('root equal', function () {
    var value1 = 'value'
      , value2 = 'value'
      , foo = 'foo';
    should.equal(value1, value2);
    should.not.equal(value1, foo);

    err(function () {
      should.equal(value1, foo, 'blah');
    }, "blah: expected 'value' to equal 'foo'");

    err(function () {
      should.not.equal(value1, value2, 'blah');
    }, "blah: expected 'value' to not equal 'value'")
  });

  it('root Throw', function () {
    should.Throw(function() { throw new Error('error!') }, Error, 'error!');
    should.not.Throw(function () { });

    err(function () {
      should.Throw(function () { throw new Error('error!') }, Error, 'needed user!', 'blah');
    }, "blah: expected [Function] to throw error including 'needed user!' but got 'error!'");

    err(function () {
      should.not.Throw(function () { throw new Error('error!') }, Error, 'error!', 'blah');
    }, "blah: expected [Function] to not throw 'Error' but 'Error: error!' was thrown");
  });

  it('true', function(){
    (true).should.be.true;
    false.should.not.be.true;
    (1).should.not.be.true;false
    false.should.have.been.false;

    err(function(){
      'test'.should.be.true;
    }, "expected 'test' to be true")
  });

  it('ok', function(){
    true.should.be.ok;
    false.should.not.be.ok;
    (1).should.be.ok;
    (0).should.not.be.ok;

    err(function(){
      ''.should.be.ok;
    }, "expected '' to be truthy");

    err(function(){
      'test'.should.not.be.ok;
    }, "expected 'test' to be falsy");
  });

  it('false', function(){
    false.should.be.false;
    true.should.not.be.false;
    (0).should.not.be.false;

    err(function(){
      ''.should.be.false;
    }, "expected '' to be false")
  });

  it('null', function(){
    (0).should.not.be.null;

    err(function(){
      ''.should.be.null;
    }, "expected '' to be null")
  });

  it('undefined', function(){
    (0).should.not.be.undefined;

    err(function(){
      ''.should.be.undefined;
    }, "expected '' to be undefined")
  });

  it('arguments', function(){
    var args = (function(){ return arguments; })(1,2,3);
    args.should.be.arguments;
    [].should.not.be.arguments;
  });

  it('.equal()', function(){
    var foo;
    should.equal(undefined, foo);
  });

  it('typeof', function(){
    'test'.should.be.a('string');

    err(function(){
      'test'.should.not.be.a('string');
    }, "expected 'test' not to be a string");

    (5).should.be.a('number');
    (new Number(1)).should.be.a('number');
    Number(1).should.be.a('number');
    (true).should.be.a('boolean');
    (new Array()).should.be.a('array');
    (new Object()).should.be.a('object');
    ({}).should.be.a('object');
    ([]).should.be.a('array');
    (function() {}).should.be.a('function');

    (5).should.be.a('number');

    err(function(){
      (5).should.not.be.a('number');
    }, "expected 5 not to be a number");
  });

  it('instanceof', function(){
    function Foo(){}
    new Foo().should.be.an.instanceof(Foo);

    err(function(){
      (3).should.an.instanceof(Foo, 'blah');
    }, "blah: expected 3 to be an instance of Foo");
  });

  it('within(start, finish)', function(){
    (5).should.be.within(5, 10);
    (5).should.be.within(3,6);
    (5).should.be.within(3,5);
    (5).should.not.be.within(1,3);

    err(function(){
      (5).should.not.be.within(4,6, 'blah');
    }, "blah: expected 5 to not be within 4..6");

    err(function(){
      (10).should.be.within(50,100, 'blah');
    }, "blah: expected 10 to be within 50..100");

    err(function(){
      ({ foo: 1 }).should.have.length.within(50,100, 'blah');
    }, "blah: expected { foo: 1 } to have a property 'length'");
  });

  it('above(n)', function(){
    (5).should.be.above(2);
    (5).should.be.greaterThan(2);
    (5).should.not.be.above(5);
    (5).should.not.be.above(6);

    err(function(){
      (5).should.be.above(6, 'blah');
    }, "blah: expected 5 to be above 6");

    err(function(){
      (10).should.not.be.above(6, 'blah');
    }, "blah: expected 10 to be at most 6");

    err(function(){
      ({foo: 1}).should.have.length.above(3, 'blah');
    }, "blah: expected { foo: 1 } to have a property 'length'");
  });

  it('least(n)', function(){
    (5).should.be.at.least(5);
    (5).should.not.be.at.least(6);

    err(function(){
      (5).should.be.at.least(6, 'blah');
    }, "blah: expected 5 to be at least 6");

    err(function(){
      (10).should.not.be.at.least(6, 'blah');
    }, "blah: expected 10 to be below 6");

    err(function(){
      ({foo: 1}).should.have.length.of.at.least(3, 'blah');
    }, "blah: expected { foo: 1 } to have a property 'length'");
  });

  it('below(n)', function(){
    (2).should.be.below(5);
    (2).should.be.lessThan(5);
    (2).should.not.be.below(2);
    (2).should.not.be.below(1);

    err(function(){
      (6).should.be.below(5, 'blah');
    }, "blah: expected 6 to be below 5");

    err(function(){
      (6).should.not.be.below(10, 'blah');
    }, "blah: expected 6 to be at least 10");

    err(function(){
      ({foo: 1}).should.have.length.below(3, 'blah');
    }, "blah: expected { foo: 1 } to have a property 'length'");
  });

  it('most(n)', function(){
    (2).should.be.at.most(2);
    (2).should.not.be.at.most(1);

    err(function(){
      (6).should.be.at.most(5, 'blah');
    }, "blah: expected 6 to be at most 5");

    err(function(){
      (6).should.not.be.at.most(10, 'blah');
    }, "blah: expected 6 to be above 10");

    err(function(){
      ({foo: 1}).should.have.length.of.at.most(3, 'blah');
    }, "blah: expected { foo: 1 } to have a property 'length'");
  });

  it('match(regexp)', function(){
    'foobar'.should.match(/^foo/)
    'foobar'.should.not.match(/^bar/)

    err(function(){
      'foobar'.should.match(/^bar/i, 'blah')
    }, "blah: expected 'foobar' to match /^bar/i");

    err(function(){
      'foobar'.should.not.match(/^foo/i, 'blah')
    }, "blah: expected 'foobar' not to match /^foo/i");
  });

  it('length(n)', function(){
    'test'.should.have.length(4);
    'test'.should.not.have.length(3);
    [1,2,3].should.have.length(3);

    err(function(){
      (4).should.have.length(3, 'blah');
    }, 'blah: expected 4 to have a property \'length\'');

    err(function(){
      'asd'.should.not.have.length(3, 'blah');
    }, "blah: expected 'asd' to not have a length of 3");
  });

  it('eql(val)', function(){
    var a = new Date(1, 2, 3)
      , b = new Date(4, 5, 6);

    a.should.eql(a);
    a.should.not.eql(b);
    a.should.not.eql({});
    'test'.should.eql('test');
    ({ foo: 'bar' }).should.eql({ foo: 'bar' });
    /a/.should.eql(/a/);
    /a/.should.not.eql(/b/);
    /a/.should.not.eql({});
    /a/g.should.eql(/a/g);
    /a/g.should.not.eql(/b/g);
    /a/i.should.eql(/a/i);
    /a/i.should.not.eql(/b/i);
    /a/m.should.eql(/a/m);
    /a/m.should.not.eql(/b/m);
    (1).should.eql(1);
    '4'.should.not.eql(4);

    err(function(){
      (4).should.eql(3, 'blah');
    }, 'blah: expected 4 to deeply equal 3');
  });

  it('equal(val)', function(){
    'test'.should.equal('test');
    (1).should.equal(1);

    err(function(){
      (4).should.equal(3, 'blah');
    }, 'blah: expected 4 to equal 3');

    err(function(){
      '4'.should.equal(4, 'blah');
    }, "blah: expected '4' to equal 4");
  });

  it('empty', function(){
    function FakeArgs() {};
    FakeArgs.prototype.length = 0;

    ''.should.be.empty;
    'foo'.should.not.be.empty;
    ([]).should.be.empty;
    (['foo']).should.not.be.empty;
    (new FakeArgs).should.be.empty;
    ({arguments: 0}).should.not.be.empty;
    ({}).should.be.empty;
    ({foo: 'bar'}).should.not.be.empty;

    err(function(){
      ''.should.not.be.empty;
    }, "expected \'\' not to be empty");

    err(function(){
      'foo'.should.be.empty;
    }, "expected \'foo\' to be empty");

    err(function(){
      ([]).should.not.be.empty;
    }, "expected [] not to be empty");

    err(function(){
      (['foo']).should.be.empty;
    }, "expected [ \'foo\' ] to be empty");

    err(function(){
      (new FakeArgs).should.not.be.empty;
    }, "expected { length: 0 } not to be empty");

    err(function(){
      ({arguments: 0}).should.be.empty;
    }, "expected { arguments: 0 } to be empty");

    err(function(){
      ({}).should.not.be.empty;
    }, "expected {} not to be empty");

    err(function(){
      ({foo: 'bar'}).should.be.empty;
    }, "expected { foo: \'bar\' } to be empty");
  });

  it('property(name)', function(){
    'test'.should.have.property('length');
    (4).should.not.have.property('length');

    err(function(){
      'asd'.should.have.property('foo');
    }, "expected 'asd' to have a property 'foo'");
  });

  it('property(name, val)', function(){
    'test'.should.have.property('length', 4);
    'asd'.should.have.property('constructor', String);

    err(function(){
      'asd'.should.have.property('length', 4, 'blah');
    }, "blah: expected 'asd' to have a property 'length' of 4, but got 3");

    err(function(){
      'asd'.should.not.have.property('length', 3, 'blah');
    }, "blah: expected 'asd' to not have a property 'length' of 3");

    err(function(){
      'asd'.should.not.have.property('foo', 3, 'blah');
    }, "blah: 'asd' has no property 'foo'");

    err(function(){
      'asd'.should.have.property('constructor', Number, 'blah');
    }, "blah: expected 'asd' to have a property 'constructor' of [Function: Number], but got [Function: String]");
  });

  it('ownProperty(name)', function(){
    'test'.should.have.ownProperty('length');
    'test'.should.haveOwnProperty('length');
    ({ length: 12 }).should.have.ownProperty('length');

    err(function(){
      ({ length: 12 }).should.not.have.ownProperty('length', 'blah');
    }, "blah: expected { length: 12 } to not have own property 'length'");
  });

  it('string()', function(){
    'foobar'.should.contain.string('bar');
    'foobar'.should.contain.string('foo');
    'foobar'.should.not.contain.string('baz');

    err(function(){
      (3).should.contain.string('baz', 'blah');
    }, "blah: expected 3 to be a string");

    err(function(){
      'foobar'.should.contain.string('baz', 'blah');
    }, "blah: expected 'foobar' to contain 'baz'");

    err(function(){
      'foobar'.should.not.contain.string('bar', 'blah');
    }, "blah: expected 'foobar' to not contain 'bar'");
  });

  it('include()', function(){
    ['foo', 'bar'].should.include('foo');
    ['foo', 'bar'].should.contain('foo');
    ['foo', 'bar'].should.include('bar');
    [1,2].should.include(1);
    ['foo', 'bar'].should.not.include('baz');
    ['foo', 'bar'].should.not.include(1);
    ({a:1,b:2}).should.include({b:2});
    ({a:1,b:2}).should.not.include({b:3});

    err(function(){
      ['foo'].should.include('bar', 'blah');
    }, "blah: expected [ 'foo' ] to include 'bar'");

    err(function(){
      ['bar', 'foo'].should.not.include('foo', 'blah');
    }, "blah: expected [ 'bar', 'foo' ] to not include 'foo'");

    err(function(){
      ({a:1}).should.include({b:2});
    }, "expected { a: 1 } to have a property 'b'")
  });

  it('keys(array)', function(){
    ({ foo: 1 }).should.have.keys(['foo']);
    ({ foo: 1, bar: 2 }).should.have.keys(['foo', 'bar']);
    ({ foo: 1, bar: 2 }).should.have.keys('foo', 'bar');
    ({ foo: 1, bar: 2, baz: 3 }).should.include.keys('foo', 'bar');
    ({ foo: 1, bar: 2, baz: 3 }).should.contain.keys('bar', 'foo');
    ({ foo: 1, bar: 2, baz: 3 }).should.contain.keys('baz');

    ({ foo: 1, bar: 2 }).should.contain.keys('foo');
    ({ foo: 1, bar: 2 }).should.contain.keys('bar', 'foo');
    ({ foo: 1, bar: 2 }).should.contain.keys(['foo']);
    ({ foo: 1, bar: 2 }).should.contain.keys(['bar']);
    ({ foo: 1, bar: 2 }).should.contain.keys(['bar', 'foo']);

    ({ foo: 1, bar: 2 }).should.not.have.keys('baz');
    ({ foo: 1, bar: 2 }).should.not.have.keys('foo', 'baz');
    ({ foo: 1, bar: 2 }).should.not.contain.keys('baz');
    ({ foo: 1, bar: 2 }).should.not.contain.keys('foo', 'baz');
    ({ foo: 1, bar: 2 }).should.not.contain.keys('baz', 'foo');

    err(function(){
      ({ foo: 1 }).should.have.keys();
    }, "keys required");

    err(function(){
      ({ foo: 1 }).should.have.keys([]);
    }, "keys required");

    err(function(){
      ({ foo: 1 }).should.not.have.keys([]);
    }, "keys required");

    err(function(){
      ({ foo: 1 }).should.contain.keys([]);
    }, "keys required");

    err(function(){
      ({ foo: 1 }).should.have.keys(['bar']);
    }, "expected { foo: 1 } to have key 'bar'");

    err(function(){
      ({ foo: 1 }).should.have.keys(['bar', 'baz']);
    }, "expected { foo: 1 } to have keys 'bar', and 'baz'");

    err(function(){
      ({ foo: 1 }).should.have.keys(['foo', 'bar', 'baz']);
    }, "expected { foo: 1 } to have keys 'foo', 'bar', and 'baz'");

    err(function(){
      ({ foo: 1 }).should.not.have.keys(['foo']);
    }, "expected { foo: 1 } to not have key 'foo'");

    err(function(){
      ({ foo: 1 }).should.not.have.keys(['foo']);
    }, "expected { foo: 1 } to not have key 'foo'");

    err(function(){
      ({ foo: 1, bar: 2 }).should.not.have.keys(['foo', 'bar']);
    }, "expected { foo: 1, bar: 2 } to not have keys 'foo', and 'bar'");

    err(function(){
      ({ foo: 1 }).should.not.contain.keys(['foo']);
    }, "expected { foo: 1 } to not contain key 'foo'");

    err(function(){
      ({ foo: 1 }).should.contain.keys('foo', 'bar');
    }, "expected { foo: 1 } to contain keys 'foo', and 'bar'");
  });

  it('throw', function () {
    // See GH-45: some poorly-constructed custom errors don't have useful names
    // on either their constructor or their constructor prototype, but instead
    // only set the name inside the constructor itself.
    var PoorlyConstructedError = function () {
      this.name = 'PoorlyConstructedError';
    };
    PoorlyConstructedError.prototype = Object.create(Error.prototype);

    function CustomError(message) {
        this.name = 'CustomError';
        this.message = message;
    }
    CustomError.prototype = Error.prototype;

    var specificError = new RangeError('boo');

    var goodFn = function () { 1==1; }
      , badFn = function () { throw new Error('testing'); }
      , stringErrFn = function () { throw 'testing'; }
      , refErrFn = function () { throw new ReferenceError('hello'); }
      , ickyErrFn = function () { throw new PoorlyConstructedError(); }
      , specificErrFn = function () { throw specificError; }
      , customErrFn = function() { throw new CustomError('foo'); };

    (goodFn).should.not.throw();
    (goodFn).should.not.throw(Error);
    (goodFn).should.not.throw(specificError);
    (badFn).should.throw();
    (badFn).should.throw(Error);
    (badFn).should.not.throw(ReferenceError);
    (badFn).should.not.throw(specificError);
    (stringErrFn).should.throw();
    (stringErrFn).should.not.throw(ReferenceError);
    (stringErrFn).should.not.throw(specificError);
    (refErrFn).should.throw();
    (refErrFn).should.throw(ReferenceError);
    (refErrFn).should.throw(Error);
    (refErrFn).should.not.throw(TypeError);
    (refErrFn).should.not.throw(specificError);
    (ickyErrFn).should.throw();
    (ickyErrFn).should.throw(PoorlyConstructedError);
    (ickyErrFn).should.throw(Error);
    (ickyErrFn).should.not.throw(specificError);
    (specificErrFn).should.throw(specificError);

    (badFn).should.throw(/testing/);
    (badFn).should.throw('testing');
    (badFn).should.not.throw(/hello/);
    (badFn).should.not.throw('hello');
    (badFn).should.throw(Error, /testing/);
    (badFn).should.throw(Error, 'testing');

    (stringErrFn).should.throw(/testing/);
    (stringErrFn).should.throw('testing');
    (stringErrFn).should.not.throw(/hello/);
    (stringErrFn).should.not.throw('hello');

    should.throw(badFn);
    should.throw(refErrFn, ReferenceError);
    should.throw(refErrFn, Error);
    should.throw(ickyErrFn, PoorlyConstructedError);
    should.throw(specificErrFn, specificError);
    should.not.throw(goodFn);
    should.not.throw(badFn, ReferenceError);
    should.not.throw(badFn, specificError);

    should.throw(badFn, Error, /testing/);
    should.throw(badFn, Error, 'testing');

    err(function(){
      (goodFn).should.throw();
    }, "expected [Function] to throw an error");

    err(function(){
      (goodFn).should.throw(ReferenceError);
    }, "expected [Function] to throw ReferenceError");

    err(function(){
      (goodFn).should.throw(specificError);
    }, "expected [Function] to throw 'RangeError: boo'");

    err(function(){
      (badFn).should.not.throw();
    }, "expected [Function] to not throw an error but 'Error: testing' was thrown");

    err(function(){
      (badFn).should.throw(ReferenceError);
    }, "expected [Function] to throw 'ReferenceError' but 'Error: testing' was thrown");

    err(function(){
      (badFn).should.throw(specificError);
    }, "expected [Function] to throw 'RangeError: boo' but 'Error: testing' was thrown");

    err(function(){
      (badFn).should.not.throw(Error);
    }, "expected [Function] to not throw 'Error' but 'Error: testing' was thrown");

    err(function(){
      (stringErrFn).should.not.throw();
    }, "expected [Function] to not throw an error but 'testing' was thrown");

    err(function(){
      (stringErrFn).should.throw(ReferenceError);
    }, "expected [Function] to throw 'ReferenceError' but 'testing' was thrown");

    err(function(){
      (stringErrFn).should.throw(specificError);
    }, "expected [Function] to throw 'RangeError: boo' but 'testing' was thrown");

    err(function(){
      (stringErrFn).should.not.throw('testing');
    }, "expected [Function] to throw error not including 'testing'");

    err(function(){
      (refErrFn).should.not.throw(ReferenceError);
    }, "expected [Function] to not throw 'ReferenceError' but 'ReferenceError: hello' was thrown");

    err(function(){
      (badFn).should.throw(PoorlyConstructedError);
    }, "expected [Function] to throw 'PoorlyConstructedError' but 'Error: testing' was thrown")

    err(function(){
      (ickyErrFn).should.not.throw(PoorlyConstructedError);
    }, /^(expected \[Function\] to not throw 'PoorlyConstructedError' but)(.*)(PoorlyConstructedError|\{ Object \()(.*)(was thrown)$/);

    err(function(){
      (ickyErrFn).should.throw(ReferenceError);
    }, /^(expected \[Function\] to throw 'ReferenceError' but)(.*)(PoorlyConstructedError|\{ Object \()(.*)(was thrown)$/);

    err(function(){
      (specificErrFn).should.throw(new ReferenceError('eek'));
    }, "expected [Function] to throw 'ReferenceError: eek' but 'RangeError: boo' was thrown");

    err(function(){
      (specificErrFn).should.not.throw(specificError);
    }, "expected [Function] to not throw 'RangeError: boo'");

    err(function (){
      (badFn).should.not.throw(/testing/);
    }, "expected [Function] to throw error not matching /testing/");

    err(function () {
      (badFn).should.throw(/hello/);
    }, "expected [Function] to throw error matching /hello/ but got \'testing\'");

    err(function () {
      (badFn).should.throw(Error, /hello/, 'blah');
    }, "blah: expected [Function] to throw error matching /hello/ but got 'testing'");

    err(function () {
      (badFn).should.throw(Error, 'hello', 'blah');
    }, "blah: expected [Function] to throw error including 'hello' but got 'testing'");

    err(function () {
      (customErrFn).should.not.throw();
    }, "expected [Function] to not throw an error but 'CustomError: foo' was thrown");
  });

  it('respondTo', function(){
    function Foo(){};
    Foo.prototype.bar = function(){};
    Foo.func = function(){};

    var bar = {};
    bar.foo = function(){};

    Foo.should.respondTo('bar');
    Foo.should.not.respondTo('foo');
    Foo.should.itself.respondTo('func');
    Foo.should.itself.not.respondTo('bar');

    bar.should.respondTo('foo');

    err(function(){
      Foo.should.respondTo('baz', 'constructor');
    }, /^(constructor: expected)(.*)(\[Function: Foo\])(.*)(to respond to \'baz\')$/);

    err(function(){
      bar.should.respondTo('baz', 'object');
    }, /^(object: expected)(.*)(\{ foo: \[Function\] \}|\{ Object \()(.*)(to respond to \'baz\')$/);
  });

  it('satisfy', function(){
    var matcher = function (num){
      return num === 1;
    };

    (1).should.satisfy(matcher);

    err(function(){
      (2).should.satisfy(matcher, 'blah');
    }, "blah: expected 2 to satisfy [Function]");
  });

  it('closeTo', function(){
    (1.5).should.be.closeTo(1.0, 0.5);

    err(function(){
      (2).should.be.closeTo(1.0, 0.5, 'blah');
    }, "blah: expected 2 to be close to 1 +/- 0.5");
  });

  it('include.members', function() {
    [1, 2, 3].should.include.members([3]);
    [1, 2, 3].should.include.members([]);
    [1, 2, 3].should.include.members([2, 1]);

    [1, 2, 3].should.not.include.members([999]);
    [].should.not.include.members([23]);

    err(function() {
      [].should.include.members([43]);
    }, 'expected [] to be a superset of [ 43 ]');

    err(function() {
      [5, 2, 1].should.not.include.members([2]);
    }, 'expected [ 5, 2, 1 ] to not be a superset of [ 2 ]');

    err(function() {
      'foo'.should.include.members([12]);
    }, "expected 'foo' to be an array");

    err(function() {
      [1, 2, 3].should.include.members('o');
    }, "expected 'o' to be an array");
  });

  it('memberEquals', function() {
    [1, 2, 3].should.have.same.members([3, 2, 1]);
    [5, 4].should.have.same.members([5, 4]);
    [].should.have.same.members([]);

    err(function() {
      [1, 2, 3].should.have.same.members([]);
    }, 'expected [ 1, 2, 3 ] to have the same members as []');

    err(function() {
      [1, 2, 3].should.have.same.members(4);
    }, 'expected 4 to be an array');
  });
});
