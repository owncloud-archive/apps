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

function cleanupRegexEnvironment()
{
	for (var element in vCard.tplM)
		if(element=='unprocessed_unrelated')
			vCard.tplM[element]='';
		else
			vCard.tplM[element]=new Array();
}

var vCard = new Object();
// RFC compiant templates (clean)
vCard.tplC = new Object();
// RFC compiant templates (modified -> if the editor does not support some of the attribute or value, we keep these intact)
vCard.tplM = new Object();

// subset of RFC 2234 (Augmented BNF for Syntax Specifications) used in RFC 2426 (vCard MIME Directory Profile)
vCard.re = new Object();
vCard.pre = new Object();
//vCard.re['ALPHA']='[\u0041-\u005a\u0061-\u007a]';	// ASCII Alphabetic characters
vCard.re['ALPHA']='[\u0041-\u005a\u0061-\u007a\u00aa\u00b5\u00ba\u00c0-\u00d6\u00d8-\u00f6\u00f8-\u02c1\u02c6-\u02d1\u02e0-\u02e4\u02ec\u02ee\u0370-\u0374\u0376\u037a-\u037d\u0386\u0388-\u038a\u038c\u038e-\u03a1\u03a3-\u03f5\u03f7-\u0481\u048a-\u0523\u0531-\u0556\u0559\u0561-\u0587\u05d0-\u05ea\u05f0-\u05f2\u0621-\u064a\u066e\u0671-\u06d3\u06d5\u06e5\u06ee-\u06ef\u06fa-\u06fc\u06ff\u0710\u0712-\u072f\u074d-\u07a5\u07b1\u07ca-\u07ea\u07f4\u07fa\u0904-\u0939\u093d\u0950\u0958-\u0961\u0971\u097b-\u097f\u0985-\u098c\u098f\u0993-\u09a8\u09aa-\u09b0\u09b2\u09b6-\u09b9\u09bd\u09ce\u09dc\u09df-\u09e1\u09f0\u0a05-\u0a0a\u0a0f\u0a13-\u0a28\u0a2a-\u0a30\u0a32\u0a35-\u0a36\u0a38\u0a59-\u0a5c\u0a5e\u0a72-\u0a74\u0a85-\u0a8d\u0a8f-\u0a91\u0a93-\u0aa8\u0aaa-\u0ab0\u0ab2\u0ab5-\u0ab9\u0abd\u0ad0\u0ae0\u0b05-\u0b0c\u0b0f\u0b13-\u0b28\u0b2a-\u0b30\u0b32\u0b35-\u0b39\u0b3d\u0b5c\u0b5f-\u0b61\u0b71\u0b83\u0b85-\u0b8a\u0b8e-\u0b90\u0b92-\u0b95\u0b99\u0b9c\u0b9e-\u0b9f\u0ba3\u0ba8-\u0baa\u0bae-\u0bb9\u0bd0\u0c05-\u0c0c\u0c0e-\u0c10\u0c12-\u0c28\u0c2a-\u0c33\u0c35-\u0c39\u0c3d\u0c58\u0c60-\u0c61\u0c85-\u0c8c\u0c8e-\u0c90\u0c92-\u0ca8\u0caa-\u0cb3\u0cb5-\u0cb9\u0cbd\u0cde\u0ce0\u0d05-\u0d0c\u0d0e-\u0d10\u0d12-\u0d28\u0d2a-\u0d39\u0d3d\u0d60\u0d7a-\u0d7f\u0d85-\u0d96\u0d9a-\u0db1\u0db3-\u0dbb\u0dbd\u0dc0-\u0dc6\u0e01-\u0e30\u0e32\u0e40-\u0e46\u0e81\u0e84\u0e87-\u0e88\u0e8a\u0e8d\u0e94-\u0e97\u0e99-\u0e9f\u0ea1-\u0ea3\u0ea5\u0ea7\u0eaa\u0ead-\u0eb0\u0eb2\u0ebd\u0ec0-\u0ec4\u0ec6\u0edc\u0f00\u0f40-\u0f47\u0f49-\u0f6c\u0f88-\u0f8b\u1000-\u102a\u103f\u1050-\u1055\u105a-\u105d\u1061\u1065\u106e-\u1070\u1075-\u1081\u108e\u10a0-\u10c5\u10d0-\u10fa\u10fc\u1100-\u1159\u115f-\u11a2\u11a8-\u11f9\u1200-\u1248\u124a-\u124d\u1250-\u1256\u1258\u125a-\u125d\u1260-\u1288\u128a-\u128d\u1290-\u12b0\u12b2-\u12b5\u12b8-\u12be\u12c0\u12c2-\u12c5\u12c8-\u12d6\u12d8-\u1310\u1312-\u1315\u1318-\u135a\u1380-\u138f\u13a0-\u13f4\u1401-\u166c\u166f-\u1676\u1681-\u169a\u16a0-\u16ea\u16ee-\u16f0\u1700-\u170c\u170e-\u1711\u1720-\u1731\u1740-\u1751\u1760-\u176c\u176e-\u1770\u1780-\u17b3\u17d7\u17dc\u1820-\u1877\u1880-\u18a8\u18aa\u1900-\u191c\u1950-\u196d\u1970-\u1974\u1980-\u19a9\u19c1-\u19c7\u1a00-\u1a16\u1b05-\u1b33\u1b45-\u1b4b\u1b83-\u1ba0\u1bae\u1c00-\u1c23\u1c4d-\u1c4f\u1c5a-\u1c7d\u1d00-\u1dbf\u1e00-\u1f15\u1f18-\u1f1d\u1f20-\u1f45\u1f48-\u1f4d\u1f50-\u1f57\u1f59\u1f5b\u1f5d\u1f5f-\u1f7d\u1f80-\u1fb4\u1fb6-\u1fbc\u1fbe\u1fc2-\u1fc4\u1fc6-\u1fcc\u1fd0-\u1fd3\u1fd6-\u1fdb\u1fe0-\u1fec\u1ff2-\u1ff4\u1ff6-\u1ffc\u2071\u207f\u2090-\u2094\u2102\u2107\u210a-\u2113\u2115\u2119-\u211d\u2124\u2126\u2128\u212a-\u212d\u212f-\u2139\u213c-\u213f\u2145-\u2149\u214e\u2160-\u2188\u2c00-\u2c2e\u2c30-\u2c5e\u2c60-\u2c6f\u2c71-\u2c7d\u2c80-\u2ce4\u2d00-\u2d25\u2d30-\u2d65\u2d6f\u2d80-\u2d96\u2da0-\u2da6\u2da8-\u2dae\u2db0-\u2db6\u2db8-\u2dbe\u2dc0-\u2dc6\u2dc8-\u2dce\u2dd0-\u2dd6\u2dd8-\u2dde\u2e2f\u3005-\u3007\u3021-\u3029\u3031-\u3035\u3038-\u303c\u3041-\u3096\u309d-\u309f\u30a1-\u30fa\u30fc-\u30ff\u3105-\u312d\u3131-\u318e\u31a0-\u31b7\u31f0-\u31ff\u3400\u4db5\u4e00\u9fc3\ua000-\ua48c\ua500-\ua60c\ua610-\ua61f\ua62a\ua640-\ua65f\ua662-\ua66e\ua67f-\ua697\ua717-\ua71f\ua722-\ua788\ua78b\ua7fb-\ua801\ua803-\ua805\ua807-\ua80a\ua80c-\ua822\ua840-\ua873\ua882-\ua8b3\ua90a-\ua925\ua930-\ua946\uaa00-\uaa28\uaa40-\uaa42\uaa44-\uaa4b\uac00\ud7a3\uf900-\ufa2d\ufa30-\ufa6a\ufa70-\ufad9\ufb00-\ufb06\ufb13-\ufb17\ufb1d\ufb1f-\ufb28\ufb2a-\ufb36\ufb38-\ufb3c\ufb3e\ufb40\ufb43-\ufb44\ufb46-\ufbb1\ufbd3-\ufd3d\ufd50-\ufd8f\ufd92-\ufdc7\ufdf0-\ufdfb\ufe70-\ufe74\ufe76-\ufefc\uff21-\uff3a\uff41-\uff5a\uff66-\uffbe\uffc2-\uffc7\uffca-\uffcf\uffd2-\uffd7\uffda-\uffdc\u0345\u05b0-\u05bd\u05bf\u05c1-\u05c2\u05c4-\u05c5\u05c7\u0610-\u061a\u064b-\u0657\u0659-\u065e\u0670\u06d6-\u06dc\u06e1-\u06e4\u06e7-\u06e8\u06ed\u0711\u0730-\u073f\u07a6-\u07b0\u0901-\u0902\u0903\u093e-\u0940\u0941-\u0948\u0949-\u094c\u0962-\u0963\u0981\u0982-\u0983\u09be-\u09c0\u09c1-\u09c4\u09c7-\u09c8\u09cb-\u09cc\u09d7\u09e2-\u09e3\u0a01-\u0a02\u0a03\u0a3e-\u0a40\u0a41-\u0a42\u0a47-\u0a48\u0a4b-\u0a4c\u0a51\u0a70-\u0a71\u0a75\u0a81-\u0a82\u0a83\u0abe-\u0ac0\u0ac1-\u0ac5\u0ac7-\u0ac8\u0ac9\u0acb-\u0acc\u0ae2-\u0ae3\u0b01\u0b02-\u0b03\u0b3e\u0b3f\u0b40\u0b41-\u0b44\u0b47-\u0b48\u0b4b-\u0b4c\u0b56\u0b57\u0b62-\u0b63\u0b82\u0bbe-\u0bbf\u0bc0\u0bc1-\u0bc2\u0bc6-\u0bc8\u0bca-\u0bcc\u0bd7\u0c01-\u0c03\u0c3e-\u0c40\u0c41-\u0c44\u0c46-\u0c48\u0c4a-\u0c4c\u0c55-\u0c56\u0c62-\u0c63\u0c82-\u0c83\u0cbe\u0cbf\u0cc0-\u0cc4\u0cc6\u0cc7-\u0cc8\u0cca-\u0ccb\u0ccc\u0cd5-\u0cd6\u0ce2-\u0ce3\u0d02-\u0d03\u0d3e-\u0d40\u0d41-\u0d44\u0d46-\u0d48\u0d4a-\u0d4c\u0d57\u0d62-\u0d63\u0d82-\u0d83\u0dcf-\u0dd1\u0dd2-\u0dd4\u0dd6\u0dd8-\u0ddf\u0df2-\u0df3\u0e31\u0e34-\u0e3a\u0e4d\u0eb1\u0eb4-\u0eb9\u0ebb-\u0ebc\u0ecd\u0f71-\u0f7e\u0f7f\u0f80-\u0f81\u0f90-\u0f97\u0f99-\u0fbc\u102b-\u102c\u102d-\u1030\u1031\u1032-\u1036\u1038\u103b-\u103c\u103d-\u103e\u1056-\u1057\u1058-\u1059\u105e-\u1060\u1062\u1067-\u1068\u1071-\u1074\u1082\u1083-\u1084\u1085-\u1086\u135f\u1712-\u1713\u1732-\u1733\u1752-\u1753\u1772-\u1773\u17b6\u17b7-\u17bd\u17be-\u17c5\u17c6\u17c7-\u17c8\u18a9\u1920-\u1922\u1923-\u1926\u1927-\u1928\u1929-\u192b\u1930-\u1931\u1932\u1933-\u1938\u19b0-\u19c0\u19c8-\u19c9\u1a17-\u1a18\u1a19-\u1a1b\u1b00-\u1b03\u1b04\u1b35\u1b36-\u1b3a\u1b3b\u1b3c\u1b3d-\u1b41\u1b42\u1b43\u1b80-\u1b81\u1b82\u1ba1\u1ba2-\u1ba5\u1ba6-\u1ba7\u1ba8-\u1ba9\u1c24-\u1c2b\u1c2c-\u1c33\u1c34-\u1c35\u24b6-\u24e9\u2de0-\u2dff\ua823-\ua824\ua825-\ua826\ua827\ua880-\ua881\ua8b4-\ua8c3\ua926-\ua92a\ua947-\ua951\ua952\uaa29-\uaa2e\uaa2f-\uaa30\uaa31-\uaa32\uaa33-\uaa34\uaa35-\uaa36\uaa43\uaa4c\uaa4d\ufb1e]';	// UTF-8 Alphabetic characters
vCard.re['CR']='\u000d';
vCard.re['LF']='\u000a';
vCard.re['CRLF']='(?:'+vCard.re['CR']+vCard.re['LF']+')';
vCard.re['DIGIT']='[\u0030-\u0039]';
vCard.re['DQUOTE']='\u0022';
vCard.re['HTAB']='\u0009';
vCard.re['SP']='\u0020';
vCard.re['WSP']='(?:'+vCard.re['SP']+'|'+vCard.re['HTAB']+')';
//vCard.re['VCHAR']='[\u0021-\u007e]';	// ASCII Visible characters
//vCard.re['NON-ASCII']='[\u0080-\u00ff]';		// NON-ASCII
vCard.re['VCHAR']='[\u0021-\u007e\u00a0-\u00ac\u00ae-\u0377\u037a-\u037e\u0384-\u038a\u038c\u038e-\u03a1\u03a3-\u0523\u0531-\u0556\u0559-\u055f\u0561-\u0587\u0589\u0591-\u05c7\u05d0-\u05ea\u05f0-\u05f4\u0606-\u061b\u061e\u0621-\u065e\u0660-\u06dc\u06de-\u070d\u0710-\u074a\u074d-\u07b1\u07c0-\u07fa\u0901-\u0939\u093c-\u094d\u0950-\u0954\u0958-\u0972\u097b-\u097f\u0981-\u0983\u0985-\u098c\u098f\u0993-\u09a8\u09aa-\u09b0\u09b2\u09b6-\u09b9\u09bc-\u09c4\u09c7\u09cb-\u09ce\u09d7\u09dc\u09df-\u09e3\u09e6-\u09fa\u0a01-\u0a03\u0a05-\u0a0a\u0a0f\u0a13-\u0a28\u0a2a-\u0a30\u0a32\u0a35-\u0a36\u0a38\u0a3c\u0a3e-\u0a42\u0a47\u0a4b-\u0a4d\u0a51\u0a59-\u0a5c\u0a5e\u0a66-\u0a75\u0a81-\u0a83\u0a85-\u0a8d\u0a8f-\u0a91\u0a93-\u0aa8\u0aaa-\u0ab0\u0ab2\u0ab5-\u0ab9\u0abc-\u0ac5\u0ac7-\u0ac9\u0acb-\u0acd\u0ad0\u0ae0-\u0ae3\u0ae6-\u0aef\u0af1\u0b01-\u0b03\u0b05-\u0b0c\u0b0f\u0b13-\u0b28\u0b2a-\u0b30\u0b32\u0b35-\u0b39\u0b3c-\u0b44\u0b47\u0b4b-\u0b4d\u0b56\u0b5c-\u0b5d\u0b5f-\u0b63\u0b66-\u0b71\u0b82\u0b85-\u0b8a\u0b8e-\u0b90\u0b92-\u0b95\u0b99\u0b9c\u0b9e-\u0b9f\u0ba3\u0ba8-\u0baa\u0bae-\u0bb9\u0bbe-\u0bc2\u0bc6-\u0bc8\u0bca-\u0bcd\u0bd0\u0bd7\u0be6-\u0bfa\u0c01-\u0c03\u0c05-\u0c0c\u0c0e-\u0c10\u0c12-\u0c28\u0c2a-\u0c33\u0c35-\u0c39\u0c3d-\u0c44\u0c46-\u0c48\u0c4a-\u0c4d\u0c55\u0c58-\u0c59\u0c60-\u0c63\u0c66-\u0c6f\u0c78-\u0c7f\u0c82\u0c85-\u0c8c\u0c8e-\u0c90\u0c92-\u0ca8\u0caa-\u0cb3\u0cb5-\u0cb9\u0cbc-\u0cc4\u0cc6-\u0cc8\u0cca-\u0ccd\u0cd5\u0cde\u0ce0-\u0ce3\u0ce6-\u0cef\u0cf1\u0d02-\u0d03\u0d05-\u0d0c\u0d0e-\u0d10\u0d12-\u0d28\u0d2a-\u0d39\u0d3d-\u0d44\u0d46-\u0d48\u0d4a-\u0d4d\u0d57\u0d60-\u0d63\u0d66-\u0d75\u0d79-\u0d7f\u0d82\u0d85-\u0d96\u0d9a-\u0db1\u0db3-\u0dbb\u0dbd\u0dc0-\u0dc6\u0dca\u0dcf-\u0dd4\u0dd6\u0dd8-\u0ddf\u0df2-\u0df4\u0e01-\u0e3a\u0e3f-\u0e5b\u0e81\u0e84\u0e87-\u0e88\u0e8a\u0e8d\u0e94-\u0e97\u0e99-\u0e9f\u0ea1-\u0ea3\u0ea5\u0ea7\u0eaa\u0ead-\u0eb9\u0ebb-\u0ebd\u0ec0-\u0ec4\u0ec6\u0ec8-\u0ecd\u0ed0-\u0ed9\u0edc\u0f00-\u0f47\u0f49-\u0f6c\u0f71-\u0f8b\u0f90-\u0f97\u0f99-\u0fbc\u0fbe-\u0fcc\u0fce-\u0fd4\u1000-\u1099\u109e-\u10c5\u10d0-\u10fc\u1100-\u1159\u115f-\u11a2\u11a8-\u11f9\u1200-\u1248\u124a-\u124d\u1250-\u1256\u1258\u125a-\u125d\u1260-\u1288\u128a-\u128d\u1290-\u12b0\u12b2-\u12b5\u12b8-\u12be\u12c0\u12c2-\u12c5\u12c8-\u12d6\u12d8-\u1310\u1312-\u1315\u1318-\u135a\u135f-\u137c\u1380-\u1399\u13a0-\u13f4\u1401-\u1676\u1680-\u169c\u16a0-\u16f0\u1700-\u170c\u170e-\u1714\u1720-\u1736\u1740-\u1753\u1760-\u176c\u176e-\u1770\u1772\u1780-\u17b3\u17b6-\u17dd\u17e0-\u17e9\u17f0-\u17f9\u1800-\u180e\u1810-\u1819\u1820-\u1877\u1880-\u18aa\u1900-\u191c\u1920-\u192b\u1930-\u193b\u1940\u1944-\u196d\u1970-\u1974\u1980-\u19a9\u19b0-\u19c9\u19d0-\u19d9\u19de-\u1a1b\u1a1e\u1b00-\u1b4b\u1b50-\u1b7c\u1b80-\u1baa\u1bae-\u1bb9\u1c00-\u1c37\u1c3b-\u1c49\u1c4d-\u1c7f\u1d00-\u1de6\u1dfe-\u1f15\u1f18-\u1f1d\u1f20-\u1f45\u1f48-\u1f4d\u1f50-\u1f57\u1f59\u1f5b\u1f5d\u1f5f-\u1f7d\u1f80-\u1fb4\u1fb6-\u1fc4\u1fc6-\u1fd3\u1fd6-\u1fdb\u1fdd-\u1fef\u1ff2-\u1ff4\u1ff6-\u1ffe\u2000-\u200a\u2010-\u2027\u202f-\u205f\u2070\u2074-\u208e\u2090-\u2094\u20a0-\u20b5\u20d0-\u20f0\u2100-\u214f\u2153-\u2188\u2190-\u23e7\u2400-\u2426\u2440-\u244a\u2460-\u269d\u26a0-\u26bc\u26c0-\u26c3\u2701-\u2704\u2706-\u2709\u270c-\u2727\u2729-\u274b\u274d\u274f-\u2752\u2756\u2758-\u275e\u2761-\u2794\u2798-\u27af\u27b1-\u27be\u27c0-\u27ca\u27cc\u27d0-\u2b4c\u2b50-\u2b54\u2c00-\u2c2e\u2c30-\u2c5e\u2c60-\u2c6f\u2c71-\u2c7d\u2c80-\u2cea\u2cf9-\u2d25\u2d30-\u2d65\u2d6f\u2d80-\u2d96\u2da0-\u2da6\u2da8-\u2dae\u2db0-\u2db6\u2db8-\u2dbe\u2dc0-\u2dc6\u2dc8-\u2dce\u2dd0-\u2dd6\u2dd8-\u2dde\u2de0-\u2e30\u2e80-\u2e99\u2e9b-\u2ef3\u2f00-\u2fd5\u2ff0-\u2ffb\u3000-\u303f\u3041-\u3096\u3099-\u30ff\u3105-\u312d\u3131-\u318e\u3190-\u31b7\u31c0-\u31e3\u31f0-\u321e\u3220-\u3243\u3250-\u32fe\u3300-\u3400\u4db5\u4dc0-\u4e00\u9fc3\ua000-\ua48c\ua490-\ua4c6\ua500-\ua62b\ua640-\ua65f\ua662-\ua673\ua67c-\ua697\ua700-\ua78c\ua7fb-\ua82b\ua840-\ua877\ua880-\ua8c4\ua8ce-\ua8d9\ua900-\ua953\ua95f\uaa00-\uaa36\uaa40-\uaa4d\uaa50-\uaa59\uaa5c-\uaa5f\uac00\ud7a3\ue000\uf8ff-\ufa2d\ufa30-\ufa6a\ufa70-\ufad9\ufb00-\ufb06\ufb13-\ufb17\ufb1d-\ufb36\ufb38-\ufb3c\ufb3e\ufb40\ufb43-\ufb44\ufb46-\ufbb1\ufbd3-\ufd3f\ufd50-\ufd8f\ufd92-\ufdc7\ufdf0-\ufdfd\ufe00-\ufe19\ufe20-\ufe26\ufe30-\ufe52\ufe54-\ufe66\ufe68-\ufe6b\ufe70-\ufe74\ufe76-\ufefc\uff01-\uffbe\uffc2-\uffc7\uffca-\uffcf\uffd2-\uffd7\uffda-\uffdc\uffe0-\uffe6\uffe8-\uffee\ufffc\ufffd]';	// UTF-8 Visible characters (Print characters except \u0020 - space)
vCard.re['NON-ASCII']='[\u0080-\uffff]';	// UTF-8 NON-ASCII
vCard.re['QSAFE-CHAR']='(?:'+vCard.re['WSP']+'|[\u0021\u0023-\u007e]|'+vCard.re['NON-ASCII']+')';
vCard.re['SAFE-CHAR']='(?:'+vCard.re['WSP']+'|[\u0021\u0023-\u002b\u002d-\u0039\u003c-\u007e]|'+vCard.re['NON-ASCII']+')';
vCard.re['VALUE-CHAR']='(?:'+vCard.re['WSP']+'|'+vCard.re['VCHAR']+'|'+vCard.re['NON-ASCII']+')';
vCard.re['ESCAPED-CHAR']='(?:(?:\\\\)|(?:\\\\;)|(?:\\\\,)|(?:\\\\[nN]))';

