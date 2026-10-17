export function loadRepresentatives() {
    const $representativeSelect = $('#rep-id');
    $representativeSelect
        .empty()
        .append(
            $('<option>', {
                value: '',
                text: 'Loading representatives...'
            })
        )
        .prop('disabled', true);

    $.ajax({
        url: '/api/representatives',
        method: 'GET',
        dataType: 'json',
        headers: {
            Accept: 'application/json'
        }
    })
        .done(function (representatives) {
            $representativeSelect.empty();

            $representativeSelect.append(
                $('<option>', {
                    value: '',
                    text: 'Select a representative'
                })
            );

            representatives.forEach(function (representative) {
                $representativeSelect.append(
                    $('<option>', {
                        value: representative.id,
                        text: representative.name + ' — ' + representative.email
                    })
                );
            });

            if (representatives.length === 0) {
                $representativeSelect.empty().append(
                    $('<option>', {
                        value: '',
                        text: 'No representatives found'
                    })
                );
            }
        })
        .fail(function () {
            $representativeSelect.empty().append(
                $('<option>', {
                    value: '',
                    text: 'Failed to load representatives'
                })
            );
        })
        .always(function () {
            $representativeSelect.prop('disabled', false);
        });
}

export function summaryTextToggle(status, element) {
    if (status == 'success') {
        $('#' + element).removeClass('text-danger');
        $('#' + element).addClass('text-success');
    } else {
        $('#' + element).addClass('text-danger');
        $('#' + element).removeClass('text-success');
    }
}