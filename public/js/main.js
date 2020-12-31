$( document ).ready(function() {

	$("img").one("load", function() {
			$(this).fadeIn(1000);
		}).each(function() {
		  if(this.complete)
			{
				$(this).load();
			}
		});


	


	$( ".cart-hover" ).hover(function() {
		  $( this ).find(".cart-elements").show(200); }
	     , function(){
	    	 $( this ).find(".cart-elements").hide();
		});


	$(".lens-pic").imageLens({ lensSize: 300 });

	$(".thumb-pic").click(function(){
		new_src=$(this).attr("src");
		old_src=$(".lens-pic").attr("src");
		$(".lens-pic").attr("src",new_src);
		$(this).attr("src",old_src);
		$( ".lens-pic").unbind();
		$(".lens-pic").imageLens({ lensSize: 300});
	});


	$(".size-ctl").change(function(){
		window.location.href = $(this).val();
	});

	$(".cart-btn").click(function(){
		window.location.href="/cart";
	});


});


function create_token()
{

	paymill.createToken({
		  number:         $('.card-number').val(),       // required
		  exp_month:      $('.card-expiry-month').val(), // required
		  exp_year:       $('.card-expiry-year').val(),  // required
		  cvc:            $('.card-cvc').val(),          // required
		  amount_int:     $('.amount').val(),   // required, e.g. "4900" for 49.00 EUR
		  currency:       $('.currency').val(),          // required
		  cardholder:     $('.card-holdername').val()    // optional
		},
		paymillResponseHandler);

}


function paymillResponseHandler(error, result) {

	 if (error) {
	   // Displays the error above the form
	   $(".payment-errors").text(error.apierror);
	   $(".payment-errors").addClass("alert");
	   $(".payment-errors").addClass("alert-danger");
	 } else {

	   // Output token
	   var token = result.token;
	   $("#token").val("");
	   $("#token").val(token);
	   $(".payfrm").submit();// Insert token into form in order to submit to server

	   // Submit form

	 }
	}
function OrderPlaced()
{
	$(".order-btn").hide();
	$("#wait-msg").show();
  $("#submit-frm").submit();
}
