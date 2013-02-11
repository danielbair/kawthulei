var myCommon = {
    createElement : function(eName)
    {
        if (document.createElementNS)
            return document.createElementNS(
                "http://www.w3.org/1999/xhtml", eName);
        return document.createElement(eName);
    },
    getId : function (node)
    {
        if (node.id != undefined) return node.id;
        return note.getAttribute("id");
    }
};

var requirementsList = "<ol>  <li>You need a modern graphite enabled browser, such as Firefox. Firefox is available from <a href='http://www.mozilla.org/'>www.mozilla.org</a>.</li>  <li>For best results, you should enable graphite font rendering on firefox. Directions are available <a href='http://scripts.sil.org/cms/scripts/page.php?site_id=projects&item_id=graphite_firefox'>here</a>.  <li>You also need Unicode 6.0 compliant Myanmar fonts.</li>  <ul>   <li>The Padauk Myanmar font is available free of charge on the <a href='http://scripts.sil.org/cms/scripts/page.php?site_id=nrsi&id=Padauk'>SIL International Website</a>. Simply download the Padauk file, unzip the files, and install in your fonts folder.</li>   <li>A version of the Padauk font stylized for Sgaw Karen is also freely available <a href='http://www.openroad.net.au/languages/seasian/sgawkaren/'>here</a>. Download the PadaukSgaw file, unzip the files, and install in your fonts folder.</li>  </ul>  </ol>"

var topUnsupportedMessage = "<p><em class='warning'>Warning!</em><br>Your setup does not support Karen text. You will not be able to read Karen text on this page.</p>" + requirementsList + "<p><a class='hideMessageLink' href=\"javascript:{myUnicode.hide(document.getElementById('topUnsupportedMessage')); }\">Hide this message.</a></p>"

var bottomUnsupportedMessage = "<p><em class='warning'>Warning!</em><br>Your setup does not support Karen text. Scripts have attempted to convert the Karen Unicode text information on this page to images; however, for best results, you should consider upgrading your system.</p>" + requirementsList + "<p><a class='hideMessageLink' href=\"javascript:{myUnicode.hide(document.getElementById('bottomUnsupportedMessage')); }\">Hide this message.</a></p>"

function MyNodeParser(node) {
    this.node = node;
}

MyNodeParser.prototype.parse = function() {
    var node = this.node;
    if (node == undefined || node.tagName == undefined || node.nodeType != 1) return;
        myUnicode.nodeCount++;
        var tag = node.tagName.toLowerCase();
        if (node.tagName.toLowerCase() == "input")
        {
                //if (node.getAttribute("type") && node.getAttribute("type").toLowerCase() == "text")
                //{
                    //myUnicode.addOverlay(node);
                //}
                return; // don't mess with fields
        }
        else if (node.tagName.toLowerCase() == "textarea")
        {
             //myUnicode.addOverlay(node);
             return; // don't mess with fields
        }
        else if (node.tagName.toLowerCase() == "option")
        {
             return; // don't mess with fields
        }
        else if (node.tagName.toLowerCase() == "svg")
        {
             return; // already processed
        }
        else if (node.tagName.toLowerCase() == "shape")
        {
             return; // already processed
        }
        else if (node.tagName.toLowerCase() == "canvas")
        {
             return; // already processed
        }
        else if (node.hasChildNodes())
        {
            
            var children = node.childNodes;
            var nodeCount = children.length;
            for (var i = 0; i < children.length; i++)
            {
                var child = children.item(i);
                if (child.nodeType == 3)//Node.TEXT_NODE
                {
                    myUnicode.parseText(child, new String(child.data));
                }
                else if (child.nodeType == 1)//Node.ELEMENT_NODE
                {
                    // nodes with ids can be parsed in batches to avoid 
                    // timeouts on long documents
                    if (myCommon.getId(child) != "myDebug" && node.getAttribute("class") != "tlsNoConvert")
                      myUnicode.queueNode(new MyNodeParser(child));
                }
                else if (child.nodeType == 8) {} // ignore comments
                else if (child.nodeType == 7) {} // ignore processing instructions
                else
                {
                    alert("node " + child + " type" + child.nodeType);
                }
                if (node.childNodes.length > nodeCount)
                {
                    children = node.childNodes;
                    // nodes were inserted
                    i += children.length - nodeCount;
                    nodeCount = children.length;
                }
                else if (node.childNodes.length < nodeCount)
                {
                    alert(nodeCount + " lost nodes " + node.childNodes.length);
                }
                else
                {
                    children = node.childNodes;
                }
            }
        }
}

