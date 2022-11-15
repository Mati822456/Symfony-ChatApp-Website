const body = document.querySelector('body');
const sidebar = body.querySelector('nav');
const toggle = body.querySelector(".toggle");

toggle.addEventListener("click" , () =>{
    sidebar.classList.toggle("close");
})

const searchBtn = body.querySelector(".search-box");

searchBtn.addEventListener("click" , () =>{
    sidebar.classList.remove("close");
})

const modeSwitch = body.querySelector(".toggle-switch");
const modeText = body.querySelector(".mode-text");

modeSwitch.addEventListener("click" , () =>{
    body.classList.toggle("dark");
    
    if(body.classList.contains("dark")){
        modeText.innerText = "Light mode";
    }else{
        modeText.innerText = "Dark mode";
    }
});


