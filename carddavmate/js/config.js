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

// globalAccountSettings must be an array (can be undefined if you use globalNetworkCheckSettings or globalNetworkAccountSettings)
//  the href value is a "principal URL" - the last character in href must be '/'
//    principal URL != collection URL -> the client automatically detects collections for each principal URL
//    PROPER principal URL looks like:
//      https://server.com:8443/principals/users/USER/
//      https://server.com:8443/caldav.php/USER/
//    INVALID principal URL looks like:
//      https://server.com:8443/principals/users/USER/addressbook/	<- url to addressbook collection
//      https://server.com:8443/caldav.php/USER/addressbook/		<- url to addressbook collection
//      https://server.com:8443/principals/users/USER				<- missing '/'
//      https://server.com:8443/caldav.php/USER						<- missing '/'
// the hrefLabel sets the server name in the resource header - useful if your server name is 'something.server.com' and you want to see only the 'something' as the server name; if undefined, empty or or null, href value is used (see above)
// the crossDomain sets jQuery's ajax crossDomain value (must be true if your CardDavMATE installation has not the same [protocol,hostname,port] as your CardDav server - by default null = autodetect /detected setting is shown in the console/)
// the withCredentials sets jQuery's ajax withCredentials value for cross domain queries (if true, Access-Control-Allow-Origin "*" is not allowed)
// the syncInterval sets how often (in miliseconds) to asynchronously sync the active collection on background (but only if the browser window has focus)
// the timeOut sets the timeout for jQuery .ajax call (in miliseconds)
// the lockTimeOut sets the LOCK Timeout value (in miliseconds)
//var globalAccountSettings=[{href: 'https://server1.com:8443/principals/users/USERNAME1/', hrefLabel: null, crossDomain: null, withCredentials: false, userAuth: {userName: 'USERNAME1', userPassword: 'PASSWORD1'}, syncInterval: 60000, timeOut: 30000, lockTimeOut: 10000}, {href: 'https://server1.com:8443/principals/users/USERNAME2/', hrefLabel: null, crossDomain: null, withCredentials: false, userAuth: {userName: 'USERNAME2', userPassword: 'PASSWORD2'}, syncInterval: 60000, timeOut: 30000, lockTimeOut: 10000}, {href: 'https://server2.com:8443/principals/users/USERNAME/', hrefLabel: null, crossDomain: null, withCredentials: false, userAuth: {userName: 'USERNAME', userPassword: 'PASSWORD'}, syncInterval: 60000, timeOut: 30000, lockTimeOut: 10000}];

// if set, the client authenticates against the href URL (the last character in href must be '/') and if the authentication is successful it appends the USER + '/' to end of href and sets the userAuth: {userName: USER, userPassword: PASSWORD}
// then the client uses the modified globalNetworkCheckSettings in the same way as the globalAccountSettings
// this option ivokes a login screen and disallows access until successfull authentication
// the additionalResources array can contain additional resources (shared resources accessible by all users), for example: additionalResources: ['company','customers'] ... href values for these resources are created in the same way as described above for the USER
// see globalAccountSettings for more information
// Lion server example (http + https setup; see misc/readme_lion.txt for server setup):
//var globalNetworkCheckSettings={href: 'http://lion.server.com:8008/principals/users/', hrefLabel: null, additionalResources: [], crossDomain: null, withCredentials: false, syncInterval: 60000, timeOut: 30000, lockTimeOut: 10000}
//var globalNetworkCheckSettings={href: 'https://lion.server.com:8443/principals/users/', hrefLabel: null, additionalResources: [], crossDomain: null, withCredentials: false, syncInterval: 60000, timeOut: 30000, lockTimeOut: 10000}
// Davical example (for cross-domain setup see misc/config_davical.txt):
//var globalNetworkCheckSettings={href: 'http://davical.server.com:8080/caldav.php/', hrefLabel: null, additionalResources: [], crossDomain: null, withCredentials: false, syncInterval: 60000, timeOut: 30000, lockTimeOut: 10000}
// Davical example (CardDavMATE installed into Davical subdirectory - works out of the box, no additional setup required):
var globalNetworkCheckSettings={href: location.protocol+'//'+location.hostname+(location.port ? ':'+location.port: '')+location.pathname.replace(RegExp('/+[^/]+/*$'),'')+'/caldav.php/', hrefLabel: null, additionalResources: [], crossDomain: null, withCredentials: false, syncInterval: 60000, timeOut: 30000, lockTimeOut: 10000}

// if set, the configuration is loaded from the network (using HTTP auth) - the returned configuration XML settings are added
//  to globalAccountSettings ... it is possible to combine this option with the globalAccountSettings although it is not recommended
// this option ivokes a login screen and disallows access until the client get correct XML configuration file from the server
// the syncInterval is currently unused (the configuration XML is loaded only once)
// the timeOut sets the timeout for jQuery .ajax call (in miliseconds)
//var globalNetworkAccountSettings={href: 'https://www.config-server.com/auth/', crossDomain: null, withCredentials: false, syncInterval: 0, timeOut: 30000};

// default interface language - see localization.js
//  supported languages (note: value is case sensitive):
//   cs_CZ (Čeština [Czech])
//   da_DK (Dansk [Danish]; thanks Niels Bo Andersen)
//   fr_FR (Français [French]; thanks John Fischer)
//   de_DE (Deutsch [German]; thanks Marten Gajda and Thomas Scheel)
//   en_US (English [English/US])
//   it_IT (Italiano [Italian]; thanks Luca Ferrario)
//   hu_HU (Magyar [Hungarian])
//   sk_SK (Slovenčina [Slovak])
var globalInterfaceLanguage='en_US';

