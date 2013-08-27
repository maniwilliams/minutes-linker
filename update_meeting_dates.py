#!/usr/bin/python

import csv, sqlite3, os.path, sys

if len(sys.argv) > 1:
    sqlitefile = sys.argv[1]
else:
    sqlitefile = 'test.sqlite'

print("Updating database {0}...".format(sqlitefile))

meetings = ['meeting1.csv',
            'meeting2.csv',
            'meeting3.csv',
            'meeting4.csv',
            'meeting5.csv',
            'meeting6.csv',
            'meeting7.csv',
            'meeting8.csv',
            'meeting9.csv',
            'meeting10.csv',
            'meeting11.csv',
            'meeting12.csv',
            'meeting13.csv',
            'meeting14.csv',
            'meeting15.csv',
            'meeting16.csv',
            'meeting17.csv',
            'meeting18.csv']

con = sqlite3.connect(sqlitefile)
con.row_factory = sqlite3.Row

cur = con.cursor()

con.text_factory = str
meeting_count = 1

for filename in meetings:
    with open(filename, 'r') as csvfile:
        print("Updating {0}...".format(filename))
        f = csv.reader(csvfile, delimiter=',', quotechar='"')
        line = 0
        last_topic = ''
        for row in f:
            line = line + 1
            if line == 1:
                meeting_id = meeting_count
                group_name = row[0]
                title = row[1]
                caller = row[3]
            elif line == 2:
                date = row[1]
                participants = row[3]

                data = [date, meeting_id]
                cur.execute('update meetings set date =?  where id = ?', data)
		meeting_count = meeting_count + 1
            elif line == 3:
                pass

              
con.commit()
