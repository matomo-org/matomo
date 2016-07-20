$(function () {
    $('#piwik-promo-thumbnail').click(function () {
        var promoEmbed = $('#piwik-promo-embed'),
          widgetWidth = $(this).closest('.widgetContent').width(),
          height = (266 * widgetWidth) / 421,
          embedHtml = '<iframe width="100%" height="' + height + '" src="http://www.youtube.com/embed/OslfF_EH81g?autoplay=1&vq=hd720&wmode=transparent" frameborder="0" wmode="Opaque"></iframe>';

        $(this).hide();
        promoEmbed.height(height).html(embedHtml);
        promoEmbed.show();
    });
});