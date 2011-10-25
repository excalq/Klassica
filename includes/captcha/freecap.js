function new_freecap() {
  // loads new freeCap image
  if(document.getElementById)
  {
    // extract image name from image source (i.e. cut off ?randomness)
    theSrc = document.getElementById("captcha_img").src;
    theSrc = theSrc.substring(0,theSrc.lastIndexOf(".")+4);
    // add ?(random) to prevent browser/isp caching
    document.getElementById("captcha_img").src = theSrc+"?"+Math.round(Math.random()*100000);
  } else {
    alert("Sorry, cannot autoreload freeCap image\nSubmit the form and a new freeCap will be loaded");
  }
}