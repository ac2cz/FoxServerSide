#!/usr/bin/python

#Process the images decoded by the server and output a web page suitable for display on the web

import sys
import time
import os
import glob
from shutil import copyfile

def getFoxName(id):
    names = ("AO-85 (Fox-1A)","RadFxSat (Fox-1B)","Fox-1Cliff","Fox-1D","Fox-1E")
    return names[int(id)-1]

# Read all the images in the passed diretory and return as a sorted list
def parseImageDir(id, imageDir):
    match = str(id) + "_*"
    images = sorted(glob.glob(imageDir + os.sep + match), key=os.path.getmtime)
    #images = sorted(glob.glob(imageDir + os.sep + match), reverse=False)
    #if len(images) == 0:
    #    print ('No image files found in ' + imageDir + 'for: ' + match)
    return images

# Copy the images from the server image dir to the directory for this spacecraft
# Does not copy a file that already exists and has the same timestamp
def copyImages(images, webDir):
    filesCopied = 0
    for item in images:
        pic = os.path.basename(item)
        toFile = webDir + os.sep + pic
        if not (os.path.exists(toFile)):
            copyfile(item, toFile) 
            filesCopied = filesCopied + 1
        else:
            picDt = os.path.getmtime(item)
            toFileDt = os.path.getmtime(toFile)
            if (picDt > toFileDt):
                copyfile(item, toFile) 
                #print("Updated File: " + toFile)
            #else:
            #    print("Existing File: " + toFile)
    #print("Files copied: " + str(filesCopied))
    return filesCopied

def processSat(id, imageDir, webDir):
    images = parseImageDir(id, imageDir)    
    num = copyImages(images,webDir)

def main():
    # We expect exactly one command line argument, the directory that contains the image files
    if len(sys.argv) <= 1:
        print ('Usage: '+sys.argv[0]+' <image_dir>')
        print ('Copy the image files extracted from the SQL database to website')
        sys.exit(1)
    # We process all files in the directory that end with the names for the log files
    imageDir = sys.argv[1]
    while(True):
        processSat(4, imageDir, "/home/tlmmgr/tlm/fox1d/images")
        processSat(3, imageDir, "/home/tlmmgr/tlm/fox1c/images")
        time.sleep(5)

main()
