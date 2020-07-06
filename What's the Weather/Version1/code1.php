<!DOCTYPE html>
<html>
	<title>MyWayPoints</title>
	<h3>MyWayPoints</h3>

<?php
		
		$homepage=0;
		$weather=0;

		// This checks if the user has given inputs or not and then copies it to the variables
		if(isset($_POST['start'])&&isset($_POST['end']))
		{
			$xs = $_POST['start'];
			$x = str_replace(' ', '', $xs);
			$ys = $_POST['end'];
			$y = str_replace(' ', '', $ys);
			echo "The route from $xs to $ys is displayed, along with Temperature Range and Climatic Conditions<br>";
		}

		

			$homepage = file_get_contents("https://maps.googleapis.com/maps/api/directions/json?origin=".$x."&destination=".$y."&key=AIzaSyDpEC1ZrM00LYhxgNHxJ8We4wStFMmgIGY");			//Request to Google Direction API & JSON Object --> PHP Object
		
		$obj = json_decode($homepage);								//Convert JSON obkect->Php Object
		$dist = $obj->routes[0]->legs[0]->distance->value;		// To adjust the zoom size on maps and to space out marker points
		

		$elementCount  = count($obj->routes[0]->legs[0]->steps); 	//COM COUNTS THE NUMBER OF STEPS SO THAT LOOP SE UTNE LATLONG MILE
		for ($i=0;$i<$elementCount;$i++){
			$lat=$obj->routes[0]->legs[0]->steps[$i]->start_location->lat;
			$lng=$obj->routes[0]->legs[0]->steps[$i]->start_location->lng;
			

			//COM OPEN WEATHER API CALL
			$weather = file_get_contents("http://api.openweathermap.org/data/2.5/weather?lat=".$lat."&lon=".$lng."&units=metric&appid=0e091a3706893d06d911e904721fd047");
			$wea = json_decode($weather);    
			$weaMat[$i][0]=$wea->name;
			$weaMat[$i][1]=$wea->main->temp_min;
			$weaMat[$i][2]=$wea->main->temp;
			$weaMat[$i][3]=$wea->main->temp_max;
			$weaMat[$i][4]=$lat;
			$weaMat[$i][5]=$lng;
			$weaMat[$i][6]=$wea->weather[0]->icon;
			$weaMat[$i][7]=$obj->routes[0]->legs[0]->steps[$i]->distance->value;
		}

?>
	

  <head>

    <style>
    	#map {
    	
        height: 460px; 											/* DIV MAP ELEMENT-The height is 400 pixels */
        width: 100%;  											/* The width is the width of the web page */
       	}
       	/*div{
       		 margin-top: 1000px;
       	}*/
    </style>
  </head>
  <body>
    <p>
	<form action="mywaypoints.php" method="POST">
			<input id="alignsubmitbutton" type="submit" value="Return">
	</form>
	</p>
    <div id="map" ></div>										<!--The div element for the map -->

	<script>

	var route_display = JSON.parse(<?php echo json_encode($homepage); ?>);
    var  weaMat= <?php echo json_encode($weaMat); ?>;			/*Import data from PHP to Javascript*/
    var len = "<?php echo $elementCount; ?>";      				/* To find the number of cities i.e rows of 2d matrix*/
   	var midlen= Math.floor(len/2);   							/*Map ka Center ke liye*/ 
   	var zoomsize = "<?php echo $dist; ?>";
   	var intzoomsize= Math.floor(zoomsize);
   	if (intzoomsize>1000000)
   		intzoomsize= 5;
   	else intzoomsize = 7;

	var directionsService = null;
  	var directionsDisplay = null;
	/*Map Function to Initialize and add the Map*/
	function initMap() {
		var spacing=zoomsize/10;								//to set the spacing distance
		console.log(spacing);
		/*var markerspacing= Math.floor(spacing);*/
		directionsDisplay = new google.maps.DirectionsRenderer();

		var map = new google.maps.Map(document.getElementById('map'), 
	  									{zoom: intzoomsize, center: {lat: weaMat[midlen][4], lng: weaMat[midlen][5]}}); 		
																				/*The map initialization with parameters*/
		/*directionsDisplay.setMap(map);*/
	    var i;
	    var limitmarker=0;
	    renderDirections(map, route_display, {  travelMode: 'DRIVING',  origin: 'START_LOCATION',  destination: 'END_LOCATION'} );
		for (i = 0; i < len; i++){
	  		var mark = {lat: weaMat[i][4], lng: weaMat[i][5]}; 	 		 		// The location of each marker's location
	  		var data1 = weaMat[i][1].toString();
	  		var data2 = weaMat[i][2].toString();
	  		data1 = data1.concat("<=>");											//Label only accept strings data, so convert int to string
	  		data1= data1.concat(data2);		

	  		var weaicon=weaMat[i][6];								//Icon leke aao upar se par pahile usko string banao
	  		var image = 'http://openweathermap.org/img/w/'; 		/*Make icons dynamic --- with concatenation*/	
	  		image= image.concat(weaicon);
	  		image= image.concat('.png');

	  		limitmarker += weaMat[i][7];							//we will use var limitmarker to distance out the markers 
	  		if (limitmarker>spacing || i==0 || i==len-1){
	  		var marker = new google.maps.Marker({position: mark, map: map, icon: {url: image,labelOrigin: {x:100, y:25} }, label: 
	  																	{text: data1,color: 'black',fontSize:'15px'}}); 
			console.log(i,limitmarker );
			limitmarker=0;
	  		} 		
																				// The marker, positioned at mark and displayed on map
					
		}
		
	}

	// ROUTE DISPLAY
	function typecastRoutes(routes){

    routes.forEach(function(route){
    	
        route.bounds = asBounds(route.bounds);
        // I don't think `overview_path` is used but it exists on the
        // response of DirectionsService.route()
        route.overview_path = asPath(route.overview_polyline);

        route.legs.forEach(function(leg){
            
            leg.start_location = asLatLng(leg.start_location);
            leg.end_location   = asLatLng(leg.end_location);


            leg.steps.forEach(function(step){
                step.start_location = asLatLng(step.start_location);
                step.end_location   = asLatLng(step.end_location);
                step.path = asPath(step.polyline);
            });

        });
    });
    return routes;
}

function asBounds(boundsObject){
    return new google.maps.LatLngBounds(asLatLng(boundsObject.southwest),
                                    asLatLng(boundsObject.northeast));
}

function asLatLng(latLngObject){
    return new google.maps.LatLng(latLngObject.lat, latLngObject.lng);
}

function asPath(encodedPolyObject){
    return google.maps.geometry.encoding.decodePath( encodedPolyObject.points );
}




function renderDirections(map, response, request){
    directionsDisplay.setOptions({
        directions : {
            routes : typecastRoutes(response.routes),
            // "ub" is important and not returned by web service it's an
            // object containing "origin", "destination" and "travelMode"
            request : request
        },
        draggable : true,
        map : map
    });
}

    </script>

    <!-- async attribute -----Callback Parameter ->initMap()  -->
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDpEC1ZrM00LYhxgNHxJ8We4wStFMmgIGY&libraries=geometry&callback=initMap">	
    </script>	
  </body>
</html>


   
  
