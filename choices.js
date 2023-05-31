var ChoiceNumber = 3;

function ajouterChoix(){
    let placeholder = "Choix " + ChoiceNumber;
    let newChoice = document.createElement("input");
    newChoice.setAttribute("type","text");
    newChoice.setAttribute("class","prediChoicesBox");
    newChoice.setAttribute("name","choices[]");
    newChoice.setAttribute("placeholder",placeholder);
    newChoice.setAttribute("required","required");
    document.getElementById("choices").appendChild(newChoice);
    ChoiceNumber++;
};

function supprimerChoix(){
    let input_choice = document.getElementsByClassName("prediChoicesBox");
    if (input_choice.length > 2){
        input_choice.item(input_choice.length-1).remove();
        ChoiceNumber --;
    }
};