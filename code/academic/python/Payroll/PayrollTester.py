'''
    File: PayrollTester.py
    
    Payroll program to test:
    ->creation and display of Employee object
    ->creation and display of ProductionWorker object
    ->creation and display of ShiftSupervisor object
    ->polymorphism display function
    ->class attributes
    ->class __doc__ strings
    
    Includes looping menu display with user input,
    validation, and exception handling.
    
    **copyright CREngland, COD
'''

from EmployeeHierarchy import Employee, ProductionWorker, ShiftSupervisor, displayEmpData

def getShiftNum(message):
    '''
        Prompts user to enter a shift number (1--day or 2--night)

        Parameters:
        message --> string prompt for user

        Descr:
        Prompt user to enter a positive integer. Throws a TypeError
        when an invalid shift integer is entered and a ValueError
        for a non-integer entry. Provides feedback to user and
        then re-prompts by calling itself. Returns entered integer.
    '''
    try:
        num = int(input(message))
        if num < 1 or num > 2:
            raise TypeError('ERROR! Invalid shift number entered (1--day or 2--night)!')
        return num
    except ValueError:
        print('ERROR! Non integer value entered!')
        return getShiftNum(message)
    except TypeError as err:
       print(err)
       return getShiftNum(message)

def getPosFloat(message):
    '''
        Prompts user to enter a positive float.

        Parameters:
        message --> string prompt for user

        Descr:
        Prompt user to enter a positive float value. Throws a TypeError
        when a negative float is entered and a ValueError for a
        non-numeric entry. Provides feedback to user and then re-prompts
        by calling itself. Returns entered float.
    '''
    try:
        num = float(input(message))
        if num < 0.0:
            raise TypeError('ERROR! Negative value entered!')
        return num
    except ValueError:
        print('ERROR! Non float value entered!')
        return getPosFloat(message)
    except TypeError as err:
        print(err)
        return getPosFloat(message)
    
def emp():
    '''
        Prompts user to create an Employee object.

        Descr:
        Prompt user to enter name and id. Creates
        Employee object and displays object data using
        polymorphism display function and class string
        representation. 
    '''
    print('testing Employee class')
    e_name = input('Enter name: ')
    e_id = input('Enter id: ')
    e = Employee(e_name, e_id)
    displayEmpData(e)
    print(f'__str__: {e}')

def prodWorker():
    '''
        Prompts user to create a ProductionWorker object.

        Descr:
        Prompt user to enter name, id, shift number,
        and hourly pay rate. Creates ProductionWorker
        object and displays object data using
        polymorphism display function and class string
        representation. 
    '''
    print('testing ProductionWorker class')
    pw_name = input('Enter name: ')
    pw_id = input('Enter id: ')
    pw_shift = getShiftNum('Enter a shift number (1--day or 2--night): ')
    pw_rate = getPosFloat('Enter the hourly pay rate: ')
    pw = ProductionWorker(pw_name, pw_id, pw_shift, pw_rate)
    displayEmpData(pw)
    print(f'__str__: {pw}')

def shiftSuper():
    '''
        Prompts user to create a ShiftSupervisor object.

        Descr:
        Prompt user to enter name, id, salary,
        and bonus. Creates ShiftSupervisor
        object and displays object data using
        polymorphism display function and class string
        representation. 
    '''
    print('testing ShiftSupervisor class')
    ss_name = input('Enter name: ')
    ss_id = input('Enter id: ')
    ss_salary = getPosFloat('Enter the annual salary: ')
    ss_bonus = getPosFloat('Enter the yearly bonus amount: ')
    ss = ShiftSupervisor(ss_name, ss_id, ss_salary, ss_bonus)
    displayEmpData(ss)
    print(f'__str__: {ss}')

def menuChoice():
    '''
        Displays menu of testing items and validates user input
        choice.  
        
        Descr:
        Display menu options to user. Validates user input
        menu option.  Throws a ValueError for non-integer
        entry. Provides feedback to user and then re-prompts
        by calling itself.  Returns entered integer.
    '''
    # display items for testing
    print('\n**TEST MENU**')
    print('-------------')
    print()
    print(f'{"1 -- Create Employee Object"}')
    print(f'{"2 -- Create Production Worker Object"}')
    print(f'{"3 -- Create Shift Supervisor Object"}')
    print(f'{"4 -- Test Polymorphism Display"}')
    print(f'{"5 -- Employee Class Attributes"}')
    print(f'{"6 -- Class Doc Strings"}')
    print(f'{"7 -- EXIT":<20}')
    print(f'{"----------------------"}')   

    # validation of numeric input
    try:
        choice = int(input('Enter your choice: '))
        while (choice < 1 or choice > 7):
            print('ERROR! Invalid menu choice.')
            print('Choice must be in range of 1 to 7')
            choice = int(input('Enter your choice: '))
    except ValueError as err:
        print('ERROR! Choice must be whole number in range of 1 to 7')
        return menuChoice()        
    return choice

def main():
    '''
        Main controlling function to start testing program
        and call functions to get user input for testing.
        Handles exceptions thrown with message output to user.
        
    '''
    uchoice = menuChoice()
    # loop while user wishes to continue
    while (uchoice != 7):
        try:           
            match uchoice:
                case 1:
                    emp()
                case 2:
                    prodWorker()
                case 3:
                    shiftSuper()
                case 4:
                    print('-->testing polymorphism function')
                    displayEmpData('simple string')
                case 5:
                    print('-->testing Employee class attributes')
                    print(f'hours per week = {Employee.HRS_PER_WEEK}')
                    print(f'weeks per year = {Employee.WKS_PER_YEAR}')
                case 6:
                    print('-->testing class doc strings')
                    print('**Employee**')
                    print(Employee.__doc__)
                    print('**ProductionWorker**')
                    print(ProductionWorker.__doc__)
                    print('**ShiftSupervisor**')
                    print(ShiftSupervisor.__doc__)
        # display any exceptions thrown
        except Exception as err:
            print('ERROR!', err)
        # re-display menu options
        uchoice = menuChoice()
    # end program
    print(f'Exiting program....\n')
    
if __name__ == '__main__':
    print(__doc__)
    print(f'Student file: {Employee.__doc__}')
    main()
