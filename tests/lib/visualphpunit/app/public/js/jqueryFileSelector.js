(function($) {
  
  function buildTree(options, $fileSelector, dir, isActive, showTree, callback) {
    $.get(options.serverEndpoint, {dir: dir}, function(response) {
      var classAttr = ( dir == options.root ) ? " nav" : '',
          html = "<ul class='nav-list" + classAttr + "' " +
            "style='display: none;'>";

      response = $.parseJSON(response);
      
      var paths = [];
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
        
        paths.push(file.path);
      });

      html += '</ul>';
      $fileSelector.append(html);

      if ( dir == options.root ) {
        $fileSelector.find('ul:hidden').show();
      } else {
        if (typeof showTree === 'undefined' || showTree) {
          $fileSelector.find('ul:hidden').slideDown(options.expandSpeed);
        }
      }

      bindTree(options, $fileSelector);
      
      if (callback) {
        callback(paths);
      }
    });
  }
  
  function bindTree(options, $fileSelector) {
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
              options,
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
   
  function createFileSelector(options) {
      options = $.extend({
        callback: function() {},
        collapseSpeed: 500,
        expandSpeed: 500,
        root: '/',
        serverEndpoint: '/'
      }, options);

      return this.each(function() {

        options.root = encodeURIComponent(options.root);
        buildTree(options, $(this), options.root);
        
        $(this).data('fileSelectorOptions', options);
      });
  };
  
  function callFileSelectorMethod(methodName, showTree, callback) {
    if (methodName == 'expand') { // expands a link. this == link element
      var $fileSelector = $(this).closest('div'), // HACK: assumes file selector is in a div...
          options = $fileSelector.data('fileSelectorOptions'),
          $parent = $(this).parent();
      buildTree(
        options,
        $parent,
        encodeURIComponent($(this).attr('data-path')),
        $parent.hasClass('active'),
        showTree,
        callback
      );
    }
  };
  
  $.extend($.fn, {
    fileSelector: function() {
      if (typeof arguments[0] == 'string') { // calling method
        return callFileSelectorMethod.apply(this, arguments);
      } else { // creating instance
        return createFileSelector.call(this, arguments[0]);
      }
    }
  });

})(jQuery);
