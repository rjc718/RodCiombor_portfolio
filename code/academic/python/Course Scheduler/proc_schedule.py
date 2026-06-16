# Author:   Rod Ciombor
# Date:     11/06/2025
# Class:    CIS2531-NET01
# Descr:
#   Module contains a series of functions to add and remove
#   course and section numbers to a schedule in the form of a dictionary.
#   Also contains functions to read and write them to and from file after serialization.

import pickle

def getUserInput(msg):
    '''Prompts the user for input, strips leading and trailing whitespace,
    and converts the input to uppercase.

    Parameters:
    msg --> string prompt to display to the user

    Returns:
    Processed string in uppercase with no leading or trailing whitespace
    '''
    string = str(input(msg))
    return string.strip().upper()

def load_schedule_from_file(schedule):
    '''Loads a schedule dictionary from a pickle file, optionally clearing the current schedule if it contains courses.

    Prompts the user for confirmation before clearing existing courses.
    Handles errors such as file not found, empty or incomplete file, and general file access issues.

    Parameters:
    schedule --> dictionary representing the current schedule to be updated

    Returns:
    None; the function updates the passed-in dictionary in place
    '''

    if len(schedule) > 0:
        confirm = input('Current schedule contains active courses. \nDo you wish to clear existing courses and continue (Y/N) ? ')
        if confirm.upper() != 'Y':
            print('Schedule not updated!')
            return
        else:

            isValid = False
    
            while not isValid:
                fileName = str(input('Enter the filename to load the schedule from: '))
                try:
                    with open(fileName, 'rb') as inputFile:
                        loaded = pickle.load(inputFile)
                        schedule.clear()
                        schedule.update(loaded)
                except FileNotFoundError:
                    print('ERROR! File not found.')
                except EOFError:
                    print('ERROR! File is empty or incomplete!')
                except OSError:
                    print('ERROR! Problem opening or reading the file.')
                else:
                    print(f'Schedule successfully loaded from {fileName}')
                    isValid = True

def add_course_number(schedule):
    '''Prompts the user to add a new course number to the schedule.

    Checks if the course already exists and prevents duplicates.
    If the course does not exist, adds it as a key with an empty list of sections.

    Parameters:
    schedule --> dictionary representing the current schedule to be updated

    Returns:
    None; the function updates the passed-in dictionary in place
    '''
    proceed = False

    while not proceed:
    
        course = getUserInput("Please enter a course number to add: ")
        keys = schedule.keys()
        
        if course in keys:
            print("This course has already been added to the schedule!")
        else:
            schedule.update({course: []})
            print(f"Course number {course} has been successfully added to the schedule!")
            return

def rem_course_number(schedule):
    '''Prompts the user to remove an existing course number from the schedule.

    Checks if the course exists before attempting removal and notifies the user if it does not.
    If the course exists, deletes it from the schedule dictionary.

    Parameters:
    schedule --> dictionary representing the current schedule to be updated

    Returns:
    None; the function updates the passed-in dictionary in place
    '''
    proceed = False

    while not proceed:

        course = getUserInput("Please enter a course number to remove: ")
        keys = schedule.keys()
        
        if course not in keys:
            print("This course does not currently exist in the schedule!")
        else:
            del schedule[course]
            print(f"Course number {course} has been successfully removed from the schedule!")
            return

def add_course_section(schedule):
    '''Prompts the user to add a section to an existing course in the schedule.

    Checks that the course exists and prevents duplicate sections.
    If the section does not already exist, appends it to the list of sections for the specified course.

    Parameters:
    schedule --> dictionary representing the current schedule to be updated

    Returns:
    None; the function updates the passed-in dictionary in place
    '''
    proceed = False

    while not proceed:
     
        course = getUserInput("Please enter a course number to add a section to: ")
        keys = schedule.keys()

        if course not in keys:
            print("This course does not currently exist in the schedule!")
        else:
            while not proceed:
            
                section = getUserInput("Please enter a section number to add: ")

                if section in schedule[course]:
                    print("This section already exists in the schedule for this course number!")
                else:
                    schedule[course].append(section)
                    print(f"Section number {section} has been successfully added to the schedule at course number {course}!")
                    return
    
def rem_course_section(schedule):
    '''Prompts the user to remove a section from an existing course in the schedule.

    Checks that the course exists and verifies that the section is present before attempting removal.
    If the section exists, removes it from the list of sections for the specified course.

    Parameters:
    schedule --> dictionary representing the current schedule to be updated

    Returns:
    None; the function updates the passed-in dictionary in place
    '''
    proceed = False

    while not proceed:
    
        course = getUserInput("Please enter a course number to remove a section from: ")
        keys = schedule.keys()

        if course not in keys:
            print("This course does not currently exist in the schedule!")
        else:
            while not proceed:
                
                section = getUserInput("Please enter a section number to remove: ")

                if section not in schedule[course]:
                    print("This section does not currently exist in the schedule for this course number!")
                else:
                    schedule[course].remove(section)
                    print(f"Section number {section} has been successfully removed from the schedule at course number {course}!")
                    return

