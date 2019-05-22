import csv

with open('SeattleWeather20042006.csv') as csv_file:
    csv_reader = csv.reader(csv_file, delimiter=',')
    line_count = 0
    for row in csv_reader:
        if line_count == 0:
            print(f'Column name are {", ".join(row)}')
            line_count += 1
        else:
            text = f"\t{row[0]}\t{row[1]}\t{row[2]}\t{row[11]}\t{row[12]}"
            print(text)
            line_count += 1
    text = f"Processed {line_count} lines."
    print(text)