// vCard Definition (general)
vCard.re['group']='(?:'+vCard.re['ALPHA']+'|'+vCard.re['DIGIT']+'|-)+';
vCard.re['iana-token']='(?:'+vCard.re['ALPHA']+'|'+vCard.re['DIGIT']+'|-)+';
vCard.re['x-name']='X-(?:'+vCard.re['ALPHA']+'|'+vCard.re['DIGIT']+'|-)+';
vCard.re['name']='(?:'+vCard.re['iana-token']+'|'+vCard.re['x-name']+')';
vCard.re['ptext']='(?:'+vCard.re['SAFE-CHAR']+')*';
vCard.re['quoted-string']='(?:'+vCard.re['DQUOTE']+vCard.re['QSAFE-CHAR']+'*'+vCard.re['DQUOTE']+')';	// BUG in RFC? -> it defines quoted char instead quoted string
vCard.re['param-value']='(?:'+vCard.re['ptext']+'|'+vCard.re['quoted-string']+')';
vCard.re['param-name']='(?:'+vCard.re['iana-token']+'|'+vCard.re['x-name']+')';
vCard.re['param']='(?:'+vCard.re['param-name']+'='+vCard.re['param-value']+'(?:,'+vCard.re['param-value']+')*)';
vCard.re['value']='(?:'+vCard.re['VALUE-CHAR']+')*';
vCard.re['contentline']='(?:'+vCard.re['group']+'\\.)?'+vCard.re['name']+'(?:;'+vCard.re['param']+')*:'+vCard.re['value']+vCard.re['CRLF'];
// contentline_parse = [1]->"group.", [2]->"name", [3]->";param;param", [4]->"value"
vCard.re['contentline_parse']='((?:'+vCard.re['group']+'\\.)?)('+vCard.re['name']+')((?:;'+vCard.re['param']+')*):('+vCard.re['value']+')'+vCard.re['CRLF'];
vCard.pre['contentline_parse']=RegExp('\r\n'+vCard.re['contentline_parse'],'mi');
vCard.re['vcard']='(?:(?:'+vCard.re['group']+'\\.)?BEGIN:VCARD'+vCard.re['CRLF']+'(?:'+vCard.re['contentline']+')+'+'(?:'+vCard.re['group']+'\\.)?END:VCARD'+vCard.re['CRLF']+')';
vCard.pre['vcard']=RegExp(vCard.re['vcard']);
vCard.re['vcard-entity']='(?:'+vCard.re['vcard']+')+';