def save_curr_schedule_to_file(schedule):
    '''Prompts the user for a filename and saves the current schedule dictionary to a file using pickle.

    Handles errors such as invalid filenames or write permission issues, and confirms successful saving.

    Parameters:
    schedule --> dictionary representing the current schedule to be saved

    Returns:
    None; the function writes the dictionary to a file in binary format
    '''
    
    isValid = False
    while not isValid:
        fileName = str(input('Enter the filename to save the schedule to: '))
        try:
            with open(fileName, 'wb') as outputFile:
                pickle.dump(schedule, outputFile)
        except OSError:
            print('ERROR! Invalid filename or cannot write to this location.')
        else:
            print(f'Schedule successfully saved to {fileName}')
            isValid = True

# ***OUTPUT***
#
# Course Schedule Menu Options
# ============================
#
#     1     Load Schedule from File
#     2     Clear Current Schedule
#     3     View Course Numbers
#     4     View Course Sections for Course Number
#     5     Add Course Number
#     6     Remove Course Number
#     7     Add Course Section for Course Number
#     8     Remove Course Section for Course Number
#     9     Save Schedule to File
#
#
# ***OUTPUT OPTION 5: Add Course Number***
#
# Enter your choice (0 to exit): 5
# ...Add Course Number
# Please enter a course number to add: cis1400
# Course number CIS1400 has been successfully added to the schedule!
#
# Enter your choice (0 to exit): 5
# ...Add Course Number
# Please enter a course number to add: cis1400
# This course has already been added to the schedule!
# Please enter a course number to add: cis2571
# Course number CIS2571 has been successfully added to the schedule!
#
#
# ***OUTPUT OPTION 7: Add Course Section for Course Number***
#
# Enter your choice (0 to exit): 7
# ...Add Course Section for Course Number
# Please enter a course number to add a section to: cis1350
# This course does not currently exist in the schedule!
# Please enter a course number to add a section to: cis1400
# Please enter a section number to add: 001
# Section number 001 has been successfully added to the schedule at course number CIS1400!
# Please enter a course number to add a section to: cis1400
# Please enter a section number to add: 002
# Section number 002 has been successfully added to the schedule at course number CIS1400!
#
#
# ***OUTPUT OPTION 9: Save Schedule to File***
#
# Enter your choice (0 to exit): 9
# ...Save Schedule to File
# Enter the filename to save the schedule to: x:///badfile!@34
# ERROR! Invalid filename or cannot write to this location.
# Enter the filename to save the schedule to: courseSchedule.dat
# Schedule successfully saved to courseSchedule.dat
#
#
# ***OUTPUT OPTION 2: Clear Current Schedule***
#
# Enter your choice (0 to exit): 2
# ...Clear Current Schedule
# Current schedule contains active courses. 
# Do you wish to clear existing courses and continue (Y/N) ? n
# Current schedule not cleared.
# ...Current courses in schedule are: CIS1400, CIS2571
#
# Enter your choice (0 to exit): 2
# ...Clear Current Schedule
# Current schedule contains active courses. 
# Do you wish to clear existing courses and continue (Y/N) ? y
# Clearing current schedule.
# ...Current courses in schedule are:
#
#
# ***OUTPUT OPTION 1: Load Schedule from File ***
# ***(assume a course was readded so schedule not empty***
#
# Enter your choice (0 to exit): 1
# ...Load Schedule from File
# Current schedule contains active courses. 
# Do you wish to clear existing courses and continue (Y/N) ? n
# Schedule not updated!
#
# Enter your choice (0 to exit): 1
# ...Load Schedule from File
# Current schedule contains active courses. 
# Do you wish to clear existing courses and continue (Y/N) ? y
# Enter the filename to load the schedule from: x:///badfile!@34
# ERROR! File not found.
# Enter the filename to load the schedule from: courseSchedule.dat
# Schedule successfully loaded from courseSchedule.dat
#
#
# ***OUTPUT OPTION 3: View Course Numbers***
#
# Enter your choice (0 to exit): 3
# ...View Course Numbers
# ...Current courses in schedule are: CIS1400, CIS2571
#
#
# ***OUTPUT OPTION 4: View Course Sections for Course Number***
#
# Enter your choice (0 to exit): 4
# ...View Course Sections for Course Number
# Please enter a course number to locate: cis1350
# ERROR! Course CIS1350 not found in current schedule.
# ...Current courses in schedule are: CIS1400, CIS2571
#
# Enter your choice (0 to exit): 4
# ...View Course Sections for Course Number
# Please enter a course number to locate: CIS1400
# ...Current sections for CIS1400 are: 001, 002
#
#
# ***OUTPUT OPTION 6: Remove Course Number***
#
# Enter your choice (0 to exit): 6
# ...Remove Course Number
# Please enter a course number to remove: cis1350
# This course does not currently exist in the schedule!
# Please enter a course number to remove: cis2571
# Course number CIS2571 has been successfully removed from the schedule!
#
#
# ***OUTPUT OPTION 8: Remove Course Section for Course Number***
#
# Enter your choice (0 to exit): 8
# ...Remove Course Section for Course Number
# Please enter a course number to remove a section from: cis1350
# This course does not currently exist in the schedule!
# Please enter a course number to remove a section from: cis1400
# Please enter a section number to remove: hyb001
# This section does not currently exist in the schedule for this course number!
# Please enter a section number to remove: net01
# This section does not currently exist in the schedule for this course number!
# Please enter a section number to remove: 001
# Section number 001 has been successfully removed from the schedule at course number CIS1400!
