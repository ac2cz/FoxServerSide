#!/usr/bin/python
# Convert the log files extracted from the SQL database into SegDb files
# g0kla@arrl.net

import sys
import os
import glob
import errno

MAX_SEGMENT_SIZE = 1000;
DB="FOXDB";
dir=".";
def processLog(sat, logFile): 	
    "This processes a log file and splits it into SegDb segments"
    linesAdded = 0;
    with open(dir+os.sep+sat+logFile+".log", "r") as f:
        idx = open(DB+os.sep+sat+logFile+".idx", "w")
        for line in f:
	    fields = line.split(',')
            if linesAdded == MAX_SEGMENT_SIZE:
                idx.write(str(segReset)+","+str(segUptime)+","+str(linesAdded)+","+str(segFilename)+"\n")
                linesAdded = 0
                seg.close()
            if linesAdded == 0:
                #First Line of a new segment
                segReset = fields[2]
                segUptime = fields[3]
                segFilename = sat+logFile+ "_" + segReset + "_"+ segUptime +".log"
                seg = open(DB+os.sep+segFilename, 'w')
            seg.write(line)
            linesAdded += 1
        if linesAdded > 0:
            idx.write(str(segReset)+","+str(segUptime)+","+str(linesAdded)+","+str(segFilename)+"\n")
        idx.close()
        f.close()

def processType(match):
    logs = glob.glob(dir + os.sep + match)
    #if len(logs) == 0:
    #    print ('No log files found in ' + dir + 'for: ' + match)
    for item in logs:
        log = os.path.basename(item)
        id=log[0:4]
        type=log[4:]
        type=type.split('.')[0]
        #print ('ID: '+id)
        #print ('TYPE: '+type)
        processLog(id, type)


def makeDir(path):
    "This creates a directory if it does not exist"
    try:
        os.makedirs(path)
    except OSError as exception:
        if exception.errno != errno.EEXIST:
            raise

# We expect exactly one command line argument, the directory that contains the log files
if len(sys.argv) <= 1:
    print ('Usage: '+sys.argv[0]+' <dir>')
    print ('Convert the log files extracted from the SQL database into SegDb files')
    sys.exit(1)

# We process all files in the directory that end with the names for the log files
dir = sys.argv[1]
#print ('Processing logfiles in dir: ' + dir)
DB = dir + os.sep + DB
makeDir(DB)
processType("*rttelemetry.log")
processType("*maxtelemetry.log")
processType("*mintelemetry.log")
processType("*radtelemetry.log")
processType("*radtelemetry2.log")
processType("*wodtelemetry.log")
processType("*wodradtelemetry.log")
