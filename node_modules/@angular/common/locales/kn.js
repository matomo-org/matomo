/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
(function (factory) {
    if (typeof module === "object" && typeof module.exports === "object") {
        var v = factory(null, exports);
        if (v !== undefined) module.exports = v;
    }
    else if (typeof define === "function" && define.amd) {
        define("@angular/common/locales/kn", ["require", "exports"], factory);
    }
})(function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    // THIS CODE IS GENERATED - DO NOT MODIFY
    // See angular/tools/gulp-tasks/cldr/extract.js
    var u = undefined;
    function plural(n) {
        var i = Math.floor(Math.abs(n));
        if (i === 0 || n === 1)
            return 1;
        return 5;
    }
    exports.default = [
        'kn',
        [['ಪೂ', 'ಅ'], ['ಪೂರ್ವಾಹ್ನ', 'ಅಪರಾಹ್ನ'], u],
        [['ಪೂರ್ವಾಹ್ನ', 'ಅಪರಾಹ್ನ'], u, u],
        [
            ['ಭಾ', 'ಸೋ', 'ಮಂ', 'ಬು', 'ಗು', 'ಶು', 'ಶ'], ['ಭಾನು', 'ಸೋಮ', 'ಮಂಗಳ', 'ಬುಧ', 'ಗುರು', 'ಶುಕ್ರ', 'ಶನಿ'],
            ['ಭಾನುವಾರ', 'ಸೋಮವಾರ', 'ಮಂಗಳವಾರ', 'ಬುಧವಾರ', 'ಗುರುವಾರ', 'ಶುಕ್ರವಾರ', 'ಶನಿವಾರ'],
            ['ಭಾನು', 'ಸೋಮ', 'ಮಂಗಳ', 'ಬುಧ', 'ಗುರು', 'ಶುಕ್ರ', 'ಶನಿ']
        ],
        u,
        [
            ['ಜ', 'ಫೆ', 'ಮಾ', 'ಏ', 'ಮೇ', 'ಜೂ', 'ಜು', 'ಆ', 'ಸೆ', 'ಅ', 'ನ', 'ಡಿ'],
            ['ಜನವರಿ', 'ಫೆಬ್ರವರಿ', 'ಮಾರ್ಚ್', 'ಏಪ್ರಿ', 'ಮೇ', 'ಜೂನ್', 'ಜುಲೈ', 'ಆಗ', 'ಸೆಪ್ಟೆಂ', 'ಅಕ್ಟೋ', 'ನವೆಂ', 'ಡಿಸೆಂ'],
            [
                'ಜನವರಿ', 'ಫೆಬ್ರವರಿ', 'ಮಾರ್ಚ್', 'ಏಪ್ರಿಲ್', 'ಮೇ', 'ಜೂನ್', 'ಜುಲೈ', 'ಆಗಸ್ಟ್', 'ಸೆಪ್ಟೆಂಬರ್', 'ಅಕ್ಟೋಬರ್', 'ನವೆಂಬರ್',
                'ಡಿಸೆಂಬರ್'
            ]
        ],
        [
            ['ಜ', 'ಫೆ', 'ಮಾ', 'ಏ', 'ಮೇ', 'ಜೂ', 'ಜು', 'ಆ', 'ಸೆ', 'ಅ', 'ನ', 'ಡಿ'],
            ['ಜನ', 'ಫೆಬ್ರ', 'ಮಾರ್ಚ್', 'ಏಪ್ರಿ', 'ಮೇ', 'ಜೂನ್', 'ಜುಲೈ', 'ಆಗ', 'ಸೆಪ್ಟೆಂ', 'ಅಕ್ಟೋ', 'ನವೆಂ', 'ಡಿಸೆಂ'],
            [
                'ಜನವರಿ', 'ಫೆಬ್ರವರಿ', 'ಮಾರ್ಚ್', 'ಏಪ್ರಿಲ್', 'ಮೇ', 'ಜೂನ್', 'ಜುಲೈ', 'ಆಗಸ್ಟ್', 'ಸೆಪ್ಟೆಂಬರ್', 'ಅಕ್ಟೋಬರ್', 'ನವೆಂಬರ್',
                'ಡಿಸೆಂಬರ್'
            ]
        ],
        [['ಕ್ರಿ.ಪೂ', 'ಕ್ರಿ.ಶ'], u, ['ಕ್ರಿಸ್ತ ಪೂರ್ವ', 'ಕ್ರಿಸ್ತ ಶಕ']],
        0,
        [0, 0],
        ['d/M/yy', 'MMM d, y', 'MMMM d, y', 'EEEE, MMMM d, y'],
        ['hh:mm a', 'hh:mm:ss a', 'hh:mm:ss a z', 'hh:mm:ss a zzzz'],
        ['{1} {0}', u, u, u],
        ['.', ',', ';', '%', '+', '-', 'E', '×', '‰', '∞', 'NaN', ':'],
        ['#,##0.###', '#,##0%', '¤#,##0.00', '#E0'],
        'INR',
        '₹',
        'ಭಾರತೀಯ ರೂಪಾಯಿ',
        { 'JPY': ['JP¥', '¥'], 'RON': [u, 'ಲೀ'], 'THB': ['฿'], 'TWD': ['NT$'] },
        'ltr',
        plural
    ];
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoia24uanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb21tb24vbG9jYWxlcy9rbi50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7OztJQUVILHlDQUF5QztJQUN6QywrQ0FBK0M7SUFFL0MsSUFBTSxDQUFDLEdBQUcsU0FBUyxDQUFDO0lBRXBCLFNBQVMsTUFBTSxDQUFDLENBQVM7UUFDdkIsSUFBSSxDQUFDLEdBQUcsSUFBSSxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFDaEMsSUFBSSxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDO1lBQUUsT0FBTyxDQUFDLENBQUM7UUFDakMsT0FBTyxDQUFDLENBQUM7SUFDWCxDQUFDO0lBRUQsa0JBQWU7UUFDYixJQUFJO1FBQ0osQ0FBQyxDQUFDLElBQUksRUFBRSxHQUFHLENBQUMsRUFBRSxDQUFDLFdBQVcsRUFBRSxTQUFTLENBQUMsRUFBRSxDQUFDLENBQUM7UUFDMUMsQ0FBQyxDQUFDLFdBQVcsRUFBRSxTQUFTLENBQUMsRUFBRSxDQUFDLEVBQUUsQ0FBQyxDQUFDO1FBQ2hDO1lBQ0UsQ0FBQyxJQUFJLEVBQUUsSUFBSSxFQUFFLElBQUksRUFBRSxJQUFJLEVBQUUsSUFBSSxFQUFFLElBQUksRUFBRSxHQUFHLENBQUMsRUFBRSxDQUFDLE1BQU0sRUFBRSxLQUFLLEVBQUUsTUFBTSxFQUFFLEtBQUssRUFBRSxNQUFNLEVBQUUsT0FBTyxFQUFFLEtBQUssQ0FBQztZQUNqRyxDQUFDLFNBQVMsRUFBRSxRQUFRLEVBQUUsU0FBUyxFQUFFLFFBQVEsRUFBRSxTQUFTLEVBQUUsVUFBVSxFQUFFLFFBQVEsQ0FBQztZQUMzRSxDQUFDLE1BQU0sRUFBRSxLQUFLLEVBQUUsTUFBTSxFQUFFLEtBQUssRUFBRSxNQUFNLEVBQUUsT0FBTyxFQUFFLEtBQUssQ0FBQztTQUN2RDtRQUNELENBQUM7UUFDRDtZQUNFLENBQUMsR0FBRyxFQUFFLElBQUksRUFBRSxJQUFJLEVBQUUsR0FBRyxFQUFFLElBQUksRUFBRSxJQUFJLEVBQUUsSUFBSSxFQUFFLEdBQUcsRUFBRSxJQUFJLEVBQUUsR0FBRyxFQUFFLEdBQUcsRUFBRSxJQUFJLENBQUM7WUFDbkUsQ0FBQyxPQUFPLEVBQUUsVUFBVSxFQUFFLFFBQVEsRUFBRSxPQUFPLEVBQUUsSUFBSSxFQUFFLE1BQU0sRUFBRSxNQUFNLEVBQUUsSUFBSSxFQUFFLFNBQVMsRUFBRSxPQUFPLEVBQUUsTUFBTSxFQUFFLE9BQU8sQ0FBQztZQUN6RztnQkFDRSxPQUFPLEVBQUUsVUFBVSxFQUFFLFFBQVEsRUFBRSxTQUFTLEVBQUUsSUFBSSxFQUFFLE1BQU0sRUFBRSxNQUFNLEVBQUUsUUFBUSxFQUFFLFlBQVksRUFBRSxVQUFVLEVBQUUsU0FBUztnQkFDN0csVUFBVTthQUNYO1NBQ0Y7UUFDRDtZQUNFLENBQUMsR0FBRyxFQUFFLElBQUksRUFBRSxJQUFJLEVBQUUsR0FBRyxFQUFFLElBQUksRUFBRSxJQUFJLEVBQUUsSUFBSSxFQUFFLEdBQUcsRUFBRSxJQUFJLEVBQUUsR0FBRyxFQUFFLEdBQUcsRUFBRSxJQUFJLENBQUM7WUFDbkUsQ0FBQyxJQUFJLEVBQUUsT0FBTyxFQUFFLFFBQVEsRUFBRSxPQUFPLEVBQUUsSUFBSSxFQUFFLE1BQU0sRUFBRSxNQUFNLEVBQUUsSUFBSSxFQUFFLFNBQVMsRUFBRSxPQUFPLEVBQUUsTUFBTSxFQUFFLE9BQU8sQ0FBQztZQUNuRztnQkFDRSxPQUFPLEVBQUUsVUFBVSxFQUFFLFFBQVEsRUFBRSxTQUFTLEVBQUUsSUFBSSxFQUFFLE1BQU0sRUFBRSxNQUFNLEVBQUUsUUFBUSxFQUFFLFlBQVksRUFBRSxVQUFVLEVBQUUsU0FBUztnQkFDN0csVUFBVTthQUNYO1NBQ0Y7UUFDRCxDQUFDLENBQUMsU0FBUyxFQUFFLFFBQVEsQ0FBQyxFQUFFLENBQUMsRUFBRSxDQUFDLGVBQWUsRUFBRSxZQUFZLENBQUMsQ0FBQztRQUMzRCxDQUFDO1FBQ0QsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDO1FBQ04sQ0FBQyxRQUFRLEVBQUUsVUFBVSxFQUFFLFdBQVcsRUFBRSxpQkFBaUIsQ0FBQztRQUN0RCxDQUFDLFNBQVMsRUFBRSxZQUFZLEVBQUUsY0FBYyxFQUFFLGlCQUFpQixDQUFDO1FBQzVELENBQUMsU0FBUyxFQUFFLENBQUMsRUFBRSxDQUFDLEVBQUUsQ0FBQyxDQUFDO1FBQ3BCLENBQUMsR0FBRyxFQUFFLEdBQUcsRUFBRSxHQUFHLEVBQUUsR0FBRyxFQUFFLEdBQUcsRUFBRSxHQUFHLEVBQUUsR0FBRyxFQUFFLEdBQUcsRUFBRSxHQUFHLEVBQUUsR0FBRyxFQUFFLEtBQUssRUFBRSxHQUFHLENBQUM7UUFDOUQsQ0FBQyxXQUFXLEVBQUUsUUFBUSxFQUFFLFdBQVcsRUFBRSxLQUFLLENBQUM7UUFDM0MsS0FBSztRQUNMLEdBQUc7UUFDSCxlQUFlO1FBQ2YsRUFBQyxLQUFLLEVBQUUsQ0FBQyxLQUFLLEVBQUUsR0FBRyxDQUFDLEVBQUUsS0FBSyxFQUFFLENBQUMsQ0FBQyxFQUFFLElBQUksQ0FBQyxFQUFFLEtBQUssRUFBRSxDQUFDLEdBQUcsQ0FBQyxFQUFFLEtBQUssRUFBRSxDQUFDLEtBQUssQ0FBQyxFQUFDO1FBQ3JFLEtBQUs7UUFDTCxNQUFNO0tBQ1AsQ0FBQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogQGxpY2Vuc2VcbiAqIENvcHlyaWdodCBHb29nbGUgTExDIEFsbCBSaWdodHMgUmVzZXJ2ZWQuXG4gKlxuICogVXNlIG9mIHRoaXMgc291cmNlIGNvZGUgaXMgZ292ZXJuZWQgYnkgYW4gTUlULXN0eWxlIGxpY2Vuc2UgdGhhdCBjYW4gYmVcbiAqIGZvdW5kIGluIHRoZSBMSUNFTlNFIGZpbGUgYXQgaHR0cHM6Ly9hbmd1bGFyLmlvL2xpY2Vuc2VcbiAqL1xuXG4vLyBUSElTIENPREUgSVMgR0VORVJBVEVEIC0gRE8gTk9UIE1PRElGWVxuLy8gU2VlIGFuZ3VsYXIvdG9vbHMvZ3VscC10YXNrcy9jbGRyL2V4dHJhY3QuanNcblxuY29uc3QgdSA9IHVuZGVmaW5lZDtcblxuZnVuY3Rpb24gcGx1cmFsKG46IG51bWJlcik6IG51bWJlciB7XG4gIGxldCBpID0gTWF0aC5mbG9vcihNYXRoLmFicyhuKSk7XG4gIGlmIChpID09PSAwIHx8IG4gPT09IDEpIHJldHVybiAxO1xuICByZXR1cm4gNTtcbn1cblxuZXhwb3J0IGRlZmF1bHQgW1xuICAna24nLFxuICBbWyfgsqrgs4InLCAn4LKFJ10sIFsn4LKq4LOC4LKw4LON4LK14LK+4LK54LON4LKoJywgJ+CyheCyquCysOCyvuCyueCzjeCyqCddLCB1XSxcbiAgW1sn4LKq4LOC4LKw4LON4LK14LK+4LK54LON4LKoJywgJ+CyheCyquCysOCyvuCyueCzjeCyqCddLCB1LCB1XSxcbiAgW1xuICAgIFsn4LKt4LK+JywgJ+CyuOCziycsICfgsq7gsoInLCAn4LKs4LOBJywgJ+Cyl+CzgScsICfgsrbgs4EnLCAn4LK2J10sIFsn4LKt4LK+4LKo4LOBJywgJ+CyuOCzi+CyricsICfgsq7gsoLgspfgsrMnLCAn4LKs4LOB4LKnJywgJ+Cyl+CzgeCysOCzgScsICfgsrbgs4HgspXgs43gsrAnLCAn4LK24LKo4LK/J10sXG4gICAgWyfgsq3gsr7gsqjgs4HgsrXgsr7gsrAnLCAn4LK44LOL4LKu4LK14LK+4LKwJywgJ+CyruCyguCyl+Cys+CyteCyvuCysCcsICfgsqzgs4HgsqfgsrXgsr7gsrAnLCAn4LKX4LOB4LKw4LOB4LK14LK+4LKwJywgJ+CytuCzgeCyleCzjeCysOCyteCyvuCysCcsICfgsrbgsqjgsr/gsrXgsr7gsrAnXSxcbiAgICBbJ+CyreCyvuCyqOCzgScsICfgsrjgs4vgsq4nLCAn4LKu4LKC4LKX4LKzJywgJ+CyrOCzgeCypycsICfgspfgs4HgsrDgs4EnLCAn4LK24LOB4LKV4LON4LKwJywgJ+CytuCyqOCyvyddXG4gIF0sXG4gIHUsXG4gIFtcbiAgICBbJ+CynCcsICfgsqvgs4YnLCAn4LKu4LK+JywgJ+CyjycsICfgsq7gs4cnLCAn4LKc4LOCJywgJ+CynOCzgScsICfgsoYnLCAn4LK44LOGJywgJ+CyhScsICfgsqgnLCAn4LKh4LK/J10sXG4gICAgWyfgspzgsqjgsrXgsrDgsr8nLCAn4LKr4LOG4LKs4LON4LKw4LK14LKw4LK/JywgJ+CyruCyvuCysOCzjeCymuCzjScsICfgso/gsqrgs43gsrDgsr8nLCAn4LKu4LOHJywgJ+CynOCzguCyqOCzjScsICfgspzgs4HgsrLgs4gnLCAn4LKG4LKXJywgJ+CyuOCzhuCyquCzjeCyn+CzhuCygicsICfgsoXgspXgs43gsp/gs4snLCAn4LKo4LK14LOG4LKCJywgJ+CyoeCyv+CyuOCzhuCygiddLFxuICAgIFtcbiAgICAgICfgspzgsqjgsrXgsrDgsr8nLCAn4LKr4LOG4LKs4LON4LKw4LK14LKw4LK/JywgJ+CyruCyvuCysOCzjeCymuCzjScsICfgso/gsqrgs43gsrDgsr/gsrLgs40nLCAn4LKu4LOHJywgJ+CynOCzguCyqOCzjScsICfgspzgs4HgsrLgs4gnLCAn4LKG4LKX4LK44LON4LKf4LONJywgJ+CyuOCzhuCyquCzjeCyn+CzhuCyguCyrOCysOCzjScsICfgsoXgspXgs43gsp/gs4vgsqzgsrDgs40nLCAn4LKo4LK14LOG4LKC4LKs4LKw4LONJyxcbiAgICAgICfgsqHgsr/gsrjgs4bgsoLgsqzgsrDgs40nXG4gICAgXVxuICBdLFxuICBbXG4gICAgWyfgspwnLCAn4LKr4LOGJywgJ+CyruCyvicsICfgso8nLCAn4LKu4LOHJywgJ+CynOCzgicsICfgspzgs4EnLCAn4LKGJywgJ+CyuOCzhicsICfgsoUnLCAn4LKoJywgJ+CyoeCyvyddLFxuICAgIFsn4LKc4LKoJywgJ+Cyq+CzhuCyrOCzjeCysCcsICfgsq7gsr7gsrDgs43gsprgs40nLCAn4LKP4LKq4LON4LKw4LK/JywgJ+CyruCzhycsICfgspzgs4Lgsqjgs40nLCAn4LKc4LOB4LKy4LOIJywgJ+CyhuCylycsICfgsrjgs4bgsqrgs43gsp/gs4bgsoInLCAn4LKF4LKV4LON4LKf4LOLJywgJ+CyqOCyteCzhuCygicsICfgsqHgsr/gsrjgs4bgsoInXSxcbiAgICBbXG4gICAgICAn4LKc4LKo4LK14LKw4LK/JywgJ+Cyq+CzhuCyrOCzjeCysOCyteCysOCyvycsICfgsq7gsr7gsrDgs43gsprgs40nLCAn4LKP4LKq4LON4LKw4LK/4LKy4LONJywgJ+CyruCzhycsICfgspzgs4Lgsqjgs40nLCAn4LKc4LOB4LKy4LOIJywgJ+CyhuCyl+CyuOCzjeCyn+CzjScsICfgsrjgs4bgsqrgs43gsp/gs4bgsoLgsqzgsrDgs40nLCAn4LKF4LKV4LON4LKf4LOL4LKs4LKw4LONJywgJ+CyqOCyteCzhuCyguCyrOCysOCzjScsXG4gICAgICAn4LKh4LK/4LK44LOG4LKC4LKs4LKw4LONJ1xuICAgIF1cbiAgXSxcbiAgW1sn4LKV4LON4LKw4LK/LuCyquCzgicsICfgspXgs43gsrDgsr8u4LK2J10sIHUsIFsn4LKV4LON4LKw4LK/4LK44LON4LKkIOCyquCzguCysOCzjeCytScsICfgspXgs43gsrDgsr/gsrjgs43gsqQg4LK24LKVJ11dLFxuICAwLFxuICBbMCwgMF0sXG4gIFsnZC9NL3l5JywgJ01NTSBkLCB5JywgJ01NTU0gZCwgeScsICdFRUVFLCBNTU1NIGQsIHknXSxcbiAgWydoaDptbSBhJywgJ2hoOm1tOnNzIGEnLCAnaGg6bW06c3MgYSB6JywgJ2hoOm1tOnNzIGEgenp6eiddLFxuICBbJ3sxfSB7MH0nLCB1LCB1LCB1XSxcbiAgWycuJywgJywnLCAnOycsICclJywgJysnLCAnLScsICdFJywgJ8OXJywgJ+KAsCcsICfiiJ4nLCAnTmFOJywgJzonXSxcbiAgWycjLCMjMC4jIyMnLCAnIywjIzAlJywgJ8KkIywjIzAuMDAnLCAnI0UwJ10sXG4gICdJTlInLFxuICAn4oK5JyxcbiAgJ+CyreCyvuCysOCypOCzgOCyryDgsrDgs4Lgsqrgsr7gsq/gsr8nLFxuICB7J0pQWSc6IFsnSlDCpScsICfCpSddLCAnUk9OJzogW3UsICfgsrLgs4AnXSwgJ1RIQic6IFsn4Li/J10sICdUV0QnOiBbJ05UJCddfSxcbiAgJ2x0cicsXG4gIHBsdXJhbFxuXTtcbiJdfQ==