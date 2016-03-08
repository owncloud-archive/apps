/*
 * svgToPdf.js
 * 
 * Copyright 2012 Florian Hülsmann <fh@cbix.de>
 * 
 * This script is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with this file.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */

var pdfSvgAttr = {
    // allowed attributes. all others are removed if options.preview = true.
    g: ['stroke', 'fill', 'stroke-width'],
    line: ['x1', 'y1', 'x2', 'y2', 'stroke', 'stroke-width'],
    rect: ['x', 'y', 'width', 'height', 'stroke', 'fill', 'stroke-width'],
    ellipse: ['cx', 'cy', 'rx', 'ry', 'stroke', 'fill', 'stroke-width'],
    circle: ['cx', 'cy', 'r', 'stroke', 'fill', 'stroke-width'],
    text: ['x', 'y', 'font-size', 'font-family', 'text-anchor', 'font-weight', 'font-style', 'fill']
};
var svgElementToPdf = function(element, pdf, options) {
    // pdf is a jsPDF object
    console.log("options =", options);
    var preview = (typeof(options.preview) == 'undefined' ? false : options.preview);
    var k = (typeof(options.scale) == 'undefined' ? 1.0 : options.scale);
    var colorMode = null;
    $(element).children().each(function(i, node) {
        //console.log("passing: ", node);
        var n = $(node);
		var hasFillColor = false;
		var hasStrokeColor = false;
		if(n.is('g,line,rect,ellipse,circle,text')) {
            var fillColor = n.attr('fill');
            if(typeof(fillColor) != 'undefined') {
                var fillRGB = new RGBColor(fillColor);
                if(fillRGB.ok) {
					hasFillColor = true;
                    colorMode = 'F';
                } else {
                    colorMode = null;
                }
            }
        }
        if(n.is('g,line,rect,ellipse,circle')) {
            if(hasFillColor) {
				pdf.setFillColor(fillRGB.r, fillRGB.g, fillRGB.b);
			}
            if(typeof(n.attr('stroke-width')) != 'undefined') {
                pdf.setLineWidth(k * parseInt(n.attr('stroke-width')));
            }
            var strokeColor = n.attr('stroke');
            if(typeof(strokeColor) != 'undefined') {
                var strokeRGB = new RGBColor(strokeColor);
                if(strokeRGB.ok) {
					hasStrokeColor = true;
                    pdf.setDrawColor(strokeRGB.r, strokeRGB.g, strokeRGB.b);
                    if(colorMode == 'F') {
                        colorMode = 'FD';
                    } else {
                        colorMode = null;
                    }
                } else {
                    colorMode = null;
                }
            }
		}
        switch(n.get(0).tagName.toLowerCase()) {
            case 'svg':
            case 'a':
            case 'g':
                svgElementToPdf(node, pdf, options);
                if(preview) {
					$.each(node.attributes, function(i, a) {
                    	if(typeof(a) != 'undefined' && pdfSvgAttr.g.indexOf(a.name.toLowerCase()) == -1) {
                    	    node.removeAttribute(a.name);
                    	}
                	});
				}
                break;
            case 'line':
                pdf.line(
                    k*parseInt(n.attr('x1')),
                    k*parseInt(n.attr('y1')),
                    k*parseInt(n.attr('x2')),
                    k*parseInt(n.attr('y2'))
                );
				if(preview) {
                	$.each(node.attributes, function(i, a) {
                   		if(typeof(a) != 'undefined' && pdfSvgAttr.line.indexOf(a.name.toLowerCase()) == -1) {
                        	node.removeAttribute(a.name);
                    	}
                	});
				}
                break;
            case 'rect':
                pdf.rect(
                    k*parseInt(n.attr('x')),
                    k*parseInt(n.attr('y')),
                    k*parseInt(n.attr('width')),
                    k*parseInt(n.attr('height')),
                    colorMode
                );
				if(preview) {
					$.each(node.attributes, function(i, a) {
						if(typeof(a) != 'undefined' && pdfSvgAttr.rect.indexOf(a.name.toLowerCase()) == -1) {
							node.removeAttribute(a.name);
						}
					});
				}
                break;
            case 'ellipse':
                pdf.ellipse(
                    k*parseInt(n.attr('cx')),
                    k*parseInt(n.attr('cy')),
                    k*parseInt(n.attr('rx')),
                    k*parseInt(n.attr('ry')),
                    colorMode
                );
                if(preview) {
					$.each(node.attributes, function(i, a) {
                    	if(typeof(a) != 'undefined' && pdfSvgAttr.ellipse.indexOf(a.name.toLowerCase()) == -1) {
                        	node.removeAttribute(a.name);
                    	}
                	});
				}
                break;
            case 'circle':
                pdf.circle(
                    k*parseInt(n.attr('cx')),
                    k*parseInt(n.attr('cy')),
                    k*parseInt(n.attr('r')),
                    colorMode
                );
                if(preview) {
					$.each(node.attributes, function(i, a) {
                    	if(typeof(a) != 'undefined' && pdfSvgAttr.circle.indexOf(a.name.toLowerCase()) == -1) {
                        	node.removeAttribute(a.name);
                    	}
                	});
				}
                break;
            case 'text':
                if(hasFillColor) {
                    pdf.setTextColor(fillRGB.r, fillRGB.g, fillRGB.b);
                }
                var fontType = "normal";
                if(node.hasAttribute('font-weight')) {
                    if(n.attr('font-weight') == "bold") {
                        fontType = "bold";
                    } else {
                        if(preview) {
							node.removeAttribute('font-weight');
						}
                    }
                }
                if(node.hasAttribute('font-style')) {
                    if(n.attr('font-style') == "italic") {
						if(fontType == "bold") {
                        	fontType += "italic";
						} else {
							fontType = "italic";
						}
                    } else {
						if(preview) {
                        	node.removeAttribute('font-style');
						}
                    }
                }
                pdf.setFontType(fontType);
                if(node.hasAttribute('font-family')) {
                    switch(n.attr('font-family').toLowerCase()) {
                        case 'serif':
							if(preview) {
								n.attr('font-family', 'times');
							}
							pdf.setFont('times');
							break;
                        case 'monospace':
							if(preview) {
								n.attr('font-family', 'courier');
							}
							pdf.setFont('courier');
							break;
                        default:
                            if(preview) {
								n.attr('font-family', 'helvetica');
							}
                            pdf.setFont('helvetica');
                    }
                }
                var pdfFontSize = 16;
				if(node.hasAttribute('font-size')) {
                    pdfFontSize = k * parseInt(n.attr('font-size'));
					if(preview) {
						n.attr('font-size', pdfFontSize / k);
					}
					pdf.setFontSize(pdfFontSize);
                }
                var box = node.getBBox();
                //FIXME: use more accurate positioning!!
                var x, y, xOffset = 0;
                if(node.hasAttribute('text-anchor')) {
                    switch(n.attr('text-anchor')) {
                        case 'end': xOffset = box.width; break;
                        case 'middle': xOffset = box.width / 2; break;
                        case 'start':
                        case 'default': break;
                    }
                }
				x = parseInt(n.attr('x')) - xOffset;
				y = parseInt(n.attr('y'));
				if(preview) {
					n.attr('text-anchor', 'start');
					n.attr('x', x);
					n.attr('y', y);
				}
				//console.log("fontSize:", pdfFontSize, "text:", n.text());
                pdf.text(
                    k * x,
                    k * y,
                    n.text()
                );
				if(preview) {
					$.each(node.attributes, function(i, a) {
						if(typeof(a) != 'undefined' && typeof(a.name) != 'undefined' && a.namespaceURI == null) {
							if(pdfSvgAttr.text.indexOf(a.name.toLowerCase()) < 0) {
								console.log("remove attr", a.nodeName, node.removeAttribute(a.nodeName));
							}
						}
					});
				}
                break;
            //TODO: image
            default:
                if(preview) {
                    console.log("can't translate to pdf:", node);
                    n.remove();
                }
        }
    });
    return pdf;
}
