let xhr = new XMLHttpRequest();
let boutons_facture = document.getElementsByClassName("bouton-facture");
let boutons_efface = document.getElementsByClassName("bouton-efface-tache");

for(let i=0; i < boutons_efface.length; i++) {

    document.addEventListener("click", function (e) {
        if(e.target == boutons_efface[i]) {
            let confirmation = confirm("Etes vous sur de vouloir effacer cette tache ?");

            if(confirmation) {
                location.href = "/supprime/" + boutons_efface[i].getAttribute("data-id");
            }
        }
    });
}

for(let i=0; i < boutons_facture.length; i++) {
    document.addEventListener("click", function (e) {
        if(e.target == boutons_facture[i]) {
            let donnees = {
                "id": boutons_facture[i].getAttribute("data-id")
            }

            loader(true);
            donnees = JSON.stringify(donnees); 
            xhr.open("POST", route_facture);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.send(donnees);
        }
    });
}

xhr.onload = function() {
    loader(false);
    let results = xhr.response;
    results = JSON.parse(results);

    if(results != 0) {
        document.getElementById("td-facture-"+results).innerHTML = "Facturée";
        //si un message d'erreur de création de facture est present, on le supprime
        let message_facture = document.getElementById("message-facture");

        if(message_facture !== null) {
            message_facture.parentNode.removeChild(message_facture);
        }
    } else {
        //insert d'un message d'erreur de création de facture
        let message_facture = document.createElement('div');
        message_facture.id = "message-facture";
        message_facture.className = "message-flash-danger";
        message_facture.innerHTML = "Erreur : la tache n'a pas pu etre marquée facturée";
        let div_stats = document.getElementById("div-stats");
        div_stats.parentNode.insertBefore(message_facture, div_stats);
    }
}