/**
* @package shorty an ownCloud url shortener plugin
* @category internet
* @author Christian Reiner
* @copyright 2011-2013 Christian Reiner <foss@christian-reiner.info>
* @license GNU Affero General Public license (AGPL)
* @link information http://apps.owncloud.com/content/show.php/Shorty?content=150401
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the license, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.
* If not, see <http://www.gnu.org/licenses/>.
*
*/

/**
 * @file js/util.js
 * @brief Client side activity library
 * @description
 * This script implements a few always-handy routines (currently) not provided
 * by the main owncloud framework. Since they really don't fit into any of the
 * internal apps logic categories they are collected in this helper script. 
 * @author Christian Reiner
 */

/**
 * @function max
 * @brief Returns the max value of all elements in an array
 * author Christian Reiner
 */
Array.prototype.max = function() {
	var max = this[0];
	var len = this.length;
	for (var i = 1; i < len; i++)
		if (this[i] > max) max = this[i];
	return max;
}
/**
 * @function min
 * @brief Returns the min value of all elements in an array
 * author Christian Reiner
 */
Array.prototype.min = function() {
	var min = this[0];
	var len = this.length;
	for (var i = 1; i < len; i++)
		if (this[i] < min) min = this[i];
  return min;
}

/**
 * @function max
 * @brief max()-selector
 * @usage: var maxWidth = $("a").max(function() {return $(this).width(); });
 * @param selector jQueryObject Selector of objects whos values are to be compared
 * @return value Maximum of values represented by the selector
 */
$.fn.max = function(selector) {
	return Math.max.apply(null, this.map(function(index, el) {
		return selector.apply(el);
	}).get() );
}
/**
 * @function min
 * @brief min()-selector
 * @usage: var minWidth = $("a").min(function() {return $(this).width(); });
 * @param selector jQueryObject Selector of objects whos values are to be compared
 * @return value Minimum of values represented by the selector
 */
$.fn.min = function(selector) {
	return Math.min.apply(null, this.map(function(index, el) {
		return selector.apply(el);
	}).get() );
}

/**
 * @function executeFunctionByName
 * @brief Calls a namespaced function named by a string
 * @description This is something like phps call_user_func()...
 */
function executeFunctionByName(functionName, context /*, args */) {
	/* Note: the number 10 below is only required by stupid MS-IE<9 cause splice() is broken in there when using only one argument */
	var args = Array.prototype.slice.call(arguments).splice(2,10);
	var namespaces = functionName.split(".");
	var func = namespaces.pop();
	for(var i = 0; i < namespaces.length; i++) {
		context = context[namespaces[i]];
	}
	return context[func].apply(this, args);
}

/**
 * @function dateExpired
 * @brief Checks if a given date has already expired
 * @param date Date to check
 * @return bool Whether or not the date has expired
 * @author Christian Reiner
 */
function dateExpired(date){
	return (Date.parse(date)<=Date.parse(Date()));
} // dateExpired

/**
 * @function dateTimeToHuman
 * @brief Formats a given dateTime into international standard format (YYYY-MM-DD hh:mm:ss)
 * @param date integer timestamp
 * @return string formatted dateTime
 * @author Christian Reiner
 */
function dateTimeToHuman(timestamp,placeholder){
	if (undefined==timestamp)
		return placeholder||'';
	var d=new Date(1000*timestamp);
	return 	d.getFullYear()
		+'-'+padLeadingZeros(d.getMonth()+1,2)
		+'-'+padLeadingZeros(d.getDate(),2)
		+' '+padLeadingZeros(d.getHours(),2)
		+':'+padLeadingZeros(d.getMinutes(),2)
		+':'+padLeadingZeros(d.getSeconds(),2);
} // dateTimeToHuman

/**
 * @function padLeadingZeros
 * @brief Pads a given number with leading zeros up to the specified total length
 * @param number Integer number
 * @return string padded number
 * @author Christian Reiner
 */
function padLeadingZeros(number,length){
	length=length|0;
	var num=new Number(number).toString();
	var pad=new Array(length-num.length+1).join('0');
	return pad+num;
} // padLeadingZeros

/**
 * @function jsFunctionName
 * @brief Returns the name of a specified function
 * @author Christian Reiner
 */
function jsFunctionName(func){
	var name = func.toString();
	name = name.substr('function '.length);
	name = name.substr(0, name.indexOf('('));
	return name;
} // jsFunctionName

/**
 * @function nl2br
 * @brief Converts newlines inside strings to html <br> tags
 * @author Christian Reiner
 */
function nl2br (str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
} // nl2br

/**
 * @dictionary applyVersionOperator
 * @brief Dictionary of comparision operators
 * @author Christian Reiner
 */
var applyVersionOperator = {
	'<' : function(a,b){return ( 1==compareVersionNumbers(a,b))?true:false;},
	'<=': function(a,b){return (-1!=compareVersionNumbers(a,b))?true:false;},
	'=<': function(a,b){return (-1!=compareVersionNumbers(a,b))?true:false;},
	'==': function(a,b){return ( 0==compareVersionNumbers(a,b))?true:false;},
	'!=': function(a,b){return ( 0!=compareVersionNumbers(a,b))?true:false;},
	'=>': function(a,b){return ( 1!=compareVersionNumbers(a,b))?true:false;},
	'>=': function(a,b){return ( 1!=compareVersionNumbers(a,b))?true:false;},
	'>' : function(a,b){return (-1==compareVersionNumbers(a,b))?true:false;}
} // applyVersionOperator

/**
 * @function compareVersionNumbers
 * @brief Compares two given version numbers noted as dictionaries
 * @return -1|0|1 (a<b=>1,a==b=>0,a>b=>-1)
 * @author Christian Reiner
 */
function compareVersionNumbers(a,b) {
    var ax, bx;
    for (var i=0; i<Math.max(a.length,b.length); ++i) {
        ax=a[i]||0;
        bx=b[i]||0;
        if (bx>ax)
            return 1;
        else if (ax>bx)
            return -1;
    }
    return 0;
} // compareVersionNumbers

/**
 * @function parseVersionString
 * @brief Parses a given version notation in string format into a dictionary
 * @author Christian Reiner
 */
function parseVersionString (str) {
    if (typeof(str) != 'string') { return false; }
    return str.split('.');
} // parseVersionString
