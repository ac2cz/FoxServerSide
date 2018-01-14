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
    images = sorted(glob.glob(imageDir + os.sep + match), reverse=False)
    #if len(images) == 0:
    #    print ('No image files found in ' + imageDir + 'for: ' + match)
    return images

# Given a list of images, build a web page to display them
def buildPage(id, images, prevPage, nextPage):
    TABLE_COLS=3
    col=0
    content="<html><head><title>Virginia Tech Camera Images from Spacecraft " 
    content=content + getFoxName(id)+"</title>"
    content=content+"""
<link rel="stylesheet" type="text/css" media="all" href="http://www.amsat.org/wordpress/wp-content/themes/generatepress/style.css />
                  </head>
                  <body>
                  <img src='http://www.amsat.org/wordpress/wp-content/uploads/2014/08/amsat2.png'> """
    1ontent=content+"<h1 class=entry-title>Virginia Tech Camera Images from Spacecraft " + getFoxName(id) + "</h1>"
    if (prevPage != ""):
        content=content+"<a href=" + prevPage + "> &lt; Newer Images</a> |"
    else:
        content=content +"&lt; Newer Images | "
    if (nextPage != ""):
        content=content+"<a href=" + nextPage + "> Older Images &gt;</a><br>"
    else:
        content=content +"Older Images &gt;<br>"

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
    print("Files copied: " + str(filesCopied))
    return filesCopied

# Write the webpage into the web server directory
def writeWebpage(webDir, page, name):
    webPage = open(webDir + os.sep + name, "w")
    webPage.write(page)
    webPage.close()

def makePageName(page_num, total):
    if (page_num == total):
        page_idx = ""
    else:
        page_idx = str(page_num)
    name = "index" + page_idx + ".html"
    return name


def processPage(id, name, prevPage, nextPage, images_for_page, webDir):
        print("Debug: Building Page: " + name + ": prev:" + prevPage + " next:" + nextPage)
        images_for_page = sorted(images_for_page, reverse=True)
        page = buildPage(id, images_for_page, prevPage, nextPage)
        writeWebpage(webDir, page, name)

# Build a set of pages, 9 images per page, with back/forward buttons to link each page
# Index is the default page for the latest
# Other pages are numbered with index1 being the oldest and indexN the newest archive page
def buildPageSet(id, webDir, images):
    IMAGES_ON_PAGE=6
    img_num = 0
    total_pages = len(images) / IMAGES_ON_PAGE
    if (((len(images)) % IMAGES_ON_PAGE) == 0):
        # We have an exact number
        total_pages = total_pages - 1
    page_num = 0
    olderPage = "" # older images
    newerPage = "" # newer images
    images_for_page = []
    print("We need " + str(total_pages) + " pages")
    for item in images:
        print("Debug: Building page_num: " + str(page_num) + " of " + str(total_pages) + " Checking: " + item)
        images_for_page.append(item)
        img_num = img_num + 1
        if (img_num % IMAGES_ON_PAGE == 0):
            name = makePageName(page_num, total_pages)
            if (page_num < total_pages):
                # we are on a page that is not the last page, so there is going to be a newer page
                newerPage = makePageName(page_num + 1, total_pages)
            else:
                newerPage = ""
            if (img_num < len(images)):
                # Then there is still at least one image left to go on index page, so build this one
                if (page_num > total_pages-3 or not os.path.isfile(webDir + os.sep + name)):
                        print("This page does not exist, creating: " + webDir + os.sep + name) 
                        processPage(id, name, newerPage, olderPage, images_for_page, webDir)
                olderPage = name
                page_num = page_num + 1
                images_for_page = []
    print("img_num: " + str(img_num) + " of " + str(len(images)))
    print("Debug: Building index page: " + str(page_num) + " Checking: " + item)
    # we still need to build the index page
    name = makePageName(total_pages, total_pages)
    newerPage=""
    processPage(id, name, newerPage, olderPage, images_for_page, webDir)

def processSat(id, imageDir, webDir):
    images = parseImageDir(id, imageDir)    
    num = copyImages(images,webDir)
    if (num > 0):
        buildPageSet(id, webDir, images)

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
        time.sleep(5)

main()
