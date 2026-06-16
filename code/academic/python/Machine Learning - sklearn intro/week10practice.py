import seaborn as sns
import numpy as np
import pandas as pd
import matplotlib.pyplot as plt
import os
from sklearn.datasets import load_digits
from sklearn.datasets import load_iris
from sklearn.datasets import fetch_california_housing
from sklearn.model_selection import train_test_split
from sklearn.neighbors import KNeighborsClassifier
from sklearn.linear_model import LinearRegression

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
NYC_DATA = os.path.join(SCRIPT_DIR, 'ave_yearly_temp_nyc_1895-2017.csv')

def question1():
    housing = fetch_california_housing()
    df = pd.DataFrame(housing.data, columns=housing.feature_names)
    df['target'] = housing.target
    sns.pairplot(df)
    plt.show()

def question2():
    #Do seciton 15.4 but use a different dataset
    pass


def question15_2():
    #Load dataset
    digits = load_digits()

    #Divide dataset into training and test datasets
    #Training is to train the model
    #Testing is to test the validity of the model
    x_train, x_test, y_train, y_test = train_test_split(
        digits.data, digits.target, random_state=11, test_size = 0.2
    )

    #Create model/estimator
    knn = KNeighborsClassifier()

    #Load both training sets (data = X, targets = Y) into estimator
    knn.fit(x_train, y_train)

    #Set model parameters
    KNeighborsClassifier(
        algorithm='auto',
        leaf_size='30',
        metric='minkowski',
        metric_params=None,
        n_jobs=None, 
        n_neighbors=5, 
        p=2, 
        weights='uniform'
    )
    #Make predictions and compare them to expected
    predicted = knn.predict(x_test)
    expected = y_test

    #Locate all incorrect predictions
    wrong = [(p,e) for (p,e) in zip(predicted, expected) if p != e]
    #PRint accuracy percentage
    numCorrect = len(expected) - len(wrong)
    
    print(numCorrect / len(expected))


    '''
    figure, axes = plt.subplots(nrows=4, ncols=6, figsize=(6,4))
    for item in zip(axes.ravel(), digits.images, digits.target):
        axes, image, target = item
        axes.imshow(image, cmap=plt.cm.gray_r)
        axes.set_xticks([])
        axes.set_yticks([])
        axes.set_title(target)
    plt.tight_layout()

    plt.show()
    '''
    

def question15_3():
    print('Hello World')

def question15_4():
    #Load dataset
    nyc = pd.read_csv(NYC_DATA)
    #Rename columns
    nyc.columns = ['Date', 'Temperature', 'Anomaly'];
    
    #Remove last two digits from dates
    nyc.Date = nyc.Date.floordiv(100)

    #print(nyc.head(3))

    #Divide dataset into training and test datasets
    #Training is to train the model
    #Testing is to test the validity of the model
    X_train, X_test, y_train, y_test = train_test_split(
        nyc.Date.values.reshape(-1, 1), nyc.Temperature.values, random_state=11
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
    #print(linear_regression.coef_)
    #print(linear_regression.intercept_)

  
    '''
    predicted = linear_regression.predict(X_test)
    expected = y_test

    for p, e in zip(predicted[::5], expected[::5]):
        print(f'predicted: {p:2f}, expected: {e:.2f}')
    '''
    #Anonymous function to calculate slope: y = mx + b
    #.coef_ is m, .intercept_ is b
    #predict = (lambda x: linear_regression.coef_ * x + linear_regression.intercept_)
    #print(predict(2019))
    #print(predict(1890))

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

    plt.show()

def question15_4b():
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
    axes = sns.scatterplot(
        data=nyc,
        x='Date',
        y='JanHighTemp',
        color='blue',
        legend=False
    )

    axes.set_title("Average January High Temperatures Over Time")
    axes.set_ylabel("Temperature")
    axes.set_xlabel("Year")

    # Optional: fix axis range for readability
    axes.set_ylim(10, 70)

    # Regression line
    x = np.array([nyc.Date.min(), nyc.Date.max()])
    y = predict(x)
    plt.plot(x, y, color='red')

    plt.show()

def question15_7a():
    #Load dataset
    iris = load_iris()

    pd.set_option('display.max_columns', 5)
    pd.set_option('display.width', None)

    #Create Dataframe
    iris_df = pd.DataFrame(iris.data, columns=iris.feature_names)

    #Create special column
    iris_df['species'] = [iris.target_names[i] for i in iris.target]

    #Create pairplot
    sns.set_theme(font_scale=1.1)
    sns.set_style('whitegrid')
    grid = sns.pairplot(
        data=iris_df,
        vars=iris_df.columns[0:4],
        hue='species'
    )
    plt.show()

def question15_7b(numFeatures):
    # Load dataset
    cali = fetch_california_housing()

    pd.set_option('display.max_columns', 10)
    pd.set_option('display.width', None)

    # Create DataFrame
    cali_df = pd.DataFrame(cali.data, columns=cali.feature_names)

    # Add target (median house value)
    cali_df['MedHouseValue'] = cali.target

    # Create bins for a pseudo "category" (for hue)
    #Don't want hundreds of different hue values
    cali_df['PriceCategory'] = pd.cut(
        cali_df['MedHouseValue'],
        bins=3,
        labels=['Low', 'Medium', 'High']
    )

    # Seaborn styling
    sns.set_theme(font_scale=1.1)
    sns.set_style('whitegrid')

    # Pairplot
    grid = sns.pairplot(
        data=cali_df,
        vars=cali_df.columns[0:numFeatures],  # first 4 numeric features
        hue='PriceCategory'
    )

    plt.show()


question15_7b(4)