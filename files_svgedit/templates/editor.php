<?php
// load required style sheets:
OC_Util::addStyle('files_svgedit', 'ocsvg');
OC_Util::addStyle('files_svgedit', 'jgraduate/css/jPicker-1.0.12');
OC_Util::addStyle('files_svgedit', 'jgraduate/css/jgraduate');
OC_Util::addStyle('files_svgedit', 'svg-editor');
OC_Util::addStyle('files_svgedit', 'spinbtn/JQuerySpinBtn');
// load required javascripts:
OC_Util::addScript('files_svgedit', 'ocsvgEditor');
OC_Util::addScript('files_svgedit', 'js-hotkeys/jquery.hotkeys.min');
OC_Util::addScript('files_svgedit', 'jgraduate/jquery.jgraduate.min');
OC_Util::addScript('files_svgedit', 'svgicons/jquery.svgicons.min');
OC_Util::addScript('files_svgedit', 'jquerybbq/jquery.bbq.min');
OC_Util::addScript('files_svgedit', 'spinbtn/JQuerySpinBtn.min');
OC_Util::addScript('files_svgedit', 'svgcanvas.min');
OC_Util::addScript('files_svgedit', 'svg-editor.min');
OC_Util::addScript('files_svgedit', 'locale/locale.min');
OC_Util::addScript('files_svgedit', 'jgraduate/jpicker-1.0.12.min');
//only for debugging:
//OC_Util::addScript('files_svgedit', 'svgcanvas');
//OC_Util::addScript('files_svgedit', 'svg-editor');
//OC_Util::addScript('files_svgedit', 'locale/locale');
?>

<script type="text/javascript">
<!--
var ocsvgFile = {
    path: <?php echo $_['filePath']; ?>,
    mtime: <?php echo $_['filemTime']; ?>,
    contents: <?php echo $_['fileContents']; ?>
};
//-->
</script>
<div id="editorWrapper">
<div id="editorContent">
<div id="svg_editor">

<div id="main_button">
    <div id="main_icon" class="buttonup" title="Main Menu">
        <span></span>
        <div id="logo"></div>
        <div class="dropdown"></div>
    </div>
        
    <div id="main_menu"> 
    
        <!-- File-like buttons: New, Save, Source -->
        <ul>
            <li id="tool_clear" title="">
                <div></div>
                <?php echo $l->t('New Image'); ?> [N]
            </li>
            
            <li id="tool_open" style="display:none;" title="">
                <div id="fileinputs">
                    <div></div>
                </div>
                <?php echo $l->t('Open Image'); ?> [O]
            </li>
            
            <li id="tool_import" style="display:none;" title="">
                <div id="fileinputs_import">
                    <div></div>
                </div>
                <?php echo $l->t('Import SVG'); ?>
            </li>
            
            <li id="tool_save" title="">
                <div></div>
                <?php echo $l->t('Save Image'); ?> [S]
            </li>
            
            <li id="tool_export" title="">
                <div></div>
                <?php echo $l->t('Export as PNG'); ?>
            </li>
            
            <li id="tool_docprops" title="">
                <div></div>
                <?php echo $l->t('Document Properties'); ?> [P]
            </li>
        </ul>
        
        <p>
            <a href="http://svg-edit.googlecode.com/" target="_blank">
                <?php echo $l->t('SVG-edit Home Page'); ?>
            </a>
        </p>

    </div>
</div>



