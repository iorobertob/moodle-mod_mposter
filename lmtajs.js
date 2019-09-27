
var audioPlayer = document.getElementById("audiofile");

audioPlayer.addEventListener("timeupdate", function(e)
{
	syncData.forEach(function(element, index, array)
	{
	    if( audioPlayer.currentTime >= 0.5 && audioPlayer.currentTime <= 1.0 )
	        console.log("time stamp");
	});
});

// Draggable elements by class name, from their title element
var cardClassName  = "card-block";
// In the newer version of Fordson theme the name of the html element has changed...
cardClassName = "card-body";
var titleClassName = "card-title";
var blockClassName = "block";
var blockList = document.getElementsByClassName(blockClassName);
var posterColumnOne = document.querySelector('#mod_poster-content > div > div:first-child');
var posterColumnTwo = document.querySelector('#mod_poster-content > div > div:nth-child(2)');

for ( var i = 0; i < blockList.length; i++){
    var cardElement  = blockList[i].getElementsByClassName(cardClassName) [0];
    var titleElement = blockList[i].getElementsByClassName(titleClassName)[0];
    
    // Enable resizing
    //cardElement.style.resize   = 'both';
    //cardElement.style.overflow = 'auto';
    //blockList[i].style.overflow = 'visible';
    blockList[i].style.resize = 'both';
    blockList[i].style.overflow = 'auto';
    
    
    // Enable dragging
    dragElement(cardElement, blockList[i], titleElement);
}

if (posterColumnOne && posterColumnTwo) {
   // posterColumnOne.style.resize   = 'horizontal';
   // posterColumnOne.style.overflow = 'auto';
    console.log(posterColumnOne.className);
    // Store initial widths
    posterColumnOne.dataset.initialSize = posterColumnOne.offsetWidth;
    posterColumnTwo.dataset.initialSize = posterColumnTwo.offsetWidth;

    // TODO: synchronize column widths during resize (drag)
    // onresize seems to only work on window, outside of IE :/
    posterColumnOne.onresize = function() {
        console.log(e);
   }
}



function dragElement(elmnt, block, titleElement) {
  var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;

  if (titleElement){
    /* if present, the header is where you move the DIV from:*/
    titleElement.onmousedown = dragMouseDown;
  } else {
    /* otherwise, move the DIV from anywhere inside the DIV:*/
    elmnt.onmousedown = dragMouseDown;
  }

  function dragMouseDown(e) {
    e = e || window.event;
    e.preventDefault();
    // get the mouse cursor position at startup:
    pos3 = e.clientX;
    pos4 = e.clientY;
    document.onmouseup = closeDragElement;
    // call a function whenever the cursor moves:
    document.onmousemove = elementDrag;
  }

  function elementDrag(e) {
   // elmnt.style.position = 'relative';
   // block.style.position = "relative";

    e = e || window.event;
    e.preventDefault();
    // calculate the new cursor position:
    pos1 = pos3 - e.clientX;
    pos2 = pos4 - e.clientY;
    pos3 = e.clientX;
    pos4 = e.clientY;

    var yint = parseInt($(block).css('top'),  10);
    var xint = parseInt($(block).css('left'), 10);
    block.style.top  = (yint - pos2) + "px";
    block.style.left = (xint - pos1) + "px";
 }

  function closeDragElement() {
    /* stop moving when mouse button is released:*/
    document.onmouseup = null;
    document.onmousemove = null;

    block.style.resize = 'both';
    block.style.overflow = 'auto';
    //block.style.overflow = 'scroll';
    console.log("click");
  }
}



