(function($) {

  $.extend($.fn, {
    fileSelector: function(options) {
      options = $.extend({
        callback: function() {},
        collapseSpeed: 500,
        expandSpeed: 500,
        root: '/',
        serverEndpoint: '/'
      }, options);

      return this.each(function() {

        function buildTree($fileSelector, dir, isActive) {
          $.get(options.serverEndpoint, {dir: dir}, function(response) {
            var classAttr = ( dir == options.root ) ? " nav" : '',
                html = "<ul class='nav-list" + classAttr + "' " +
                  "style='display: none;'>";

            response = $.parseJSON(response);

            $.each(response, function(index, file) {
              var icon = ( file.type == 'directory' )
                ? 'icon-folder-close'
                : 'icon-file';
              var classAttr = ( isActive ) ? ' active' : '';

              html += "<li class='" + file.type + classAttr + "'>" +
                        "<a href='#' data-path='" + file.path + "'>" +
                          "<i class='" + icon + "'></i>" +
                          file.name +
                        '</a>' +
                      '</li>';
            });

            html += '</ul>';
            $fileSelector.append(html);

            if ( dir == options.root ) {
              $fileSelector.find('ul:hidden').show();
            } else {
              $fileSelector.find('ul:hidden').slideDown(options.expandSpeed);
            }

            bindTree($fileSelector);
          });
        }

        function bindTree($fileSelector) {
          $fileSelector.find('li a').bind('click', function(event) {
            var $this = $(this),
                $parent = $this.parent(),
                $children = $this.children(),
                selector,
                nearest;

            event.preventDefault();

            if ( $parent.hasClass('directory') ) {
              if ( event.metaKey || event.ctrlKey ) {
                $parent.toggleClass('active');
                $parent.find('li').toggleClass('active');
                options.callback($this.attr('data-path'));
              } else {
                if ( $children.hasClass('icon-folder-close') ) {
                  $parent.find('ul').remove();
                  buildTree(
                    $parent,
                    encodeURIComponent($this.attr('data-path')),
                    $parent.hasClass('active')
                  );
                  $children.removeClass().addClass('icon-folder-open');
                } else {
                  $parent.find('ul').slideUp(options.collapseSpeed);
                  $children.removeClass().addClass('icon-folder-close');
                }
              }
            } else {
              if ( event.shiftKey ) {
                selector = ( $parent.hasClass('active') )
                  ? ':not(.active)'
                  : '.active';

                if ( $nearest = $parent.siblings(selector) ) {
                  if ( $nearest.index() > $parent.index() ) {
                    $parent.nextUntil(selector).toggleClass('active');
                  } else {
                    $parent.prevUntil(selector).toggleClass('active');
                  }
                }
              }

              $parent.toggleClass('active');
              options.callback($this.attr('data-path'));
            }

          });
        }

        options.root = encodeURIComponent(options.root);
        buildTree($(this), options.root);
      });
    }
  });

})(jQuery);
