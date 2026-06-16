class Course:
    '''
        Simple class for representing a course with 'private'
        attributes for number, name, and credit hours.  
        Use of property decorators as accessor and mutator
        methods.
        
        Created by Carolyn England
        Copyright College of DuPage and Carolyn England
    '''
    
    def __init__(self, num = '', name = '', crHours = 0):
        ''' The __init__ method initializes the
            course object characteristics
            as private data members.
        '''
        self.__num = num
        self.__name = name
        self.__crHours = crHours

    @property
    def num(self):
        return self.__num
    @num.setter
    def num(self, n):
        self.__num = n

    @property
    def name(self):
        return self.__name
    @name.setter
    def name(self, n):
        self.__name = n

    @property
    def crHours(self):
        return self.__crHours
    @crHours.setter
    def crHours(self, cr):
        self.__crHours = cr
        
    # methods to create string representation
    def __str__(self):
        displayString = f'{self.__num:s}:{self.__name:s} ({self.__crHours:d} credit hours)\n'
        return displayString

# for module/class testing
if __name__ == '__main__':
    c1 = Course()
    print(c1)
    c2 = Course('CIS2531', 'Intro to Python', 4)
    print(c2)
    c3 = Course()
    c3.num = 'CIS2532'
    c3.name = 'Advanced Python'
    c3.crHours = 4
    print(c3.num, c3.name, c3.crHours)
    
