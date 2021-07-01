//gestion lien actif
let liens_nav = document.getElementsByClassName("lien-nav");

for(let i=0; i < liens_nav.length; i++) {

    document.addEventListener("click", function (e) {
        if(e.target == liens_nav[i]) {

            document.querySelector('#liste-nav a.active').classList.remove('active');
            liens_nav[i].className = "lien-sans-style lien-nav active";
        }
    });
}

function loader(show) {

    if(show) {
        document.getElementById("loader").style.display = "block";
    } else {
        document.getElementById('loader').style.display = "none";
    }
}