function TlsMyUnicode() {
    this.browserSupportsUnicode = false
    // config variables
    this.fontNames = "PadaukOT, Padauk, Myanmar3, Parabaik, 'MyMyanmar Unicode'";
    this.fontData = "Padauk";// can be overridden
    this.imageFonts = new Object();
    this.svgFont = "Padauk";
    this.canvasFont = undefined;
    this.codeStart = 4096;// u1000 - inclusive
    this.codeEnd   = 4256;//u10A0 - exclusive
    this.imgPrefix = "";// prefix path to images
    this.imgSuffix = ".png";// image extension
    // index of font size to use in fontImage array e.g. if images exist for {10,12,14,16}pt
    this.defaultFont = 1;// index of default font
    this.h1Font = 2;
    this.h2Font = 2;
    this.h3Font = 1;
    this.h4Font = 1;
    this.thFont = 2;
    this.supFont = 1;// smallest index
    this.subFont = 1;
    // end user config variables
    this.overlayCount = 0;
    this.currentNode = null;
    this.nodeCount = 0;
    this.isIe= false;
    this.isGecko= false;
    this.isOpera= false;
    this.retryCount= 0;
    this.queue = new Array();
    this.parseCount = 0;
    this.noticeNodeOffset = 8;
    this.conversionCount = 0;
    this.countThreshold = 5;
    this.debug = function() { return document.getElementById("myDebug");};
    return this;
}

/** initialise the img location, check for unicode support and convert to 
* images if needed 
* This is designed to be called in the onload function of body.
* @param imgPrefix path (relative or absolute) to the PadaukOT.js file.
*/
TlsMyUnicode.prototype.initParse = function(theImgPrefix) {
        var userAgent = navigator.userAgent.toLowerCase();
        this.isKonqueror = (userAgent.indexOf('konqueror') != -1);
        this.isGecko = (userAgent.indexOf('gecko') != -1);
        this.isIe = (userAgent.indexOf("msie")!=-1);
		  if (this.isIe || this.isKonqueror) return;
        this.isOpera = (userAgent.indexOf("opera")!=-1);
        this.addScript(theImgPrefix + "myParser.js");
        this.addScript(theImgPrefix + "canvas/tlsFont.js");
        if (this.isIe) {
            this.addScript(theImgPrefix + "excanvas/excanvas.js");
            this.addScript(theImgPrefix + "canvas/tlsVmlFont.js");
	} else {
            this.addScript(theImgPrefix + "canvas/tlsCanvasFont.js");
	}
        this.addScript(theImgPrefix + "svg/" + this.fontData + ".js");
        this.addScript(theImgPrefix + "svg/" + this.fontData + "Rendered.js");
        this.imgPrefix = theImgPrefix;
		  this.parseDoc();
        this.retryCount = 0;
        if (this.isOpera) this.countThreshold = 0;
    };

TlsMyUnicode.prototype.addScript= function(src) {
        var head = document.getElementsByTagName("head")[0];
        var scripts = head.getElementsByTagName("script");
        for (var i = 0; i < scripts.length; i++)
            if (scripts[i].getAttribute("src") == src) return;
        var script = document.createElement("script");
        script.setAttribute("type","text/javascript");
        script.setAttribute("src", src);
        head.appendChild(script);
    };

/** normal entry point to start conversion from unicode to images */
TlsMyUnicode.prototype.parseDoc = function() {
	try {
	// wait for the script additions to take affect
	//if (mySvgFont.hasFontData(this.svgFont) == false)
		if (tlsFontCache.hasFont(this.fontData) == false) {
			setTimeout("myUnicode.parseDoc()", 500);
			this.retryCount++;
			return;
		}
	} catch (notDefException) {
		setTimeout("myUnicode.parseDoc()", 500);
		this.retryCount++;
		return;
	}

	this.nodeCount = 0;
	this.parseDocWorker();
};

/** call back from parseDoc */
TlsMyUnicode.prototype.parseDocWorker = function () {
        this.parseNode(document.getElementsByTagName("body").item(0));
    };

TlsMyUnicode.prototype.parseNextNode = function() {
        if (this.queue.length > 0)
        {
            var nParser = this.queue[0];
            nParser.parse();
            this.parseCount++;
            this.queue.shift();
            if (this.conversionCount > this.countThreshold || this.parseCount > 100)
            {
                this.conversionCount = 0;
                this.parseCount = 0;
                setTimeout("myUnicode.parseNextNode()",1);
            }
            else 
                this.parseNextNode();
        }
        else
        {
            this.conversionCount = 0;
            this.parseCount = 0;
        }
    };

