let slideIndex = 1;

/* Begin Click Load Videos */
function loadClickLoadVideo(div) 
{
    let vidType = div.dataset.vidType;
    let src = div.dataset.src;
     
    let height = div.dataset.height;
    let width = div.dataset.width;
    let video;

    let viewport = window.innerWidth;
    if(viewport <= 480){
        height = 275;
    }
    else if(viewport <= 768){
        height = 375;
    }

    switch(vidType) {
        case 'playlist':
            
            if(!height){
                height = '425';
            }
            if(!width){
                width = '100%';
            }

            video = document.createElement('iframe');
            video.setAttribute('type', 'text/html');
            video.setAttribute('width', width);
            video.setAttribute('height', height);
            video.setAttribute('frameborder', '0');
            video.setAttribute('src', src);
        break;
        case 'custom_player':

            if(!height){
                height = '';
            }
            if(!width){
                width = '100%';
            }

            video = document.createElement('object');
            video.setAttribute('width', width);
            video.setAttribute('height', height);
           
            let param1 = document.createElement('param');
            param1.setAttribute('name', 'movie');
            param1.setAttribute('value', src);

            let param2 = document.createElement('param');
            param2.setAttribute('name', 'allowFullScreen');
            param2.setAttribute('value', 'true');

            let param3 = document.createElement('param');
            param3.setAttribute('name', 'allowscriptaccess');
            param3.setAttribute('value', 'always');

            let param4 = document.createElement('param');
            param4.setAttribute('name', 'wmode');
            param4.setAttribute('value', 'opaque');

            let embed = document.createElement('embed');
            embed.setAttribute('src', src);
            embed.setAttribute('type', 'application/x-shockwave-flash');
            embed.setAttribute('allowscriptaccess', 'always');
            embed.setAttribute('allowfullscreen', 'true');
            embed.setAttribute('wmode', 'opaque');
            embed.setAttribute('width', width);
            embed.setAttribute('height', height);

            video.appendChild(param1);
            video.appendChild(param2);
            video.appendChild(param3);
            video.appendChild(param4);
            video.appendChild(embed);
        break;
        default:
            video = document.createElement('iframe');
            video.setAttribute('src', src  + '&autoplay=1');
            video.setAttribute('frameborder', '0');
            video.setAttribute('allowfullscreen', '1');
            video.setAttribute(
                'allow',
                'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture'
            );
                       
            if(width){
                video.setAttribute('width', width);
            }
            if(height){
                video.setAttribute('height', height);
            }
    }
    div.parentNode.replaceChild(video, div);
}

function createClickLoadVideo(element)
{
    let data = element.dataset;

    let videoId = data.id;
    let vidType = data.vidType;
 
    let dataHeight = data.height;
    let dataWidth = data.width;
    let src = data.src;
    let div = document.createElement('div');

    div.setAttribute('data-id', videoId);
    div.setAttribute('data-src', src);
    div.setAttribute('data-vid-type', vidType);

    if(dataHeight){
        div.setAttribute('data-height', dataHeight);
    }
    if(dataWidth){
        div.setAttribute('data-width', dataWidth);
    }
    let thumbNode = document.createElement('img');
    thumbNode.src = '//i.ytimg.com/vi/ID/hqdefault.jpg'.replace('ID', videoId);
    thumbNode.setAttribute('alt', 'Click to Play Video');
    div.appendChild(thumbNode);
        
    let playButton = document.createElement('div');
    playButton.setAttribute('class', 'play');
    playButton.setAttribute('tabindex', '0');
    playButton.setAttribute('role', 'button');
    playButton.setAttribute('aria-label', 'Play Video');
    playButton.onclick = function () {
        loadClickLoadVideo(this);
    };
    handleEnterKey(playButton);
        
    div.appendChild(playButton);
    div.onclick = function () {
        loadClickLoadVideo(this);
    };
    element.appendChild(div);
}

function initClickLoadVideos() 
{
    let playerElements = document.getElementsByClassName('clickLoadVideo');
    for (let n = 0; n < playerElements.length; n++) {
        let element = playerElements[n];
        createClickLoadVideo(element);
    }
}
/* End Click Load Videos */