<div id="tools_top" class="tools_panel">
    
    <div id="editor_panel">
        <div class="push_button" id="tool_source" title="<?php echo $l->t('Edit Source'); ?> [U]"></div>
        <div class="tool_button" id="tool_wireframe" title="<?php echo $l->t('Wireframe Mode'); ?> [F]"></div>
    </div>

    <!-- History buttons -->
    <div id="history_panel">
        <div class="tool_sep"></div>
        <div class="push_button tool_button_disabled" id="tool_undo" title="<?php echo $l->t('Undo'); ?> [Z]"></div>
        <div class="push_button tool_button_disabled" id="tool_redo" title="<?php echo $l->t('Redo'); ?> [Y]"></div>
    </div>
    
    <!-- Buttons when a single element is selected -->
    <div id="selected_panel">
        <div class="toolset">
            <div class="tool_sep"></div>
            <div class="push_button" id="tool_clone" title="<?php echo $l->t('Clone Element'); ?> [C]"></div>
            <div class="push_button" id="tool_delete" title="<?php echo $l->t('Delete Element'); ?> [Delete/Backspace]"></div>
            <div class="tool_sep"></div>
            <div class="push_button" id="tool_move_top" title="<?php echo $l->t('Move to Top'); ?> [Shift+Up]"></div>
            <div class="push_button" id="tool_move_bottom" title="<?php echo $l->t('Move to Bottom'); ?> [Shift+Down]"></div>
            <div class="push_button" id="tool_topath" title="<?php echo $l->t('Convert to Path'); ?>"></div>
            <div class="push_button" id="tool_reorient" title="<?php echo $l->t('Reorient path'); ?>"></div>
            <div class="tool_sep"></div>
            <label id="idLabel" title="<?php echo $l->t('Identify the element'); ?>">
                <span>id:</span>
                <input id="elem_id" class="attr_changer" data-attr="id" size="10" type="text"/>
            </label>
        </div>

        <label id="tool_angle" title="<?php echo $l->t('Change rotation angle'); ?>">
            <span id="angleLabel" class="icon_label"></span>
            <input id="angle" size="2" value="0" type="text"/>
        </label>
        
        <div class="toolset" id="tool_blur" title="<?php echo $l->t('Change gaussian blur value'); ?>">
            <label>
                <span id="blurLabel" class="icon_label"></span>
                <input id="blur" size="2" value="0" type="text"/>
            </label>
            <div id="blur_dropdown" class="dropdown">
                <button></button>
                <ul>
                    <li class="special"><div id="blur_slider"></div></li>
                </ul>
            </div>
        </div>
        
        <div class="dropdown toolset" id="tool_position" title="<?php echo $l->t('Align Element to Page'); ?>">
                <div id="cur_position" class="icon_label"></div>
                <button></button>
        </div>		

        <div id="xy_panel" class="toolset">
            <label>
                x: <input id="selected_x" class="attr_changer" title="<?php echo $l->t('Change X coordinate'); ?>" size="3" data-attr="x"/>
            </label>
            <label>
                y: <input id="selected_y" class="attr_changer" title="<?php echo $l->t('Change Y coordinate'); ?>" size="3" data-attr="y"/>
            </label>
        </div>
    </div>

    <!-- Buttons when multiple elements are selected -->
    <div id="multiselected_panel">
        <div class="tool_sep"></div>
        <div class="push_button" id="tool_clone_multi" title="<?php echo $l->t('Clone Elements'); ?> [C]"></div>
        <div class="push_button" id="tool_delete_multi" title="<?php echo $l->t('Delete Selected Elements'); ?> [Delete/Backspace]"></div>
        <div class="tool_sep"></div>
        <div class="push_button" id="tool_group" title="<?php echo $l->t('Group Elements'); ?> [G]"></div>
        <div class="push_button" id="tool_alignleft" title="<?php echo $l->t('Align Left'); ?>"></div>
        <div class="push_button" id="tool_aligncenter" title="<?php echo $l->t('Align Center'); ?>"></div>
        <div class="push_button" id="tool_alignright" title="<?php echo $l->t('Align Right'); ?>"></div>
        <div class="push_button" id="tool_aligntop" title="<?php echo $l->t('Align Top'); ?>"></div>
        <div class="push_button" id="tool_alignmiddle" title="<?php echo $l->t('Align Middle'); ?>"></div>
        <div class="push_button" id="tool_alignbottom" title="<?php echo $l->t('Align Bottom'); ?>"></div>
        <label id="tool_align_relative"> 
            <span id="relativeToLabel"><?php echo $l->t('relative to:'); ?></span>
            <select id="align_relative_to" title="<?php echo $l->t('Align relative to ...'); ?>">
            <option id="selected_objects" value="selected"><?php echo $l->t('selected objects'); ?></option>
            <option id="largest_object" value="largest"><?php echo $l->t('largest object'); ?></option>
            <option id="smallest_object" value="smallest"><?php echo $l->t('smallest object'); ?></option>
            <option id="page" value="page"><?php echo $l->t('page'); ?></option>
            </select>
        </label>
        <div class="tool_sep"></div>

    </div>

    <div id="g_panel">
        <div class="tool_sep"></div>
        <div class="push_button" id="tool_ungroup" title="<?php echo $l->t('Ungroup Elements'); ?> [G]"></div>
    </div>

    <div id="rect_panel">
        <div class="toolset">
            <label id="rect_width_tool" title="<?php echo $l->t('Change rectangle width'); ?>">
                <span id="rwidthLabel" class="icon_label"></span>
                <input id="rect_width" class="attr_changer" size="3" data-attr="width"/>
            </label>
            <label id="rect_height_tool" title="<?php echo $l->t('Change rectangle height'); ?>">
                <span id="rheightLabel" class="icon_label"></span>
                <input id="rect_height" class="attr_changer" size="3" data-attr="height"/>
            </label>
        </div>
        <label id="cornerRadiusLabel" title="<?php echo $l->t('Change Rectangle Corner Radius'); ?>">
            <span class="icon_label"></span>
            <input id="rect_rx" size="3" value="0" type="text" data-attr="Corner Radius"/>
        </label>
    </div>

    <div id="image_panel">
    <div class="toolset">
        <label><span id="iwidthLabel" class="icon_label"></span>
        <input id="image_width" class="attr_changer" title="<?php echo $l->t('Change image width'); ?>" size="3" data-attr="width"/>
        </label>
        <label><span id="iheightLabel" class="icon_label"></span>
        <input id="image_height" class="attr_changer" title="<?php echo $l->t('Change image height'); ?>" size="3" data-attr="height"/>
        </label>
    </div>
    <div class="toolset">
        <label id="tool_image_url">url:
            <input id="image_url" type="text" title="<?php echo $l->t('Change URL'); ?>" size="35"/>
        </label>
        <label id="tool_change_image">
            <button id="change_image_url" style="display:none;"><?php echo $l->t('Change Image'); ?></button>
            <span id="url_notice" title="<?php echo $l->t('NOTE: This image cannot be embedded. It will depend on this path to be displayed'); ?>"></span>
        </label>
    </div>
  </div>

    <div id="circle_panel">
        <div class="toolset">
            <label id="tool_circle_cx">cx:
            <input id="circle_cx" class="attr_changer" title="<?php echo $l->t("Change circle's cx coordinate"); ?>" size="3" data-attr="cx"/>
            </label>
            <label id="tool_circle_cy">cy:
            <input id="circle_cy" class="attr_changer" title="<?php echo $l->t("Change circle's cy coordinate"); ?>" size="3" data-attr="cy"/>
            </label>
        </div>
        <div class="toolset">
            <label id="tool_circle_r">r:
            <input id="circle_r" class="attr_changer" title="<?php echo $l->t("Change circle's radius"); ?>" size="3" data-attr="r"/>
            </label>
        </div>
    </div>

    <div id="ellipse_panel">
        <div class="toolset">
            <label id="tool_ellipse_cx">cx:
            <input id="ellipse_cx" class="attr_changer" title="<?php echo $l->t("Change ellipse's cx coordinate"); ?>" size="3" data-attr="cx"/>
            </label>
            <label id="tool_ellipse_cy">cy:
            <input id="ellipse_cy" class="attr_changer" title="<?php echo $l->t("Change ellipse's cy coordinate"); ?>" size="3" data-attr="cy"/>
            </label>
        </div>
        <div class="toolset">
            <label id="tool_ellipse_rx">rx:
            <input id="ellipse_rx" class="attr_changer" title="<?php echo $l->t("Change ellipse's x radius"); ?>" size="3" data-attr="rx"/>
            </label>
            <label id="tool_ellipse_ry">ry:
            <input id="ellipse_ry" class="attr_changer" title="<?php echo $l->t("Change ellipse's y radius"); ?>" size="3" data-attr="ry"/>
            </label>
        </div>
    </div>

    <div id="line_panel">
        <div class="toolset">
            <label id="tool_line_x1">x1:
            <input id="line_x1" class="attr_changer" title="<?php echo $l->t("Change line's starting x coordinate"); ?>" size="3" data-attr="x1"/>
            </label>
            <label id="tool_line_y1">y1:
            <input id="line_y1" class="attr_changer" title="<?php echo $l->t("Change line's starting y coordinate"); ?>" size="3" data-attr="y1"/>
            </label>
        </div>
        <div class="toolset">
            <label id="tool_line_x2">x2:
            <input id="line_x2" class="attr_changer" title="<?php echo $l->t("Change line's ending x coordinate"); ?>" size="3" data-attr="x2"/>
            </label>
            <label id="tool_line_y2">y2:
            <input id="line_y2" class="attr_changer" title="<?php echo $l->t("Change line's ending y coordinate"); ?>" size="3" data-attr="y2"/>
            </label>
        </div>
    </div>

    <div id="text_panel">
        <div class="toolset">
            <div class="tool_button" id="tool_bold" title="<?php echo $l->t('Bold Text'); ?> [B]"><span></span>B</div>
            <div class="tool_button" id="tool_italic" title="<?php echo $l->t('Italic Text'); ?> [I]"><span></span>i</div>
        </div>
        
        <div class="toolset" id="tool_font_family">
            <label>
                <!-- Font family -->
                <input id="font_family" type="text" title="<?php echo $l->t('Change Font Family'); ?>" size="12"/>
            </label>
            <div id="font_family_dropdown" class="dropdown">
                <button></button>
                <ul>
                    <li style="font-family:serif"><?php echo $l->t('Serif'); ?></li>
                    <li style="font-family:sans-serif"><?php echo $l->t('Sans-serif'); ?></li>
                    <li style="font-family:cursive"><?php echo $l->t('Cursive'); ?></li>
                    <li style="font-family:fantasy"><?php echo $l->t('Fantasy'); ?></li>
                    <li style="font-family:monospace"><?php echo $l->t('Monospace'); ?></li>
                </ul>
            </div>
        </div>

        <label id="tool_font_size" title="<?php echo $l->t('Change Font Size'); ?>">
            <span id="font_sizeLabel" class="icon_label"></span>
            <input id="font_size" size="3" value="0" type="text"/>
        </label>
        
        <!-- Not visible, but still used -->
        <input id="text" type="text" size="35"/>
    </div>
    
    <div id="path_node_panel">
        <div class="tool_sep"></div>
        <div class="tool_button" id="tool_node_link" title="<?php echo $l->t('Link Control Points'); ?>"></div>
        <div class="tool_sep"></div>
        <label id="tool_node_x">x:
            <input id="path_node_x" class="attr_changer" title="<?php echo $l->t("Change node's x coordinate"); ?>" size="3" data-attr="x"/>
        </label>
        <label id="tool_node_y">y:
            <input id="path_node_y" class="attr_changer" title="<?php echo $l->t("Change node's y coordinate"); ?>" size="3" data-attr="y"/>
        </label>
        
        <select id="seg_type" title="<?php echo $l->t('Change Segment type'); ?>">
            <option id="straight_segments" selected="selected" value="4">Straight</option>
            <option id="curve_segments" value="6">Curve</option>
        </select>
        <div class="tool_button" id="tool_node_clone" title="<?php echo $l->t('Clone Node'); ?>"></div>
        <div class="tool_button" id="tool_node_delete" title="<?php echo $l->t('Delete Node'); ?>"></div>
        <div class="tool_button" id="tool_openclose_path" title="<?php echo $l->t('Open/close sub-path'); ?>"></div>
        <div class="tool_button" id="tool_add_subpath" title="<?php echo $l->t('Add sub-path'); ?>"></div>
    </div>
    
