document.body.addEventListener('htmx:afterRequest', function(evt) {
    if (evt.detail.successful) {
        let response = JSON.parse(evt.detail.xhr.responseText);
        
        if (response.success) {
            // Redirect on success
            window.location.href = response.redirect;
        } else {
            // Show error message
            document.getElementById('signup-response').innerHTML = 
                `<div class="alert alert-danger text-center">${response.message}</div>`;
        }
    } else {
        // Show generic error for failed requests
        document.getElementById('signup-response').innerHTML = 
            '<div class="alert alert-danger text-center">An error occurred. Please try again.</div>';
    }
});
