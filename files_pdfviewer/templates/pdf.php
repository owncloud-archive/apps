<!DOCTYPE html>
<html dir="ltr" mozdisallowselectionprint>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<link rel="stylesheet" href="<?php print_unescaped(OC_Helper::linkTo('files_pdfviewer', '3rdparty/pdfjs/viewer.css')); ?>"/>
	
	<!-- oC changes-->
	<script type="text/javascript" src="<?php print_unescaped(OC_Helper::linkTo('core', 'js/jquery-1.7.2.min.js')); ?>"></script>
	<script type="text/javascript" src="<?php print_unescaped(OC_Helper::linkToRoute('js_config'));?>"></script>
	<script type="text/javascript" src="<?php print_unescaped(OC_Helper::linkTo('core', 'js/oc-dialogs.js')); ?>"></script>
	<script type="text/javascript" src="<?php print_unescaped(OC_Helper::linkTo('core', 'js/js.js')); ?>"></script>
	<!-- oC changes -->

	<script type="text/javascript" src="<?php print_unescaped(OC_Helper::linkTo('files_pdfviewer', '3rdparty/pdfjs/compatibility.js')); ?>"></script>

	<link rel="resource" type="application/l10n" href="<?php print_unescaped(OC_Helper::linkTo('files_pdfviewer', '3rdparty/pdfjs/locale/locale.properties')); ?>"/>
	<script type="text/javascript" src="<?php print_unescaped(OC_Helper::linkTo('files_pdfviewer', '3rdparty/pdfjs/l10n.js')); ?>"></script>
	<script type="text/javascript" src="<?php print_unescaped(OC_Helper::linkTo('files_pdfviewer', '3rdparty/pdfjs/pdf.js')); ?>"></script>

    	<script type="text/javascript" src="<?php print_unescaped(OC_Helper::linkTo('files_pdfviewer', 'js/files.php')); ?>?file=<?php print_unescaped(urlencode($_['file'])); ?>&amp;dir=<?php print_unescaped(urlencode($_['dir'])); ?>"></script>
	<script type="text/javascript" src="<?php print_unescaped(OC_Helper::linkTo('files_pdfviewer', '3rdparty/pdfjs/viewer.js')); ?>"></script>
</head>