</div> <!-- tools_top -->

<div id="workarea">
<style id="styleoverrides" type="text/css" media="screen" scoped="scoped"></style>
<div id="svgcanvas"></div>
</div>

<div id="sidepanels">
	<div id="layerpanel">
		<h3 id="layersLabel"><?php echo $l->t('Layers'); ?></h3>
		<fieldset id="layerbuttons">
			<div id="layer_new" class="layer_button"  title="<?php echo $l->t('New Layer'); ?>"></div>
			<div id="layer_delete" class="layer_button"  title="<?php echo $l->t('Delete Layer'); ?>"></div>
			<div id="layer_rename" class="layer_button"  title="<?php echo $l->t('Rename Layer'); ?>"></div>
			<div id="layer_up" class="layer_button"  title="<?php echo $l->t('Move Layer Up'); ?>"></div>
			<div id="layer_down" class="layer_button"  title="<?php echo $l->t('Move Layer Down'); ?>"></div>
		</fieldset>
		
		<table id="layerlist">
			<tr class="layer">
				<td class="layervis"></td>
				<td class="layername"><?php echo $l->t('Layer 1'); ?></td>
			</tr>
		</table>
		<span id="selLayerLabel"><?php echo $l->t('Move elements to:'); ?></span>
		<select id="selLayerNames" title="Move selected elements to a different layer" disabled="disabled">
			<option selected="selected" value="layer1"><?php echo $l->t('Layer 1'); ?></option>
		</select>
	</div>
	<div id="sidepanel_handle" title="<?php echo $l->t('Drag left/right to resize side panel'); ?> [X]"><?php echo $l->t('L a y e r s'); ?></div>
