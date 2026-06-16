# Author:       Rod Ciombor
# Date:         02/15/2026
# Instructor:   Dr. Sheikh Shamsuddin
# Class:        CIS-2532-NET01

def validateMatrix(matrix):
    '''
    This function checks if a matrix contains exactly 9 elements
    with numeric values.

    It will return False if any of these conditions are not met
    '''
    #Check if list is 9 items in length
    if len(matrix) != 9:
        print('Matrix must contain 9 elements!')
        return False
    
    for num in matrix:
        try:
            float(num)
        except ValueError:
            print('All values in matrix must be numeric!')
            return False
    return True

def getMatrixInput(msg):
    '''
    This function prompts the user to enter a matrix of numbers,
    prompting the user to enter a new matrix if invalid data is entered.

    If the matrix is valid, it will parse all the elements as floats, 
    pass the elements to a new list, then return that list

    The msg argument is the input message that will display 
    to prompt the user.
    '''
    output = []
    isValid = False
    while not isValid:
        matrixInput = str(input(msg))
        matrix = matrixInput.split()
        isValid = validateMatrix(matrix)

    for num in matrix:
        output.append(float(num))
    
    return output

def reshapeMatrix(list, cols_per_row):
    '''This function reshapes a list into a 3 x 3 multi-dimensional list'''
    result = []
    for i in range(0, len(list), cols_per_row):
        upper = i + cols_per_row
        row = list[i:upper]
        result.append(row)
    return result

def multiplyMatrix(a, b):
    '''
    This function calculates the product of two alrebraic matrices 
    represented by multi-dimensional lists
    '''
    # Number of rows and columns
    rowsA = len(a)
    colsA = len(a[0])
    colsB = len(b[0])

    # Create result matrix filled
    result = []
    for i in range(rowsA):
        row = []
        for j in range(colsB):
            total = 0
            for k in range(colsA):
                total += a[i][k] * b[k][j]
            row.append(total)
        result.append(row)

    return result

def displayResult(a, b):
    '''
    This function displays the product of two alrebraic matrices 
    represented by multi-dimensional lists
    '''
    print()
    print('The multiplication of the matrices is')
    product = multiplyMatrix(a, b)
    for i in range(len(a)):
        strA = " ".join(f"{x:.1f}" for x in a[i])
        strB = " ".join(f"{x:.1f}" for x in b[i])
        strProd = " ".join(f"{x:.1f}" for x in product[i])
        if i == 1:
            print(f'{strA}  *  {strB}  =  {strProd}')
        else:    
            print(f'{strA}     {strB}     {strProd}')