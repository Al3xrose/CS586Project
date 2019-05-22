import csv, psycopg2

try:
    conn = psycopg2.connect("dbname='alex' host='localhost' user='alex' password='passwd123'")
    cur = conn.cursor()
except:
    print('Couldn\'t connect to database!')
    exit(1)

with open('Seatac20042005.csv', mode='r') as csv_file:
    csv_reader = csv.DictReader(csv_file)

    for row in csv_reader:
        if row['TMIN'] == '':
            row['TMIN'] = None 
        if row['TMAX'] == '':
            row['TMAX'] = None
        if row['PRCP'] == '':
            row['PRCP'] = None
        cur.execute("""INSERT INTO weather VALUES
                        ('Seatac', 'WA', %s, %s, %s, %s)""", 
                        (row['DATE'], row['TMIN'], row['TMAX'], row['PRCP'])) 
    conn.commit()
    cur.close()
    conn.close()
