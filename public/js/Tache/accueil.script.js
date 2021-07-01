window.onload= function() {

    let xhr = new XMLHttpRequest();
    let bouton_filtre = document.getElementById("bouton-filtre");
    bouton_filtre.addEventListener("click", function() {

        loader(true);
        let elements = document.querySelector('form').elements;
        let obj ={};
        for(let i = 0 ; i < elements.length ; i++){
            let item = elements.item(i);
            obj[item.name] = item.value;
        }
    
        let donnees = JSON.stringify(obj); 
        xhr.open("POST", route_formulaire);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(donnees);
    })

    xhr.onload = function() {
        loader(false);
        let results = xhr.response;

        if(IsJsonString(results)) {  //affichage erreur filtrage
            results = JSON.parse(results);
            //insert d'un message d'erreur de filtrage
            let message_accueil = document.createElement('div');
            message_accueil.id = "message-accueil";
            message_accueil.className = "message-flash-danger";
            message_accueil.innerHTML = "Erreur de filtrage : " + results;
            let div_liste_taches = document.getElementById("liste-taches");
            div_liste_taches.parentNode.insertBefore(message_accueil, div_liste_taches);
        } else { //affichage des taches obtenues
            document.getElementById("liste").innerHTML = results;
            //si un message d'erreur de filtrage est present, on le supprime
            let message_accueil = document.getElementById("message-accueil");

            if(message_accueil !== null) {
                message_accueil.parentNode.removeChild(message_accueil);
            }
        }
    }

    function IsJsonString(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }
};