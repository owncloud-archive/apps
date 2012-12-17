function av_mode_show_options(str){
	if ( str == 'daemon'){
		$('p.av_host').show('slow');
		$('p.av_port').show('slow');
		$('p.av_chunk_size').show('slow');
		$('p.av_path').hide('slow');
	} else if (str == 'executable'){
		$('p.av_host').hide('slow');
		$('p.av_port').hide('slow');
		$('p.av_chunk_size').hide('slow');
		$('p.av_path').show('slow');
	}
}
$(document).ready(function() {
	$("#av_mode").change(function () {
		var str = $("#av_mode").val();
		av_mode_show_options(str);
	});   
	$("#av_mode").change();
});