// vCard Definition (full RFC specification, internal revision 1.0)
vCard.re['langval']='(?:aa|aar|ab|abk|ace|ach|ada|af|afa|afh|afr|ajm|aka|akk|alb/sqi|ale|alg|am|amh|ang|apa|ar|ara|arc|arm/hye|arn|arp|art|arw|as|asm|ath|ava|ave|awa|ay|aym|az|aze|ba|bad|bai|bak|bal|bam|ban|baq/eus|bas|bat|be|bej|bel|bem|ben|ber|bg|bh|bho|bi|bih|bik|bin|bis|bla|bn|bo|bod/tib|br|bra|bre|bug|bul|bur/mya|ca|cad|cai|car|cat|cau|ceb|cel|ces/cze|cha|chb|che|chg|chi/zho|chn|cho|chr|chu|chv|chy|co|cop|cor|cos|cpe|cpf|cpp|cre|crp|cs|cus|cy|cym/wel|cze/ces|da|dak|dan|de|del|deu/ger|din|doi|dra|dua|dum|dut/nld|dyu|dz|dzo|efi|egy|eka|el|ell/gre|elx|en|en-cokney|eng|enm|eo|epo|es|esk|esl/spa|est|et|eth|eu|eus/baq|ewe|ewo|fa|fan|fao|fas/per|fat|fi|fij|fin|fiu|fj|fo|fon|fr|fra/fre|fre/fra|frm|fro|fry|ful|fy|ga|gaa|gae/gdh|gai/iri|gay|gd|gdh/gae|gem|geo/kat|ger/deu|gil|gl|glg|gmh|gn|goh|gon|got|grb|grc|gre/ell|grn|gu|guj|ha|hai|hau|haw|he|heb|her|hi|hil|him|hin|hmo|hr|hu|hun|hup|hy|hye/arm|i-sami-no|ia|iba|ibo|ice/isl|id|ie|ijo|ik|iku|ile|ilo|in|ina|inc|ind|ine|ipk|ira|iri/gai|iro|is|isl/ice||it|ita|iu|iw|ja|jav/jaw|jaw/jav|ji|jpn|jpr|jrb|jw|ka|kaa|kab|kac|kal|kam|kan|kar|kas|kat/geo|kau|kaw|kaz|kha|khi|khm|kho|kik|kin|kir|kk|kl|km|kn|ko|kok|kon|kor|kpe|kro|kru|ks|ku|kua|kur|kus|kut|ky|la|lad|lah|lam|lao|lap|lat|lav|lin|lit|ln|lo|lol|loz|lt|lub|lug|lui|lun|luo|lv|mac/mke|mad|mag|mah|mai|mak|mal|man|mao/mri|map|mar|mas|max|may/msa|men|mg|mi|mic|min|mis|mk|mke/mac|mkh|ml|mlg|mlt|mn|mni|mno|mo|moh|mol|mon|mos|mr|mri/mao|ms|msa/may|mt|mul|mun|mus|mwr|my|mya/bur|myn|na|nah|nai|nau|nav|nde|ndo|ne|nep|new|nic|niu|nl|nld/dut|no|no-bok|no-nyn|non|nor|nso|nub|nya|nym|nyn|nyo|nzi|oc|oci|oji|om|or|ori|orm|osa|oss|ota|oto|pa|paa|pag|pal|pam|pan|pap|pau|peo|per/fas|pl|pli|pol|pon|por|pra|pro|ps|pt|pus|qu|que|raj|rar|rm|rn|ro|roa|roh|rom|ron/rum|ru|rum/ron|run|rus|rw|sa|sad|sag|sai|sal|sam|san|sco|scr|sd|sel|sem|sg|sh|shn|si|sid|sin|sio|sit|sk|sl|sla|slk/slo|slo/slk|slv|sm|smo|sn|sna|snd|so|sog|som|son|sot|spa/esl|sq|sqi/alb|sr|srr|ss|ssa|ssw|st|su|suk|sun|sus|sux|sv|sve/swe|sw|swa|swe/sve|syr|ta|tah|tam|tat|te|tel|tem|ter|tg|tgk|tgl|th|tha|ti|tib/bod|tig|tir|tiv|tk|tl|tli|tn|to|tog|ton|tr|tru|ts|tsi|tsn|tso|tt|tuk|tum|tur|tut|tw|twi|ug|uga|uig|uk|ukr|umb|und|ur|urd|uz|uzb|vai|ven|vi|vie|vo|vol|vot|wak|wal|war|was|wel/cym|wen|wo|wo|wol|x-klingon|xh|xh|xho|yao|yap|yi|yid|yo|yo|yor|za|zap|zen|zh|zha|zho/chi|zu|zul|zun)';
vCard.re['text-param']='(?:VALUE=ptext|LANGUAGE='+vCard.re['langval']+'|'+vCard.re['x-name']+'='+vCard.re['param-value']+')';
vCard.re['text-value']='(?:'+vCard.re['SAFE-CHAR']+'|[:"]|'+vCard.re['ESCAPED-CHAR']+')*';
vCard.re['text-value-list']=vCard.re['text-value']+'(?:,'+vCard.re['text-value']+')*';
// UNUSED vCard.re['source-param']='(?:VALUE=uri|CONTEXT=word|'+vCard.re['x-name']+'=(?:'+vCard.re['SAFE-CHAR']+')*)';
vCard.re['uri']=vCard.re['param-value'];	// TODO -> change to RFC 1738 compiant
//vCard.re['iana-image-type']='(?:cgm|example|fits|g3fax|gif|ief|jp2|jpeg|jpm|jpx|ktx|naplps|png|prs\\.btif|prs\\.pti|svg\\+xml|t38|tiff|tiff-fx|vnd\\.adobe\\.photoshop|vnd\\.cns\\.inf2|vnd\\.dece\\.graphic|vnd\\.djvu|vnd\\.dwg|vnd\\.dxf|vnd\\.fastbidsheet|vnd\\.fpx|vnd\\.fst|vnd\\.fujixerox\\.edmics-mmr|vnd\\.fujixerox\\.edmics-rlc|vnd\\.globalgraphics\\.pgb|vnd\\.microsoft\\.icon|vnd\\.mix|vnd\\.ms-modi|vnd\\.net-fpx|vnd\\.radiance|vnd\\.sealed\\.png|vnd\\.sealedmedia\\.softseal\\.gif|vnd\\.sealedmedia\\.softseal\\.jpg|vnd\\.svf|vnd\\.wap\\.wbmp|vnd\\.xiff)';
// Evolution IMG type bug support:
vCard.re['iana-image-type']='(?:"X-EVOLUTION-UNKNOWN"|cgm|example|fits|g3fax|gif|ief|jp2|jpeg|jpm|jpx|ktx|naplps|png|prs\\.btif|prs\\.pti|svg\\+xml|t38|tiff|tiff-fx|vnd\\.adobe\\.photoshop|vnd\\.cns\\.inf2|vnd\\.dece\\.graphic|vnd\\.djvu|vnd\\.dwg|vnd\\.dxf|vnd\\.fastbidsheet|vnd\\.fpx|vnd\\.fst|vnd\\.fujixerox\\.edmics-mmr|vnd\\.fujixerox\\.edmics-rlc|vnd\\.globalgraphics\\.pgb|vnd\\.microsoft\\.icon|vnd\\.mix|vnd\\.ms-modi|vnd\\.net-fpx|vnd\\.radiance|vnd\\.sealed\\.png|vnd\\.sealedmedia\\.softseal\\.gif|vnd\\.sealedmedia\\.softseal\\.jpg|vnd\\.svf|vnd\\.wap\\.wbmp|vnd\\.xiff)';
// UNUSED vCard.re['iana-audio-type']='(?:1d-interleaved-parityfec|32kadpcm|3gpp|3gpp2|ac3|AMR|AMR-WB|amr-wb\\+|asc|ATRAC-ADVANCED-LOSSLESS|ATRAC-X|ATRAC3|basic|BV16|BV32|clearmode|CN|DAT12|dls|dsr-es201108|dsr-es202050|dsr-es202211|dsr-es202212|eac3|DVI4|EVRC|EVRC0|EVRC1|EVRCB|EVRCB0|EVRCB1|EVRC-QCP|EVRCWB|EVRCWB0|EVRCWB1|example|G719|G722|G7221|G723|G726-16|G726-24|G726-32|G726-40|G728|G729|G7291|G729D|G729E|GSM|GSM-EFR|GSM-HR-08|iLBC|ip-mr_v2\\.5|L8|L16|L20|L24|LPC|mobile-xmf|MPA|mp4|MP4A-LATM|mpa-robust|mpeg|mpeg4-generic|ogg|parityfec|PCMA|PCMA-WB|PCMU|PCMU-WB|prs\\.sid|QCELP|RED|rtp-enc-aescm128|rtp-midi|rtx|SMV|SMV0|SMV-QCP|sp-midi|speex|t140c|t38|telephone-event|tone|UEMCLIP|ulpfec|VDVI|VMR-WB|vnd\\.3gpp\\.iufp|vnd\\.4SB|vnd\\.audiokoz|vnd\\.CELP|vnd\\.cisco\\.nse|vnd\\.cmles\\.radio-events|vnd\\.cns\\.anp1|vnd\\.cns\\.inf1|vnd\\.dece\\.audio|vnd\\.digital-winds|vnd\\.dlna\\.adts|vnd\\.dolby\\.heaac\\.1|vnd\\.dolby\\.heaac\\.2|vnd\\.dolby\\.mlp|vnd\\.dolby\\.mps|vnd\\.dolby\\.pl2|vnd\\.dolby\\.pl2x|vnd\\.dolby\\.pl2z|vnd\\.dolby\\.pulse\\.1|vnd\\.dra|vnd\\.dts|vnd\\.dts\\.hd|vnd\\.dvb\\.file|vnd\\.everad\\.plj|vnd\\.hns\\.audio|vnd\\.lucent\\.voice|vnd\\.ms-playready\\.media\\.pya|vnd\\.nokia\\.mobile-xmf|vnd\\.nortel\\.vbk|vnd\\.nuera\\.ecelp4800|vnd\\.nuera\\.ecelp7470|vnd\\.nuera\\.ecelp9600|vnd\\.octel\\.sbc|vnd\\.qcelp|vnd\\.rhetorex\\.32kadpcm|vnd\\.rip|vnd\\.sealedmedia\\.softseal\\.mpeg|vnd\\.vmx\\.cvsd|vorbis|vorbis-config)';
// original
//vCard.re['img-inline-param']='(?:VALUE=binary|ENCODING=b|TYPE='+vCard.re['iana-image-type']+')';
// new version (apple X-ABCROP-RECTANGLE in type)
vCard.re['img-inline-param']='(?:VALUE=uri|VALUE=binary|ENCODING=b|TYPE='+vCard.re['iana-image-type']+'|'+vCard.re['x-name']+'='+vCard.re['param-value']+')';
//vCard.re['img-inline-value']='(?:[A-Za-z+/]{4})*(?:(?:[A-Za-z+/]{4})|(?:[A-Za-z+/]{3}=)|(?:[A-Za-z+/]{2}==))';	// RFC 4648 -> TODO: "BASE64:" prefix (is it RFC compiant?)
//new version
// docasne -> opravit (hore by to malo byt spravne, ale zjavne nie je ...)
vCard.re['img-inline-value']='(?:[A-Za-z0-9+:&-/=]*)';
vCard.re['img-refer-param']='(?:VALUE=uri|TYPE='+vCard.re['iana-image-type']+')';
vCard.re['img-refer-value']=vCard.re['uri'];
// UNUSED vCard.re['snd-inline-param']='(?:VALUE=binary|ENCODING=b|TYPE='+vCard.re['iana-audio-type']+')';
// UNUSED vCard.re['snd-inline-value']='(?:[A-Za-z+/]{4})*(?:(?:[A-Za-z+/]{4})|(?:[A-Za-z+/]{3}=)|(?:[A-Za-z+/]{2}==))';	// RFC 4648 -> TODO: "BASE64:" prefix (is it RFC compiant?)
// UNUSED vCard.re['snd-refer-param']='(?:VALUE=uri|TYPE='+vCard.re['iana-audio-type']+')';
// UNUSED vCard.re['snd-refer-value']=vCard.re['uri'];
vCard.re['date-value']='[0-2][0-9]{3}-?(?:0[1-9]|1[012])-?(?:0[1-9]|[12][0-9]|3[01])';		// TODO: do not allow invalid dates as: 2000-02-30
vCard.re['date-time-value']=vCard.re['date-value']+'T(?:[01][0-9]|2[0-3]):?(?:[0-5][0-9])(?::?(?:[0-5][0-9]))?(?:Z|[+-](?:[01][0-9]|2[0-3])(?::?(?:[0-5][0-9]))?)';
vCard.re['adr-type']='(?:dom|intl|postal|parcel|home|work|pref|'+vCard.re['iana-token']+'|'+vCard.re['x-name']+')';	// RFC BUG? -> refers to 'iana-type' instead of 'iana-token'
vCard.re['adr-param']='(?:TYPE='+vCard.re['adr-type']+'(?:,'+vCard.re['adr-type']+')*)';
vCard.re['adr-value']=vCard.re['text-value']+'(?:;'+vCard.re['text-value']+'){0,6}';	// PO Box, Extended Address, Street, Locality, Region, Postal, Code, Country Name
vCard.re['tel-type']='(?:HOME|WORK|PREF|VOICE|FAX|MSG|CELL|PAGER|BBS|MODEM|CAR|ISDN|VIDEO|PCS|'+vCard.re['iana-token']+'|'+vCard.re['x-name']+')';
vCard.re['tel-param']='(?:TYPE='+vCard.re['tel-type']+'(?:,'+vCard.re['tel-type']+')*)';
//vCard.re['tel-value']='\\+?[0-9 /*#()-]+';	// TODO: CCITT E.163 and CCITT X.121
vCard.re['tel-value']='(?:'+vCard.re['VALUE-CHAR']+')*';	// we allow any phone values
vCard.re['email-type']='(?:INTERNET|X400|'+vCard.re['iana-token']+'|'+vCard.re['x-name']+')';	// RFC BUG? -> it refers to undefined "X-" word
vCard.re['email-param']='(?:TYPE='+vCard.re['email-type']+'(?:,PREF)?)';
// UNUSED vCard.re['utc-offset-value']='[+-]?(?:[01][0-9]|2[0-3]):[0-5][0-9]';	// TODO - pridal som otaznik za +-
// UNUSED vCard.re['float-value']='[+-]?[0-9]+\\.[0-9]+';	// TODO - pridal som otaznik za +-
// UNUSED vCard.re['keytype']='(?:X509|PGP|'+vCard.re['iana-token']+'|'+vCard.re['x-name']+')';
// UNUSED vCard.re['key-txt-param']='TYPE='+vCard.re['keytype'];
// UNUSED vCard.re['key-bin-param']='(?:TYPE='+vCard.re['keytype']+'|ENCODING=b)';
// UNUSED vCard.re['binary-value']='(?:[A-Za-z+/]{4})*(?:(?:[A-Za-z+/]{4})|(?:[A-Za-z+/]{3}=)|(?:[A-Za-z+/]{2}==))';	// RFC 4648 -> TODO: "BASE64:" prefix (is it RFC compiant?)
// UNUSED vCard.re['contentline_NAME']='(?:'+vCard.re['group']+'\\.)?NAME:'+vCard.re['text-value']+vCard.re['CRLF'];
// UNUSED vCard.re['contentline_PROFILE']='(?:'+vCard.re['group']+'\\.)?PROFILE:'+vCard.re['text-value']+vCard.re['CRLF'];
// UNUSED vCard.re['contentline_SOURCE']='(?:'+vCard.re['group']+'\\.)?SOURCE(?:;'+vCard.re['source-param']+')*:'+vCard.re['uri']+vCard.re['CRLF'];
vCard.re['contentline_FN']='(?:'+vCard.re['group']+'\\.)?FN(?:;'+vCard.re['text-param']+')*:'+vCard.re['text-value']+vCard.re['CRLF'];
vCard.pre['contentline_FN']=RegExp('\r\n'+vCard.re['contentline_FN'],'mi');
vCard.re['contentline_N']='(?:'+vCard.re['group']+'\\.)?N(?:;'+vCard.re['text-param']+')*:'+vCard.re['text-value']+'(?:,'+vCard.re['text-value']+')*'+'(?:;'+vCard.re['text-value']+'(?:,'+vCard.re['text-value']+')*){0,4}'+vCard.re['CRLF'];
vCard.pre['contentline_N']=RegExp('\r\n'+vCard.re['contentline_N'],'mi');
vCard.re['contentline_NICKNAME']='(?:'+vCard.re['group']+'\\.)?NICKNAME(?:;'+vCard.re['text-param']+')*:'+vCard.re['text-value']+vCard.re['CRLF'];	// RFC BUG? -> refers to 'text-list' instead of 'text-value'
vCard.pre['contentline_NICKNAME']=RegExp('\r\n'+vCard.re['contentline_NICKNAME'],'mi');
vCard.re['contentline_PHOTO']='(?:'+vCard.re['group']+'\\.)?PHOTO(?:(?:(?:;'+vCard.re['img-inline-param']+')*:'+vCard.re['img-inline-value']+')|(?:(?:;'+vCard.re['img-refer-param']+')*:'+vCard.re['img-refer-value']+'))'+vCard.re['CRLF'];
vCard.pre['contentline_PHOTO']=RegExp('\r\n'+vCard.re['contentline_PHOTO'],'mi');
vCard.re['contentline_BDAY']='(?:'+vCard.re['group']+'\\.)?BDAY(?:(?:(?:;VALUE=date)?:'+vCard.re['date-value']+')|(?:(?:;VALUE=date-time)?:'+vCard.re['date-time-value']+'))'+vCard.re['CRLF'];
vCard.pre['contentline_BDAY']=RegExp('\r\n'+vCard.re['contentline_BDAY'],'mi');
vCard.re['contentline_ADR']='(?:'+vCard.re['group']+'\\.)?ADR(?:;(?:'+vCard.re['adr-param']+'|'+vCard.re['text-param']+'))*:'+vCard.re['adr-value']+vCard.re['CRLF'];
vCard.pre['contentline_ADR']=RegExp('\r\n'+vCard.re['contentline_ADR'],'mi');
// UNUSED vCard.re['contentline_LABEL']='(?:'+vCard.re['group']+'\\.)?LABEL(?:;'+vCard.re['text-param']+')*:'+vCard.re['text-value']+vCard.re['CRLF'];	// RFC BUG? -> it refers to 'adr-param' in LABEL?
vCard.re['contentline_TEL']='(?:'+vCard.re['group']+'\\.)?TEL(?:;'+vCard.re['tel-param']+')*:'+vCard.re['tel-value']+vCard.re['CRLF'];
vCard.pre['contentline_TEL']=RegExp('\r\n'+vCard.re['contentline_TEL'],'mi');
vCard.re['contentline_EMAIL']='(?:'+vCard.re['group']+'\\.)?EMAIL(?:;'+vCard.re['email-param']+')*:'+vCard.re['text-value']+vCard.re['CRLF'];
vCard.pre['contentline_EMAIL']=RegExp('\r\n'+vCard.re['contentline_EMAIL'],'mi');
// UNUSED vCard.re['contentline_MAILER']='(?:'+vCard.re['group']+'\\.)?MAILER(?:;'+vCard.re['text-param']+')*:'+vCard.re['text-value']+vCard.re['CRLF'];
// UNUSED vCard.re['contentline_TZ']='(?:'+vCard.re['group']+'\\.)?TZ:'+vCard.re['utc-offset-value']+vCard.re['CRLF'];
// UNUSED vCard.re['contentline_GEO']='(?:'+vCard.re['group']+'\\.)?GEO:'+vCard.re['float-value']+';'+vCard.re['float-value']+vCard.re['CRLF'];
vCard.re['contentline_TITLE']='(?:'+vCard.re['group']+'\\.)?TITLE(?:;'+vCard.re['text-param']+')*:'+vCard.re['text-value']+vCard.re['CRLF'];
vCard.pre['contentline_TITLE']=RegExp('\r\n'+vCard.re['contentline_TITLE'],'mi');
// UNUSED vCard.re['contentline_ROLE']='(?:'+vCard.re['group']+'\\.)?ROLE(?:;'+vCard.re['text-param']+')*:'+vCard.re['text-value']+vCard.re['CRLF'];
// UNUSED vCard.re['contentline_LOGO']='(?:'+vCard.re['group']+'\\.)?LOGO(?:(?:(?:;'+vCard.re['img-inline-param']+')*:'+vCard.re['img-inline-value']+')|(?:(?:;'+vCard.re['img-refer-param']+')*:'+vCard.re['img-refer-value']+'))'+vCard.re['CRLF'];
// UNUSED vCard.re['contentline_AGENT']='(?:'+vCard.re['group']+'\\.)?AGENT(?:(?::'+vCard.re['text-value']+')|(?:(?:;VALUE=uri)*:'+vCard.re['uri']+'))'+vCard.re['CRLF'];	// TODO: URI MUST refer to image content of given type
vCard.re['contentline_ORG']='(?:'+vCard.re['group']+'\\.)?ORG(?:;'+vCard.re['text-param']+')*:'+vCard.re['text-value']+'(?:;'+vCard.re['text-value']+')*'+vCard.re['CRLF'];	// First is Organization Name, remainders are Organization Units
vCard.pre['contentline_ORG']=RegExp('\r\n'+vCard.re['contentline_ORG'],'mi');
vCard.re['contentline_CATEGORIES']='(?:'+vCard.re['group']+'\\.)?CATEGORIES(?:;'+vCard.re['text-param']+')*:'+vCard.re['text-value-list']+vCard.re['CRLF'];	// RFC BUG? -> refers to 'text-list' instead of 'text-value-list'
vCard.pre['contentline_CATEGORIES']=RegExp('\r\n'+vCard.re['contentline_CATEGORIES'],'mi');
vCard.re['contentline_NOTE']='(?:'+vCard.re['group']+'\\.)?NOTE(?:;'+vCard.re['text-param']+')*:'+vCard.re['text-value']+vCard.re['CRLF'];
vCard.pre['contentline_NOTE']=RegExp('\r\n'+vCard.re['contentline_NOTE'],'mi');
vCard.re['contentline_PRODID']='(?:'+vCard.re['group']+'\\.)?PRODID:'+vCard.re['text-value']+vCard.re['CRLF'];
vCard.pre['contentline_PRODID']=RegExp('\r\n'+vCard.re['contentline_PRODID'],'mi');
vCard.re['contentline_REV']='(?:'+vCard.re['group']+'\\.)?REV(?:(?:(?:;VALUE=date)*:'+vCard.re['date-value']+')|(?:(?:;VALUE=date-time)*:'+vCard.re['date-time-value']+'))'+vCard.re['CRLF'];
vCard.pre['contentline_REV']=RegExp('\r\n'+vCard.re['contentline_REV'],'mi');
// UNUSED vCard.re['contentline_SORT-STRING']='(?:'+vCard.re['group']+'\\.)?SORT-STRING(?:;'+vCard.re['text-param']+')*:'+vCard.re['text-value']+vCard.re['CRLF'];
// UNUSED vCard.re['contentline_SOUND']='(?:'+vCard.re['group']+'\\.)?SOUND(?:(?:(?:;'+vCard.re['snd-inline-param']+')*:'+vCard.re['snd-inline-value']+')|(?:(?:;'+vCard.re['snd-refer-param']+')*:'+vCard.re['snd-refer-value']+'))'+vCard.re['CRLF'];
vCard.re['contentline_UID']='(?:'+vCard.re['group']+'\\.)?UID:'+vCard.re['text-value']+vCard.re['CRLF'];
vCard.pre['contentline_UID']=RegExp('\r\n'+vCard.re['contentline_UID'],'mi');
// vCard.re['contentline_URL']='(?:'+vCard.re['group']+'\\.)?URL:'+vCard.re['uri']+vCard.re['CRLF'];	// RFC
vCard.re['contentline_URL']='(?:'+vCard.re['group']+'\\.)?URL(?:;'+vCard.re['param']+')*:'+vCard.re['text-value']+vCard.re['CRLF'];	// Non-RFC
vCard.pre['contentline_URL']=RegExp('\r\n'+vCard.re['contentline_URL'],'mi');
vCard.re['contentline_VERSION']='(?:'+vCard.re['group']+'\\.)?VERSION:'+vCard.re['text-value']+vCard.re['CRLF'];
vCard.pre['contentline_VERSION']=RegExp('\r\n'+vCard.re['contentline_VERSION'],'mi');
// UNUSED vCard.re['contentline_CLASS']='(?:'+vCard.re['group']+'\\.)?CLASS:(?:PUBLIC|PRIVATE|CONFIDENTIAL|'+vCard.re['iana-token']+'|'+vCard.re['x-name']+')'+vCard.re['CRLF'];
// UNUSED vCard.re['contentline_KEY']='(?:'+vCard.re['group']+'\\.)?KEY(?:(?:(?:;'+vCard.re['key-txt-param']+')*:'+vCard.re['text-value']+')|(?:(?:;'+vCard.re['key-bin-param']+')*:'+vCard.re['binary-value']+'))'+vCard.re['CRLF'];
// UNUSED vCard.re['contentline_X-*']='(?:'+vCard.re['group']+'\\.)?'+vCard.re['x-name']+'(?:;(?:'+vCard.re['text-param']+'|'+vCard.re['x-name']+'='+vCard.re['param-value']+'))*:'+vCard.re['text-value']+vCard.re['CRLF'];