</div>



<div id="tools_left" class="tools_panel">
	<div class="tool_button" id="tool_select" title="<?php echo $l->t('Select Tool'); ?> [1]"></div>
	<div class="tool_button" id="tool_fhpath" title="<?php echo $l->t('Pencil Tool'); ?> [2]"></div>
	<div class="tool_button" id="tool_line" title="<?php echo $l->t('Line Tool'); ?> [3]"></div>
	<div class="tool_button flyout_current" id="tools_rect_show" title="<?php echo $l->t('Square/Rect Tool'); ?> [4/Shift+4]">
		<div class="flyout_arrow_horiz"></div>
	</div>
	<div class="tool_button flyout_current" id="tools_ellipse_show" title="<?php echo $l->t('Ellipse/Circle Tool'); ?> [5/Shift+5]">
		<div class="flyout_arrow_horiz"></div>
	</div>
	<div class="tool_button" id="tool_path" title="<?php echo $l->t('Path Tool'); ?> [7]"></div>
	<div class="tool_button" id="tool_text" title="<?php echo $l->t('Text Tool'); ?> [6]"></div>
	<div class="tool_button" id="tool_image" title="<?php echo $l->t('Image Tool'); ?> [8]"></div>
	<div class="tool_button" id="tool_zoom" title="<?php echo $l->t('Zoom Tool'); ?> [Ctrl+Up/Down]"></div>
	
	<div style="display: none">
		<div id="tool_rect" title="<?php echo $l->t('Rectangle'); ?>"></div>
		<div id="tool_square" title="<?php echo $l->t('Square'); ?>"></div>
		<div id="tool_fhrect" title="<?php echo $l->t('Free-Hand Rectangle'); ?>"></div>
		<div id="tool_ellipse" title="<?php echo $l->t('Ellipse'); ?>"></div>
		<div id="tool_circle" title="<?php echo $l->t('Circle'); ?>"></div>
		<div id="tool_fhellipse" title="<?php echo $l->t('Free-Hand Ellipse'); ?>"></div>
	</div>
