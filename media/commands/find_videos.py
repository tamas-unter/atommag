#!/usr/bin/python
# -*- coding: utf-8 -*-
import glob
import os
import re
import json
import sys

#	order matters!
patterns=[re.compile("(\d+)[xX](\d{2})"), 
	re.compile("[sS](\d{1,2})[eE](\d{1,2})"), 
	re.compile("[sS](\d+).*[eE]?(\d{2})"), 
	re.compile("(\d+)(\d{2})")]
extFilter=re.compile("\.(avi|mp4|mkv|iso)")
numFilter=re.compile("\d+")
#	system-dependent:
folders=["/video/_VIDEO","/video/_ZENE","/video/_MEGVOLT","/diskimages/Nagymacska"]

def scansubdir(path):
	files=[]
	subs=glob.glob(path+"*/")
	if len(subs)>0:
		for s in subs:
			files=files + scansubdir(s)
	currentfiles=glob.glob(path+"*")
	for cf in currentfiles:
		if os.path.isdir(cf) != True:
			if extFilter.search(cf):
				files=files + [cf]
	return files



root=[]
for folder in folders:
	root=root + glob.glob(folder+"/*/")

titles={}
items=[]
for f in root:
	# find sub-series:
        element=scansubdir(f)
        items=items+[element]
                
print "Content-type: application/json\n"
print json.dumps(items)
	
