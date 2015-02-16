$("#fileform").on('submit', function (event) {
	
	event.preventDefault();
	var button = $("#filepicker");
	console.log("Clicked on send button.");
	button.innerHTML = "Uploading ...";
	var sourcepath = $("#sourcepath").val();
	var exportname = $("#exportname").val();
	var libraries = $("#libraries").val();
	var keywords = $("#keywords").val();
	var assetid = $("#assetid").val();
	var accesskey = $('#accesskey').val();
	var files = document.getElementById("fileholder").files;

	var formData = new FormData();

	formData.append('sourcepath', sourcepath);
	formData.append('exportname', exportname);
	formData.append('libraries', libraries);
	formData.append('keywords', keywords);
	formData.append('assetid', assetid);
	formData.append('accesskey', accesskey);

	// Loop through each of the selected files.
	for (var i = 0; i < files.length; i++) {
 	 var file = files[i];

  	// Check the file type.
  	if (!file.type.match('image.*')) {
    	continue;
  	}

  	// Add the file to the request.
  	formData.append('file', file, file.name);
	}

	// Set up the request.
	var xhr = new XMLHttpRequest();

	xhr.open('POST', 'upload.php', false);
	
	// Set up a handler for when the request finishes.
	xhr.onload = function () {
  	if (xhr.status === 200) {
   	 		// File(s) uploaded.
   	 		button.innerHTML = 'Uploading';
  		} else {
			button.innerHTML = 'Re-Submit';
    		console.log('An error occurred!');
 		}
	};

	xhr.send(formData);
	console.log('Done Sent!');
});
