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
folders=["/video/_SOROZATOK/_KOMÉDIA","/video/_SOROZATOK/_EGYÉB","/video/_SOROZATOK/_KOREA","/video/_SOROZATOK/_KRIMI","/video/_SOROZATOK/_MESE","/video/_SOROZATOK/_SCIFI"]

def scansubdir(path):
	files=[]
	subs=glob.glob(path+"*/")
	if len(subs)>0:
		for s in subs:
			files=files + scansubdir(s)
	currentfiles=glob.glob(path+"*")
	for cf in currentfiles:
		if os.path.isdir(cf) != True:
			files=files + [cf]
	return files

def getfileinfo(fff,title):
	fileName=fff.rsplit("/",2)[2]
	parentDir=fff.rsplit("/",2)[1]
	if extFilter.search(fileName):
		srch = None
		series = "-1"
		episode="-1"
		for pattern in patterns:
			srch = pattern.search(fileName)
			if srch:
				series=int(srch.group(1))
				episode=int(srch.group(2))
				break
		if srch == None:
			p2=re.compile("(\d{2})")
			srch=p2.search(fileName)
			if srch:
				series=0
				snum=numFilter.search(parentDir)
				if snum:
					series=int(snum.group(0))
				episode=int(srch.group(1))
			#else:
				#print "nincs találta", fileName
				#re.compile("\/.*(\d+)\/(\d{2})")
				#re.compile("(\d{2})") 
		#print title, "\t", series,"\t", episode, "\t", fileName, "\t",subDir
		return {'title': title, 'series': series, 'episode': episode, 'filename':fff}
	else:
		return None

def compilevideos(subDir,title):
	items={}
	items[title]=subDir
	files=[]
	for fff in scansubdir(subDir):
		#try:
		#parentName=fff.rsplit("/",2)[1]
		element=getfileinfo(fff,title)
		if element!=None:
			#append it to files...
			files=files+[element]
	return files

root=[]
for folder in folders:
	root=root + glob.glob(folder+"/*/")

titles={}
items=[]
for f in root:
	# find sub-series:
	for f_ in glob.glob(f+"*"):
		if os.path.isdir(f_):
			nameOnly=f_.rsplit("/",3)[2]
			if numFilter.search(nameOnly) == None:
				seriesTitle=f_.rsplit("/",3)[1]+"."+f_.rsplit("/",3)[2]
				__f=f_
			else:
				seriesTitle=f.rsplit("/",2)[1]
				__f=f
			items=items+compilevideos(f_,seriesTitle)
			titles[seriesTitle]=__f
		else:
			#print f_
			element=getfileinfo(f_,f_.rsplit("/",2)[1])
			if element!=None:
				items=items+[element]
print "Content-type: application/json\n"
print json.dumps(items)
	
