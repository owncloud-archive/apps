jQuery(function($) {
	$('#cropbox').Jcrop({
		onChange:	showCoords,
		onSelect:	showCoords,
		onRelease:	clearCoords,
		maxSize:	[399, 399],
		bgColor:	'black',
		bgOpacity:	.4,
		boxWidth: 	400,
		boxHeight:	400,
		setSelect:	[ 100, 130, 50, 50 ]//,
		//aspectRatio: 0.8
	});
});
// Simple event handler, called from onChange and onSelect
// event handlers, as per the Jcrop invocation above
function showCoords(c) {
	$('#x1').val(c.x);
	$('#y1').val(c.y);
	$('#x2').val(c.x2);
	$('#y2').val(c.y2);
	$('#w').val(c.w);
	$('#h').val(c.h);
};

function clearCoords() {
	$('#coords input').val('');
};
