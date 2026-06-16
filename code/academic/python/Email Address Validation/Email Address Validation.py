# Author:   Rod Ciombor
# Date:     10/23/2025
# Class:    CIS2531-NET01
# Descr:
#   Program prompts user to enter filename
#   containing a series of email addresses.
#   It then reads the emails, validates them,
#   then prints the email and their validity status
#   Also displays the total emails, and total valid and invalid emails

EMAIL_COL_HEADER = 'EMAIL ADDRESS'
VAL_COL_HEADER = 'VALIDATION STATUS'
DOMAIN_MAX = 190
LOCAL_MAX = 64
LOCAL_ALLOWED_CHARS = ('.', '-', '_')
INVALID_PARTS_STATUS = 3
INVALID_DOMAIN_STATUS = 2
INVALID_LOCAL_STATUS = 1
VALID_STATUS = 0


def checkEmailParts(email):
    '''
    Checks if the given email contains exactly one '@' character.

    Args:
        email (str): The email address to validate.

    Returns:
        bool: True if the email contains one '@' and splits into two parts,
              otherwise False.
    '''
    #check for @
    if '@' not in email:
        return False

    #Split at @
    #Should be exactly two tokens
    tokens = email.split('@')
    if len(tokens) != 2:
        return False
    else:
        return True
        

def checkLocal(local):
    '''
    Validates the local part (before '@') of the email.

    Conditions:
        - Must be between 1 and 64 characters long.
        - Must only contain alphanumeric characters, dots, hyphens, or underscores.

    Args:
        local (str): The local part of the email.

    Returns:
        bool: True if valid, otherwise False.
    '''
    
    #Check between 1 and 64 characters
    length = len(local)

    if length < 1 or length > LOCAL_MAX:
        return False
    else:
        #loop through all letters
        for char in local:
            #Must be alphanumeric
            #Or dot, hyphen or underscore
            if not char.isalnum() and char not in LOCAL_ALLOWED_CHARS:
                return False
        
        return True

def checkTopLevelDomain(domain):
    '''
    Validates the top-level domain (e.g., 'com', 'org', 'uk').

    Conditions:
        - Must be at least 2 characters long.
        - Must consist only of lowercase alphabetical characters.

    Args:
        domain (str): The top-level domain portion of the email domain.

    Returns:
        bool: True if valid, otherwise False.
    '''
    
    #Must be minimum two letters
    #Must be alphabetical
    #Must be lowercase
    if len(domain) < 2:
        return False
    elif not domain.isalpha():
        return False
    elif not domain.islower():
        return False
    else:
        return True

def checkDomain(domain):
    '''
    Validates the domain part (after '@') of the email.

    Conditions:
        - Must be 190 characters or fewer.
        - Must have 2 or 3 dot-separated labels.
        - Top-level domain must pass `checkTopLevelDomain()`.
        - Labels cannot start or end with hyphens.
        - Labels must be alphanumeric or contain hyphens.

    Args:
        domain (str): The domain part of the email.

    Returns:
        bool: True if valid, otherwise False.
    '''
    
    #May be max 190 characters
    if len(domain) > DOMAIN_MAX:
        return False
    
    #split at dot
    #should be 2 or 3 tokens
    labels = []
    tokens = domain.split('.')
    if len(tokens) == 3:
        topLevel = tokens[2]
        labels.append(tokens[0])
        labels.append(tokens[1])
    elif len(tokens) == 2:
        topLevel = tokens[1]
        labels.append(tokens[0])
    else:
        return False
    
    #validate topLevel
    if not checkTopLevelDomain(topLevel):
        return False
     
    #loop through labels
    for word in labels:
        #First and last letter may not be hyphen
        if len(word) == 0:
            return False
        elif word[0] == '-' or word[-1] == '-':
            return False
        else:
            #May contain alphanumeric characters or hyphens
            for char in word:
                if not char.isalnum() and char != '-':
                    return False

    return True

def getStatusMessage(status):
    '''
    Converts a numeric status code into a human-readable message.

    Args:
        status (int): One of the defined constants:
                      3 - Invalid: missing parts
                      2 - Invalid: domain part
                      1 - Invalid: local part
                      0 - Valid

    Returns:
        str: Corresponding status message.
    '''
    
    match status:
        case 3:
            return 'Invalid: missing parts'
        case 2:
            return 'Invalid: domain part'
        case 1:
            return 'Invalid: local part'
        case _:
            return 'Valid'

