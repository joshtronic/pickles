#!/bin/bash
rm ../js/combined.min.js
cat ../js/jquery.js ../js/jquery-ui.js ../js/jquery-validate.js ../js/core.min.js >> ../js/combined.min.js
