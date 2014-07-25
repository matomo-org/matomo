// jslint.js
// 2013-03-19

// Copyright (c) 2002 Douglas Crockford  (www.JSLint.com)

// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:

// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.

// The Software shall be used for Good, not Evil.

// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.

// WARNING: JSLint will hurt your feelings.

// JSLINT is a global function. It takes two parameters.

//     var myResult = JSLINT(source, option);

// The first parameter is either a string or an array of strings. If it is a
// string, it will be split on '\n' or '\r'. If it is an array of strings, it
// is assumed that each string represents one line. The source can be a
// JavaScript text or a JSON text.

// The second parameter is an optional object of options that control the
// operation of JSLINT. Most of the options are booleans: They are all
// optional and have a default value of false. One of the options, predef,
// can be an array of names, which will be used to declare global variables,
// or an object whose keys are used as global names, with a boolean value
// that determines if they are assignable.

// If it checks out, JSLINT returns true. Otherwise, it returns false.

// If false, you can inspect JSLINT.errors to find out the problems.
// JSLINT.errors is an array of objects containing these properties:

//  {
//      line      : The line (relative to 0) at which the lint was found
//      character : The character (relative to 0) at which the lint was found
//      reason    : The problem
//      evidence  : The text line in which the problem occurred
//      raw       : The raw message before the details were inserted
//      a         : The first detail
//      b         : The second detail
//      c         : The third detail
//      d         : The fourth detail
//  }

// If a stopping error was found, a null will be the last element of the
// JSLINT.errors array. A stopping error means that JSLint was not confident
// enough to continue. It does not necessarily mean that the error was
// especially heinous.

// You can request a data structure that contains JSLint's results.

//     var myData = JSLINT.data();

// It returns a structure with this form:

//     {
//         errors: [
//             {
//                 line: NUMBER,
//                 character: NUMBER,
//                 reason: STRING,
//                 evidence: STRING
//             }
//         ],
//         functions: [
//             {
//                 name: STRING,
//                 line: NUMBER,
//                 last: NUMBER,
//                 params: [
//                     {
//                         string: STRING
//                     }
//                 ],
//                 closure: [
//                     STRING
//                 ],
//                 var: [
//                     STRING
//                 ],
//                 exception: [
//                     STRING
//                 ],
//                 outer: [
//                     STRING
//                 ],
//                 unused: [
//                     STRING
//                 ],
//                 undef: [
//                     STRING
//                 ],
//                 global: [
//                     STRING
//                 ],
//                 label: [
//                     STRING
//                 ]
//             }
//         ],
//         globals: [
//             STRING
//         ],
//         member: {
//             STRING: NUMBER
//         },
//         urls: [
//             STRING
//         ],
//         json: BOOLEAN
//     }

// Empty arrays will not be included.

// You can request a Function Report, which shows all of the functions
// and the parameters and vars that they use. This can be used to find
// implied global variables and other problems. The report is in HTML and
// can be inserted in an HTML <body>. It should be given the result of the
// JSLINT.data function.

//     var myReport = JSLINT.report(data);

// You can request an HTML error report.

//     var myErrorReport = JSLINT.error_report(data);

// You can obtain an object containing all of the properties found in the
// file. JSLINT.property contains an object containing a key for each
// property used in the program, the value being the number of times that
// property name was used in the file.

// You can request a properties report, which produces a list of the program's
// properties in the form of a /*properties*/ declaration.

//      var myPropertyReport = JSLINT.properties_report(JSLINT.property);

// You can obtain the parse tree that JSLint constructed while parsing. The
// latest tree is kept in JSLINT.tree. A nice stringification can be produced
// with

//     JSON.stringify(JSLINT.tree, [
//         'string',  'arity', 'name',  'first',
//         'second', 'third', 'block', 'else'
//     ], 4));

// You can request a context coloring table. It contains information that can be
// applied to the file that was analyzed. Context coloring colors functions
// based on their nesting level, and variables on the color of the functions
// in which they are defined.

//      var myColorization = JSLINT.color(data);

// It returns an array containing objects of this form:

//      {
//          from: COLUMN,
//          thru: COLUMN,
//          line: ROW,
//          level: 0 or higher
//      }

// JSLint provides three inline directives. They look like slashstar comments,
// and allow for setting options, declaring global variables, and establishing a
// set of allowed property names.

// These directives respect function scope.

// The jslint directive is a special comment that can set one or more options.
// For example:

/*jslint
    es5: true, evil: true, nomen: true, regexp: true, todo: true
*/

// The current option set is

//     bitwise    true, if bitwise operators should be allowed
//     browser    true, if the standard browser globals should be predefined
//     'continue' true, if the continuation statement should be tolerated
//     debug      true, if debugger statements should be allowed
//     devel      true, if logging should be allowed (console, alert, etc.)
//     eqeq       true, if == should be allowed
//     es5        true, if ES5 syntax should be allowed
//     evil       true, if eval should be allowed
//     forin      true, if for in statements need not filter
//     indent     the indentation factor
//     maxerr     the maximum number of errors to allow
//     maxlen     the maximum length of a source line
//     newcap     true, if constructor names capitalization is ignored
//     node       true, if Node.js globals should be predefined
//     nomen      true, if names may have dangling _
//     passfail   true, if the scan should stop on first error
//     plusplus   true, if increment/decrement should be allowed
//     properties true, if all property names must be declared with /*properties*/
//     regexp     true, if the . should be allowed in regexp literals
//     rhino      true, if the Rhino environment globals should be predefined
//     undef      true, if variables can be declared out of order
//     unparam    true, if unused parameters should be tolerated
//     sloppy     true, if the 'use strict'; pragma is optional
//     stupid     true, if really stupid practices are tolerated
//     sub        true, if all forms of subscript notation are tolerated
//     todo       true, if TODO comments are tolerated
//     vars       true, if multiple var statements per function should be allowed
//     white      true, if sloppy whitespace is tolerated
//     windows    true, if MS Windows-specific globals should be predefined

// The properties directive declares an exclusive list of property names.
// Any properties named in the program that are not in the list will
// produce a warning.

// For example:

/*properties
    '\b', '\t', '\n', '\f', '\r', '!', '!=', '!==', '"', '%', '\'',
    '(arguments)', '(begin)', '(breakage)', '(context)', '(error)',
    '(identifier)', '(level)', '(line)', '(loopage)', '(name)', '(params)',
    '(scope)', '(token)', '(vars)', '(verb)', '*', '+', '-', '/', '<', '<=',
    '==', '===', '>', '>=', '\\', a, a_label, a_scope, already_defined, and,
    apply, arity, assign, assign_exception, assignment_function_expression, at,
    avoid_a, b, bad_assignment, bad_constructor, bad_in_a, bad_invocation,
    bad_new, bad_number, bad_operand, bad_wrap, bitwise, block, browser, c,
    call, charAt, charCodeAt, character, closure, color, combine_var,
    conditional_assignment, confusing_a, confusing_regexp, constructor_name_a,
    continue, control_a, create, d, dangling_a, data, debug, deleted, devel,
    disrupt, duplicate_a, edge, edition, else, empty_block, empty_case,
    empty_class, entityify, eqeq, error_report, errors, es5, evidence, evil,
    exception, exec, expected_a, expected_a_at_b_c, expected_a_b,
    expected_a_b_from_c_d, expected_id_a, expected_identifier_a,
    expected_identifier_a_reserved, expected_number_a, expected_operator_a,
    expected_percent_a, expected_positive_a, expected_small_a,
    expected_space_a_b, expected_string_a, f, filter, first, flag, floor,
    forEach, for_if, forin, from, fromCharCode, fud, function, function_block,
    function_eval, function_loop, function_statement, function_strict,
    functions, global, globals, hasOwnProperty, id, identifier,
    identifier_function, immed, implied_evil, indent, indexOf, infix_in, init,
    insecure_a, isAlpha, isArray, isDigit, isNaN, join, jslint, json, keys,
    label, labeled, lbp, leading_decimal_a, led, left, length, level, line,
    match, maxerr, maxlen, message, missing_a, missing_a_after_b,
    missing_property, missing_space_a_b, missing_use_strict, mode,
    move_invocation, move_var, n, name, name_function, nested_comment, newcap,
    node, nomen, not, not_a_constructor, not_a_defined, not_a_function,
    not_a_label, not_a_scope, not_greater, nud, number, octal_a, open, outer,
    parameter_a_get_b, parameter_arguments_a, parameter_set_a, params, paren,
    passfail, plusplus, postscript, predef, properties, properties_report,
    property, prototype, push, quote, r, radix, range, raw, read_only, reason,
    regexp, replace, report, reserved, reserved_a, rhino, right, scanned_a_b,
    search, second, shift, slash_equal, slice, sloppy, sort, split,
    statement_block, stopping, strange_loop, strict, string, stupid, sub,
    subscript, substr, supplant, sync_a, t, tag_a_in_b, test, third, thru,
    toString, todo, todo_comment, token, tokens, too_long, too_many,
    trailing_decimal_a, tree, unclosed, unclosed_comment, unclosed_regexp,
    undef, undefined, unescaped_a, unexpected_a, unexpected_char_a,
    unexpected_comment, unexpected_else, unexpected_label_a,
    unexpected_property_a, unexpected_space_a_b, unexpected_typeof_a,
    unnecessary_initialize, unnecessary_use, unparam, unreachable_a_b, unsafe,
    unused, url, urls, use_array, use_braces, use_object, use_or, use_param,
    use_spaces, used_before_a, var, var_a_not, vars, was, weird_assignment,
    weird_condition, weird_new, weird_program, weird_relation, weird_ternary,
    white, windows, wrap, wrap_immediate, wrap_regexp, write_is_wrong,
    writeable
*/

// The global directive is used to declare global variables that can
// be accessed by the program. If a declaration is true, then the variable
// is writeable. Otherwise, it is read-only.

// We build the application inside a function so that we produce only a single
// global variable. That function will be invoked immediately, and its return
// value is the JSLINT function itself. That function is also an object that
// can contain data and other functions.

