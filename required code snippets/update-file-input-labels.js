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
                    // Reset the label elements when no file is selected
                    const originalLabel = label.getAttribute('data-original-label');
                    label.textContent = originalLabel;
                }
            });
        });
    }

    // Function to reset form file button texts back to original
    function resetFileLabels() {
        const labels = document.querySelectorAll('.custom-file-label');
        labels.forEach(label => {
            const inputId = label.htmlFor;
            if (inputId === 'your-cv' || inputId === 'your-cv-eng') {
                label.textContent = inputId === 'your-cv' ? 'Lisää CV' : 'Add a resume';
            } else if (inputId === 'your-application' || inputId === 'your-application-eng') {
                label.textContent = inputId === 'your-application' ? 'Lisää hakemuskirje' : 'Add a cover letter';
            } else if (inputId === 'your-photo' || inputId === 'your-photo-eng') {
                label.textContent = inputId === 'your-photo' ? 'Lisää kuva' : 'Add your photo';
            }
            // Save the original label text for resetting later
            label.setAttribute('data-original-label', label.textContent);
        });
    }

    // Call the function once the page loads
    updateFileLabels();
    resetFileLabels();

    // Listen for popup events and call updateFileLabels() accordingly
    document.addEventListener('pumAfterOpen', function(event) {
        updateFileLabels();
    });

    // Listen for form submission and reset button texts
    document.addEventListener('wpcf7mailsent', function(event) {
        resetFileLabels();
    });
});