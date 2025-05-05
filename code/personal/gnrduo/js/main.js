function sayHi(){
	console.log('Hello');
}
function elemExists(targ)
{
	let exists = false;
	if(typeof(targ) != 'undefined' && targ != null){
		exists = true;
	}
	return exists;
}
function addEvent(el, type, handler) 
{
    if(!el) return;
    if (el.attachEvent) el.attachEvent('on'+type, handler); 
	else el.addEventListener(type, handler);
}
function removeEvent(el, type, handler) 
{
    if(!el) return;
    if (el.detachEvent) el.detachEvent('on'+type, handler); 
	else el.removeEventListener(type, handler);
}
function flipNavIcon(x) 
{
	x.classList.toggle("change");
}
function toggleAriaExpand(targ)
{
	let aria = targ.getAttribute('aria-expanded');
	if (aria === 'true') {
		targ.setAttribute('aria-expanded', 'false');
	} else {
		targ.setAttribute('aria-expanded', 'true');
	}
}
function toggleAriaExpandForGroup(elements, targ)
{
	elements.forEach(function(elem) {
		elem.setAttribute('aria-expanded', 'false');
	});
	toggleAriaExpand(targ);
}
function toggleAriaPressed(targ)
{
	let aria = targ.getAttribute('aria-pressed');
	if (aria === 'true') {
		targ.setAttribute('aria-pressed', 'false');
	} else {
		targ.setAttribute('aria-pressed', 'true');
	}
}
function toggleAriaPressedForGroup(elements, targ)
{
	elements.forEach(function(elem) {
		elem.setAttribute('aria-pressed', 'false');
	});
	toggleAriaPressed(targ);
}
function handleEnterKey(targ)
{
    targ.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            targ.click(); 
        }
    });
}
function handleNavEvents()
{
	let dropbtns = document.querySelectorAll('.dropbtn');
	dropbtns.forEach(function(dropbtn) {
		dropbtn.addEventListener('keypress', function() {
			let isOpen = this.nextElementSibling.classList.contains('open');
			let dropdownContent = this.nextElementSibling;
		
			if (isOpen) {
				dropdownContent.classList.remove('open');
				this.setAttribute('aria-expanded', 'false');
			} else {
				let allDropdowns = document.querySelectorAll('.dropdown-content');
				allDropdowns.forEach(function(dropdown) {
					dropdown.classList.remove('open');
				});
		
				this.setAttribute('aria-expanded', 'false');
				dropdownContent.classList.add('open');
				this.setAttribute('aria-expanded', 'true');
			}
		});
		dropbtn.addEventListener('mouseout', function() {
			toggleAriaExpand(this);
		});
		dropbtn.addEventListener('mouseover', function() {
			toggleAriaExpand(this);
		});
	});
}
function handleMobileNavEvents()
{
	let menuButton = document.getElementById('menuButton');
	let mobileMenu = document.getElementById('mobileMenu');
	let mobHeader = document.getElementById('mob-header');

	mobHeader.addEventListener('click', function() {
		mobileMenu.classList.toggle('open');
		flipNavIcon(menuButton);
		toggleAriaPressed(menuButton);
		toggleAriaExpand(mobileMenu);
	});
	let subMenuBtns = document.querySelectorAll('.sub-menu-btn');

	subMenuBtns.forEach(function(subMenuBtn) {
		subMenuBtn.addEventListener('click', function() {
			let subMenuContent = this.nextElementSibling;
			subMenuContent.classList.toggle('open');
			toggleAriaExpand(subMenuContent);

			let plusIcon = this.querySelector('.plus-icon');
			plusIcon.classList.toggle('change');
			toggleAriaPressed(this);
		});
		subMenuBtn.addEventListener('keypress', function() {
			this.click();
		});
	});
}
function handleFooterNavEvents()
{
	let footerSection = document.getElementById('footer');
	let footerMenus = footerSection.querySelectorAll('.section_title.mobile.hasMenus');
	footerMenus.forEach(function(footerMenu) {
		footerMenu.addEventListener('click', function() {
			let parent = footerMenu.closest('.link_section')
			let linkList = parent.querySelector('.link_list');
			linkList.classList.toggle('open');

			let plusIcon = footerMenu.querySelector('.plus_icon');
			plusIcon.classList.toggle('change');
			toggleAriaPressed(footerMenu);
		});
		footerMenu.addEventListener('keypress', function() {
			this.click();
		});
	}); 
}

document.addEventListener('DOMContentLoaded', function() {	
	handleNavEvents();
	handleMobileNavEvents();
	handleFooterNavEvents();
});
