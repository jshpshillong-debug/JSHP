// Covers 11th, 12th, 13th
        function getOrdinalSuffix(day) {
    if (day > 3 && day < 21) return 'th'; 
    switch (day % 10) {
        case 1:  return "st";
        case 2:  return "nd";
        case 3:  return "rd";
        default: return "th";
    }
}
// Example usage:
const today = new Date();
const day = today.getDate();
const suffix = getOrdinalSuffix(day);

document.getElementById("date-display").innerHTML = 
    `Today is the ${day}<sup>${suffix}</sup> of ${today.toLocaleString('default', { month: 'long' })}`;