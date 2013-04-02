/*
CardDavMATE - CardDav Web Client
Copyright (C) 2011-2012 Jan Mate <jan.mate@inf-it.com>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

var globalCardDavMATEVersion='0.9.7.1';
var globalVersionCheckURL='http://www.inf-it.com/versioncheck/CardDavMATE/?v='+globalCardDavMATEVersion;
var globalAddressbookList = new AddressbookList();
var globalResourceList=new ResourceList();
var globalWindowFocus=true;
var globalAddressbookIntervalID=null;
var globalLoginUsername='';
var globalLoginPassword='';
var globalResourceIntervalID=null;
var globalQs=null;

var origResourceListTemplate=null;
var origABListTemplate=null;
var origVcardTemplate=null;
var cleanResourceListTemplate=null;
var cleanABListTemplate=null;
var cleanVcardTemplate=null;

function login()
{
	$('#LoginLoader').fadeTo(1200,1,
		function()
		{
			globalLoginUsername=$('#LoginPage').find('[data-type="system_username"]').val();
			globalLoginPassword=$('#LoginPage').find('[data-type="system_password"]').val();

			loadConfig();
		}
	);
}

function logout()
{
	globalLoginUsername='';
	globalLoginPassword='';
	globalAddressbookList.reset();
	globalResourceList.reset();

	if(globalAddressbookIntervalID!=null)
		clearInterval(globalAddressbookIntervalID);

	if(globalResourceIntervalID!=null)
		clearInterval(globalResourceIntervalID);

	for(var i=globalAccountSettings.length-1;i>=0;i--)
		if(globalAccountSettings[i].type=='network')
			this.globalAccountSettings.splice(i,1);

	// if the editor is in "edit" state during the logout,
	//  we need to remove all overlays (for next login)
	$('#ResourceListOverlay').fadeOut(2000);
	$('#ABListOverlay').fadeOut(2000);

	$('#System').fadeOut(2000,function()
		{
			if(typeof globalDemoMode=='undefined')
			{
				$('[data-type="system_username"]').val('').change();
				$('[data-type="system_password"]').val('').change();
			}
			$('#LoginLoader').fadeOut(1200);
			$('#System').find('div.update_d').css('display','none');
		}
	);
	$('#LoginPage').fadeTo(2000,1,function(){init()});
}

function main()
{
	// create backup from the original editor objects (needed for localization switching)
	origResourceListTemplate = $('#ResourceListTemplate').clone().wrap('<div>').parent().html();
	origABListTemplate = $('#ABListTemplate').clone().wrap('<div>').parent().html();
	origVcardTemplate = $('#vCardTemplate').clone().wrap('<div>').parent().html();

	/* language selector */
	var lang_num=0;
	var language_option=$('#Login').find('[data-type="language"]').find('option');
	$('#Login').find('[data-type="language"]').html('');

	if(typeof globalInterfaceCustomLanguages!='undefined' && globalInterfaceCustomLanguages.length!=undefined && globalInterfaceCustomLanguages.length>0)
	{
		for(var i=0;i<globalInterfaceCustomLanguages.length;i++)
			if(localization[globalInterfaceCustomLanguages[i]]!=undefined)
			{
				var tmp=language_option;
				tmp.attr('data-type',globalInterfaceCustomLanguages[i]);
				tmp.text(localization[globalInterfaceCustomLanguages[i]]['_name_']);
				$('#Login').find('[data-type="language"]').append(tmp.clone());
				lang_num++;
			}
	}
	if(lang_num==0)	// no language option, use the default (all languages from localization.js)
	{
		for(var loc in localization)
		{
			var tmp=language_option;
			tmp.attr('data-type',loc);
			tmp.text(localization[loc]['_name_']);	// translation
			$('#Login').find('[data-type="language"]').append(tmp.clone());
		}
	}

	// select the globalInterfaceLanguage in the interface
	$('[data-type="language"]').find('[data-type='+globalInterfaceLanguage+']').prop('selected',true);

	init();
}

