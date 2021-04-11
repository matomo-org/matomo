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
        define("@angular/common/locales/gu", ["require", "exports"], factory);
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
        'gu',
        [['AM', 'PM'], u, u],
        u,
        [
            ['ર', 'સો', 'મં', 'બુ', 'ગુ', 'શુ', 'શ'], ['રવિ', 'સોમ', 'મંગળ', 'બુધ', 'ગુરુ', 'શુક્ર', 'શનિ'],
            ['રવિવાર', 'સોમવાર', 'મંગળવાર', 'બુધવાર', 'ગુરુવાર', 'શુક્રવાર', 'શનિવાર'],
            ['ર', 'સો', 'મં', 'બુ', 'ગુ', 'શુ', 'શ']
        ],
        u,
        [
            ['જા', 'ફે', 'મા', 'એ', 'મે', 'જૂ', 'જુ', 'ઑ', 'સ', 'ઑ', 'ન', 'ડિ'],
            ['જાન્યુ', 'ફેબ્રુ', 'માર્ચ', 'એપ્રિલ', 'મે', 'જૂન', 'જુલાઈ', 'ઑગસ્ટ', 'સપ્ટે', 'ઑક્ટો', 'નવે', 'ડિસે'],
            [
                'જાન્યુઆરી', 'ફેબ્રુઆરી', 'માર્ચ', 'એપ્રિલ', 'મે', 'જૂન', 'જુલાઈ', 'ઑગસ્ટ', 'સપ્ટેમ્બર', 'ઑક્ટોબર', 'નવેમ્બર',
                'ડિસેમ્બર'
            ]
        ],
        u,
        [['ઇ સ પુ', 'ઇસ'], ['ઈ.સ.પૂર્વે', 'ઈ.સ.'], ['ઈસવીસન પૂર્વે', 'ઇસવીસન']],
        0,
        [0, 0],
        ['d/M/yy', 'd MMM, y', 'd MMMM, y', 'EEEE, d MMMM, y'],
        ['hh:mm a', 'hh:mm:ss a', 'hh:mm:ss a z', 'hh:mm:ss a zzzz'],
        ['{1} {0}', u, '{1} એ {0} વાગ્યે', u],
        ['.', ',', ';', '%', '+', '-', 'E', '×', '‰', '∞', 'NaN', ':'],
        ['#,##,##0.###', '#,##,##0%', '¤#,##,##0.00', '[#E0]'],
        'INR',
        '₹',
        'ભારતીય રૂપિયા',
        { 'JPY': ['JP¥', '¥'], 'MUR': [u, 'રૂ.'], 'THB': ['฿'], 'TWD': ['NT$'], 'USD': ['US$', '$'] },
        'ltr',
        plural
    ];
});
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZ3UuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb21tb24vbG9jYWxlcy9ndS50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7Ozs7Ozs7Ozs7OztJQUVILHlDQUF5QztJQUN6QywrQ0FBK0M7SUFFL0MsSUFBTSxDQUFDLEdBQUcsU0FBUyxDQUFDO0lBRXBCLFNBQVMsTUFBTSxDQUFDLENBQVM7UUFDdkIsSUFBSSxDQUFDLEdBQUcsSUFBSSxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUM7UUFDaEMsSUFBSSxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsS0FBSyxDQUFDO1lBQUUsT0FBTyxDQUFDLENBQUM7UUFDakMsT0FBTyxDQUFDLENBQUM7SUFDWCxDQUFDO0lBRUQsa0JBQWU7UUFDYixJQUFJO1FBQ0osQ0FBQyxDQUFDLElBQUksRUFBRSxJQUFJLENBQUMsRUFBRSxDQUFDLEVBQUUsQ0FBQyxDQUFDO1FBQ3BCLENBQUM7UUFDRDtZQUNFLENBQUMsR0FBRyxFQUFFLElBQUksRUFBRSxJQUFJLEVBQUUsSUFBSSxFQUFFLElBQUksRUFBRSxJQUFJLEVBQUUsR0FBRyxDQUFDLEVBQUUsQ0FBQyxLQUFLLEVBQUUsS0FBSyxFQUFFLE1BQU0sRUFBRSxLQUFLLEVBQUUsTUFBTSxFQUFFLE9BQU8sRUFBRSxLQUFLLENBQUM7WUFDL0YsQ0FBQyxRQUFRLEVBQUUsUUFBUSxFQUFFLFNBQVMsRUFBRSxRQUFRLEVBQUUsU0FBUyxFQUFFLFVBQVUsRUFBRSxRQUFRLENBQUM7WUFDMUUsQ0FBQyxHQUFHLEVBQUUsSUFBSSxFQUFFLElBQUksRUFBRSxJQUFJLEVBQUUsSUFBSSxFQUFFLElBQUksRUFBRSxHQUFHLENBQUM7U0FDekM7UUFDRCxDQUFDO1FBQ0Q7WUFDRSxDQUFDLElBQUksRUFBRSxJQUFJLEVBQUUsSUFBSSxFQUFFLEdBQUcsRUFBRSxJQUFJLEVBQUUsSUFBSSxFQUFFLElBQUksRUFBRSxHQUFHLEVBQUUsR0FBRyxFQUFFLEdBQUcsRUFBRSxHQUFHLEVBQUUsSUFBSSxDQUFDO1lBQ25FLENBQUMsUUFBUSxFQUFFLFFBQVEsRUFBRSxPQUFPLEVBQUUsUUFBUSxFQUFFLElBQUksRUFBRSxLQUFLLEVBQUUsT0FBTyxFQUFFLE9BQU8sRUFBRSxPQUFPLEVBQUUsT0FBTyxFQUFFLEtBQUssRUFBRSxNQUFNLENBQUM7WUFDdkc7Z0JBQ0UsV0FBVyxFQUFFLFdBQVcsRUFBRSxPQUFPLEVBQUUsUUFBUSxFQUFFLElBQUksRUFBRSxLQUFLLEVBQUUsT0FBTyxFQUFFLE9BQU8sRUFBRSxXQUFXLEVBQUUsU0FBUyxFQUFFLFNBQVM7Z0JBQzdHLFVBQVU7YUFDWDtTQUNGO1FBQ0QsQ0FBQztRQUNELENBQUMsQ0FBQyxRQUFRLEVBQUUsSUFBSSxDQUFDLEVBQUUsQ0FBQyxZQUFZLEVBQUUsTUFBTSxDQUFDLEVBQUUsQ0FBQyxlQUFlLEVBQUUsUUFBUSxDQUFDLENBQUM7UUFDdkUsQ0FBQztRQUNELENBQUMsQ0FBQyxFQUFFLENBQUMsQ0FBQztRQUNOLENBQUMsUUFBUSxFQUFFLFVBQVUsRUFBRSxXQUFXLEVBQUUsaUJBQWlCLENBQUM7UUFDdEQsQ0FBQyxTQUFTLEVBQUUsWUFBWSxFQUFFLGNBQWMsRUFBRSxpQkFBaUIsQ0FBQztRQUM1RCxDQUFDLFNBQVMsRUFBRSxDQUFDLEVBQUUsa0JBQWtCLEVBQUUsQ0FBQyxDQUFDO1FBQ3JDLENBQUMsR0FBRyxFQUFFLEdBQUcsRUFBRSxHQUFHLEVBQUUsR0FBRyxFQUFFLEdBQUcsRUFBRSxHQUFHLEVBQUUsR0FBRyxFQUFFLEdBQUcsRUFBRSxHQUFHLEVBQUUsR0FBRyxFQUFFLEtBQUssRUFBRSxHQUFHLENBQUM7UUFDOUQsQ0FBQyxjQUFjLEVBQUUsV0FBVyxFQUFFLGNBQWMsRUFBRSxPQUFPLENBQUM7UUFDdEQsS0FBSztRQUNMLEdBQUc7UUFDSCxlQUFlO1FBQ2YsRUFBQyxLQUFLLEVBQUUsQ0FBQyxLQUFLLEVBQUUsR0FBRyxDQUFDLEVBQUUsS0FBSyxFQUFFLENBQUMsQ0FBQyxFQUFFLEtBQUssQ0FBQyxFQUFFLEtBQUssRUFBRSxDQUFDLEdBQUcsQ0FBQyxFQUFFLEtBQUssRUFBRSxDQUFDLEtBQUssQ0FBQyxFQUFFLEtBQUssRUFBRSxDQUFDLEtBQUssRUFBRSxHQUFHLENBQUMsRUFBQztRQUMzRixLQUFLO1FBQ0wsTUFBTTtLQUNQLENBQUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuLy8gVEhJUyBDT0RFIElTIEdFTkVSQVRFRCAtIERPIE5PVCBNT0RJRllcbi8vIFNlZSBhbmd1bGFyL3Rvb2xzL2d1bHAtdGFza3MvY2xkci9leHRyYWN0LmpzXG5cbmNvbnN0IHUgPSB1bmRlZmluZWQ7XG5cbmZ1bmN0aW9uIHBsdXJhbChuOiBudW1iZXIpOiBudW1iZXIge1xuICBsZXQgaSA9IE1hdGguZmxvb3IoTWF0aC5hYnMobikpO1xuICBpZiAoaSA9PT0gMCB8fCBuID09PSAxKSByZXR1cm4gMTtcbiAgcmV0dXJuIDU7XG59XG5cbmV4cG9ydCBkZWZhdWx0IFtcbiAgJ2d1JyxcbiAgW1snQU0nLCAnUE0nXSwgdSwgdV0sXG4gIHUsXG4gIFtcbiAgICBbJ+CqsCcsICfgqrjgq4snLCAn4Kqu4KqCJywgJ+CqrOCrgScsICfgqpfgq4EnLCAn4Kq24KuBJywgJ+CqtiddLCBbJ+CqsOCqteCqvycsICfgqrjgq4vgqq4nLCAn4Kqu4KqC4KqX4KqzJywgJ+CqrOCrgeCqpycsICfgqpfgq4HgqrDgq4EnLCAn4Kq24KuB4KqV4KuN4KqwJywgJ+CqtuCqqOCqvyddLFxuICAgIFsn4Kqw4Kq14Kq/4Kq14Kq+4KqwJywgJ+CquOCri+CqruCqteCqvuCqsCcsICfgqq7gqoLgqpfgqrPgqrXgqr7gqrAnLCAn4Kqs4KuB4Kqn4Kq14Kq+4KqwJywgJ+Cql+CrgeCqsOCrgeCqteCqvuCqsCcsICfgqrbgq4HgqpXgq43gqrDgqrXgqr7gqrAnLCAn4Kq24Kqo4Kq/4Kq14Kq+4KqwJ10sXG4gICAgWyfgqrAnLCAn4Kq44KuLJywgJ+CqruCqgicsICfgqqzgq4EnLCAn4KqX4KuBJywgJ+CqtuCrgScsICfgqrYnXVxuICBdLFxuICB1LFxuICBbXG4gICAgWyfgqpzgqr4nLCAn4Kqr4KuHJywgJ+CqruCqvicsICfgqo8nLCAn4Kqu4KuHJywgJ+CqnOCrgicsICfgqpzgq4EnLCAn4KqRJywgJ+CquCcsICfgqpEnLCAn4KqoJywgJ+CqoeCqvyddLFxuICAgIFsn4Kqc4Kq+4Kqo4KuN4Kqv4KuBJywgJ+Cqq+Crh+CqrOCrjeCqsOCrgScsICfgqq7gqr7gqrDgq43gqponLCAn4KqP4Kqq4KuN4Kqw4Kq/4KqyJywgJ+CqruCrhycsICfgqpzgq4LgqqgnLCAn4Kqc4KuB4Kqy4Kq+4KqIJywgJ+CqkeCql+CquOCrjeCqnycsICfgqrjgqqrgq43gqp/gq4cnLCAn4KqR4KqV4KuN4Kqf4KuLJywgJ+CqqOCqteCrhycsICfgqqHgqr/gqrjgq4cnXSxcbiAgICBbXG4gICAgICAn4Kqc4Kq+4Kqo4KuN4Kqv4KuB4KqG4Kqw4KuAJywgJ+Cqq+Crh+CqrOCrjeCqsOCrgeCqhuCqsOCrgCcsICfgqq7gqr7gqrDgq43gqponLCAn4KqP4Kqq4KuN4Kqw4Kq/4KqyJywgJ+CqruCrhycsICfgqpzgq4LgqqgnLCAn4Kqc4KuB4Kqy4Kq+4KqIJywgJ+CqkeCql+CquOCrjeCqnycsICfgqrjgqqrgq43gqp/gq4fgqq7gq43gqqzgqrAnLCAn4KqR4KqV4KuN4Kqf4KuL4Kqs4KqwJywgJ+CqqOCqteCrh+CqruCrjeCqrOCqsCcsXG4gICAgICAn4Kqh4Kq/4Kq44KuH4Kqu4KuN4Kqs4KqwJ1xuICAgIF1cbiAgXSxcbiAgdSxcbiAgW1sn4KqHIOCquCDgqqrgq4EnLCAn4KqH4Kq4J10sIFsn4KqILuCquC7gqqrgq4LgqrDgq43gqrXgq4cnLCAn4KqILuCquC4nXSwgWyfgqojgqrjgqrXgq4Dgqrjgqqgg4Kqq4KuC4Kqw4KuN4Kq14KuHJywgJ+Cqh+CquOCqteCrgOCquOCqqCddXSxcbiAgMCxcbiAgWzAsIDBdLFxuICBbJ2QvTS95eScsICdkIE1NTSwgeScsICdkIE1NTU0sIHknLCAnRUVFRSwgZCBNTU1NLCB5J10sXG4gIFsnaGg6bW0gYScsICdoaDptbTpzcyBhJywgJ2hoOm1tOnNzIGEgeicsICdoaDptbTpzcyBhIHp6enonXSxcbiAgWyd7MX0gezB9JywgdSwgJ3sxfSDgqo8gezB9IOCqteCqvuCql+CrjeCqr+CrhycsIHVdLFxuICBbJy4nLCAnLCcsICc7JywgJyUnLCAnKycsICctJywgJ0UnLCAnw5cnLCAn4oCwJywgJ+KInicsICdOYU4nLCAnOiddLFxuICBbJyMsIyMsIyMwLiMjIycsICcjLCMjLCMjMCUnLCAnwqQjLCMjLCMjMC4wMCcsICdbI0UwXSddLFxuICAnSU5SJyxcbiAgJ+KCuScsXG4gICfgqq3gqr7gqrDgqqTgq4Dgqq8g4Kqw4KuC4Kqq4Kq/4Kqv4Kq+JyxcbiAgeydKUFknOiBbJ0pQwqUnLCAnwqUnXSwgJ01VUic6IFt1LCAn4Kqw4KuCLiddLCAnVEhCJzogWyfguL8nXSwgJ1RXRCc6IFsnTlQkJ10sICdVU0QnOiBbJ1VTJCcsICckJ119LFxuICAnbHRyJyxcbiAgcGx1cmFsXG5dO1xuIl19