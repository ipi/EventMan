jQuery(document).ready(function($)
{	
$(".tfdate").datepicker({
    dateFormat: 'D, M d, yy',
    showOn: 'button',
    buttonImage: 'wp-content/plugins/eventman/images/icon-datepicker.png',
    buttonImageOnly: true,
    numberOfMonths: 3
    });
});