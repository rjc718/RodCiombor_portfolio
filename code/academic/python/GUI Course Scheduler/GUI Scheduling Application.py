# Author:   Rod Ciombor
# Date:     12/12/2025
# Class:    CIS2531-NET01
# Descr:
#   Program creates a GUI that allows the user to input student data
#   Also lets them add or withdraw courses to their schedule from a list box.
#   Displays student and/or schedule data and allows the to save it to a .txt file.


import tkinter as tk
import tkinter.font as tkFont
import tkinter.messagebox as messagebox
from student import Student
from course import Course

COURSE_DATA = [
    ("CIS-1400", "Programming and Logic Technique", 3),
    ("CIS-2531", "Intro to Python", 4),
    ("CIS-2532", "Python Programming & Data Science", 4)
]

class MyGUI:
    def __init__(self):
        self.student = Student()
        self.createGUI()

        
    def createGUI(self):
        '''
        Initialize and build the main GUI window for the application.

        This function performs the following tasks:
        1. Creates the main Tkinter window and sets its title.
        2. Calls helper functions to create the student info section, 
        schedule section, radio buttons for display options, and 
        the save data section.
        3. Creates a Text widget for displaying student and course information.
        4. Packs and arranges all GUI components with padding and layout options.
        5. Starts the Tkinter main event loop to display the window and handle user interaction.
        '''

        self.main_window = tk.Tk()
        self.main_window.title('Student & Schedule Manager')
        
        self.createStudentInfoSection()
        
        self.createScheduleSection()

        self.createRadioButtons()

        self.text_box = tk.Text(self.main_window, height=10)
        self.text_box.pack(padx=10, pady=10, fill='x')

        self.createSaveDataSection()

        self.main_window.mainloop()
    
    def createStudentInfoSection(self):
        '''
        Create and layout the Student Info section of the GUI.

        This function performs the following tasks:
        1. Creates a frame to contain all student information widgets.
        2. Adds a title label "Student Info:" with bold, larger font.
        3. Creates labeled Entry widgets for:
        - Student Name
        - Student ID
        - Student GPA
        Each Entry widget is left-aligned within its sub-frame.
        4. Adds an "Update Student Info" button that calls the updateStudent() method when clicked.
        5. Packs the frame and all sub-widgets with padding and left-aligned layout.
        '''

        title_font = tkFont.Font(size=14, weight='bold')
        bold_font = tkFont.Font(weight='bold')

        #Create Student Frame
        self.student_frame = tk.Frame(self.main_window)
        
        self.student_title = tk.Label(self.student_frame, text='Student Info:', font=title_font)
        self.student_title.pack(side='top', padx=10, pady=10)

        #Student Name
        self.name_frame = tk.Frame(self.student_frame)

        self.name_label = tk.Label(self.name_frame, text='Enter Student Name:')
        self.name_label.pack(side='left')
        self.name_entry = tk.Entry(self.name_frame, width=50)
        self.name_entry.pack(side='left', ipadx=5, ipady=5)

        self.name_frame.pack(side='top', anchor='w', padx=10, pady=10)
        
        #student Id
        self.id_frame = tk.Frame(self.student_frame)

        self.id_label = tk.Label(self.id_frame, text='Enter Student ID:')
        self.id_label.pack(side='left')
        self.id_entry = tk.Entry(self.id_frame, width=50)
        self.id_entry.pack(side='left', ipadx=5, ipady=5)

        self.id_frame.pack(side='top', anchor='w', padx=10, pady=10)

        #student GPA
        self.gpa_frame = tk.Frame(self.student_frame)

        self.gpa_label = tk.Label(self.gpa_frame, text='Enter Student GPA:')
        self.gpa_label.pack(side='left')
        self.gpa_entry = tk.Entry(self.gpa_frame, width=25)
        self.gpa_entry.pack(side='left', ipadx=5, ipady=5)

        self.gpa_frame.pack(side='top', anchor='w', padx=10, pady=10)

        #Update Student Info button
        self.student_info_btn = tk.Button(
            self.student_frame,
            text='Update Student Info',
            bg='dark blue',            
            fg='white',          
            font=bold_font,
            relief='flat',
            borderwidth=0,
            command=self.updateStudent
        )
        self.student_info_btn.pack(side='top', anchor='w', padx=10, pady=10, ipadx=5, ipady=5)

        self.student_frame.pack(padx=10, pady=10)
    
    def createScheduleSection(self):
        '''
        Create and layout the Schedule section of the GUI.

        This function performs the following tasks:
        1. Creates a frame to contain all schedule-related widgets.
        2. Adds a title label "Available Courses:" with bold, larger font.
        3. Creates a Listbox widget to display all available courses, with an attached vertical scrollbar.
        4. Populates the Listbox with courses from the course_catalog, showing course number, name, and credit hours.
        5. Adds two buttons:
        - "Add Course": calls the addCourse() method when clicked.
        - "Withdraw Course": calls the withdrawCourse() method when clicked.
        6. Packs the frame and all sub-widgets with padding to create a clean layout.
        '''

        title_font = tkFont.Font(size=14, weight='bold')
        bold_font = tkFont.Font(weight='bold')

         # --- Schedule Frame ---
        self.schedule_frame = tk.Frame(self.main_window)
        self.schedule_label = tk.Label(self.schedule_frame, text='Available Courses:', font=title_font)
        self.schedule_label.pack(side='top', padx=5, pady=5)

        self.schedule_listbox = tk.Listbox(self.schedule_frame, height=6, width=100)
        self.schedule_listbox.pack(side='left', padx=5, pady=5)

        self.scrollbar = tk.Scrollbar(self.schedule_frame, orient="vertical")
        self.scrollbar.config(command=self.schedule_listbox.yview)
        self.schedule_listbox.config(yscrollcommand=self.scrollbar.set)
        self.scrollbar.pack(side='right', fill='y')

        self.schedule_frame.pack(padx=10, pady=10)

        # Populate listbox
        self.course_catalog = self.getCourseCatalog()
        for key, course in self.course_catalog.items():
            self.schedule_listbox.insert("end", f"{course.num}: {course.name} ({course.crHours} credit hours)")

        #Add Course button
        self.student_info_btn = tk.Button(
            self.schedule_frame,
            text='Add Course',
            bg='dark blue',            
            fg='white',          
            font=bold_font,
            relief='flat',
            borderwidth=0,
            command=self.addCourse
        )
        self.student_info_btn.pack(side='left', padx=10, pady=10, ipadx=5, ipady=5)

        #Withdraw Course button
        self.student_info_btn = tk.Button(
            self.schedule_frame,
            text='Withdraw Course',
            bg='dark blue',            
            fg='white',          
            font=bold_font,
            relief='flat',
            borderwidth=0,
            command=self.withdrawCourse
        )
        self.student_info_btn.pack(side='left', padx=10, pady=10, ipadx=5, ipady=5)

    def createRadioButtons(self):
        '''
        Create and layout a group of radio buttons for display options.

        This function performs the following tasks:
        1. Creates a frame to hold the radio buttons and their label.
        2. Adds a label "Display:" to indicate the purpose of the radio buttons.
        3. Defines an IntVar `display_option` to track the currently selected option.
        4. Sets the default selection to 1 (Student & Schedule Info).
        5. Creates three radio buttons with numeric values:
        - 1: Student & Schedule Info
        - 2: Student Info
        - 3: Schedule Info
        Each radio button is linked to the `display_option` variable and calls `updateDisplay()` when clicked.
        6. Packs the radio buttons horizontally with padding and packs the frame on the main window.
        '''

        # Frame to contain the label and radio buttons
        self.radio_frame = tk.Frame(self.main_window)
        
        # Label
        self.radio_label = tk.Label(self.radio_frame, text="Display:")
        self.radio_label.pack(side='left', padx=(0,10))
        
        # Variable to track selected option
        self.display_option = tk.IntVar()
        self.display_option.set(1)  # default selection (1 = Student & Schedule Info)
        
        # Radio buttons with numeric values
        options = [
            (1, "Student & Schedule Info"),
            (2, "Student Info"),
            (3, "Schedule Info")
        ]
        
        for value, label in options:
            rb = tk.Radiobutton(
                self.radio_frame,
                text=label,
                variable=self.display_option,
                value=value,
                command=self.updateDisplay
            )
            rb.pack(side='left', padx=5)
        
        # Pack the frame
        self.radio_frame.pack(padx=10, pady=10, anchor='w')
    
    def createSaveDataSection(self):
        '''
        Create and layout the "Save Data" section of the GUI.

        This function performs the following tasks:
        1. Creates a frame to hold the save button and checkboxes.
        2. Adds a "Save" button that calls `saveData()` when clicked.
        3. Adds two checkboxes with default values checked:
        - "Include Student Info" linked to `include_student_var`
        - "Include Schedule Info" linked to `include_schedule_var`
        4. Packs all widgets horizontally in the frame with padding.
        5. Packs the frame itself into the main window with horizontal fill and extra bottom padding.
        '''

        bold_font = tkFont.Font(weight='bold')
        
        # Create frame
        self.save_frame = tk.Frame(self.main_window)
        
        # Button to save data
        self.save_button = tk.Button(
            self.save_frame,
            text="Save",
            bg="dark blue",
            fg="white",
            font=bold_font,
            command=self.saveData
        )
        self.save_button.pack(side="left", padx=5, pady=5)
        
        # Checkboxes
        self.include_student_var = tk.IntVar(value=1)  # default checked
        self.include_schedule_var = tk.IntVar(value=1)  # default checked
        
        self.include_student_cb = tk.Checkbutton(
            self.save_frame,
            text="Include Student Info",
            variable=self.include_student_var
        )
        self.include_student_cb.pack(side="left", padx=5, pady=5)
        
        self.include_schedule_cb = tk.Checkbutton(
            self.save_frame,
            text="Include Schedule Info",
            variable=self.include_schedule_var
        )
        self.include_schedule_cb.pack(side="left", padx=5, pady=5)
        
        # Pack the frame
        self.save_frame.pack(padx=10, pady=(10, 60), fill="x")

    def updateDisplay(self):
        '''
        Update the display in the text box based on the selected radio button option.

        This function performs the following tasks:
        1. Clears the `text_box` widget.
        2. Reads the currently selected display option from `display_option` (numeric values):
        - 1 = Student & Schedule Info
        - 2 = Student Info only
        - 3 = Schedule Info only
        3. Depending on the selection:
        - Displays student information (name, ID, GPA) if appropriate.
        - Displays the list of enrolled courses and sums their credit hours if appropriate.
        4. Adds a separator line between the course list and the total credit hours.
        5. Inserts the resulting formatted output into the `text_box`.
        '''

        # Clear the text box
        self.text_box.delete("1.0", "end")
    
        output = ""
        totalCreditHours = 0
    
        # Read selected radio button value
        choice = self.display_option.get()  # will be 1, 2, or 3 if using numeric values
    
        # 1 = Student & Schedule Info, 2 = Student Info, 3 = Schedule Info
        if choice == 1 or choice == 2:
            # Display student info
            output += (
                f"Student Name: {self.student.name}\n"
                f"Student ID: {self.student.id_num}\n"
                f"Student GPA: {self.student.gpa:.2f}\n"
            )
    
        if choice == 1 or choice == 3:
            # Display courses
            if self.student.courses:
                output += "\nCourses:\n"
                for course in self.student.courses:
                    output += f"{course.num}: {course.name} ({course.crHours} credit hours)\n"
                    totalCreditHours += course.crHours  # sum credit hours
                
                # Add a separator line before total credit hours
                output += "-------------------------\n"
                output += f"Total Credit Hours: {totalCreditHours}\n"
            
            else:
                output += "\nCourses: None\n"
        
        # Insert output into the text box
        self.text_box.insert("end", output)

    def updateStudent(self):
        '''
        Update the student object with data entered in the input fields.

        This function performs the following tasks:
        1. Retrieves values from the Entry widgets for student name, ID, and GPA.
        2. Updates the corresponding attributes of the `student` object.
        3. Converts the GPA to a float; if conversion fails, sets GPA to 0.0.
        4. Calls `updateDisplay()` to refresh the text box with the updated student information and course schedule (depending on the selected display option).
        '''

        # Get input from Entry widgets
        name = self.name_entry.get()
        id_num = self.id_entry.get()
        gpa = self.gpa_entry.get()

        # Update Student object
        self.student.name = name
        self.student.id_num = id_num
    
        # GPA should be converted to float
        try:
            self.student.gpa = float(gpa)
        except ValueError:
            self.student.gpa = 0.0

        # Update output
        self.updateDisplay()
    
    def getCourseCatalog(self):
        '''
        Create and return a dictionary of Course objects representing the course catalog.

        Each entry in the dictionary uses the course number (`num`) as the key and the corresponding `Course` object as the value. 
        The course data is sourced from the constant `COURSE_DATA`.

        Returns:
            dict: A dictionary mapping course numbers (str) to `Course` objects.
        '''
        course_catalog = {num: Course(num, name, cr) for num, name, cr in COURSE_DATA}
        return course_catalog
    
    def addCourse(self):
        '''
        Add the currently selected course from the schedule listbox to the student's schedule.

        This function:
        1. Retrieves the selected item from the listbox.
        2. Maps the selection to the corresponding `Course` object from `course_catalog`.
        3. Adds the course to the student's set of enrolled courses using `student.addCourse`.
        4. Updates the display to reflect the newly added course.

        If no item is selected in the listbox, the function does nothing.
        '''

        # Get the index of the selected item
        selected_indices = self.schedule_listbox.curselection()
        if not selected_indices:
            return  # No selection

        index = selected_indices[0]

        # Get the key of the course from course_catalog
        # Since listbox items were inserted in order of course_catalog.items()
        course_keys = list(self.course_catalog.keys())
        selected_key = course_keys[index]

        # Get the Course object
        selected_course = self.course_catalog[selected_key]

        # Add to student's courses
        self.student.addCourse(selected_course)

        # Update output
        self.updateDisplay()

    def withdrawCourse(self):
        '''
        Remove the currently selected course from the student's schedule.

        This function:
        1. Retrieves the selected item from the schedule listbox.
        2. Maps the selection to the corresponding `Course` object from `course_catalog`.
        3. Removes the course from the student's set of enrolled courses using `student.withdrawCourse`.
        4. Updates the display to reflect the change.

        If no item is selected in the listbox, the function does nothing.
        '''

        # Get the index of the selected item
        selected_indices = self.schedule_listbox.curselection()
        if not selected_indices:
            return  # No selection

        index = selected_indices[0]

        # Get the key of the course from course_catalog
        course_keys = list(self.course_catalog.keys())
        selected_key = course_keys[index]

        # Get the Course object
        selected_course = self.course_catalog[selected_key]

        # Remove from student's courses
        self.student.withdrawCourse(selected_course)

        # Update output
        self.updateDisplay()

    def saveData(self):
        '''
        Save the current student information and/or course schedule to a text file.

        The function writes a user-readable ASCII file named using the student's name
        (e.g., "<StudentName>_schedule.txt"). If no student name is provided, it defaults
        to "student_schedule.txt". Only data corresponding to checked options is saved:
        - "Include Student Info" checkbox includes the student's name, ID, and GPA.
        - "Include Schedule Info" checkbox includes the list of enrolled courses and the
        total credit hours.

        Displays a message box to confirm successful saving or to show warnings/errors:
        - Warns if no checkboxes are selected.
        - Warns if student info is empty when "Include Student Info" is checked.
        - Shows an error message if the file could not be saved due to an exception.

        '''

        # Check if at least one checkbox is selected
        if self.include_student_var.get() == 0 and self.include_schedule_var.get() == 0:
            messagebox.showwarning("Save Data", "No data selected to save. Please check at least one option.")
            return

        # Check if student info is empty when included
        if self.include_student_var.get() == 1:
            if not self.student.name and not self.student.id_num and self.student.gpa == 0.0:
                messagebox.showwarning("Save Data", "Student information is empty. Cannot save.")
                return

        # Filename using student name or default
        filename = (self.student.name if self.student.name else "student") + "_schedule.txt"

        try:
            with open(filename, "w", encoding="ascii") as f:
                # Include student info if checkbox is checked
                if self.include_student_var.get() == 1:
                    f.write("Student Info:\n")
                    f.write(f"Name: {self.student.name}\n")
                    f.write(f"ID: {self.student.id_num}\n")
                    f.write(f"GPA: {self.student.gpa:.2f}\n\n")

                # Include schedule info if checkbox is checked
                if self.include_schedule_var.get() == 1:
                    f.write("Course Schedule:\n")
                    total_credit_hours = 0
                    if self.student.courses:
                        for course in self.student.courses:
                            f.write(f"{course.num}: {course.name} ({course.crHours} credit hours)\n")
                            total_credit_hours += course.crHours
                    else:
                        f.write("No courses enrolled.\n")

                    f.write("\n")
                    f.write(f"Total Credit Hours: {total_credit_hours}\n")

            # Confirmation message
            messagebox.showinfo("Save Data", f"Data successfully saved to {filename}")

        except Exception as e:
            # Show error message if saving fails
            messagebox.showerror("Save Data Error", f"Failed to save data:\n{e}")

if __name__ == '__main__':
    my_gui = MyGUI()