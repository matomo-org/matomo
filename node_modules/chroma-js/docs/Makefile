all:
	cat src/index.md ../CHANGELOG.md > tmp.md
	../node_modules/.bin/markdown -f gfm -s src/index.css tmp.md --title "chroma.js api docs!" > index.html
	bin/post-process
	rm tmp.md
	cp ../chroma*.js libs/

preview:
	../node_modules/.bin/http-server
	# python -m SimpleHTTPServer
