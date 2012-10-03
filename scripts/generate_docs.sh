#!/bin/bash
rm -rf ../docs
phpdoc -d ../ -t ../docs -ct usage -ti "PHP Interface Collection of Killer Libraries to Enhance Stuff" -ric README.md,MIT-LICENSE.txt -po PICKLES -s
