# Author:   Rod Ciombor
# Date:     11/26/2025
# Class:    CIS2531-NET01
# Descr:
#   This file contains classes to create an abstract Employee class
#   as well as ShiftSupervisor and ProductionWorker classes that extend Employee
#   All classes contain data attributes that describe a type of Employee and 
#   contains properties to retrieve and update data attributes
#
#   Also contains a function called displayEmpData() to display information
#   about the states of various objects

from abc import ABC, abstractmethod

class Employee(ABC):

    __doc__ = '''
    Employee Abstract Base Class
    ----------------------------
    This abstract class represents a generic employee. It defines common
    attributes and methods that all employee types should have.

    Class Attributes:
        HRS_PER_WEEK (int): Standard number of working hours per week (default 40).
        WKS_PER_YEAR (int): Standard number of working weeks per year (default 52).

    Instance Attributes:
        __name (str): The employee's name (private).
        __id (str/int): The employee's identification number (private).

    Properties:
        name: Getter and setter for the employee's name.
        id: Getter and setter for the employee's ID.

    Methods:
        yearly_gross_pay(): Abstract method to calculate the employee's yearly gross pay.
        __str__(): Returns a formatted string representation of the employee's information.
    '''

    HRS_PER_WEEK = 40
    WKS_PER_YEAR = 52

    def __init__(self, name, id):
        self.__name = name
        self.__id = id
    
    @property
    def name(self):
        return self.__name
    
    @property
    def id(self):
        return self.__id

    @name.setter
    def setName(self, name):
        self.__name = name

    @id.setter
    def id(self, id):
        self.__id = id

    @abstractmethod
    def yearly_gross_pay(self):
        pass

    def __str__(self):
        return (
            f"Employee Information:\n"
            f"  Name: {self.name}\n"
            f"  ID:   {self.id}"
        )
    
class ProductionWorker(Employee):

    __doc__ = '''
    ProductionWorker Class
    ----------------------
    This class represents a production worker employee. It inherits from
    the abstract Employee class and adds attributes specific to production
    workers, such as shift number and hourly pay rate.

    Instance Attributes:
        __shift_number (int): The worker's shift number (private).
        __pay_rate (float): The worker's hourly pay rate (private).

    Properties:
        shift_number: Getter and setter for the worker's shift number.
        pay_rate: Getter and setter for the worker's hourly pay rate.

    Methods:
        yearly_gross_pay(): Calculates and returns the yearly gross pay based
            on the hourly pay rate, standard hours per week, and weeks per year.
        __str__(): Returns a formatted string representation of the production
            worker's information, including name, ID, shift number, and pay rate.
    '''

    def __init__(self, name, id, shift_number, pay_rate):
        self.__shift_number = shift_number
        self.__pay_rate = pay_rate
        Employee.__init__(self, name, id)
    
    @property
    def shift_number(self):
        return self.__shift_number
    
    @property
    def pay_rate(self):
        return self.__pay_rate

    @shift_number.setter
    def shift_number(self, shift_number):
        self.__shift_number = shift_number

    @pay_rate.setter
    def pay_rate(self, pay_rate):
        self.__pay_rate = pay_rate

    def yearly_gross_pay(self):
        return self.__pay_rate * Employee.HRS_PER_WEEK * Employee.WKS_PER_YEAR
    
    def __str__(self):
        shift = 'day'
        if self.shift_number == 2:
            shift = 'night'

        return (
            f"{self.name}: IDNUM {self.id}\n"
            f"working {shift} shift with {self.pay_rate:,.2f} hourly pay rate\n"
        )

class ShiftSupervisor(Employee):

    __doc__ = '''
    ShiftSupervisor Class
    --------------------
    This class represents a shift supervisor employee. It inherits from
    the abstract Employee class and adds attributes specific to supervisors,
    such as annual salary and bonus.

    Instance Attributes:
        __salary (float): The supervisor's annual salary (private).
        __bonus (float): The supervisor's annual bonus (private).

    Properties:
        salary: Getter and setter for the supervisor's annual salary.
        bonus: Getter and setter for the supervisor's annual bonus.

    Methods:
        yearly_gross_pay(): Calculates and returns the total yearly gross pay
            by adding salary and bonus.
        __str__(): Returns a formatted string representation of the supervisor's
            information, including name, ID, salary, and bonus.
    '''

    def __init__(self, name, id, salary, bonus):
        self.__salary = salary
        self.__bonus = bonus
        Employee.__init__(self, name, id)
    
    @property
    def salary(self):
        return self.__salary
    
    @property
    def bonus(self):
        return self.__bonus

    @salary.setter
    def salary(self, salary):
        self.__salary = salary

    @bonus.setter
    def bonus(self, bonus):
        self.__bonus = bonus

    def yearly_gross_pay(self):
        return self.__salary + self.__bonus
    
    def __str__(self):
        return (
            f"{self.name}: IDNUM {self.id}\n"
            f"supervising with {self.salary:,.2f} annual salary and {self.bonus:,.2f} yearly bonus\n"
        )

def displayEmpData(obj):
    '''
    Prints the information of an Employee object. If the object is an instance
    of ProductionWorker or ShiftSupervisor, it also prints the calculated yearly
    gross pay.

    Parameters:
        obj (Employee): The object to display. Must be an instance of Employee
                        or its subclasses.

    Behavior:
        - If `obj` is a ProductionWorker or ShiftSupervisor, prints all relevant
          information including yearly gross pay.
        - If `obj` is a base Employee instance, prints only the general employee
          information.
        - If `obj` is not an Employee instance, prints a warning message.
    '''

    if isinstance(obj, Employee):
        print(obj)    
        if isinstance(obj, (ProductionWorker, ShiftSupervisor)):
            print(f'  Yearly Gross Pay: {obj.yearly_gross_pay():,.2f}')
    else:
        print('Passed object is NOT an instance of Employee class hierarchy!')