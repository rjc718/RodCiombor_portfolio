'''
    Program to maintain a semester course schedule
    that implements a dictionary of course number as
    keys and set of sections as the value.

    Menu options to:

    1     Load Schedule from File (serialized format)**
    2     Clear Current Schedule
    3     View Course Numbers
    4     View Course Sections for Course Number
    5     Add Course Number**
    6     Remove Course Number**
    7     Add Course Section for Course Number**
    8     Remove Course Section for Course Number**
    9     Save Schedule to File  (serialized format)**

    **COMPLETED BY STUDENT

    copyright CREngland, COD
'''

# TO BE CREATED BY STUDENT
import proc_schedule as ps

def confirm_clear (schedule):
    '''Checks current schedule for active courses and confirms user wishes to clear
        active schedule.
        
        Name:
        confirm_clear

        Parameters:
        schedule --> dictionary of current courses (keys) and sections (set)
        
        Returns:
        boolean --> indicating whether schedule should be cleared

        Descr:
        If schedule contains courses (len > 0), prompt and reads confirmation from user
        on clearing schedule.  If user confirms clear or there are no active courses, return
        True, otherwise return False. 
    '''
    if len(schedule) > 0:
        confirm = input('Current schedule contains active courses. \nDo you wish to clear existing courses and continue (Y/N) ? ')
        if confirm.upper() != 'Y':
            return False
        else:
            return True
        
def clear_schedule (schedule):
    '''Attempts to clear current schedule.

        Name:
        clear_schedule
        
        Parameters:
        schedule --> dictionary of current courses (keys) and sections (set)

        Returns:
        nothing

        Descr:
        If schedule has courses, get user confirmation before clearing all
        courses in schedule. Display current course numbers in schedule.
   '''
    if len(schedule) > 0 and confirm_clear(schedule):
        print(f'Clearing current schedule.')
        schedule.clear()
    elif len(schedule) == 0:
        print(f'No courses to clear from current schedule.')
    else:
        print(f'Current schedule not cleared.')
        
    # print all courses on single line separated by comma and space
    print(f'\n...Current courses in schedule are: {', '.join(schedule.keys())}')
    print() # blank line

    
def view_course_numbers (schedule):
    '''Attempts to view course numbers in current schedule.

        Name:
        view_course_numbers
        
        Parameters:
        schedule --> dictionary of current courses (keys) and sections (set)

        Returns:
        nothing

        Descr:
        Displays all course numbers in current schedule.
   '''
    if len(schedule) == 0:
        print(f'\nNo courses in current schedule.')
    else:
        # print all courses on single line separated by comma and space
        print(f'\n...Current courses in schedule are: {', '.join(schedule.keys())}')
        print() # blank line
            
def view_course_sections (schedule):
    '''Attempts to view course sections for a given course.

        Name:
        view_course_sections
        
        Parameters:
        schedule --> dictionary of current courses (keys) and sections (set)

        Returns:
        nothing

        Descr:
        Prompt and reads course number from user.  Displays all course sections if
        course number found in schedule.  If not found, message output to user.
    '''
    courseNum = input('Please enter a course number to locate: ')
    # convert to upper
    courseNum = courseNum.upper()
    # Check current schedule for course
    if courseNum not in schedule:
        print(f'\nERROR! Course {courseNum} not found in current schedule.')
        view_course_numbers(schedule)
    else:
        sections = schedule[courseNum]
        if len(sections) == 0:
            print(f'\nCourse {courseNum} has no sections in current schedule.')
        else:
            # print all course sections on single line separated by comma and space
            print(f'\n...Current sections for {courseNum} are: {', '.join(sections)}')
            print() # blank line
    
def menu_choice(mitems):
    '''
        Display menu choices.  Prompt, read, and validate a menu item.

        Name:
        menu_choice
        
        Parameters:
        mitems --> list of menu items

        Returns:
        choice --> validated user's menu choice

        Descr:
        Display list of menu choices to user.  Prompt, read,
        and validate user integer item menu entry.  Throws ValueError
        for non-integer user entry. Provides error message feedback
        to user and then uses recursion to allow re-entry. Returns valid
        entered integer.
    **copyright CREngland, COD
    '''
    print('\nCourse Schedule Menu Options')
    print('============================')
    print() # blank line
    
    for counter in range(len(mitems)):
        print(f'{counter + 1:^10}{mitems[counter]}')
        
    # validation of numeric input
    try:
        choice = int(input('Enter your choice (0 to exit): '))
        while (choice < 0 or choice > len(mitems)):
            print(f'ERROR! Invalid choice.')
            print(f'Choice must be in range of 0 to', len(mitems))
            choice = int(input('Enter your choice (0 to exit): '))
    except ValueError as err:
        print(f'ERROR! Choice must be whole number in range of 0 to', len(mitems))
        return menu_choice(mitems)        
    return choice

def main():
    '''
        main controlling function to display menu and process user
        selection for course schedule.
    **copyright CREngland, COD
    '''
   
    # create dictionary of empty course schedule
    curr_schedule = {}

    # tuple of menu options
    MENU_OPTIONS = ("Load Schedule from File", "Clear Current Schedule",
                 "View Course Numbers", "View Course Sections for Course Number",
                 "Add Course Number", "Remove Course Number",
                 "Add Course Section for Course Number",
                 "Remove Course Section for Course Number",
                 "Save Schedule to File")
    
    # get user menu choice
    uchoice = menu_choice(MENU_OPTIONS)
    
    # loop while user requests menu options
    while(uchoice != 0):
        # process menu choice
        match uchoice:
            case 1:
                # Load Schedule from File
                print(f'\n...{MENU_OPTIONS[0]}')
                ps.load_schedule_from_file(curr_schedule)  # TO BE CREATED BY STUDENT
            case 2:
                # Clear Schedule
                print(f'\n...{MENU_OPTIONS[1]}')
                clear_schedule(curr_schedule)
            case 3:
                # View Course Numbers
                print(f'\n...{MENU_OPTIONS[2]}')
                view_course_numbers(curr_schedule)
            case 4:
                # View Course Sections for Course Number
                print(f'\n...{MENU_OPTIONS[3]}')
                view_course_sections(curr_schedule)
            case 5:
                # Add Course Number 
                print(f'\n...{MENU_OPTIONS[4]}')
                ps.add_course_number(curr_schedule) # TO BE CREATED BY STUDENT
            case 6:
                # Remove Course Section for Course Number
                print(f'\n...{MENU_OPTIONS[5]}')
                ps.rem_course_number(curr_schedule) # TO BE CREATED BY STUDENT
            case 7:
                # Add Course Number 
                print(f'\n...{MENU_OPTIONS[6]}')
                ps.add_course_section(curr_schedule) # TO BE CREATED BY STUDENT
            case 8:
                # Remove Course Section for Course Number
                print(f'\n...{MENU_OPTIONS[7]}')
                ps.rem_course_section(curr_schedule) # TO BE CREATED BY STUDENT
            case 9:
                # Save Schedule to File
                print(f'\n...{MENU_OPTIONS[8]}')
                ps.save_curr_schedule_to_file(curr_schedule) # TO BE CREATED BY STUDENT
        # redisplay menu and get selection
        uchoice = menu_choice(MENU_OPTIONS)
    print(f'Thank you for using the Course Schedule!...Exiting...\n')
   
    
if __name__ == '__main__':
    main()