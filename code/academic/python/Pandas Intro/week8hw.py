# Author:       Rod Ciombor
# Date:         03/23/2026
# Instructor:   Dr. Sheikh Shamsuddin
# Class:        CIS-2532-NET01

import numpy as np
import pandas as pd
import os

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))

TEMPS = {
        'Maxine' : [98.6, 98.9, 100.2], 
        'James': [97.9, 98.4, 99.2], 
        'Amanda': [100.3, 99.7, 98.1]
    }

def question1():
    print('Question a: Series from list')
    print('=' * 50)
    s = pd.Series([7, 11, 13, 17])
    print(s)
    print()

    print('Question b: Series of five 100.0 values')
    print('=' * 50)
    s = pd.Series([100.0] * 5)
    print(s)
    print()

    print('Question c: Describe Series of 20 random numbers (range 0 - 100)')
    print('=' * 70)
    s = pd.Series(np.random.randint(0, 101, 20))
    print(s.describe())
    print()

    print('Question d: Series with custom indices')
    print('=' * 50)
    temperatures = pd.Series(
        [98.6, 98.9, 100.2, 97.9], 
        index=['Julie', 'Charlie', 'Sam', 'Andrea']
    )
    print(temperatures)
    print()

    print('Question e: Series from dictionary')
    print('=' * 50)
    d = temperatures.to_dict()
    print('Dictionary:')
    print(d)
    s = pd.Series(d)
    print()
    print('Series:')
    print(s)
    print()

    print('Question f: DataFrame from dictionary')
    print('=' * 50)
    df = pd.DataFrame(TEMPS)
    print(df)
    print()

    print('Question g: DataFrame w/ custom indices')
    print('=' * 50)
    temperatures = pd.DataFrame(TEMPS, index=['Morning', 'Afternoon', 'Evening'])
    print(temperatures)
    print()

    print('Question h: All temps for Maxine')
    print('=' * 50)
    print(temperatures.loc[:, 'Maxine'])
    print()

    print('Question i: Morning temps')
    print('=' * 50)
    print(temperatures.loc['Morning'])
    print()

    print('Question j: Morning & Evening temps')
    print('=' * 50)
    print(temperatures.loc[['Morning', 'Evening']])
    print()

    print('Question k:  All temps Amanda & Maxine')
    print('=' * 50)
    print(temperatures.loc[:, ['Amanda', 'Maxine']])
    print()

    print('Question l:  Morning & Afternoon temps of Amanda & Maxine')
    print('=' * 60)
    print(temperatures.loc[['Morning', 'Afternoon'], ['Amanda', 'Maxine']])
    print()

    print('Question m:  Describe DataFrame')
    print('=' * 50)
    print(temperatures.describe())
    print()

    print('Question n: Transposed')
    print('=' * 50)
    print(temperatures.T)
    print()

    print('Question o: Sorted by column alphabetically')
    print('=' * 50)
    print(temperatures.sort_index(axis=1))
    print()

def question2():

    filePath = os.path.join(SCRIPT_DIR, 'titanic.csv')
    data = pd.read_csv(filePath)

    # Open output file
    outputFilePath = os.path.join(SCRIPT_DIR, 'Week8_report.txt')
    with open(outputFilePath, 'w') as f:

        # Helper function to print and write simultaneously
        def pw(line):
            print(line)
            f.write(line + '\n')

        # How many passengers
        pw(f'There were {data.shape[0]} passengers on the Titanic.')

        # How many male and female passengers
        values = data['sex'].value_counts()
        pw(f"Number of Male Passengers: {values['male']}")
        pw(f"Number of Female Passengers: {values['female']}")

        # Average age
        pw(f'The average age of all passengers was {data["age"].mean():.2f}')

        # Under 21
        pw(f'There are {(data["age"] < 21).sum()} passengers under 21 years old.')

        # Survived
        survived = data[data['survived'].str.lower() == 'yes']
        males = survived[survived['sex'].str.lower() == 'male']
        females = survived[survived['sex'].str.lower() == 'female']
        pw(f'Total Passengers Survived: {survived.shape[0]}, Male {males.shape[0]}, Female {females.shape[0]}')

        # Deceased
        deceased = data[data['survived'].str.lower() == 'no']
        males = deceased[deceased['sex'].str.lower() == 'male']
        females = deceased[deceased['sex'].str.lower() == 'female']
        pw(f'Total Passengers Deceased: {deceased.shape[0]}, Male {males.shape[0]}, Female {females.shape[0]}')

        # Youngest survivor
        youngest = survived.loc[survived['age'].idxmin()]
        pronoun = 'She' if youngest['sex'].lower() == 'female' else 'He'
        pw(f'The youngest survivor was {youngest["Name"]}. {pronoun} was {float(youngest["age"]):.2f} years old.')

        # Oldest survivor
        oldest = survived.loc[survived['age'].idxmax()]
        pronoun = 'She' if oldest['sex'].lower() == 'female' else 'He'
        pw(f'The oldest survivor was {oldest["Name"]}. {pronoun} was {float(oldest["age"]):.1f} years old.')

        # List of passengers
        pw('List of Passengers:')
        for name in data['Name']:
            pw(name)
