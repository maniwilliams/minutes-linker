#!/usr/bin/python

import csv, sqlite3, os.path, sys



sqlitefile = 'related.sqlite'

#if os.path.isfile(sqlitefile):
#    print("Database already exists. Please delete it.")
#    sys.exit()

con = sqlite3.connect(sqlitefile)
con.row_factory = sqlite3.Row
con.text_factory = str

cur = con.cursor()

# link is a table of list
cur.execute("drop table if exists links") 
cur.executescript("""
    create table links (
        item integer,
        meeting integer,
        link integer
    );
        """)

# nodes is a table of item connections
cur.execute("drop table if exists nodes")
cur.executescript("""
    create table nodes (
        item integer,
        meeting integer,
        related text
    );
        """)

sqlitefile_org = 'minutes.sqlite' #source database
con_org = sqlite3.connect(sqlitefile_org)
cur_org = con_org.cursor()
cur_org.execute("select * from items where importance = 1")
rows = cur_org.fetchall() # get all important items

for row in rows:
    meeting = row[1]
    cur_org.execute("select category from meetings where id = ?", (meeting,))
    result = cur_org.fetchone()
    if(result != 1): # skip ones from the meetings that labeled skip
        item = row[0]
	related = row[6]
	print(item)
        data = [item, meeting, related]
        cur.execute('insert into nodes values (?,?,?)', data) 
	if not related: # if no link then link to itself
	    data = [item, meeting, item]
            cur.execute('insert into links values (?,?,?)', data)	
        else:
	    num = [int(s) for s in related.split() if s.isdigit()]
	    for i in num:
	        data = [item, meeting, i]
                cur.execute('insert into links values (?,?,?)', data) 	

con.commit()
cur_org.close()
cur.close()
