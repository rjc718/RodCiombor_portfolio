# Author:   Rod Ciombor
# Date:     09/10/2025
# Class:    CIS2531-NET01
# Descr:
#   Program asks user to enter a Roulette Wheel pocket number.
#   Program determines the color based on the number range and if it is odd or even.
#   Program then outputs the color name of the pocket
#   Program also outputs an error message if an invalid number is selected

#Set named constants for pocket colors and number range
MAX_NUM = 36
MIN_NUM = 0

COLOR_BLACK = 'black'
COLOR_RED = 'red'
COLOR_GREEN = 'green'

# Ask user to choose a pocket number
# Assign input to variable (should be an int)

pocket_number = int(input('Please enter a pocket number between 0 and 36: '))

if pocket_number < 0 or pocket_number > 36:
    #Display error for out of range numbers
    print(f'Error:  Pocket number must be between {MIN_NUM} and {MAX_NUM}')
else:

    #Use modulus to determine if number is odd or even
    is_even = True if pocket_number % 2 == 0 else False

    #Assign color based on number range and odd/even
    match pocket_number:
        case 0:
            pocket_color = COLOR_GREEN
            
        case _ if pocket_number >= 1 and pocket_number <= 10:
            pocket_color = COLOR_BLACK if is_even else COLOR_RED
            
        case _ if pocket_number >= 11 and pocket_number <= 18:
            pocket_color = COLOR_RED if is_even else COLOR_BLACK

        case _ if pocket_number >= 19 and pocket_number <= 28:
            pocket_color = COLOR_BLACK if is_even else COLOR_RED

        case _ if pocket_number >= 29 and pocket_number <= 36:
            pocket_color = COLOR_RED if is_even else COLOR_BLACK

    #Display output for valid numbers only
    print(f'Pocket number {pocket_number} is the color {pocket_color}')

# ***OUTPUT***
#
# Please enter a pocket number between 0 and 36: 0
# Pocket number 0 is the color green
# 
# Please enter a pocket number between 0 and 36: 14
# Pocket number 14 is the color red
# 
# Please enter a pocket number between 0 and 36: 15
# Pocket number 15 is the color black
#
# Please enter a pocket number between 0 and 36: 48
# Error:  Pocket Number must be between 0 and 36

    



