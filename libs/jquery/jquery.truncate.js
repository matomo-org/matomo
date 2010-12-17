/*!
 * Copyright 2007, 2008, 2009, 2010  Matthieu Aubry
 * All rights reserved.
 *
 * @link http://dev.piwik.org/trac/browser/trunk/libs/jquery/truncate
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are
 * met:
 *
 * * Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright
 *   notice, this list of conditions and the following disclaimer in the
 *   documentation and/or other materials provided with the distribution.
 *
 * * Neither the name of Matthieu Aubry nor the names of its contributors
 *   may be used to endorse or promote products derived from this
 *   software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
jQuery.fn.truncate = function(max) {
 return this.each(
 	function() {
 		var trail='...';
		v=jQuery.trim(jQuery(this).text());
		while(max<v.length) {
			c=v.charAt(max);
			newStringTruncated=v.substring(0,max)+trail;
			charToRemove='"';
			regExp=new RegExp("["+charToRemove+"]","g");
			vCleaned = v
						.replace(regExp,"&amp;quot;")
						.replace(/</g, '&amp;lt;')
						.replace(/>/g, '&amp;gt;');
			newStringTruncated = newStringTruncated
 										.replace(regExp,"'")
 										.replace(/</g, '&lt;')
 										.replace(/>/g, '&gt;');
			html='<span class="truncated" title="'+vCleaned+'">'+newStringTruncated+'</span>';
			jQuery(this).html(html);
			break;
			max--;
		}
 	}
 );
};
