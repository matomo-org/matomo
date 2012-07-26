{if isset($enableFrames) && !$enableFrames}
{literal}<script type="text/javascript">if(self == top) { var theBody = document.getElementsByTagName('body')[0];	theBody.style.display = 'block'; } else { top.location = self.location;	}</script>
{/literal}{/if}