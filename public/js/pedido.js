// Ubicación: public/js/pedido.js

/*
 * Usamos $(document).ready() para asegurar que este código
 * solo se ejecute cuando toda la página (el HTML) se haya cargado.
 */
$(document).ready(function() {
    
    // Escuchar el clic en el botón "Añadir Producto"
    $('#btnAnadirProducto').on('click', function() {
        
        // 1. Obtener los datos del producto seleccionado
        const productoSelect = $('#producto'); // El <select>
        const productoOpcion = productoSelect.find('option:selected'); // El <option>
        
        const id = productoOpcion.val();
        const nombre = productoOpcion.data('nombre');
        const precio = parseFloat(productoOpcion.data('precio'));
        const cantidad = parseInt($('#cantidad').val());

        // Validar que se haya seleccionado un producto y la cantidad sea válida
        if (!id || cantidad <= 0 || isNaN(precio)) {
            alert('Por favor, seleccione un producto y una cantidad válida.');
            return;
        }

        // 2. Calcular el subtotal del item
        const subtotalItem = (precio * cantidad).toFixed(2); // .toFixed(2) para 2 decimales

        // 3. Crear la fila de la tabla (HTML)
        const fila = `
            <tr data-idproducto="${id}">
                <td>${id}</td>
                <td>${nombre}</td>
                <td>${cantidad}</td>
                <td>S/ ${precio.toFixed(2)}</td>
                <td>S/ ${subtotalItem}</td>
                <td><button type="button" class="btn btn-quitar">Quitar</button></td>
            </tr>
        `;

        // 4. Añadir la fila a la tabla
        $('#cuerpoTablaDetalle').append(fila);

        // 5. [MUY IMPORTANTE] Crear los inputs ocultos para el backend
        // Estos son los datos que PHP recibirá en el $_POST
        const inputsOcultos = `
            <div id="item-oculto-${id}">
                <input type="hidden" name="productos[id][]" value="${id}">
                <input type="hidden" name="productos[cantidad][]" value="${cantidad}">
                <input type="hidden" name="productos[precio][]" value="${precio}">
                <input type="hidden" name="productos[subtotal][]" value="${subtotalItem}">
            </div>
        `;

        // 6. Añadir los inputs ocultos al formulario
        $('#itemsOcultosParaEnvio').append(inputsOcultos);

        // 7. Actualizar los totales
        actualizarTotales();

        // 8. Resetear los campos de añadir
        productoSelect.val('');
        $('#cantidad').val(1);
    });

    // Escuchar clics en el botón "Quitar"
    // Usamos 'on()' en el 'tbody' porque los botones 'Quitar' se crean dinámicamente
    $('#cuerpoTablaDetalle').on('click', '.btn-quitar', function() {
        
        // 1. Obtener la fila (<tr>) padre del botón
        const fila = $(this).closest('tr');
        
        // 2. Obtener el ID del producto de esa fila
        const idProducto = fila.data('idproducto');

        // 3. Quitar la fila visible de la tabla
        fila.remove();

        // 4. Quitar los inputs ocultos correspondientes
        $(`#item-oculto-${idProducto}`).remove();

        // 5. Recalcular
        actualizarTotales();
    });


    // Función para calcular y mostrar los totales
    function actualizarTotales() {
        let subtotalGeneral = 0;

        // Recorrer todas las filas de la tabla
        $('#cuerpoTablaDetalle tr').each(function() {
            // Obtener el texto del subtotal (ej: "S/ 71.00")
            let subtotalTexto = $(this).find('td:nth-child(5)').text();
            // Quitar el "S/ " y convertir a número
            let subtotalItem = parseFloat(subtotalTexto.replace('S/ ', ''));
            
            subtotalGeneral += subtotalItem;
        });

        const igv = subtotalGeneral * 0.18;
        const total = subtotalGeneral + igv;

        // Mostrar los totales en el HTML (con 2 decimales)
        $('#spanSubtotal').text(subtotalGeneral.toFixed(2));
        $('#spanIGV').text(igv.toFixed(2));
        $('#spanTotal').text(total.toFixed(2));

        // Guardar los totales en los inputs ocultos que se enviarán
        $('#hiddenSubtotal').val(subtotalGeneral.toFixed(2));
        $('#hiddenIGV').val(igv.toFixed(2));
        $('#hiddenTotal').val(total.toFixed(2));
    }

});