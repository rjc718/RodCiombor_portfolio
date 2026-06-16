# Author:       Rod Ciombor
# Date:         02/15/2026
# Instructor:   Dr. Sheikh Shamsuddin
# Class:        CIS-2532-NET01

SALES_TAX_RATE = 0.07

def getPricesFromUser():
    '''
    This function prompts the user to enter an item cost.
    It then parses the value as a float and adds it to a list.

    It then asks the user if they would like to enter another item price,
    and the process will continue until the user 
    enters something other than "Y" or "y"

    Upon completion, the function will return the list of item prices
    '''
    items = []
    proceed = 'Y'
    #Prompt user for input
    while proceed == 'Y':
        itemCost = float(input('Please enter the price of an item: '))
        items.append(itemCost)
        
        print('Item price added to total')
        print()
        print('Would you like to enter another item price?')
        proceed = str(input('Enter "Y" for yes, "N" for no: ')).upper()
        print()
    
    print('Thank You!')
    print()
    return items

def displayItemPriceList(items):
    '''
    This function displays a list of items, including the item number and price. 
    '''
    itemNum = 0
    for item in items:
        #print items of list
        itemNum += 1
        print(f'{"Item " + str(itemNum) + ":":<15}${item:.2f}')
    print('-' * 30)

def displayTotalSection(items):
    '''
    This function calculates the Sub-Total 
    by adding up the values in a list of items.

    It then calculates the Sales Tax by multiplying 
    the Sub Total by the Sales Tax rate.

    It then calculates the Total by adding the Sales Tax to the Sub-Total.

    It then displays this information formatted to two decimal places.
    '''
    #Calculate and display Sub-Total
    subTotal = sum(items)
    print(f'{"Sub-Total:":<15}${subTotal:.2f}')
    
    # Calculate tax and add it
    
    salesTaxTotal = subTotal * SALES_TAX_RATE
    print(f'{"Sales Tax:":<15}${salesTaxTotal:.2f}')
    
    #Display Final Cost
    print()
    print(f'{"Total:":<15}${subTotal + salesTaxTotal:.2f}')


    def getMatrixValues():
        #
        pass
    def multiplyMatrix(a, b):
        pass