let failedEmail = "";
let failedPass = "";
let messageError= "";

$(function() {		

	//Login
	$(".form-login").on("submit", function(event) {
		event.preventDefault();
		let email= $('#email').val().trim();
		let password= $('#password').val().trim();		
		
		if(email== "" || password== "") {
			$(".container-alert").addClass("container-alert-danger");
			$(".alert-text").html("Todos los campos son obligatorios");
		}else{
			$.post('login', {
				email: email,
				password: password
			}, function(answer) {
				if(answer.state== 1){
					window.location.href= answer.redirect;			
				}else{
					failedEmail= email;
					failedPass= password;
					messageError= answer.message;
					$(".input").addClass("input-error");
					$(".container-alert").addClass("container-alert-danger");
					$(".alert-text").html(messageError);
				}
			});
		}
	});
	
	
	$(".input").on("input", function(){
		let currentEmail= $("#email").val().trim();
		let currentPass= $("#password").val().trim();		
		
		if(currentEmail== failedEmail && currentPass== failedPass){
			$(".input").addClass("input-error");
			$(".container-alert").removeClass("container-alert-success").addClass("container-alert-danger");
			$(".alert-text").html(messageError);
		}else{
			$(".input").removeClass('input-error');
			$(".container-alert").removeClass("container-alert-danger container-alert-success");
			$(".alert-text").html("");
		}		
	});		
	
	const inputs= [
		"#names", 
		"#last-names", 
		"#id-number",
		"#email-sena",
		"#password-user",
		"#re-password"
	];
	
	$(".btns-close").on("click", function(){
		$(".container-alert").removeClass("container-alert-success container-alert-danger");
		$(".alert-text").html("");
		$(".form-control").removeClass("input-error");
		inputs.forEach(function(idInput){
			$(idInput).val("");
		});
	});	
	
    //Script Registro	
	$("#signup-button").on("click", function(event){
		event.preventDefault();
		let inputsEmpty= false;
		let firstInputEmpty= null;	
		
		inputs.forEach(function(idInput){
			if($(idInput).val().trim()=== ""){
				inputsEmpty= true;				
				
				if(!firstInputEmpty){
					firstInputEmpty= idInput;
					$(firstInputEmpty).addClass("input-error");	
				}
			}else{
				$(idInput).removeClass("input-error");
			}
		})
		
		if(inputsEmpty){
			$(".container-alert").addClass("container-alert-danger");
			$(".alert-text").html("Todos los campos son requeridos");
			
			$(firstInputEmpty).focus();
			
			$(".form-control").on("input", function(){
				$(this).removeClass("input-error");
			});
			return;
		}
		
		let password= $("#password-user").val().trim();
		let rePassword= $("#re-password").val().trim();

		if(password!== rePassword){
			$(".container-alert").removeClass("container-alert-success").addClass("container-alert-danger");
			$(".alert-text").html("Las contraseñas no coinciden");
			return; 
		} else {
			$(".container-alert").removeClass("container-alert-danger").addClass("container-alert-success");
			$(".alert-text").html("");
		}		
		
		console.log($("#centro").val().trim());
		
		$.post("register", {
			names: $("#names").val().trim(),
			lastNames: $("#last-names").val().trim(),
			idNumber: $("#id-number").val().trim(),
			emailSena: $("#email-sena").val().trim(),
			password: $("#password-user").val().trim(),
			rePassword: $("#re-password").val().trim(),
			idCentro: $("#centro").val().trim()
		}, function(answer){
			if(answer.state== 1){
				$(".container-alert").removeClass("container-alert-danger").addClass("container-alert-success");
				$(".alert-text").html(answer.message);
				try {
					const modalEl = document.getElementById('staticBackdrop');
					if (modalEl) {
						const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
						modalInstance.hide();
					}
				} catch(e) {
					$("#staticBackdrop").removeClass('show').attr('aria-hidden','true').css('display','none');
				}
				setTimeout(function(){
					$("body").removeClass('modal-open');
					$(".modal-backdrop").remove();
					$(".form-registro")[0]?.reset();
					$(".form-control").removeClass('input-error');
				}, 150);
			}else{
			
				$(".container-alert").addClass("container-alert-danger");
				$(".alert-text").html(answer.message);
			}			
		});
		

	});
	
	
	//Reestablecer Contraseña
	$(".remember_password").on("click", function(){		
		
		let email= $("#email").val().trim();
		
		function contieneCorreo(email) {
			const regexCorreo = /\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/;
			return regexCorreo.test(email);
		}
		
		if(!contieneCorreo(email)){
			$(".container-alert").addClass("container-alert-danger");
			$(".alert-text").html("Ingrese un correo valido");
			$("#email").addClass("input-error");
			$("#email").focus();
			return;
		};

		//window.location.href = "recovery?email=" + encodeURIComponent(email);
		if(email){
			$.post("/recovery",
				{
					email: email
				}, 
			function(answer){
				if(answer.state== 1){
					//window.location.href = "recovery?email=" + encodeURIComponent(email);
					$(".container-alert").removeClass("container-alert-success container-alert-danger");
					$(".container-alert").addClass("container-alert-success");
					$(".alert-text").html(answer.message);					
				}else{
					$(".container-alert").removeClass("container-alert-success container-alert-danger");
					$(".container-alert").addClass("container-alert-danger");
					$(".alert-text").html(answer.message);					
				}				
			});
		}else{
			$(".container-alert").addClass("container-alert-danger");
			$(".alert-text").html("Caja de texto sin datos");
		}		
	});
	
	//Scripts cargar departamento
	$("#departamento").on("change", function(){
		let idDepartamento= $(this).val();
		
		$.ajax({
			url: 'getCentro',
			type: "GET",
			data: { departamento: idDepartamento },
			dataType: "json",
			success: function(data) {
                let $centro = $("#centro");
                $centro.empty();                 
                
                $.each(data, function(index, centro) {
                    $centro.append('<option value="'+ centro.idCentro +'">'+ centro.centro +'</option>');
                });

            },error: function() {            
            	console.log("Error cargando centros");
            }
		});

	});
	
});