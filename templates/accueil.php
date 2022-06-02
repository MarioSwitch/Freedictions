<?php

//C'est la propriété php_self qui nous l'indique : 
// Quand on vient de index : 
// [PHP_SELF] => /chatISIG/index.php 
// Quand on vient directement par le répertoire templates
// [PHP_SELF] => /chatISIG/templates/accueil.php

// Si la page est appelée directement par son adresse, on redirige en passant pas la page index
// Pas de soucis de bufferisation, puisque c'est dans le cas où on appelle directement la page sans son contexte
if (basename($_SERVER["PHP_SELF"]) != "index.php")
{
	header("Location:../index.php?view=accueil");
	die("");
}

?>
<footer id="footer-protocol">
    <p class="https-available-text">ce site est disponible en https!</p>
    <button class="https-button" onclick="use_https()">utiliser https ✅</button>
    <button class="http-button" onclick="use_http()">http (non sécurisé) ❌</button>
</footer>
<script type="text/javascript">
    let protocol = getCookie("protocol");
    if (protocol !== "")
    {
        document.getElementById("footer-protocol").style.display = "none";
        if (window.location.protocol === "http:" && getCookie("protocol") === "https")
        {
            window.location.protocol = "https:";
        }
        if (window.location.protocol === "https:" && getCookie("protocol") === "http")
        {
            window.location.protocol = "http:";
        }
    }
    function getCookie(cname) {
        let name = cname + "=";
        let decodedCookie = decodeURIComponent(document.cookie);
        let ca = decodedCookie.split(';');
        for(let i = 0; i <ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) === 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }
    function use_https() {
        document.cookie = "protocol=https;expires=Fri, 18 Dec 2099 12:00:00 UTC";
        document.getElementById("footer-protocol").style.display = "none";
        if (window.location.protocol === "http:")
        {
            window.location.protocol = "https:";
        }
    }
    function use_http() {
        document.cookie = "protocol=http;expires=Fri, 18 Dec 2099 12:00:00 UTC";
        document.getElementById("footer-protocol").style.display = "none";
        if (window.location.protocol === "https:")
        {
            window.location.protocol = "http:";
        }
    }
</script>

    <div class="page-header">
      <h1>Ce site est en cours de création (75%)</h1>
    </div>

    <p class="lead">Ce site n'est pas encore prêt et peut contenir des bugs ! N'hésitez pas à les signaler en envoyant un mail à <a href="mailto:marioswitch@marioswitch.fr">marioswitch@marioswitch.fr</a><br>Cependant, la majorité des fonctionnalités sont présentes et le site peut donc quand même être utilisé.</p>

