#!/usr/bin/python

# Display a page with a single image and data about it
# Support zoom levels

import sys
import os
import urllib2

def getFoxName(id):
    names = ("AO-85 (Fox-1A)","RadFxSat (Fox-1B)","Fox-1Cliff","Fox-1D","Fox-1E")
    return names[int(id)-1]


# Make a sub page for the image, where the image is larger
def makeImagePage(id, pic, picId, reset, uptime, zoom, mapZoom):
    webDir = os.path.dirname(pic)
    requestUrl1='http://127.0.0.1:8080/getSatUtcAtResetUptime?sat='+str(id)+'&reset='+str(reset)+'&uptime='+str(uptime)
    time = urllib2.urlopen(requestUrl1).read()
    title = "Pic: " + picId + " : " + reset + " / " + uptime + "<br>" + time
    requestUrl='http://127.0.0.1:8080/getSatLatLonAtResetUptime?sat='+str(id)+'&reset='+str(reset)+'&uptime='+str(uptime)
    r = urllib2.urlopen(requestUrl).read()
    #print("DEBUG position: " + r)
    latLon = r.split(",")
    lat = 0
    lon = 0
    if (len(latLon) == 2):
        lat=latLon[0]
        lon=latLon[1]

    content="<html><head><title>Virginia Tech Camera Images from Spacecraft "
    content=content + getFoxName(id)+"</title>"
    with open('head.php', 'r') as f:
        head = f.readlines()
    for line in head:
        content=content+line

    content=content+"""
    <style>
    table, th, td, tr {
    border : none;
    border-collapse: collapse;
    }
    </style>
    </head>
    <body>
    <img src='http://www.amsat.org/wordpress/wp-content/uploads/2014/08/amsat2.png'> """
    content=content+"<h2 class=entry-title>Virginia Tech Camera Image: "+title+ " from Spacecraft " + getFoxName(id) + "</h2>"
    content=content+"""
    <div>
"""
    height = 240
    width = 420
    height = height * zoom
    width = width * zoom
    with open('api_key', 'r') as f:
        apiKey = f.readline().strip()
    command = "showSubPage.php?id=" + str(id) + "&image=" + pic + "&pc=" + picId + "&reset=" + reset + "&uptime=" + uptime + "&mapZoom="+str(mapZoom)
    content = content + "<div class='col-1;' style='float:left; z-index: 1000;'><br><a href=showImages.php?id=" + id+ ">Back to Index</a> | "
    if (zoom > 1):
        content = content + "<a href=" + command+ "&zoom="+str(zoom/2) + ">Smaller</a> | "
    else:
        content = content + "Smaller | "
    if (zoom < 16):
        content = content + "<a href=" + command+ "&zoom="+str(zoom*2) + ">Larger</a>"
    else:
        content = content + "Larger"
    imgTag1='<img style="border:10px solid black;" height='+str(height)+' width='+str(width)+' src="'
    imgTag2='"alt="Image from spacecraft '+getFoxName(id)+'" /><figcaption>'
    imgTag3='</figcaption>>'
    content = content + "<p>"
    content = content + "<a href=" + command + "&zoom="+str(zoom*2) + ">"
    content = content + imgTag1 + pic + imgTag2 + title
    content = content + "</a>"
    selfUrl = "/tlm/showSubPage.php?id=" + str(id) + "&image=" + pic + "&pc=" + picId + "&reset=" + reset + "&uptime=" + uptime + "&zoom="+str(zoom) + "&mapZoom="+str(mapZoom)

    content = content + "</div>"
    content = content + "<div class='col-2;' style='float:right;' text-align:center;'><div>Spacecraft sub-point at image aquisition: "+str(lat) + ", " + str(lon) + "<br>"
    commandTime = "showSubPage.php?id=" + str(id) + "&image=" + pic + "&pc=" + picId + "&zoom="+str(zoom)+ "&mapZoom="+str(mapZoom)
    commandMap = "showSubPage.php?id=" + str(id) + "&image=" + pic + "&pc=" + picId + "&reset=" + reset + "&uptime=" + uptime +  "&zoom="+str(zoom)
    content = content + "Push uptime <a href=" + commandTime+  "&reset=" + reset + "&uptime=" + str(int(uptime) - 10) +">10 Sec Earlier</a> | "
    content = content + "<a href=" + commandTime+  "&reset=" + reset + "&uptime=" + str(int(uptime) + 10) +">10 Sec Later</a> | "
    content = content + "<a href=" + commandMap+ "&mapZoom="+str(mapZoom+1) + ">Zoom In</a> | "
    content = content + "<a href=" + commandMap+ "&mapZoom="+str(mapZoom-1) + ">Zoom Out</a>"
    content = content + "<a href=" + commandMap + "&mapZoom="+str(mapZoom+1) + "></div>"
    content = content + '<img style="border:15px solid white;" src="https://maps.googleapis.com/maps/api/staticmap?'
    content = content + "zoom=" + str(mapZoom) + "&size=640x500&maptype=terrain"
    content = content + "&markers=color:red%7Clabel:X%7C"+str(lat)+","+str(lon)
    content = content + '&key='+apiKey+'">'
    content = content + "</a>"
    content = content + """
</div>
</div>
"""
    content = content + """
<div style='clear:both;'>
<table>
<tr>
<br>
<b>Enter Comments Here:</b>
<br>
<form method="post" action="/tlm/Comment.php">
<td colspan="2">
<textarea rows="3" cols="80" wrap="physical" name="comments"></textarea>
"""
    baseImage = pic.split('?')
    content = content + '<input type="hidden" name="file" value="' + baseImage[0] + '" /><br>'
    content = content + '<input type="hidden" name="url" value="' + selfUrl + '" /><br>'
    content = content + """
<tr valign=top>
<td>
<b>Name / Ground station name: </b>
<textarea rows="1" cols="30" wrap="physical" name="name"> </textarea>
<input style="border:10px solid white;" type="submit" value="Submit">
</td>
<td>
The third planet from the sun is called what?
<textarea rows="1" cols="30" wrap="physical" name="question"> </textarea><br>
<b>Answer this question to help prevent spam: </b><br>
(one word, not case sensitive)
<input type="hidden" name="answer" value="1" /><br>

</td>
</tr>
</table>
</form>
</div>
<h2>Comments on this Image </h2>
<div style='clear:both;'>
<table>
"""
    if (os.path.isfile(baseImage[0]+'.comments.html')): 
        with open(baseImage[0]+'.comments.html', 'r') as f:
            lines = f.readlines()
        for line in lines:
            content = content + line 
    else:
        content = content + "No Comments So Far"

    content = content + "</table></div></div></body></html>"
    return content;


def main():
    # We expect some command line params
    if len(sys.argv) < 7:
        print ('Usage: '+sys.argv[0]+'<foxId> <image> <pc> <reset> <uptime> <zoom> [-map]')
        sys.exit(1)
    id = sys.argv[1]
    pic = sys.argv[2]
    # Pic has been wrapped with quotes to prevent shell security risk. Need to remove them
    pic = pic.replace("'", "")
    pc = sys.argv[3]
    reset = sys.argv[4]
    uptime = sys.argv[5]
    zoom = int(sys.argv[6])
    if (zoom > 16):
        zoom = 16
    mapZoom = 5
    if len(sys.argv) == 8:
        mapZoom = int(sys.argv[7])
    page = makeImagePage(id, pic, pc, reset, uptime, zoom, mapZoom)
    print(page)

main()