/** parse an element node and all its children */
TlsMyUnicode.prototype.parseNode = function (node) {
        this.queueNode(new MyNodeParser(node));
    };

/** callback from MyNodeParser */
TlsMyUnicode.prototype.queueNode = function (nodeParser) {
        this.queue.push(nodeParser);
        if (this.queue.length == 1)
            this.parseNextNode();
    };

/** tests whether the code point is in the range where images may be needed */
TlsMyUnicode.prototype.inRange = function(code) {
        if (code == 0x25cc) return true; // hack for dotted circle
        if ((code >= this.codeStart) &&
            (code < this.codeEnd))
            return true;
        return false;
    };

/** parses a text node and converts it to images if required */
TlsMyUnicode.prototype.parseText = function(node, text) {
        if (text == undefined) return;

        var docFrag = undefined;
        var lastMatchEnd = -1;
        var codeString = "u";
        var lastOutput = 0;
        var width = 0;
        var height = 0;
        var fontSize = 0;
        var maxCharLen = 12;
        var sizeIndex = 0;
        for (var i = 0; i < text.length; i++)
        {
            var code = text.charCodeAt(i);
            
            if (this.inRange(text.charCodeAt(i)))
            {
                if (typeof docFrag == "undefined")
                {
                    docFrag = document.createDocumentFragment();
                    var prefix = document.createTextNode(text.substring(0,i));
                    docFrag.appendChild(prefix);
                    this.conversionCount++;
                    // these don't change between strings, so set them once
                    if (typeof tlsFontCache != "undefined" && tlsFontCache.hasFont(this.fontData))
                    {
                        if (!this.canvasFont)
                        {
                            TlsDebug().print("Loaded font: " + this.fontData);
                            if (this.isIe)
                                this.canvasFont = new TlsVmlFont(tlsFontCache[this.fontData]);
                            else
                                this.canvasFont = new TlsCanvasFont(tlsFontCache[this.fontData]);
                        }
                    }
                }
                    var j;
                    for (j = i + 1; j < i + text.length; j++) {
                        code = text.charCodeAt(j);
                        if (this.inRange(code) == false) break;
                        if (typeof myParser != 'undefined' && myParser.canBreakAfter(text, j - 1)) {
                            break;
                        }
                    }
                    try {
                        var textColor = document.fgColor;
                        var backColor = document.bgColor;

                        if (typeof this.canvasFont != "undefined")
                        {
                            var computedStyle = this.canvasFont.computedStyle(node.parentNode);
                            //TlsDebug().dump(computedStyle,2);
                            if (computedStyle && computedStyle.color)
                            {
                                textColor = computedStyle.color;
                                if (this.isIe) 
                                {
                                    if (computedStyle.backgroundColor)
                                        backColor = computedStyle.backgroundColor;
                                    else
                                        backColor = "#fff";
                                }
                            }
                            else if (node.parentNode.style.color.specified)
                                textColor = node.parentNode.style.color;

                            var fontSize = this.canvasFont.nodeFontSize(node.parentNode) * 1;
                            TlsDebug().print("fontsize:" + fontSize + " computed:" +
                                computedStyle.fontSize);
                            if (this.canvasFont.appendText(docFrag, fontSize, text.substring(i,j), textColor, undefined))//(this.isIe)?backColor:undefined
                            {
//                                 var enableTextCopy = document.createElement("img");
//                                 enableTextCopy.setAttribute("src",this.imgPrefix + "null.gif");
//                                 enableTextCopy.setAttribute("alt",'')//enableTextCopy.setAttribute("alt",text.substring(i,j));
//                                 enableTextCopy.style.width = enableTextCopy.style.height = "0px";
//                                 enableTextCopy.style.borderStyle = "none";
//                                 docFrag.appendChild(enableTextCopy);
                                i = j - 1;
                            }
                            else return;// something failed, best not to replace anything
                        }
                    }
                    catch (e) 
                    { 
                        if (typeof TlsDebug != "undefined")
                            TlsDebug().print("Exception:" + e + 
                                ((e.description)? e.description + e.line : "")); 
                    }

            }
            else if (docFrag != undefined)
            {
                var normalText = document.createTextNode(text.substring(i,i+1));
                docFrag.appendChild(normalText);
            }
        }
        // replace with new text
        if (docFrag != undefined)
        {
            var parent = node.parentNode;
			if (this.isIe && parent.getAttribute("href"))
			{
				for (var j = 0; j < docFrag.childNodes.length; j++)
				{
					if (docFrag.childNodes[j].nodeType != 1) continue;
					for (var k = 0; k < docFrag.childNodes[j].childNodes.length; k++)
					{
						var sNode = docFrag.childNodes[j].childNodes[k];
						if (sNode.nodeType != 1) continue;
						if (sNode.tagName.toLowerCase().indexOf("shape") > -1)
							sNode.setAttribute("href", parent.getAttribute("href"));
					}
				}
			}
            if (parent) parent.replaceChild(docFrag, node);
        }
    };

