<?php
// Si la page est appelée directement par son adresse, on redirige en passant pas la page index
// Pas de soucis de bufferisation, puisque c'est dans le cas où on appelle directement la page sans son contexte
if (basename($_SERVER["PHP_SELF"]) != "index.php")
{
        header("Location:../index.php?view=accueil");
        die("");
}
include_once("templates/search.php");
include_once("lis/maLibSQL.pdo.php");
?>
<h1 class="title">À propos du site</h1>
<h2 class="category-h2">Description</h2>
<p class="text">
Ce site web permet de miser des points virtuels sur des prédictions créées par les utilisateurs.<br><br>
Tous les utilisateurs peuvent créer des prédictions, du moment qu'ils sont connectés.<br><br>
Les prédictions possèdent une date limite de mise (à partir de laquelle les paris sont bloqués) et un nombre variable de réponses.<br><br>
Les prédictions peuvent posséder autant de choix que le créateur le souhaite. Cependant, 2 choix sont nécessaires pour créer une prédiction.<br><br>
Une fois la date limite dépassée, le créateur peut alors valider la bonne réponse. Une fois fait, les utilisateurs ayant misés sur cette réponse se partagent tous les points (suivant leur mise initiale).
</p>
<hr class="line">
<h2 class="category-h2">Règles</h2>
<h3 class="title-h3">Chapitre 1 : Généralités</h3>
<p class="text left">
1a. Tout non-respect des règles pourra être sanctionné par un administrateur.<br>
1b. Les sanctions données par les administateurs ne sont pas contestables, du moment qu'ils sont justifiés par une preuve du non-respect de ces règles.<br>
1c. Si l'administrateur responsable de la sanction ne fournit aucune preuve de non-respect des règles ou une preuve non reçevable, vous pouvez exiger une annulation de la sanction ainsi qu'une compensation en points.<br>
1d. Les règles peuvent évoluer à tout moment. Consultez-les régulièrement pour être sûr de connaître toutes les règles applicables.<br>
</p>
<h3 class="title-h3">Chapitre 2 : Utilisateurs</h3>
<p class="text left">
2a. Tous les noms d'utilisateurs doivent respecter les règles. Un administrateur pourra, sans préavis, renommer et/ou supprimer un compte, si une ou plusieurs règles ne sont pas respectées.<br>
2b. Les noms d'utilisateurs ne doivent pas contenir d'insultes, ni de contenu NSFW.<br>
2c. Merci de ne pas utiliser un pseudo connu, notamment pour faire croire qu'une personnalité crée des prédictions inappropriées.
</p>
<h3 class="title-h3">Chapitre 3 : Prédictions</h3>
<p class="text left">
3a. Toutes les prédictions doivent respecter les règles. Un administrateur pourra, sans préavis, modifier et/ou supprimer la prédiction, voire le compte, si une ou plusieurs des règles ne sont pas respectées. Notez que la suppression d'une prédiction rendra les points misés aux utilisateurs concernés.<br>
3b. Seul l'auteur de la prédiction est responsable des prédictions qu'il crée. Si nous constatons des abus, nous pourrons être amenés à restreindre la création de prédictions à certaines personnes uniquement !<br>
3c. Les prédictions ne doivent concerner uniquement des faits publics attendus.<br>
3d. Les prédictions doivent se terminer avant que la réponse à la question ne soit actée.<br>
3e. Les prédictions doivent respecter une certaine éthique. Ainsi, il est strictement interdit de prédire, directement ou indirectement, sur la mort de personne(s) ou la violence envers une personne et/ou un groupe. Le non-respect de cette règle entraînera la suppression immédiate et irréversible du compte ainsi que de toutes les prédictions créées. Nous rappelons que nous pouvons restreindre la création de prédictions !<br>
3f. Les prédictions ne doivent pas contenir d'insultes, ni de contenu NSFW.
</p>
<hr class="line">
<h2 class="category-h2">Répartition des points</h2>
<h3 class="title-h3">Définition</h3>
<p class="text">
Losqu'une réponse est donnée à une prédiction, les points sont répartis comme suit :<br><br>
Si la réponse gagnante n'a aucun vote, tous les points misés sont perdus.<br>
Si la réponse gagnante a un seul vote, l'utilisateur ayant voté pour cette réponse gagne tous les points misés.<br>
Dans le reste des cas, le total des points misés est réparti entre les gagnants, à hauteur du pourcentage de points dépensés dans la répose gagnante.<br>
</p>
<h3 class="title-h3">Exemple détaillé</h3>
<table class="table">
    <tr>
        <th>Choix</th>
        <th>Nombre de votants</th>
        <th>Nombre de points</th>
        <th>Rendement</th>
        <th>Distribution</th>
    </tr>
    <tr>
        <td>Choix n°1</td>
        <td>3</td>
        <td>400</td>
        <td>2,50</td>
        <td>Personne 1 : 100 points<br>Personne 2 : 290 points<br>Personne 3 : 10 points</td>
    </tr>
    <tr>
        <td>Choix n°2</td>
        <td>4</td>
        <td>400</td>
        <td>2,50</td>
        <td>Personne 4 : 1 point<br>Personne 5 : 99 points<br>Personnes  6 et 7 : 150 points chacun</td>
    </tr>
    <tr>
        <td>Choix n°3</td>
        <td>6</td>
        <td>200</td>
        <td>5,00</td>
        <td>Personnes 8 à 11 : 25 points chacun<br>Personnes 12 et 13 : 50 points chacun</td>
    </tr>
