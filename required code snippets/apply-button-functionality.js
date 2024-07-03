document.addEventListener('DOMContentLoaded', function() {
	
    var btnHaePaikkaa = document.querySelector("a.avia-button.avia-size-x-large");
    if (btnHaePaikkaa) {
        /* otetaan napin linkki talteen. Linkki viittaa Likeit:n työnhakuilmoitukseen, ja se sisältää työpaikkailmoituksen ID-arvon, 
        * jota puolestaan tarvitaan, jotta tiedetään, mihin työpaikkailmoitukseen hakemus on lähetetty
        * samalla poistetaan ylimääräiset välit linkistä, jos niitä on */
        var url = decodeURIComponent(btnHaePaikkaa.getAttribute('href').trim());

        // erotetaan ID-arvo likeit URL-linkistä
		var idMatch = url.match(/\/(\d+)[^\/]*$/);
		var advertId = idMatch ? idMatch[1] : null;	// jos URL:ssa ei syystä tai toisesta ole ID:tä, ei lisätä mitään

        if (advertId) {
            btnHaePaikkaa.addEventListener('click', function(event) {
                event.preventDefault(); // Ei lataa mitään (esim. likeit:n sivua tai ilmoitusta uudelleen), kun nappia painetaan
				
                PUM.open(1917); // avataan tässä välissä popup vain, jos linkin urlissa oli id. Arvo 1917 on työnhaku-popupin ID-arvo
			
				//	odotetaan, että popup ja lomake ovat kokonaan ladattu
				setTimeout(function() {
                    // Etsitään piilokentät enkku ja suomi lomakkeista
                    var hiddenFieldFinnish = document.querySelector('input[name="advert-id"]'); // Finnish form
                    var hiddenFieldEnglish = document.querySelector('input[name="advert-id-eng"]'); // English form

                    if (hiddenFieldFinnish) {
                        hiddenFieldFinnish.value = advertId; // Set the advert ID in the hidden field for Finnish form
                    }
					if (hiddenFieldEnglish) {
                        hiddenFieldEnglish.value = advertId; // Set the advert ID in the hidden field for English form
                    } else {
                        console.log("Hidden field not found"); // Log if the hidden field is not found
                    }
				}, 500);
            });
        }
    }
});