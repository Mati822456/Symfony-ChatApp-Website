const container = document.querySelector(".container");
const pwShowHide = document.querySelectorAll(".showHidePw");
const pwFields = document.querySelectorAll(".password");

pwShowHide.forEach(eyeIcon =>{
    eyeIcon.addEventListener("click", ()=>{
        pwFields.forEach(pwField =>{
            if(pwField.type ==="password"){
                pwField.type = "text";

                pwShowHide.forEach(icon =>{
                    icon.classList.replace("fa-eye-slash", "fa-eye");
                })
            }else{
                pwField.type = "password";

                pwShowHide.forEach(icon =>{
                    icon.classList.replace("fa-eye", "fa-eye-slash");
                })
            }
        }) 
    })
})

   