def validateEmail(email):
    '''
    Validates the overall email address by checking its parts.

    Steps:
        1. Ensure there is exactly one '@' character.
        2. Split into local and domain parts.
        3. Validate domain using `checkDomain()`.
        4. Validate local part using `checkLocal()`.

    Args:
        email (str): The email address to validate.

    Returns:
        int: One of the predefined status constants indicating validation result.
    '''

    status = VALID_STATUS
    if not checkEmailParts(email):
        status = INVALID_PARTS_STATUS
    else:
        tokens = email.split('@')
        local = tokens[0]
        domain = tokens[1]
        
        if not checkDomain(domain):
            status = INVALID_DOMAIN_STATUS
        elif not checkLocal(local):
            status = INVALID_LOCAL_STATUS
            
    return status

def printColHeaders():
    '''
    Prints the column headers and divider lines for the email validation table.
    '''
    
    print()
    print(f'{EMAIL_COL_HEADER:<50}{VAL_COL_HEADER:<50}')
    bar1 = '=' * len(EMAIL_COL_HEADER)
    bar2 = '=' * len(VAL_COL_HEADER)
    print(f'{bar1:<50}{bar2:<50}')

def main():
    '''
    Main function that runs the email validation program.

    Prompts user for an input filename, reads each email, validates it,
    prints formatted results, and displays summary statistics.
    '''
    
    totalEmails = 0
    validEmails = 0
    invalidEmails = 0

    #Open input file if valid name given
    isValid = False
    while not isValid:
        fileName = str(input('Enter the filename to read email addresses: '))
        try:
            inputFile = open(fileName, 'r')
        except FileNotFoundError:
            print('ERROR! Problem opening file!')
        else:
            isValid = True

    #Print column headers
    printColHeaders()

    #Loop through all emails
    #Read email from file
    for line in inputFile:

        #Clean input
        email = str(line.strip())
        
        totalEmails = totalEmails + 1
        status = validateEmail(email)
        
        if status == 0:
            validEmails = validEmails + 1
        else:
            invalidEmails = invalidEmails + 1

        print(f'{email:<50}{getStatusMessage(status):<50}')

    #Diplay totals
    print()
    print(f'Number of emails read from file = {totalEmails}')
    print(f'Number of valid emails read from file = {validEmails}')
    print(f'Number of invalid emails read from file = {invalidEmails}')
    print()
    print('Thank you for using the email format validation tool!')

    #Close file 
    inputFile.close()

if __name__ == '__main__':
    main()

# ***OUTPUT***
#
# Enter the filename to read email addresses: EmailAddressestxt
# ERROR! Problem opening file!
# Enter the filename to read email addresses: EmailAddresses.txt
# 
# EMAIL ADDRESS                                     VALIDATION STATUS                                 
# =============                                     =================                                 
# valid@example.com                                 Valid                                             
# invalid@example.c0m                               Invalid: domain part                              
# VAL1D@EXAMPLE.com                                 Valid                                             
# invalid@EXAMPLE.COM                               Invalid: domain part                              
# invalid@example.com-org                           Invalid: domain part                              
# valid@example.com.org                             Valid                                             
# 123@456.ab                                        Valid                                             
# 123@456.a                                         Invalid: domain part                              
# 123@456.AB                                        Invalid: domain part                              
# 123@456.78                                        Invalid: domain part                              
# valid-email@example.com                           Valid                                             
# valid.email@example.com                           Valid                                             
# valid_email@example.com                           Valid                                             
# inv@lid@example.com                               Invalid: missing parts                            
# invalid_$@example.com                             Invalid: local part                               
# invalid@                                          Invalid: domain part                              
# invalid_toolonglocal_toolonglocal_toolonglocal_toolonglocal_toolonglocal@example.comInvalid: local part                               
# invalid@toolongdomain.toolongdomain.toolongdomain.toolongdomain.toolongdomain.toolongdomain.toolongdomain.toolongdomain.toolongdomain.toolongdomain.toolongdomain.toolongdomain.toolongdomain.example.comInvalid: domain part                              
# invalid@example                                   Invalid: domain part                              
# invalid@.                                         Invalid: domain part                              
# invalid-email@.com                                Invalid: domain part                              
# valid@domain.co.uk                                Valid                                             
# invalid@-domain.com                               Invalid: domain part                              
# invalid@domain.com-                               Invalid: domain part                              
# invalid@domain.c                                  Invalid: domain part                              
# invalid.com                                       Invalid: missing parts                            
# @invalid.com                                      Invalid: local part                               
# 
# Number of emails read from file = 27
# Number of valid emails read from file = 8
# Number of invalid emails read from file = 19
# 
# Thank you for using the email format validation tool!






