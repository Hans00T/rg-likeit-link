/* Tämän scriptin tarkoitus on päivittää filu napit formissa niin, että
 * niissä lukee valitun tiedoston nimi. Tämä scripti myös palauttaa nappien tekstit entiselleen, kun formi on lähetetty ja tyhjennetty.
 * Formissa käytetyt napit ovat itseasiassa label elementtejä, jotka näyttävät napeilta. Siksi tässä koodissa puhutaan labeleista, ei buttoneista.
 */
document.addEventListener('DOMContentLoaded', function() {
    function updateFileLabels() {
        const fileInputs = document.querySelectorAll('.file-input');
        fileInputs.forEach(input => {
            input.addEventListener('change', function() {
                const parent = input.closest('.custom-file-input');
                const label = parent.querySelector('.custom-file-label');
                if (input.files.length > 0) {
                    label.textContent = input.files[0].name;
                } else {
                    // Resetoi label elementit, kun tiedostoa ei olla valittu
                    label.textContent = 'Lisää ' + label.htmlFor.replace('your-', '').replace('-', ' ');
                }
            });
        });
    }
	
	// funktio, jolla palautettaan formin filunappien tekstit takaisin alkuperäisiksi.
	function resetFileLabels() {
        const labels = document.querySelectorAll('.custom-file-label');
        labels.forEach(label => {
            const inputId = label.htmlFor;
            if (inputId === 'your-cv') {
                label.textContent = 'Lisää CV';
            } else if (inputId === 'your-application') {
                label.textContent = 'Lisää hakemuskirje';
            } else if (inputId === 'your-photo') {
                label.textContent = 'Lisää kuva';
            }
        });
    }

    // Kutsutaan funktiota jo kerran sivun ladattua
    updateFileLabels();

    // Kuuntelee popup eventtejä, minkä perusteella kutsuu updateFileLabels() funktiota
    document.addEventListener('pumAfterOpen', function(event) {
        updateFileLabels();
    });
	
	// Kuuntelee, milloin formi on lähetetty onnistuneesti ja palauttaa nappien tekstit
    document.addEventListener('wpcf7mailsent', function(event) {
        resetFileLabels();
    });
});