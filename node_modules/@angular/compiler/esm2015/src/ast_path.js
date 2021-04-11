/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
/**
 * A path is an ordered set of elements. Typically a path is to  a
 * particular offset in a source file. The head of the list is the top
 * most node. The tail is the node that contains the offset directly.
 *
 * For example, the expression `a + b + c` might have an ast that looks
 * like:
 *     +
 *    / \
 *   a   +
 *      / \
 *     b   c
 *
 * The path to the node at offset 9 would be `['+' at 1-10, '+' at 7-10,
 * 'c' at 9-10]` and the path the node at offset 1 would be
 * `['+' at 1-10, 'a' at 1-2]`.
 */
export class AstPath {
    constructor(path, position = -1) {
        this.path = path;
        this.position = position;
    }
    get empty() {
        return !this.path || !this.path.length;
    }
    get head() {
        return this.path[0];
    }
    get tail() {
        return this.path[this.path.length - 1];
    }
    parentOf(node) {
        return node && this.path[this.path.indexOf(node) - 1];
    }
    childOf(node) {
        return this.path[this.path.indexOf(node) + 1];
    }
    first(ctor) {
        for (let i = this.path.length - 1; i >= 0; i--) {
            let item = this.path[i];
            if (item instanceof ctor)
                return item;
        }
    }
    push(node) {
        this.path.push(node);
    }
    pop() {
        return this.path.pop();
    }
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYXN0X3BhdGguanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi9wYWNrYWdlcy9jb21waWxlci9zcmMvYXN0X3BhdGgudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7OztHQU1HO0FBRUg7Ozs7Ozs7Ozs7Ozs7Ozs7R0FnQkc7QUFDSCxNQUFNLE9BQU8sT0FBTztJQUNsQixZQUFvQixJQUFTLEVBQVMsV0FBbUIsQ0FBQyxDQUFDO1FBQXZDLFNBQUksR0FBSixJQUFJLENBQUs7UUFBUyxhQUFRLEdBQVIsUUFBUSxDQUFhO0lBQUcsQ0FBQztJQUUvRCxJQUFJLEtBQUs7UUFDUCxPQUFPLENBQUMsSUFBSSxDQUFDLElBQUksSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDO0lBQ3pDLENBQUM7SUFDRCxJQUFJLElBQUk7UUFDTixPQUFPLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFDdEIsQ0FBQztJQUNELElBQUksSUFBSTtRQUNOLE9BQU8sSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLE1BQU0sR0FBRyxDQUFDLENBQUMsQ0FBQztJQUN6QyxDQUFDO0lBRUQsUUFBUSxDQUFDLElBQWlCO1FBQ3hCLE9BQU8sSUFBSSxJQUFJLElBQUksQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsSUFBSSxDQUFDLEdBQUcsQ0FBQyxDQUFDLENBQUM7SUFDeEQsQ0FBQztJQUNELE9BQU8sQ0FBQyxJQUFPO1FBQ2IsT0FBTyxJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLElBQUksQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDO0lBQ2hELENBQUM7SUFFRCxLQUFLLENBQWMsSUFBOEI7UUFDL0MsS0FBSyxJQUFJLENBQUMsR0FBRyxJQUFJLENBQUMsSUFBSSxDQUFDLE1BQU0sR0FBRyxDQUFDLEVBQUUsQ0FBQyxJQUFJLENBQUMsRUFBRSxDQUFDLEVBQUUsRUFBRTtZQUM5QyxJQUFJLElBQUksR0FBRyxJQUFJLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDO1lBQ3hCLElBQUksSUFBSSxZQUFZLElBQUk7Z0JBQUUsT0FBVSxJQUFJLENBQUM7U0FDMUM7SUFDSCxDQUFDO0lBRUQsSUFBSSxDQUFDLElBQU87UUFDVixJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQztJQUN2QixDQUFDO0lBRUQsR0FBRztRQUNELE9BQU8sSUFBSSxDQUFDLElBQUksQ0FBQyxHQUFHLEVBQUcsQ0FBQztJQUMxQixDQUFDO0NBQ0YiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuLyoqXG4gKiBBIHBhdGggaXMgYW4gb3JkZXJlZCBzZXQgb2YgZWxlbWVudHMuIFR5cGljYWxseSBhIHBhdGggaXMgdG8gIGFcbiAqIHBhcnRpY3VsYXIgb2Zmc2V0IGluIGEgc291cmNlIGZpbGUuIFRoZSBoZWFkIG9mIHRoZSBsaXN0IGlzIHRoZSB0b3BcbiAqIG1vc3Qgbm9kZS4gVGhlIHRhaWwgaXMgdGhlIG5vZGUgdGhhdCBjb250YWlucyB0aGUgb2Zmc2V0IGRpcmVjdGx5LlxuICpcbiAqIEZvciBleGFtcGxlLCB0aGUgZXhwcmVzc2lvbiBgYSArIGIgKyBjYCBtaWdodCBoYXZlIGFuIGFzdCB0aGF0IGxvb2tzXG4gKiBsaWtlOlxuICogICAgICtcbiAqICAgIC8gXFxcbiAqICAgYSAgICtcbiAqICAgICAgLyBcXFxuICogICAgIGIgICBjXG4gKlxuICogVGhlIHBhdGggdG8gdGhlIG5vZGUgYXQgb2Zmc2V0IDkgd291bGQgYmUgYFsnKycgYXQgMS0xMCwgJysnIGF0IDctMTAsXG4gKiAnYycgYXQgOS0xMF1gIGFuZCB0aGUgcGF0aCB0aGUgbm9kZSBhdCBvZmZzZXQgMSB3b3VsZCBiZVxuICogYFsnKycgYXQgMS0xMCwgJ2EnIGF0IDEtMl1gLlxuICovXG5leHBvcnQgY2xhc3MgQXN0UGF0aDxUPiB7XG4gIGNvbnN0cnVjdG9yKHByaXZhdGUgcGF0aDogVFtdLCBwdWJsaWMgcG9zaXRpb246IG51bWJlciA9IC0xKSB7fVxuXG4gIGdldCBlbXB0eSgpOiBib29sZWFuIHtcbiAgICByZXR1cm4gIXRoaXMucGF0aCB8fCAhdGhpcy5wYXRoLmxlbmd0aDtcbiAgfVxuICBnZXQgaGVhZCgpOiBUfHVuZGVmaW5lZCB7XG4gICAgcmV0dXJuIHRoaXMucGF0aFswXTtcbiAgfVxuICBnZXQgdGFpbCgpOiBUfHVuZGVmaW5lZCB7XG4gICAgcmV0dXJuIHRoaXMucGF0aFt0aGlzLnBhdGgubGVuZ3RoIC0gMV07XG4gIH1cblxuICBwYXJlbnRPZihub2RlOiBUfHVuZGVmaW5lZCk6IFR8dW5kZWZpbmVkIHtcbiAgICByZXR1cm4gbm9kZSAmJiB0aGlzLnBhdGhbdGhpcy5wYXRoLmluZGV4T2Yobm9kZSkgLSAxXTtcbiAgfVxuICBjaGlsZE9mKG5vZGU6IFQpOiBUfHVuZGVmaW5lZCB7XG4gICAgcmV0dXJuIHRoaXMucGF0aFt0aGlzLnBhdGguaW5kZXhPZihub2RlKSArIDFdO1xuICB9XG5cbiAgZmlyc3Q8TiBleHRlbmRzIFQ+KGN0b3I6IHtuZXcoLi4uYXJnczogYW55W10pOiBOfSk6IE58dW5kZWZpbmVkIHtcbiAgICBmb3IgKGxldCBpID0gdGhpcy5wYXRoLmxlbmd0aCAtIDE7IGkgPj0gMDsgaS0tKSB7XG4gICAgICBsZXQgaXRlbSA9IHRoaXMucGF0aFtpXTtcbiAgICAgIGlmIChpdGVtIGluc3RhbmNlb2YgY3RvcikgcmV0dXJuIDxOPml0ZW07XG4gICAgfVxuICB9XG5cbiAgcHVzaChub2RlOiBUKSB7XG4gICAgdGhpcy5wYXRoLnB1c2gobm9kZSk7XG4gIH1cblxuICBwb3AoKTogVCB7XG4gICAgcmV0dXJuIHRoaXMucGF0aC5wb3AoKSE7XG4gIH1cbn1cbiJdfQ==