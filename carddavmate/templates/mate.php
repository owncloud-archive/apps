<script type="text/javascript">
<!--
//configuration
globalNetworkCheckSettings.href = '<?php echo $_['carddavUrl']; ?>';
globalInterfaceLanguage = '<?php echo $l->t('en_US'); ?>';
//-->
</script>
<div id="carddavmate">
	<div id="LoginPage">
		<div class="window">
			<div id="Login">
				<form onsubmit="login(); return false;">
					<table>
						<tr>
							<td><img data-type="system_logo" src="<?php echo OCP\Util::imagePath('carddavmate', 'logo.svg'); ?>" alt="Logo" /></td>
						</tr>
						<tr>
							<td><input data-type="system_username" type="text" class="fs" autocomplete="off" placeholder="Login" value="<?php echo OCP\User::getUser(); ?>" /></td>
						</tr>
						<tr>
							<td><input data-type="system_password" type="password" class="fs" autocomplete="off" placeholder="Password" /></td>
						</tr>
						<tr>
							<td data-size="full">
								<select data-type="language" onchange="globalInterfaceLanguage=$(this).find('option').filter(':selected').attr('data-type'); init();">
									<option data-type=""></option>
								</select>
							</td>
						</tr>
						<tr>
							<td><input data-type="system_login" type="submit" value="Login" /></td>
						</tr>
						<tr id="login_message" style="display: none;"><td></td></tr>
					</table>
				</form>
			</div>
			<div id="LoginLoader">
				<div class="loader"></div>
			</div>
		</div>
	</div>
	<div id="System">
		<div class="update_d" style="display: none;">
			<div class="update_h"></div>
		</div>
		<div class="resources_d">
			<div data-type="resources_txt" class="resources_h">Resources</div>
			<input id="Logout" style="display: none;" data-url="" class="system_l" type="image" src="<?php echo OCP\Util::imagePath('carddavmate', 'logout.svg'); ?>" alt="Logout" onclick="logout();" />
		</div>
		<div class="collection_d">
			<div data-type="addressbook_txt" class="collection_h">Addressbook</div>
			<input id="AddContact" disabled="disabled" data-account-uid="" data-url="" data-filter-url="" class="collection_a element_no_display" type="image" src="<?php echo OCP\Util::imagePath('carddavmate', 'new_contact.svg'); ?>" alt="Add Contact" onclick="$('#ResourceListOverlay').fadeTo(globalEditorFadeAnimation,0.6); $('#ABListOverlay').fadeTo(globalEditorFadeAnimation,0.6); $('#ABList').find('.ablist_item').removeClass('ablist_item_selected'); editor_cleanup(true); $('#vcard_editor').attr('data-account-uid',this.getAttribute('data-account-uid')); $('#vcard_editor').attr('data-url',this.getAttribute('data-url')); 
		$('#vcard_editor').find('[data-type=cancel]').attr('data-id',globalAddressbookList.getLoadedContactUID());
		processEditorElements('add',null); $('[data-type=given]').focus();" />
		</div>
		<div class="contact_d">
			<div data-type="contact_txt" class="contact_h">Contact</div>
		</div>
		<div id="ResourceList">
			<div id="ResourceListTemplate" style="display: none;">
				<div class="resource_header"></div>
				<div class="resource_item">
					<div class="resource"></div>
					<div class="contact_group">
						<div class="group" style="display: none;"></div>
					</div>
				</div>
			</div>
		</div>
		<div id="ResourceListOverlay"></div>
		<div id="SearchBox">
			<img data-type="invalid" style="position: inline; margin-top: 0px; margin-left: 3px; vertical-align: top;" src="<?php echo OCP\Util::imagePath('carddavmate', 'search.svg'); ?>" alt="invalid" />
			<div class="container">
				<input data-type="search" type="text" placeholder="Search" size="45" value="" />
			</div>
			<img data-type="reset" style="display: none; position: absolute; margin-top: 7px; right: 9px; vertical-align: top;" src="<?php echo OCP\Util::imagePath('carddavmate', 'x.svg'); ?>" alt="reset" onclick="if(globalQs!=null) {$('[data-type=search]').val(''); globalQs.search('');}" />
		</div>
		<div id="ABList">
			<div id="ABListTemplate" style="display: none;">
				<div class="ablist_header" style="display: none;"></div>
				<div class="ablist_item" style="display: none;">
					<div class="ablist_item_data"></div>
					<div data-type="searchable_data" style="display: none;"></div>
				</div>
			</div>
		</div>
		<div id="ABListLoader">
			<div class="half">
				<div class="loader"></div>
			</div>
		</div>
		<div id="ABListOverlay"></div>
		<div id="ABMessage">
			<div id="ABMessageText"></div>
		</div>
		<div id="ABContact">
			<div id="vCardTemplate">
				<div id="ABInMessage">
					<div id="ABInMessageText">
					</div>
				</div>
				<div id="EditorBox" style="display: none;">
					<table id="vcard_editor" data-url="" data-etag="" data-editor-state="show">
						<tr>
							<td class="opw zero_height">
							</td>
							<td class="opw zero_height">
							</td>
							<td class="type zero_height">
							</td>
							<td colspan="2" class="zero_height">
							</td>
						</tr>
						<tr>
							<td colspan="5" class="clean">
								<table>
									<tr>
										<td rowspan="8" class="photo_box">
											<div class="photo_div">
												<img id="photo" data-type="photo" class="photo" src="<?php echo OCP\Util::imagePath('carddavmate', 'user.svg'); ?>" alt="Photo" />
											</div>
										</td>
										<td><input data-type="given" type="text" class="hs" placeholder="FirstName" value="" /></td>
										<td><input data-type="family" type="text" class="hs" placeholder="LastName" value="" /></td>
									</tr>
									<tr>
										<td><input data-type="middle" type="text" class="hs" placeholder="MiddleName" value="" /></td>
										<td><input data-type="nickname" type="text" class="hs" placeholder="NickName" value="" /></td>
									</tr>
									<tr>
										<td><input data-type="prefix" type="text" class="hs" placeholder="Prefix" value="" /></td>
										<td><input data-type="suffix" type="text" class="hs" placeholder="Suffix" value="" /></td>
									</tr>
									<tr>
										<td>
											<input data-type="date_bday" type="text" class="hs" placeholder="BirthDay" value="" /><img data-type="invalid" style="position: inline; margin-top: 1px; margin-left: -20px; display: none; vertical-align: top;" src="<?php echo OCP\Util::imagePath('carddavmate', 'error_b.svg'); ?>" alt="invalid" />
										</td>
										<td>
											<input data-type="date_anniversary" type="text" class="hs" placeholder="Anniversary" value="" /><img data-type="invalid" style="position: inline; margin-top: 1px; margin-left: -20px; display: none; vertical-align: top;" src="<?php echo OCP\Util::imagePath('carddavmate', 'error_b.svg'); ?>" alt="invalid" />
										</td>
									</tr>
									<tr>
										<td colspan="2"><input data-type="title" type="text" class="fs" placeholder="JobTitle" value="" /></td>
									</tr>
									<tr>
										<td colspan="2"><input data-type="org" type="text" class="fs" placeholder="Company" size="45" value="" /></td>
									</tr>
									<tr>
										<td colspan="2"><input data-type="department" type="text" class="fs" placeholder="Department" size="45" value="" /></td>
									</tr>
									<tr class="heightfix">
										<td colspan="2" class="heightfix">
											<label>
												<input data-type="isorg" type="checkbox" onclick="if($(this).prop('checked')) {if($('#vcard_editor').find('img[data-type=photo]').attr('src')=='<?php echo OCP\Util::imagePath('carddavmate', 'user.svg'); ?>') $('#vcard_editor').find('img[data-type=photo]').attr('src','<?php echo OCP\Util::imagePath('carddavmate', 'company.svg'); ?>')} else if($('#vcard_editor').find('img[data-type=photo]').attr('src')=='<?php echo OCP\Util::imagePath('carddavmate', 'company.svg'); ?>') $('#vcard_editor').find('img[data-type=photo]').attr('src','<?php echo OCP\Util::imagePath('carddavmate', 'user.svg'); ?>')" /><span data-type="company_contact">Company Contact</span>
											</label>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td data-type="phone_txt" colspan="5" class="attr_desc">Phone</td>
						</tr>
						<tr data-type="%phone" data-id="0">
							<td data-type="%del" style="visibility: hidden;"><input class="op" type="image" src="<?php echo OCP\Util::imagePath('carddavmate', 'op_del.svg'); ?>" alt="-" /></td>
							<td data-type="%add"><input class="op" type="image" src="<?php echo OCP\Util::imagePath('carddavmate', 'op_add.svg'); ?>" alt="+" /></td>
							<td data-size="small">
								<select data-type="phone_type">
									<option data-type="work">work</option>
									<option data-type="home">home</option>
									<option data-type="cell">mobile</option>
									<option data-type="cell_work">work mobile</option>
									<option data-type="cell_home">home mobile</option>
									<option data-type="main">main</option>
									<option data-type="pager">pager</option>
									<option data-type="fax">fax</option>
									<option data-type="fax_work">work fax</option>
									<option data-type="fax_home">home fax</option>
									<option data-type="iphone">iPhone</option>
									<option data-type="other">other</option>
								</select>
							</td>
							<td colspan="2" onmouseover="if(typeof globalUriHandlerTel!='undefined' && globalUriHandlerTel!=null && $(this).find('input[data-type=value]').prop('readonly') && $(this).find('input[type=image]').css('visibility')=='hidden') $(this).find('input[type=image]').css('visibility','')" onmouseout="$(this).find('input[type=image]').css('visibility','hidden');">
								<input data-type="value" type="text" class="fs" placeholder="Phone" value="" /><input data-type="value_handler" style="position: inline; margin-left: -13px; visibility: hidden; vertical-align: top;" type="image" src="<?php echo OCP\Util::imagePath('carddavmate', 'arrow.svg'); ?>" alt=">" onclick="if(typeof globalUriHandlerTel!='undefined' && globalUriHandlerTel!=null) parent.location=globalUriHandlerTel+$(this).parent().find('input[data-type=value]').val();" />
							</td>
						</tr>
						<tr>
							<td data-type="email_txt" colspan="5" class="attr_desc">Email</td>
						</tr>
						<tr data-type="%email" data-id="0">
							<td data-type="%del" style="visibility: hidden;"><input class="op" type="image" src="<?php echo OCP\Util::imagePath('carddavmate', 'op_del.svg'); ?>" alt="-" /></td>
							<td data-type="%add"><input class="op" type="image" src="<?php echo OCP\Util::imagePath('carddavmate', 'op_add.svg'); ?>" alt="+" /></td>
							<td data-size="small">
								<select data-type="email_type">
									<option data-type="internet_work">work</option>
									<option data-type="home_internet">home</option>
									<option data-type="/mobileme/_internet">mobileMe</option>
									<option data-type="/_$!<other>!$_/_internet">other</option>
								</select>
							</td>
							<td colspan="2" onmouseover="if(typeof globalUriHandlerEmail!='undefined' && globalUriHandlerEmail!=null && $(this).find('input[data-type=value]').prop('readonly') && $(this).find('input[type=image]').css('visibility')=='hidden') $(this).find('input[type=image]').css('visibility','')" onmouseout="$(this).find('input[type=image]').css('visibility','hidden');">
								<input data-type="value" type="text" class="fs" placeholder="Email" value="" /><input data-type="value_handler" style="position: inline; margin-left: -13px; visibility: hidden; vertical-align: top;" type="image" src="<?php echo OCP\Util::imagePath('carddavmate', 'arrow.svg'); ?>" alt=">" onclick="if(typeof globalUriHandlerEmail!='undefined' && globalUriHandlerEmail!=null) parent.location=globalUriHandlerEmail+$(this).parent().find('input[data-type=value]').val();" />
							</td>
						</tr>
						<tr>
							<td data-type="url_txt" colspan="5" class="attr_desc">URL</td>
						</tr>
						<tr data-type="%url" data-id="0">
							<td data-type="%del" style="visibility: hidden;"><input class="op" type="image" src="<?php echo OCP\Util::imagePath('carddavmate', 'op_del.svg'); ?>" alt="-" /></td>
							<td data-type="%add"><input class="op" type="image" src="<?php echo OCP\Util::imagePath('carddavmate', 'op_add.svg'); ?>" alt="+" /></td>
							<td data-size="small">
								<select data-type="url_type">
									<option data-type="work">work</option>
									<option data-type="home">home</option>
									<option data-type="/_$!<homepage>!$_/">homePage</option>
									<option data-type="/_$!<other>!$_/">other</option>
								</select>
							</td>
							<td colspan="2" onmouseover="if(typeof globalUriHandlerUrl!='undefined' && globalUriHandlerUrl!=null && $(this).find('input[data-type=value]').prop('readonly') && $(this).find('input[data-type=value]').val()!='' && $(this).find('input[type=image]').css('visibility')=='hidden') $(this).find('input[type=image]').css('visibility','')" onmouseout="$(this).find('input[type=image]').css('visibility','hidden');">
								<input data-type="value" type="text" class="fs" placeholder="URL" value="" /><input data-type="value_handler" style="position: inline; margin-left: -13px; visibility: hidden; vertical-align: top;" type="image" src="<?php echo OCP\Util::imagePath('carddavmate', 'arrow.svg'); ?>" alt=">" onclick="if(typeof globalUriHandlerUrl!='undefined' && globalUriHandlerUrl!=null) {var value=$(this).parent().find('input[data-type=value]').val(); if(value.match(RegExp('^[a-z0-9]+:','i'))==null) value=globalUriHandlerUrl+value; window.open(value);}" />
							</td>
						</tr>
						<tr>
							<td data-type="related_txt" colspan="5" class="attr_desc">Related</td>
						</tr>
						<tr data-type="%person" data-id="0">
							<td data-type="%del" style="visibility: hidden;"><input class="op" type="image" src="<?php echo OCP\Util::imagePath('carddavmate', 'op_del.svg'); ?>" alt="-" /></td>
							<td data-type="%add"><input class="op" type="image" src="<?php echo OCP\Util::imagePath('carddavmate', 'op_add.svg'); ?>" alt="+" /></td>
							<td data-size="small">
								<select data-type="person_type">
									<option data-type="/_$!<father>!$_/">father</option>
									<option data-type="/_$!<mother>!$_/">mother</option>
									<option data-type="/_$!<parent>!$_/">parent</option>
									<option data-type="/_$!<brother>!$_/">brother</option>
									<option data-type="/_$!<sister>!$_/">sister</option>
									<option data-type="/_$!<child>!$_/">child</option>
									<option data-type="/_$!<friend>!$_/">friend</option>
									<option data-type="/_$!<spouse>!$_/">spouse</option>
									<option data-type="/_$!<partner>!$_/">partner</option>
									<option data-type="/_$!<assistant>!$_/">assistant</option>
									<option data-type="/_$!<manager>!$_/">manager</option>
									<option data-type="/_$!<other>!$_/">other</option>
								</select>
							</td>
							<td colspan="2"><input data-type="value" type="text" class="fs" placeholder="Name" value="" /></td>
						</tr>
						<tr>
							<td data-type="im_txt" colspan="5" class="attr_desc">IM</td>
						</tr>
						<tr data-type="%im" data-id="0">
							<td data-type="%del" style="visibility: hidden;"><input class="op" type="image" src="<?php echo OCP\Util::imagePath('carddavmate', 'op_del.svg'); ?>" alt="-" /></td>
							<td data-type="%add"><input class="op" type="image" src="<?php echo OCP\Util::imagePath('carddavmate', 'op_add.svg'); ?>" alt="+" /></td>
							<td data-size="small">
								<select data-type="im_type">
									<option data-type="work">work</option>
									<option data-type="home">home</option>
									<option data-type="/mobileme/">mobileMe</option>
									<option data-type="/_$!<other>!$_/">other</option>
								</select>
							</td>
							<td><input data-type="value" type="text" class="ms" placeholder="UserID" value="" /></td>
							<td data-size="small">
								<select data-type="im_service_type">
									<option data-type="aim">AIM</option>
									<option data-type="icq">ICQ</option>
									<option data-type="irc">IRC</option>
									<option data-type="jabber">Jabber</option>
									<option data-type="msn">MSN</option>
									<option data-type="yahoo">Yahoo</option>
									<option data-type="facebook">Facebook</option>
									<option data-type="gadugadu">GaduGadu</option>
									<option data-type="googletalk">GoogleTalk</option>
									<option data-type="qq">QQ</option>
									<option data-type="skype">Skype</option>
								</select>
							</td>
						</tr>
						<tr>
							<td data-type="address_txt" colspan="5" class="attr_desc">Address</td>
						</tr>
						<tr data-type="%address" data-id="0">
							<td data-type="%del" style="visibility: hidden;"><input class="op" type="image" src="<?php echo OCP\Util::imagePath('carddavmate', 'op_del.svg'); ?>" alt="-" /></td>
							<td data-type="%add"><input class="op" type="image" src="<?php echo OCP\Util::imagePath('carddavmate', 'op_add.svg'); ?>" alt="+" /></td>
							<td data-size="small">
								<select data-type="address_type">
									<option data-type="work">work</option>
									<option data-type="home">home</option>
									<option data-type="/_$!<other>!$_/">other</option>
								</select>
							</td>
							<td colspan="2" class="clean">
								<table>
									<tr data-type="container">
										<td data-addr-fid="0" colspan="2">
											<input data-type="value" data-addr-field="" type="text" class="fs" placeholder="" value="" />
										</td>
									</tr>
									<tr data-type="container">
										<td data-addr-fid="1" colspan="2" data-size="full">
										</td>
									</tr>
									<tr data-type="container">
										<td data-addr-fid="2" colspan="2">
											<input data-type="value" data-addr-field="" type="text" class="fs" placeholder="" value="" />
										</td>
									</tr>
									<tr data-type="container">
										<td data-addr-fid="3" colspan="2">
											<input data-type="value" data-addr-field="" type="text" class="fs" placeholder="" value="" />
										</td>
									</tr>
									<tr data-type="container">
										<td data-addr-fid="4" colspan="2">
											<input data-type="value" data-addr-field="" type="text" class="fs" placeholder="" value="" />
										</td>
									</tr>
									<tr data-type="container">
										<td data-addr-fid="5">
											<input data-type="value" data-addr-field="" type="text" class="hs" placeholder="" value="" />
										</td>
										<td data-addr-fid="6">
											<input data-type="value" data-addr-field="" type="text" class="hs" placeholder="" value="" />
										</td>
									</tr>
									<tr data-type="container">
										<td data-addr-fid="7">
											<input data-type="value" data-addr-field="" type="text" class="hs" placeholder="" value="" />
										</td>
										<td data-addr-fid="8" data-size="half">
										</td>
									</tr>
									<tr data-type="container">
										<td data-addr-fid="9" colspan="2">
											<input data-type="value" data-addr-field="" type="text" class="fs" placeholder="" value="" />
										</td>
									</tr>
									<tr data-type="container">
										<td data-addr-fid="10" colspan="2">
											<input data-type="value" data-addr-field="" type="text" class="fs" placeholder="" value="" />
										</td>
									</tr>
	 								<tr data-type="container">
										<td data-addr-fid="11" colspan="2" data-size="full">
											<select data-addr-field="country" data-type="%country">
												<option data-type="" data-full-name=""></option>
											</select>
										</td>
									</tr>
									<tr data-type="container">
										<td data-addr-fid="12" colspan="2">
											<input data-type="value" data-addr-field="" type="text" class="fs" placeholder="" value="" />
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td data-type="categories_txt" colspan="5" class="attr_desc">Categories</td>
						</tr>
						<tr data-type="%categories" data-id="0">
							<td></td>
							<td></td>
							<td colspan="3">
								<input data-type="value" class="fs" name="tags" id="tags" value="" />
							</td>
						</tr>
						<tr>
							<td data-type="note_txt" colspan="5" class="attr_desc">Note</td>
						</tr>
						<tr data-type="%note" data-id="0">
							<td></td>
							<td></td>
							<td colspan="3">
								<textarea data-type="value" class="ms" placeholder="NoteText"></textarea>
							</td>
						</tr>
						<tr>
							<td colspan="5" class="buttons">
								<input data-type="edit" type="button" value="Edit" onclick="$('#ResourceListOverlay').fadeTo(globalEditorFadeAnimation,0.6); $('[data-type=given]').focus(); $('#ABListOverlay').fadeTo(globalEditorFadeAnimation,0.6); processEditorElements('show',null);" />
								<input data-type="save" type="button" value="Save" onclick="
								if($('[id=vcard_editor]').find('img[data-type=invalid]').filter(function() { return this.style.display != 'none' }).length>0) {show_editor_message('in','message_error','Error: \'unable to save\': correct the highlighted invalid values!',globalHideInfoMessageAfter); return false;} else {$('#ResourceListOverlay').fadeTo(globalEditorFadeAnimation,0.6); $('#ABListOverlay').fadeTo(globalEditorFadeAnimation,0.6); $('#AddContact').prop('disabled',true); $('#ABContactOverlay').fadeTo(globalEditorFadeAnimation,1,function() {
dataToVcard($('[id=vcard_editor]').attr('data-account-uid'), $('[id=vcard_editor]').attr('data-url'), $('#AddContact').attr('data-filter-url'), $('[id=vcard_editor]').attr('data-etag'))})}" />
								<input data-type="cancel" type="button" value="Cancel" data-id="" onclick="$('#ResourceListOverlay').fadeOut(globalEditorFadeAnimation); $('#ABListOverlay').fadeOut(globalEditorFadeAnimation); globalAddressbookList.loadContactByUID(this.getAttribute('data-id'));" />
								<input data-type="delete_from_group" type="button" value="Delete from Group" onclick="
								$('#ResourceListOverlay').fadeTo(globalEditorFadeAnimation,0.6); $('#ABListOverlay').fadeTo(globalEditorFadeAnimation,0.6); $('#AddContact').prop('disabled',true); $('#ABContactOverlay').fadeTo(globalEditorFadeAnimation,1,function() {
									lockAndPerformToCollection({accountUID: $('[id=vcard_editor]').attr('data-account-uid'), uid: $('[id=vcard_editor]').attr('data-url')}, $('#AddContact').attr('data-filter-url'), 'DELETE_FROM_GROUP');
									});
								" />
								<input data-type="delete" type="button" value="Delete" onclick="
								$('#ResourceListOverlay').fadeTo(globalEditorFadeAnimation,0.6); $('#ABListOverlay').fadeTo(globalEditorFadeAnimation,0.6); $('#AddContact').prop	('disabled',true); $('#ABContactOverlay').fadeTo(globalEditorFadeAnimation,1,function() {
									lockAndPerformToCollection({accountUID: $('[id=vcard_editor]').attr('data-account-uid'), uid: $('[id=vcard_editor]').attr('data-url')}, $('#AddContact').attr('data-filter-url'), 'DELETE');
									});
								" />
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<div id="ABContactOverlay">
			<div class="half">
				<div class="loader"></div>
			</div>
		</div>
	</div>
</div>
