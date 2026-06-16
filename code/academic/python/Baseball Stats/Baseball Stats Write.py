# Author:   Rod Ciombor
# Date:     10/09/2025
# Class:    CIS2531-NET01
# Descr:
#   Program prompts user to enter various statistics 
#   for given number of baseball players
#   then writes the values to a file it creates as delimited string.

def checkNegative(value, checkFloat = False):
    '''Checks if given number is negative

    Params:
    value is the number to evaluate
    checkFloat will cast value as a float if True
    Will case value as int if False
    
    Descr:
    Casts entered value as int or float
    Raises ValueError if value is less than zero
        
    '''
    
    if checkFloat:
        num = float(value)
    else:
        num = int(value)
    if num < 0:
        raise ValueError('ERROR!  Negative value entered!')


def checkInt(value):
    '''Checks if given value is an integer

    Params:
    value is the number to evaluate
    
    Descr:
    Casts entered value as int
    Raises ValueError with custom message if value not an int
        
    '''
    
    try:
        int(value)
    except ValueError:
        raise ValueError('ERROR!  Non integer value entered!')


def checkFloat(value):
    '''Checks if given value is a float

    Params:
    value is the number to evaluate
    
    Descr:
    Casts entered value as float
    Raises ValueError with custom message if value not a float
        
    '''
    
    try:
        float(value)
    except ValueError:
        raise ValueError('ERROR!  Non float value entered!')


def checkBatAvgRange(value):
    '''Checks if given Batting Average is between 0.0 and 1.0

    Params:
    value is the Batting Average to evaluate
    
    Descr:
    Casts entered value as float
    Raises ValueError with custom message if value not in desired range
        
    '''
    
    num = float(value)
    if num < 0.0 or num > 1.0:
        raise ValueError('ERROR!  Batting average should be between 0.0 and 1.0!')


def getPositiveIntInput(prompt):
    '''Recieves and validates positive integer value from user

    Params:
    prompt is the message displayed to the user indicating what data to enter
    
    Descr:
    Prompts user to enter input
    Runs checkInt() to confirm user entered integer
    Runs checkNegative() to confirm user entered positive number
    
    If ValueError raised by these functions, displays error message
    Then prompts user to enter input again.

    If input valid, casts as int and returns value

    For use with number of players, games played, and home runs
        
    '''
    
    isValid = False
    while not isValid:
        try:
            value = input(prompt)
            checkInt(value)
            checkNegative(value)
        
        except ValueError as err:
            print(err)
        else:
            return int(value)

def getBatAvg():
    '''Recieves and validates Batting Average entered by user
   
    Descr:
    Prompts user to enter input
    
    Runs checkFloat() to confirm user entered float
    Runs checkNegative() to confirm user entered positive number
    True argument passed to checkNegative() confirms it will be cast as float
    Runs checkBatAvgRange() to confirm input is between 0.0 and 1.0
    
    If ValueError raised by these functions, displays error message
    Then prompts user to enter input again.

    If input valid, casts as float and returns value
        
    '''
    isValid = False
    while not isValid:
        try:
            value = input('Enter the batting average: ')
            checkFloat(value)
            checkNegative(value, True)
            checkBatAvgRange(value)
            
        except ValueError as err:
            print(err)
        else:
            return float(value)

def main():
    '''
    Program prompts user to enter filename containing records of baseball player statistics.
    It then reads and displays this data as a chart.
   
    Descr:
    Prompts user to enter filename that contains player stats
    Opens file in read-only mode after checking validity of file path

    Performs the following for each player:
    
    Prompts user to enter player name, which should be a string
    Prompts user to enter games played, which should be positive int
    Prompts user to enter home runs, which should be positive int
    Prompts user to enter batting average
    Should be positive float between 0.0 and 1.0
    
    Error validation handled by functions called within

    Writes user input to output file as a single record
    Values are separated by :
    
    Closes output file
    Displays success messages
        
    '''

    #Get number of players
    numPlayers = getPositiveIntInput('Enter number of player records to enter: ')

    #Create and open output file if valid name given
    isValid = False
    while not isValid:
        fileName = str(input('Enter the filename to store player records: '))
        try:
            outputFile = open(fileName, 'w')
        except FileNotFoundError:
            print('ERROR! Problem opening file!')
        else:
            isValid = True

    for i in range(numPlayers):
        #Get input from user
        playerName = str(input('Enter Player first and last name: '))
        gamesPlayed = getPositiveIntInput('Enter number of games played: ')
        homeRuns = getPositiveIntInput('Enter the number of home runs: ')
        batAvg = getBatAvg()

        #Write user input to file as delimited string
        outputFile.write(f'{playerName}:{gamesPlayed}:{homeRuns}:{batAvg:.3f}\n')

    #Close file and cleanup
    outputFile.close()
     
    print('Finished processing...')
    print('Exiting program...')

if __name__ == '__main__':
    main()

# ***OUTPUT***
#
# Enter number of player records to enter: -4
# ERROR!  Negative value entered!
# Enter number of player records to enter: 4.5
# ERROR!  Non integer value entered!
# Enter number of player records to enter: 4
#
# Enter the filename to store player records: x:\baddir\badfile.txt
# ERROR! Problem opening file!
# Enter the filename to store player records: players.txt
#
# Enter Player first and last name: Anthony Rizzo
#
# Enter number of games played: -5
# ERROR!  Negative value entered!
# Enter number of games played: 27.5
# ERROR!  Non integer value entered!
# Enter number of games played: 146
#
# Enter the number of home runs: -10
# ERROR!  Negative value entered!
# Enter the number of home runs: 27.5
# ERROR!  Non integer value entered!
# Enter the number of home runs: 27
#
# Enter the batting average: -0.15
# ERROR!  Negative value entered!
# Enter the batting average: point two
# ERROR!  Non float value entered!
# Enter the batting average: 2.0
# ERROR!  Batting average should be between 0.0 and 1.0!
# Enter the batting average: 0.293
#
# Enter Player first and last name:




