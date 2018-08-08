jQuery(document).ready(function () {

    elements = jQuery('[href*="/publish.json/task:Run"], [data-publish*="/publish.json/task:Run"]');

    disable = function() {
        elements
            .attr('disabled', 'disabled')
            .find('> .fa').removeClass('fa-cload-upload').addClass('fa-spin fa-refresh');
    };

    enable = function() {
        elements
            .removeAttr('disabled')
            .find('> .fa').removeClass('fa-spin fa-refresh').addClass('fa-cload-upload');
    };

    elements.on('click', function() {
        event.preventDefault();

        let element = $(this);
        let url = element.data('publish') || element.attr('href');

        if (element.attr('disabled')) {
            return;
        }
        
        // this.disable();
        // request(url, () => this.enable());
        
        let modal;
        let callback;
        let data = new FormData();
        data.append('admin-nonce', GravAdmin.config.admin_nonce);

        disable();
        fetch(url, {method: 'POST', body: data, credentials: 'same-origin'})
            .then(res => res.json())
            .then(result => {
                if (modal) {
                    if (!result.error) {
                        modal.close();
                    }
                }
                if (callback) {
                    callback(result);
                }
                if (result.status == 'success') {
                    Grav.default.Utils.toastr.success(`Publish started.`, null, {onHidden: enable});
                } else {
                  Grav.default.Utils.toastr.error(result.message, null, {onHidden: enable});                  
                }
            })
            .catch(error => {
                Grav.default.Utils.toastr.error(`Publish Failed: <br /> ${error}`, null, {onHidden: enable});
            });
          
    });

});
