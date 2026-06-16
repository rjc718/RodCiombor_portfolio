# Author:   Rod Ciombor
# Date:     11/20/2025
# Class:    CIS2531-NET01
# Descr:
#   This class allows users to add or remove RetailItem objects from their order
#   Also contains functions to calculate the total price and tax of all items

class CashRegister:

    __doc__ = '''
    CashRegister Class
    ------------------
    The CashRegister class manages a collection of RetailItem objects 
    representing items a customer intends to purchase. It allows items 
    to be added, updated, displayed, and cleared. It also provides 
    functionality to compute totals and tax amounts.

    Attributes:
        TAX_RATE (float): Class-level constant representing the sales tax rate.
        __items (list): A private list storing RetailItem objects in the cart.

    Properties:
        items (list): Read-only access to the list of items currently in the cart.

    Methods:
        purchase_item(newItem):
            Adds a RetailItem to the cart. If an item with the same description 
            already exists, its quantity is increased.

        get_total():
            Calculates and returns the total price of all items in the cart 
            before tax.

        get_tax_amt():
            Returns the total sales tax for all items in the cart.

        show_items():
            Prints all RetailItem objects currently in the cart using their 
            __str__() representations.

        clear():
            Empties the cart by resetting the internal item list.

        __str__(): Return a formatted string representation of all the items in the register
    '''    
    TAX_RATE = 0.075

    def __init__(self):
        self.__items = []

    @property
    def items(self):
        return self.__items

    def purchase_item(self, newItem):
        if len(self.__items) > 0:
            for item in self.__items:
                if item.description == newItem.description:
                    item.units = item.units + newItem.units
                    return
        self.__items.append(newItem)

    def get_total(self):
        total = 0.00
        for item in self.__items:
            total = total + (item.price * item.units)
        return total
        
    def get_tax_amt(self):
        return self.get_total() * CashRegister.TAX_RATE

    def show_items(self):
        if len(self.__items) == 0:
            print('No items in cart!')
        else:
            for item in self.__items:
                print(item)
                print()

    def clear(self):
        self.__items = []

    def __str__(self):
        if len(self.__items) == 0:
            return "No items in cart."

        lines = ["Items in Cart:"]
        for item in self.__items:
            lines.append(str(item))

        return "\n".join(lines)


    
            

        
