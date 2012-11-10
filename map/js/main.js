/*$(document).ready(function() {
var map = L.map('map').setView([51.505, -0.09], 13);

L.tileLayer('http://{s}.tile.cloudmade.com/BC9A493B41014CAABB98F0471D759707/997/256/{z}/{x}/{y}.png', {
  maxZoom: 18,
  attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://cloudmade.com">CloudMade</a>'
}).addTo(map);


L.marker([51.5, -0.09]).addTo(map)
  .bindPopup("<b>Hello world!</b><br />I am a popup.").openPopup();

L.circle([51.508, -0.11], 500, {
  color: 'red',
  fillColor: '#f03',
  fillOpacity: 0.5
}).addTo(map).bindPopup("I am a circle.");

var popup = L.popup();

function onMapClick(e) {
  popup
    .setLatLng(e.latlng)
    .setContent("You clicked the map at " + e.latlng.toString())
    .openOn(map);
}

map.on('click', onMapClick);
});
*/
defaultZoom = 18

function loadMap() {
  map = new L.Map('map');
	var cloudmade = new L.TileLayer('http://{s}.tile.cloudmade.com/BC9A493B41014CAABB98F0471D759707/997/256/{z}/{x}/{y}.png', {
		attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://cloudmade.com">CloudMade</a>',
		maxZoom: 18
	});
	
	map.setView([51.505, -0.09], 13).addLayer(cloudmade);
	$("#search_launch").bind('click', function clickButt(){
		var address = $("#search_field input").val();
		geoCode(address, displayAddress);
	})
}

document.addEventListener('DOMContentLoaded', loadMap)

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

function displayAddress(data) {
	var markerLocation = new L.LatLng(data[0].lat, data[0].lon);
	setCenter(markerLocation);
	var marker = new L.Marker(markerLocation);
	map.addLayer(marker);
}

function setCenter(markerLocation) {
	map.setView(markerLocation, defaultZoom);
}	