/* Begin Video Gallery */
// Next/previous controls
function plusSlides(n) 
{
    showSlides(slideIndex += n);
}
  
// Thumbnail image controls
function currentSlide(n) 
{
    showSlides(slideIndex = n);
}
  
function showSlides(n) 
{
    let i;
    let slides = document.getElementsByClassName("mySlides");

    let dots = document.getElementsByClassName("dot");
    if (n > slides.length) {
        slideIndex = 1
    }
    if (n < 1) {
        slideIndex = slides.length
    }
    for (i = 0; i < slides.length; i++) {
      slides[i].style.display = "none";
    }
    for (i = 0; i < dots.length; i++) {
      dots[i].className = dots[i].className.replace(" active", "");
    }
    
    slides[slideIndex-1].style.display = "block";
    dots[slideIndex-1].className += " active";
}

function replaceVideos()
{
    let iframes = document.querySelectorAll('.video-gallery iframe');
    iframes.forEach(function(iframe){
        let parent = iframe.parentElement;
        iframe.remove();
        createClickLoadVideo(parent);
    });
}

function initVideoGallery()
{    
    showSlides(slideIndex);
    
    let prev = document.querySelector('.prev');
    prev.onclick = function () {
        replaceVideos();
        plusSlides(-1)  
    };
    handleEnterKey(prev);
    
    let next = document.querySelector('.next');
    next.onclick = function () {
        replaceVideos(); 
        plusSlides(1)  
    };
    handleEnterKey(next);
  
    let dots = document.querySelectorAll('.dot');
    dots.forEach(function(dot){
        let slideNum = dot.dataset.slideNumber;
        dot.onclick = function () {
            replaceVideos(); 
            currentSlide(parseInt(slideNum));  
        };
        handleEnterKey(dot);
    });
}
/*End Video Gallery*/

/*Begin Image Gallery*/
function initImageGallery()
{
	let imgFlipper = document.getElementById('imgGallery');
	
	if(elemExists(imgFlipper)){
		setTimeout(fadeOut1, 5000);
		setInterval(beginFlip, 10000);
	}
}

function getImgIndex()
{
	let mainImgIndex = document.getElementById('imgIndex');
	let currentIndex = parseInt(mainImgIndex.value);

	let mainImgStr = document.getElementById('imgStr').value;
	let mainImgList = mainImgStr.split('|');

	currentIndex++;
	if (currentIndex >= mainImgList.length) {
		currentIndex = 0;
	}
	mainImgIndex.value = currentIndex;

	return currentIndex;
}

function getImgSrc()
{
    let index = getImgIndex();
    let gallery = document.getElementById('imgGallery');
    let galleryId = gallery.dataset.galleryId

	let imgStr = document.getElementById('imgStr').value;
	let imgList = imgStr.split('|');

	let dir = 'assets/img/galleries/gallery' + galleryId + '/';
	let src = dir + imgList[index];

	return src;
}

function changeImage1() 
{
	let src = getImgSrc();
	document.getElementById('image1').src = src;
} 

function changeImage2() 
{
	let src = getImgSrc();
	document.getElementById('image2').src = src;
}

function fadeOut1() 
{
	let image1 = document.getElementById('image1');
	image1.style.opacity = '0';
	setTimeout(changeImage1, 2000);
}  

function fadeIn1() 
{
	let image1 = document.getElementById('image1');
	image1.style.opacity = '1';
	setTimeout(changeImage2, 2000);
}  

function beginFlip() 
{
	fadeIn1();
	setTimeout(fadeOut1, 5000);
}
/*End Image Gallery*/

document.addEventListener('DOMContentLoaded', function() {
	//Get widget elements
    let videoGallery = document.querySelector('.video-gallery');
    let imgGallery = document.querySelector('#imgGallery');

    //Initialize
    if(elemExists(videoGallery)){
        initVideoGallery(); 
	    initClickLoadVideos();
    }
    if(elemExists(imgGallery)){
        initImageGallery();
    }
});


