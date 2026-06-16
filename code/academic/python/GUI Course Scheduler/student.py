class Student:
    '''
        Simple class for representing a student with 'private'
        attributes for name, id_num, gpa, and courses.
        Use of property decorators as accessor and mutator
        methods.
        
        Created by Carolyn England
        Copyright College of DuPage and Carolyn England
    '''
    
    def __init__(self, name = '', id_num = '', gpa = 0.0, courses = None):
        '''
            The __init__ method initializes the
            student object characteristics
            as private data members.

            'courses' will be a non-duplicate
            set of course numbers

            Python GOTCHA (https://docs.python-guide.org/writing/gotchas/)
            be careful with mutable default arguments:
            Python's default arguments are evaluated once
            when function is defined.  Any mutations to
            default argument will change object for future
            function calls
        '''
        self.__name = name
        self.__id_num = id_num
        self.__gpa = gpa
        # handle mutable default argument of set()
        if courses is None:
            self.__courses = set()
        else:
            self.__courses = courses

    @property
    def name(self):
        return self.__name
    @name.setter
    def name(self, n):
        self.__name = n

    @property
    def id_num(self):
        return self.__id_num
    @id_num.setter
    def id_num(self, id):
        self.__id_num = id

    @property
    def gpa(self):
        return self.__gpa
    @gpa.setter
    def gpa(self, gpa):
        self.__gpa = gpa

    @property
    def courses(self):
        return self.__courses

    def addCourse(self, course):
        ''' add course to student schedule '''
        # add element, if not already present
        self.__courses.add(course)

    def withdrawCourse(self, course):
        ''' remove course from student schedule '''
        # discard does not throw exception if not found
        self.__courses.discard(course)

    def numCredits(self, schedule):
        '''
            method to determine number of credits for
               all current courses using the master
               schedule of course details
            schedule is a dictionary of course
               numbers (key) with their course object (value)
        '''
        semHours = 0
        # loop through each enrolled course
        for cnum in self.__courses:
            # get corresponding value
            # from list of possible courses in schedule
            if cnum in schedule:
                # get appropriate course object
                # and access the number of credit hours
                cval = schedule[cnum].crHours
                # increment the number of credit hours
                # by the credit hours in the tuple of
                # class name and credit hours
                semHours += cval
        return semHours

    # methods to create string representation
    def __str__(self):
        # using implicit f string concatenation
        displayString = (f'{"Name":10} {self.__name:15s}\n'
                         f'{"ID":10} {self.__id_num:15}\n'
                         f'{"GPA":10} {self.__gpa:<15.2f}\n'
                         f'{"Courses":10} {self.__courses}\n')
        return displayString
  
# for module/class testing  
if __name__ == '__main__':
    s1 = Student()
    print(s1)
    s2 = Student('Sally Student', '111', 3.5)
    print(s2)
    s3 = Student()
    s3.name = 'Bob Johnson'
    s3.id_num = '222'
    s3.gpa = 3.0
    s3.addCourse('CIS1400')
    s3.addCourse('CIS2531')
    print(s3.name, s3.id_num, s3.gpa, s3.courses)
    s3.withdrawCourse('CIS1400')
    print(s3)

