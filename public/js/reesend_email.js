$(function(){
	
	$("#resend-email").on("click", function(){
		$.post("../Controller/controller_forms.php", 
		{
			"type-form": "recovery_password",
			email: email
		}, function(answer){
			if(answer.state== 1){
				console.log(answer.message);
			}else{
				alert(answer.message);
			}
		});
	});
});