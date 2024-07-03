document.addEventListener('DOMContentLoaded', function() {
    var links = document.querySelectorAll('#linkContainer .langLink');
    var formContainerFi = document.getElementById('formContainerFi');
    var formContainerEn = document.getElementById('formContainerEn');

    links.forEach(function(link) {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            var lang = link.getAttribute('data-lang');

            if (lang === 'fi') {
                formContainerFi.style.display = 'block';
                formContainerEn.style.display = 'none';
            } else if (lang === 'en') {
                formContainerFi.style.display = 'none';
                formContainerEn.style.display = 'block';
            }
        });
    });

    // Initially show the Finnish form
    formContainerFi.style.display = 'block';
    formContainerEn.style.display = 'none';
});