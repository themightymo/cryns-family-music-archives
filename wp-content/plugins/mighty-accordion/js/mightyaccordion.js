jQuery(document).ready(function($) {
	
	// Choose the controller div - in this case it is the h2 tag
	$("#accordion").accordion({ header: 'h2', autoHeight: false });
	
	
	// Now add the divs that will be controlled by the h2 tag
	// For help, search google for jquery automatically closes tag
	
	//$('.entry-meta').before('<div class="accordion-content">');
	//$('.entry-content').after('</div><!-- .accordion-content -->');
	
	//$('<div class="accordion-content">').insertAfter('h2');
	
	//$(".entry-meta").nextUntil(".entry-utility").after("hello");
	
	

	//prepend span tag to H2
	//$("h2").prepend("<span></span>");
	
});