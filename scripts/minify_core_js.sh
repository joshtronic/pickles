#!/bin/bash
curl --header "Content-Type: application/x-www-form-urlencoded; charset=utf-8" --data "compilation_level=SIMPLE_OPTIMIZATIONS&output_format=text&output_info=compiled_code" --data-urlencode "js_code=`cat ../js/core.js`" http://closure-compiler.appspot.com/compile > ../js/core.min.js
./combine_js.sh
