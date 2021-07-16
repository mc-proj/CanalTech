window.onload= function() {
    let bouton_filtre = document.getElementById("bouton-filtre");
    bouton_filtre.addEventListener("click", function(e) {

        loader(true);
        e.preventDefault();
        let form = document.getElementById("form-filtre");
        let form_data = new FormData(form);
        let xhr = new XMLHttpRequest();
        xhr.open('POST', route_formulaire , true);
        xhr.send(form_data);

        xhr.onload = function() {
            loader(false);
            if(xhr.status === 200) {
                let data = xhr.response;

                if(data.includes('class="message-flash-danger"')) {
                    //le formulaire contient au moins 1 champ invalide
                    document.getElementsByTagName("body")[0].innerHTML = data;
                } else {
                    let liste = document.getElementById("liste");
                    liste.innerHTML = data;
                }
            }
        }
    })

    let reset_filtre = document.getElementById("reset-filtre");
    reset_filtre.addEventListener("click", function() {
        document.getElementById("filtre_tache_date_debut_date").value = "";
        document.getElementById("filtre_tache_date_debut_time").value = "";
        document.getElementById("filtre_tache_date_fin_date").value = "";
        document.getElementById("filtre_tache_date_fin_time").value = "";
        document.getElementById("filtre_tache_projet").value = "";
        simulateClick(bouton_filtre);
    })

    var simulateClick = function (elem) {
        // Create our event (with options)
        var evt = new MouseEvent('click', {
            bubbles: true,
            cancelable: true,
            view: window
        });
        // If cancelled, don't dispatch our event
        var canceled = !elem.dispatchEvent(evt);
    };
};