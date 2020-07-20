var thousand_separator = ".";
function format_number(number){
    number = number.split(",");
    number[0] = number[0].toString().replace(/\B(?=(\d{3})+(?!\d))/g, thousand_separator);
    return number.join(","); //pecah perseribu
}
function reformat_number(number){
    number = number.split(".").join("");
    return number; //balikin komda (,) jadi koma (.)
}
var input_element = document.querySelectorAll(".nf_input");
for(var a = 0 ;a<input_element.length; a++){
    input_element[a].oninput = function(){
        var text = this.value;
        text = reformat_number(text);
        text = format_number(text);
        this.value = text;
    }
}