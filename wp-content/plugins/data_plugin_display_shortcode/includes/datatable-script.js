jQuery(document).ready(function ($) {
    // Initialize DataTable
    $('#datatable').DataTable({
        ajax: {
            url: datatable_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'get_datatable_data'
            },
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('Error:', textStatus, errorThrown);
            }
        },
        columns: [
            {data: 'name'},
            {
                data: 'url',
                render: function (data, type, row) {
                    return '<button class="btn btn-primary" onclick="showPokemonDetails(\'' + row.name + '\')">View Details</button>';
                }
            }
        ]
    });

    // Add custom CSS
    $('head').append('<style>\
        .modal-dialog {\
            max-width: 75%; \
            margin: 0 auto;\
        }\
    </style>');

    window.showPokemonDetails = function (name) {
        $.ajax({
            url: datatable_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'get_pokemon_details',
                name: name
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    // Display Pokemon details
                    $('#pokemonModalLabel').text(response.data.name);

                    // Display Abilities
                    var abilitiesContent = 'Abilities: ' + response.data.abilities.join(', ');
                    $('#abilitiesContent').text(abilitiesContent);

                    // Display Stats
                    var statsContent = 'Stats: ';
                    $.each(response.data.stats, function (index, stat) {
                        statsContent += stat.name + ': ' + stat.base_stat + ' ';
                    });
                    $('#statsContent').text(statsContent);


                    // Display Types
                    var typesContent = 'Types: ';
                    $.each(response.data.types, function (index, type) {
                        // Assuming type colors are defined in your CSS
                        var typeColorClass = getTypeColorClass(type);

                        // Generate HTML for colored square with dynamic width
                        var typeHtml = '<span class="type-square ' + typeColorClass + '">' + type + '</span> ';

                        // Append the HTML to typesContent
                        typesContent += typeHtml;
                    });

                    // Update the typesContent
                    $('#typesContent').html(typesContent);

                    // Display Weight
                    $('#weightContent').text('Weight: ' + response.data.weight);

                    // Display Image
                    if (response.data.image_url) {
                        $('#pokemonImage').attr('src', response.data.image_url);
                        $('#pokemonImage').attr('alt', 'Image of ' + response.data.name);
                        $('#pokemonImage').show();
                    } else {
                        $('#pokemonImage').hide();
                    }

                    // Add other information as needed

                    $('#pokemonModal').modal('show');
                } else {
                    // Handle error case
                    console.error('Error:', response.data.message || 'Unknown error');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('Error:', textStatus, errorThrown);
            }
        });
    };

});

function getTypeColorClass(type) { // type color list for block schematic
    switch (type.toLowerCase()) {
        case 'fire':
            return 'fire-type';
        case 'water':
            return 'water-type';
        case 'electric':
            return 'electric-type';
        case 'fairy':
            return 'fairy-type';
        case 'dark':
            return 'dark-type';
        case 'grass':
            return 'grass-type';
        case 'dragon':
            return 'dragon-type';
        case 'normal':
            return 'normal-type';
        case 'ice':
            return 'ice-type';
        case 'fighting':
            return 'fighting-type';
        case 'poison':
            return 'poison-type';
        case 'ground':
            return 'ground-type';
        case 'flying':
            return 'flying-type';
        case 'psychic':
            return 'psychic-type';
        case 'bug':
            return 'bug-type';
        case 'rock':
            return 'rock-type';
        case 'ghost':
            return 'ghost-type';
        case 'steel':
            return 'steel-type';
        case 'stellar':
            return 'stellar-type';
        default:
            return 'normal-type';
    }
}






