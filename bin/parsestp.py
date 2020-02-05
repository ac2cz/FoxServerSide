#!/usr/bin/python
# Parse the logfwd file into stp files
# g0kla@arrl.net

import sys

if len(sys.argv) <= 1:
    print ('Usage: convert <fileName.log>')
    print ('Seperates logfwd into stp files')
    sys.exit(1)
    
fileName = sys.argv[1]
print ('Processing file: ' + fileName)

with open(fileName, "rb") as f:
    content = f.readlines()

i = 0
outfile = open('file' + str(i) +'.stp' , "wb" )

for line in content:
    try:
        forward = line[0:10].decode()
        #print (forward)
        if forward == 'Forwarding':
            outfile.close()
            #new stp file
            print ('Creating new stp file: file' +  str(i) + '.stp')
            outfile = open('file'+str(i)+'.stp' , "wb" )
            i += 1
            outfile.write(line[13:])
    except:
        pass
    outfile.write(line)
