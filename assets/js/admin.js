jQuery(document).ready(function ($) {
    // Clicks dinámicos para ocultar / mostrar campos del formulario
    var unClickCheckbox = document.getElementById('aceprensa_un_click');
    var usernameRow = document.getElementById('aceprensa_username_row');
    var usernameInput = document.getElementsByName('aceprensa_username')[0];

    function toggleUsernameField() {
        if (unClickCheckbox && usernameRow) {
            if (unClickCheckbox.checked) {
                usernameRow.style.display = 'table-row';
                usernameInput.required = true;
            } else {
                usernameRow.style.display = 'none';
                usernameInput.required = false;
                usernameInput.value = '';
            }
        }
    }
    // Set initial state
    toggleUsernameField();
    // Add event listener
    if (unClickCheckbox) {
        unClickCheckbox.addEventListener('change', toggleUsernameField);
    }
    // Obtenemos las categorías guardadas impresas en el DOM por PHP
    var savedCats = JSON.parse(document.querySelector('.savedcats').value);

    // Inicializa Select2 en el campo de categorías.
    $('#aceprensa_categories').select2({
        placeholder: 'Selecciona categorías',
        minimumInputLength: 3, // Mínimo de 3 caracteres para activar la búsqueda.
        ajax: {
            url: remotePostsData.url, //remotePostsData.url = 'admin-ajax.php'
            method: 'post',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    action: 'conectar_api', //llama a la función 'conectar_api' de funtions.php
                    endpoint: '/wp/v2/categories?search=' + params.term, // Término de búsqueda proporcionado por el usuario.
                    nonce: remotePostsData.nonce //añadir el nonce 'remote-posts'
                };
            },
            processResults: function (resp) {
                return {
                    results: resp.data.map(function (category) {
                        return {
                            id: category.id,
                            text: category.name
                        };
                    }),
                };
            },
        },
    });
    // Itera sobre cada elemento en el array 'savedCats'
    for (var i in savedCats) {
        // Crea un objeto 'data' con las propiedades 'id' (clave) y 'text' (valor)
        var data = {
            id: i,
            text: savedCats[i],
        };
        // Crea un nuevo elemento 'Option' con el texto y valor correspondientes
        var newOption = new Option(data.text, data.id, true, true);
        // Agrega el nuevo 'Option' al elemento html con el id 'aceprensa_categories'
        $('#aceprensa_categories').append(newOption);
        // Crea un nuevo input oculto y lo inserta antes del elemento '#aceprensa_categories'
        createNewInput(i, savedCats[i]).insertBefore("#aceprensa_categories");
    }
    // Maneja la deselección en el elemento 'aceprensa_categories'
    $('#aceprensa_categories').on('select2:unselect', function (e) {
        // Elimina el elemento Option asociado a la categoría deseleccionada
        e.params.data.element.remove();
        // Elimina el input oculto asociado a la categoría deseleccionada
        $('input[name="aceprensa_selected_categories[' + e.params.data.id + ']"').remove();
    });

    // Maneja el evento de selección en el elemento 'aceprensa_categories'
    $('#aceprensa_categories').on('select2:select', function (e) {
        // Crea un nuevo input oculto para la categoría seleccionada y lo inserta antes del elemento '#aceprensa_categories'
        createNewInput(e.params.data.id, e.params.data.text).insertBefore("#aceprensa_categories");
    });
    // Función para crear un nuevo input oculto
    function createNewInput(id, value) {
        // Crea un nuevo input oculto con el nombre, tipo y valor especificados
        var newInput = $("<input>").attr({
            name: 'aceprensa_selected_categories[' + id + ']',
            type: "hidden",
            value: value
        });
        return newInput;
    }
});
