
PHP_ARG_ENABLE(xhprof, whether to enable xhprof support,
[ --enable-xhprof      Enable xhprof support])

if test "$PHP_XHPROF" != "no"; then
  PHP_NEW_EXTENSION(xhprof, xhprof.c, $ext_shared)
fi
