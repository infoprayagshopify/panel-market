<?php include 'header.php'; ?>

<div class="container-fluid">
   <div class="row">
      <div class="col-lg-12">
    <h1>Proxy Editing Area</h1>
    <hr>
    <div class="alert alert-info">Adding Sample: ["213.177.105.14:1080","212.192.202.207:4550"]<br>
    Do not forget to put the [ ] signs at the beginning and at the end</div>
   <div class="alert alert-warning">You can click <a href="https://free-proxy-list.net">HERE</a> for free IPV6 Proxy. Just write 1 of them.
   <hr>
<strong>  The proxy feature is OK if it doesn't work, it means that there is a problem with your proxy.</strong> </div>
<?php

if(!empty($_POST['icerik'])) 
{ 
  $yazdir = file_put_contents('app/hidden/proxylist.txt', $_POST['icerik']); 
  if(!$yazdir) 
    echo '<br><br><div class="alert alert-danger"><strong>BAŞARISIZ!</strong><br> Update Unsuccessful.<br> Redirecting in 3 Seconds....</div>
    <meta http-equiv="refresh" content="3;">'; 
  else 
    echo '<br><br><div class="alert alert-success"><strong>BAŞARILI!</strong><br> Update Successfuly.<br> Redirecting in 3 Seconds....</div>
    <meta http-equiv="refresh" content="3;">
'; 

  exit; 
} 

$icerik = file_get_contents('app/hidden/proxylist.txt'); 

echo ' 
<form action="" method="post"> 

<div class="editor">
  <div class="gutter"><span>1</span></div>
  <textarea name="icerik" rows="5">', $icerik, '</textarea>
</div>
<br>
<button class="btn btn-primary" type="submit">Update</button><br><br>
</form>';  
?>
</div></div>
</div>




<script src='//production-assets.codepen.io/assets/editor/live/console_runner-079c09a0e3b9ff743e39ee2d5637b9216b3545af0de366d4b9aad9dc87e26bfd.js'></script><script src='//production-assets.codepen.io/assets/editor/live/css_live_reload_init-2c0dc5167d60a5af3ee189d570b1835129687ea2a61bee3513dee3a50c115a77.js'></script><meta charset='UTF-8'><meta name="robots" content="noindex"><link rel="shortcut icon" type="image/x-icon" href="//production-assets.codepen.io/assets/favicon/favicon-8ea04875e70c4b0bb41da869e81236e54394d63638a1ef12fa558a4a835f1164.ico" /><link rel="mask-icon" type="" href="//production-assets.codepen.io/assets/favicon/logo-pin-f2d2b6d2c61838f7e76325261b7195c27224080bc099486ddd6dccb469b8e8e6.svg" color="#111" /><link rel="canonical" href="https://codepen.io/mohdule/pen/WpQbjx?limit=all&page=60&q=editor" />

<style class="cp-pen-styles">

.editor {
  margin-top: 2rem;
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -ms-flex-flow: row wrap;
      flex-flow: row wrap;
  min-width: 40vw;
  min-height: 50vh;
  background: grey;
  box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.5);
}
.editor .gutter {
  background: #11101f;
  color: grey;
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -ms-flex-flow: column wrap;
      flex-flow: column wrap;
  padding: 1rem;
}
.editor .gutter span {
  padding: 0.5rem;
  color: #62a9b9;
}
.editor textarea {
  background: #1a1930;
  -webkit-box-flex: 1;
      -ms-flex: 1;
          flex: 1;
  border: 0;
  color: white;
  padding: 1rem;
  line-height: 2;
}
.editor textarea:focus {
  outline: none;
}
</style>
<script src='//production-assets.codepen.io/assets/common/stopExecutionOnTimeout-b2a7b3fe212eaa732349046d8416e00a9dec26eb7fd347590fbced3ab38af52e.js'></script>
<script >var input = document.querySelector('textarea');
var gutter = document.querySelector('.gutter');
var val = input.value;
var i = 1;

input.addEventListener('input', update);

function update() {
	val = input.value;
	
	var lineBreaks = val.match(/\n/g);
	// alert('Hi');
	var numOfSpans = gutter.childElementCount;
	var numOfLines = lineBreaks.length + 1;
	
	
	
	if (numOfSpans < numOfLines) {
			var el = document.createElement('span');
			el.innerHTML = numOfLines;
			gutter.appendChild(el);
		}
	else if (numOfSpans > numOfLines){
			gutter.removeChild(gutter.childNodes[numOfLines]);
		}
	
}

update();
// Darn !! i can't get rid of the second span >_<
//# sourceURL=pen.js
</script>


<?php include 'footer.php'; ?>