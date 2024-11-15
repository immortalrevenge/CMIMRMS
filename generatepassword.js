function generateRandomString(length) {
  var result = '';
  var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  var charactersLength = characters.length;

  for (var i = 0; i < length; i++) {
    result += characters.charAt(Math.floor(Math.random() * charactersLength));
  }

  return result;
}


function generate_password() {
  // Generate a random combination of 3 letters and 3 digits
  var randomCombination = generateRandomString(6);

  // Display the random combination in the HTML element with id "output_password"
  document.getElementById("output").value = randomCombination;
}