// APPLE specific
vCard.re['x-abrelatednames-type']='(?:PREF|'+vCard.re['iana-token']+'|'+vCard.re['x-name']+')';
vCard.re['x-abrelatednames-param']='(?:TYPE='+vCard.re['x-abrelatednames-type']+'(?:,'+vCard.re['x-abrelatednames-type']+')*)';
vCard.re['contentline_X-ABRELATEDNAMES']='(?:'+vCard.re['group']+'\\.)?X-ABRELATEDNAMES(?:;'+vCard.re['x-abrelatednames-param']+')*:'+vCard.re['text-value']+vCard.re['CRLF'];
vCard.pre['contentline_X-ABRELATEDNAMES']=RegExp('\r\n'+vCard.re['contentline_X-ABRELATEDNAMES'],'mi');
vCard.re['impp-type']='(?:PREF|'+vCard.re['iana-token']+'|'+vCard.re['x-name']+')';
vCard.re['impp-param']='(?:TYPE='+vCard.re['impp-type']+'(?:,'+vCard.re['impp-type']+')*)';
vCard.re['impp-service-type']='(?:'+vCard.re['iana-token']+'|'+vCard.re['x-name']+')';
vCard.re['impp-service-param']='(?:X-SERVICE-TYPE='+vCard.re['impp-service-type']+')';
vCard.re['contentline_IMPP']='(?:'+vCard.re['group']+'\\.)?IMPP(?:;'+vCard.re['impp-param']+')*;'+vCard.re['impp-service-param']+'(?:;'+vCard.re['impp-param']+')*:'+vCard.re['text-value']+vCard.re['CRLF'];
vCard.pre['contentline_IMPP']=RegExp('\r\n'+vCard.re['contentline_IMPP'],'mi');

