/* 
lylina news aggregator

Copyright (C) 2006 Eric Harmon

Contains code from 'lilina':
Copyright (C) 2004-2005 Panayotis Vryonis

lylina is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

lylina is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with lylina; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

function SetCookie(cookieName, cookieData) {
	var expires = new Date ();
	expires.setTime(expires.getTime() + 31 * (24 * 60 * 60 * 1000));
	document.cookie = cookieName + "=" + escape(cookieData) + "; expires=" + expires.toGMTString();
} 

function GetCookie(name) {
    var dc = document.cookie;
    var prefix = name + "=";
    var begin = dc.indexOf("; " + prefix);
    if (begin == -1)
    {
        begin = dc.indexOf(prefix);
        if (begin != 0) return null;
    }
    else
    {
        begin += 2;
    }
    var end = document.cookie.indexOf(";", begin);
    if (end == -1)
    {
        end = dc.length;
    }
    return unescape(dc.substring(begin + prefix.length, end));
}

// Custom Event Handling
document.onclick = EVT_Click;

function EVT_Click(evt) {
	evt = (evt) ? evt : ((window.event) ? event : null ) ;
	var target = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null ) ;
	OBJ_Click(target) ;
	evt.cancelBubble ;
}

function OBJ_Click(target) {
	if (target.id) {
		ObjectID = target.id ;
		ObjectType = ObjectID.substring(0,5) ;
		ObjectNyyum = ObjectID.substring(6,38) ; // Asumption that len(number)<20 ... 
		switch(ObjectType) {
		case 'TITLE' :
			ItemId = getItemId(target) ;
			ItemShowHide(ItemId) ;
			return ;
			break ;
		case 'IMARK' :
			ItemId = getItemId(target) ;
			setMark(ItemId) ;
			return ;
		default:
			break ;
		}
	}
	if (!target.parentNode) {
		return ;
	} else {
		OBJ_Click(target.parentNode) ;	
	}
}

function Obj_findStyleValue(target,styleProp, IEStyleProp) {
	if (target.currentStyle) return target.currentStyle[ IEStyleProp ] ;
	else if (window.getComputedStyle) {
		compStyle = window.getComputedStyle(target,'') ;
		return compStyle.getPropertyValue(styleProp) ;
	}
}

function getItemId(obj) {
	if (obj.id && obj.id.substring(0,5)=='IITEM' ) {
		return obj.id.substring(6,38) ;
	}
	if (!obj.parentNode) return null;
	return getItemId(obj.parentNode) ;
}
function ItemShowHide(id) {
	var item = document.getElementById('IITEM-'+id) ;
	var i ;
	for (i=0; i<item.childNodes.length; i++)
	if (item.childNodes[i].id && item.childNodes[i].id.substring(0,5)=='ICONT') {
		content = item.childNodes[i] ;
		break ;
	}
	status =  Obj_findStyleValue(content,'display','display')  ;
	if (status!='block') {
		content.style['display'] = 'block' ;
		item.style['background'] = Obj_findStyleValue(document.getElementById('c2'),'color','color') ;
	} else {
		content.style['display'] = 'none' ;
		item.style['background'] = Obj_findStyleValue(document.getElementById('c1'),'color','color') ;
	}
}
function getMarkObj(element) {
	var i ;
	if (!element) return null ;
	if (element.id && element.id.substring(0,5)=='IMARK') return element ;
	if (!element.childNodes) return null ;

	for (i=0; i<element.childNodes.length; i++) {
		itm = getMarkObj(element.childNodes[i]) ;
		if (itm) return itm ;
	}
}
function getMarkById(id) {
	var item = document.getElementById('IITEM-'+id) ;
	return getMarkObj(item) ;
}
function setMark(id) {
	var item ;
	if (!markID) { markID=id ; }
	if (item = getMarkById(markID) ) {
		item.setAttribute( 'src', 'i/mark_off.gif' );
	}
	if (item = getMarkById(id) ) {
		item.setAttribute( 'src', 'i/mark_on.gif' );
	}
	markID = id ;
	SetCookie('mark', markID) ;
}
function SourcesSetShowHide(show) {
	var item = document.getElementById('sources') ;
	if (show) {
		item.style['display'] = 'block' ;
	} else {
		item.style['display'] = 'none' ;
	}
}


function ItemSetShowHide(id,show) {
	var item = document.getElementById('IITEM-'+id) ;
	var i ;
	for (i=0; i<item.childNodes.length; i++)
	if (item.childNodes[i].id && item.childNodes[i].id.substring(0,5)=='ICONT') {
		content = item.childNodes[i] ;
		break ;
	}
	if (show) {
		content.style['display'] = 'block' ;
		item.style['background'] = Obj_findStyleValue(document.getElementById('c2'),'color','color') ;
	} else {
		content.style['display'] = 'none' ;
		item.style['background'] = Obj_findStyleValue(document.getElementById('c1'),'color','color') ;
	}
}

function visible_mode(display) {
        var main = document.getElementById('main') ;
        var i ;
        var j ;
        var items ;
	var feed ;
        for (i=0; i<main.childNodes.length; i++) {
		feed = main.childNodes[i] ;
                for (j=0; j<feed.childNodes.length; j++) {
                        if (feed.childNodes[j].id && feed.childNodes[j].id.substring(0,5)=='IITEM') {
                                itemID = feed.childNodes[j].id.substring(6,38) ;
                                ItemSetShowHide(itemID,display) ;
                        }
                }
	}
        showDetails = display ;         SetCookie('showDetails', showDetails) ;
}

function toggleStyle(StyleName) {
	object = document.getElementById(StyleName) ;
	if (object.disabled==true) object.disabled=false ;
	else object.disabled=true;
}
function visible_mode_toggle() {
	visible_mode(!showDetails) ;
}

function furlPost(URL,title) {
	q=URL;
	p=title;

	d=document;
	t=d.selection?(d.selection.type!='None'?d.selection.createRange().text:''):(d.getSelection?d.getSelection():'');
	void(furlit=window.open('http://www.furl.net/storeIt.jsp?t='+escape(title)+'&u='+escape(URL)+'&c='+escape(t),'furlit','scrollbars=no,width=475,height=575,left=75,top=20,status=no,resizable=yes'));furlit.focus();
}


