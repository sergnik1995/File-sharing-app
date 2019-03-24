let form = document.querySelector(".my-form");
let progressBlock = document.querySelector(".progress");
let progressBar = document.querySelector(".progress-bar");

form.onsubmit = function(event) {
  event.preventDefault();
  let file = document.getElementById("inputGroupFile04");

  if(!file.value) {
  	alert("Выберите файл!");
  	return false;
  }

  let formData = new FormData( form );
  let xhr = new XMLHttpRequest();

  xhr.upload.onloadstart = function() {
  	progressBlock.style.display = "";
  	progressBar.style.width = "0%";
  };

  xhr.upload.onprogress = function(e) {
  	progressBar.style.width = Math.ceil(e.loaded / e.total * 100) + "%";
  };

  xhr.upload.onload = function() {
  	progressBar.style.width = "100%";
  };

  xhr.onreadystatechange = function() {
    if (this.readyState == this.HEADERS_RECEIVED) {
      console.log(this.responseText);
      alert(this.getResponseHeader("Location"));
	  window.location = this.getResponseHeader("Location");
	}
  };

  xhr.open("POST", "/");
  xhr.send( formData );
}

