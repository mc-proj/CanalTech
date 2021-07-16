window.onload= function() {    
    let bouton_efface = document.getElementsByClassName("bouton-efface-projet");

    for(let i=0; i < bouton_efface.length; i++) {
        bouton_efface[i].addEventListener("click", function() {
            let confirmation = confirm("Etes vous sur de vouloir effacer ce projet ?");

            if(confirmation) {
                location.href = "/projet/supprime/" + bouton_efface[i].getAttribute("data-id");
            }
        })
    }
}