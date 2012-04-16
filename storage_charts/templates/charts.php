<?php

/**
* ownCloud - DjazzLab Storage Charts plugin
*
* @author Xavier Beurois
* @copyright 2012 Xavier Beurois www.djazz-lab.net
* 
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either 
* version 3 of the License, or any later version.
* 
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*  
* You should have received a copy of the GNU Lesser General Public 
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
* 
* JS minified by http://fmarcia.info/jsmin/test.html
* 
*/

OC_Util::addStyle('storage_charts', 'styles');
OC_Util::addScript('storage_charts', 'highCharts-2.2.1/highcharts');
OC_Util::addScript('storage_charts', 'highCharts-2.2.1/modules/exporting');

?>

<script type="text/javascript">
$(function(){
    var pie;var histo;
    $(document).ready(function(){
        pie=new Highcharts.Chart({chart:{renderTo:'pie-rfsus',plotBackgroundColor:null,plotBorderWidth:true,plotShadow:false},title:{text:'Current ratio free space / used space for "<?php print(OC_Group::inGroup(OC_User::getUser(), 'admin')?'all users':OC_User::getUser()); ?>"'},tooltip:{formatter:function(){return'<b>'+this.point.name+'</b>: '+this.percentage+' %';}},plotOptions:{pie:{allowPointSelect:true,cursor:'pointer',dataLabels:{enabled:true,color:'#000000',connectorColor:'#000000',formatter:function(){return'<b>'+this.point.name+'</b>: '+Math.round(this.percentage)+' %';}}}},series:[{type:'pie',name:'Used-Free space ratio',data:[<?php print(OC_DLStCharts::arrayParser('pie',$_['pie_rfsus']));?>]}]});
        histo=new Highcharts.Chart({chart:{renderTo:'histo',type:'line'},title:{text:'Daily Used Space Evolution',x:-20},subtitle:{text:'Last 7 days',x:-20},xAxis:{categories:['<?php print(date('m/d', mktime(0,0,0,date('m'),date('d')-6))); ?>','<?php print(date('m/d', mktime(0,0,0,date('m'),date('d')-5))); ?>','<?php print(date('m/d', mktime(0,0,0,date('m'),date('d')-4))); ?>','<?php print(date('m/d', mktime(0,0,0,date('m'),date('d')-3))); ?>','<?php print(date('m/d', mktime(0,0,0,date('m'),date('d')-2))); ?>','<?php print(date('m/d', mktime(0,0,0,date('m'),date('d')-1))); ?>','<?php print(date('m/d', mktime(0,0,0,date('m'),date('d')))); ?>']},yAxis:{title:{text:'Used space (MB)'},plotLines:[{value:0,width:1,color:'#808080'}]},tooltip:{formatter:function(){return'<b>'+this.series.name+'</b><br/>'+this.x+': '+this.y+'MB';}},legend:{layout:'horizontal',align:'center',verticalAlign:'top',x:-25,y:40,borderWidth:0},series:[<?php print(OC_DLStCharts::arrayParser('line',$_['lines_usse']));?>]});
    });
});
</script>

<div id="storage-charts">
	<div class="personalblock topblock titleblock">
		DjazzLab Storage Charts
	</div>
	<div class="personalblock">
		<div id="pie-rfsus" style="max-width:100%;height:400px;margin:0 auto"></div>
	</div>
	<div class="personalblock bottomblock">
		<div id="histo" style="max-width:100%;height:400px;margin:0 auto"></div>
	</div>
</div>
