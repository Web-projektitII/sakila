const menutoggle = () => {
    var x = document.querySelector("#myTopnav");
    if (x.className === "topnav") x.className += " responsive"; 
    else x.className = "topnav";
    }

const poista_is_invalid = event => {
  let element = event.target;
  if (element.classList.contains('is-invalid')){
    element.classList.remove("is-invalid");    
    element.removeEventListener("keyup", poista_is_invalid);
    }
  console.log("sisältääkö "+element.name+" is_invalid-luokan: ",element.classList.contains('is-invalid'))
  }    

(() => {
  'use strict'
  // Fetch all the forms we want to apply custom Bootstrap validation styles to
   document.querySelectorAll(".is-invalid").forEach(
    element => element.addEventListener("keyup", poista_is_invalid)
    ) 
  
  const forms = document.querySelectorAll('.needs-validation')
  // Loop over them and prevent submission
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
              event.preventDefault()
              event.stopPropagation()
            }
            form.classList.add('was-validated')
          }, false)
        })
  })()   
