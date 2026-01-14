jQuery(function($){
    /**
     * Fetch Account Managers from JSONPlaceholder /users and populate dropdown.
     * This is front-end only because the API is public and does not require auth.
     */
    $.get('https://jsonplaceholder.typicode.com/users')
        .done(function(users){
            let options = '<option value="">Select manager</option>';
            users.forEach(function(u){
                options += `<option value="${u.name}">${u.name}</option>`;
            });
            $('#account-manager').html(options);
        })
        .fail(function(){
            $('#account-manager').html('<option value="">Unable to load managers</option>');
        });

    /**
     * Conditional logic:
     * Show/hide fields based on selected service without page reload.
     */
    $('#service').on('change', function(){
        $('.conditional').hide();
        const service = $(this).val();
        if(service){
            $('.' + service).css('display','block');
        }
        // Clear previous result when switching services.
        $('#quote-result').empty();
    });

    /**
     * Submit quote via AJAX to WordPress (admin-ajax.php).
     * Server returns: { quote, message } (no API Response ID in UI).
     */
    $('#fqc-form').on('submit', function(e){
        e.preventDefault();

        const $btn = $('#fqc-submit');
        $btn.prop('disabled', true).text('Calculating...');

        $.post(fqcData.ajaxUrl, {
            action: 'fqc_submit',
            nonce: fqcData.nonce,
            ...Object.fromEntries(new FormData(this))
        })
        .done(function(res){
            if(!res.success){
                const msg = res.data && res.data.message ? res.data.message : 'Something went wrong.';
                $('#quote-result').html(`<div class="fqc-card fqc-card-error">${msg}</div>`);
                return;
            }

            // Bordered summary card with bold black result.
            $('#quote-result').html(`
                <div class="fqc-card">
                    <div class="fqc-quote">Estimated Quote: R${res.data.quote}</div>
                    <div class="fqc-success">${res.data.message || ''}</div>
                </div>
            `);
        })
        .fail(function(){
            $('#quote-result').html('<div class="fqc-card fqc-card-error">Request failed. Please try again.</div>');
        })
        .always(function(){
            $btn.prop('disabled', false).text('Calculate Quote');
        });
    });

    /**
     * Reset/New Quote:
     * - Clears form values
     * - Hides conditional sections
     * - Clears result card
     */
    $('#fqc-reset').on('click', function(){
        $('#fqc-form')[0].reset();
        $('.conditional').hide();
        $('#quote-result').empty();
        // Reset account manager placeholder (keep loaded options).
        if($('#account-manager option').length){
            $('#account-manager').val('');
        }
    });
});