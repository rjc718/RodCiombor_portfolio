# Author:   Rod Ciombor
# Date:     10/17/2025
# Class:    CIS2531-NET01
# Descr:
#   Program prompts user to enter filename
#   containing information on student names and grades.
#   Groups students by letter grade, and displays text output
#   Also displays data as a bar chart.

import matplotlib.pyplot as plt

DELIMITER = ', '
NAME_INDEX = 0
POINTS_INDEX = 1
GRADE_RANGES = (
    ('A', 900.0, 1000.0),
    ('B', 800.0, 899.9),
    ('C', 700.0, 799.9),
    ('D', 600.0, 699.9),
    ('F', 0.0, 599.9)
)

def getStudentData():
     #Create and open output file if valid name given
    isValid = False
    while not isValid:
        fileName = str(input('Enter the filename to read student total points: '))
        try:
            inputFile = open(fileName, 'r')
        except FileNotFoundError:
            print('ERROR! Problem opening file!')
        else:
            isValid = True

    studentList = []

    for line in inputFile:

        fieldList = line.split(DELIMITER)

        studentName = fieldList[NAME_INDEX]
        studentPoints = fieldList[POINTS_INDEX]

        student = []
        student.append(studentName)
        student.append(studentPoints)
        studentList.append(student)
  
    #Close file 
    inputFile.close()

    return studentList

def groupStudentsByGrade(studentData, gradeRanges):
    studentsByGrade = [
        [], [], [] ,[] ,[]
    ]
    for student in studentData:
        studentName = student[NAME_INDEX]
        studentPoints = float(student[POINTS_INDEX])

        rowNum = 0
        for row in gradeRanges:
            upperRange = row[2]
            lowerRange = row[1]
            if studentPoints >= lowerRange and studentPoints <= upperRange:
                studentsByGrade[rowNum].append(studentName)
            rowNum = rowNum + 1
            
    return studentsByGrade

def countTotalStudents(studentsByGrade):
    students = 0;
    for row in studentsByGrade:
        for elem in row:
            students = students + 1
    return students

def displayOutput(studentsByGrade, gradeRanges):
    
    totalStudents = countTotalStudents(studentsByGrade)
    print()
    print(f'Total number of students in file: {totalStudents}')
    
    rowNum = 0
    for row in studentsByGrade:
        letterGrade = gradeRanges[rowNum][0]
        studentCount = len(row)
        rowNum = rowNum + 1

        countStr = f'Count of students with {letterGrade} = {studentCount};'
        percentStr = f'Percent of students with {letterGrade} = {(studentCount / totalStudents) * 100:.1f}%'
        print(countStr, percentStr)
        
        if(studentCount > 0):
            nameList = ''
            for name in row:
                if nameList:  # if not empty, add comma
                    nameList += ', '
                nameList += f'{name}'
            print(f'\tStudent Names: {nameList}')

def displayBarGraph(studentsByGrade, gradeRanges):
    barWidth = 8

    # Heights of each bar
    heights = [len(row) for row in studentsByGrade]

    # Compute bar centers dynamically
    xTickValues = [i*10 + 5 for i in range(len(gradeRanges))]  # centers
    xTickLabels = [row[0] for row in gradeRanges]

    # Plot bars centered on tick positions
    plt.bar(xTickValues, heights, width=barWidth, color=('g', 'b', 'c', 'y', 'r'), align='center')

    # Set x-axis labels at the same positions
    plt.xticks(xTickValues, xTickLabels)

    plt.title('Student Grade Report')
    plt.xlabel('Student Count')
    plt.ylabel('Grade')

    yTickValues = (0.0, 0.5, 1.0, 1.5, 2.0, 2.5, 3.0, 3.5, 4.0, 4.5)
    yTickLabels = (0.0, 0.5, 1.0, 1.5, 2.0, 2.5, 3.0, 3.5, 4.0, 4.5)
    plt.yticks(yTickValues, yTickLabels)

    plt.show()
    
def main():

    studentData = getStudentData()
    studentsByGrade = groupStudentsByGrade(studentData, GRADE_RANGES)
    displayOutput(studentsByGrade, GRADE_RANGES)
    displayBarGraph(studentsByGrade, GRADE_RANGES)


if __name__ == '__main__':
    main()

# ***OUTPUT***
#
# Enter the filename to read student total points: sdfsfd.txt
# ERROR! Problem opening file!
#
# Enter the filename to read student total points: CIS1150.txt
# 
# Total number of students in file: 13
# Count of students with A = 3; Percent of students with A = 23.1%
# 	Student Names: Sophia Harris, Jackson Clark, Isabella Martinez
# Count of students with B = 4; Percent of students with B = 30.8%
# 	Student Names: Ava Turner, Noah Brown, Aiden Walker, Harper Thomas
# Count of students with C = 4; Percent of students with C = 30.8%
# 	Student Names: Emma Wilson, Ethan Miller, Olivia Smith, Caleb Anderson
# Count of students with D = 1; Percent of students with D = 7.7%
# 	Student Names: Liam Davis
# Count of students with F = 1; Percent of students with F = 7.7%
# 	Student Names: Mia Garcia
#
# Enter the filename to read student total points: CIS2531.txt
# 
# Total number of students in file: 10
# Count of students with A = 4; Percent of students with A = 40.0%
# 	Student Names: Jackson Davis, Ava Johnson, Mia Harris, Isabella Brown
# Count of students with B = 3; Percent of students with B = 30.0%
# 	Student Names: Sophia Turner, Liam Foster, Ethan Clark
# Count of students with C = 3; Percent of students with C = 30.0%
# 	Student Names: Olivia Martinez, Noah Wilson, Lucas Miller
# Count of students with D = 0; Percent of students with D = 0.0%
# Count of students with F = 0; Percent of students with F = 0.0%
