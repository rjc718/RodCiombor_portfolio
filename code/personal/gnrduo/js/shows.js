function handleEvents()
{
    let rangeToggles = document.querySelectorAll('.date-range-toggle');

	rangeToggles.forEach(function(btn) {
		btn.addEventListener('click', function() {
            rangeToggles.forEach(function(btn) {
                btn.classList.remove('active');
            });
            
            this.classList.add('active');
            toggleAriaPressedForGroup(rangeToggles, btn);

            let range = this.dataset.range;
            if(range == 0){
                document.getElementById('upcomingShows').classList.remove('hide');
                document.getElementById('pastShows').classList.add('hide');
                document.getElementById('yearList').classList.add('hide');
            }
            else{
                document.getElementById('upcomingShows').classList.add('hide');
                document.getElementById('pastShows').classList.remove('hide');
                document.getElementById('yearList').classList.remove('hide');
            }
		});
        handleEnterKey(btn);
	});

    let yearToggles = document.querySelectorAll('.year-toggle');
    let yearRanges = document.querySelectorAll('.year-range');
    
    yearToggles.forEach(function(btn) {
		btn.addEventListener('click', function() {
            yearToggles.forEach(function(btn) {
                btn.classList.remove('active');
            });
            
            this.classList.add('active');
            toggleAriaPressedForGroup(yearToggles, btn);

            let btnYear = this.dataset.year;
        
            yearRanges.forEach(function(range) {
                let year = range.dataset.year;
                if(btnYear == year){
                    range.classList.remove('hide');
                }
                else{
                    range.classList.add('hide');
                }
            });
		});
        handleEnterKey(btn);
	});

    let readMoreBtns = document.querySelectorAll('.read-more-btn');
    let eventDescriptions = document.querySelectorAll('.event-description');

    if(readMoreBtns.length > 0){
        readMoreBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {

                this.classList.toggle('active');
                let ariaStatus = '';
                let statusMsg = this.querySelector('.status');
                let status = statusMsg.innerHTML;
    
                if(status == 'More'){
                    statusMsg.innerHTML = 'Less';
                    ariaStatus = 'Read Less About This Event';
                }
                else{
                    statusMsg.innerHTML = 'More';
                    ariaStatus = 'Read More About This Event';
                }
                btn.setAttribute('aria-label', ariaStatus);
                toggleAriaPressed(btn);
            
                let evtTarget = this.dataset.target;
                eventDescriptions.forEach(function(desc) {
                    let evtId = desc.dataset.evtId;
                    if(evtId == evtTarget){
                        desc.classList.toggle('hide');
                        toggleAriaExpand(desc);
                    }
                });
            });
            handleEnterKey(btn);
        });
    }
}
document.addEventListener('DOMContentLoaded', function() {	
	handleEvents();
});