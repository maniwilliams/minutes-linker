#!/usr/bin/python

import csv, sqlite3, os.path, sys

if len(sys.argv) > 2:
    sqlitefile = sys.argv[1]
    skip_argv = 2
else:
    sqlitefile = 'test.sqlite'
    skip_argv = 1

con = sqlite3.connect(sqlitefile)
con.row_factory = sqlite3.Row

cur = con.cursor()
print(skip_argv)
print('Number of meeting minutes to delete: {} '. format(len(sys.argv)-skip_argv))
for i in range(skip_argv, len(sys.argv)):
    meeting_number = int(sys.argv[i])
    print('Deleting meeting: {} ...'. format(meeting_number))

    #remove the items with the given id
    cur.execute('update meetings set category = 1 where id = ?', (meeting_number,))
    #cur.execute('delete from meetings where id = ?', (meeting_number,))
    #cur.execute('delete from items where meeting_id = ?', (meeting_number,))
    #decrement items with id>given id
    #cur.execute('update meeting set id = id-1 where id > ?', (meeting_number,))
    #cur.execute('update items set meeting_id = meeting_id-1 where meeting_id > ?', (meeting_number,))
con.commit()
