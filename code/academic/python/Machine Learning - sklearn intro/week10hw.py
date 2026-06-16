# Author:       Rod Ciombor
# Date:         04/20/2026
# Instructor:   Dr. Sheikh Shamsuddin
# Class:        CIS-2532-NET01

import seaborn as sns
import numpy as np
import pandas as pd
import matplotlib.pyplot as plt
import os

from sklearn.datasets import fetch_california_housing
from sklearn.model_selection import train_test_split
from sklearn.linear_model import LinearRegression

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
NYC_DATA = os.path.join(SCRIPT_DIR, 'ave_yearly_temp_nyc_1895-2017.csv')

def showYearlyHighTempTrend():
    #Load dataset
    nyc = pd.read_csv(NYC_DATA)
    #Rename columns
    nyc.columns = ['Date', 'Temperature', 'Anomaly']
    
    #Remove last two digits from dates
    nyc.Date = nyc.Date.floordiv(100)

    #Divide dataset into training and test datasets
    #Training is to train the model
    #Testing is to test the validity of the model
    X_train, X_test, y_train, y_test = train_test_split(
        nyc.Date.values.reshape(-1, 1), 
        nyc.Temperature.values, 
        random_state=11
    )

    #Create model/estimator
    linear_regression = LinearRegression()

    #Load both training sets (data = X, targets = Y) into estimator
    linear_regression.fit(X_train, y_train)

    #Set model parameters
    LinearRegression(
        copy_X=True,
        fit_intercept=True,
        n_jobs=None
    )

    #Anonymous function to predict slope
    predict = (lambda x: linear_regression.coef_ * x + linear_regression.intercept_)

    #Create scatterplot
    axes = sns.scatterplot(
        data=nyc, 
        x='Date', 
        y='Temperature', 
        hue='Temperature', 
        palette='winter', 
        legend=False
    )
    axes.set_ylim(10,70)
    axes.set_title("Average Yearly High Temperatures Over Time")

    #Create array of x-coordinates of regression line's start and end points
    x = np.array([nyc.Date.values.min(), nyc.Date.values.max()])

    #Create array of predicted y-coordinate values
    y = predict(x)

    #Plot the line
    line = plt.plot(x, y)

    #Show the plot
    plt.show()

def showJanuaryHighTempTrend():
    # Load dataset
    nyc = pd.read_csv(NYC_DATA)

    # Rename columns to reflect meaning
    nyc.columns = ['Date', 'JanHighTemp', 'Anomaly']

    # Convert Date to year only (removes last two digits if formatted YYYYMM)
    nyc.Date = nyc.Date.floordiv(100)

    # Split into training and test sets
    X_train, X_test, y_train, y_test = train_test_split(
        nyc.Date.values.reshape(-1, 1),
        nyc.JanHighTemp.values,
        random_state=11
    )

    # Create and train model
    linear_regression = LinearRegression()
    linear_regression.fit(X_train, y_train)

    # Prediction function (y = mx + b)
    predict = lambda x: linear_regression.coef_ * x + linear_regression.intercept_

    # Scatterplot: January average high temperatures over time
    '''
    axes = sns.scatterplot(
        data=nyc,
        x='Date',
        y='JanHighTemp',
        color='blue',
        legend=False
    )
    '''

    axes = sns.scatterplot(
        data=nyc, 
        x='Date', 
        y='JanHighTemp', 
        hue='JanHighTemp', 
        palette='winter', 
        legend=False
    )

    axes.set_title("Average January High Temperatures Over Time")
    axes.set_ylabel("Temperature")
    axes.set_xlabel("Year")
    axes.set_ylim(10, 70)

    # Regression line
    x = np.array([nyc.Date.min(), nyc.Date.max()])
    y = predict(x)
    plt.plot(x, y)

    plt.show()

def caliHousingPairPlot(numFeatures):

    # numFeatures indicates how many features from the Cali Housing Dataset to display
    
    # Load dataset
    cali = fetch_california_housing()

    pd.set_option('display.max_columns', 10)
    pd.set_option('display.width', None)

    # Create DataFrame
    cali_df = pd.DataFrame(cali.data, columns=cali.feature_names)

    # Add target (median house value)
    cali_df['MedHouseValue'] = cali.target

    # Create bins for a pseudo "category" (for hue)
    # Don't want hundreds of different hue values
    cali_df['PriceCategory'] = pd.cut(
        cali_df['MedHouseValue'],
        bins=3,
        labels=['Low', 'Medium', 'High']
    )

    # Seaborn styling
    sns.set_theme(font_scale=1.1)
    sns.set_style('whitegrid')

    # Create Pairplot
    grid = sns.pairplot(
        data=cali_df,
        vars=cali_df.columns[0:numFeatures],  # first 4 numeric features
        hue='PriceCategory'
    )

    #Show plot
    plt.show()

caliHousingPairPlot(4)