</div> <!-- tools_left -->

<div id="tools_bottom" class="tools_panel">

    <!-- Zoom buttons -->
	<div id="zoom_panel" class="toolset" title="<?php echo $l->t('Change zoom level'); ?>">
		<label>
		<span id="zoomLabel" class="zoom_tool icon_label"></span>
		<input id="zoom" size="3" value="100" type="text" />
		</label>
		<div id="zoom_dropdown" class="dropdown">
			<button></button>
			<ul>
				<li>1000%</li>
				<li>400%</li>
				<li>200%</li>
				<li>100%</li>
				<li>50%</li>
				<li>25%</li>
				<li id="fit_to_canvas" data-val="canvas"><?php echo $l->t('Fit to canvas'); ?></li>
				<li id="fit_to_sel" data-val="selection"><?php echo $l->t('Fit to selection'); ?></li>
				<li id="fit_to_layer_content" data-val="layer"><?php echo $l->t('Fit to layer content'); ?></li>
				<li id="fit_to_all" data-val="content"><?php echo $l->t('Fit to all content'); ?></li>
				<li>100%</li>
			</ul>
		</div>
		<div class="tool_sep"></div>
	</div>

	<div id="tools_bottom_2">
		<div id="color_tools">
			<div class="color_tool" id="tool_fill">
				<label class="icon_label" for="fill_color" title="<?php echo $l->t('Change fill color'); ?>"></label>
				<div class="color_block">
					<div id="fill_bg"></div>
					<div id="fill_color" class="color_block"></div>
				</div>
			</div>
		
			<div class="color_tool" id="tool_stroke">
				<div class="color_block">
					<label class="icon_label" title="<?php echo $l->t('Change stroke color'); ?>"></label>
				</div>
				<div class="color_block">
					<div id="stroke_bg"></div>
					<div id="stroke_color" class="color_block" title="<?php echo $l->t('Change stroke color'); ?>"></div>
				</div>
				
				<label>
					<input id="stroke_width" title="<?php echo $l->t('Change stroke width by 1, shift-click to change by 0.1'); ?>" size="2" value="5" type="text" data-attr="Stroke Width"/>
				</label>
				
				<label class="stroke_tool">
					<select id="stroke_style" title="<?php echo $l->t('Change stroke dash style'); ?>">
						<option selected="selected" value="none">&mdash;</option>
						<option value="2,2">...</option>
						<option value="5,5">- -</option>
						<option value="5,2,2,2">- .</option>
						<option value="5,2,2,2,2,2">- ..</option>
					</select>
				</label>	

 				<div class="stroke_tool dropdown" id="stroke_linejoin">
 					<div>
						<div id="cur_linejoin" title="<?php echo $l->t('Linejoin: Miter'); ?>"></div>
						<button></button>
					</div>
 				</div>
 				
 				<div class="stroke_tool dropdown" id="stroke_linecap">
 					<div>
						<div id="cur_linecap" title="<?php echo $l->t('Linecap: Butt'); ?>"></div>
						<button></button>
					</div>
 				</div>
			
				<div id="toggle_stroke_tools" title="<?php echo $l->t('Show/hide more stroke tools'); ?>">
					&gt;&gt;
				</div>
				
			</div>
		</div>
	
		<div class="toolset" id="tool_opacity" title="<?php echo $l->t('Change selected item opacity'); ?>">
			<label>
				<span id="group_opacityLabel" class="icon_label"></span>
				<input id="group_opacity" size="3" value="100" type="text"/>
			</label>
			<div id="opacity_dropdown" class="dropdown">
				<button></button>
				<ul>
					<li>0%</li>
					<li>25%</li>
					<li>50%</li>
					<li>75%</li>
					<li>100%</li>
					<li class="special"><div id="opac_slider"></div></li>
				</ul>
			</div>
		</div>

	</div>

	<div id="tools_bottom_3">
		<div id="palette_holder"><div id="palette" title="<?php echo $l->t('Click to change fill color, shift-click to change stroke color'); ?>"></div></div>
	</div>
	<div id="copyright"><span id="copyrightLabel"><?php echo $l->t('Powered by'); ?></span> <a href="http://svg-edit.googlecode.com/" target="_blank">SVG-edit v2.5.1</a></div>
