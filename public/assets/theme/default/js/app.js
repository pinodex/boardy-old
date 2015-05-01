document.getElementById('mainNav').addEventListener('click', function() {
	this.classList.toggle('active');
}, false);

/*
if (textarea = document.querySelector(".post-view.create textarea, .reply-view.create textarea")) {	
	textarea.addEventListener('keyup', function() {
		this.style.overflow = 'hidden';
		this.style.height = 0;
		this.style.height = this.scrollHeight + 'px';
	}, false);
}
*/