var JSLINT = (function () {
    'use strict';

    function array_to_object(array, value) {

// Make an object from an array of keys and a common value.

        var i, length = array.length, object = {};
        for (i = 0; i < length; i += 1) {
            object[array[i]] = value;
        }
        return object;
    }

    var allowed_option = {
            bitwise   : true,
            browser   : true,
            'continue': true,
            couch     : true,
            debug     : true,
            devel     : true,
            eqeq      : true,
            es5       : true,
            evil      : true,
            forin     : true,
            indent    :   10,
            maxerr    : 1000,
            maxlen    :  256,
            newcap    : true,
            node      : true,
            nomen     : true,
            passfail  : true,
            plusplus  : true,
            properties: true,
            regexp    : true,
            rhino     : true,
            undef     : true,
            unparam   : true,
            sloppy    : true,
            stupid    : true,
            sub       : true,
            todo      : true,
            vars      : true,
            white     : true,
            windows   : true
        },
        anonname,       // The guessed name for anonymous functions.

// These are operators that should not be used with the ! operator.

        bang = {
            '<'  : true,
            '<=' : true,
            '==' : true,
            '===': true,
            '!==': true,
            '!=' : true,
            '>'  : true,
            '>=' : true,
            '+'  : true,
            '-'  : true,
            '*'  : true,
            '/'  : true,
            '%'  : true
        },
        begin,          // The root token

// browser contains a set of global names that are commonly provided by a
// web browser environment.

        browser = array_to_object([
            'clearInterval', 'clearTimeout', 'document', 'event', 'FormData',
            'frames', 'history', 'Image', 'localStorage', 'location', 'name',
            'navigator', 'Option', 'parent', 'screen', 'sessionStorage',
            'setInterval', 'setTimeout', 'Storage', 'window', 'XMLHttpRequest'
        ], false),

// bundle contains the text messages.

        bundle = {
            a_label: "'{a}' is a statement label.",
            a_scope: "'{a}' used out of scope.",
            already_defined: "'{a}' is already defined.",
            and: "The '&&' subexpression should be wrapped in parens.",
            assign_exception: "Do not assign to the exception parameter.",
            assignment_function_expression: "Expected an assignment or " +
                "function call and instead saw an expression.",
            avoid_a: "Avoid '{a}'.",
            bad_assignment: "Bad assignment.",
            bad_constructor: "Bad constructor.",
            bad_in_a: "Bad for in variable '{a}'.",
            bad_invocation: "Bad invocation.",
            bad_new: "Do not use 'new' for side effects.",
            bad_number: "Bad number '{a}'.",
            bad_operand: "Bad operand.",
            bad_wrap: "Do not wrap function literals in parens unless they " +
                "are to be immediately invoked.",
            combine_var: "Combine this with the previous 'var' statement.",
            conditional_assignment: "Expected a conditional expression and " +
                "instead saw an assignment.",
            confusing_a: "Confusing use of '{a}'.",
            confusing_regexp: "Confusing regular expression.",
            constructor_name_a: "A constructor name '{a}' should start with " +
                "an uppercase letter.",
            control_a: "Unexpected control character '{a}'.",
            dangling_a: "Unexpected dangling '_' in '{a}'.",
            deleted: "Only properties should be deleted.",
            duplicate_a: "Duplicate '{a}'.",
            empty_block: "Empty block.",
            empty_case: "Empty case.",
            empty_class: "Empty class.",
            es5: "This is an ES5 feature.",
            evil: "eval is evil.",
            expected_a: "Expected '{a}'.",
            expected_a_b: "Expected '{a}' and instead saw '{b}'.",
            expected_a_b_from_c_d: "Expected '{a}' to match '{b}' from line " +
                "{c} and instead saw '{d}'.",
            expected_a_at_b_c: "Expected '{a}' at column {b}, not column {c}.",
            expected_id_a: "Expected an id, and instead saw #{a}.",
            expected_identifier_a: "Expected an identifier and instead saw '{a}'.",
            expected_identifier_a_reserved: "Expected an identifier and " +
                "instead saw '{a}' (a reserved word).",
            expected_number_a: "Expected a number and instead saw '{a}'.",
            expected_operator_a: "Expected an operator and instead saw '{a}'.",
            expected_positive_a: "Expected a positive number and instead saw '{a}'",
            expected_small_a: "Expected a small positive integer and instead saw '{a}'",
            expected_space_a_b: "Expected exactly one space between '{a}' and '{b}'.",
            expected_string_a: "Expected a string and instead saw '{a}'.",
            for_if: "The body of a for in should be wrapped in an if " +
                "statement to filter unwanted properties from the prototype.",
            function_block: "Function statements should not be placed in blocks." +
                "Use a function expression or move the statement to the top of " +
                "the outer function.",
            function_eval: "The Function constructor is eval.",
            function_loop: "Don't make functions within a loop.",
            function_statement: "Function statements are not invocable." +
                "Wrap the whole function invocation in parens.",
            function_strict: "Use the function form of 'use strict'.",
            identifier_function: "Expected an identifier in an assignment " +
                "and instead saw a function invocation.",
            implied_evil: "Implied eval is evil. Pass a function instead of a string.",
            infix_in: "Unexpected 'in'. Compare with undefined, or use the " +
                "hasOwnProperty method instead.",
            insecure_a: "Insecure '{a}'.",
            isNaN: "Use the isNaN function to compare with NaN.",
            leading_decimal_a: "A leading decimal point can be confused with a dot: '.{a}'.",
            missing_a: "Missing '{a}'.",
            missing_a_after_b: "Missing '{a}' after '{b}'.",
            missing_property: "Missing property name.",
            missing_space_a_b: "Missing space between '{a}' and '{b}'.",
            missing_use_strict: "Missing 'use strict' statement.",
            move_invocation: "Move the invocation into the parens that " +
                "contain the function.",
            move_var: "Move 'var' declarations to the top of the function.",
            name_function: "Missing name in function statement.",
            nested_comment: "Nested comment.",
            not: "Nested not.",
            not_a_constructor: "Do not use {a} as a constructor.",
            not_a_defined: "'{a}' has not been fully defined yet.",
            not_a_function: "'{a}' is not a function.",
            not_a_label: "'{a}' is not a label.",
            not_a_scope: "'{a}' is out of scope.",
            not_greater: "'{a}' should not be greater than '{b}'.",
            octal_a: "Don't use octal: '{a}'. Use '\\u....' instead.",
            parameter_arguments_a: "Do not mutate parameter '{a}' when using 'arguments'.",
            parameter_a_get_b: "Unexpected parameter '{a}' in get {b} function.",
            parameter_set_a: "Expected parameter (value) in set {a} function.",
            radix: "Missing radix parameter.",
            read_only: "Read only.",
            reserved_a: "Reserved name '{a}'.",
            scanned_a_b: "{a} ({b}% scanned).",
            slash_equal: "A regular expression literal can be confused with '/='.",
            statement_block: "Expected to see a statement and instead saw a block.",
            stopping: "Stopping.",
            strange_loop: "Strange loop.",
            strict: "Strict violation.",
            subscript: "['{a}'] is better written in dot notation.",
            sync_a: "Unexpected sync method: '{a}'.",
            tag_a_in_b: "A '<{a}>' must be within '<{b}>'.",
            todo_comment: "Unexpected TODO comment.",
            too_long: "Line too long.",
            too_many: "Too many errors.",
            trailing_decimal_a: "A trailing decimal point can be confused " +
                "with a dot: '.{a}'.",
            unclosed: "Unclosed string.",
            unclosed_comment: "Unclosed comment.",
            unclosed_regexp: "Unclosed regular expression.",
            unescaped_a: "Unescaped '{a}'.",
            unexpected_a: "Unexpected '{a}'.",
            unexpected_char_a: "Unexpected character '{a}'.",
            unexpected_comment: "Unexpected comment.",
            unexpected_else: "Unexpected 'else' after 'return'.",
            unexpected_label_a: "Unexpected label '{a}'.",
            unexpected_property_a: "Unexpected /*property*/ '{a}'.",
            unexpected_space_a_b: "Unexpected space between '{a}' and '{b}'.",
            unexpected_typeof_a: "Unexpected 'typeof'. " +
                "Use '===' to compare directly with {a}.",
            unnecessary_initialize: "It is not necessary to initialize '{a}' " +
                "to 'undefined'.",
            unnecessary_use: "Unnecessary 'use strict'.",
            unreachable_a_b: "Unreachable '{a}' after '{b}'.",
            unsafe: "Unsafe character.",
            url: "JavaScript URL.",
            use_array: "Use the array literal notation [].",
            use_braces: "Spaces are hard to count. Use {{a}}.",
            use_object: "Use the object literal notation {}.",
            use_or: "Use the || operator.",
            use_param: "Use a named parameter.",
            use_spaces: "Use spaces, not tabs.",
            used_before_a: "'{a}' was used before it was defined.",
            var_a_not: "Variable {a} was not declared correctly.",
            weird_assignment: "Weird assignment.",
            weird_condition: "Weird condition.",
            weird_new: "Weird construction. Delete 'new'.",
            weird_program: "Weird program.",
            weird_relation: "Weird relation.",
            weird_ternary: "Weird ternary.",
            wrap_immediate: "Wrap an immediate function invocation in parentheses " +
                "to assist the reader in understanding that the expression " +
                "is the result of a function, and not the function itself.",
            wrap_regexp: "Wrap the /regexp/ literal in parens to " +
                "disambiguate the slash operator.",
            write_is_wrong: "document.write can be a form of eval."
        },
        comments_off,
        couch = array_to_object([
            'emit'
        ], false),

        descapes = {
            'b': '\b',
            't': '\t',
            'n': '\n',
            'f': '\f',
            'r': '\r',
            '"': '"',
            '/': '/',
            '\\': '\\',
            '!': '!'
        },

        devel = array_to_object([
            'alert', 'confirm', 'console', 'Debug', 'opera', 'prompt', 'WSH'
        ], false),
        directive,
        escapes = {
            '\b': '\\b',
            '\t': '\\t',
            '\n': '\\n',
            '\f': '\\f',
            '\r': '\\r',
            '\'': '\\\'',
            '"' : '\\"',
            '/' : '\\/',
            '\\': '\\\\'
        },

        funct,          // The current function, including the labels used in
                        // the function, as well as (breakage),
                        // (context), (loopage), (name), (params), (token),
                        // (vars), (verb)

        functionicity = [
            'closure', 'exception', 'global', 'label', 'outer', 'undef',
            'unused', 'var'
        ],

        functions,      // All of the functions
        global_funct,   // The global body
        global_scope,   // The global scope
        in_block,
        indent,
        itself,         // JSLint itself
        json_mode,
        lex,            // the tokenizer
        lines,
        lookahead,
        node = array_to_object([
            'Buffer', 'clearInterval', 'clearTimeout', 'console', 'exports',
            'global', 'module', 'process', 'querystring', 'require',
            'setInterval', 'setTimeout', '__dirname', '__filename'
        ], false),
        node_js,
        numbery = array_to_object(['indexOf', 'lastIndexOf', 'search'], true),
        next_token,
        option,
        predefined,     // Global variables defined by option
        prereg,
        prev_token,
        property,
        regexp_flag = array_to_object(['g', 'i', 'm'], true),
        return_this = function return_this() {
            return this;
        },
        rhino = array_to_object([
            'defineClass', 'deserialize', 'gc', 'help', 'load', 'loadClass',
            'print', 'quit', 'readFile', 'readUrl', 'runCommand', 'seal',
            'serialize', 'spawn', 'sync', 'toint32', 'version'
        ], false),

        scope,      // An object containing an object for each variable in scope
        semicolon_coda = array_to_object([';', '"', '\'', ')'], true),
        src,
        stack,

// standard contains the global names that are provided by the
// ECMAScript standard.

        standard = array_to_object([
            'Array', 'Boolean', 'Date', 'decodeURI', 'decodeURIComponent',
            'encodeURI', 'encodeURIComponent', 'Error', 'eval', 'EvalError',
            'Function', 'isFinite', 'isNaN', 'JSON', 'Math', 'Number',
            'Object', 'parseInt', 'parseFloat', 'RangeError', 'ReferenceError',
            'RegExp', 'String', 'SyntaxError', 'TypeError', 'URIError'
        ], false),

        strict_mode,
        syntax = {},
        tab,
        token,
        tokens,
        urls,
        var_mode,
        warnings,

        windows = array_to_object([
            'ActiveXObject', 'CScript', 'Debug', 'Enumerator', 'System',
            'VBArray', 'WScript', 'WSH'
        ], false),

// Regular expressions. Some of these are stupidly long.

// carriage return, carriage return linefeed, or linefeed
        crlfx = /\r\n?|\n/,
// unsafe characters that are silently deleted by one or more browsers
        cx = /[\u0000-\u0008\u000a-\u001f\u007f-\u009f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/,
// identifier
        ix = /^([a-zA-Z_$][a-zA-Z0-9_$]*)$/,
// javascript url
        jx = /^(?:javascript|jscript|ecmascript|vbscript|mocha|livescript)\s*:/i,
// star slash
        lx = /\*\/|\/\*/,
// characters in strings that need escapement
        nx = /[\u0000-\u001f'\\\u007f-\u009f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
// sync
        syx = /Sync$/,
// comment todo
        tox = /^\W*to\s*do(?:\W|$)/i,
// token
        tx = /^\s*([(){}\[\]\?.,:;'"~#@`]|={1,3}|\/(\*(jslint|properties|property|members?|globals?)?|=|\/)?|\*[\/=]?|\+(?:=|\++)?|-(?:=|-+)?|[\^%]=?|&[&=]?|\|[|=]?|>{1,3}=?|<(?:[\/=!]|\!(\[|--)?|<=?)?|\!(\!|==?)?|[a-zA-Z_$][a-zA-Z0-9_$]*|[0-9]+(?:[xX][0-9a-fA-F]+|\.[0-9]*)?(?:[eE][+\-]?[0-9]+)?)/;

    function F() {}     // Used by Object.create

// Provide critical ES5 functions to ES3.

    if (typeof Array.prototype.filter !== 'function') {
        Array.prototype.filter = function (f) {
            var i, length = this.length, result = [], value;
            for (i = 0; i < length; i += 1) {
                try {
                    value = this[i];
                    if (f(value)) {
                        result.push(value);
                    }
                } catch (ignore) {
                }
            }
            return result;
        };
    }

    if (typeof Array.prototype.forEach !== 'function') {
        Array.prototype.forEach = function (f) {
            var i, length = this.length;
            for (i = 0; i < length; i += 1) {
                try {
                    f(this[i]);
                } catch (ignore) {
                }
            }
        };
    }

    if (typeof Array.isArray !== 'function') {
        Array.isArray = function (o) {
            return Object.prototype.toString.apply(o) === '[object Array]';
        };
    }

    if (!Object.prototype.hasOwnProperty.call(Object, 'create')) {
        Object.create = function (o) {
            F.prototype = o;
            return new F();
        };
    }

    if (typeof Object.keys !== 'function') {
        Object.keys = function (o) {
            var array = [], key;
            for (key in o) {
                if (Object.prototype.hasOwnProperty.call(o, key)) {
                    array.push(key);
                }
            }
            return array;
        };
    }

    if (typeof String.prototype.entityify !== 'function') {
        String.prototype.entityify = function () {
            return this
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        };
    }

    if (typeof String.prototype.isAlpha !== 'function') {
        String.prototype.isAlpha = function () {
            return (this >= 'a' && this <= 'z\uffff') ||
                (this >= 'A' && this <= 'Z\uffff');
        };
    }

    if (typeof String.prototype.isDigit !== 'function') {
        String.prototype.isDigit = function () {
            return (this >= '0' && this <= '9');
        };
    }

    if (typeof String.prototype.supplant !== 'function') {
        String.prototype.supplant = function (o) {
            return this.replace(/\{([^{}]*)\}/g, function (a, b) {
                var replacement = o[b];
                return typeof replacement === 'string' ||
                    typeof replacement === 'number' ? replacement : a;
            });
        };
    }

    function sanitize(a) {

//  Escapify a troublesome character.

        return escapes[a] ||
            '\\u' + ('0000' + a.charCodeAt().toString(16)).slice(-4);
    }

    function add_to_predefined(group) {
        Object.keys(group).forEach(function (name) {
            predefined[name] = group[name];
        });
    }

    function assume() {
        if (option.rhino) {
            add_to_predefined(rhino);
            option.rhino = false;
        }
        if (option.devel) {
            add_to_predefined(devel);
            option.devel = false;
        }
        if (option.browser) {
            add_to_predefined(browser);
            option.browser = false;
        }
        if (option.windows) {
            add_to_predefined(windows);
            option.windows = false;
        }
        if (option.node) {
            add_to_predefined(node);
            option.node = false;
            node_js = true;
        }
        if (option.couch) {
            add_to_predefined(couch);
            option.couch = false;
        }
    }

// Produce an error warning.

    function artifact(tok) {
        if (!tok) {
            tok = next_token;
        }
        return tok.number || tok.string;
    }

    function quit(message, line, character) {
        throw {
            name: 'JSLintError',
            line: line,
            character: character,
            message: bundle.scanned_a_b.supplant({
                a: message,
                b: Math.floor((line / lines.length) * 100)
            })
        };
    }

    function warn(message, offender, a, b, c, d) {
        var character, line, warning;
        offender = offender || next_token;  // ~~
        line = offender.line || 0;
        character = offender.from || 0;
        warning = {
            id: '(error)',
            raw: bundle[message] || message,
            evidence: lines[line - 1] || '',
            line: line,
            character: character,
            a: a || (offender.id === '(number)'
                ? String(offender.number)
                : offender.string),
            b: b,
            c: c,
            d: d
        };
        warning.reason = warning.raw.supplant(warning);
        JSLINT.errors.push(warning);
        if (option.passfail) {
            quit(bundle.stopping, line, character);
        }
        warnings += 1;
        if (warnings >= option.maxerr) {
            quit(bundle.too_many, line, character);
        }
        return warning;
    }

    function warn_at(message, line, character, a, b, c, d) {
        return warn(message, {
            line: line,
            from: character
        }, a, b, c, d);
    }

    function stop(message, offender, a, b, c, d) {
        var warning = warn(message, offender, a, b, c, d);
        quit(bundle.stopping, warning.line, warning.character);
    }

    function stop_at(message, line, character, a, b, c, d) {
        return stop(message, {
            line: line,
            from: character
        }, a, b, c, d);
    }

    function expected_at(at) {
        if (!option.white && next_token.from !== at) {
            warn('expected_a_at_b_c', next_token, '', at,
                next_token.from);
        }
    }

// lexical analysis and token construction

    lex = (function lex() {
        var character, c, from, length, line, pos, source_row;

// Private lex methods

        function next_line() {
            var at;
            character = 1;
            source_row = lines[line];
            line += 1;
            if (source_row === undefined) {
                return false;
            }
            at = source_row.search(/\t/);
            if (at >= 0) {
                if (option.white) {
                    source_row = source_row.replace(/\t/g, ' ');
                } else {
                    warn_at('use_spaces', line, at + 1);
                }
            }
            at = source_row.search(cx);
            if (at >= 0) {
                warn_at('unsafe', line, at);
            }
            if (option.maxlen && option.maxlen < source_row.length) {
                warn_at('too_long', line, source_row.length);
            }
            return true;
        }

// Produce a token object.  The token inherits from a syntax symbol.

        function it(type, value) {
            var id, the_token;
            if (type === '(string)' || type === '(range)') {
                if (jx.test(value)) {
                    warn_at('url', line, from);
                }
            }
            the_token = Object.create(syntax[(
                type === '(punctuator)' || (type === '(identifier)' &&
                        Object.prototype.hasOwnProperty.call(syntax, value))
                    ? value
                    : type
            )] || syntax['(error)']);
            if (type === '(identifier)') {
                the_token.identifier = true;
                if (value === '__iterator__' || value === '__proto__') {
                    stop_at('reserved_a', line, from, value);
                } else if (!option.nomen &&
                        (value.charAt(0) === '_' ||
                        value.charAt(value.length - 1) === '_')) {
                    warn_at('dangling_a', line, from, value);
                }
            }
            if (type === '(number)') {
                the_token.number = +value;
            } else if (value !== undefined) {
                the_token.string = String(value);
            }
            the_token.line = line;
            the_token.from = from;
            the_token.thru = character;
            id = the_token.id;
            prereg = id && (
                ('(,=:[!&|?{};~+-*%^<>'.indexOf(id.charAt(id.length - 1)) >= 0) ||
                id === 'return' || id === 'case'
            );
            return the_token;
        }

        function match(x) {
            var exec = x.exec(source_row), first;
            if (exec) {
                length = exec[0].length;
                first = exec[1];
                c = first.charAt(0);
                source_row = source_row.slice(length);
                from = character + length - first.length;
                character += length;
                return first;
            }
            for (;;) {
                if (!source_row) {
                    if (!option.white) {
                        warn_at('unexpected_char_a', line, character - 1, '(space)');
                    }
                    return;
                }
                c = source_row.charAt(0);
                if (c !== ' ') {
                    break;
                }
                source_row = source_row.slice(1);
                character += 1;
            }
            stop_at('unexpected_char_a', line, character, c);

        }

        function string(x) {
            var c, pos = 0, r = '', result;

            function hex(n) {
                var i = parseInt(source_row.substr(pos + 1, n), 16);
                pos += n;
                if (i >= 32 && i <= 126 &&
                        i !== 34 && i !== 92 && i !== 39) {
                    warn_at('unexpected_a', line, character, '\\');
                }
                character += n;
                c = String.fromCharCode(i);
            }

            if (json_mode && x !== '"') {
                warn_at('expected_a', line, character, '"');
            }

            for (;;) {
                while (pos >= source_row.length) {
                    pos = 0;
                    if (!next_line()) {
                        stop_at('unclosed', line, from);
                    }
                }
                c = source_row.charAt(pos);
                if (c === x) {
                    character += 1;
                    source_row = source_row.slice(pos + 1);
                    result = it('(string)', r);
                    result.quote = x;
                    return result;
                }
                if (c < ' ') {
                    if (c === '\n' || c === '\r') {
                        break;
                    }
                    warn_at('control_a', line, character + pos,
                        source_row.slice(0, pos));
                } else if (c === '\\') {
                    pos += 1;
                    character += 1;
                    c = source_row.charAt(pos);
                    switch (c) {
                    case '':
                        if (!option.es5) {
                            warn_at('es5', line, character);
                        }
                        next_line();
                        pos = -1;
                        break;
                    case '\'':
                        if (json_mode) {
                            warn_at('unexpected_a', line, character, '\\\'');
                        }
                        break;
                    case 'u':
                        hex(4);
                        break;
                    case 'v':
                        if (json_mode) {
                            warn_at('unexpected_a', line, character, '\\v');
                        }
                        c = '\v';
                        break;
                    case 'x':
                        if (json_mode) {
                            warn_at('unexpected_a', line, character, '\\x');
                        }
                        hex(2);
                        break;
                    default:
                        if (typeof descapes[c] !== 'string') {
                            warn_at(c >= '0' && c <= '7' ? 'octal_a' : 'unexpected_a',
                                line, character, '\\' + c);
                        } else {
                            c = descapes[c];
                        }
                    }
                }
                r += c;
                character += 1;
                pos += 1;
            }
        }

        function number(snippet) {
            var digit;
            if (source_row.charAt(0).isAlpha()) {
                warn_at('expected_space_a_b',
                    line, character, c, source_row.charAt(0));
            }
            if (c === '0') {
                digit = snippet.charAt(1);
                if (digit.isDigit()) {
                    if (token.id !== '.') {
                        warn_at('unexpected_a', line, character, snippet);
                    }
                } else if (json_mode && (digit === 'x' || digit === 'X')) {
                    warn_at('unexpected_a', line, character, '0x');
                }
            }
            if (snippet.slice(snippet.length - 1) === '.') {
                warn_at('trailing_decimal_a', line, character, snippet);
            }
            digit = +snippet;
            if (!isFinite(digit)) {
                warn_at('bad_number', line, character, snippet);
            }
            snippet = digit;
            return it('(number)', snippet);
        }

        function comment(snippet) {
            if (comments_off || src) {
                warn_at('unexpected_comment', line, character);
            } else if (!option.todo && tox.test(snippet)) {
                warn_at('todo_comment', line, character);
            }
        }

        function regexp() {
            var b,
                bit,
                captures = 0,
                depth = 0,
                flag = '',
                high,
                letter,
                length = 0,
                low,
                potential,
                quote,
                result;
            for (;;) {
                b = true;
                c = source_row.charAt(length);
                length += 1;
                switch (c) {
                case '':
                    stop_at('unclosed_regexp', line, from);
                    return;
                case '/':
                    if (depth > 0) {
                        warn_at('unescaped_a', line, from + length, '/');
                    }
                    c = source_row.slice(0, length - 1);
                    potential = Object.create(regexp_flag);
                    for (;;) {
                        letter = source_row.charAt(length);
                        if (potential[letter] !== true) {
                            break;
                        }
                        potential[letter] = false;
                        length += 1;
                        flag += letter;
                    }
                    if (source_row.charAt(length).isAlpha()) {
                        stop_at('unexpected_a', line, from, source_row.charAt(length));
                    }
                    character += length;
                    source_row = source_row.slice(length);
                    quote = source_row.charAt(0);
                    if (quote === '/' || quote === '*') {
                        stop_at('confusing_regexp', line, from);
                    }
                    result = it('(regexp)', c);
                    result.flag = flag;
                    return result;
                case '\\':
                    c = source_row.charAt(length);
                    if (c < ' ') {
                        warn_at('control_a', line, from + length, String(c));
                    } else if (c === '<') {
                        warn_at(bundle.unexpected_a, line, from + length, '\\');
                    }
                    length += 1;
                    break;
                case '(':
                    depth += 1;
                    b = false;
                    if (source_row.charAt(length) === '?') {
                        length += 1;
                        switch (source_row.charAt(length)) {
                        case ':':
                        case '=':
                        case '!':
                            length += 1;
                            break;
                        default:
                            warn_at(bundle.expected_a_b, line, from + length,
                                ':', source_row.charAt(length));
                        }
                    } else {
                        captures += 1;
                    }
                    break;
                case '|':
                    b = false;
                    break;
                case ')':
                    if (depth === 0) {
                        warn_at('unescaped_a', line, from + length, ')');
                    } else {
                        depth -= 1;
                    }
                    break;
                case ' ':
                    pos = 1;
                    while (source_row.charAt(length) === ' ') {
                        length += 1;
                        pos += 1;
                    }
                    if (pos > 1) {
                        warn_at('use_braces', line, from + length, pos);
                    }
                    break;
                case '[':
                    c = source_row.charAt(length);
                    if (c === '^') {
                        length += 1;
                        if (!option.regexp) {
                            warn_at('insecure_a', line, from + length, c);
                        } else if (source_row.charAt(length) === ']') {
                            stop_at('unescaped_a', line, from + length, '^');
                        }
                    }
                    bit = false;
                    if (c === ']') {
                        warn_at('empty_class', line, from + length - 1);
                        bit = true;
                    }
klass:              do {
                        c = source_row.charAt(length);
                        length += 1;
                        switch (c) {
                        case '[':
                        case '^':
                            warn_at('unescaped_a', line, from + length, c);
                            bit = true;
                            break;
                        case '-':
                            if (bit) {
                                bit = false;
                            } else {
                                warn_at('unescaped_a', line, from + length, '-');
                                bit = true;
                            }
                            break;
                        case ']':
                            if (!bit) {
                                warn_at('unescaped_a', line, from + length - 1, '-');
                            }
                            break klass;
                        case '\\':
                            c = source_row.charAt(length);
                            if (c < ' ') {
                                warn_at(bundle.control_a, line, from + length, String(c));
                            } else if (c === '<') {
                                warn_at(bundle.unexpected_a, line, from + length, '\\');
                            }
                            length += 1;
                            bit = true;
                            break;
                        case '/':
                            warn_at('unescaped_a', line, from + length - 1, '/');
                            bit = true;
                            break;
                        default:
                            bit = true;
                        }
                    } while (c);
                    break;
                case '.':
                    if (!option.regexp) {
                        warn_at('insecure_a', line, from + length, c);
                    }
                    break;
                case ']':
                case '?':
                case '{':
                case '}':
                case '+':
                case '*':
                    warn_at('unescaped_a', line, from + length, c);
                    break;
                }
                if (b) {
                    switch (source_row.charAt(length)) {
                    case '?':
                    case '+':
                    case '*':
                        length += 1;
                        if (source_row.charAt(length) === '?') {
                            length += 1;
                        }
                        break;
                    case '{':
                        length += 1;
                        c = source_row.charAt(length);
                        if (c < '0' || c > '9') {
                            warn_at(bundle.expected_number_a, line,
                                from + length, c);
                        }
                        length += 1;
                        low = +c;
                        for (;;) {
                            c = source_row.charAt(length);
                            if (c < '0' || c > '9') {
                                break;
                            }
                            length += 1;
                            low = +c + (low * 10);
                        }
                        high = low;
                        if (c === ',') {
                            length += 1;
                            high = Infinity;
                            c = source_row.charAt(length);
                            if (c >= '0' && c <= '9') {
                                length += 1;
                                high = +c;
                                for (;;) {
                                    c = source_row.charAt(length);
                                    if (c < '0' || c > '9') {
                                        break;
                                    }
                                    length += 1;
                                    high = +c + (high * 10);
                                }
                            }
                        }
                        if (source_row.charAt(length) !== '}') {
                            warn_at(bundle.expected_a_b, line, from + length,
                                '}', c);
                        } else {
                            length += 1;
                        }
                        if (source_row.charAt(length) === '?') {
                            length += 1;
                        }
                        if (low > high) {
                            warn_at(bundle.not_greater, line, from + length,
                                low, high);
                        }
                        break;
                    }
                }
            }
            c = source_row.slice(0, length - 1);
            character += length;
            source_row = source_row.slice(length);
            return it('(regexp)', c);
        }

// Public lex methods

        return {
            init: function (source) {
                if (typeof source === 'string') {
                    lines = source.split(crlfx);
                } else {
                    lines = source;
                }
                line = 0;
                next_line();
                from = 1;
            },

            range: function (begin, end) {
                var c, value = '';
                from = character;
                if (source_row.charAt(0) !== begin) {
                    stop_at('expected_a_b', line, character, begin,
                        source_row.charAt(0));
                }
                for (;;) {
                    source_row = source_row.slice(1);
                    character += 1;
                    c = source_row.charAt(0);
                    switch (c) {
                    case '':
                        stop_at('missing_a', line, character, c);
                        break;
                    case end:
                        source_row = source_row.slice(1);
                        character += 1;
                        return it('(range)', value);
                    case '\\':
                        warn_at('unexpected_a', line, character, c);
                        break;
                    }
                    value += c;
                }
            },

// token -- this is called by advance to get the next token.

            token: function () {
                var c, i, snippet;

                for (;;) {
                    while (!source_row) {
                        if (!next_line()) {
                            return it('(end)');
                        }
                    }
                    snippet = match(tx);
                    if (snippet) {

//      identifier

                        c = snippet.charAt(0);
                        if (c.isAlpha() || c === '_' || c === '$') {
                            return it('(identifier)', snippet);
                        }

//      number

                        if (c.isDigit()) {
                            return number(snippet);
                        }
                        switch (snippet) {

//      string

                        case '"':
                        case "'":
                            return string(snippet);

//      // comment

                        case '//':
                            comment(source_row);
                            source_row = '';
                            break;

//      /* comment

                        case '/*':
                            for (;;) {
                                i = source_row.search(lx);
                                if (i >= 0) {
                                    break;
                                }
                                comment(source_row);
                                if (!next_line()) {
                                    stop_at('unclosed_comment', line, character);
                                }
                            }
                            comment(source_row.slice(0, i));
                            character += i + 2;
                            if (source_row.charAt(i) === '/') {
                                stop_at('nested_comment', line, character);
                            }
                            source_row = source_row.slice(i + 2);
                            break;

                        case '':
                            break;
//      /
                        case '/':
                            if (token.id === '/=') {
                                stop_at(
                                    bundle.slash_equal,
                                    line,
                                    from
                                );
                            }
                            return prereg
                                ? regexp()
                                : it('(punctuator)', snippet);

//      punctuator

                        case '<!--':
                            length = line;
//                            c = character;
                            for (;;) {
                                i = source_row.indexOf('--');
                                if (i >= 0) {
                                    break;
                                }
                                i = source_row.indexOf('<!');
                                if (i >= 0) {
                                    stop_at('nested_comment',
                                        line, character + i);
                                }
                                if (!next_line()) {
                                    stop_at('unclosed_comment', length, c);
                                }
                            }
                            length = source_row.indexOf('<!');
                            if (length >= 0 && length < i) {
                                stop_at('nested_comment',
                                    line, character + length);
                            }
                            character += i;
                            if (source_row.charAt(i + 2) !== '>') {
                                stop_at('expected_a', line, character, '-->');
                            }
                            character += 3;
                            source_row = source_row.slice(i + 3);
                            break;
                        default:
                            return it('(punctuator)', snippet);
                        }
                    }
                }
            }
        };
    }());

    function add_label(token, kind, name) {

// Define the symbol in the current function in the current scope.

        name = name || token.string;
        if (funct === global_funct) {
            if (typeof global_funct[name] !== 'string') {
                token.writeable = typeof predefined[name] === 'boolean'
                    ? predefined[name]
                    : true;
                global_scope[name] = token;
            }
            if (kind === 'becoming') {
                kind = 'var';
            }

// Ordinary variables.

        } else {

// Warn if the variable already exists.

            if (typeof funct[name] === 'string') {
                if (funct[name] === 'undef') {
                    if (!option.undef) {
                        warn('used_before_a', token, name);
                    }
                    kind = 'var';
                } else {
                    warn('already_defined', token, name);
                }
            } else {

// Add the symbol to the current function.

                token.writeable = true;
                scope[name] = token;
            }
        }
        token.function = funct;
        funct[name] = kind;
    }

    function peek(distance) {

// Peek ahead to a future token. The distance is how far ahead to look. The
// default is the next token.

        var found, slot = 0;

        distance = distance || 0;
        while (slot <= distance) {
            found = lookahead[slot];
            if (!found) {
                found = lookahead[slot] = lex.token();
            }
            slot += 1;
        }
        return found;
    }

    function advance(id, match) {

// Produce the next token, also looking for programming errors.

        if (indent) {

// If indentation checking was requested, then inspect all of the line breakings.
// The var statement is tricky because the names might be aligned or not. We
// look at the first line break after the var to determine the programmer's
// intention.

            if (var_mode && next_token.line !== token.line) {
                if ((var_mode !== indent || !next_token.edge) &&
                        next_token.from === indent.at -
                        (next_token.edge ? option.indent : 0)) {
                    var dent = indent;
                    for (;;) {
                        dent.at -= option.indent;
                        if (dent === var_mode) {
                            break;
                        }
                        dent = dent.was;
                    }
                    dent.open = false;
                }
                var_mode = null;
            }
            if (next_token.id === '?' && indent.mode === ':' &&
                    token.line !== next_token.line) {
                indent.at -= option.indent;
            }
            if (indent.open) {

// If the token is an edge.

                if (next_token.edge) {
                    if (next_token.edge === 'label') {
                        expected_at(1);
                    } else if (next_token.edge === 'case' || indent.mode === 'statement') {
                        expected_at(indent.at - option.indent);
                    } else if (indent.mode !== 'array' || next_token.line !== token.line) {
                        expected_at(indent.at);
                    }

// If the token is not an edge, but is the first token on the line.

                } else if (next_token.line !== token.line) {
                    if (next_token.from < indent.at + (indent.mode ===
                            'expression' ? 0 : option.indent)) {
                        expected_at(indent.at + option.indent);
                    }
                    indent.wrap = true;
                }
            } else if (next_token.line !== token.line) {
                if (next_token.edge) {
                    expected_at(indent.at);
                } else {
                    indent.wrap = true;
                    if (indent.mode === 'statement' || indent.mode === 'var') {
                        expected_at(indent.at + option.indent);
                    } else if (next_token.from < indent.at + (indent.mode ===
                            'expression' ? 0 : option.indent)) {
                        expected_at(indent.at + option.indent);
                    }
                }
            }
        }

        switch (token.id) {
        case '(number)':
            if (next_token.id === '.') {
                warn('trailing_decimal_a');
            }
            break;
        case '-':
            if (next_token.id === '-' || next_token.id === '--') {
                warn('confusing_a');
            }
            break;
        case '+':
            if (next_token.id === '+' || next_token.id === '++') {
                warn('confusing_a');
            }
            break;
        }
        if (token.id === '(string)' || token.identifier) {
            anonname = token.string;
        }

        if (id && next_token.id !== id) {
            if (match) {
                warn('expected_a_b_from_c_d', next_token, id,
                    match.id, match.line, artifact());
            } else if (!next_token.identifier || next_token.string !== id) {
                warn('expected_a_b', next_token, id, artifact());
            }
        }
        prev_token = token;
        token = next_token;
        next_token = lookahead.shift() || lex.token();
        next_token.function = funct;
        tokens.push(next_token);
    }

    function do_globals() {
        var name, writeable;
        for (;;) {
            if (next_token.id !== '(string)' && !next_token.identifier) {
                return;
            }
            name = next_token.string;
            advance();
            writeable = false;
            if (next_token.id === ':') {
                advance(':');
                switch (next_token.id) {
                case 'true':
                    writeable = predefined[name] !== false;
                    advance('true');
                    break;
                case 'false':
                    advance('false');
                    break;
                default:
                    stop('unexpected_a');
                }
            }
            predefined[name] = writeable;
            if (next_token.id !== ',') {
                return;
            }
            advance(',');
        }
    }

    function do_jslint() {
        var name, value;
        while (next_token.id === '(string)' || next_token.identifier) {
            name = next_token.string;
            if (!allowed_option[name]) {
                stop('unexpected_a');
            }
            advance();
            if (next_token.id !== ':') {
                stop('expected_a_b', next_token, ':', artifact());
            }
            advance(':');
            if (typeof allowed_option[name] === 'number') {
                value = next_token.number;
                if (value > allowed_option[name] || value <= 0 ||
                        Math.floor(value) !== value) {
                    stop('expected_small_a');
                }
                option[name] = value;
            } else {
                if (next_token.id === 'true') {
                    option[name] = true;
                } else if (next_token.id === 'false') {
                    option[name] = false;
                } else {
                    stop('unexpected_a');
                }
            }
            advance();
            if (next_token.id === ',') {
                advance(',');
            }
        }
        assume();
    }

    function do_properties() {
        var name;
        option.properties = true;
        for (;;) {
            if (next_token.id !== '(string)' && !next_token.identifier) {
                return;
            }
            name = next_token.string;
            advance();
            if (next_token.id === ':') {
                for (;;) {
                    advance();
                    if (next_token.id !== '(string)' && !next_token.identifier) {
                        break;
                    }
                }
            }
            property[name] = 0;
            if (next_token.id !== ',') {
                return;
            }
            advance(',');
        }
    }

    directive = function directive() {
        var command = this.id,
            old_comments_off = comments_off,
            old_indent = indent;
        comments_off = true;
        indent = null;
        if (next_token.line === token.line && next_token.from === token.thru) {
            warn('missing_space_a_b', next_token, artifact(token), artifact());
        }
        if (lookahead.length > 0) {
            warn('unexpected_a', this);
        }
        switch (command) {
        case '/*properties':
        case '/*property':
        case '/*members':
        case '/*member':
            do_properties();
            break;
        case '/*jslint':
            do_jslint();
            break;
        case '/*globals':
        case '/*global':
            do_globals();
            break;
        default:
            stop('unexpected_a', this);
        }
        comments_off = old_comments_off;
        advance('*/');
        indent = old_indent;
    };

// Indentation intention

    function edge(mode) {
        next_token.edge = indent ? indent.open && (mode || 'edge') : '';
    }

    function step_in(mode) {
        var open;
        if (typeof mode === 'number') {
            indent = {
                at: +mode,
                open: true,
                was: indent
            };
        } else if (!indent) {
            indent = {
                at: 1,
                mode: 'statement',
                open: true
            };
        } else if (mode === 'statement') {
            indent = {
                at: indent.at,
                open: true,
                was: indent
            };
        } else {
            open = mode === 'var' || next_token.line !== token.line;
            indent = {
                at: (open || mode === 'control'
                    ? indent.at + option.indent
                    : indent.at) + (indent.wrap ? option.indent : 0),
                mode: mode,
                open: open,
                was: indent
            };
            if (mode === 'var' && open) {
                var_mode = indent;
            }
        }
    }

    function step_out(id, symbol) {
        if (id) {
            if (indent && indent.open) {
                indent.at -= option.indent;
                edge();
            }
            advance(id, symbol);
        }
        if (indent) {
            indent = indent.was;
        }
    }

// Functions for conformance of whitespace.

    function one_space(left, right) {
        left = left || token;
        right = right || next_token;
        if (right.id !== '(end)' && !option.white &&
                (token.line !== right.line ||
                token.thru + 1 !== right.from)) {
            warn('expected_space_a_b', right, artifact(token), artifact(right));
        }
    }

    function one_space_only(left, right) {
        left = left || token;
        right = right || next_token;
        if (right.id !== '(end)' && (left.line !== right.line ||
                (!option.white && left.thru + 1 !== right.from))) {
            warn('expected_space_a_b', right, artifact(left), artifact(right));
        }
    }

    function no_space(left, right) {
        left = left || token;
        right = right || next_token;
        if ((!option.white) &&
                left.thru !== right.from && left.line === right.line) {
            warn('unexpected_space_a_b', right, artifact(left), artifact(right));
        }
    }

    function no_space_only(left, right) {
        left = left || token;
        right = right || next_token;
        if (right.id !== '(end)' && (left.line !== right.line ||
                (!option.white && left.thru !== right.from))) {
            warn('unexpected_space_a_b', right, artifact(left), artifact(right));
        }
    }

    function spaces(left, right) {
        if (!option.white) {
            left = left || token;
            right = right || next_token;
            if (left.thru === right.from && left.line === right.line) {
                warn('missing_space_a_b', right, artifact(left), artifact(right));
            }
        }
    }

    function comma() {
        if (next_token.id !== ',') {
            warn_at('expected_a_b', token.line, token.thru, ',', artifact());
        } else {
            if (!option.white) {
                no_space_only();
            }
            advance(',');
            spaces();
        }
    }

    function semicolon() {
        if (next_token.id !== ';') {
            warn_at('expected_a_b', token.line, token.thru, ';', artifact());
        } else {
            if (!option.white) {
                no_space_only();
            }
            advance(';');
            if (semicolon_coda[next_token.id] !== true) {
                spaces();
            }
        }
    }

    function use_strict() {
        if (next_token.string === 'use strict') {
            if (strict_mode) {
                warn('unnecessary_use');
            }
            edge();
            advance();
            semicolon();
            strict_mode = true;
            option.undef = false;
            return true;
        }
        return false;
    }

    function are_similar(a, b) {
        if (a === b) {
            return true;
        }
        if (Array.isArray(a)) {
            if (Array.isArray(b) && a.length === b.length) {
                var i;
                for (i = 0; i < a.length; i += 1) {
                    if (!are_similar(a[i], b[i])) {
                        return false;
                    }
                }
                return true;
            }
            return false;
        }
        if (Array.isArray(b)) {
            return false;
        }
        if (a.id === '(number)' && b.id === '(number)') {
            return a.number === b.number;
        }
        if (a.arity === b.arity && a.string === b.string) {
            switch (a.arity) {
            case 'prefix':
            case 'suffix':
            case undefined:
                return a.id === b.id && are_similar(a.first, b.first) &&
                    a.id !== '{' && a.id !== '[';
            case 'infix':
                return are_similar(a.first, b.first) &&
                    are_similar(a.second, b.second);
            case 'ternary':
                return are_similar(a.first, b.first) &&
                    are_similar(a.second, b.second) &&
                    are_similar(a.third, b.third);
            case 'function':
            case 'regexp':
                return false;
            default:
                return true;
            }
        } else {
            if (a.id === '.' && b.id === '[' && b.arity === 'infix') {
                return a.second.string === b.second.string && b.second.id === '(string)';
            }
            if (a.id === '[' && a.arity === 'infix' && b.id === '.') {
                return a.second.string === b.second.string && a.second.id === '(string)';
            }
        }
        return false;
    }

// This is the heart of JSLINT, the Pratt parser. In addition to parsing, it
// is looking for ad hoc lint patterns. We add .fud to Pratt's model, which is
// like .nud except that it is only used on the first token of a statement.
// Having .fud makes it much easier to define statement-oriented languages like
// JavaScript. I retained Pratt's nomenclature.

// .nud     Null denotation
// .fud     First null denotation
// .led     Left denotation
//  lbp     Left binding power
//  rbp     Right binding power

// They are elements of the parsing method called Top Down Operator Precedence.

    function expression(rbp, initial) {

// rbp is the right binding power.
// initial indicates that this is the first expression of a statement.

        var left;
        if (next_token.id === '(end)') {
            stop('unexpected_a', token, next_token.id);
        }
        advance();
        if (initial) {
            anonname = 'anonymous';
            funct['(verb)'] = token.string;
        }
        if (initial === true && token.fud) {
            left = token.fud();
        } else {
            if (token.nud) {
                left = token.nud();
            } else {
                if (next_token.id === '(number)' && token.id === '.') {
                    warn('leading_decimal_a', token, artifact());
                    advance();
                    return token;
                }
                stop('expected_identifier_a', token, token.id);
            }
            while (rbp < next_token.lbp) {
                advance();
                if (token.led) {
                    left = token.led(left);
                } else {
                    stop('expected_operator_a', token, token.id);
                }
            }
        }
        return left;
    }

// Functional constructors for making the symbols that will be inherited by
// tokens.

    function symbol(s, p) {
        var x = syntax[s];
        if (!x || typeof x !== 'object') {
            syntax[s] = x = {
                id: s,
                lbp: p || 0,
                string: s
            };
        }
        return x;
    }

    function postscript(x) {
        x.postscript = true;
        return x;
    }

    function ultimate(s) {
        var x = symbol(s, 0);
        x.from = 1;
        x.thru = 1;
        x.line = 0;
        x.edge = 'edge';
        x.string = s;
        return postscript(x);
    }

    function stmt(s, f) {
        var x = symbol(s);
        x.identifier = x.reserved = true;
        x.fud = f;
        return x;
    }

    function labeled_stmt(s, f) {
        var x = stmt(s, f);
        x.labeled = true;
    }

    function disrupt_stmt(s, f) {
        var x = stmt(s, f);
        x.disrupt = true;
    }

    function reserve_name(x) {
        var c = x.id.charAt(0);
        if ((c >= 'a' && c <= 'z') || (c >= 'A' && c <= 'Z')) {
            x.identifier = x.reserved = true;
        }
        return x;
    }

    function prefix(s, f) {
        var x = symbol(s, 150);
        reserve_name(x);
        x.nud = function () {
            var that = this;
            that.arity = 'prefix';
            if (typeof f === 'function') {
                that = f(that);
                if (that.arity !== 'prefix') {
                    return that;
                }
            } else {
                if (s === 'typeof') {
                    one_space();
                } else {
                    no_space_only();
                }
                that.first = expression(150);
            }
            switch (that.id) {
            case '++':
            case '--':
                if (!option.plusplus) {
                    warn('unexpected_a', that);
                } else if ((!that.first.identifier || that.first.reserved) &&
                        that.first.id !== '.' && that.first.id !== '[') {
                    warn('bad_operand', that);
                }
                break;
            default:
                if (that.first.arity === 'prefix' ||
                        that.first.arity === 'function') {
                    warn('unexpected_a', that);
                }
            }
            return that;
        };
        return x;
    }

    function type(s, t, nud) {
        var x = symbol(s);
        x.arity = t;
        if (nud) {
            x.nud = nud;
        }
        return x;
    }

    function reserve(s, f) {
        var x = symbol(s);
        x.identifier = x.reserved = true;
        if (typeof f === 'function') {
            x.nud = f;
        }
        return x;
    }

    function constant(name) {
        var x = reserve(name);
        x.string = name;
        x.nud = return_this;
        return x;
    }

    function reservevar(s, v) {
        return reserve(s, function () {
            if (typeof v === 'function') {
                v(this);
            }
            return this;
        });
    }

    function infix(s, p, f, w) {
        var x = symbol(s, p);
        reserve_name(x);
        x.led = function (left) {
            this.arity = 'infix';
            if (!w) {
                spaces(prev_token, token);
                spaces();
            }
            if (!option.bitwise && this.bitwise) {
                warn('unexpected_a', this);
            }
            if (typeof f === 'function') {
                return f(left, this);
            }
            this.first = left;
            this.second = expression(p);
            return this;
        };
        return x;
    }

    function expected_relation(node, message) {
        if (node.assign) {
            warn(message || bundle.conditional_assignment, node);
        }
        return node;
    }

    function expected_condition(node, message) {
        switch (node.id) {
        case '[':
        case '-':
            if (node.arity !== 'infix') {
                warn(message || bundle.weird_condition, node);
            }
            break;
        case 'false':
        case 'function':
        case 'Infinity':
        case 'NaN':
        case 'null':
        case 'true':
        case 'undefined':
        case 'void':
        case '(number)':
        case '(regexp)':
        case '(string)':
        case '{':
        case '?':
        case '~':
            warn(message || bundle.weird_condition, node);
            break;
        case '(':
            if (node.first.id === 'new' ||
                    (node.first.string === 'Boolean') ||
                    (node.first.id === '.' &&
                        numbery[node.first.second.string] === true)) {
                warn(message || bundle.weird_condition, node);
            }
            break;
        }
        return node;
    }

    function check_relation(node) {
        switch (node.arity) {
        case 'prefix':
            switch (node.id) {
            case '{':
            case '[':
                warn('unexpected_a', node);
                break;
            case '!':
                warn('confusing_a', node);
                break;
            }
            break;
        case 'function':
        case 'regexp':
            warn('unexpected_a', node);
            break;
        default:
            if (node.id  === 'NaN') {
                warn('isNaN', node);
            }
        }
        return node;
    }

    function relation(s, eqeq) {
        return infix(s, 100, function (left, that) {
            check_relation(left);
            if (eqeq && !option.eqeq) {
                warn('expected_a_b', that, eqeq, that.id);
            }
            var right = expression(100);
            if (are_similar(left, right) ||
                    ((left.id === '(string)' || left.id === '(number)') &&
                    (right.id === '(string)' || right.id === '(number)'))) {
                warn('weird_relation', that);
            } else if (left.id === 'typeof') {
                if (right.id !== '(string)') {
                    warn("expected_string_a", right, right.id === '(number)'
                        ? right.number
                        : right.string);
                } else if (right.string === 'undefined' ||
                        right.string === 'null') {
                    warn("unexpected_typeof_a", left, right.string);
                }
            } else if (right.id === 'typeof') {
                if (left.id !== '(string)') {
                    warn("expected_string_a", left, left.id === '(number)'
                        ? left.number
                        : left.string);
                } else if (left.string === 'undefined' ||
                        left.string === 'null') {
                    warn("unexpected_typeof_a", right, left.string);
                }
            }
            that.first = left;
            that.second = check_relation(right);
            return that;
        });
    }

    function assignop(s, op) {
        var x = infix(s, 20, function (left, that) {
            that.first = left;
            if (left.identifier) {
                if (scope[left.string]) {
                    if (scope[left.string].writeable === false) {
                        warn('read_only', left);
                    }
                } else {
                    stop('read_only');
                }
                if (funct['(params)']) {
                    funct['(params)'].forEach(function (value) {
                        if (value.string === left.string) {
                            value.assign = true;
                        }
                    });
                }
            }
            if (left === syntax.function) {
                warn('identifier_function', token);
            }
            if (left.id === '.' || left.id === '[') {
                if (!left.first || left.first.string === 'arguments') {
                    warn('bad_assignment', that);
                }
            } else if (left.identifier) {
                if (!left.reserved && funct[left.string] === 'exception') {
                    warn('assign_exception', left);
                }
            } else {
                warn('bad_assignment', that);
            }
            that.second = expression(19);
            if (that.id === '=' && are_similar(that.first, that.second)) {
                warn('weird_assignment', that);
            }
            return that;
        });
        x.assign = true;
        if (op) {
            if (syntax[op].bitwise) {
                x.bitwise = true;
            }
        }
        return x;
    }

    function bitwise(s, p) {
        var x = infix(s, p, 'number');
        x.bitwise = true;
        return x;
    }

    function suffix(s) {
        var x = symbol(s, 150);
        x.led = function (left) {
            no_space_only(prev_token, token);
            if (!option.plusplus) {
                warn('unexpected_a', this);
            } else if ((!left.identifier || left.reserved) &&
                    left.id !== '.' && left.id !== '[') {
                warn('bad_operand', this);
            }
            this.first = left;
            this.arity = 'suffix';
            return this;
        };
        return x;
    }

    function optional_identifier(variable) {
        if (next_token.identifier) {
            advance();
            if (token.reserved && (!option.es5 || variable)) {
                warn('expected_identifier_a_reserved', token);
            }
            return token.string;
        }
    }

    function identifier(variable) {
        var i = optional_identifier(variable);
        if (!i) {
            stop(token.id === 'function' && next_token.id === '('
                ? 'name_function'
                : 'expected_identifier_a');
        }
        return i;
    }

    function statement() {

        var label, old_scope = scope, the_statement;

// We don't like the empty statement.

        if (next_token.id === ';') {
            warn('unexpected_a');
            semicolon();
            return;
        }

// Is this a labeled statement?

        if (next_token.identifier && !next_token.reserved && peek().id === ':') {
            edge('label');
            label = next_token;
            advance();
            advance(':');
            scope = Object.create(old_scope);
            add_label(label, 'label');
            if (next_token.labeled !== true || funct === global_funct) {
                stop('unexpected_label_a', label);
            } else if (jx.test(label.string + ':')) {
                warn('url', label);
            }
            next_token.label = label;
        }

// Parse the statement.

        if (token.id !== 'else') {
            edge();
        }
        step_in('statement');
        the_statement = expression(0, true);
        if (the_statement) {

// Look for the final semicolon.

            if (the_statement.arity === 'statement') {
                if (the_statement.id === 'switch' ||
                        (the_statement.block && the_statement.id !== 'do')) {
                    spaces();
                } else {
                    semicolon();
                }
            } else {

// If this is an expression statement, determine if it is acceptable.
// We do not like
//      new Blah;
// statements. If it is to be used at all, new should only be used to make
// objects, not side effects. The expression statements we do like do
// assignment or invocation or delete.

                if (the_statement.id === '(') {
                    if (the_statement.first.id === 'new') {
                        warn('bad_new');
                    }
                } else if (!the_statement.assign &&
                        the_statement.id !== 'delete' &&
                        the_statement.id !== '++' &&
                        the_statement.id !== '--') {
                    warn('assignment_function_expression', the_statement);
                }
                semicolon();
            }
        }
        step_out();
        scope = old_scope;
        return the_statement;
    }

    function statements() {
        var array = [], disruptor, the_statement;

// A disrupt statement may not be followed by any other statement.
// If the last statement is disrupt, then the sequence is disrupt.

        while (next_token.postscript !== true) {
            if (next_token.id === ';') {
                warn('unexpected_a', next_token);
                semicolon();
            } else {
                if (next_token.string === 'use strict') {
                    if ((!node_js) || funct !== global_funct || array.length > 0) {
                        warn('function_strict');
                    }
                    use_strict();
                }
                if (disruptor) {
                    warn('unreachable_a_b', next_token, next_token.string,
                        disruptor.string);
                    disruptor = null;
                }
                the_statement = statement();
                if (the_statement) {
                    array.push(the_statement);
                    if (the_statement.disrupt) {
                        disruptor = the_statement;
                        array.disrupt = true;
                    }
                }
            }
        }
        return array;
    }

    function block(ordinary) {

// array block is array sequence of statements wrapped in braces.
// ordinary is false for function bodies and try blocks.
// ordinary is true for if statements, while, etc.

        var array,
            curly = next_token,
            old_in_block = in_block,
            old_scope = scope,
            old_strict_mode = strict_mode;

        in_block = ordinary;
        scope = Object.create(scope);
        if (next_token.id === '{') {
            spaces();
            advance('{');
            step_in();
            if (!ordinary && !use_strict() && !old_strict_mode &&
                    !option.sloppy && funct['(context)'] === global_funct) {
                warn('missing_use_strict');
            }
            array = statements();
            strict_mode = old_strict_mode;
            step_out('}', curly);
        } else if (!ordinary) {
            stop('expected_a_b', next_token, '{', artifact());
        } else {
            warn('expected_a_b', next_token, '{', artifact());
            array = [statement()];
            array.disrupt = array[0].disrupt;
        }
        funct['(verb)'] = null;
        scope = old_scope;
        in_block = old_in_block;
        if (ordinary && array.length === 0) {
            warn('empty_block');
        }
        return array;
    }

    function tally_property(name) {
        if (option.properties && typeof property[name] !== 'number') {
            warn('unexpected_property_a', token, name);
        }
        if (typeof property[name] === 'number') {
            property[name] += 1;
        } else {
            property[name] = 1;
        }
    }

// ECMAScript parser

    syntax['(identifier)'] = {
        id: '(identifier)',
        lbp: 0,
        identifier: true,
        nud: function () {
            var name = this.string,
                variable = scope[name],
                site,
                writeable;

// If the variable is not in scope, then we may have an undeclared variable.
// Check the predefined list. If it was predefined, create the global
// variable.

            if (typeof variable !== 'object') {
                writeable = predefined[name];
                if (typeof writeable === 'boolean') {
                    global_scope[name] = variable = {
                        string: name,
                        writeable: writeable,
                        function: global_funct
                    };
                    global_funct[name] = 'var';

// But if the variable is not in scope, and is not predefined, and if we are not
// in the global scope, then we have an undefined variable error.

                } else {
                    if (!option.undef) {
                        warn('used_before_a', token);
                    }
                    scope[name] = variable = {
                        string: name,
                        writeable: true,
                        function: funct
                    };
                    funct[name] = 'undef';
                }

            }
            site = variable.function;

// The name is in scope and defined in the current function.

            if (funct === site) {

//      Change 'unused' to 'var', and reject labels.

                switch (funct[name]) {
                case 'becoming':
                    warn('unexpected_a', token);
                    funct[name] = 'var';
                    break;
                case 'unused':
                    funct[name] = 'var';
                    break;
                case 'unparam':
                    funct[name] = 'parameter';
                    break;
                case 'unction':
                    funct[name] = 'function';
                    break;
                case 'label':
                    warn('a_label', token, name);
                    break;
                }
                this.function = funct;

// If the name is already defined in the current
// function, but not as outer, then there is a scope error.

            } else {
                switch (funct[name]) {
                case 'closure':
                case 'function':
                case 'var':
                case 'unused':
                    warn('a_scope', token, name);
                    break;
                case 'label':
                    warn('a_label', token, name);
                    break;
                case 'outer':
                case 'global':
                    break;
                default:

// If the name is defined in an outer function, make an outer entry, and if
// it was unused, make it var.

                    switch (site[name]) {
                    case 'becoming':
                    case 'closure':
                    case 'function':
                    case 'parameter':
                    case 'unction':
                    case 'unparam':
                    case 'unused':
                    case 'var':
                        site[name] = 'closure';
                        funct[name] = site === global_funct
                            ? 'global'
                            : 'outer';
                        this.function = site;
                        break;
                    case 'undef':
                        funct[name] = 'undef';
                        break;
                    case 'label':
                        warn('a_label', token, name);
                        break;
                    }
                }
            }
            return this;
        },
        led: function () {
            stop('expected_operator_a');
        }
    };

// Build the syntax table by declaring the syntactic elements.

    type('(array)', 'array');
    type('(color)', 'color');
    type('(function)', 'function');
    type('(number)', 'number', return_this);
    type('(object)', 'object');
    type('(string)', 'string', return_this);
    type('(boolean)', 'boolean', return_this);
    type('(range)', 'range');
    type('(regexp)', 'regexp', return_this);

    ultimate('(begin)');
    ultimate('(end)');
    ultimate('(error)');
    postscript(symbol('</'));
    symbol('<!');
    symbol('<!--');
    symbol('-->');
    postscript(symbol('}'));
    symbol(')');
    symbol(']');
    postscript(symbol('"'));
    postscript(symbol('\''));
    symbol(';');
    symbol(':');
    symbol(',');
    symbol('#');
    symbol('@');
    symbol('*/');
    postscript(reserve('case'));
    reserve('catch');
    postscript(reserve('default'));
    reserve('else');
    reserve('finally');

    reservevar('arguments', function (x) {
        if (strict_mode && funct === global_funct) {
            warn('strict', x);
        }
        funct['(arguments)'] = true;
    });
    reservevar('eval');
    constant('false', 'boolean');
    constant('Infinity', 'number');
    constant('NaN', 'number');
    constant('null', '');
    reservevar('this', function (x) {
        if (strict_mode && funct['(token)'] &&
                (funct['(token)'].arity === 'statement' &&
                funct['(name)'].charAt(0) > 'Z')) {
            warn('strict', x);
        }
    });
    constant('true', 'boolean');
    constant('undefined', '');

    infix('?', 30, function (left, that) {
        step_in('?');
        that.first = expected_condition(expected_relation(left));
        that.second = expression(0);
        spaces();
        step_out();
        var colon = next_token;
        advance(':');
        step_in(':');
        spaces();
        that.third = expression(10);
        that.arity = 'ternary';
        if (are_similar(that.second, that.third)) {
            warn('weird_ternary', colon);
        } else if (are_similar(that.first, that.second)) {
            warn('use_or', that);
        }
        step_out();
        return that;
    });

    infix('||', 40, function (left, that) {
        function paren_check(that) {
            if (that.id === '&&' && !that.paren) {
                warn('and', that);
            }
            return that;
        }

        that.first = paren_check(expected_condition(expected_relation(left)));
        that.second = paren_check(expected_relation(expression(40)));
        if (are_similar(that.first, that.second)) {
            warn('weird_condition', that);
        }
        return that;
    });

    infix('&&', 50, function (left, that) {
        that.first = expected_condition(expected_relation(left));
        that.second = expected_relation(expression(50));
        if (are_similar(that.first, that.second)) {
            warn('weird_condition', that);
        }
        return that;
    });

    prefix('void', function (that) {
        that.first = expression(0);
        if (option.es5 || strict_mode) {
            warn('expected_a_b', that, 'undefined', 'void');
        } else if (that.first.number !== 0) {
            warn('expected_a_b', that.first, '0', artifact(that.first));
        }
        return that;
    });

    bitwise('|', 70);
    bitwise('^', 80);
    bitwise('&', 90);

    relation('==', '===');
    relation('===');
    relation('!=', '!==');
    relation('!==');
    relation('<');
    relation('>');
    relation('<=');
    relation('>=');

    bitwise('<<', 120);
    bitwise('>>', 120);
    bitwise('>>>', 120);

    infix('in', 120, function (left, that) {
        warn('infix_in', that);
        that.left = left;
        that.right = expression(130);
        return that;
    });
    infix('instanceof', 120);
    infix('+', 130, function (left, that) {
        if (left.id === '(number)') {
            if (left.number === 0) {
                warn('unexpected_a', left, '0');
            }
        } else if (left.id === '(string)') {
            if (left.string === '') {
                warn('expected_a_b', left, 'String', '\'\'');
            }
        }
        var right = expression(130);
        if (right.id === '(number)') {
            if (right.number === 0) {
                warn('unexpected_a', right, '0');
            }
        } else if (right.id === '(string)') {
            if (right.string === '') {
                warn('expected_a_b', right, 'String', '\'\'');
            }
        }
        if (left.id === right.id) {
            if (left.id === '(string)' || left.id === '(number)') {
                if (left.id === '(string)') {
                    left.string += right.string;
                    if (jx.test(left.string)) {
                        warn('url', left);
                    }
                } else {
                    left.number += right.number;
                }
                left.thru = right.thru;
                return left;
            }
        }
        that.first = left;
        that.second = right;
        return that;
    });
    prefix('+');
    prefix('+++', function () {
        warn('confusing_a', token);
        this.first = expression(150);
        this.arity = 'prefix';
        return this;
    });
    infix('+++', 130, function (left) {
        warn('confusing_a', token);
        this.first = left;
        this.second = expression(130);
        return this;
    });
    infix('-', 130, function (left, that) {
        if ((left.id === '(number)' && left.number === 0) || left.id === '(string)') {
            warn('unexpected_a', left);
        }
        var right = expression(130);
        if ((right.id === '(number)' && right.number === 0) || right.id === '(string)') {
            warn('unexpected_a', right);
        }
        if (left.id === right.id && left.id === '(number)') {
            left.number -= right.number;
            left.thru = right.thru;
            return left;
        }
        that.first = left;
        that.second = right;
        return that;
    });
    prefix('-');
    prefix('---', function () {
        warn('confusing_a', token);
        this.first = expression(150);
        this.arity = 'prefix';
        return this;
    });
    infix('---', 130, function (left) {
        warn('confusing_a', token);
        this.first = left;
        this.second = expression(130);
        return this;
    });
    infix('*', 140, function (left, that) {
        if ((left.id === '(number)' && (left.number === 0 || left.number === 1)) || left.id === '(string)') {
            warn('unexpected_a', left);
        }
        var right = expression(140);
        if ((right.id === '(number)' && (right.number === 0 || right.number === 1)) || right.id === '(string)') {
            warn('unexpected_a', right);
        }
        if (left.id === right.id && left.id === '(number)') {
            left.number *= right.number;
            left.thru = right.thru;
            return left;
        }
        that.first = left;
        that.second = right;
        return that;
    });
    infix('/', 140, function (left, that) {
        if ((left.id === '(number)' && left.number === 0) || left.id === '(string)') {
            warn('unexpected_a', left);
        }
        var right = expression(140);
        if ((right.id === '(number)' && (right.number === 0 || right.number === 1)) || right.id === '(string)') {
            warn('unexpected_a', right);
        }
        if (left.id === right.id && left.id === '(number)') {
            left.number /= right.number;
            left.thru = right.thru;
            return left;
        }
        that.first = left;
        that.second = right;
        return that;
    });
    infix('%', 140, function (left, that) {
        if ((left.id === '(number)' && (left.number === 0 || left.number === 1)) || left.id === '(string)') {
            warn('unexpected_a', left);
        }
        var right = expression(140);
        if ((right.id === '(number)' && right.number === 0) || right.id === '(string)') {
            warn('unexpected_a', right);
        }
        if (left.id === right.id && left.id === '(number)') {
            left.number %= right.number;
            left.thru = right.thru;
            return left;
        }
        that.first = left;
        that.second = right;
        return that;
    });

    suffix('++');
    prefix('++');

    suffix('--');
    prefix('--');
    prefix('delete', function (that) {
        one_space();
        var p = expression(0);
        if (!p || (p.id !== '.' && p.id !== '[')) {
            warn('deleted');
        }
        that.first = p;
        return that;
    });

    prefix('~', function (that) {
        no_space_only();
        if (!option.bitwise) {
            warn('unexpected_a', that);
        }
        that.first = expression(150);
        return that;
    });
    function banger(that) {
        no_space_only();
        that.first = expected_condition(expression(150));
        if (bang[that.first.id] === that || that.first.assign) {
            warn('confusing_a', that);
        }
        return that;
    }
    prefix('!', banger);
    prefix('!!', banger);
    prefix('typeof');
    prefix('new', function (that) {
        one_space();
        var c = expression(160), n, p, v;
        that.first = c;
        if (c.id !== 'function') {
            if (c.identifier) {
                switch (c.string) {
                case 'Object':
                    warn('use_object', token);
                    break;
                case 'Array':
                    if (next_token.id === '(') {
                        p = next_token;
                        p.first = this;
                        advance('(');
                        if (next_token.id !== ')') {
                            n = expression(0);
                            p.second = [n];
                            if (n.id !== '(number)' || next_token.id === ',') {
                                warn('use_array', p);
                            }
                            while (next_token.id === ',') {
                                advance(',');
                                p.second.push(expression(0));
                            }
                        } else {
                            warn('use_array', token);
                        }
                        advance(')', p);
                        return p;
                    }
                    warn('use_array', token);
                    break;
                case 'Number':
                case 'String':
                case 'Boolean':
                case 'Math':
                case 'JSON':
                    warn('not_a_constructor', c);
                    break;
                case 'Function':
                    if (!option.evil) {
                        warn('function_eval');
                    }
                    break;
                case 'Date':
                case 'RegExp':
                case 'this':
                    break;
                default:
                    if (c.id !== 'function') {
                        v = c.string.charAt(0);
                        if (!option.newcap && (v < 'A' || v > 'Z')) {
                            warn('constructor_name_a', token);
                        }
                    }
                }
            } else {
                if (c.id !== '.' && c.id !== '[' && c.id !== '(') {
                    warn('bad_constructor', token);
                }
            }
        } else {
            warn('weird_new', that);
        }
        if (next_token.id !== '(') {
            warn('missing_a', next_token, '()');
        }
        return that;
    });

    infix('(', 160, function (left, that) {
        var e, p;
        if (indent && indent.mode === 'expression') {
            no_space(prev_token, token);
        } else {
            no_space_only(prev_token, token);
        }
        if (!left.immed && left.id === 'function') {
            warn('wrap_immediate');
        }
        p = [];
        if (left.identifier) {
            if (left.string.match(/^[A-Z]([A-Z0-9_$]*[a-z][A-Za-z0-9_$]*)?$/)) {
                if (left.string !== 'Number' && left.string !== 'String' &&
                        left.string !== 'Boolean' && left.string !== 'Date') {
                    if (left.string === 'Math' || left.string === 'JSON') {
                        warn('not_a_function', left);
                    } else if (left.string === 'Object') {
                        warn('use_object', token);
                    } else if (left.string === 'Array' || !option.newcap) {
                        warn('missing_a', left, 'new');
                    }
                }
            }
        } else if (left.id === '.') {
            if (left.second.string === 'split' &&
                    left.first.id === '(string)') {
                warn('use_array', left.second);
            }
        }
        step_in();
        if (next_token.id !== ')') {
            no_space();
            for (;;) {
                edge();
                e = expression(10);
                if (left.string === 'Boolean' && (e.id === '!' || e.id === '~')) {
                    warn('weird_condition', e);
                }
                p.push(e);
                if (next_token.id !== ',') {
                    break;
                }
                comma();
            }
        }
        no_space();
        step_out(')', that);
        if (typeof left === 'object') {
            if (left.string === 'parseInt' && p.length === 1) {
                warn('radix', left);
            } else if (left.string === 'String' && p.length >= 1 && p[0].id === '(string)') {
                warn('unexpected_a', left);
            }
            if (!option.evil) {
                if (left.string === 'eval' || left.string === 'Function' ||
                        left.string === 'execScript') {
                    warn('evil', left);
                } else if (p[0] && p[0].id === '(string)' &&
                        (left.string === 'setTimeout' ||
                        left.string === 'setInterval')) {
                    warn('implied_evil', left);
                }
            }
            if (!left.identifier && left.id !== '.' && left.id !== '[' &&
                    left.id !== '(' && left.id !== '&&' && left.id !== '||' &&
                    left.id !== '?') {
                warn('bad_invocation', left);
            }
            if (left.id === '.') {
                if (p.length > 0 &&
                        left.first && left.first.first &&
                        are_similar(p[0], left.first.first)) {
                    if (left.second.string === 'call' ||
                            (left.second.string === 'apply' && (p.length === 1 ||
                            (p[1].arity === 'prefix' && p[1].id === '[')))) {
                        warn('unexpected_a', left.second);
                    }
                }
                if (left.second.string === 'toString') {
                    if (left.first.id === '(string)' || left.first.id === '(number)') {
                        warn('unexpected_a', left.second);
                    }
                }
            }
        }
        that.first = left;
        that.second = p;
        return that;
    }, true);

    prefix('(', function (that) {
        step_in('expression');
        no_space();
        edge();
        if (next_token.id === 'function') {
            next_token.immed = true;
        }
        var value = expression(0);
        value.paren = true;
        no_space();
        step_out(')', that);
        if (value.id === 'function') {
            switch (next_token.id) {
            case '(':
                warn('move_invocation');
                break;
            case '.':
            case '[':
                warn('unexpected_a');
                break;
            default:
                warn('bad_wrap', that);
            }
        } else if (!value.arity) {
            warn('unexpected_a', that);
        }
        return value;
    });

    infix('.', 170, function (left, that) {
        no_space(prev_token, token);
        no_space();
        var name = identifier();
        if (typeof name === 'string') {
            tally_property(name);
        }
        that.first = left;
        that.second = token;
        if (left && left.string === 'arguments' &&
                (name === 'callee' || name === 'caller')) {
            warn('avoid_a', left, 'arguments.' + name);
        } else if (!option.evil && left && left.string === 'document' &&
                (name === 'write' || name === 'writeln')) {
            warn('write_is_wrong', left);
        } else if (!option.stupid && syx.test(name)) {
            warn('sync_a', token);
        }
        if (!option.evil && (name === 'eval' || name === 'execScript')) {
            warn('evil');
        }
        return that;
    }, true);

    infix('[', 170, function (left, that) {
        var e, s;
        no_space_only(prev_token, token);
        no_space();
        step_in();
        edge();
        e = expression(0);
        switch (e.id) {
        case '(number)':
            if (e.id === '(number)' && left.id === 'arguments') {
                warn('use_param', left);
            }
            break;
        case '(string)':
            if (!option.evil &&
                    (e.string === 'eval' || e.string === 'execScript')) {
                warn('evil', e);
            } else if (!option.sub && ix.test(e.string)) {
                s = syntax[e.string];
                if (!s || !s.reserved) {
                    warn('subscript', e);
                }
            }
            tally_property(e.string);
            break;
        }
        step_out(']', that);
        no_space(prev_token, token);
        that.first = left;
        that.second = e;
        return that;
    }, true);

    prefix('[', function (that) {
        that.first = [];
        step_in('array');
        while (next_token.id !== '(end)') {
            while (next_token.id === ',') {
                warn('unexpected_a', next_token);
                advance(',');
            }
            if (next_token.id === ']') {
                break;
            }
            indent.wrap = false;
            edge();
            that.first.push(expression(10));
            if (next_token.id === ',') {
                comma();
                if (next_token.id === ']' && !option.es5) {
                    warn('unexpected_a', token);
                    break;
                }
            } else {
                break;
            }
        }
        step_out(']', that);
        return that;
    }, 170);

    function property_name() {
        var id = optional_identifier();
        if (!id) {
            if (next_token.id === '(string)') {
                id = next_token.string;
                advance();
            } else if (next_token.id === '(number)') {
                id = next_token.number.toString();
                advance();
            }
        }
        return id;
    }

    assignop('=');
    assignop('+=', '+');
    assignop('-=', '-');
    assignop('*=', '*');
    assignop('/=', '/').nud = function () {
        stop('slash_equal');
    };
    assignop('%=', '%');
    assignop('&=', '&');
    assignop('|=', '|');
    assignop('^=', '^');
    assignop('<<=', '<<');
    assignop('>>=', '>>');
    assignop('>>>=', '>>>');

    function function_params() {
        var id, paren = next_token, params = [];
        advance('(');
        token.function = funct;
        step_in();
        no_space();
        if (next_token.id === ')') {
            no_space();
            step_out(')', paren);
            return params;
        }
        for (;;) {
            edge();
            id = identifier();
            params.push(token);
            add_label(token, option.unparam ? 'parameter' : 'unparam');
            if (next_token.id === ',') {
                comma();
            } else {
                no_space();
                step_out(')', paren);
                return params;
            }
        }
    }

    function do_function(func, name) {
        var old_funct      = funct,
            old_option     = option,
            old_scope      = scope;
        funct = {
            '(name)'     : name || '\'' + (anonname || '').replace(nx, sanitize) + '\'',
            '(line)'     : next_token.line,
            '(context)'  : old_funct,
            '(breakage)' : 0,
            '(loopage)'  : 0,
            '(scope)'    : scope,
            '(token)'    : func,
            '(level)'    : old_funct['(level)'] + 1
        };
        func.function = funct;
        option = Object.create(old_option);
        scope = Object.create(old_scope);
        functions.push(funct);
        func.name = name;
        func.function = funct;
        if (name) {
            add_label(func, 'function', name);
        }
        func.writeable = false;
        func.first = funct['(params)'] = function_params();
        one_space();
        func.block = block(false);
        if (funct['(arguments)']) {
            func.first.forEach(function (value) {
                if (value.assign) {
                    warn('parameter_arguments_a', value, value.string);
                }
            });
        }
        funct      = old_funct;
        option     = old_option;
        scope      = old_scope;
    }

    prefix('{', function (that) {
        var get, i, j, name, p, set, seen = {};
        that.first = [];
        step_in();
        while (next_token.id !== '}') {
            indent.wrap = false;

// JSLint recognizes the ES5 extension for get/set in object literals,
// but requires that they be used in pairs.

            edge();
            if (next_token.string === 'get' && peek().id !== ':') {
                if (!option.es5) {
                    warn('es5');
                }
                get = next_token;
                advance('get');
                one_space_only();
                name = next_token;
                i = property_name();
                if (!i) {
                    stop('missing_property');
                }
                get.string = '';
                do_function(get);
                if (funct['(loopage)']) {
                    warn('function_loop', get);
                }
                p = get.first;
                if (p && p.length) {
                    warn('parameter_a_get_b', p[0], p[0].string, i);
                }
                comma();
                set = next_token;
                spaces();
                edge();
                advance('set');
                set.string = '';
                one_space_only();
                j = property_name();
                if (i !== j) {
                    stop('expected_a_b', token, i, j || next_token.string);
                }
                do_function(set);
                if (set.block.length === 0) {
                    warn('missing_a', token, 'throw');
                }
                p = set.first;
                if (!p || p.length !== 1) {
                    stop('parameter_set_a', set, 'value');
                } else if (p[0].string !== 'value') {
                    stop('expected_a_b', p[0], 'value', p[0].string);
                }
                name.first = [get, set];
            } else {
                name = next_token;
                i = property_name();
                if (typeof i !== 'string') {
                    stop('missing_property');
                }
                advance(':');
                spaces();
                name.first = expression(10);
            }
            that.first.push(name);
            if (seen[i] === true) {
                warn('duplicate_a', next_token, i);
            }
            seen[i] = true;
            tally_property(i);
            if (next_token.id !== ',') {
                break;
            }
            for (;;) {
                comma();
                if (next_token.id !== ',') {
                    break;
                }
                warn('unexpected_a', next_token);
            }
            if (next_token.id === '}' && !option.es5) {
                warn('unexpected_a', token);
            }
        }
        step_out('}', that);
        return that;
    });

    stmt('{', function () {
        warn('statement_block');
        this.arity = 'statement';
        this.block = statements();
        this.disrupt = this.block.disrupt;
        advance('}', this);
        return this;
    });

    stmt('/*global', directive);
    stmt('/*globals', directive);
    stmt('/*jslint', directive);
    stmt('/*member', directive);
    stmt('/*members', directive);
    stmt('/*property', directive);
    stmt('/*properties', directive);

    stmt('var', function () {

// JavaScript does not have block scope. It only has function scope. So,
// declaring a variable in a block can have unexpected consequences.

// var.first will contain an array, the array containing name tokens
// and assignment tokens.

        var assign, id, name;

        if (funct['(vars)'] && !option.vars) {
            warn('combine_var');
        } else if (funct !== global_funct) {
            funct['(vars)'] = true;
        }
        this.arity = 'statement';
        this.first = [];
        step_in('var');
        for (;;) {
            name = next_token;
            id = identifier(true);
            add_label(name, 'becoming');

            if (next_token.id === '=') {
                assign = next_token;
                assign.first = name;
                spaces();
                advance('=');
                spaces();
                if (next_token.id === 'undefined') {
                    warn('unnecessary_initialize', token, id);
                }
                if (peek(0).id === '=' && next_token.identifier) {
                    stop('var_a_not');
                }
                assign.second = expression(0);
                assign.arity = 'infix';
                this.first.push(assign);
            } else {
                this.first.push(name);
            }
            if (funct[id] === 'becoming') {
                funct[id] = 'unused';
            }
            if (next_token.id !== ',') {
                break;
            }
            comma();
            indent.wrap = false;
            if (var_mode && next_token.line === token.line &&
                    this.first.length === 1) {
                var_mode = null;
                indent.open = false;
                indent.at -= option.indent;
            }
            spaces();
            edge();
        }
        var_mode = null;
        step_out();
        return this;
    });

    stmt('function', function () {
        one_space();
        if (in_block) {
            warn('function_block', token);
        }
        var name = next_token,
            id = identifier(true);
        add_label(name, 'unction');
        no_space();
        this.arity = 'statement';
        do_function(this, id);
        if (next_token.id === '(' && next_token.line === token.line) {
            stop('function_statement');
        }
        return this;
    });

    prefix('function', function (that) {
        var id = optional_identifier(true);
        if (id) {
            no_space();
        } else {
            id = '';
        }
        do_function(that, id);
        if (funct['(loopage)']) {
            warn('function_loop');
        }
        switch (next_token.id) {
        case ';':
        case '(':
        case ')':
        case ',':
        case ']':
        case '}':
        case ':':
            break;
        case '.':
            if (peek().string !== 'bind' || peek(1).id !== '(') {
                warn('unexpected_a');
            }
            break;
        default:
            stop('unexpected_a');
        }
        that.arity = 'function';
        return that;
    });

    stmt('if', function () {
        var paren = next_token;
        one_space();
        advance('(');
        step_in('control');
        no_space();
        edge();
        this.arity = 'statement';
        this.first = expected_condition(expected_relation(expression(0)));
        no_space();
        step_out(')', paren);
        one_space();
        this.block = block(true);
        if (next_token.id === 'else') {
            one_space();
            advance('else');
            one_space();
            this.else = next_token.id === 'if' || next_token.id === 'switch'
                ? statement(true)
                : block(true);
            if (this.else.disrupt && this.block.disrupt) {
                this.disrupt = true;
            }
        }
        return this;
    });

    stmt('try', function () {

// try.first    The catch variable
// try.second   The catch clause
// try.third    The finally clause
// try.block    The try block

        var exception_variable, old_scope, paren;
        one_space();
        this.arity = 'statement';
        this.block = block(false);
        if (next_token.id === 'catch') {
            one_space();
            advance('catch');
            one_space();
            paren = next_token;
            advance('(');
            step_in('control');
            no_space();
            edge();
            old_scope = scope;
            scope = Object.create(old_scope);
            exception_variable = next_token.string;
            this.first = exception_variable;
            if (!next_token.identifier) {
                warn('expected_identifier_a', next_token);
            } else {
                add_label(next_token, 'exception');
            }
            advance();
            no_space();
            step_out(')', paren);
            one_space();
            this.second = block(false);
            scope = old_scope;
        }
        if (next_token.id === 'finally') {
            one_space();
            advance('finally');
            one_space();
            this.third = block(false);
        } else if (!this.second) {
            stop('expected_a_b', next_token, 'catch', artifact());
        }
        return this;
    });

    labeled_stmt('while', function () {
        one_space();
        var paren = next_token;
        funct['(breakage)'] += 1;
        funct['(loopage)'] += 1;
        advance('(');
        step_in('control');
        no_space();
        edge();
        this.arity = 'statement';
        this.first = expected_relation(expression(0));
        if (this.first.id !== 'true') {
            expected_condition(this.first, bundle.unexpected_a);
        }
        no_space();
        step_out(')', paren);
        one_space();
        this.block = block(true);
        if (this.block.disrupt) {
            warn('strange_loop', prev_token);
        }
        funct['(breakage)'] -= 1;
        funct['(loopage)'] -= 1;
        return this;
    });

    reserve('with');

    labeled_stmt('switch', function () {

// switch.first         the switch expression
// switch.second        the array of cases. A case is 'case' or 'default' token:
//    case.first        the array of case expressions
//    case.second       the array of statements
// If all of the arrays of statements are disrupt, then the switch is disrupt.

        var cases = [],
            old_in_block = in_block,
            particular,
            that = token,
            the_case = next_token,
            unbroken = true;

        function find_duplicate_case(value) {
            if (are_similar(particular, value)) {
                warn('duplicate_a', value);
            }
        }

        funct['(breakage)'] += 1;
        one_space();
        advance('(');
        no_space();
        step_in();
        this.arity = 'statement';
        this.first = expected_condition(expected_relation(expression(0)));
        no_space();
        step_out(')', the_case);
        one_space();
        advance('{');
        step_in();
        in_block = true;
        this.second = [];
        if (that.from !== next_token.from && !option.white) {
            warn('expected_a_at_b_c', next_token, next_token.string, that.from, next_token.from);
        }
        while (next_token.id === 'case') {
            the_case = next_token;
            cases.forEach(find_duplicate_case);
            the_case.first = [];
            the_case.arity = 'case';
            spaces();
            edge('case');
            advance('case');
            for (;;) {
                one_space();
                particular = expression(0);
                cases.forEach(find_duplicate_case);
                cases.push(particular);
                the_case.first.push(particular);
                if (particular.id === 'NaN') {
                    warn('unexpected_a', particular);
                }
                no_space_only();
                advance(':');
                if (next_token.id !== 'case') {
                    break;
                }
                spaces();
                edge('case');
                advance('case');
            }
            spaces();
            the_case.second = statements();
            if (the_case.second && the_case.second.length > 0) {
                particular = the_case.second[the_case.second.length - 1];
                if (particular.disrupt) {
                    if (particular.id === 'break') {
                        unbroken = false;
                    }
                } else {
                    warn('missing_a_after_b', next_token, 'break', 'case');
                }
            } else {
                warn('empty_case');
            }
            this.second.push(the_case);
        }
        if (this.second.length === 0) {
            warn('missing_a', next_token, 'case');
        }
        if (next_token.id === 'default') {
            spaces();
            the_case = next_token;
            the_case.arity = 'case';
            edge('case');
            advance('default');
            no_space_only();
            advance(':');
            spaces();
            the_case.second = statements();
            if (the_case.second && the_case.second.length > 0) {
                particular = the_case.second[the_case.second.length - 1];
                if (unbroken && particular.disrupt && particular.id !== 'break') {
                    this.disrupt = true;
                }
            }
            this.second.push(the_case);
        }
        funct['(breakage)'] -= 1;
        spaces();
        step_out('}', this);
        in_block = old_in_block;
        return this;
    });

    stmt('debugger', function () {
        if (!option.debug) {
            warn('unexpected_a', this);
        }
        this.arity = 'statement';
        return this;
    });

    labeled_stmt('do', function () {
        funct['(breakage)'] += 1;
        funct['(loopage)'] += 1;
        one_space();
        this.arity = 'statement';
        this.block = block(true);
        if (this.block.disrupt) {
            warn('strange_loop', prev_token);
        }
        one_space();
        advance('while');
        var paren = next_token;
        one_space();
        advance('(');
        step_in();
        no_space();
        edge();
        this.first = expected_condition(expected_relation(expression(0)), bundle.unexpected_a);
        no_space();
        step_out(')', paren);
        funct['(breakage)'] -= 1;
        funct['(loopage)'] -= 1;
        return this;
    });

    labeled_stmt('for', function () {

        var blok, filter, ok = false, paren = next_token, value;
        this.arity = 'statement';
        funct['(breakage)'] += 1;
        funct['(loopage)'] += 1;
        advance('(');
        if (next_token.id === ';') {
            no_space();
            advance(';');
            no_space();
            advance(';');
            no_space();
            advance(')');
            blok = block(true);
        } else {
            step_in('control');
            spaces(this, paren);
            no_space();
            if (next_token.id === 'var') {
                stop('move_var');
            }
            edge();
            if (peek(0).id === 'in') {
                this.forin = true;
                value = next_token;
                switch (funct[value.string]) {
                case 'unused':
                    funct[value.string] = 'var';
                    break;
                case 'closure':
                case 'var':
                    break;
                default:
                    warn('bad_in_a', value);
                }
                advance();
                advance('in');
                this.first = value;
                this.second = expression(20);
                step_out(')', paren);
                blok = block(true);
                if (!option.forin) {
                    if (blok.length === 1 && typeof blok[0] === 'object' &&
                            blok[0].string === 'if' && !blok[0].else) {
                        filter = blok[0].first;
                        while (filter.id === '&&') {
                            filter = filter.first;
                        }
                        switch (filter.id) {
                        case '===':
                        case '!==':
                            ok = filter.first.id === '['
                                ? filter.first.first.string === this.second.string &&
                                    filter.first.second.string === this.first.string
                                : filter.first.id === 'typeof' &&
                                    filter.first.first.id === '[' &&
                                    filter.first.first.first.string === this.second.string &&
                                    filter.first.first.second.string === this.first.string;
                            break;
                        case '(':
                            ok = filter.first.id === '.' && ((
                                filter.first.first.string === this.second.string &&
                                filter.first.second.string === 'hasOwnProperty' &&
                                filter.second[0].string === this.first.string
                            ) || (
                                filter.first.first.id === '.' &&
                                filter.first.first.first.id === '.' &&
                                filter.first.first.first.first.string === 'Object' &&
                                filter.first.first.first.second.string === 'prototype' &&
                                filter.first.first.second.string === 'hasOwnProperty' &&
                                filter.first.second.string === 'call' &&
                                filter.second[0].string === this.second.string &&
                                filter.second[1].string === this.first.string
                            ));
                            break;
                        }
                    }
                    if (!ok) {
                        warn('for_if', this);
                    }
                }
            } else {
                edge();
                this.first = [];
                for (;;) {
                    this.first.push(expression(0, 'for'));
                    if (next_token.id !== ',') {
                        break;
                    }
                    comma();
                }
                semicolon();
                edge();
                this.second = expected_relation(expression(0));
                if (this.second.id !== 'true') {
                    expected_condition(this.second, bundle.unexpected_a);
                }
                semicolon(token);
                if (next_token.id === ';') {
                    stop('expected_a_b', next_token, ')', ';');
                }
                this.third = [];
                edge();
                for (;;) {
                    this.third.push(expression(0, 'for'));
                    if (next_token.id !== ',') {
                        break;
                    }
                    comma();
                }
                no_space();
                step_out(')', paren);
                one_space();
                blok = block(true);
            }
        }
        if (blok.disrupt) {
            warn('strange_loop', prev_token);
        }
        this.block = blok;
        funct['(breakage)'] -= 1;
        funct['(loopage)'] -= 1;
        return this;
    });

    disrupt_stmt('break', function () {
        var label = next_token.string;
        this.arity = 'statement';
        if (funct['(breakage)'] === 0) {
            warn('unexpected_a', this);
        }
        if (next_token.identifier && token.line === next_token.line) {
            one_space_only();
            if (funct[label] !== 'label') {
                warn('not_a_label', next_token);
            } else if (scope[label].function !== funct) {
                warn('not_a_scope', next_token);
            }
            this.first = next_token;
            advance();
        }
        return this;
    });

    disrupt_stmt('continue', function () {
        if (!option.continue) {
            warn('unexpected_a', this);
        }
        var label = next_token.string;
        this.arity = 'statement';
        if (funct['(breakage)'] === 0) {
            warn('unexpected_a', this);
        }
        if (next_token.identifier && token.line === next_token.line) {
            one_space_only();
            if (funct[label] !== 'label') {
                warn('not_a_label', next_token);
            } else if (scope[label].function !== funct) {
                warn('not_a_scope', next_token);
            }
            this.first = next_token;
            advance();
        }
        return this;
    });

    disrupt_stmt('return', function () {
        if (funct === global_funct) {
            warn('unexpected_a', this);
        }
        this.arity = 'statement';
        if (next_token.id !== ';' && next_token.line === token.line) {
            one_space_only();
            if (next_token.id === '/' || next_token.id === '(regexp)') {
                warn('wrap_regexp');
            }
            this.first = expression(0);
            if (this.first.assign) {
                warn('unexpected_a', this.first);
            }
        }
        if (peek(0).id === '}' && peek(1).id === 'else') {
            warn('unexpected_else', this);
        }
        return this;
    });

    disrupt_stmt('throw', function () {
        this.arity = 'statement';
        one_space_only();
        this.first = expression(20);
        return this;
    });

//  Superfluous reserved words

    reserve('class');
    reserve('const');
    reserve('enum');
    reserve('export');
    reserve('extends');
    reserve('import');
    reserve('super');

// Harmony reserved words

    reserve('implements');
    reserve('interface');
    reserve('let');
    reserve('package');
    reserve('private');
    reserve('protected');
    reserve('public');
    reserve('static');
    reserve('yield');

// Parse JSON

    function json_value() {

        function json_object() {
            var brace = next_token, object = {};
            advance('{');
            if (next_token.id !== '}') {
                while (next_token.id !== '(end)') {
                    while (next_token.id === ',') {
                        warn('unexpected_a', next_token);
                        advance(',');
                    }
                    if (next_token.id !== '(string)') {
                        warn('expected_string_a');
                    }
                    if (object[next_token.string] === true) {
                        warn('duplicate_a');
                    } else if (next_token.string === '__proto__') {
                        warn('dangling_a');
                    } else {
                        object[next_token.string] = true;
                    }
                    advance();
                    advance(':');
                    json_value();
                    if (next_token.id !== ',') {
                        break;
                    }
                    advance(',');
                    if (next_token.id === '}') {
                        warn('unexpected_a', token);
                        break;
                    }
                }
            }
            advance('}', brace);
        }

        function json_array() {
            var bracket = next_token;
            advance('[');
            if (next_token.id !== ']') {
                while (next_token.id !== '(end)') {
                    while (next_token.id === ',') {
                        warn('unexpected_a', next_token);
                        advance(',');
                    }
                    json_value();
                    if (next_token.id !== ',') {
                        break;
                    }
                    advance(',');
                    if (next_token.id === ']') {
                        warn('unexpected_a', token);
                        break;
                    }
                }
            }
            advance(']', bracket);
        }

        switch (next_token.id) {
        case '{':
            json_object();
            break;
        case '[':
            json_array();
            break;
        case 'true':
        case 'false':
        case 'null':
        case '(number)':
        case '(string)':
            advance();
            break;
        case '-':
            advance('-');
            no_space_only();
            advance('(number)');
            break;
        default:
            stop('unexpected_a');
        }
    }

// The actual JSLINT function itself.

    itself = function JSLint(the_source, the_option) {

        var i, predef, tree;
        JSLINT.errors = [];
        JSLINT.tree = '';
        JSLINT.properties = '';
        begin = prev_token = token = next_token =
            Object.create(syntax['(begin)']);
        tokens = [];
        predefined = {};
        add_to_predefined(standard);
        property = {};
        if (the_option) {
            option = Object.create(the_option);
            predef = option.predef;
            if (predef) {
                if (Array.isArray(predef)) {
                    for (i = 0; i < predef.length; i += 1) {
                        predefined[predef[i]] = true;
                    }
                } else if (typeof predef === 'object') {
                    add_to_predefined(predef);
                }
            }
        } else {
            option = {};
        }
        option.indent = +option.indent || 4;
        option.maxerr = +option.maxerr || 50;
        tab = '';
        for (i = 0; i < option.indent; i += 1) {
            tab += ' ';
        }
        global_scope = scope = {};
        global_funct = funct = {
            '(scope)': scope,
            '(breakage)': 0,
            '(loopage)': 0,
            '(level)': 0
        };
        functions = [funct];

        comments_off = false;
        in_block = false;
        indent = null;
        json_mode = false;
        lookahead = [];
        node_js = false;
        prereg = true;
        src = false;
        stack = null;
        strict_mode = false;
        urls = [];
        var_mode = null;
        warnings = 0;
        lex.init(the_source);

        assume();

        try {
            advance();
            if (next_token.id === '(number)') {
                stop('unexpected_a');
            } else {
                switch (next_token.id) {
                case '{':
                case '[':
                    json_mode = true;
                    json_value();
                    break;
                default:

// If the first token is a semicolon, ignore it. This is sometimes used when
// files are intended to be appended to files that may be sloppy. A sloppy
// file may be depending on semicolon insertion on its last line.

                    step_in(1);
                    if (next_token.id === ';' && !node_js) {
                        semicolon();
                    }
                    tree = statements();
                    begin.first = tree;
                    itself.tree = begin;
                    if (tree.disrupt) {
                        warn('weird_program', prev_token);
                    }
                }
            }
            indent = null;
            advance('(end)');
            itself.property = property;
        } catch (e) {
            if (e) {        // ~~
                JSLINT.errors.push({
                    reason    : e.message,
                    line      : e.line || next_token.line,
                    character : e.character || next_token.from
                }, null);
            }
        }
        return JSLINT.errors.length === 0;
    };

// Data summary.

    itself.data = function () {
        var data = {functions: []},
            function_data,
            globals,
            i,
            j,
            kind,
            name,
            the_function,
            undef = [],
            unused = [];
        if (itself.errors.length) {
            data.errors = itself.errors;
        }

        if (json_mode) {
            data.json = true;
        }

        if (urls.length > 0) {
            data.urls = urls;
        }

        globals = Object.keys(global_scope).filter(function (value) {
            return value.charAt(0) !== '(' && typeof standard[value] !== 'boolean';
        });
        if (globals.length > 0) {
            data.globals = globals;
        }

        for (i = 1; i < functions.length; i += 1) {
            the_function = functions[i];
            function_data = {};
            for (j = 0; j < functionicity.length; j += 1) {
                function_data[functionicity[j]] = [];
            }
            for (name in the_function) {
                if (Object.prototype.hasOwnProperty.call(the_function, name)) {
                    if (name.charAt(0) !== '(') {
                        kind = the_function[name];
                        if (kind === 'unction' || kind === 'unparam') {
                            kind = 'unused';
                        }
                        if (Array.isArray(function_data[kind])) {
                            function_data[kind].push(name);
                            if (kind === 'unused') {
                                unused.push({
                                    name: name,
                                    line: the_function['(line)'],
                                    'function': the_function['(name)']
                                });
                            } else if (kind === 'undef') {
                                undef.push({
                                    name: name,
                                    line: the_function['(line)'],
                                    'function': the_function['(name)']
                                });
                            }
                        }
                    }
                }
            }
            for (j = 0; j < functionicity.length; j += 1) {
                if (function_data[functionicity[j]].length === 0) {
                    delete function_data[functionicity[j]];
                }
            }
            function_data.name = the_function['(name)'];
            function_data.params = the_function['(params)'];
            function_data.line = the_function['(line)'];
            function_data.level = the_function['(level)'];
            data.functions.push(function_data);
        }

        if (unused.length > 0) {
            data.unused = unused;
        }
        if (undef.length > 0) {
            data.undefined = undef;
        }
        data.tokens = tokens;
        return data;
    };

    itself.error_report = function (data) {
        var evidence, i, output = [], snippets, warning;
        if (data.errors) {
            if (data.json) {
                output.push('<cite>JSON: bad.</cite><br>');
            }
            for (i = 0; i < data.errors.length; i += 1) {
                warning = data.errors[i];
                if (warning) {
                    evidence = warning.evidence || '';
                    output.push('<cite>');
                    if (isFinite(warning.line)) {
                        output.push('<address>line ' +
                            String(warning.line) +
                            ' character ' + String(warning.character) +
                            '</address>');
                    }
                    output.push(warning.reason.entityify() + '</cite>');
                    if (evidence) {
                        output.push('<pre>' + evidence.entityify() + '</pre>');
                    }
                }
            }
        }
        if (data.unused || data.undefined) {
            output.push('<dl>');
            if (data.undefined) {
                output.push('<dt>undefined</dt><dd>');
                snippets = [];
                for (i = 0; i < data.undefined.length; i += 1) {
                    snippets[i] = '<code>' + data.undefined[i].name +
                        '</code>&nbsp;<address>' +
                        data.undefined[i]['function']  + ' ' +
                        String(data.undefined[i].line) + '</address>';
                }
                output.push(snippets.join(', '));
                output.push('</dd>');
            }
            if (data.unused) {
                output.push('<dt>unused</dt><dd>');
                snippets = [];
                for (i = 0; i < data.unused.length; i += 1) {
                    snippets[i] = '<code>' + data.unused[i].name + '</code>&nbsp;<address>' +
                        data.unused[i].function  + ' ' +
                        String(data.unused[i].line) + '</address>';
                }
                output.push(snippets.join(', '));
                output.push('</dd>');
            }
            output.push('</dl>');
        }
        return output.join('');
    };

    itself.report = function (data) {
        var dl, i, j, names, output = [], the_function;

        function detail(h, value) {
            var comma_needed, singularity;
            if (Array.isArray(value)) {
                output.push("<dt>" + h + "</dt><dd>");
                value.sort().forEach(function (item) {
                    if (item !== singularity) {
                        singularity = item;
                        output.push((comma_needed ? ', ' : '') + singularity);
                        comma_needed = true;
                    }
                });
                output.push("</dd>");
            } else if (value) {
                output.push("<dt>" + h + "</dt><dd>", value, "</dd>");
            }
        }

        output.push('<dl class=level0>');
        if (data.urls) {
            detail('url', data.urls);
            dl = true;
        }
        if (data.globals) {
            detail('global', data.globals);
            dl = true;
        } else if (data.json) {
            if (!data.errors) {
                output.push("<dt>JSON: good.</dt>");
            }
        } else {
            output.push("<dt><i>No new global variables introduced.</i></dt>");
        }
        if (dl) {
            output.push("</dl>");
        } else {
            output[0] = '';
        }

        if (data.functions) {
            for (i = 0; i < data.functions.length; i += 1) {
                the_function = data.functions[i];
                names = [];
                if (the_function.params) {
                    for (j = 0; j < the_function.params.length; j += 1) {
                        names[j] = the_function.params[j].string;
                    }
                }
                output.push('<dl class=level' + the_function.level +
                    '><address>line ' + String(the_function.line) +
                    '</address>' + the_function.name.entityify() + '(' +
                    names.join(', ') + ')');
                detail('undefined', the_function.undefined);
                detail('unused', the_function.unused);
                detail('closure', the_function.closure);
                detail('variable', the_function.var);
                detail('exception', the_function.exception);
                detail('outer', the_function.outer);
                detail('global', the_function.global);
                detail('label', the_function.label);
                output.push('</dl>');
            }
        }
        return output.join('');
    };

    itself.properties_report = function (property) {
        if (!property) {
            return '';
        }
        var i,
            key,
            keys = Object.keys(property).sort(),
            length,
            mem = '    ',
            name,
            not_first = false,
            output = ['/*properties'];
        for (i = 0; i < keys.length; i += 1) {
            key = keys[i];
            if (property[key] > 0) {
                if (not_first) {
                    mem += ', ';
                }
                name = ix.test(key)
                    ? key
                    : '\'' + key.replace(nx, sanitize) + '\'';
                length += name.length + 2;
                if (mem.length + name.length >= 80) {
                    output.push(mem);
                    mem = '    ';
                }
                mem += name;
                not_first = true;
            }
        }
        output.push(mem, '*/\n');
        return output.join('\n');
    };

    itself.color = function (data) {
        var from,
            i = 1,
            level,
            line,
            result = [],
            thru,
            token = data.tokens[0];
        while (token && token.id !== '(end)') {
            from = token.from;
            line = token.line;
            thru = token.thru;
            level = token.function['(level)'];
            do {
                thru = token.thru;
                token = data.tokens[i];
                i += 1;
            } while (token && token.line === line && token.from - thru < 5 &&
                    level === token.function['(level)']);
            result.push({
                line: line,
                level: level,
                from: from,
                thru: thru
            });
        }
        return result;
    };

    itself.jslint = itself;

    itself.edition = '2013-03-19';

    return itself;
}());