</table>
<p class="text">
<br>
Prenons une prédiction qui a collecté 1000 points (de 13 personnes) répartis comme ci-dessus.<br><br>

Si le choix n°1 est la bonne réponse, les 1000 points sont partagés entre les 3 votants du choix n°1.<br>
Comme la personne 1 a contribué à hauteur de 100 points (soit 100/400 = 25% des points dépensés dans la réponse 1), il récupère 25% des 1000 points, soit 250 points. Notez que sa mise a bien été multipliée par 2,5.<br>
La personne 2, ayant misé 290 points, soit 72,5% des 400 points, reçoit 725 points (= 72,5% de 1000 = 290 x 2,5).<br>
La personne 3, reçoit 25 points (= 2,5% de 1000 = 10 x 2,5).<br><br>

Si le choix 2 est gagnant, en suivant la même logique :<br>
La personne 4 reçoit 2,5 points (= 0,25% de 1000 = 1 x 2,5), arrondi à l'inférieur, soit 2 points.<br>
La personne 5 reçoit 247(,5) points.<br>
Les personnes 6 et 7 reçoivent chacun 375 points.<br><br>

Si le choix 3 est gagnant, les personnes 8 à 11 gagnent 125 points et les personnes 12 et 13, 250 points.
</p>
<h3 class="title-h3">Conclusion</h3>
<p class="text">
Si vous gagnez, votre mise est multipliée par le rendement (icône de coupe). Si cette valeur n'est pas entière, elle est arrondie à l'inférieur.<br>
Notez que tant que la prédiction n'est pas terminée, le rendement peut ÉNORMÉMENT évoluer !<br>
Notez aussi que ce sont les mises perdues qui font votre victoire. Gagner alors que tout le monde a voté la même chose ne sert à rien, puisque votre mise sera multipliée par 1,00.
</p>
<hr class="line">
<h2 class="category-h2">Crédits</h2>
<p class="text">
Les icônes proviennent de <a href="https://www.streamlinehq.com/icons/streamline-mini-line">Stream Line HQ</a> et des prédictions <a href="https://www.twitch.tv/">Twitch</a>.<br><br>
v1.0 (juin 2022) par <a href="https://www.marioswitch.fr/">MarioSwitch</a> et <a href="https://github.com/yoshi2999">Yosh</a><br>
v1.1 (mai 2023) par <a href="https://www.marioswitch.fr/">MarioSwitch</a>
</p>