// if defined and not empty then only languages listed here are shown at the login screen (for example: ['en_US','fr_FR']),
//  otherwise (default) all languages are shown
//  values in the array must refer to an existing localization defined in the common.js (see the option above)
var globalInterfaceCustomLanguages=[];

// compatibility settings
//  anniversaryOutputFormat:
//  different clients use different (and incompatible) approach to store anniversary date in vCard
//   Apple stores this attribute as 'itemX.X-ABDATE;TYPE=pref:2000-01-01\r\nitemX.X-ABLabel:_$!<Anniversary>!$_\r\n'
//   other clients store this attribute as 'X-ANNIVERSARY:2000-01-01\r\n'
//  choose 'apple' or 'other' (lower case) for your 3rd party client compatibility (you can chose both: ['apple', 'other'] but it can cause many problems in the future, for example: duplicate anniversary dates, invalid/old anniversary date in your clients, and many others ...)
var globalCompatibility={anniversaryOutputFormat: ['apple']}

// JavaScript localeCompare() or custom alphabet for addressbook sorting
//  custom alphabet is used by default because the JavaScript localeCompare() not support collation and often returns "wrong" result
//var globalSortAlphabet=null;	// use localeCompare()
var globalSortAlphabet='0123456789AÀÁÂÄÆÃÅĀBCÇĆČDĎEÈÉÊËĒĖĘĚFGHIÌÍÎÏĪĮJKLŁĹĽMNŃÑŇOÒÓÔÖŐŒØÕŌPQRŔŘSŚŠẞTŤUÙÚÛÜŰŮŪVWXYÝŸZŹŻŽaàáâäæãåābcçćčdďeèéêëēėęěfghiìíîïīįjklłĺľmnńñňoòóôöőœøõōpqrŕřsśšßtťuùúûüűůūvwxyýÿzźżž';	// use custom alphabet sorting
// search functionality character equivalence (transformation to ASCII: key = regex text, value = result character)
var globalSearchTransformAlphabet={'[ÀàÁáÂâÄäÆæÃãÅåĀā]': 'a', '[ÇçĆćČč]': 'c', '[Ďď]': 'd', '[ÈèÉéÊêËëĒēĖėĘęĚě]': 'e', '[ÌìÍíÎîÏïĪīĮį]': 'i', '[ŁłĹĺĽľ]': 'l', '[ŃńÑñŇň]': 'n', '[ÒòÓóÔôÖöŐőŒœØøÕõŌō]': 'o', '[ŔŕŘř]': 'r', '[ŚśŠšẞß]': 's', '[Ťť]': 't', '[ÙùÚúÛûÜüŰűŮůŪū]': 'u', '[ÝýŸÿ]': 'y', '[ŹźŻżŽž]': 'z'};

// set the collection sorting, displaying and storing FN attribute into vCard - use a pair of values separated by a comma (spaces are allowed)
//  possible values:
//   last (equivalents: surname, lastname, family)
//   middle (equivalents: middlename)
//   first (equivalents: firstname, given)
//   prefix (no equivalents) -> unsupported in the editor, but if the contact includes a prefix, it is retained
//   suffix (no equivalents) -> unsupported in the editor, but if the contact includes a suffix, it is retained
var globalCollectionSort='last,middle,first';
var globalCollectionDisplay='last,middle,first';
var globalContactStoreFN='prefix,last,middle,first,suffix';	/* if undefined globalCollectionDisplay value is used */

// set the URI handlers for EMAIL, TEL and URL attributes (set to null or comment out to disable)
var globalUriHandlerTel='tel:';	// if 'tel' is not supported by system/browser, you can use 'callto' or 'skype'
var globalUriHandlerEmail='mailto:';
var globalUriHandlerUrl='http://';	// the value is used only if no URI handler is defined in the URL

// update notification will be shown only to users with login names defined in this array (for example: ['admin','peter'])
//  if undefined (or empty), update notifications will be shown to all users
var globalNewVersionNotifyUsers=[];

// set the datepicker format (see http://docs.jquery.com/UI/Datepicker/formatDate for valid values)
var globalDatepickerFormat='yy-mm-dd';

// default country for new address fields (must be defined in addressTypes variable - see common.js)
var globalDefaultAddressCountry='us';
// if there is no X-ABADR defined for the ADR attribute and the country name not matches any country name defined in the common.js the globalDefaultAddressCountry is used unless you define alternativne country names here
//  the country must refer to an existing country defined in the common.js and the regex is any regex string which matches the given country (note: regex match is case insensitive)
var globalAddressCountryEquivalence=[{country: 'de', regex: '^\\W*Deutschland\\W*$'}, {country: 'sk', regex: '^\\W*Slovensko\\W*$'}];
// countries listed here are shown at the top of the ADR country list (for example: ['de','sk'])
//  values in the array must refer to an existing country defined in the common.js
var globalAddressCountryFavorites=[];

// editor hide information message (success, error) after X miliseconds
var globalHideInfoMessageAfter=1800;

// editor fade in/out animation duration (contact editing, saving)
var globalEditorFadeAnimation=1000;

// show login names in resource header information?
var globalResourceHeaderShowLogin=true;

// enable keyboard navigation?
//  if undefined or not false, keyboard navigation is enabled
var globalEnableKbNavigation=true;

// asynchronously sync resources on background every X miliseconds (used for detection of collection changes in resources)
var globalSyncResourcesInterval=300000;
