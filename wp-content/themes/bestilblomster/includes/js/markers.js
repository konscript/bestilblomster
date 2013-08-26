function createMarker(map,point,root,the_link,the_title,color,callout) {

	var baseIcon = root + "/images/icons/shadow.png";
	var blueIcon = root + "/images/icons/blue-dot.png";
	var redIcon = root + "/images/icons/red-dot.png"; 
	var greenIcon = root + "/images/icons/green-dot.png";   
	var yellowIcon = root + "/images/icons/yellow-dot.png";      		
	var tealIcon = root + "/images/icons/teal-dot.png"; 
	var blackIcon = root + "/images/icons/black-dot.png"; 
	var whiteIcon = root + "/images/icons/white-dot.png"; 
	var purpleIcon = root + "/images/icons/purple-dot.png"; 
	var pinkIcon = root + "/images/icons/pink-dot.png"; 
	var customIcon = color;
	
	var image = root + "/images/icons/red-dot.png";
	
	if(color == 'blue')			{ image = blueIcon } 
	else if(color == 'red')		{ image = redIcon } 
	else if(color == 'green')	{ image = greenIcon } 
	else if(color == 'yellow')	{ image = yellowIcon } 
	else if(color == 'teal')	{ image = tealIcon } 
	else if(color == 'black')	{ image = blackIcon }  
	else if(color == 'white')	{ image = whiteIcon } 
	else if(color == 'purple')	{ image = purpleIcon } 
	else if(color == 'pink')	{ image = pinkIcon } 
	else { image = customIcon } 
		
	var marker = new google.maps.Marker({
    	map:map,
   		draggable:false,
    	animation: google.maps.Animation.DROP,
    	position: point,
    	icon: image,
    	title: the_title
  	});
  	
  	var infowindow = new google.maps.InfoWindow({
        content: callout
    });
    
  	google.maps.event.addListener(marker, 'click', function() {
  		if ( callout == '' ) {
  			window.location = the_link;
  		} else {
  			infowindow.open(map,marker);
  		} 
  	});
  	
  	return marker;
	
}