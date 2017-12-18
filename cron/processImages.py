#!/usr/bin/python

#Process the images decoded by the server and output a web page suitable for display on the web

import sys
import time
import os
import glob
from shutil import copyfile

def getFoxName(id):
    names = ("AO-85 (Fox-1A)","RadFxSat (Fox-1B)","Fox-1Cliff","Fox-1D","Fox-1E")
    return names[int(id)]

# Read all the images in the passed diretory and return as a sorted list
def parseImageDir(id, imageDir):
    match = str(id) + "_*"
    images = sorted(glob.glob(imageDir + os.sep + match))
    #if len(images) == 0:
    #    print ('No image files found in ' + imageDir + 'for: ' + match)
    return images

# Given a list of images, build a web page to display them
def buildPage(id, images):
    TABLE_COLS=3
    col=0
    content="<html><head><title>Virginia Tech Camera Images from Spacecraft " 
    content=content + getFoxName(id)+"</title>"
    content=content+"""<link rel="stylesheet" type="text/css" media="all" 
                  href="http://www.amsat.org/wordpress/wp-content/themes/generatepress/style.css" />
                  </head>
                  <body>
                  <img src='http://www.amsat.org/wordpress/wp-content/uploads/2014/08/amsat2.png'> """
    content=content+"<h1 class=entry-title>Virginia Tech Camera Images from Spacecraft " + getFoxName(id) + "</h1>"
    imgTag1='<figure><img style="border:10px solid black;" src="'
    #imgTag2='" width="225" height="151" alt="Image from spacecraft Fox" /><figcaption>'
    imgTag2='"alt="Image from spacecraft '+getFoxName(id)+'" /><figcaption>'
    imgTag3='</figcaption></figure>'

    content=content+"<table>\n<tr>"
    for item in images:
        pic = os.path.basename(item)
        params = pic.split('_')
        reset=params[1]
        uptime=params[2]
        picId=params[3].split('.')[0] 
        if (col % TABLE_COLS == 0):
            content = content + "</tr><tr>"
            col=0;
        content = content + "<td>"
        content = content + imgTag1 + pic + imgTag2 + "Pic: " + picId + " : " + reset + " / " + uptime + imgTag3 
        content = content + "</td>"
        col=col+1
    content = content + "</tr>\n</table></body></html>"
    return content

# Copy the images from the server image dir to the directory for this spacecraft
# Does not copy a file that already exists and has the same timestamp
def copyImages(images, webDir):
    for item in images:
        pic = os.path.basename(item)
        toFile = webDir + os.sep + pic
        if not (os.path.exists(toFile)):
            copyfile(item, toFile) 
            print("New file to process: " + toFile)
        else:
            picDt = os.path.getmtime(item)
            toFileDt = os.path.getmtime(toFile)
            if (picDt > toFileDt):
                copyfile(item, toFile) 
                #print("Updated File: " + toFile)
            #else:
            #    print("Existing File: " + toFile)
                

# Write the webpage into the web server directory
def writeWebpage(webDir, page):
    webPage = open(webDir + os.sep + "index.html", "w")
    webPage.write(page)
    webPage.close()

def processSat(id, imageDir, webDir):
        images = parseImageDir(id, imageDir)    
        copyImages(images,webDir)
        page = buildPage(id, images)
        writeWebpage(webDir, page)
        time.sleep(5)

def main():
    # We expect exactly one command line argument, the directory that contains the log files
    if len(sys.argv) <= 1:
        print ('Usage: '+sys.argv[0]+' <image_dir>')
        print ('Convert the log files extracted from the SQL database into SegDb files')
        sys.exit(1)
    # We process all files in the directory that end with the names for the log files
    imageDir = sys.argv[1]
    while(True):
        processSat(4, imageDir, "/home/ec2-user/tlm/fox1d/images")

main()
