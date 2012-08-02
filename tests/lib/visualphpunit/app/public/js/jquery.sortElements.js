/**
 * jQuery.fn.sortElements
 * --------------
 * @author James Padolsey (http://james.padolsey.com)
 * @version 0.11
 * @updated 18-MAR-2010
 * --------------
 * @param Function comparator:
 *   Exactly the same behaviour as [1,2,3].sort(comparator)
 *
 * @param Function getSortable
 *   A function that should return the element that is
 *   to be sorted. The comparator will run on the
 *   current collection, but you may want the actual
 *   resulting sort to occur on a parent or another
 *   associated element.
 *
 *   E.g. $('td').sortElements(comparator, function(){
 *      return this.parentNode;
 *   })
 *
 *   The <td>'s parent (<tr>) will be sorted instead
 *   of the <td> itself.
 */
jQuery.fn.sortElements=(function(){var a=[].sort;return function(c,d){d=d||function(){return this};var b=this.map(function(){var f=d.call(this),e=f.parentNode,g=e.insertBefore(document.createTextNode(""),f.nextSibling);return function(){if(e===this){throw new Error("You can't sort elements if any one is a descendant of another.")}e.insertBefore(this,g);e.removeChild(g)}});return a.call(this,c).each(function(e){b[e].call(d.call(this))})}})();