</div>

<div id="option_lists">
	<ul id="linejoin_opts">
		<li class="tool_button current" id="linejoin_miter" title="<?php echo $l->t('Linejoin: Miter'); ?>"></li>
		<li class="tool_button" id="linejoin_round" title="<?php echo $l->t('Linejoin: Round'); ?>"></li>
		<li class="tool_button" id="linejoin_bevel" title="<?php echo $l->t('Linejoin: Bevel'); ?>"></li>
	</ul>
	
	<ul id="linecap_opts">
		<li class="tool_button current" id="linecap_butt" title="<?php echo $l->t('Linecap: Butt'); ?>"></li>
		<li class="tool_button" id="linecap_square" title="<?php echo $l->t('Linecap: Square'); ?>"></li>
		<li class="tool_button" id="linecap_round" title="<?php echo $l->t('Linecap: Round'); ?>"></li>
	</ul>
	
	<ul id="position_opts" class="optcols3">
		<li class="push_button" id="tool_posleft" title="<?php echo $l->t('Align Left'); ?>"></li>
		<li class="push_button" id="tool_poscenter" title="<?php echo $l->t('Align Center'); ?>"></li>
		<li class="push_button" id="tool_posright" title="<?php echo $l->t('Align Right'); ?>"></li>
		<li class="push_button" id="tool_postop" title="<?php echo $l->t('Align Top'); ?>"></li>
		<li class="push_button" id="tool_posmiddle" title="<?php echo $l->t('Align Middle'); ?>"></li>
		<li class="push_button" id="tool_posbottom" title="<?php echo $l->t('Align Bottom'); ?>"></li>
	</ul>