function init()
{
	$('#ResourceList').html(origResourceListTemplate);
	$('#ABList').html(origABListTemplate);
	$('#ABContact').html(origVcardTemplate);

	localizeAddressTypes();
	var country_option=$('[data-type="\\%address"]').find('[data-type="\\%country"]').find('option');
	$('[data-type="\\%address"]').find('[data-type="\\%country"]').html('');

	// if globalAddressCountryFavorites defined reorder the addressTypes
	if(globalAddressCountryFavorites!=undefined && globalAddressCountryFavorites.length>0)
		for(var i=globalAddressCountryFavorites.length-1;i>=0;i--)
		{
			var tmp=new Object();
			tmp[globalAddressCountryFavorites[i]]=addressTypes[globalAddressCountryFavorites[i]];
			delete addressTypes[globalAddressCountryFavorites[i]];
			addressTypes=$.extend(tmp,addressTypes);
			delete tmp;
		}

	for(var country in addressTypes)
	{
		var tmp=country_option;
		tmp.attr('data-type',country);
		tmp.attr('data-full-name',addressTypes[country][0]);
		tmp.text(localization[globalInterfaceLanguage]['txtAddressCountry'+country.toUpperCase()]);	// translation
		$('[data-type="\\%address"]').find('[data-type="\\%country"]').append(tmp.clone());
	}

	$('[data-type="\\%address"]').find('[data-type="\\%country"]').attr('data-autoselect',globalDefaultAddressCountry);

	// interface translation
	$('[data-type="system_logo"]').attr('alt',localization[globalInterfaceLanguage].altLogo);
	$('[data-type="system_username"]').attr('placeholder',localization[globalInterfaceLanguage].pholderUsername);
	$('[data-type="system_password"]').attr('placeholder',localization[globalInterfaceLanguage].pholderPassword);
	$('[data-type="system_login"]').val(localization[globalInterfaceLanguage].buttonLogin);

	$('[data-type="resources_txt"]').text(localization[globalInterfaceLanguage].txtResources);
	$('[data-type="addressbook_txt"]').text(localization[globalInterfaceLanguage].txtAddressbook);
	$('[data-type="contact_txt"]').text(localization[globalInterfaceLanguage].txtContact);
	$('[data-type="search"]').attr('placeholder',localization[globalInterfaceLanguage].txtSearch);
	$('#AddContact').attr('alt',localization[globalInterfaceLanguage].altAddContact);
	$('#Logout').attr('alt',localization[globalInterfaceLanguage].altLogout);
	$('[data-type="photo"]').attr('alt',localization[globalInterfaceLanguage].altPhoto);

	$('[data-type="given"]').attr('placeholder',localization[globalInterfaceLanguage].pholderGiven);
	$('[data-type="family"]').attr('placeholder',localization[globalInterfaceLanguage].pholderFamily);
	$('[data-type="middle"]').attr('placeholder',localization[globalInterfaceLanguage].pholderMiddle);
	$('[data-type="nickname"]').attr('placeholder',localization[globalInterfaceLanguage].pholderNickname);
	$('[data-type="prefix"]').attr('placeholder',localization[globalInterfaceLanguage].pholderPrefix);
	$('[data-type="suffix"]').attr('placeholder',localization[globalInterfaceLanguage].pholderSuffix);
	$('[data-type="date_bday"]').attr('placeholder',localization[globalInterfaceLanguage].pholderBday);
	$('[data-type="date_anniversary"]').attr('placeholder',localization[globalInterfaceLanguage].pholderAnniversary);
	$('[data-type="title"]').attr('placeholder',localization[globalInterfaceLanguage].pholderTitle);
	$('[data-type="org"]').attr('placeholder',localization[globalInterfaceLanguage].pholderOrg);
	$('[data-type="department"]').attr('placeholder',localization[globalInterfaceLanguage].pholderDepartment);
	$('span[data-type="company_contact"]').text(localization[globalInterfaceLanguage].txtCompanyContact);

	$('[data-type="\\%del"]').attr('alt',localization[globalInterfaceLanguage].altDel);
	$('[data-type="\\%add"]').attr('alt',localization[globalInterfaceLanguage].altAdd);
	$('[data-type="value_handler"]').attr('alt',localization[globalInterfaceLanguage].altValueHandler);

	$('[data-type="phone_txt"]').text(localization[globalInterfaceLanguage].txtPhone);
	$('[data-type="\\%phone"]').find('input[data-type="value"]').attr('placeholder',localization[globalInterfaceLanguage].pholderPhoneVal);
	$('[data-type="\\%phone"]').find('[data-type="work"]').text(localization[globalInterfaceLanguage].txtPhoneWork);
	$('[data-type="\\%phone"]').find('[data-type="home"]').text(localization[globalInterfaceLanguage].txtPhoneHome);
	$('[data-type="\\%phone"]').find('[data-type="cell"]').text(localization[globalInterfaceLanguage].txtPhoneCell);
	$('[data-type="\\%phone"]').find('[data-type="cell_work"]').text(localization[globalInterfaceLanguage].txtPhoneCellWork);
	$('[data-type="\\%phone"]').find('[data-type="cell_home"]').text(localization[globalInterfaceLanguage].txtPhoneCellHome);
	$('[data-type="\\%phone"]').find('[data-type="main"]').text(localization[globalInterfaceLanguage].txtPhoneMain);
	$('[data-type="\\%phone"]').find('[data-type="pager"]').text(localization[globalInterfaceLanguage].txtPhonePager);
	$('[data-type="\\%phone"]').find('[data-type="fax"]').text(localization[globalInterfaceLanguage].txtPhoneFax);
	$('[data-type="\\%phone"]').find('[data-type="fax_work"]').text(localization[globalInterfaceLanguage].txtPhoneFaxWork);
	$('[data-type="\\%phone"]').find('[data-type="fax_home"]').text(localization[globalInterfaceLanguage].txtPhoneFaxHome);
	$('[data-type="\\%phone"]').find('[data-type="iphone"]').text(localization[globalInterfaceLanguage].txtPhoneIphone);
	$('[data-type="\\%phone"]').find('[data-type="other"]').text(localization[globalInterfaceLanguage].txtPhoneOther);

	$('[data-type="email_txt"]').text(localization[globalInterfaceLanguage].txtEmail);
	$('[data-type="\\%email"]').find('input[data-type="value"]').attr('placeholder',localization[globalInterfaceLanguage].pholderEmailVal);
	$('[data-type="\\%email"]').find('[data-type="internet_work"]').text(localization[globalInterfaceLanguage].txtEmailWork);
	$('[data-type="\\%email"]').find('[data-type="home_internet"]').text(localization[globalInterfaceLanguage].txtEmailHome);
	$('[data-type="\\%email"]').find('[data-type="/mobileme/_internet"]').text(localization[globalInterfaceLanguage].txtEmailMobileme);
	$('[data-type="\\%email"]').find('[data-type="/_$!<other>!$_/_internet"]').text(localization[globalInterfaceLanguage].txtEmailOther);

	$('[data-type="url_txt"]').text(localization[globalInterfaceLanguage].txtUrl);
	$('[data-type="\\%url"]').find('input[data-type="value"]').attr('placeholder',localization[globalInterfaceLanguage].pholderUrlVal);
	$('[data-type="\\%url"]').find('[data-type="work"]').text(localization[globalInterfaceLanguage].txtUrlWork);
	$('[data-type="\\%url"]').find('[data-type="home"]').text(localization[globalInterfaceLanguage].txtUrlHome);
	$('[data-type="\\%url"]').find('[data-type="/_$!<homepage>!$_/"]').text(localization[globalInterfaceLanguage].txtUrlHomepage);
	$('[data-type="\\%url"]').find('[data-type="/_$!<other>!$_/"]').text(localization[globalInterfaceLanguage].txtUrlOther);

	$('[data-type="related_txt"]').text(localization[globalInterfaceLanguage].txtRelated);
	$('[data-type="\\%person"]').find('input[data-type="value"]').attr('placeholder',localization[globalInterfaceLanguage].pholderRelatedVal);
	$('[data-type="\\%person"]').find('[data-type="/_$!<father>!$_/"]').text(localization[globalInterfaceLanguage].txtRelatedFather);
	$('[data-type="\\%person"]').find('[data-type="/_$!<mother>!$_/"]').text(localization[globalInterfaceLanguage].txtRelatedMother);
	$('[data-type="\\%person"]').find('[data-type="/_$!<parent>!$_/"]').text(localization[globalInterfaceLanguage].txtRelatedParent);
	$('[data-type="\\%person"]').find('[data-type="/_$!<brother>!$_/"]').text(localization[globalInterfaceLanguage].txtRelatedBrother);
	$('[data-type="\\%person"]').find('[data-type="/_$!<sister>!$_/"]').text(localization[globalInterfaceLanguage].txtRelatedSister);
	$('[data-type="\\%person"]').find('[data-type="/_$!<child>!$_/"]').text(localization[globalInterfaceLanguage].txtRelatedChild);
	$('[data-type="\\%person"]').find('[data-type="/_$!<friend>!$_/"]').text(localization[globalInterfaceLanguage].txtRelatedFriend);
	$('[data-type="\\%person"]').find('[data-type="/_$!<spouse>!$_/"]').text(localization[globalInterfaceLanguage].txtRelatedSpouse);
	$('[data-type="\\%person"]').find('[data-type="/_$!<partner>!$_/"]').text(localization[globalInterfaceLanguage].txtRelatedPartner);
	$('[data-type="\\%person"]').find('[data-type="/_$!<assistant>!$_/"]').text(localization[globalInterfaceLanguage].txtRelatedAssistant);
	$('[data-type="\\%person"]').find('[data-type="/_$!<manager>!$_/"]').text(localization[globalInterfaceLanguage].txtRelatedManager);
	$('[data-type="\\%person"]').find('[data-type="/_$!<other>!$_/"]').text(localization[globalInterfaceLanguage].txtRelatedOther);

	$('[data-type="im_txt"]').text(localization[globalInterfaceLanguage].txtIm);
	$('[data-type="\\%im"]').find('input[data-type="value"]').attr('placeholder',localization[globalInterfaceLanguage].pholderImVal);
	$('[data-type="\\%im"]').find('[data-type="work"]').text(localization[globalInterfaceLanguage].txtImWork);
	$('[data-type="\\%im"]').find('[data-type="home"]').text(localization[globalInterfaceLanguage].txtImHome);
	$('[data-type="\\%im"]').find('[data-type="/mobileme/"]').text(localization[globalInterfaceLanguage].txtImMobileme);
	$('[data-type="\\%im"]').find('[data-type="/_$!<other>!$_/"]').text(localization[globalInterfaceLanguage].txtImOther);
	$('[data-type="\\%im"]').find('[data-type="aim"]').text(localization[globalInterfaceLanguage].txtImProtAim);
	$('[data-type="\\%im"]').find('[data-type="icq"]').text(localization[globalInterfaceLanguage].txtImProtIcq);
	$('[data-type="\\%im"]').find('[data-type="irc"]').text(localization[globalInterfaceLanguage].txtImProtIrc);
	$('[data-type="\\%im"]').find('[data-type="jabber"]').text(localization[globalInterfaceLanguage].txtImProtJabber);
	$('[data-type="\\%im"]').find('[data-type="msn"]').text(localization[globalInterfaceLanguage].txtImProtMsn);
	$('[data-type="\\%im"]').find('[data-type="yahoo"]').text(localization[globalInterfaceLanguage].txtImProtYahoo);
	$('[data-type="\\%im"]').find('[data-type="facebook"]').text(localization[globalInterfaceLanguage].txtImProtFacebook);
	$('[data-type="\\%im"]').find('[data-type="gadugadu"]').text(localization[globalInterfaceLanguage].txtImProtGadugadu);
	$('[data-type="\\%im"]').find('[data-type="googletalk"]').text(localization[globalInterfaceLanguage].txtImProtGoogletalk);
	$('[data-type="\\%im"]').find('[data-type="qq"]').text(localization[globalInterfaceLanguage].txtImProtQq);
	$('[data-type="\\%im"]').find('[data-type="skype"]').text(localization[globalInterfaceLanguage].txtImProtSkype);

	$('[data-type="address_txt"]').text(localization[globalInterfaceLanguage].txtAddress);
	$('[data-type="\\%address"]').find('[data-type="work"]').text(localization[globalInterfaceLanguage].txtAddressWork);
	$('[data-type="\\%address"]').find('[data-type="home"]').text(localization[globalInterfaceLanguage].txtAddressHome);
	$('[data-type="\\%address"]').find('[data-type="/_$!<other>!$_/"]').text(localization[globalInterfaceLanguage].txtAddressOther);

	$('[data-type="categories_txt"]').text(localization[globalInterfaceLanguage].txtCategories);

	$('[data-type="note_txt"]').text(localization[globalInterfaceLanguage].txtNote);
	$('[data-type="\\%note"]').find('textarea[data-type="value"]').attr('placeholder',localization[globalInterfaceLanguage].pholderNoteVal);

	$('[data-type="edit"]').val(localization[globalInterfaceLanguage].buttonEdit);
	$('[data-type="save"]').val(localization[globalInterfaceLanguage].buttonSave);
	$('[data-type="cancel"]').val(localization[globalInterfaceLanguage].buttonCancel);
	$('[data-type="delete_from_group"]').val(localization[globalInterfaceLanguage].buttonDeleteFromGroup);
	$('[data-type="delete"]').val(localization[globalInterfaceLanguage].buttonDelete);

	cleanResourceListTemplate = $('#ResourceListTemplate').clone().wrap('<div>').parent().html();
	cleanABListTemplate = $('#ABListTemplate').clone().wrap('<div>').parent().html();
	cleanVcardTemplate = $('#vCardTemplate').clone().wrap('<div>').parent().html();

	// CUSTOM PLACEHOLDER (initialization for the whole page)
	$('input[placeholder],textarea[placeholder]').placeholder();

	// browser check
	if(($.browser.msie && parseInt($.browser.version, 10)<9) || $.browser.opera)
		$('#login_message').css('display','').find('td').text(localization[globalInterfaceLanguage].unsupportedBrowser);

	if(typeof globalDemoMode!='undefined')
	{
		if(typeof globalDemoMode['userName']!=undefined)
			$('[data-type="system_username"]').val(globalDemoMode['userName']).change();
		if(typeof globalDemoMode['userPassword']!=undefined)
			$('[data-type="system_password"]').val(globalDemoMode['userPassword']).change();
	}
	loadConfig();
}

