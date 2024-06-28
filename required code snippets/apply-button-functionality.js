document.addEventListener('DOMContentLoaded', function() {  // wrap this into a function that takes popup id as parameter gets called from another code snippet(s) which are used in portfolio items??
    var btnHaePaikkaa = document.querySelector("a.avia-button.avia-size-x-large");
    var btnHaePaikkaaTitle = document.querySelector("span.avia_iconbox_title");
    if (btnHaePaikkaa) {
        /* otetaan napin linkki talteen. Linkki viittaa Likeit:n työnhakuilmoitukseen, ja se sisältää työpaikkailmoituksen ID-arvon, 
        * jota puolestaan tarvitaan, jotta tiedetään, mihin työpaikkailmoitukseen hakemus on lähetetty
        * samalla poistetaan ylimääräiset välit linkistä, jos niitä on */
        var url = decodeURIComponent(btnHaePaikkaa.getAttribute('href').trim());

        // erotetaan ID-arvo likeit URL-linkistä
		var idMatch = url.match(/\/(\d+)[^\/]*$/);
		var advertId = idMatch ? idMatch[1] : null;	// jos URL:ssa ei syystä tai toisesta ole ID:tä, ei lisätä mitään

        if (advertId) {
            // btnHaePaikkaa.classList.add("popmake-1917"); // Lisätään elementin luokkalistaan luokka "popmake-1917", jonka perusteella popup hakemus avataan, kun hae-paikkaa nappia painetaan
            btnHaePaikkaa.addEventListener('click', function(event) {
                event.preventDefault(); // Ei lataa mitään (esim. likeit:n sivu tai ilmoitusta uudelleen), kun nappia painetaan

                PUM.open(1917); // avataan tässä välissä popup vain, jos linkin urlissa oli id. Arvo 1917 on työnhaku-popupin ID-arvo
			
				//	odotetaan, että popup ja lomake ovat kokonaan ladattu
				setTimeout(function() {
					var hiddenField = document.querySelector('input[name="advert-id"]');	// etsitään formin piilokenttä
					if (hiddenField) {
						hiddenField.value = advertId;	// lisätään likeit:n linkistä saatu id arvo formin piilokenttään
					} else {
						console.log("Hidden field not found");	// jos piilokenttää ei syystä tai toisesta löydetä, laitetaan ilmoitus konsoliin
					}
				}, 500);
            });
        }
    }
});