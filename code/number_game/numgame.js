
addEvent(window, "load", init, false);

    

function addEvent(object, evName, fnName, cap) {
   if (object.attachEvent)
       object.attachEvent("on" + evName, fnName);
   else if (object.addEventListener)
       object.addEventListener(evName, fnName, cap);
}

function removeEvent(object, evName, fnName, cap) {
    if (object.detachEvent)
       object.detachEvent("on" + evName, fnName);
   else if (object.removeEventListener)
       object.removeEventListener(evName, fnName, cap);
}


function init() {
    alert("js loaded"); 

var sButton = document.getElementById("start_button");
addEvent(sButton, "click", startGame, false);
var gButton = document.getElementById("guess_button");
addEvent(gButton, "click", startWarn, false);

        
}

function startWarn() {
    alert("Please set range of numbers and press the Start button.");
}

function getRandInt(min, max) {
  
  var number = Math.floor((Math.random() * ((max-min)+1) + min));
    return number;
}

 function startGame() {
   // alert("Game started");
   
   //STEP 1: SET UP GAME

var gButton = document.getElementById("guess_button");
removeEvent(gButton, "click", startWarn, false);
addEvent(gButton, "click", processGuess, false);

    
    var gCount = 1;
    var result = false;
    var instructS = "Please set range of numbers and press the Start button.";
    var instructG = "Please guess a number, enter it, and press Guess.";
    var ngMessage = "Number(s) Guessed: "
    
    /*var gButton = document.getElementById("guess_button");*/
    var fBox = document.getElementById("from_box"); //lowest number
    var tBox = document.getElementById("to_box"); //highest number
    var gBox = document.getElementById("guess_box"); 
    var mBox = document.getElementById("message_box");
    var nBox = document.getElementById("number_box"); 
    
    
    //get numbers from To and From Box, parse to int  
    var low = parseInt(fBox.value);
    var high = parseInt(tBox.value);
    
    //Generate random number between high and low
    var answer = getRandInt(low, high);
  
    //Change value of message box to instructG message
    mBox.value = instructG;
    alert(answer);
    
    //STEP 2:  THE USER PLAYS THE GAME
    
    do{
    //get number from guess box, parse to int
    var guess = parseInt(gBox.value);
    
    //Compare guess to answer
    result=gButton.onclick.processGuess(guess, answer);
    
    
    //if correct, display alert message (indicate attempts) and remove contents of text boxes 
    if (result) {
       alert("Correct! It took you " + gCount + " attempts to guess this number");
       //Remove contents of all boxes
    tBox.value="";
    fBox.value="";
    nBox.value="";
    gBox.value="";
    //Set contents of message box to Starting instructions
    mBox.value=instructS;
    //Reset number of guesses
    gCount = 1;
    
    //Disable Guess Button until startGame() called again
    removeEvent(gButton, "click", processGuess, false);
    addEvent(gButton, "click", startWarn, false);
    }
    
    else{
    
    //Add guess to guessed numbers string in number box
    //set guess box to empty and focus
    //increment number of guesses by 1
    
    ngMessage += guess + " ";    //may have to parse guess to string
    nBox.value = ngMessage;
    gBox.value="";
    gCount++;
    
    //Determine if guess is lower or higher than answer, give a hint in message box
   if(guess > answer){
        mBox.value = "My number is lower than " + guess;
    }
    else{
        mBox.value = "My number is higher than " + guess;
        }
    }    
    } while (!result);   
       
           
} //End of startGame()


    function processGuess(guess, answer) {
    alert("Guess processed");
   
   if (guess == answer) {
    alert("Result is true");
   return true; 
   }
   
   else {
    alert("Result is false");
    return false;}
    
} //End of processGuess


