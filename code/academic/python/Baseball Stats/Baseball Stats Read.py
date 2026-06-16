# Author:   Rod Ciombor
# Date:     10/10/2025
# Class:    CIS2531-NET01
# Descr:
#   Program prompts user to enter filename
#   containing records of baseball player statistics.
#   It then reads and displays this data as a chart.

ROW_WIDTH = 70
PLAYER_COL = 'Player Name'
GAMES_COL = 'Games Played'
HOME_RUNS_COL = 'Home Runs'
DELIMITER = ':'

NAME_INDEX = 0
GAMES_INDEX = 1
HOME_RUNS_INDEX = 2
BAT_AVG_INDEX = 3


def calculateAvg(total, players):
    '''Calculates average of two values

    Params:
    total is some aggregated statistic
    cplayers is the total number of players
    
    Descr:
    Divides total by players and returns the result
        
    '''
    return total / players

def displayColHeaders():

    '''Displays the header of a chart
    
    Descr:
    Adds a blank space at the top

    Creates and prints a three column chart
    First column is left aligned and is 30 spaces long
    Other columns are right aligned and are 20 spaces each
    Named constants represent column names

    Prints a bar that is 70 spaces long (defined by ROW_WIDTH)
        
    '''

    bar = ''
    for i in range(ROW_WIDTH):
        bar += '='

    print()    
    print(f"{PLAYER_COL:<30}{GAMES_COL:>20}{HOME_RUNS_COL:>20}")
    print(bar)
    

def main():
    '''
    Program prompts user to enter various statistics 
    for given number of baseball players
    then writes the values to a file it creates.
   
    Descr:
    Asks user how many player records they would like to enter
    Validates choice as positive integer

    Creates and opens file after checking validity of file path
    Reads each line/record in the specified file
    
    Performs the following for each record:
    
    Splits the string into four parts, using : as a delimiter
    Assigns each part to specific variable, based on the index
    Casts numerical data as strings or floats
    Keeps running total of total players, games, home runs and batting average
    Prints each record on a separate line, with spacing/alignment matching column headers
    
    Calculates average games played, home runs and team batting average
    Displays this data, along with total players, in a summary section

    Closes input file
        
    '''
    
    #Create and open output file if valid name given
    isValid = False
    while not isValid:
        fileName = str(input('Enter the filename to read player records: '))
        try:
            inputFile = open(fileName, 'r')
        except FileNotFoundError:
            print('ERROR! Problem opening file!')
        else:
            isValid = True

    #Display Column Headers
    displayColHeaders()

    #Read and display player stats
    #Calculate running totals
    totalPlayers = 0
    totalGamesPlayed = 0
    totalHomeRuns = 0
    totalBatAvg = 0.0
    
    for line in inputFile:
        fieldList = line.split(DELIMITER)

        playerName = fieldList[NAME_INDEX]
        totalPlayers = totalPlayers + 1
        
        gamesPlayed = int(fieldList[GAMES_INDEX])
        totalGamesPlayed = totalGamesPlayed + gamesPlayed
        
        homeRuns = int(fieldList[HOME_RUNS_INDEX])
        totalHomeRuns = totalHomeRuns + homeRuns

        batAvg = float(fieldList[BAT_AVG_INDEX])
        totalBatAvg = totalBatAvg + batAvg

        print(f"{playerName:<30}{gamesPlayed:>20}{homeRuns:>20}")

    #Print footer
    print()
    print('***SUMMARY STATISTICS***')

    print(f"Total number of players: {totalPlayers}")
    
    avgGames = calculateAvg(totalGamesPlayed, totalPlayers)
    print(f"Average number of games played: {avgGames:.3f}")

    avgHomeRuns = calculateAvg(totalHomeRuns, totalPlayers)
    print(f"Average number of home runs: {avgHomeRuns:.3f}")

    teamBatAvg = calculateAvg(totalBatAvg, totalPlayers)
    print(f"Overall Batting Average: {teamBatAvg:.3f}")
    
    #Close file 
    inputFile.close()

if __name__ == '__main__':
    main()


# ***OUTPUT***
#
# Enter the filename to read player records: CubStats.txt
# ERROR! Problem opening file!
# Enter the filename to read player records: 2019CubStats.txt

# Player Name                           Games Played           Home Runs
# ======================================================================
# Wilson Contreras                               105                  24
# Anthony Rizzo                                  146                  27
# Addison Russell                                 82                   9
# Javier Baez                                    138                  29
# Kris Bryant                                    147                  31
# Kyle Schwarber                                 155                  38
# Albert Almora                                  130                  12
# Jason Heyward                                  147                  21

# ***SUMMARY STATISTICS***
# Total number of players: 8
# Average number of games played: 131.250
# Average number of home runs: 23.875
# Overall Batting Average: 0.265
