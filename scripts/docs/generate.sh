#!/bin/bash
rm ../../docs -rf
phpdoc -d ../../ -t ../../docs -ct usage -ti "PHP Interface Collection of Killer Libraries to Enhance Stuff" -ric README,INSTALL,COPYING,TODO -po PICKLES -s