<body>
	<div id="outerContainer">

		<div id="sidebarContainer">
			<div id="toolbarSidebar">
				<div class="splitToolbarButton toggled">
					<button id="viewThumbnail" class="toolbarButton group toggled" title="Show Thumbnails" tabindex="1" data-l10n-id="thumbs">
						<span data-l10n-id="thumbs_label">Thumbnails</span>
					</button>
					<button id="viewOutline" class="toolbarButton group" title="Show Document Outline" tabindex="2" data-l10n-id="outline">
						<span data-l10n-id="outline_label">Document Outline</span>
					</button>
				</div>
			</div>
			<div id="sidebarContent">
				<div id="thumbnailView">
				</div>
				<div id="outlineView" class="hidden">
				</div>
			</div>
		</div>  <!-- sidebarContainer -->

		<div id="mainContainer">
			<div class="findbar hidden doorHanger" id="findbar">
				<label for="findInput" class="toolbarLabel" data-l10n-id="find_label">Find:</label>
				<input id="findInput" class="toolbarField" tabindex="20">
				<div class="splitToolbarButton">
					<button class="toolbarButton findPrevious" title="" id="findPrevious" tabindex="21" data-l10n-id="find_previous">
						<span data-l10n-id="find_previous_label">Previous</span>
					</button>
					<div class="splitToolbarButtonSeparator"></div>
					<button class="toolbarButton findNext" title="" id="findNext" tabindex="22" data-l10n-id="find_next">
						<span data-l10n-id="find_next_label">Next</span>
					</button>
				</div>
				<input type="checkbox" id="findHighlightAll" class="toolbarField">
				<label for="findHighlightAll" class="toolbarLabel" tabindex="23" data-l10n-id="find_highlight">Highlight all</label>
				<input type="checkbox" id="findMatchCase" class="toolbarField">
				<label for="findMatchCase" class="toolbarLabel" tabindex="24" data-l10n-id="find_match_case_label">Match case</label>
				<span id="findMsg" class="toolbarLabel"></span>
			</div>
			<div class="toolbar">
				<div id="toolbarContainer">
					<div id="toolbarViewer">
						<div id="toolbarViewerLeft">
							<button id="sidebarToggle" class="toolbarButton" title="Toggle Sidebar" tabindex="3" data-l10n-id="toggle_sidebar">
								<span data-l10n-id="toggle_sidebar_label">Toggle Sidebar</span>
							</button>
							<div class="toolbarButtonSpacer"></div>
							<button id="viewFind" class="toolbarButton group" title="Find in Document" tabindex="4" data-l10n-id="findbar">
								<span data-l10n-id="findbar_label">Find</span>
							</button>
							<div class="splitToolbarButton">
								<button class="toolbarButton pageUp" title="Previous Page" id="previous" tabindex="5" data-l10n-id="previous">
									<span data-l10n-id="previous_label">Previous</span>
								</button>
								<div class="splitToolbarButtonSeparator"></div>
								<button class="toolbarButton pageDown" title="Next Page" id="next" tabindex="6" data-l10n-id="next">
									<span data-l10n-id="next_label">Next</span>
								</button>
							</div>
							<label id="pageNumberLabel" class="toolbarLabel" for="pageNumber" data-l10n-id="page_label">Page: </label>
							<input type="number" id="pageNumber" class="toolbarField pageNumber" value="1" size="4" min="1" tabindex="7">
						</input>
						<span id="numPages" class="toolbarLabel"></span>
					</div>
					<div id="toolbarViewerRight">
						<input id="fileInput" class="fileInput" type="file" oncontextmenu="return false;" style="visibility: hidden; position: fixed; right: 0; top: 0" />


						<button id="fullscreen" class="toolbarButton fullscreen" title="Switch to Presentation Mode" tabindex="11" data-l10n-id="presentation_mode">
							<span data-l10n-id="presentation_mode_label">Presentation Mode</span>
						</button>

						<button id="openFile" style="display:none;" class="toolbarButton openFile" title="Open File" tabindex="12" data-l10n-id="open_file">
							<span data-l10n-id="open_file_label">Open</span>
						</button>

						<button id="print" class="toolbarButton print" title="Print" tabindex="13" data-l10n-id="print">
							<span data-l10n-id="print_label">Print</span>
						</button>

						<button id="download" class="toolbarButton download" title="Download" tabindex="14" data-l10n-id="download">
							<span data-l10n-id="download_label">Download</span>
						</button>
						<!-- <div class="toolbarButtonSpacer"></div> -->
						<a href="#" id="viewBookmark" style="display:none;" class="toolbarButton bookmark" title="Current view (copy or open in new window)" tabindex="15" data-l10n-id="bookmark"><span data-l10n-id="bookmark_label">Current View</span></a>
					</div>
					<div class="outerCenter">
						<div class="innerCenter" id="toolbarViewerMiddle">
							<div class="splitToolbarButton">
								<button class="toolbarButton zoomOut" title="Zoom Out" tabindex="8" data-l10n-id="zoom_out">
									<span data-l10n-id="zoom_out_label">Zoom Out</span>
								</button>
								<div class="splitToolbarButtonSeparator"></div>
								<button class="toolbarButton zoomIn" title="Zoom In" tabindex="9" data-l10n-id="zoom_in">
									<span data-l10n-id="zoom_in_label">Zoom In</span>
								</button>
							</div>
							<span id="scaleSelectContainer" class="dropdownToolbarButton">
								<select id="scaleSelect" title="Zoom" oncontextmenu="return false;" tabindex="10" data-l10n-id="zoom">
									<option id="pageAutoOption" value="auto" selected="selected" data-l10n-id="page_scale_auto">Automatic Zoom</option>
									<option id="pageActualOption" value="page-actual" data-l10n-id="page_scale_actual">Actual Size</option>
									<option id="pageFitOption" value="page-fit" data-l10n-id="page_scale_fit">Fit Page</option>
									<option id="pageWidthOption" value="page-width" data-l10n-id="page_scale_width">Full Width</option>
									<option id="customScaleOption" value="custom"></option>
									<option value="0.5">50%</option>
									<option value="0.75">75%</option>
									<option value="1">100%</option>
									<option value="1.25">125%</option>
									<option value="1.5">150%</option>
									<option value="2">200%</option>
								</select>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<menu type="context" id="viewerContextMenu">
			<menuitem label="First Page" id="first_page"
			data-l10n-id="first_page" ></menuitem>
			<menuitem label="Last Page" id="last_page"
			data-l10n-id="last_page" ></menuitem>
			<menuitem label="Rotate Counter-Clockwise" id="page_rotate_ccw"
			data-l10n-id="page_rotate_ccw" ></menuitem>
			<menuitem label="Rotate Clockwise" id="page_rotate_cw"
			data-l10n-id="page_rotate_cw" ></menuitem>
		</menu>

		<div id="viewerContainer">
			<div id="viewer" contextmenu="viewerContextMenu"></div>
		</div>

		<div id="loadingBox">
			<div id="loading"></div>
			<div id="loadingBar"><div class="progress"></div></div>
		</div>

		<div id="errorWrapper" hidden='true'>
			<div id="errorMessageLeft">
				<span id="errorMessage"></span>
				<button id="errorShowMore" onclick="" oncontextmenu="return false;" data-l10n-id="error_more_info">
					More Information
				</button>
				<button id="errorShowLess" onclick="" oncontextmenu="return false;" data-l10n-id="error_less_info" hidden='true'>
					Less Information
				</button>
			</div>
			<div id="errorMessageRight">
				<button id="errorClose" oncontextmenu="return false;" data-l10n-id="error_close">
					Close
				</button>
			</div>
			<div class="clearBoth"></div>
			<textarea id="errorMoreInfo" hidden='true' readonly="readonly"></textarea>
		</div>
	</div> <!-- mainContainer -->
</div> <!-- outerContainer -->
<div id="printContainer"></div>
</body>
</html>