function run()
{
	window.onfocus = function() {globalWindowFocus=true; /*console.log('focus: true')*/}
	window.onblur = function() {globalWindowFocus=false; /*console.log('focus: false')*/}

	$('#LoginPage').fadeOut(2000);
	$('#System').fadeTo(2000,1);


	if(typeof globalAccountSettings=='undefined')
	{
		console.log('Error: \'no account configured\': see config.js!');
		return false;
	}

	if(typeof globalNewVersionNotifyUsers=='undefined' || globalNewVersionNotifyUsers!=null)
		netVersionCheck();

	// Automatically detect crossDomain settings
	var detectedHref=location.protocol+'//'+location.hostname+(location.port ? ':'+location.port : '');
	for(var i=0;i<globalAccountSettings.length;i++)
	{
		if(globalAccountSettings[i].crossDomain==undefined || typeof globalAccountSettings[i].crossDomain!='boolean')
		{
			if(globalAccountSettings[i].href.indexOf(detectedHref)==0)
				globalAccountSettings[i].crossDomain=false;
			else
				globalAccountSettings[i].crossDomain=true;

			console.log("Info: [account: '"+globalAccountSettings[i].href.replace('\/\/','//'+globalAccountSettings[i].userAuth.userName+'@')+"'] crossDomain set to: '"+(globalAccountSettings[i].crossDomain==true ? 'true' : 'false')+"'");
		}
	}

	loadResources(globalAccountSettings, true);

	// automatically reload resources
	function reloadResources() {loadResources(globalAccountSettings, false)}
	globalResourceIntervalID=setInterval(reloadResources, globalSyncResourcesInterval);
}

