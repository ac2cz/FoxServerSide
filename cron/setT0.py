#!/usr/bin/python

import datetime
import mysql.connector

cnx = mysql.connector.connect(user='foxrpt', password='amsatfox', database='FOXDB')
cursor = cnx.cursor()

query = ("SELECT resets, uptime, receiver, stpDate FROM STP_HEADER "
         "WHERE date_time BETWEEN %s AND %s")

start = datetime.date(2019, 10, 14)
end = datetime.date(2019, 10, 15)

cursor.execute(query, (start, end))

for (resets, uptime, receiver, stpDate) in cursor:
  print( str(resets), str(uptime), str(receiver), str(stpDate))

cursor.close()
cnx.close()
