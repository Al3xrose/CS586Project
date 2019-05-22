import psycopg2

conn = psycopg2.connect("dbname='alex' host='localhost' user='alex' password='passwd123'")

cursor = conn.cursor()

postgresql_select_query = "select * from weather"
print("executing select * from weather")
cursor.execute(postgresql_select_query)
records = cursor.fetchall()
print(records)