/** Choose which image size to use for the given node type 
* This could be modified to look at the specified style, or class on the
* node, but currently, this is ignored.
*/
TlsMyUnicode.prototype.chooseFontIndex = function(node) {
    var index = this.defaultFont;
    if (!node.tagName) return index;
        var elementName = node.tagName.toLowerCase();
        while (elementName == "a" || elementName == "span" || 
               elementName == "b" || elementName == "i" || elementName == "emph")
        {
            node = node.parentNode;
            elementName = node.tagName.toLowerCase();
        }
        if (elementName == "h1") index = this.h1Font;
        else if (elementName == "h2") index = this.h2Font;
        else if (elementName == "h3") index = this.h3Font;
        else if (elementName == "h4") index = this.h4Font;
        else if (elementName == "sup") index = this.supFont;
        else if (elementName == "sub") index = this.subFont;
        else if (elementName == "th") index = this.thFont;
        //else if (elementName == "dt") index = this.thFont;
        return index;
    }


TlsMyUnicode.prototype.myUnicodeCheck = function() {
   isSupported = true;
   var myAW1 = document.getElementById('myTestAWidth1');
   var myAW2 = document.getElementById('myTestAWidth2');
   var myBW1 = document.getElementById('myTestBWidth1');
   var myBW2 = document.getElementById('myTestBWidth2');
   var myC = document.getElementById('myTestC');
   var myD = document.getElementById('myTestD');
   if (myAW1 && myAW2 && myBW1 && myBW2 && myC && myD) {
   var myAW1Width = myAW1.offsetWidth;
   var myAW2Width = myAW2.offsetWidth;
   var myBW1Width = myBW1.offsetWidth;
   var myBW2Width = myBW2.offsetWidth;
   var myCWidth = myC.offsetWidth;
   var myDWidth = myD.offsetWidth;
   if (myAW1Width == undefined) myAW1Width = myAW1.width;
   if (myAW2Width == undefined) myAW2Width = myAW2.width;
   if (myBW1Width == undefined) myBW1Width = myBW1.width;
   if (myBW2Width == undefined) myBW2Width = myBW2.width;
   if (myCWidth == undefined) myCWidth = myC.width;
   if (myDWidth == undefined) myDWidth = myD.width;

   if (myAW1Width >= 0.75 * myAW2Width || myAW1Width == undefined || myAW2Width == undefined) {
   isSupported = false;
   }

   if (myBW2Width > 1.2 * myBW1Width || myBW1Width == undefined || myBW2Width == undefined) {
   isSupported = false;
   }

   if (myCWidth > 1.5 * myBW1Width || myBW1Width == undefined || myCWidth == undefined) {
   isSupported = false;
   }

   if (myDWidth > 1.6 * myBW1Width || myBW1Width == undefined || myDWidth == undefined) {
   isSupported = false;
   }
   this.browserSupportsUnicode = isSupported
   return isSupported;

   } else {
   return false;
   }
};

TlsMyUnicode.prototype.show = function(element) {
   if (!document.getElementById || element == null)
      return
   element.style.display="block"
};

TlsMyUnicode.prototype.hide = function(element) {
   if (!document.getElementById || element == null)
      return
   element.style.display="none"
};

TlsMyUnicode.prototype.main = function(scriptsDir) {
   this.show(document.getElementById('myUniTest'));
   if (!this.myUnicodeCheck()) {
      this.hide(document.getElementById('myUniTest'));
      this.initParse(scriptsDir);
      if (this.isIe || this.isKonqueror) {
			document.getElementById('topUnsupportedMessage').innerHTML = topUnsupportedMessage
			this.show(document.getElementById('topUnsupportedMessage'));
      } else {
			document.getElementById('bottomUnsupportedMessage').innerHTML = bottomUnsupportedMessage
			this.show(document.getElementById('bottomUnsupportedMessage'));
      }
   } else {
      this.hide(document.getElementById('myUniTest'));
   }
};
