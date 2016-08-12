//Must create two image elements, in same position
//#Image1 z-index = 2
//#Image2 z-index=1
//Script tag order is jQuery then image_flipper....before closing body tag


var count=1;
var images = ["katie1.jpg", "sonny7.jpg", "sean1.jpg", "rod4.jpg", "sonnywes3.jpg"];

function changeImage1() {
    
count++;
    if (count >= images.length) {
        count=0;
    }   
    
    $('#image1').attr("src", images[count]);
    var testsrc =  $('#image1').attr("src");
    
}

function changeImage2() {
   
    count++;
    if (count >= images.length) {
        count=0;
    }  
    
    $('#image2').attr("src", images[count]);
    
}

function fadeOut1() {
    $('#image1').fadeOut(2000, changeImage1);
}

function fadeIn1() {
    $('#image1').fadeIn(2000, changeImage2);
}

function beginFlip() {
    $(fadeIn1);
    setTimeout(fadeOut1, 5000);
}

$(document).ready(function(){
    
    setTimeout(fadeOut1, 5000);
    setInterval(beginFlip, 10000);
                               
})

/*
Sample HTML:

 <div id="main_flipper">
        <img src="logo.jpg" height="200px" width="200px" style="z-index: 2;" id="image1" />
        <img src="India.jpg" height="200px" width="200px" style="z-index: 1;" id="image2" /> 
    <br />
    <br />    
    </div>
     
    <script type="text/javascript" src="jquery.js"></script>
    <script type="text/javascript" src="jqTest.js"></script>
   
Sample CSS:

 #main_flipper img{position: absolute;
                        left: 500px;
                        top: 450px;}
                     
*/
