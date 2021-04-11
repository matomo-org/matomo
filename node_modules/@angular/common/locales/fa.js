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
        define("@angular/common/locales/fa", ["require", "exports"], factory);
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
        'fa',
        [['ق', 'ب'], ['ق.ظ.', 'ب.ظ.'], ['قبل\u200cازظهر', 'بعدازظهر']],
        u,
        [
            ['ی', 'د', 'س', 'چ', 'پ', 'ج', 'ش'],
            ['یکشنبه', 'دوشنبه', 'سه\u200cشنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه'], u,
            ['۱ش', '۲ش', '۳ش', '۴ش', '۵ش', 'ج', 'ش']
        ],
        u,
        [
            ['ژ', 'ف', 'م', 'آ', 'م', 'ژ', 'ژ', 'ا', 'س', 'ا', 'ن', 'د'],
            [
                'ژانویهٔ', 'فوریهٔ', 'مارس', 'آوریل', 'مهٔ', 'ژوئن', 'ژوئیهٔ', 'اوت', 'سپتامبر', 'اکتبر',
                'نوامبر', 'دسامبر'
            ],
            u
        ],
        [
            ['ژ', 'ف', 'م', 'آ', 'م', 'ژ', 'ژ', 'ا', 'س', 'ا', 'ن', 'د'],
            [
                'ژانویه', 'فوریه', 'مارس', 'آوریل', 'مه', 'ژوئن', 'ژوئیه', 'اوت', 'سپتامبر', 'اکتبر',
                'نوامبر', 'دسامبر'
            ],
            u
        ],
        [['ق', 'م'], ['ق.م.', 'م.'], ['قبل از میلاد', 'میلادی']],
        6,
        [5, 5],
        ['y/M/d', 'd MMM y', 'd MMMM y', 'EEEE d MMMM y'],
        ['H:mm', 'H:mm:ss', 'H:mm:ss (z)', 'H:mm:ss (zzzz)'],
        ['{1}،\u200f {0}', u, '{1}، ساعت {0}', u],
        ['.', ',', ';', '%', '\u200e+', '\u200e−', 'E', '×', '‰', '∞', 'ناعدد', ':'],
        ['#,##0.###', '#,##0%', '\u200e¤ #,##0.00', '#E0'],
        'IRR',
        'ریال',
        'ریال ایران',
        {
            'AFN': ['؋'],
            'CAD': ['$CA', '$'],
            'CNY': ['¥CN', '¥'],
            'HKD': ['$HK', '$'],
            'IRR': ['ریال'],
            'MXN': ['$MX', '$'],
            'NZD': ['$NZ', '$'],
            'THB': ['฿'],
            'XCD': ['$EC', '$']
        },
        'rtl',
        plural
    ];
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZmEuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb21tb24vbG9jYWxlcy9mYS50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7OztJQUVILHlDQUF5QztJQUN6QywrQ0FBK0M7SUFFL0MsSUFBTSxDQUFDLEdBQUcsU0FBUyxDQUFDO0lBRXBCLFNBQVMsTUFBTSxDQUFDLENBQVM7UUFDdkIsSUFBSSxDQUFDLEdBQUcsSUFBSSxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFDaEMsSUFBSSxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDO1lBQUUsT0FBTyxDQUFDLENBQUM7UUFDakMsT0FBTyxDQUFDLENBQUM7SUFDWCxDQUFDO0lBRUQsa0JBQWU7UUFDYixJQUFJO1FBQ0osQ0FBQyxDQUFDLEdBQUcsRUFBRSxHQUFHLENBQUMsRUFBRSxDQUFDLE1BQU0sRUFBRSxNQUFNLENBQUMsRUFBRSxDQUFDLGdCQUFnQixFQUFFLFVBQVUsQ0FBQyxDQUFDO1FBQzlELENBQUM7UUFDRDtZQUNFLENBQUMsR0FBRyxFQUFFLEdBQUcsRUFBRSxHQUFHLEVBQUUsR0FBRyxFQUFFLEdBQUcsRUFBRSxHQUFHLEVBQUUsR0FBRyxDQUFDO1lBQ25DLENBQUMsUUFBUSxFQUFFLFFBQVEsRUFBRSxjQUFjLEVBQUUsVUFBVSxFQUFFLFNBQVMsRUFBRSxNQUFNLEVBQUUsTUFBTSxDQUFDLEVBQUUsQ0FBQztZQUM5RSxDQUFDLElBQUksRUFBRSxJQUFJLEVBQUUsSUFBSSxFQUFFLElBQUksRUFBRSxJQUFJLEVBQUUsR0FBRyxFQUFFLEdBQUcsQ0FBQztTQUN6QztRQUNELENBQUM7UUFDRDtZQUNFLENBQUMsR0FBRyxFQUFFLEdBQUcsRUFBRSxHQUFHLEVBQUUsR0FBRyxFQUFFLEdBQUcsRUFBRSxHQUFHLEVBQUUsR0FBRyxFQUFFLEdBQUcsRUFBRSxHQUFHLEVBQUUsR0FBRyxFQUFFLEdBQUcsRUFBRSxHQUFHLENBQUM7WUFDNUQ7Z0JBQ0UsU0FBUyxFQUFFLFFBQVEsRUFBRSxNQUFNLEVBQUUsT0FBTyxFQUFFLEtBQUssRUFBRSxNQUFNLEVBQUUsUUFBUSxFQUFFLEtBQUssRUFBRSxTQUFTLEVBQUUsT0FBTztnQkFDeEYsUUFBUSxFQUFFLFFBQVE7YUFDbkI7WUFDRCxDQUFDO1NBQ0Y7UUFDRDtZQUNFLENBQUMsR0FBRyxFQUFFLEdBQUcsRUFBRSxHQUFHLEVBQUUsR0FBRyxFQUFFLEdBQUcsRUFBRSxHQUFHLEVBQUUsR0FBRyxFQUFFLEdBQUcsRUFBRSxHQUFHLEVBQUUsR0FBRyxFQUFFLEdBQUcsRUFBRSxHQUFHLENBQUM7WUFDNUQ7Z0JBQ0UsUUFBUSxFQUFFLE9BQU8sRUFBRSxNQUFNLEVBQUUsT0FBTyxFQUFFLElBQUksRUFBRSxNQUFNLEVBQUUsT0FBTyxFQUFFLEtBQUssRUFBRSxTQUFTLEVBQUUsT0FBTztnQkFDcEYsUUFBUSxFQUFFLFFBQVE7YUFDbkI7WUFDRCxDQUFDO1NBQ0Y7UUFDRCxDQUFDLENBQUMsR0FBRyxFQUFFLEdBQUcsQ0FBQyxFQUFFLENBQUMsTUFBTSxFQUFFLElBQUksQ0FBQyxFQUFFLENBQUMsY0FBYyxFQUFFLFFBQVEsQ0FBQyxDQUFDO1FBQ3hELENBQUM7UUFDRCxDQUFDLENBQUMsRUFBRSxDQUFDLENBQUM7UUFDTixDQUFDLE9BQU8sRUFBRSxTQUFTLEVBQUUsVUFBVSxFQUFFLGVBQWUsQ0FBQztRQUNqRCxDQUFDLE1BQU0sRUFBRSxTQUFTLEVBQUUsYUFBYSxFQUFFLGdCQUFnQixDQUFDO1FBQ3BELENBQUMsZ0JBQWdCLEVBQUUsQ0FBQyxFQUFFLGVBQWUsRUFBRSxDQUFDLENBQUM7UUFDekMsQ0FBQyxHQUFHLEVBQUUsR0FBRyxFQUFFLEdBQUcsRUFBRSxHQUFHLEVBQUUsU0FBUyxFQUFFLFNBQVMsRUFBRSxHQUFHLEVBQUUsR0FBRyxFQUFFLEdBQUcsRUFBRSxHQUFHLEVBQUUsT0FBTyxFQUFFLEdBQUcsQ0FBQztRQUM1RSxDQUFDLFdBQVcsRUFBRSxRQUFRLEVBQUUsa0JBQWtCLEVBQUUsS0FBSyxDQUFDO1FBQ2xELEtBQUs7UUFDTCxNQUFNO1FBQ04sWUFBWTtRQUNaO1lBQ0UsS0FBSyxFQUFFLENBQUMsR0FBRyxDQUFDO1lBQ1osS0FBSyxFQUFFLENBQUMsS0FBSyxFQUFFLEdBQUcsQ0FBQztZQUNuQixLQUFLLEVBQUUsQ0FBQyxLQUFLLEVBQUUsR0FBRyxDQUFDO1lBQ25CLEtBQUssRUFBRSxDQUFDLEtBQUssRUFBRSxHQUFHLENBQUM7WUFDbkIsS0FBSyxFQUFFLENBQUMsTUFBTSxDQUFDO1lBQ2YsS0FBSyxFQUFFLENBQUMsS0FBSyxFQUFFLEdBQUcsQ0FBQztZQUNuQixLQUFLLEVBQUUsQ0FBQyxLQUFLLEVBQUUsR0FBRyxDQUFDO1lBQ25CLEtBQUssRUFBRSxDQUFDLEdBQUcsQ0FBQztZQUNaLEtBQUssRUFBRSxDQUFDLEtBQUssRUFBRSxHQUFHLENBQUM7U0FDcEI7UUFDRCxLQUFLO1FBQ0wsTUFBTTtLQUNQLENBQUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuLy8gVEhJUyBDT0RFIElTIEdFTkVSQVRFRCAtIERPIE5PVCBNT0RJRllcbi8vIFNlZSBhbmd1bGFyL3Rvb2xzL2d1bHAtdGFza3MvY2xkci9leHRyYWN0LmpzXG5cbmNvbnN0IHUgPSB1bmRlZmluZWQ7XG5cbmZ1bmN0aW9uIHBsdXJhbChuOiBudW1iZXIpOiBudW1iZXIge1xuICBsZXQgaSA9IE1hdGguZmxvb3IoTWF0aC5hYnMobikpO1xuICBpZiAoaSA9PT0gMCB8fCBuID09PSAxKSByZXR1cm4gMTtcbiAgcmV0dXJuIDU7XG59XG5cbmV4cG9ydCBkZWZhdWx0IFtcbiAgJ2ZhJyxcbiAgW1sn2YInLCAn2KgnXSwgWyfZgi7YuC4nLCAn2Kgu2LguJ10sIFsn2YLYqNmEXFx1MjAwY9in2LLYuNmH2LEnLCAn2KjYudiv2KfYsti42YfYsSddXSxcbiAgdSxcbiAgW1xuICAgIFsn24wnLCAn2K8nLCAn2LMnLCAn2oYnLCAn2b4nLCAn2KwnLCAn2LQnXSxcbiAgICBbJ9uM2qnYtNmG2KjZhycsICfYr9mI2LTZhtio2YcnLCAn2LPZh1xcdTIwMGPYtNmG2KjZhycsICfahtmH2KfYsdi02YbYqNmHJywgJ9m+2YbYrNi02YbYqNmHJywgJ9is2YXYudmHJywgJ9i02YbYqNmHJ10sIHUsXG4gICAgWyfbsdi0JywgJ9uy2LQnLCAn27PYtCcsICfbtNi0JywgJ9u12LQnLCAn2KwnLCAn2LQnXVxuICBdLFxuICB1LFxuICBbXG4gICAgWyfamCcsICfZgScsICfZhScsICfYoicsICfZhScsICfamCcsICfamCcsICfYpycsICfYsycsICfYpycsICfZhicsICfYryddLFxuICAgIFtcbiAgICAgICfamNin2YbZiNuM2YfZlCcsICfZgdmI2LHbjNmH2ZQnLCAn2YXYp9ix2LMnLCAn2KLZiNix24zZhCcsICfZhdmH2ZQnLCAn2pjZiNim2YYnLCAn2pjZiNim24zZh9mUJywgJ9in2YjYqicsICfYs9m+2KrYp9mF2KjYsScsICfYp9qp2KrYqNixJyxcbiAgICAgICfZhtmI2KfZhdio2LEnLCAn2K/Ys9in2YXYqNixJ1xuICAgIF0sXG4gICAgdVxuICBdLFxuICBbXG4gICAgWyfamCcsICfZgScsICfZhScsICfYoicsICfZhScsICfamCcsICfamCcsICfYpycsICfYsycsICfYpycsICfZhicsICfYryddLFxuICAgIFtcbiAgICAgICfamNin2YbZiNuM2YcnLCAn2YHZiNix24zZhycsICfZhdin2LHYsycsICfYotmI2LHbjNmEJywgJ9mF2YcnLCAn2pjZiNim2YYnLCAn2pjZiNim24zZhycsICfYp9mI2KonLCAn2LPZvtiq2KfZhdio2LEnLCAn2Kfaqdiq2KjYsScsXG4gICAgICAn2YbZiNin2YXYqNixJywgJ9iv2LPYp9mF2KjYsSdcbiAgICBdLFxuICAgIHVcbiAgXSxcbiAgW1sn2YInLCAn2YUnXSwgWyfZgi7ZhS4nLCAn2YUuJ10sIFsn2YLYqNmEINin2LIg2YXbjNmE2KfYrycsICfZhduM2YTYp9iv24wnXV0sXG4gIDYsXG4gIFs1LCA1XSxcbiAgWyd5L00vZCcsICdkIE1NTSB5JywgJ2QgTU1NTSB5JywgJ0VFRUUgZCBNTU1NIHknXSxcbiAgWydIOm1tJywgJ0g6bW06c3MnLCAnSDptbTpzcyAoeiknLCAnSDptbTpzcyAoenp6eiknXSxcbiAgWyd7MX3YjFxcdTIwMGYgezB9JywgdSwgJ3sxfdiMINiz2KfYudiqIHswfScsIHVdLFxuICBbJy4nLCAnLCcsICc7JywgJyUnLCAnXFx1MjAwZSsnLCAnXFx1MjAwZeKIkicsICdFJywgJ8OXJywgJ+KAsCcsICfiiJ4nLCAn2YbYp9i52K/YrycsICc6J10sXG4gIFsnIywjIzAuIyMjJywgJyMsIyMwJScsICdcXHUyMDBlwqTCoCMsIyMwLjAwJywgJyNFMCddLFxuICAnSVJSJyxcbiAgJ9ix24zYp9mEJyxcbiAgJ9ix24zYp9mEINin24zYsdin2YYnLFxuICB7XG4gICAgJ0FGTic6IFsn2IsnXSxcbiAgICAnQ0FEJzogWyckQ0EnLCAnJCddLFxuICAgICdDTlknOiBbJ8KlQ04nLCAnwqUnXSxcbiAgICAnSEtEJzogWyckSEsnLCAnJCddLFxuICAgICdJUlInOiBbJ9ix24zYp9mEJ10sXG4gICAgJ01YTic6IFsnJE1YJywgJyQnXSxcbiAgICAnTlpEJzogWyckTlonLCAnJCddLFxuICAgICdUSEInOiBbJ+C4vyddLFxuICAgICdYQ0QnOiBbJyRFQycsICckJ11cbiAgfSxcbiAgJ3J0bCcsXG4gIHBsdXJhbFxuXTtcbiJdfQ==