# Author:   Rod Ciombor
# Date:     11/19/2025
# Class:    CIS2531-NET01
# Descr:
#   This class contains properties describing
#   information about a retail item a customer intends to purchase.
#   Also contains properties to retrieve and update data attributes

class RetailItem:

    __doc__ = '''
    RetailItem Class
    ----------------
    This class represents an item sold in a retail store. It stores a
    description, the number of units in inventory or the cart, and the price per unit.

    Attributes:
        __description (str): Description of the item.
        __units (int): Units available in inventory or in the shopping cart.
        __price (float): Price per unit.

    Properties:
        description: Get or set the item description.
        units: Get or set the quantity.
        price: Get or set the unit price.

    Methods:
        __str__(): Return a formatted string representation of the item.
    '''
    
    def __init__(self, desc='', units=0, price=0.0):
        
        self.__description = desc
        self.__units = units
        self.__price = price

    @property
    def description(self):
        return self.__description

    @property
    def units(self):
        return self.__units

    @property
    def price(self):
        return self.__price

    @description.setter
    def description(self, desc):
        self.__description = str(desc)

    @units.setter
    def units(self, units):
        self.__units = int(units)

    @price.setter
    def price(self, price):
        self.__price = float(price)

    def __str__(self):
        return (
            f"Retail Item:\n"
            f"  Description: {self.description}\n"
            f"  Units:       {self.units}\n"
            f"  Price:       ${self.price:.2f}"
        )

