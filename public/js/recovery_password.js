$(function(){
	
	$(".form-recovery").on("input", function(){
		$(".alert").addClass("d-none");
	});
	
	$(".form-recovery").on("submit", function(event){	
		event.preventDefault();
		
		let newPassword= $("#password").val().trim();
		let rePassword= $("#re-password").val().trim();	
		
		if(! (newPassword== rePassword)){
			
			$(".alert").removeClass("d-none");
			$(".text").html("Las contraseñas no coinciden");			
			return;
		}	
		
		$.post("../Controller/controller_forms.php", 
			{
				"type-form": "updatePassword",
				newPassword: newPassword,
				rePassword: rePassword,
				email: email,
				token: token
			}, function (answer){
				if(answer.state== 1){
					$(".alert").removeClass("d-none");
					$(".text").html(answer.message);
				}else{
					$(".alert").removeClass("d-none");
					$(".text").html(answer.message);
				}
		});
	});
});