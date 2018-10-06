#!/usr/bin/python

# Generate a page of thumbnails given a start index
# The index is the filename

import sys
import glob
import os
import urllib2
import time


def getFoxName(id):
    names = ("AO-85 (Fox-1A)","RadFxSat (Fox-1B)","Fox-1Cliff","Fox-1D","Fox-1E")
    return names[int(id)-1]

def processPage(id, prevPage, nextPage, images, webDir):
    TABLE_COLS=3
    col=0
    content="<html><head><title>Virginia Tech Camera Images from Spacecraft "
    content=content + getFoxName(id)+"</title>"
    content=content+"""
<link rel='stylesheet' type='text/css'
media="all" href="/wordpress/wp-content/themes/generatepress/style.css" >
    <style>
table, th, td, tr {
    border : none;
    border-collapse: collapse;
}

</style>
    <table>
    <tr>
    <td>
    </head>
    <body>
    <img src='http://www.amsat.org/wordpress/wp-content/uploads/2014/08/amsat2.png'> """
    content=content+"<h1 class=entry-title>Virginia Tech Camera Images from Spacecraft " + getFoxName(id) + "</h1>"
    if (prevPage != ""):
        content=content+"<a href=showImages.php?id="+id+"&start=" + prevPage + "&reverse=reverse> &lt; Newer Images</a> |"
    else:
        content=content +"&lt; Newer Images | "
    if (nextPage != ""):
        content=content+"<a href=showImages.php?id="+id+"&start=" + nextPage + "> Older Images &gt;</a><br>"
    else:
        content=content +"Older Images &gt;<br>\n"

    imgTag1='<figure><img style="border:10px solid black;" width="320" height="240" src="'
    imgTag2='"alt="Image from spacecraft '+getFoxName(id)+'" /><figcaption>'
    imgTag3='</figcaption></figure>'

    content=content+"<table>\n<tr>"
    for item in images:
        pic = os.path.basename(item)
        fileSize = os.path.getsize(item)
        params = pic.split('_')
        reset=params[1]
        uptime=params[2]
        picId=params[3].split('.')[0]
        if (col % TABLE_COLS == 0):
            content = content + "</tr><tr>"
            col=0;
        content = content + "<td>"
        requestUrl1='http://127.0.0.1:8080/getSatUtcAtResetUptime?sat='+str(id)+'&reset='+str(reset)+'&uptime='+str(uptime)
        time = urllib2.urlopen(requestUrl1).read()
        title = "Pic: " + picId + " : " + reset + " / " + uptime + "<br>" + time 
        name = item + "?"+str(fileSize) 
        command = "showSubPage.php?id=" + str(id) + "&image=" + name + "&pc=" + picId + "&reset=" + reset + "&uptime=" + uptime + "&zoom=1&mapZoom=5"
        content = content + "<a href="+command+">"+imgTag1 +name + imgTag2 + title + imgTag3 + "</a>"
        content = content + "</td>\n"
        col=col+1
    content = content + "</tr>\n</table>\n"
    if (prevPage != ""):
        content=content+"<a href=showImages.php?id="+id+"&start=" + prevPage + "&reverse=reverse> &lt; Newer Images</a> |"
    else:
        content=content +"&lt; Newer Images | "
    if (nextPage != ""):
        content=content+"<a href=showImages.php?id="+id+"&start=" + nextPage + "> Older Images &gt;</a><br>"
    else:
        content=content +"Older Images &gt;<br>\n"

    content = content + "</body></html>"

    return content


def makePageName(page_num, total):
    if (page_num == total):
        page_idx = ""
    else:
        page_idx = str(page_num)
    name = "index" + page_idx + ".html"
    return name

def buildPageSet(id, imageDir, images, startImage, reverse):
    IMAGES_ON_PAGE=6
    img_num = 0
    olderPage = "" # older images
    newerPage = "" # newer images
    images_for_page = []
    found = False
    if (startImage == ""):
        found = True
    for item in images:
        pic = os.path.basename(item)
        #print("Debug: Checking: " + pic)
        if (img_num == IMAGES_ON_PAGE):
            olderPage = pic
            break
        if (pic == startImage):
             found = True
        else: 
            if (not found):
                newerPage = pic  # This might be the previous page link
        if (found):
            images_for_page.append(item)
            img_num = img_num + 1
    # On the page we want the newest first, so we reverse the sub list
    if (reverse):
        images_for_page = list(reversed(images_for_page))
        new = olderPage
        olderPage = newerPage
        newerPage = new
    page = processPage(id, newerPage, olderPage, images_for_page, imageDir)
    return page

# Read all the images in the passed diretory and return as a sorted list
def parseImageDir(satId, imageDir):
    match = str(satId) + "_*jpg"
    images = sorted(glob.glob(imageDir + os.sep + match), key=os.path.getmtime)
    return images

def main():
    # We expect exactly one command line argument, the directory that contains the image files
    if len(sys.argv) <= 2:
        print ('Usage: '+sys.argv[0]+' <foxId> <image_dir> [<startImage>] [reverse]')
        print ('Copy the image files extracted from the SQL database to website')
        sys.exit(1)
    satId = sys.argv[1]
    imageDir = sys.argv[2]
    startImage = ""
    if (len(sys.argv) >= 4):
        startImage = sys.argv[3]
    reverse = False
    direction = ""
    if (len(sys.argv) == 5):
        direction = sys.argv[4]
    if (direction == "reverse"):
        reverse = True
    images = parseImageDir(satId, imageDir)
    if (not reverse):
        images = list(reversed(images))
    page = buildPageSet(satId, imageDir, images, startImage, reverse)
    print(page)

main()
