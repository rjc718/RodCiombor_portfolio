# Author:       Rod Ciombor
# Date:         02/15/2026
# Instructor:   Dr. Sheikh Shamsuddin
# Class:        CIS-2532-NET01
# Descr:
#   Program prompts user to enter two matrixes.
#   After validating the user input, 
#   it reshapes each matrix into a 3 x 3 list
#   then multiplies them by each other and prints the result.

import MultiplyMatrixLib as lib

def main():
    '''
    This function prompts user to enter two matrixes.
    After validating the user input, 
    it reshapes each matrix into a 3 x 3 list
    then multiplies them by each other and prints the result.
    '''

    matrix1 = lib.getMatrixInput('Enter matrix 1: ')
    matrix2 = lib.getMatrixInput('Enter matrix 2: ')
    
    #Reshape both matrices to 3 x 3
    matrix1 = lib.reshapeMatrix(matrix1, 3)
    matrix2 = lib.reshapeMatrix(matrix2, 3)

    #Multiply both matrices and display product
    lib.displayResult(matrix1, matrix2)

main()