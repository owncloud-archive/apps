/*$(document).ready(function() {
var map = L.map('map').setView([51.505, -0.09], 13);

L.tileLayer('http://{s}.tile.cloudmade.com/BC9A493B41014CAABB98F0471D759707/997/256/{z}/{x}/{y}.png', {
  maxZoom: 18,
  attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://cloudmade.com">CloudMade</a>'
}).addTo(map);

L.circle([51.508, -0.11], 500, {
  color: 'red',
  fillColor: '#f03',
  fillOpacity: 0.5
}).addTo(map).bindPopup("I am a circle.");

var popup = L.popup();



map.on('click', onMapClick);
});
*/
defaultZoom = 18


function geoCode(address, callback) {
	var url = 'http://open.mapquestapi.com/nominatim/v1/search';
	$.ajax({
		type: 'GET',
		dataType: 'jsonp',
		url: url,
		success: callback,
		jsonp: 'json_callback',
		data: {
			format:'json',
			q: address,
		}
	})
}

function displaySearchAddress(data) {
	console.log(data[0]);
	var markerLocation = new L.LatLng(data[0].lat, data[0].lon);
	setCenter(markerLocation);
	var marker = new L.Marker(markerLocation);
	map.addLayer(marker);
	marker.bindPopup("<b>Hello world!</b><br />I am a popup.");//.openPopup();
}

function setCenter(markerLocation) {
	map.setView(markerLocation, defaultZoom);
}

function onMapClick(e) {
  popup
    .setLatLng(e.latlng)
    .setContent("You clicked the map at " + e.latlng.toString())
    .openOn(map);
}

function setUserLocation(position){
	console.log("yyy");
}

function loadMap() {
  map = new L.Map('map');
	var cloudmade = new L.TileLayer('http://{s}.tile.cloudmade.com/BC9A493B41014CAABB98F0471D759707/997/256/{z}/{x}/{y}.png', {
		attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://cloudmade.com">CloudMade</a>',
		maxZoom: 18
	});

	map.setView([51.505, -0.09], 13).addLayer(cloudmade);
	$("#search_launch").bind('click', function clickButt(){
		var address = $("#search_field input").val();
		geoCode(address, displaySearchAddress);
	})
	map.on('click', onMapClick);
}

document.addEventListener('DOMContentLoaded', loadMap)

$(document).ready(function() {
	// Try to fetch html5 location
	if(navigator.geolocation){
		navigator.geolocation.getCurrentPosition(setUserLocation);
	}
});
var popup = L.popup();
