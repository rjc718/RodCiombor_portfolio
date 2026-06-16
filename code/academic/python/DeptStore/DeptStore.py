'''
    Program to use CashRegister object to make
    RetailItem purchases.  Includes looping menu
    display with validation, user selection of items
    for purchase, checkout, and total calculations.
    **copyright CREngland, COD
'''
import RetailItem as r
import CashRegister as cr

def menuChoice(ritems):
    '''
        Display items currently in inventory.  Prompt, read,
        and validate an item for purchase.

        Parameters:
        ritems --> list of RetailItem inventory objects

        Returns:
        choice --> user's choice of inventory RetailItem to purchase

        Descr:
        Display list of items in inventory to user.  Prompt, read,
        and validate user integer item menu entry.  Throws ValueError
        for non-integer user entry. Provides error message feedback
        to user and then re-prompts by calling itself. Returns valid
        entered integer.
    **copyright CREngland, COD
    '''
    print('\nItems Available for Purchase')
    print('----------------------------')
    print()
    print(f'{"Choice":^10}' +\
          f'{"Description":<20}' + \
          f'{"Qty":>5}' + \
          f'{"Price":>15}')
    print(f'{"------":^10}' +\
          f'{"-----------":<20}' + \
          f'{"---":>5}' + \
          f'{"-----":>15}')   
    for counter in range(len(ritems)):
        print(f'{counter + 1:^10}{ritems[counter]}')
    # validation of numeric input
    try:
        choice = int(input('Enter your choice (0 to check out): '))
        while (choice < 0 or choice > len(ritems)):
            print('ERROR! Invalid choice.')
            print('Choice must be in range of 0 to', len(ritems))
            choice = int(input('Enter your choice (0 to check out): '))
    except ValueError as err:
        print('ERROR! Choice must be whole number in range of 0 to', len(ritems))
        return menuChoice(ritems)        
    return choice

def getQty(maxQty):
    '''
        Prompt, read, and and validate a quantity for purchase.

        Parameters:
        maxQty --> maximum number of items in inventory

        Returns:
        qty --> number of items to purchase

        Descr:
        Prompt, read, and validate quantity of RetailItem objects to purchase
        up to the maximum number of items in inventory.  Throws ValueError
        for non-integer user entry.  Provides error message feedback to user
        and then re-prompts by calling itself.  Returns valid entered integer.
    **copyright CREngland, COD
    '''
    try:
        qty = int(input('Quantity to purchase: '))       
        while(qty < 1 or qty > maxQty):
            print(f'ERROR! Invalid quantity.')
            print(f'Valid quantity is 1 to {maxQty}.')
            qty = int(input('Quantity to purchase: '))
    except ValueError as err:
        print('ERROR! Choice must be whole number in range of 1 to', maxQty)
        return getQty(maxQty)        
    return qty

def checkOut(cReg):

    '''
        Display and total items purchased.  Prompt, read,
        and validate an item for purchase.

        Parameters:
        cReg --> CashRegister object for current purchase

        Descr:
        Display list of items for purchase to user by calling
        CashRegister object show_items method which invokes each
        RetailItem object's string representation.  Total and display
        all purchased items, tax, tax rate, and total plus tax amounts.
        Clear all items from CashRegister for next purchase.
    **copyright CREngland, COD
    '''
    totalAmt = cReg.get_total()
    taxAmt = cReg.get_tax_amt()
    print('Shopping Cart')
    print('-------------')
    # display items in cart using RetailItem object string representation
    # with proper alignment/display of receipt 
    cReg.show_items()
    print(f'\n{"Subtotal":<15}${totalAmt:>15,.2f}')
    print(f'{"Tax":<15}${taxAmt:>15,.2f}')
    # NOTE: access of class level constant for TAX_RATE
    print(f'{"Tax Rate":<15} {cr.CashRegister.TAX_RATE:>15.2%}')
    print(f'{"Total":<15}${totalAmt + taxAmt:>15,.2f}')
    print(f'\nCheck out complete.  Clearing cash register...')
    cReg.clear() # remove RetailItem objects from cart/cash register
    if len(cReg.items) == 0:
        print('Cash register cleared.')
    

def main():
    '''
        main controlling function to create inventory item objects,
        create cash register object for processing user selections,
        obtain user selections, and checkout user selection from
        cash register
    **copyright CREngland, COD
    '''
    inventory = (r.RetailItem('Jacket', 12, 59.95),
                 r.RetailItem('Designer Jeans', 40, 34.95),
                 r.RetailItem('Shirt', 20, 24.95),
                 r.RetailItem('Breitling Top Time', 5, 19900.00))
    # create cash register object to hold retail items
    register = cr.CashRegister()
    # get user menu choice
    uchoice = menuChoice(inventory)
    # loop while items to purchase
    while(uchoice != 0):
        qty = getQty(inventory[uchoice - 1].units)
        # create item to purchase
        itemToPurch = r.RetailItem()
        itemToPurch.description = inventory[uchoice - 1].description
        itemToPurch.units = qty
        itemToPurch.price = inventory[uchoice - 1].price
        # update inventory quantity for additional purchase
        inventory[uchoice - 1].units -= itemToPurch.units
        # add item to cash register
        register.purchase_item(itemToPurch)
        # redisplay menu and get selection
        uchoice = menuChoice(inventory)
    # see if any items purchased
    if len(register.items) == 0:
        print(f'No items purchased.\n')
    else:
        print(f'Checking out....\n')
        # display items in cart and totals
        checkOut(register)
    print(f'Thank you for visiting the COD Student Store today!\n')
   
    
if __name__ == '__main__':
    main()
