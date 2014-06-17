function    startGoogleCategoriesImport(url)
{
    new Ajax.Request(url, {
        method: 'get',
        parameters: {
            evalJS: true
        },
        onSuccess: function(transport) {
            window.location.reload();
        },
        onFailure: function(transport) {
            window.location.reload();
        }
    });
}