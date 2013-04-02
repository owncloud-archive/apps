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

// possible address field positions [fid] (value = text input, ?country?= country select input)
//  0: [              value              ]
//  1: [            ?country?            ]
//  2: [              value              ]
//  3: [              value              ]
//  4: [              value              ]
//  5: [    value    ]  6: [    value    ]
//  7: [    value    ]  8: [  ?country?  ]
//  9: [              value              ]
// 10: [              value              ]
// 11: [            ?country?            ] <- here is the country defined by default
// 12: [              value              ]
//
// address field in vCard has the following format: pobox;extaddr;street;locality;region;code;country
// only these can be used as 'data-addr-field' values
var addressTypes=null;
function localizeAddressTypes()
{
	addressTypes={
		'af':	[	'Afghanistan',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'al':	[	'Albania',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'dz':	[	'Algeria',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'ad':	[	'Andorra',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'ao':	[	'Angola',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'ag':	[	'Antigua and Barbuda',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'ar':	[	'Argentina',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  9, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressProvince},
					{fid: 11, type: 'country'}
				],
		'am':	[	'Armenia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'au':	[	'Australia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressSuburb},
					{fid:  6, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressState},
					{fid:  7, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostal},
					{fid:  8, type: 'country'}
				],
		'at':	[	'Austria',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'az':	[	'Azerbaijan',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'bs':	[	'The Bahamas',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressIslandName},
					{fid: 11, type: 'country'}
				],
		'bh':	[	'Bahrain',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'bd':	[	'Bangladesh',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'bb':	[	'Barbados',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'by':	[	'Belarus',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  9, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressProvince},
					{fid: 11, type: 'country'}
				],
		'be':	[	'Belgium',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'bz':	[	'Belize',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  9, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressProvince},
					{fid: 11, type: 'country'}
				],
		'bj':	[	'Benin',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'bm':	[	'Bermuda',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'bt':	[	'Bhutan',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'bo':	[	'Bolivia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'ba':	[	'Bosnia and Herzegovina',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'bw':	[	'Botswana',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'br':	[	'Brazil',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressZip},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  7, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressProvince},
					{fid:  8, type: 'country'}
				],
		'bn':	[	'Brunei Darussalam',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'bg':	[	'Bulgaria',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'bf':	[	'Burkina Faso',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'bi':	[	'Burundi',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'kh':	[	'Cambodia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'cm':	[	'Cameroon',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'ca':	[	'Canada',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressProvince},
					{fid:  7, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  8, type: 'country'}
				],
		'cv':	[	'Cape Verde',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'ky':	[	'Cayman Islands',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressProvince},
					{fid: 11, type: 'country'}
				],
		'cf':	[	'Central African Republic',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'td':	[	'Chad',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'cl':	[	'Chile',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'cn':	[	'China',
					{fid:  1, type: 'country'},
					{fid:  5, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressProvince},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  9, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid: 10, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostal}
				],
		'co':	[	'Colombia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'km':	[	'Comoros',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'cd':	[	'Democratic Republic of the Congo',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'cg':	[	'Republic of the Congo',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'cr':	[	'Costa Rica',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'ci':	[	'Côte d’Ivoire',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'hr':	[	'Croatia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'cu':	[	'Cuba',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'cy':	[	'Cyprus',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'cz':	[	'Czech Republic',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'dk':	[	'Denmark',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'dj':	[	'Djibouti',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'dm':	[	'Dominica',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'do':	[	'Dominican Republic',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalDistrict},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'ec':	[	'Ecuador',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  9, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'eg':	[	'Egypt',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressDistrict},
					{fid:  9, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressGovernorate},
					{fid: 11, type: 'country'}
				],
		'sv':	[	'El Salvador',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  9, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressDepartment},
					{fid: 11, type: 'country'}
				],
		'gq':	[	'Equatorial Guinea',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'er':	[	'Eritrea',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'ee':	[	'Estonia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'et':	[	'Ethiopia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'fk':	[	'Falkland Islands',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  9, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'fo':	[	'Faroe Islands',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'fj':	[	'Fiji',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalDistrict},
					{fid:  9, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'fi':	[	'Finland',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'fr':	[	'France',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'pf':	[	'French Polynesia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  7, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressIslandName},
					{fid:  8, type: 'country'}
				],
		'ga':	[	'Gabon',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'gm':	[	'The Gambia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'ge':	[	'Georgia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'de':	[	'Germany',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'gh':	[	'Ghana',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'gr':	[	'Greece',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'gl':	[	'Greenland',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalDistrict},
					{fid: 11, type: 'country'}
				],
		'gd':	[	'Grenada',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'gp':	[	'Guadeloupe',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'gt':	[	'Guatemala',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'gn':	[	'Guinea',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'gw':	[	'Guinea-Bissau',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'gy':	[	'Guyana',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'ht':	[	'Haiti',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'hn':	[	'Honduras',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  7, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressDepartment},
					{fid:  8, type: 'country'}
				],
		'hk':	[	'Hong Kong',
					{fid:  1, type: 'country'},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressDistrict},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  9, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
				],
		'hu':	[	'Hungary',
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  9, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid: 11, type: 'country'}
				],
		'is':	[	'Iceland',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'in':	[	'India',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressProvince},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPinCode},
					{fid: 11, type: 'country'}
				],
		'id':	[	'Indonesia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  5, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressProvince},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'ir':	[	'Iran',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'iq':	[	'Iraq',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  9, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'ie':	[	'Ireland',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressCounty},
					{fid:  7, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  8, type: 'country'}
				],
		'im':	[	'Isle of Man',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  9, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'il':	[	'Israel',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'it':	[	'Italy',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  7, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressProvince},
					{fid:  8, type: 'country'}
				],
		'jm':	[	'Jamaica',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'jp':	[	'Japan',
					{fid:  2, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  5, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressPrefecture},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCountyCity},
					{fid:  9, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressFurtherDivisions},
					{fid: 11, type: 'country'}
				],
		'jo':	[	'Jordan',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'kz':	[	'Kazakhstan',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  4, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'ke':	[	'Kenya',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  4, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'ki':	[	'Kiribati',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  4, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressIslandName},
					{fid: 11, type: 'country'}
				],
		'kp':	[	'North Korea',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'kr':	[	'South Korea',
					{fid:  0, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  1, type: 'country'},
					{fid:  5, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressProvince},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  9, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet}
				],
		'kw':	[	'Kuwait',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  9, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressProvince},
					{fid: 11, type: 'country'}
				],
		'kg':	[	'Kyrgyzstan',
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  9, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid: 11, type: 'country'}
				],
		'la':	[	'Laos',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'lv':	[	'Latvia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'lb':	[	'Lebanon',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'ls':	[	'Lesotho',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'lr':	[	'Liberia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'ly':	[	'Libya',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'li':	[	'Liechtenstein',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'lt':	[	'Lithuania',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'lu':	[	'Luxembourg',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'mo':	[	'Macau',
					{fid:  1, type: 'country'},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressDistrict},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  9, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
				],
		'mk':	[	'Macedonia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'mg':	[	'Madagascar',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'mw':	[	'Malawi',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'my':	[	'Malaysia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  7, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressState},
					{fid:  8, type: 'country'}
				],
		'mv':	[	'Maldives',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'ml':	[	'Mali',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'mt':	[	'Malta',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  4, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'mh':	[	'Marshall Islands',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'mq':	[	'Martinique',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'mr':	[	'Mauritania',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'mu':	[	'Mauritius',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  4, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'mx':	[	'Mexico',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  7, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressState},
					{fid:  8, type: 'country'}
				],
		'fm':	[	'Micronesia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressState},
					{fid:  7, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressZip},
					{fid:  8, type: 'country'}
				],
		'md':	[	'Moldova',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'mc':	[	'Monaco',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'mn':	[	'Mongolia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'me':	[	'Montenegro',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'ma':	[	'Morocco',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'mz':	[	'Mozambique',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  9, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressProvince},
					{fid: 11, type: 'country'}
				],
		'mm':	[	'Myanmar',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'na':	[	'Namibia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'nr':	[	'Nauru',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressDistrict},
					{fid:  11, type: 'country'},
				],
		'np':	[	'Nepal',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'nl':	[	'Netherlands',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'nc':	[	'New Caledonia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'nz':	[	'New Zealand',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressSuburb},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostal},
					{fid: 11, type: 'country'}
				],
		'ni':	[	'Nicaragua',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressDepartment},
					{fid: 11, type: 'country'}
				],
		'ne':	[	'Niger',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'ng':	[	'Nigeria',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  9, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressState},
					{fid: 11, type: 'country'}
				],
		'no':	[	'Norway',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'om':	[	'Oman',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  4, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  9, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressProvince},
					{fid: 11, type: 'country'}
				],
		'pk':	[	'Pakistan',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'pw':	[	'Palau',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressState},
					{fid:  7, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressZip},
					{fid:  8, type: 'country'}
				],
		'ps':	[	'Palestinian Territories',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'pa':	[	'Panama',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  9, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressProvince},
					{fid: 11, type: 'country'}
				],
		'pg':	[	'Papua New Guinea',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  7, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressProvince},
					{fid:  8, type: 'country'}
				],
		'py':	[	'Paraguay',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'pe':	[	'Peru',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'ph':	[	'Philippines',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressDistrictSubdivision},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostCode},
					{fid:  7, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  8, type: 'country'}
				],
		'pl':	[	'Poland',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'pt':	[	'Portugal',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'pr':	[	'Puerto Rico',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressState},
					{fid:  7, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressZip},
					{fid:  8, type: 'country'}
				],
		'qa':	[	'Qatar',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		're':	[	'Réunion',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'ro':	[	'Romania',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'ru':	[	'Russia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCityRegion},
					{fid:  4, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressProvince},
					{fid: 11, type: 'country'},
					{fid: 12, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode}
				],
		'rw':	[	'Rwanda',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'bl':	[	'Saint Barthélemy',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'sh':	[	'Saint Helena',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  4, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'kn':	[	'Saint Kitts and Nevis',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  7, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressIslandName},
					{fid:  8, type: 'country'}
				],
		'lc':	[	'Saint Lucia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'mf':	[	'Saint Martin',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'vc':	[	'Saint Vincent and the Grenadines',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'ws':	[	'Samoa',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'sm':	[	'San Marino',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  7, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressProvince},
					{fid:  8, type: 'country'}
				],
		'st':	[	'Sao Tome and Principe',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'sa':	[	'Saudi Arabia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'sn':	[	'Senegal',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'rs':	[	'Serbia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'sc':	[	'Seychelles',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'sl':	[	'Sierra Leone',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'sg':	[	'Singapore',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'sk':	[	'Slovak Republic',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'si':	[	'Slovenia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'sb':	[	'Solomon Islands',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'so':	[	'Somalia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressRegion},
					{fid:  7, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  8, type: 'country'}
				],
		'za':	[	'South Africa',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  4, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressProvince},
					{fid:  9, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'gs':	[	'South Georgia and South Sandwich Islands',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  4, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'es':	[	'Spain',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  7, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressProvince},
					{fid:  8, type: 'country'}
				],
		'lk':	[	'Sri Lanka',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  4, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'sd':	[	'Sudan',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  4, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'sr':	[	'Suriname',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  4, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalDistrict},
					{fid: 11, type: 'country'}
				],
		'sz':	[	'Swaziland',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  4, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'se':	[	'Sweden',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'ch':	[	'Switzerland',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'sy':	[	'Syria',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'tw':	[	'Taiwan',
					{fid:  1, type: 'country'},
					{fid:  2, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressZip},
					{fid:  3, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressCountyCity},
					{fid:  4, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressTownshipDistrict},
					{fid:  9, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet}
				],
		'tj':	[	'Tajikistan',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'tz':	[	'Tanzania',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'th':	[	'Thailand',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressDistrictSubdivision},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressProvince},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostCode},
					{fid: 11, type: 'country'}
				],
		'tl':	[	'Timor-Leste',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'tg':	[	'Togo',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'to':	[	'Tonga',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'tt':	[	'Trinidad and Tobago',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'tn':	[	'Tunisia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'tr':	[	'Turkey',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressDistrict},
					{fid:  7, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  8, type: 'country'}
				],
		'tm':	[	'Turkmenistan',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'tv':	[	'Tuvalu',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'vi':	[	'U.S. Virgin Islands',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressState},
					{fid:  7, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressZip},
					{fid:  8, type: 'country'}
				],
		'ug':	[	'Uganda',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'ua':	[	'Ukraine',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  4, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'ae':	[	'United Arab Emirates',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'gb':	[	'United Kingdom',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  4, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressCounty},
					{fid:  9, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostCode},
					{fid: 11, type: 'country'}
				],
		'us':	[	'United States',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressState},
					{fid:  7, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressZip},
					{fid:  8, type: 'country'}
				],
		'uy':	[	'Uruguay',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  7, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressDepartment},
					{fid:  8, type: 'country'}
				],
		'uz':	[	'Uzbekistan',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'},
					{fid: 12, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode}
				],
		'vu':	[	'Vanuatu',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'va':	[	'Vatican',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		've':	[	'Venezuela',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  7, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressState},
					{fid:  8, type: 'country'}
				],
		'vn':	[	'Vietnam',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'region', placeholder: localization[globalInterfaceLanguage].pholderAddressProvince},
					{fid:  5, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid:  6, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid: 11, type: 'country'}
				],
		'ye':	[	'Yemen',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'zm':	[	'Zambia',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  5, type: 'input', 'data-addr-field': 'code', placeholder: localization[globalInterfaceLanguage].pholderAddressPostalCode},
					{fid:  6, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				],
		'zw':	[	'Zimbabwe',
					{fid:  2, type: 'input', 'data-addr-field': 'street', placeholder: localization[globalInterfaceLanguage].pholderAddressStreet},
					{fid:  3, type: 'input', 'data-addr-field': 'locality', placeholder: localization[globalInterfaceLanguage].pholderAddressCity},
					{fid: 11, type: 'country'}
				]
	}
}


// equivalent data types (multiply types and/or type combinations can represent the same thing)
// the vcard editor by default uses the "key" value as a type, but when this type is matched by
// by "value" regexp then the server specified type is used as default
dataTypes=new Object();
dataTypes['address_type']={
	'work':'^work$',
	'home':'^home$',
	'/_$!<other>!$_/':'^(?:/_\\$!<other>!\\$_/|other)$'
};

dataTypes['address_type_store_as']={
	'_$!<other>!$_':'_$!<Other>!$_'
}

dataTypes['phone_type']={
	'work':'^(?:voice_)?work$',
	'home':'^home(?:_voice)?$',
	'cell':'^cell(?:_voice)?$',
	'cell_work':'^cell(?:_voice)?_work$',
	'cell_home':'^cell_home(?:_voice)?$',
	'main':'^main(?:_voice)?$',
	'pager':'^pager$',
	'fax':'^fax$',
	'fax_work':'^fax_work$',
	'fax_home':'^fax_home$',
	'iphone':'^(?:/_\\$!<iphone>!\\$_/|(?:cell_)?iphone(?:_voice)?)$',
	'other':'^(?:/_\\$!<other>!\\$_/|other)(?:_voice)?$'
};

dataTypes['phone_type_store_as']={
	'_$!<iphone>!$_':'_$!<iPhone>!$_',
	'_$!<other>!$_':'_$!<Other>!$_'
}

dataTypes['email_type']={
	'internet_work':'^internet_work$',
	'home_internet':'^home_internet$',
	'/mobileme/_internet':'^(?:/mobileme/_internet|internet_mobileme)$',
	'/_$!<other>!$_/_internet':'^(?:/_\\$!<other>!\\$_/_internet|internet_other)$'
};

dataTypes['email_type_store_as']={
	'_$!<mobileme>!$_':'_$!<mobileMe>!$_',
	'_$!<other>!$_':'_$!<Other>!$_'
}

dataTypes['url_type']={
	'work':'^work$',
	'home':'^home$',
	'/_$!<homepage>!$_/':'^(?:/_\\$!<homepage>!\\$_/|homepage)$',
	'/_$!<other>!$_/':'^(?:/_\\$!<other>!\\$_/|other)$'
};

dataTypes['url_type_store_as']={
	'_$!<homepage>!$_':'_$!<HomePage>!$_',
	'_$!<other>!$_':'_$!<Other>!$_'
}

dataTypes['person_type']={
	'/_$!<father>!$_/':'^/_\\$!<father>!\\$_/$',
	'/_$!<mother>!$_/':'^/_\\$!<mother>!\\$_/$',
	'/_$!<parent>!$_/':'^/_\\$!<parent>!\\$_/$',
	'/_$!<brother>!$_/':'^/_\\$!<brother>!\\$_/$',
	'/_$!<sister>!$_/':'^/_\\$!<sister>!\\$_/$',
	'/_$!<child>!$_/':'^/_\\$!<child>!\\$_/$',
	'/_$!<friend>!$_/':'^/_\\$!<friend>!\\$_/$',
	'/_$!<spouse>!$_/':'^/_\\$!<spouse>!\\$_/$',
	'/_$!<partner>!$_/':'^/_\\$!<partner>!\\$_/$',
	'/_$!<assistant>!$_/':'^/_\\$!<assistant>!\\$_/$',
	'/_$!<manager>!$_/':'^/_\\$!<manager>!\\$_/$',
	'/_$!<other>!$_/':'^/_\\$!<other>!\\$_/$'
};

dataTypes['person_type_store_as']={
	'_$!<father>!$_':'_$!<Father>!$_',
	'_$!<mother>!$_':'_$!<Mother>!$_',
	'_$!<parent>!$_':'_$!<Parent>!$_',
	'_$!<brother>!$_':'_$!<Brother>!$_',
	'_$!<sister>!$_':'_$!<Sister>!$_',
	'_$!<child>!$_':'_$!<Child>!$_',
	'_$!<friend>!$_':'_$!<Friend>!$_',
	'_$!<spouse>!$_':'_$!<Spouse>!$_',
	'_$!<partner>!$_':'_$!<Partner>!$_',
	'_$!<assistant>!$_':'_$!<Assistant>!$_',
	'_$!<manager>!$_':'_$!<Manager>!$_',
	'_$!<other>!$_':'_$!<Other>!$_'
}

dataTypes['im_type']={
	'work':'^work$',
	'home':'^home$',
	'/mobileme/':'^(?:/mobileme/|mobileme)$',
	'/_$!<other>!$_/':'^(?:/_\\$!<other>!\\$_/|other)$'
};

dataTypes['im_type_store_as']={
	'_$!<mobileme>!$_':'_$!<mobileMe>!$_',
	'_$!<other>!$_':'_$!<Other>!$_'
}

dataTypes['im_service_type_store_as']={
	'aim':'AIM',
	'icq':'ICQ',
	'irc':'IRC',
	'jabber':'Jabber',
	'msn':'MSN',
	'yahoo':'Yahoo',
	'facebook':'Facebook',
	'gadugadu':'GaduGadu',
	'googletalk':'GoogleTalk',
	'qq':'QQ',
	'skype':'Skype'
}

// Used to match XML element names with any namespace
jQuery.fn.filterNsNode = function(name)
{
	return this.filter(
		function()
		{
			return (this.nodeName === name || this.nodeName.replace(RegExp('^[^:]+:',''),'') === name);
		}
	);
};

// Escape jQuery selector
function jqueryEscapeSelector(inputValue)
{
	return (inputValue==undefined ? '' : inputValue).toString().replace(/([ !"#$%&'()*+,./:;<=>?@[\\\]^`{|}~])/g,'\\$1');
}

// Escape vCard value - RFC2426 (Section 2.4.2)
function vcardEscapeValue(inputValue)
{
	return (inputValue==undefined ? '' : inputValue).replace(/(,|;|\\)/g,"\\$1").replace(/\n/g,'\\n');
}

// Unescape vCard value - RFC2426 (Section 2.4.2)
function vcardUnescapeValue(inputValue)
{
	var outputValue='';
	if(inputValue!=undefined)
	{
		for(var i=0;i<inputValue.length;i++)
			if(inputValue[i]=='\\' && i+1<inputValue.length)
			{
				if(inputValue[++i]=='n')
					outputValue+='\n';
				else
					outputValue+=inputValue[i];
			}
			else
				outputValue+=inputValue[i];
	}
	return outputValue;
}

// Split parameters and remove double quotes from values (if parameter values are quoted)
function vcardSplitParam(inputValue)
{
	var result=vcardSplitValue(inputValue, ';');
	var index;

	for(var i=0;i<result.length;i++)
	{
		index=result[i].indexOf('=');
		if(index!=-1 && index+1<result[i].length && result[i][index+1]=='"' && result[i][result[i].length-1]=='"')
			result[i]=result[i].substring(0,index+1)+result[i].substring(index+2,result[i].length-1);
	}

	return result;
}

// Split string by separator (but not '\' escaped separator)
function vcardSplitValue(inputValue, inputDelimiter)
{
	var outputArray=new Array(),
	i=0,j=0;

	for(i=0;i<inputValue.length;i++)
	{
		if(inputValue[i]==inputDelimiter)
		{
			if(outputArray[j]==undefined)
				outputArray[j]='';
			++j;
			continue;
		}
		outputArray[j]=(outputArray[j]==undefined ? '' : outputArray[j]) + inputValue[i];

		if(inputValue[i]=='\\' && i+1<inputValue.length)
			outputArray[j]=outputArray[j] + inputValue[++i];
	}
	return outputArray;
}

// Generate random string (UID)
function generateUID()
{
	uidChars='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	UID='';
	for(i=0;i<32;i++)
	{
		if(i==8 || i==12 || i==16 || i==20) UID+='-';
		UID+=uidChars.charAt(Math.floor(Math.random()*(uidChars.length-1)));
	}
	return UID+'-CardDavMATE';
}


// IE compatibility (note: preparation for future IE compatibility - IE10?)
if (typeof window.btoa=='undefined' && typeof base64.encode!='undefined') window.btoa=base64.encode;

// Create Basic auth string (for HTTP header)
function basicAuth(user, password) {
	var tok = user + ':' + password;
	var hash = btoa(tok);
	return "Basic " + hash;
}

// multiply regex replace {'regex': value, 'regex': value}
String.prototype.multiReplace = function (hash)
{
	var str = this, key;
	for(key in hash)
		str=str.replace(new RegExp(key,'g'),hash[key]);
	return str;
};

// Used for sorting the contact and resource list ...
String.prototype.customCompare = function(stringB, alphabet, dir, caseSensitive)
{
	var stringA=this;

	if(alphabet==undefined || alphabet==null)
		return stringA.localeCompare(stringB);
	else
	{
		var pos = 0,
		min = Math.min(stringA.length, stringB.length);
		dir = dir || 1;
		caseSensitive = caseSensitive || false;
		if(!caseSensitive)
		{
			stringA = stringA.toLowerCase();
			stringB = stringB.toLowerCase();
		}
		while(stringA.charAt(pos) === stringB.charAt(pos) && pos < min){ pos++; }
		return alphabet.indexOf(stringA.charAt(pos)) > alphabet.indexOf(stringB.charAt(pos)) ? dir : -dir;
	}
}

// Get unique values from array
Array.prototype.unique = function() {
	var o = {}, i, l = this.length, r = [];
	for(i=0; i<l;i++)
		o[this[i]] = this[i];
	for(i in o)
		r.push(o[i]);
	return r;
};

// Recursive replaceAll
String.prototype.replaceAll = function(stringToFind,stringToReplace)
{
	var temp = this;
	while(temp.indexOf(stringToFind) != -1)
		temp = temp.replace(stringToFind,stringToReplace);
	return temp;
}

// Case insensitive search for attributes
// Usage: 	$('[id=vcard_editor]').find(':attrCaseInsensitive(data-type,"'+typeList[i]+'")')
jQuery.expr[':'].attrCaseInsensitive = function(elem, index, match) {
    var matchParams = match[3].split(','),
		attribute = matchParams[0].replace(/^\s*|\s*$/g,''),
		value = matchParams[1].replace(/^\s*"|"\s*$/g,'').toLowerCase();
	return jQuery(elem)['attr'](attribute)!=undefined && jQuery(elem)['attr'](attribute)==value;
}
