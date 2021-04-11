/**
 * @license
 * Copyright Google LLC All Rights Reserved.
 *
 * Use of this source code is governed by an MIT-style license that can be
 * found in the LICENSE file at https://angular.io/license
 */
import { assertNotEqual } from '../../util/assert';
/**
 * Returns an index of `classToSearch` in `className` taking token boundaries into account.
 *
 * `classIndexOf('AB A', 'A', 0)` will be 3 (not 0 since `AB!==A`)
 *
 * @param className A string containing classes (whitespace separated)
 * @param classToSearch A class name to locate
 * @param startingIndex Starting location of search
 * @returns an index of the located class (or -1 if not found)
 */
export function classIndexOf(className, classToSearch, startingIndex) {
    ngDevMode && assertNotEqual(classToSearch, '', 'can not look for "" string.');
    let end = className.length;
    while (true) {
        const foundIndex = className.indexOf(classToSearch, startingIndex);
        if (foundIndex === -1)
            return foundIndex;
        if (foundIndex === 0 || className.charCodeAt(foundIndex - 1) <= 32 /* SPACE */) {
            // Ensure that it has leading whitespace
            const length = classToSearch.length;
            if (foundIndex + length === end ||
                className.charCodeAt(foundIndex + length) <= 32 /* SPACE */) {
                // Ensure that it has trailing whitespace
                return foundIndex;
            }
        }
        // False positive, keep searching from where we left off.
        startingIndex = foundIndex + 1;
    }
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiY2xhc3NfZGlmZmVyLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vLi4vcGFja2FnZXMvY29yZS9zcmMvcmVuZGVyMy9zdHlsaW5nL2NsYXNzX2RpZmZlci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7O0dBTUc7QUFFSCxPQUFPLEVBQUMsY0FBYyxFQUFDLE1BQU0sbUJBQW1CLENBQUM7QUFJakQ7Ozs7Ozs7OztHQVNHO0FBQ0gsTUFBTSxVQUFVLFlBQVksQ0FDeEIsU0FBaUIsRUFBRSxhQUFxQixFQUFFLGFBQXFCO0lBQ2pFLFNBQVMsSUFBSSxjQUFjLENBQUMsYUFBYSxFQUFFLEVBQUUsRUFBRSw2QkFBNkIsQ0FBQyxDQUFDO0lBQzlFLElBQUksR0FBRyxHQUFHLFNBQVMsQ0FBQyxNQUFNLENBQUM7SUFDM0IsT0FBTyxJQUFJLEVBQUU7UUFDWCxNQUFNLFVBQVUsR0FBRyxTQUFTLENBQUMsT0FBTyxDQUFDLGFBQWEsRUFBRSxhQUFhLENBQUMsQ0FBQztRQUNuRSxJQUFJLFVBQVUsS0FBSyxDQUFDLENBQUM7WUFBRSxPQUFPLFVBQVUsQ0FBQztRQUN6QyxJQUFJLFVBQVUsS0FBSyxDQUFDLElBQUksU0FBUyxDQUFDLFVBQVUsQ0FBQyxVQUFVLEdBQUcsQ0FBQyxDQUFDLGtCQUFrQixFQUFFO1lBQzlFLHdDQUF3QztZQUN4QyxNQUFNLE1BQU0sR0FBRyxhQUFhLENBQUMsTUFBTSxDQUFDO1lBQ3BDLElBQUksVUFBVSxHQUFHLE1BQU0sS0FBSyxHQUFHO2dCQUMzQixTQUFTLENBQUMsVUFBVSxDQUFDLFVBQVUsR0FBRyxNQUFNLENBQUMsa0JBQWtCLEVBQUU7Z0JBQy9ELHlDQUF5QztnQkFDekMsT0FBTyxVQUFVLENBQUM7YUFDbkI7U0FDRjtRQUNELHlEQUF5RDtRQUN6RCxhQUFhLEdBQUcsVUFBVSxHQUFHLENBQUMsQ0FBQztLQUNoQztBQUNILENBQUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIEBsaWNlbnNlXG4gKiBDb3B5cmlnaHQgR29vZ2xlIExMQyBBbGwgUmlnaHRzIFJlc2VydmVkLlxuICpcbiAqIFVzZSBvZiB0aGlzIHNvdXJjZSBjb2RlIGlzIGdvdmVybmVkIGJ5IGFuIE1JVC1zdHlsZSBsaWNlbnNlIHRoYXQgY2FuIGJlXG4gKiBmb3VuZCBpbiB0aGUgTElDRU5TRSBmaWxlIGF0IGh0dHBzOi8vYW5ndWxhci5pby9saWNlbnNlXG4gKi9cblxuaW1wb3J0IHthc3NlcnROb3RFcXVhbH0gZnJvbSAnLi4vLi4vdXRpbC9hc3NlcnQnO1xuaW1wb3J0IHtDaGFyQ29kZX0gZnJvbSAnLi4vLi4vdXRpbC9jaGFyX2NvZGUnO1xuXG5cbi8qKlxuICogUmV0dXJucyBhbiBpbmRleCBvZiBgY2xhc3NUb1NlYXJjaGAgaW4gYGNsYXNzTmFtZWAgdGFraW5nIHRva2VuIGJvdW5kYXJpZXMgaW50byBhY2NvdW50LlxuICpcbiAqIGBjbGFzc0luZGV4T2YoJ0FCIEEnLCAnQScsIDApYCB3aWxsIGJlIDMgKG5vdCAwIHNpbmNlIGBBQiE9PUFgKVxuICpcbiAqIEBwYXJhbSBjbGFzc05hbWUgQSBzdHJpbmcgY29udGFpbmluZyBjbGFzc2VzICh3aGl0ZXNwYWNlIHNlcGFyYXRlZClcbiAqIEBwYXJhbSBjbGFzc1RvU2VhcmNoIEEgY2xhc3MgbmFtZSB0byBsb2NhdGVcbiAqIEBwYXJhbSBzdGFydGluZ0luZGV4IFN0YXJ0aW5nIGxvY2F0aW9uIG9mIHNlYXJjaFxuICogQHJldHVybnMgYW4gaW5kZXggb2YgdGhlIGxvY2F0ZWQgY2xhc3MgKG9yIC0xIGlmIG5vdCBmb3VuZClcbiAqL1xuZXhwb3J0IGZ1bmN0aW9uIGNsYXNzSW5kZXhPZihcbiAgICBjbGFzc05hbWU6IHN0cmluZywgY2xhc3NUb1NlYXJjaDogc3RyaW5nLCBzdGFydGluZ0luZGV4OiBudW1iZXIpOiBudW1iZXIge1xuICBuZ0Rldk1vZGUgJiYgYXNzZXJ0Tm90RXF1YWwoY2xhc3NUb1NlYXJjaCwgJycsICdjYW4gbm90IGxvb2sgZm9yIFwiXCIgc3RyaW5nLicpO1xuICBsZXQgZW5kID0gY2xhc3NOYW1lLmxlbmd0aDtcbiAgd2hpbGUgKHRydWUpIHtcbiAgICBjb25zdCBmb3VuZEluZGV4ID0gY2xhc3NOYW1lLmluZGV4T2YoY2xhc3NUb1NlYXJjaCwgc3RhcnRpbmdJbmRleCk7XG4gICAgaWYgKGZvdW5kSW5kZXggPT09IC0xKSByZXR1cm4gZm91bmRJbmRleDtcbiAgICBpZiAoZm91bmRJbmRleCA9PT0gMCB8fCBjbGFzc05hbWUuY2hhckNvZGVBdChmb3VuZEluZGV4IC0gMSkgPD0gQ2hhckNvZGUuU1BBQ0UpIHtcbiAgICAgIC8vIEVuc3VyZSB0aGF0IGl0IGhhcyBsZWFkaW5nIHdoaXRlc3BhY2VcbiAgICAgIGNvbnN0IGxlbmd0aCA9IGNsYXNzVG9TZWFyY2gubGVuZ3RoO1xuICAgICAgaWYgKGZvdW5kSW5kZXggKyBsZW5ndGggPT09IGVuZCB8fFxuICAgICAgICAgIGNsYXNzTmFtZS5jaGFyQ29kZUF0KGZvdW5kSW5kZXggKyBsZW5ndGgpIDw9IENoYXJDb2RlLlNQQUNFKSB7XG4gICAgICAgIC8vIEVuc3VyZSB0aGF0IGl0IGhhcyB0cmFpbGluZyB3aGl0ZXNwYWNlXG4gICAgICAgIHJldHVybiBmb3VuZEluZGV4O1xuICAgICAgfVxuICAgIH1cbiAgICAvLyBGYWxzZSBwb3NpdGl2ZSwga2VlcCBzZWFyY2hpbmcgZnJvbSB3aGVyZSB3ZSBsZWZ0IG9mZi5cbiAgICBzdGFydGluZ0luZGV4ID0gZm91bmRJbmRleCArIDE7XG4gIH1cbn1cbiJdfQ==