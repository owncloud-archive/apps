var defaultZoom = 18
var points = {};

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
	});
}

function displaySearchAddress(data) {
	console.debug(data[0]);
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
	html = tmpl("new_point",e.latlng );
	point = e.latlng;
  popup
    .setLatLng(e.latlng)
    .setContent(html)
    .openOn(map);
	$('#new_pnt').submit(addPoint);
}

function setUserLocation(position){
	console.log("yyy");
}

function addPoint(e) {
	e.preventDefault();
	$.ajax({
		type: 'POST',
		url: OC.filePath('map', 'ajax', 'item.php'),
		data: {
			action: 'add',
			lat: point.lat,
			lon: point.lng,
			name: $('input[name="pt_name"]').val(),
			type: $('select[name="pt_type"]').val()
		},
		success: function(msg) {
			if (msg.status == 'success') {
				//console.log('Add'+ point.toString());
				map.closePopup();
				addItemToMap(msg.data);
			}
		}

	});
}
function getDistanceReadable(point){
	pt2 = map.getCenter();
	distance = pt2.distanceTo(point);
	return meterToSize(distance);
}

function meterToSize(m) {
	if(m <= 50) return m.toFixed(2) + 'm';
	if(m <1000)
		return m.toFixed(0) + 'm';
	if(m < (100*1000) )
		return 	(m/1000).toFixed(1) + 'km';
	return (m/1000).toFixed(0) + 'km';

}

function onSidebarPointClick(e){
	e.preventDefault();
	item = points[$(this).data('id')];
	map.setView([item['lat'],item['lon']], defaultZoom);
}

function loadItems(){
	$.ajax({
		url: OC.filePath('map', 'ajax', 'item.php'),
		data: {	action: 'load' },
		success: function(msg) {
			if (msg.status == 'success') {
					for ( var i=0, len=msg.data.length; i<len; ++i ) {
						points[msg.data[i].id] = msg.data[i];
						addItemToMap(msg.data[i]);
					}
			}
		}
	});
}

function addItemToMap(item) {
	li_item = $('<li>')
		.data('id',item.id)
		.text(item.name)
		.append('<span> ~ '+getDistanceReadable([item.lat,item.lon])+'</span>')
		.click(onSidebarPointClick);
	$('#pts_myplaces').append(li_item);
	
	L.marker([item.lat, item.lon]).addTo(map)
		.bindPopup('This is '+ item.type + " named "+ item.name);
}

function updateDistance() {
	$('#pts_myplaces li').each(function () {
		var pt = points[$(this).data('id')];
		$(this).find('span').text('~ ' + getDistanceReadable([pt.lat, pt.lon]));
	});
}

function putMapPosition() {
	center = map.getCenter();
	document.cookie='lat='+center.lat;
	document.cookie='lon='+center.lng;
	document.cookie='z='+map.getZoom();
}

function readLastPosition() {
	position = {lat: read_cookie('lat'), lon: read_cookie('lon'), zoom: read_cookie('z')};
	if(position.lat && position.lon && position.zoom)
		return position;
	return false;
}

function read_cookie(key)
{
	var result;
	return (result = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)').exec(document.cookie)) ? (result[1]) : null;
}

function onSearch(e) {
	e.preventDefault();
	search_el = $("#search_field input");
	var address = search_el.val();
	//search_el.val('');
	if(address != '')
		geoCode(address, displaySearchAddress);
}

function loadMap() {
  map = new L.Map('map');
	var cloudmade = new L.TileLayer('http://{s}.tile.cloudmade.com/BC9A493B41014CAABB98F0471D759707/997/256/{z}/{x}/{y}.png', {
		attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://cloudmade.com">CloudMade</a>',
		maxZoom: 18
	});

	var last_position= readLastPosition();
	if(last_position) {
		map.setView([last_position.lat, last_position.lon], last_position.zoom)
	} else {
		console.log(last_position);
		map.setView([51.505, -0.09], 13)
	}

	map.addLayer(cloudmade);
	$("#search_launch").bind('click',onSearch);
	$("#search_field form").bind('submit',onSearch);
	map.on('click', onMapClick);
	map.on('move', updateDistance);
	loadItems();
}

document.addEventListener('DOMContentLoaded', loadMap)

$(document).ready(function() {
	// Try to fetch html5 location
	if(navigator.geolocation){
		navigator.geolocation.getCurrentPosition(setUserLocation);
	}
	window.onbeforeunload = putMapPosition;
});
var popup = L.popup();
var point;