function loadConfig()
{
	var configLoaded=true;
	// Automatically detect crossDomain settings
	var detectedHref=location.protocol+'//'+location.hostname+(location.port ? ':'+location.port : '');

	// check username and password against the server and create config from globalNetworkCheckSettings
	if(typeof globalNetworkCheckSettings!='undefined' && globalNetworkCheckSettings!=null)
	{
		if(globalLoginUsername=='' || globalLoginPassword=='')
		{
			$('#LoginPage').fadeTo(500,1,function(){if(typeof globalDemoMode=='undefined') $('[data-type="system_username"]').focus()});
			$('#LoginLoader').fadeOut(1200);
			return false;
		}
		else
		{
			if(globalNetworkCheckSettings.crossDomain==undefined || typeof globalNetworkCheckSettings.crossDomain!='boolean')
			{
				if(globalNetworkCheckSettings.href.indexOf(detectedHref)==0)
					globalNetworkCheckSettings.crossDomain=false;
				else
					globalNetworkCheckSettings.crossDomain=true;

				console.log("Info: [globalNetworkCheckSettings: '"+globalNetworkCheckSettings.href+"'] crossDomain set to: '"+(globalNetworkCheckSettings.crossDomain==true ? 'true' : 'false')+"'");
			}
			netCheckAndCreateConfiguration(globalNetworkCheckSettings);
			return true;
		}
	}

	// load the configuration XML(s) from the network
	if(typeof globalNetworkAccountSettings!='undefined' && globalNetworkAccountSettings!=null)
	{
		if(globalLoginUsername=='' || globalLoginPassword=='')
		{
			$('#LoginPage').fadeTo(500,1,function(){if(typeof globalDemoMode=='undefined') $('[data-type="system_username"]').focus()});
			$('#LoginLoader').fadeOut(1200);
			return false;
		}
		else
		{
			if(globalNetworkAccountSettings.crossDomain==undefined || typeof globalNetworkAccountSettings.crossDomain!='boolean')
			{
				if(globalNetworkAccountSettings.href.indexOf(detectedHref)==0)
					globalNetworkAccountSettings.crossDomain=false;
				else
					globalNetworkAccountSettings.crossDomain=true;

				console.log("Info: [globalNetworkAccountSettings: '"+globalNetworkAccountSettings.href+"'] crossDomain set to: '"+(globalNetworkAccountSettings.crossDomain==true ? 'true' : 'false')+"'");
			}
			netLoadConfiguration(globalNetworkAccountSettings);
			return true;
		}
	}

	run();
}

window.onload = main;
