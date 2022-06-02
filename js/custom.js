var ChoiceNumber = 3;

function ajouterChoix(){
    let placeholder = "Choix " + ChoiceNumber;
    let newChoice = document.createElement("input");
    newChoice.setAttribute("type","text");
    newChoice.setAttribute("class","form-control");
    // newChoice.setAttribute("id","prediChoicesBox"); not valid html there : see https://stackoverflow.com/questions/3607291/javascript-and-getelementbyid-for-multiple-elements-with-the-same-id
    newChoice.setAttribute("name","choices[]");
    newChoice.setAttribute("placeholder",placeholder);
    newChoice.setAttribute("required","required");
    document.getElementById("choices").appendChild(newChoice);
    ChoiceNumber++;
};
function supprimerChoix(){
    let input_choice = document.getElementsByClassName("form-control");
    if (input_choice.length > 4) // if there are more than 2 choices
    {
        input_choice.item(input_choice.length-1).remove();
        ChoiceNumber --;
    }
};