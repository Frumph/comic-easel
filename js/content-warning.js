//Copyright 2014-2016 Kisai, please ask permission to use.
//This script creates a "content warning" blur on graphic images.
console.log("content Warning: Make sure you use Chrome/Safari or Firefox");
if(contentwarningtext != ""){
var storecomic="";
var storetitle="";
var nextcomic="";
var alreadyremoved=false;
//WP Easel shim
if(!document.getElementById("g-comic"))
{
	if(document.getElementById("comic") )
	{
		var whereiscomic=document.getElementById("comic").childNodes;
		
		for(i=0; i < whereiscomic.length;i++){
			console.log(whereiscomic[i].tagName);
			if(whereiscomic[i].tagName=="img" || whereiscomic[i].tagName=="IMG")
			{
				whereiscomic[i].setAttribute("id","g-comic");

			}
			if(whereiscomic[i].tagName=="a" || whereiscomic[i].tagName=="A")
			{
				whereiscomic[i].firstChild.setAttribute("id","g-comic");

			}
		}
	}
}

//Code that does stuff
if(document.getElementById("g-comic") )
{
    storecomic=document.getElementById("g-comic").src;
    storetitle=document.getElementById("g-comic").title;
    nextcomic=document.getElementById("g-comic").parentNode.href;
	document.getElementById("g-comic").parentNode.href="#";

    document.getElementById("g-comic").style.cssText="-webkit-filter: blur(20px); -moz-filter: blur(20px);    -o-filter: blur(20px);    -ms-filter: blur(20px);    filter: blur(20px);";
    document.getElementById("g-comic").addEventListener("click",altcomic);
	
	var contentwarning=document.createElement('div');
	contentwarning.innerHTML = '<p>' + contentwarningtext + '</p>';
	contentwarning.setAttribute("id","contentwarningimg");
	contentwarning.addEventListener("click",altcomic);
	document.getElementById("g-comic").parentNode.appendChild(contentwarning);

}
}

function altcomic()
{

    if(alreadyremoved==true)
    {
        location.href=nextcomic;
    }

    if(document.getElementById("g-comic") && storecomic !="" && alreadyremoved==false)
    {
        document.getElementById("contentwarningimg").parentNode.removeChild(document.getElementById("contentwarningimg"));

	document.getElementById("g-comic").src=storecomic;
	document.getElementById("g-comic").title=storetitle;
    document.getElementById("g-comic").style.cssText="";
    alreadyremoved=true;
    }


}