// Match specified X-attributes
vCard.re['X-ABShowAs']='(?:'+vCard.re['group']+'\\.)?X-ABShowAs(?:;(?:'+vCard.re['text-param']+'|'+vCard.re['x-name']+'='+vCard.re['param-value']+'))*:' +vCard.re['text-value']+vCard.re['CRLF'];
vCard.pre['X-ABShowAs']=RegExp('\r\n'+vCard.re['X-ABShowAs'],'mi');
vCard.re['X-ADDRESSBOOKSERVER-KIND']='(?:'+vCard.re['group']+'\\.)?X-ADDRESSBOOKSERVER-KIND(?:;(?:'+vCard.re['text-param']+'|'+vCard.re['x-name']+'='+vCard.re['param-value']+'))*:' +vCard.re['text-value']+vCard.re['CRLF'];
vCard.pre['X-ADDRESSBOOKSERVER-KIND']=RegExp('\r\n'+vCard.re['X-ADDRESSBOOKSERVER-KIND'],'mi');
vCard.re['X-ADDRESSBOOKSERVER-MEMBER']='(?:'+vCard.re['group']+'\\.)?X-ADDRESSBOOKSERVER-MEMBER(?:;(?:'+vCard.re['text-param']+'|'+vCard.re['x-name']+'='+vCard.re['param-value']+'))*:' +vCard.re['text-value']+vCard.re['CRLF'];
vCard.pre['X-ADDRESSBOOKSERVER-MEMBER']=RegExp('\r\n'+vCard.re['X-ADDRESSBOOKSERVER-MEMBER'],'mi');
vCard.re['contentline_X-ANNIVERSARY']='(?:'+vCard.re['group']+'\\.)?X-ANNIVERSARY(?:(?:(?:;VALUE=date)?:'+vCard.re['date-value']+')|(?:(?:;VALUE=date-time)?:'+vCard.re['date-time-value']+'))'+vCard.re['CRLF'];
vCard.pre['contentline_X-ANNIVERSARY']=RegExp('\r\n'+vCard.re['contentline_X-ANNIVERSARY'],'mi');


