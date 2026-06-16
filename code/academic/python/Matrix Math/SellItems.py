# Author:       Rod Ciombor
# Date:         02/15/2026
# Instructor:   Dr. Sheikh Shamsuddin
# Class:        CIS-2532-NET01
# Descr:
#   Program prompts user to enter item prices and adds values to list
#   It then calculates and displays the list of prices,
#   the Sub-Total, the Sales Tax, and the final Total

import SellItemsLib as lib

def main():
    '''
    This program prompts user to enter item prices and adds values to list.
    
    It then calculates and displays the list of prices,
    the Sub-Total, the Sales Tax, and the final Total.
    '''
    #Prompt user for input
    items = lib.getPricesFromUser()
  
    #Print items of list
    lib.displayItemPriceList(items)

    #Calculate and display totals
    lib.displayTotalSection(items)

main()