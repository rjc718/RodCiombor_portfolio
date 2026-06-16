from tensorflow.keras.datasets import mnist
from tensorflow.keras.utils import to_categorical
from tensorflow.keras.models import Sequential
from tensorflow.keras.layers import Conv2D, Dense, Flatten, MaxPooling2D


import matplotlib.pyplot as plt
import seaborn as sns
import numpy as np

(X_train, y_train), (X_test, y_test) = mnist.load_data()

#X_train.shape
#y_train.shape
#X_test.shape
#y_test.shape

#sns.set(font_scale=2)
index = np.random.choice(np.arange(len(X_train)), 24, replace=False)
figure, axes = plt.subplots(nrows=4, ncols=6, figsize=(16, 9))
for item in zip(axes.ravel(), X_train[index], y_train[index]):
    axes, image, target = item
    axes.imshow(image, cmap=plt.cm.gray_r)
    axes.set_xticks([])
    axes.set_yticks([])
    axes.set_title(target)
plt.tight_layout()

X_train = X_train.reshape((60000, 28, 28, 1))
#X_train.shape
X_test.reshape((10000, 28, 28, 1))
#X_train.shape

X_train = X_train.astype('float32') / 255
X_test = X_test.astype('float32') / 255

y_train = to_categorical(y_train)
y_test = to_categorical(y_test)

#Create model
cnn = Sequential()
#Add Convolution Layer
cnn.add(
    Conv2D(
        filters=64, 
        kernel_size=(3, 3), 
        activation='relu'
    ), 
    input_shape=(28, 28, 1)
)

#Add Pooling Layer
cnn.add(MaxPooling2D(pool_size=(2,2)))

#Add Additional Convolution Layer
cnn.add(
    Conv2D(
        filters=128, 
        kernel_size=(3, 3), 
        activation='relu'
    )
)

#Add Additional Pooling Layer
cnn.add(MaxPooling2D(pool_size=(2,2)))\

#Add Flatten  Layer to reduce output to one-dimensional array
cnn.add(Flatten())

#Add Dense layer to create neurons that learn from previouslayer outputs
cnn.add(Dense(units=128, activation='relu'))

#Add Dense layer
#Classify inputs into neaurons representing class 0 - 9
# Convert neurons into classification probabilities
cnn.add(Dense(units=10, activation='softmax')) 

#cnn.summary()

#Compile the model
cnn.compile(
    optimizer='adam', 
    loss='categorical_crossentropy', 
    metrics=['accuracy']
)

#Train the model using training data
cnn.fit(
    X_train,
    y_train,
    epoch=5,
    batch_size=64,
    validation_split=0.1
)

#Check the model's accurary using test data it hasn't seen yet
loss, accuracy = cnn.evaluate(X_test, y_test)
print(loss)
print(accuracy)

#Predict classes of data in test array
#Creates list of probabilities for each element
predictions = cnn.predict(X_test)

#Check the probability for the first element in the array
#All the elements in predictions will be a percentage
#Shows how likely a data item will belong to a certain class
#In example, one index was 99%, the others were a tiny fraction
#Lets us know which class the test data element belonged to.
for index, probability in enumerate(predictions[0]):
    print('f{index}: {probability:.10%}')

#Find incorrect predictions
#Compare index with highest value in predictions and y_test
#If they don't match, prediction is wrong
#Reshape to 28 x 28 for Matplotlib requirements
images = X_test.reshape((10000, 28, 28))
incorrect_predictions = []

for i, (p, e) in enumerate(zip(predictions, y_test)):
    predicted, expected = np.argmax(p), np.argmax(e)

    if predicted != expected:
        incorrect_predictions.append(i, images[i], predicted, expected)

print(len(incorrect_predictions))

#Display incorrect images
figure, axes = plt.subplots(nrows=4, ncols=6, figsize=(16, 12))

for axes, item in zip(axes.ravel(), incorrect_predictions):
    index, image, predicted, expected = item
    axes.imshow(image, cmap=plt.cm.gray_r)
    axes.set_xticks([])  # remove x-axis tick marks
    axes.set_yticks([])  # remove y-axis tick marks
    axes.set_title(
    f'index: {index}\np: {predicted}; e: {expected}')
plt.tight_layout()

#Function for displaying probabilities of incorrect predictions
def display_probabilities(prediction):
    for index, probability in enumerate(prediction):
        print(f'{index}: {probability:.10%}')

#Run function to probabilites of specific image/element
# Variability in percentages at classification indexes will who where it made mistakes
display_probabilities(predictions[495]) 

#Save model state
cnn.save('minst_cnn.h5')