vCard.tplC['begin']='##:::##group_wd##:::##BEGIN:VCARD\r\n';
vCard.tplM['begin']=null;
vCard.tplC['contentline_VERSION']='##:::##group_wd##:::##VERSION:##:::##version##:::##\r\n';
vCard.tplM['contentline_VERSION']=null;
vCard.tplC['contentline_UID']='##:::##group_wd##:::##UID##:::##params_wsc##:::##:##:::##uid##:::##\r\n';
vCard.tplM['contentline_UID']=null;
vCard.tplC['contentline_FN']='##:::##group_wd##:::##FN##:::##params_wsc##:::##:##:::##fn##:::##\r\n';
vCard.tplM['contentline_FN']=null;
vCard.tplC['contentline_N']='##:::##group_wd##:::##N##:::##params_wsc##:::##:##:::##family##:::##;##:::##given##:::##;##:::##middle##:::##;##:::##prefix##:::##;##:::##suffix##:::##\r\n';
vCard.tplM['contentline_N']=null;
vCard.tplC['contentline_CATEGORIES']='##:::##group_wd##:::##CATEGORIES##:::##params_wsc##:::##:##:::##value##:::##\r\n';
vCard.tplM['contentline_CATEGORIES']=null;
vCard.tplC['contentline_NOTE']='##:::##group_wd##:::##NOTE##:::##params_wsc##:::##:##:::##value##:::##\r\n';
vCard.tplM['contentline_NOTE']=null;
vCard.tplC['contentline_NICKNAME']='##:::##group_wd##:::##NICKNAME##:::##params_wsc##:::##:##:::##value##:::##\r\n';
vCard.tplM['contentline_NICKNAME']=null;
vCard.tplC['contentline_BDAY']='##:::##group_wd##:::##BDAY##:::##params_wsc##:::##:##:::##value##:::##\r\n';
vCard.tplM['contentline_BDAY']=null;
vCard.tplC['contentline_X-ANNIVERSARY']='##:::##group_wd##:::##X-ANNIVERSARY##:::##params_wsc##:::##:##:::##value##:::##\r\n';
vCard.tplM['contentline_X-ANNIVERSARY']=null;
vCard.tplC['contentline_TITLE']='##:::##group_wd##:::##TITLE##:::##params_wsc##:::##:##:::##value##:::##\r\n';
vCard.tplM['contentline_TITLE']=null;
vCard.tplC['contentline_URL']='##:::##group_wd##:::##URL##:::##params_wsc##:::##:##:::##value##:::##\r\n';
vCard.tplM['contentline_URL']=null;
vCard.tplC['contentline_ORG']='##:::##group_wd##:::##ORG##:::##params_wsc##:::##:##:::##org##:::####:::##units_wsc##:::##\r\n';
vCard.tplM['contentline_ORG']=null;
vCard.tplC['contentline_X-ABShowAs']='##:::##group_wd##:::##X-ABShowAs##:::##params_wsc##:::##:##:::##value##:::##\r\n';
vCard.tplM['contentline_X-ABShowAs']=null;
vCard.tplC['contentline_ADR']='##:::##group_wd##:::##ADR##:::##params_wsc##:::##:##:::##pobox##:::##;##:::##extaddr##:::##;##:::##street##:::##;##:::##locality##:::##;##:::##region##:::##;##:::##code##:::##;##:::##country##:::##\r\n';
vCard.tplM['contentline_ADR']=null;
vCard.tplC['contentline_TEL']='##:::##group_wd##:::##TEL##:::##params_wsc##:::##:##:::##value##:::##\r\n';
vCard.tplM['contentline_TEL']=null;
vCard.tplC['contentline_EMAIL']='##:::##group_wd##:::##EMAIL##:::##params_wsc##:::##:##:::##value##:::##\r\n';
vCard.tplM['contentline_EMAIL']=null;
vCard.tplC['contentline_X-ABRELATEDNAMES']='##:::##group_wd##:::##X-ABRELATEDNAMES##:::##params_wsc##:::##:##:::##value##:::##\r\n';
vCard.tplM['contentline_X-ABRELATEDNAMES']=null;
vCard.tplC['contentline_IMPP']='##:::##group_wd##:::##IMPP##:::##params_wsc##:::##:##:::##value##:::##\r\n';
vCard.tplM['contentline_IMPP']=null;
vCard.tplC['contentline_REV']='##:::##group_wd##:::##REV##:::##params_wsc##:::##:##:::##value##:::##\r\n';
vCard.tplM['contentline_REV']=null;
vCard.tplC['end']='##:::##group_wd##:::##END:VCARD\r\n';
vCard.tplM['end']=null;
vCard.tplM['unprocessed_unrelated']='';
