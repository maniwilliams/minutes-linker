#!/usr/bin/python

import csv, sqlite3, os.path, sys

sqlitefile = 'minutes.sqlite'

if os.path.isfile(sqlitefile):
    print("Database already exists. Please delete it.")
    sys.exit()

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

cur.executescript("""
    create table meetings (
        id integer primary key,
        group_name text,
        title text,
        date text,
        caller text,
        participants text
    );
    create table items (
        id integer primary key,
        meeting_id integer,
        topic text,
        item text,
        action text,
        person text,
        related text
    );
        """)

con.text_factory = str

for filename in meetings:
    with open(filename, 'r') as csvfile:
        print("Importing {0}...".format(filename))
        f = csv.reader(csvfile, delimiter=',', quotechar='"')
        line = 0
        last_topic = ''
        for row in f:
            line = line + 1
            if line == 1:
                meeting_id = None
                group_name = row[0]
                title = row[1]
                caller = row[3]
            elif line == 2:
                date = row[1]
                participants = row[3]
                
                #print "{0} - {1}".format(group_name, title)
                #print "Date: {0}\nCalled By: {1}".format(date, caller)
                #print "Participants: {0}\n".format(participants)
                data = [meeting_id, group_name, title, date, caller, participants]
                cur.execute('insert into meetings values (?,?,?,?,?,?)', data)
                meeting_id = cur.lastrowid
            elif line == 3:
                pass
            else:
                item_id = None
                topic = row[0]
                item = row[1]
                action = row[2]
                person = row[3]
                related = None
                if topic == '':
                    topic = last_topic
                if item == '':
                    item = 'n.a.'
                if action == '':
                    action = 'n.a.'
                if person == '':
                    person = 'n.a.'
                last_topic = topic
                data = [item_id, meeting_id, topic, item, action, person, related]
                cur.execute('insert into items values (?,?,?,?,?,?,?)', data)
            #print "{0} {1} {2} {3}".format(topic, item, action, person)

cur.execute('alter table items add column importance integer')
cur.execute('alter table meetings add column category integer')
		
con.commit()
