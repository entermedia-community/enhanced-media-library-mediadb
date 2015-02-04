$("#text-id").on( 'click', function () {
    var button = $('#sourcepath');
    var value = button.val();
    console.log('value: ', value);    
    $.ajax({
        type: 'post',
        url: 'upload.php',
        data: {
            sourcepath: value
        },
        success: function( data ) {
	    console.log('did: ', data);
        }
    });
});
