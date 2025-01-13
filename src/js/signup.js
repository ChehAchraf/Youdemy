htmx.on('#login-container', 'htmx:afterRequest', function(evt) {
    if (evt.detail.successful) {
        try {
            const response = JSON.parse(evt.detail.xhr.response);
            if (response.success && response.redirect) {
                window.location.href = response.redirect;
            }
        } catch (e) {
            // Response wasn't JSON, just update the UI normally
        }
    }
});
