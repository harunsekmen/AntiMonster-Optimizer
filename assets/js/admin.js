document.addEventListener('DOMContentLoaded', function() {
	var tabs = document.querySelectorAll('.antimonster-subnav a');
	var sections = document.querySelectorAll('.antimonster-section-content');
	tabs.forEach(function(tab) {
		tab.addEventListener('click', function(e) {
			e.preventDefault();
			var target = this.getAttribute('href');
			tabs.forEach(function(t) { t.classList.remove('active'); });
			sections.forEach(function(s) { s.classList.remove('active'); });
			this.classList.add('active');
			document.querySelector(target).classList.add('active');
		});
	});
});
