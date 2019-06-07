import csv, psycopg2, sys, datetime

cityId = {"Portland": 1,
          "Seattle": 2,
          "Anchorage": 3,
          "LosAngeles": 4,
          "NewYork": 5,
          "Honolulu": 6}

cityStation = {"Portland":410510080,
               "Anchorage":20200018,
               "Honolulu":150030010,
               "LosAngeles":60371103,
               "NewYork":340030003,
               "Seattle":530330080}
if len(sys.argv) < 2:
    print("Usage: importAirQ.py <cityname> <csvfile>")
    exit(1)


try:
    conn = psycopg2.connect("dbname='s19wdb31' host='dbclass.cs.pdx.edu' user='s19wdb31' password='Alexdbcla$$'")
    cur = conn.cursor()
except:
    print('Couldn\'t connect to database!')
    exit(1)
dates = []
with open(sys.argv[2], mode='r') as csv_file:
    csv_reader = csv.DictReader(csv_file)

    print('Opened csv')
    #loc_id, airquality_date, pollutant, measurement, unit

    for row in csv_reader:
        date = datetime.datetime.strptime(row['Date'], '%m/%d/%Y').date().strftime('%Y-%m-%d')
        if int(row['Site ID']) == int(cityStation[sys.argv[1]]):
            print("""INSERT INTO CityInfo.AirQuality VALUES
                        (%s, %s, %s, %s, %s)""", 
                        (cityId[sys.argv[1]], date, 'PM2.5', row['Daily Mean PM2.5 Concentration'], row['UNITS'])) 
            if row['Date'] not in dates:
                dates.append(row['Date'])
                cur.execute("""INSERT INTO CityInfo.AirQuality VALUES
                        (%s, %s, %s, %s, %s)""", 
                        (cityId[sys.argv[1]], date, 'PM2.5', row['Daily Mean PM2.5 Concentration'], row['UNITS'])) 
    conn.commit()
    cur.close()
    conn.close()