</div>
</div> <!-- closing #editorContent -->

<!-- hidden divs -->
<div id="color_picker"></div>

</div> <!-- svg_editor -->

<div id="svg_source_editor">
	<div id="svg_source_overlay"></div>
	<div id="svg_source_container">
		<div id="tool_source_back" class="toolbar_button">
			<button id="tool_source_save"><?php echo $l->t('Apply Changes'); ?></button>
			<button id="tool_source_cancel"><?php echo $l->t('Cancel'); ?></button>
		</div>
		<form>
			<textarea id="svg_source_textarea" spellcheck="false"></textarea>
		</form>
	</div>
</div>

<div id="svg_docprops">
	<div id="svg_docprops_overlay"></div>
	<div id="svg_docprops_container">
		<div id="tool_docprops_back" class="toolbar_button">
			<button id="tool_docprops_save"><?php echo $l->t('OK'); ?></button>
			<button id="tool_docprops_cancel"><?php echo $l->t('Cancel'); ?></button>
		</div>


		<fieldset id="svg_docprops_docprops">
			<legend id="svginfo_image_props"><?php echo $l->t('Image Properties'); ?></legend>
			<label>
				<span id="svginfo_title"><?php echo $l->t('Title:'); ?></span>
				<input type="text" id="canvas_title" size="24"/>
			</label>			
	
			<fieldset id="change_resolution">
				<legend id="svginfo_dim"><?php echo $l->t('Canvas Dimensions'); ?></legend>

				<label><span id="svginfo_width"><?php echo $l->t('width:'); ?></span> <input type="text" id="canvas_width" size="6"/></label>
					
				<label><span id="svginfo_height"><?php echo $l->t('height:'); ?></span> <input type="text" id="canvas_height" size="6"/></label>
				
				<label>
					<select id="resolution">
						<option id="selectedPredefined" selected="selected"><?php echo $l->t('Select predefined:'); ?></option>
						<option>640x480</option>
						<option>800x600</option>
						<option>1024x768</option>
						<option>1280x960</option>
						<option>1600x1200</option>
						<option id="fitToContent" value="content"><?php echo $l->t('Fit to Content'); ?></option>
					</select>
				</label>
			</fieldset>

			<fieldset id="image_save_opts">
				<legend id="includedImages"><?php echo $l->t('Included Images'); ?></legend>
				<label><input type="radio" name="image_opt" value="embed" checked="checked"/> <span id="image_opt_embed"><?php echo $l->t('Embed data (local files)'); ?></span> </label>
				<label><input type="radio" name="image_opt" value="ref"/> <span id="image_opt_ref"><?php echo $l->t('Use file reference'); ?></span> </label>
			</fieldset>			


		</fieldset>

		<fieldset id="svg_docprops_prefs">
			<legend id="svginfo_editor_prefs"><?php echo $l->t('Editor Preferences'); ?></legend>

			<label><span id="svginfo_lang"><?php echo $l->t('Language:'); ?></span>
				<!-- Source: http://en.wikipedia.org/wiki/Language_names -->
				<select id="lang_select">
				  <option id="lang_ar" value="ar">العربية</option>
					<option id="lang_cs" value="cs">Čeština</option>
					<option id="lang_de" value="de">Deutsch</option>
					<option id="lang_en" value="en" selected="selected">English</option>
					<option id="lang_es" value="es">Español</option>
					<option id="lang_fa" value="fa">فارسی</option>
					<option id="lang_fr" value="fr">Français</option>
					<option id="lang_fy" value="fy">Frysk</option>
					<option id="lang_hi" value="hi">&#2361;&#2367;&#2344;&#2381;&#2342;&#2368;, &#2361;&#2367;&#2306;&#2342;&#2368;</option>
					<option id="lang_ja" value="ja">日本語</option>
					<option id="lang_nl" value="nl">Nederlands</option>
					<option id="lang_pt-BR" value="pt-BR">Português (BR)</option>
					<option id="lang_ro" value="ro">Româneşte</option>
					<option id="lang_ru" value="ru">Русский</option>
					<option id="lang_sk" value="sk">Slovenčina</option>
					<option id="lang_zh-TW" value="zh-TW">繁體中文</option>
				</select>
			</label>

			<label><span id="svginfo_icons"><?php echo $l->t('Icon size:'); ?></span>
				<select id="iconsize">
					<option id="icon_small" value="s"><?php echo $l->t('Small'); ?></option>
					<option id="icon_medium" value="m" selected="selected"><?php echo $l->t('Medium'); ?></option>
					<option id="icon_large" value="l"><?php echo $l->t('Large'); ?></option>
					<option id="icon_xlarge" value="xl"><?php echo $l->t('Extra Large'); ?></option>
				</select>
			</label>

			<fieldset id="change_background">
				<legend id="svginfo_change_background"><?php echo $l->t('Editor Background'); ?></legend>
				<div id="bg_blocks"></div>
				<label><span id="svginfo_bg_url"><?php echo $l->t('URL:'); ?></span> <input type="text" id="canvas_bg_url" size="21"/></label>
				<p id="svginfo_bg_note"><?php echo $l->t('Note: Background will not be saved with image.'); ?></p>
			</fieldset>
			
		</fieldset>

	</div>
</div>

<div id="dialog_box">
	<div id="dialog_box_overlay"></div>
	<div id="dialog_container">
		<div id="dialog_content"></div>
		<div id="dialog_buttons"></div>
	</div>
</div>

</div> <!-- closing #editorWrapper -->
